<?php

use App\Models\CommodityProfile;
use App\Models\Harvest;
use App\Models\Shipment;
use App\Services\Agriculture\QualityPredictionService;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-08-29 12:00:00'));
});

afterEach(function () {
    Carbon::setTestNow();
});

function cucumberProfileForQualityTest(): CommodityProfile
{
    return new CommodityProfile([
        'name' => 'Cucumber',
        'local_name' => 'Timun',
        'slug' => 'cucumber',
        'category' => 'Fruit Vegetable',
        'storage_life_min_days' => 10,
        'storage_life_max_days' => 14,
        'optimal_temp_min' => 10,
        'optimal_temp_max' => 12.5,
        'optimal_humidity_min' => 95,
        'optimal_humidity_max' => 95,
        'chilling_threshold_c' => 10,
        'q10_factor' => null,
        'perishability_level' => 'High',
        'temperature_control_recommended' => true,
        'source_name' => 'UC Davis Postharvest Research and Extension Center',
        'source_url' => 'https://postharvest.ucdavis.edu/produce-facts-sheets/cucumber',
    ]);
}

function shipmentForQualityTest(
    float $durationHours = 8,
    string $expiryDate = '2026-09-07'
): Shipment {
    $harvest = new Harvest([
        'commodity' => 'Timun',
        'weight' => 800,
        'location' => 'Bandung',
        'harvest_date' => '2026-08-26',
        'expiry_date' => $expiryDate,
    ]);

    $shipment = new Shipment([
        'origin' => 'Bandung',
        'destination' => 'Jakarta',
        'status' => 'Packed',
        'distance_km' => 160,
        'duration_hours' => $durationHours,
    ]);

    $shipment->setRelation('harvest', $harvest);

    return $shipment;
}

it('predicts better reference quality at cucumber reference temperature than at warm temperature', function () {
    $service = app(QualityPredictionService::class);
    $shipment = shipmentForQualityTest();
    $profile = cucumberProfileForQualityTest();

    $optimal = $service->predict($shipment, $profile, [
        'temperature' => 12,
    ]);

    $warm = $service->predict($shipment, $profile, [
        'temperature' => 25,
    ]);

    expect($optimal['temperature_deterioration_factor'])->toBe(1.0)
        ->and($warm['temperature_deterioration_factor'])->toBeGreaterThan(1.0)
        ->and($optimal['reference_quality_at_arrival'])
            ->toBeGreaterThan($warm['reference_quality_at_arrival'])
        ->and($optimal['reference_remaining_shelf_life_at_arrival_days'])
            ->toBeGreaterThan($warm['reference_remaining_shelf_life_at_arrival_days']);
});

it('does not reward cucumber chilling temperature with longer reference shelf life', function () {
    $service = app(QualityPredictionService::class);
    $shipment = shipmentForQualityTest();
    $profile = cucumberProfileForQualityTest();

    $optimal = $service->predict($shipment, $profile, [
        'temperature' => 12,
    ]);

    $cold = $service->predict($shipment, $profile, [
        'temperature' => 5,
    ]);

    expect($cold['temperature_assessment']['status'])->toBe('Chilling risk')
        ->and($cold['temperature_deterioration_factor'])->toBe(1.0)
        ->and($cold['reference_quality_at_arrival'])
            ->toBeLessThan($optimal['reference_quality_at_arrival'])
        ->and($cold['reference_remaining_shelf_life_at_arrival_days'])
            ->toBeLessThan($optimal['reference_remaining_shelf_life_at_arrival_days']);
});

it('makes delay consume more reference product life', function () {
    $service = app(QualityPredictionService::class);
    $shipment = shipmentForQualityTest();
    $profile = cucumberProfileForQualityTest();

    $normal = $service->predict($shipment, $profile, [
        'temperature' => 12,
        'delay' => 0,
    ]);

    $delayed = $service->predict($shipment, $profile, [
        'temperature' => 12,
        'delay' => 8,
    ]);

    expect($delayed['effective_transit_age_days'])
        ->toBeGreaterThan($normal['effective_transit_age_days'])
        ->and($delayed['reference_quality_at_arrival'])
        ->toBeLessThan($normal['reference_quality_at_arrival']);
});

it('uses recorded expiry as the operational constraint when it is more conservative', function () {
    $service = app(QualityPredictionService::class);
    $shipment = shipmentForQualityTest(8, '2026-08-29');
    $profile = cucumberProfileForQualityTest();

    $result = $service->predict($shipment, $profile, [
        'temperature' => 12,
    ]);

    expect($result['expiry_constraint_applied'])->toBeTrue()
        ->and($result['shelf_life_reconciliation_status'])
            ->toBe('Recorded expiry is limiting')
        ->and($result['remaining_shelf_life_at_arrival_days'])
            ->toBeLessThan($result['reference_remaining_shelf_life_at_arrival_days'])
        ->and($result['safe_transit_window_hours'])
            ->toBeLessThanOrEqual($result['recorded_expiry_window_hours'])
        ->and($result['quality_at_arrival'])
            ->toBeLessThan($result['reference_quality_at_arrival']);
});

it('returns zero operational life after the recorded expiry threshold is passed', function () {
    $service = app(QualityPredictionService::class);
    $shipment = shipmentForQualityTest(1, '2026-08-28');
    $profile = cucumberProfileForQualityTest();

    $result = $service->predict($shipment, $profile, [
        'temperature' => 12,
    ]);

    expect($result['expiry_constraint_applied'])->toBeTrue()
        ->and($result['shelf_life_reconciliation_status'])
            ->toBe('Recorded expiry reached')
        ->and($result['remaining_shelf_life_at_arrival_days'])->toBe(0.0)
        ->and($result['safe_transit_window_hours'])->toBe(0.0)
        ->and($result['quality_at_arrival'])->toBe(0);
});

it('returns a safe fallback when the commodity profile is unknown', function () {
    $service = app(QualityPredictionService::class);
    $shipment = shipmentForQualityTest();

    $result = $service->predict($shipment, null, [
        'temperature' => 20,
    ]);

    expect($result['prediction_available'])->toBeFalse()
        ->and($result['baseline_shelf_life_days'])->toBeNull()
        ->and($result['reference_remaining_shelf_life_at_arrival_days'])->toBeNull()
        ->and($result['temperature_assessment']['status'])
            ->toBe('Unknown commodity profile');
});

it('uses one exact recorded window for freshness and expiry timing', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-29 20:40:00'));

    $service = app(QualityPredictionService::class);
    $shipment = shipmentForQualityTest(1.6, '2026-08-30');
    $shipment->harvest->harvest_date = '2026-08-29';
    $profile = cucumberProfileForQualityTest();

    $result = $service->predict($shipment, $profile);

    expect($result['recorded_remaining_hours'])->toBeGreaterThan(0)
        ->and($result['recorded_remaining_at_arrival_hours'])
            ->toBeLessThan($result['recorded_remaining_hours'])
        ->and($result['transit_margin_hours'])
            ->toBe(round(
                $result['safe_transit_window_hours'] - 1.6,
                1
            ))
        ->and($result['quality_at_arrival'])
            ->toBeLessThanOrEqual($result['reference_quality_at_arrival'])
        ->and($result['quality_at_arrival'])
            ->toBeLessThanOrEqual($result['recorded_freshness_index_at_arrival']);
});
