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
    }
}
