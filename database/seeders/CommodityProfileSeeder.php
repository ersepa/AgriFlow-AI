<?php

namespace Database\Seeders;

use App\Models\CommodityProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use RuntimeException;

class CommodityProfileSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/commodity_profiles.json');

        if (!File::exists($path)) {
            throw new RuntimeException(
                "Commodity knowledge dataset not found: {$path}"
            );
        }

        $payload = json_decode(
            File::get($path),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $profiles = $payload['profiles'] ?? null;

        if (!is_array($profiles) || $profiles === []) {
            throw new RuntimeException(
                'Commodity knowledge dataset contains no profiles.'
            );
        }

        $seenSlugs = [];

        foreach ($profiles as $index => $profile) {
            $this->validateProfile($profile, $index);

            if (in_array($profile['slug'], $seenSlugs, true)) {
                throw new RuntimeException(
                    "Duplicate commodity slug in dataset: {$profile['slug']}"
                );
            }

            $seenSlugs[] = $profile['slug'];

            CommodityProfile::updateOrCreate(
                ['slug' => $profile['slug']],
                $profile
            );
        }

        $this->command?->info(
            sprintf(
                'Imported %d commodity profiles from dataset version %s.',
                count($profiles),
                $payload['dataset_version'] ?? 'unknown'
            )
        );
    }

    private function validateProfile(array $profile, int $index): void
    {
        $required = [
            'name',
            'slug',
            'category',
            'perishability_level',
            'temperature_control_recommended',
            'source_name',
            'source_url',
        ];

        foreach ($required as $field) {
            if (!array_key_exists($field, $profile)) {
                throw new RuntimeException(
                    "Commodity profile #{$index} is missing required field '{$field}'."
                );
            }
        }

        if (!filter_var($profile['source_url'], FILTER_VALIDATE_URL)) {
            throw new RuntimeException(
                "Commodity '{$profile['name']}' has an invalid source URL."
            );
        }

        $minTemp = $profile['optimal_temp_min'] ?? null;
        $maxTemp = $profile['optimal_temp_max'] ?? null;

        if (
            $minTemp !== null
            && $maxTemp !== null
            && (float) $minTemp > (float) $maxTemp
        ) {
            throw new RuntimeException(
                "Commodity '{$profile['name']}' has optimal_temp_min greater than optimal_temp_max."
            );
        }

        $minRh = $profile['optimal_humidity_min'] ?? null;
        $maxRh = $profile['optimal_humidity_max'] ?? null;

        if (
            $minRh !== null
            && $maxRh !== null
            && (float) $minRh > (float) $maxRh
        ) {
            throw new RuntimeException(
                "Commodity '{$profile['name']}' has optimal_humidity_min greater than optimal_humidity_max."
            );
        }

        $commodityClass =
            $profile['commodity_class']
            ?? 'fresh_produce';

        $qualityModel =
            $profile['quality_model_type']
            ?? 'shelf_life_quality';

        $allowedClasses = [
            'fresh_produce',
            'dry_commodity',
            'dry_grain',
        ];

        $allowedModels = [
            'shelf_life_quality',
            'storage_stability',
        ];

        if (!in_array($commodityClass, $allowedClasses, true)) {
            throw new RuntimeException(
                "Commodity '{$profile['name']}' has unsupported commodity_class '{$commodityClass}'."
            );
        }

        if (!in_array($qualityModel, $allowedModels, true)) {
            throw new RuntimeException(
                "Commodity '{$profile['name']}' has unsupported quality_model_type '{$qualityModel}'."
            );
        }

        if ($qualityModel === 'storage_stability') {
            $hasStorageThreshold =
                ($profile['safe_moisture_short_term_max_percent'] ?? null) !== null
                || ($profile['safe_moisture_long_term_max_percent'] ?? null) !== null
                || ($profile['safe_relative_humidity_max_percent'] ?? null) !== null;

            if (!$hasStorageThreshold) {
                throw new RuntimeException(
                    "Dry commodity '{$profile['name']}' must include at least one validated storage-stability threshold."
                );
            }
        }

        foreach (
            [
                'safe_moisture_short_term_max_percent',
                'safe_moisture_long_term_max_percent',
                'safe_relative_humidity_max_percent',
            ]
            as $percentageField
        ) {
            $value = $profile[$percentageField] ?? null;

            if (
                $value !== null
                && (
                    (float) $value < 0
                    || (float) $value > 100
                )
            ) {
                throw new RuntimeException(
                    "Commodity '{$profile['name']}' has invalid {$percentageField}."
                );
            }
        }
    }
}
