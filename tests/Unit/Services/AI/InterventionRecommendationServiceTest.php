<?php

namespace Tests\Unit\Services\AI;

use App\Models\CommodityProfile;
use App\Models\Shipment;
use App\Services\AI\InterventionRecommendationService;
use Tests\TestCase;

class InterventionRecommendationServiceTest extends TestCase
{
    public function test_moderate_risk_returns_three_distinct_non_mandatory_actions(): void
    {
        $shipment = new Shipment([
            'status' => 'Packed',
            'distance_km' => 162,
            'duration_hours' => 2.1,
        ]);

        $profile = new CommodityProfile([
            'name' => 'Cucumber',
            'local_name' => 'Timun',
            'perishability_level' => 'High',
            'temperature_control_recommended' => true,
            'optimal_temp_min' => 10,
            'optimal_temp_max' => 12.5,
            'optimal_humidity_min' => 95,
            'optimal_humidity_max' => 95,
            'chilling_threshold_c' => 10,
            'source_name' => 'UC Davis Produce Facts',
            'source_url' => 'https://postharvest.ucdavis.edu/',
        ]);

        $service = app(InterventionRecommendationService::class);

        $result = $service->recommend(
            $shipment,
            $profile,
            [
                'quality_at_arrival' => 83,
                'remaining_shelf_life_at_arrival_days' => 4.0,
                'transit_margin_hours' => 74.6,
                'safe_transit_status' => 'Within estimated safe window',
                'expiry_constraint_applied' => true,
            ],
            [
                'risk_severity' => 'Moderate',
                'urgency_level' => 'Elevated',
                'dispatch_deadline' => 'Within 24 hours',
                'intervention_status' => 'Monitor',
                'top_drivers' => [
                    ['title' => 'Shelf-Life Pressure'],
                ],
            ],
            [
                'status' => 'Not provided',
            ]
        );

$this->assertCount(3, $result['actions']);

$this->assertSame(
    'Utamakan pengiriman ini dibanding muatan berisiko rendah dan periksa ulang dalam 24 jam',
    $result['actions'][0]['action']
);

$this->assertSame(
    'Jaga batas masa simpan tercatat dari penundaan tambahan',
    $result['actions'][1]['action']
);

$this->assertSame(
    'Periksa dan catat suhu muatan sebelum berangkat bila memungkinkan',
    $result['actions'][2]['action']
);

$this->assertSame('Pantau', $result['intervention_status']);

$this->assertNotSame(
    $result['decision_rationale'],
    $result['expected_outcome']
);
    }

    public function test_handling_guidance_uses_actual_step2_profile_field_names(): void
    {
        $shipment = new Shipment(['status' => 'Packed']);
        $profile = new CommodityProfile([
            'name' => 'Cucumber',
            'local_name' => 'Timun',
            'temperature_control_recommended' => true,
            'optimal_temp_min' => 10,
            'optimal_temp_max' => 12.5,
            'optimal_humidity_min' => 95,
            'optimal_humidity_max' => 95,
            'chilling_threshold_c' => 10,
            'source_name' => 'UC Davis Produce Facts',
            'source_url' => 'https://postharvest.ucdavis.edu/',
        ]);

        $service = app(InterventionRecommendationService::class);
        $result = $service->recommend(
            $shipment,
            $profile,
            [
                'quality_at_arrival' => 83,
                'remaining_shelf_life_at_arrival_days' => 4,
                'transit_margin_hours' => 72,
                'safe_transit_status' => 'Within estimated safe window',
                'expiry_constraint_applied' => true,
            ],
            [
                'risk_severity' => 'Moderate',
                'urgency_level' => 'Elevated',
                'dispatch_deadline' => 'Dalam 24 jam',
                'intervention_status' => 'Monitor',
                'top_drivers' => [['title' => 'Shelf-Life Pressure']],
            ],
            ['status' => 'Not provided']
        );

        $this->assertSame(
    'Disarankan menggunakan transportasi berpendingin (suhu terkontrol)',
    $result['recommended_vehicle']
);
        $this->assertSame('10.0–12.5°C', $result['recommended_temperature_range']);
        $this->assertSame('95–95% RH', $result['recommended_humidity_range']);
        $this->assertStringContainsString(
    'Suhu di bawah 10.0°C',
    $result['chilling_threshold']
);
        $this->assertSame('10.0–12.5°C; 95–95% RH', $result['recommended_storage']);
        $this->assertSame('UC Davis Produce Facts', $result['reference_source_name']);
    }

    public function test_high_risk_primary_action_is_dispatch_priority(): void
    {
        $shipment = new Shipment(['status' => 'Packed']);
        $service = app(InterventionRecommendationService::class);

        $result = $service->recommend(
            $shipment,
            null,
            [
                'quality_at_arrival' => 55,
                'remaining_shelf_life_at_arrival_days' => 1.2,
                'transit_margin_hours' => 12,
                'safe_transit_status' => 'Within estimated safe window',
                'expiry_constraint_applied' => true,
            ],
            [
                'risk_severity' => 'High',
                'urgency_level' => 'High',
                'dispatch_deadline' => 'Dalam 6 jam',
                'intervention_status' => 'Required',
                'top_drivers' => [
                    ['title' => 'Arrival Quality Pressure'],
                ],
            ],
            ['status' => 'Not provided']
        );

$this->assertSame(
    'Prioritaskan keberangkatan dalam rentang waktu mendesak',
    $result['primary_action']
);

$this->assertSame('Dalam 6 jam', $result['action_window']);
    }
}
