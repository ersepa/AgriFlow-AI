<?php

namespace Tests\Unit\Services\Agriculture;

use PHPUnit\Framework\TestCase;

class DryCommodityDatasetTest extends TestCase
{
    private function dataset(): array
    {
        $path = database_path(
            'data/commodity_profiles.json'
        );

        return json_decode(
            file_get_contents($path),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    public function test_green_coffee_profile_is_storage_stability_based(): void
    {
        $profile = collect(
            $this->dataset()['profiles']
        )->firstWhere(
            'slug',
            'green-coffee'
        );

        $this->assertNotNull($profile);
        $this->assertSame(
            'storage_stability',
            $profile['quality_model_type']
        );
        $this->assertSame(
            11.0,
            (float) $profile[
                'safe_moisture_long_term_max_percent'
            ]
        );
        $this->assertSame(
            65.0,
            (float) $profile[
                'safe_relative_humidity_max_percent'
            ]
        );
        $this->assertNull(
            $profile['storage_life_min_days']
        );
        $this->assertNull(
            $profile['storage_life_max_days']
        );
    }

    public function test_milled_rice_profile_uses_irri_moisture_thresholds(): void
    {
        $profile = collect(
            $this->dataset()['profiles']
        )->firstWhere(
            'slug',
            'milled-rice'
        );

        $this->assertNotNull($profile);
        $this->assertSame(
            14.0,
            (float) $profile[
                'safe_moisture_short_term_max_percent'
            ]
        );
        $this->assertSame(
            13.0,
            (float) $profile[
                'safe_moisture_long_term_max_percent'
            ]
        );
        $this->assertContains(
            'beras',
            $profile['aliases']
        );
        $this->assertNotContains(
            'padi',
            $profile['aliases']
        );
        $this->assertNotContains(
            'paddy',
            $profile['aliases']
        );
    }

    public function test_dry_commodity_profiles_do_not_invent_fresh_produce_shelf_life(): void
    {
        $profiles = collect(
            $this->dataset()['profiles']
        )->where(
            'quality_model_type',
            'storage_stability'
        );

        $this->assertNotEmpty($profiles);

        foreach ($profiles as $profile) {
            $this->assertNull(
                $profile[
                    'storage_life_min_days'
                ]
            );
            $this->assertNull(
                $profile[
                    'storage_life_max_days'
                ]
            );
            $this->assertNull(
                $profile[
                    'q10_factor'
                ]
            );
        }
    }
}
