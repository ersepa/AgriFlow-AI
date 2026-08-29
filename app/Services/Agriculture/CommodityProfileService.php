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
            'storage_recommendation' => $this->storageRecommendation($profile),
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
                'message' => 'No scenario temperature was provided.',
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
        if (
            !$profile
            || $profile->optimal_temp_min === null
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
            'storage_recommendation' => 'Verify commodity-specific storage requirements',
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
