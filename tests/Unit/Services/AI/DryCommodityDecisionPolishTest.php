<?php

namespace Tests\Unit\Services\AI;

use App\Models\CommodityProfile;
use App\Models\Shipment;
use App\Services\AI\InterventionRecommendationService;
use PHPUnit\Framework\TestCase;

class DryCommodityDecisionPolishTest extends TestCase
{
    public function test_dry_commodity_recommendation_uses_storage_evidence_not_temperature_fallback(): void
    {
        $service =
            new InterventionRecommendationService();

        $profile =
            new CommodityProfile([
                'name' => 'Green Coffee',
                'local_name' => 'Kopi',
                'commodity_class' =>
                    'dry_commodity',
                'quality_model_type' =>
                    'storage_stability',
                'safe_moisture_short_term_max_percent' =>
                    11.0,
                'safe_moisture_long_term_max_percent' =>
                    11.0,
                'safe_relative_humidity_max_percent' =>
                    65.0,
                'reference_storage_max_months' =>
                    12,
            ]);

        $shipment = new Shipment([
            'status' => 'Packed',
            'distance_km' => 534,
            'duration_hours' => 6.3,
        ]);

        $qualityPrediction = [
            'condition_model_type' =>
                'storage_stability',
            'quality_at_arrival' => null,
            'remaining_shelf_life_at_arrival_days' =>
                7.2,
            'transit_margin_hours' => 173.6,
            'safe_transit_status' =>
                'Within safe transit window',
            'expiry_constraint_applied' => true,
            'storage_stability_assessment' => [
                'applicable' => true,
                'available' => false,
                'status' =>
                    'Storage telemetry required',
                'message' =>
                    'Validated storage thresholds exist, but cargo moisture/RH telemetry was not provided.',
            ],
        ];

        $risk = [
            'risk_severity' => 'Moderate',
            'urgency_level' => 'Elevated',
            'dispatch_deadline' =>
                'Within 24 hours',
            'intervention_status' =>
                'Monitor',
            'top_drivers' => [
                [
                    'title' =>
                        'Storage Condition Evidence',
                ],
            ],
        ];

        $result = $service->recommend(
            $shipment,
            $profile,
            $qualityPrediction,
            $risk,
            [
                'status' =>
                    'Temperature reference unavailable',
            ]
        );

        $actions =
            collect($result['actions'])
                ->pluck('action')
                ->implode(' | ');

        $this->assertStringContainsString(
            'moisture',
            strtolower($actions)
        );

        $this->assertStringContainsString(
            'relative humidity',
            strtolower($actions)
        );

        $this->assertStringNotContainsString(
            'verify cargo temperature',
            strtolower($actions)
        );

        $this->assertSame(
            'storage_stability',
            $result['quality_model_type']
        );

        $this->assertSame(
            11.0,
            (float) $result[
                'safe_moisture_short_term_max_percent'
            ]
        );

        $this->assertSame(
            65.0,
            (float) $result[
                'safe_relative_humidity_max_percent'
            ]
        );
    }

    public function test_dry_commodity_recorded_deadline_is_not_called_biological_shelf_life(): void
    {
        $service =
            new InterventionRecommendationService();

        $profile =
            new CommodityProfile([
                'name' => 'Milled Rice',
                'local_name' => 'Beras',
                'quality_model_type' =>
                    'storage_stability',
            ]);

        $shipment =
            new Shipment([
                'status' => 'Packed',
            ]);

        $result =
            $service->recommend(
                $shipment,
                $profile,
                [
                    'condition_model_type' =>
                        'storage_stability',
                    'remaining_shelf_life_at_arrival_days' =>
                        5.0,
                    'transit_margin_hours' =>
                        120.0,
                    'safe_transit_status' =>
                        'Within safe transit window',
                    'expiry_constraint_applied' =>
                        true,
                    'storage_stability_assessment' => [
                        'available' => false,
                    ],
                ],
                [
                    'risk_severity' =>
                        'Moderate',
                    'urgency_level' =>
                        'Elevated',
                    'dispatch_deadline' =>
                        'Within 24 hours',
                ],
                []
            );

        $text =
            strtolower(
                collect($result['actions'])
                    ->pluck('reason')
                    ->implode(' ')
            );

        $this->assertStringContainsString(
            'not claimed as biological shelf life',
            $text
        );
    }
}
