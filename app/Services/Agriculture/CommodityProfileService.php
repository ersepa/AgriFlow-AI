<?php

namespace App\Services\Agriculture;

use App\Models\CommodityProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Commodity knowledge resolver for AgriFlow.
 *
 * Responsibilities:
 * - exact normalized matching for canonical names, Indonesian names, and aliases
 * - commodity-specific storage summary
 * - commodity-specific temperature assessment
 * - safe unknown fallback (never invents missing agricultural parameters)
 *
 * This service intentionally does NOT use fuzzy matching for operational
 * decisions. A wrong crop match is more dangerous than returning "unknown".
 */
class CommodityProfileService
{
    private ?Collection $profileIndex = null;

    public function findForCommodity(?string $commodity): ?CommodityProfile
    {
        $needle = $this->normalize($commodity);

        if ($needle === '') {
            return null;
        }

        return $this->index()->get($needle);
    }

    public function summary(?CommodityProfile $profile): array
    {
        if (!$profile) {
            return $this->unknownSummary();
        }

        return [
            'found' => true,
            'name' => $profile->name,
            'local_name' => $profile->local_name,
            'category' => $profile->category,
            'commodity_class' =>
                $profile->commodity_class
                ?? 'fresh_produce',
            'quality_model_type' =>
                $profile->quality_model_type
                ?? 'shelf_life_quality',
            'profile_context' => $profile->profile_context,
            'perishability_level' => $profile->perishability_level,
            'temperature_control_recommended' => $profile->temperature_control_recommended,
            'storage_life_min_days' => $profile->storage_life_min_days,
            'storage_life_max_days' => $profile->storage_life_max_days,
            'optimal_temp_min' => $profile->optimal_temp_min,
            'optimal_temp_max' => $profile->optimal_temp_max,
            'optimal_humidity_min' => $profile->optimal_humidity_min,
            'optimal_humidity_max' => $profile->optimal_humidity_max,
            'chilling_threshold_c' => $profile->chilling_threshold_c,
            'q10_factor' => $profile->q10_factor,

            'safe_moisture_short_term_max_percent' =>
                $profile->safe_moisture_short_term_max_percent,
            'safe_moisture_long_term_max_percent' =>
                $profile->safe_moisture_long_term_max_percent,
            'safe_relative_humidity_max_percent' =>
                $profile->safe_relative_humidity_max_percent,
            'reference_storage_max_months' =>
                $profile->reference_storage_max_months,
            'storage_science_note' =>
                $profile->storage_science_note,

            'storage_recommendation' => $this->storageRecommendation($profile),
            'source_references' =>
                $profile->source_references
                ?? [],
            'source_name' => $profile->source_name,
            'source_url' => $profile->source_url,
            'notes' => $profile->notes,
        ];
    }

    public function assessTemperature(
        ?CommodityProfile $profile,
        ?float $temperatureC,
        float $exposureHours = 0
    ): array {
        if ($temperatureC === null) {
            return [
                'available' => false,
                'status' => 'Not provided',
                'severity' => 'unknown',
                'temperature_c' => null,
                'risk_modifier' => 0,
                'message' => 'No recorded or scenario cargo temperature was provided.',
            ];
        }

        if (
            $profile
            && ($profile->quality_model_type ?? null)
                === 'storage_stability'
            && (
                $profile->optimal_temp_min === null
                || $profile->optimal_temp_max === null
            )
        ) {
            return [
                'available' => true,
                'status' => 'Temperature reference unavailable',
                'severity' => 'unknown',
                'temperature_c' => round($temperatureC, 1),
                'risk_modifier' => 0,
                'message' =>
                    'This dry-commodity profile is driven by validated moisture/RH storage evidence. No exact cargo-temperature threshold is asserted from the current source set.',
            ];
        }

        if (
            !$profile
            || $profile->optimal_temp_min === null
            || $profile->optimal_temp_max === null
        ) {
            return [
                'available' => true,
                'status' => 'Unknown commodity profile',
                'severity' => 'unknown',
                'temperature_c' => round($temperatureC, 1),
                'risk_modifier' => 0,
                'message' => 'AgriFlow has no validated temperature profile for this commodity yet, so no temperature benefit or penalty is applied.',
            ];
        }

        $min = (float) $profile->optimal_temp_min;
        $max = (float) $profile->optimal_temp_max;

        $chilling = $profile->chilling_threshold_c !== null
            ? (float) $profile->chilling_threshold_c
            : null;

        $durationPenalty = match (true) {
            $exposureHours >= 24 => 6,
            $exposureHours >= 12 => 4,
            $exposureHours >= 6 => 2,
            default => 0,
        };

        if ($temperatureC >= $min && $temperatureC <= $max) {
            return [
                'available' => true,
                'status' => 'Optimal',
                'severity' => 'low',
                'temperature_c' => round($temperatureC, 1),
                'risk_modifier' => -6,
                'message' => sprintf(
                    '%.1f°C is inside the %.1f–%.1f°C reference range for %s.',
                    $temperatureC,
                    $min,
                    $max,
                    $profile->local_name ?: $profile->name
                ),
            ];
        }

        if ($chilling !== null && $temperatureC < $chilling) {
            $deviation = $chilling - $temperatureC;
            $modifier = min(
                25,
                8 + (int) round($deviation * 2) + $durationPenalty
            );

            return [
                'available' => true,
                'status' => 'Chilling risk',
                'severity' => $modifier >= 18 ? 'high' : 'medium',
                'temperature_c' => round($temperatureC, 1),
                'risk_modifier' => $modifier,
                'message' => sprintf(
                    '%.1f°C is below the %.1f°C chilling threshold used by the validated %s profile.',
                    $temperatureC,
                    $chilling,
                    $profile->local_name ?: $profile->name
                ),
            ];
        }

        if ($temperatureC < $min) {
            $deviation = $min - $temperatureC;
            $modifier = min(
                14,
                2 + (int) round($deviation * 1.5) + $durationPenalty
            );

            return [
                'available' => true,
                'status' => 'Below optimum',
                'severity' => $modifier >= 10 ? 'medium' : 'low',
                'temperature_c' => round($temperatureC, 1),
                'risk_modifier' => $modifier,
                'message' => sprintf(
                    '%.1f°C is below the %.1f–%.1f°C reference range for %s.',
                    $temperatureC,
                    $min,
                    $max,
                    $profile->local_name ?: $profile->name
                ),
            ];
        }

        $deviation = $temperatureC - $max;
        $modifier = min(
            22,
            3 + (int) round($deviation * 1.25) + $durationPenalty
        );

        return [
            'available' => true,
            'status' => 'Above optimum',
            'severity' => $modifier >= 15
                ? 'high'
                : ($modifier >= 8 ? 'medium' : 'low'),
            'temperature_c' => round($temperatureC, 1),
            'risk_modifier' => $modifier,
            'message' => sprintf(
                '%.1f°C is above the %.1f–%.1f°C reference range for %s and can accelerate deterioration.',
                $temperatureC,
                $min,
                $max,
                $profile->local_name ?: $profile->name
            ),
        ];
    }

    public function perishabilityRisk(?CommodityProfile $profile): int
    {
        if (!$profile) {
            return 4;
        }

        return match (strtolower($profile->perishability_level)) {
            'very high' => 8,
            'high' => 6,
            'moderate' => 3,
            'low' => 1,
            default => 4,
        };
    }

    public function storageRecommendation(?CommodityProfile $profile): string
    {
        if (!$profile) {
            return 'Verify commodity-specific storage requirements';
        }

        if (
            ($profile->quality_model_type ?? null)
                === 'storage_stability'
        ) {
            $parts = [];

            if (
                $profile->safe_moisture_short_term_max_percent
                !== null
            ) {
                $parts[] = sprintf(
                    'Moisture ≤ %.1f%% for short-term storage guidance',
                    (float) $profile->safe_moisture_short_term_max_percent
                );
            }

            if (
                $profile->safe_moisture_long_term_max_percent
                !== null
            ) {
                $parts[] = sprintf(
                    'Moisture ≤ %.1f%% for longer storage guidance',
                    (float) $profile->safe_moisture_long_term_max_percent
                );
            }

            if (
                $profile->safe_relative_humidity_max_percent
                !== null
            ) {
                $parts[] = sprintf(
                    'RH ≤ %.0f%% where the cited source supports this limit',
                    (float) $profile->safe_relative_humidity_max_percent
                );
            }

            return $parts !== []
                ? implode('; ', $parts)
                : 'Verify dry-commodity moisture and humidity conditions';
        }

        if (
            $profile->optimal_temp_min === null
            || $profile->optimal_temp_max === null
        ) {
            return 'Verify commodity-specific storage requirements';
        }

        $temp = $this->formatRange(
            (float) $profile->optimal_temp_min,
            (float) $profile->optimal_temp_max,
            '°C'
        );

        $humidity = '';

        if (
            $profile->optimal_humidity_min !== null
            && $profile->optimal_humidity_max !== null
        ) {
            $humidity = '; ' . $this->formatRange(
                (float) $profile->optimal_humidity_min,
                (float) $profile->optimal_humidity_max,
                '% RH'
            );
        }

        return $temp . $humidity;
    }

    public function assessStorageStability(
        ?CommodityProfile $profile,
        ?float $moisturePercent = null,
        ?float $relativeHumidityPercent = null,
        string $storageHorizon = 'short_term'
    ): array {
        if (
            !$profile
            || ($profile->quality_model_type ?? null)
                !== 'storage_stability'
        ) {
            return [
                'applicable' => false,
                'status' => 'Not applicable',
                'severity' => 'unknown',
                'message' =>
                    'Storage-stability assessment is only used for validated dry-commodity profiles.',
            ];
        }

        $moistureLimit =
            $storageHorizon === 'long_term'
                ? $profile->safe_moisture_long_term_max_percent
                : $profile->safe_moisture_short_term_max_percent;

        $rhLimit =
            $profile->safe_relative_humidity_max_percent;

        if (
            $moisturePercent === null
            && $relativeHumidityPercent === null
        ) {
            return [
                'applicable' => true,
                'available' => false,
                'status' => 'Storage condition evidence required',
                'severity' => 'unknown',
                'moisture_percent' => null,
                'moisture_limit_percent' =>
                    $moistureLimit,
                'relative_humidity_percent' => null,
                'relative_humidity_limit_percent' =>
                    $rhLimit,
                'message' =>
                    'Validated storage thresholds exist, but recorded cargo moisture/RH condition was not provided. AgriFlow does not fabricate a condition score.',
            ];
        }

        $breaches = [];

        if (
            $moisturePercent !== null
            && $moistureLimit !== null
            && $moisturePercent > $moistureLimit
        ) {
            $breaches[] = sprintf(
                'moisture %.1f%% exceeds the %.1f%% reference limit',
                $moisturePercent,
                $moistureLimit
            );
        }

        if (
            $relativeHumidityPercent !== null
            && $rhLimit !== null
            && $relativeHumidityPercent > $rhLimit
        ) {
            $breaches[] = sprintf(
                'RH %.1f%% exceeds the %.1f%% reference limit',
                $relativeHumidityPercent,
                $rhLimit
            );
        }

        if ($breaches !== []) {
            return [
                'applicable' => true,
                'available' => true,
                'status' => 'Outside reference storage limits',
                'severity' => 'high',
                'moisture_percent' => $moisturePercent,
                'moisture_limit_percent' => $moistureLimit,
                'relative_humidity_percent' =>
                    $relativeHumidityPercent,
                'relative_humidity_limit_percent' =>
                    $rhLimit,
                'message' => ucfirst(
                    implode('; ', $breaches)
                ) . '.',
            ];
        }

        return [
            'applicable' => true,
            'available' => true,
            'status' => 'Within available reference limits',
            'severity' => 'low',
            'moisture_percent' => $moisturePercent,
            'moisture_limit_percent' => $moistureLimit,
            'relative_humidity_percent' =>
                $relativeHumidityPercent,
            'relative_humidity_limit_percent' =>
                $rhLimit,
            'message' =>
                'Recorded storage condition does not exceed the validated limits currently stored for this commodity.',
        ];
    }

    public function supportedCommodityCount(): int
    {
        return CommodityProfile::query()->count();
    }

    private function index(): Collection
    {
        if ($this->profileIndex !== null) {
            return $this->profileIndex;
        }

        $index = collect();

        CommodityProfile::query()
            ->orderBy('id')
            ->get()
            ->each(function (CommodityProfile $profile) use ($index) {
                $candidates = array_filter([
                    $profile->name,
                    $profile->local_name,
                    ...($profile->aliases ?? []),
                ]);

                foreach ($candidates as $candidate) {
                    $key = $this->normalize((string) $candidate);

                    if ($key === '') {
                        continue;
                    }

                    /*
                     * First validated profile wins if an alias collision somehow
                     * reaches production. Dataset tests should prevent collisions.
                     */
                    if (!$index->has($key)) {
                        $index->put($key, $profile);
                    }
                }
            });

        return $this->profileIndex = $index;
    }

    private function unknownSummary(): array
    {
        return [
            'found' => false,
            'name' => 'Unknown',
            'local_name' => null,
            'category' => null,
            'commodity_class' => null,
            'quality_model_type' => null,
            'profile_context' => null,
            'perishability_level' => 'Unknown',
            'temperature_control_recommended' => false,
            'storage_life_min_days' => null,
            'storage_life_max_days' => null,
            'optimal_temp_min' => null,
            'optimal_temp_max' => null,
            'optimal_humidity_min' => null,
            'optimal_humidity_max' => null,
            'chilling_threshold_c' => null,
            'q10_factor' => null,
            'safe_moisture_short_term_max_percent' => null,
            'safe_moisture_long_term_max_percent' => null,
            'safe_relative_humidity_max_percent' => null,
            'reference_storage_max_months' => null,
            'storage_science_note' => null,
            'storage_recommendation' => 'Verify commodity-specific storage requirements',
            'source_references' => [],
            'source_name' => null,
            'source_url' => null,
            'notes' => 'No matching validated commodity profile is available. AgriFlow deliberately avoids inventing storage parameters.',
        ];
    }

    private function normalize(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return (string) Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish();
    }

    private function formatRange(float $min, float $max, string $suffix): string
    {
        $format = static fn (float $value): string =>
            rtrim(
                rtrim(
                    number_format($value, 1, '.', ''),
                    '0'
                ),
                '.'
            );

        if (abs($min - $max) < 0.001) {
            return $format($min) . $suffix;
        }

        return $format($min) . '–' . $format($max) . $suffix;
    }
}
