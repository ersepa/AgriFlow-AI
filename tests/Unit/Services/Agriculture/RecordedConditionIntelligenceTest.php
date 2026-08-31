<?php

namespace Tests\Unit\Services\Agriculture;

use App\Models\CommodityProfile;
use App\Models\Harvest;
use App\Models\Shipment;
use App\Services\Agriculture\CommodityProfileService;
use App\Services\Agriculture\QualityPredictionService;
use Carbon\Carbon;
use Tests\TestCase;

class RecordedConditionIntelligenceTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_fresh_commodity_uses_recorded_condition_as_baseline(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-31 10:00:00'));

        $service = new QualityPredictionService(
            new CommodityProfileService()
        );

        $shipment = $this->freshShipment();
        $shipment->recorded_temperature_c = 17.0;
        $shipment->recorded_relative_humidity_percent = 80.0;
        $shipment->condition_source = 'manual_entry';
        $shipment->condition_recorded_at = Carbon::now();

        $result = $service->predict(
            $shipment,
            $this->freshProfile()
        );

        $this->assertSame(17.0, $result['temperature_c']);
        $this->assertSame('recorded_shipment', $result['temperature_basis']);
        $this->assertSame(
            'Above optimum',
            $result['temperature_assessment']['status']
        );
        $this->assertSame(
            80.0,
            $result['relative_humidity_percent']
        );
        $this->assertSame(
            'recorded_shipment',
            $result['relative_humidity_basis']
        );
        $this->assertSame(
            'Outside reference',
            $result['condition_assessment']['overall_status']
        );
        $this->assertSame(
            'Recorded',
            $result['condition_assessment']['evidence_status']
        );
        $this->assertFalse(
            $result['condition_assessment']['is_live_sensor_data']
        );
    }

    public function test_scenario_condition_overrides_recorded_baseline_without_mutating_shipment(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-31 10:00:00'));

        $service = new QualityPredictionService(
            new CommodityProfileService()
        );

        $shipment = $this->freshShipment();
        $shipment->recorded_temperature_c = 17.0;
        $shipment->recorded_relative_humidity_percent = 88.0;

        $baseline = $service->predict(
            $shipment,
            $this->freshProfile()
        );

        $scenario = $service->predict(
            $shipment,
            $this->freshProfile(),
            [
                'temperature' => 12.0,
            ]
        );

        $this->assertSame(17.0, $baseline['temperature_c']);
        $this->assertSame(12.0, $scenario['temperature_c']);
        $this->assertSame('scenario_input', $scenario['temperature_basis']);
        $this->assertSame(
            'Scenario override + recorded baseline',
            $scenario['condition_assessment']['evidence_status']
        );
        $this->assertSame(17.0, $shipment->recorded_temperature_c);
    }

    public function test_missing_fresh_condition_remains_an_explicit_evidence_gap(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-31 10:00:00'));

        $service = new QualityPredictionService(
            new CommodityProfileService()
        );

        $result = $service->predict(
            $this->freshShipment(),
            $this->freshProfile()
        );

        $this->assertNull($result['temperature_c']);
        $this->assertSame(
            'reference_neutral_fallback',
            $result['temperature_basis']
        );
        $this->assertNull($result['relative_humidity_percent']);
        $this->assertSame(
            'Condition evidence unavailable',
            $result['condition_assessment']['overall_status']
        );
        $this->assertSame(
            'Unavailable',
            $result['condition_assessment']['evidence_status']
        );
    }

    public function test_dry_commodity_uses_recorded_moisture_and_rh_against_reference_limits(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-31 10:00:00'));

        $service = new QualityPredictionService(
            new CommodityProfileService()
        );

        $shipment = $this->dryShipment();
        $shipment->recorded_moisture_percent = 12.2;
        $shipment->recorded_relative_humidity_percent = 72.0;
        $shipment->condition_source = 'manual_entry';
        $shipment->condition_recorded_at = Carbon::now();

        $result = $service->predict(
            $shipment,
            $this->dryProfile()
        );

        $this->assertSame(12.2, $result['moisture_percent']);
        $this->assertSame(72.0, $result['relative_humidity_percent']);
        $this->assertSame('recorded_shipment', $result['moisture_basis']);
        $this->assertSame(
            'Outside reference storage limits',
            $result['storage_stability_assessment']['status']
        );
        $this->assertSame(
            'Outside reference',
            $result['condition_assessment']['overall_status']
        );
        $this->assertNull($result['quality_at_arrival']);
    }

    private function freshProfile(): CommodityProfile
    {
        return new CommodityProfile([
            'name' => 'Tomato',
            'local_name' => 'Tomat',
            'quality_model_type' => 'shelf_life_quality',
            'storage_life_min_days' => 7,
            'storage_life_max_days' => 10,
            'optimal_temp_min' => 10,
            'optimal_temp_max' => 13,
            'optimal_humidity_min' => 85,
            'optimal_humidity_max' => 95,
            'chilling_threshold_c' => 10,
            'perishability_level' => 'High',
            'source_name' => 'Reference test profile',
            'source_url' => 'https://example.test/tomato',
        ]);
    }

    private function freshShipment(): Shipment
    {
        $harvest = new Harvest([
            'commodity' => 'Tomat',
            'weight' => 500,
            'location' => 'Bogor',
            'harvest_date' => '2026-08-30',
            'expiry_date' => '2026-09-07',
        ]);

        $shipment = new Shipment([
            'origin' => 'Bogor',
            'destination' => 'Jakarta',
            'status' => 'Packed',
            'distance_km' => 70,
            'duration_hours' => 3,
        ]);

        $shipment->setRelation('harvest', $harvest);

        return $shipment;
    }

    private function dryProfile(): CommodityProfile
    {
        return new CommodityProfile([
            'name' => 'Green Coffee',
            'local_name' => 'Kopi',
            'commodity_class' => 'dry_commodity',
            'quality_model_type' => 'storage_stability',
            'safe_moisture_short_term_max_percent' => 11.0,
            'safe_moisture_long_term_max_percent' => 11.0,
            'safe_relative_humidity_max_percent' => 65.0,
            'perishability_level' => 'Low',
            'source_name' => 'Reference test profile',
            'source_url' => 'https://example.test/coffee',
        ]);
    }

    private function dryShipment(): Shipment
    {
        $harvest = new Harvest([
            'commodity' => 'Kopi',
            'weight' => 1000,
            'location' => 'Lampung',
            'harvest_date' => '2026-08-20',
            'expiry_date' => '2026-09-30',
        ]);

        $shipment = new Shipment([
            'origin' => 'Lampung',
            'destination' => 'Jakarta',
            'status' => 'Packed',
            'distance_km' => 250,
            'duration_hours' => 7,
        ]);

        $shipment->setRelation('harvest', $harvest);

        return $shipment;
    }
}
