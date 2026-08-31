<?php

use App\Models\CommodityProfile;
use App\Models\Shipment;
use App\Services\AI\OperationalRiskService;

function step4Shipment(
    string $status = 'Packed',
    float $distance = 123,
    float $duration = 1.6
): Shipment {
    return new Shipment([
        'origin' => 'Bogor',
        'destination' => 'Bandung',
        'status' => $status,
        'distance_km' => $distance,
        'duration_hours' => $duration,
    ]);
}

function step4Profile(string $perishability = 'High'): CommodityProfile
{
    return new CommodityProfile([
        'name' => 'Spinach',
        'local_name' => 'Spinach',
        'perishability_level' => $perishability,
    ]);
}

function step4Quality(array $overrides = []): array
{
    return array_merge([
        'quality_at_arrival' => 57,
        'remaining_shelf_life_at_arrival_days' => 1.04,
        'transit_margin_hours' => 24.9,
        'safe_transit_status' => 'Within estimated safe window',
        'expiry_constraint_applied' => true,
        'shelf_life_reconciliation_status' => 'Recorded expiry is limiting',
        'recorded_remaining_at_arrival_hours' => 24.9,
    ], $overrides);
}

it('makes freshness signals dominate the operational risk score', function () {
    $service = app(OperationalRiskService::class);

    $result = $service->assess(
        step4Shipment(),
        step4Profile(),
        step4Quality(),
        [
            'status' => 'Not provided',
            'severity' => 'unknown',
            'message' => 'No scenario temperature was provided.',
        ]
    );

    expect($result['risk_score'])->toBeGreaterThanOrEqual(60)
        ->and($result['risk_level'])->toBe('High')
        ->and($result['risk_severity'])->toBe('High')
        ->and($result['urgency_level'])->toBe('High')
        ->and($result['intervention_required'])->toBeTrue();
});

it('returns low risk for strong freshness with large transit margin', function () {
    $service = app(OperationalRiskService::class);

    $result = $service->assess(
        step4Shipment('Packed', 80, 1),
        step4Profile('Low'),
        step4Quality([
            'quality_at_arrival' => 94,
            'remaining_shelf_life_at_arrival_days' => 18,
            'transit_margin_hours' => 120,
            'expiry_constraint_applied' => false,
            'recorded_remaining_at_arrival_hours' => 432,
        ]),
        [
            'status' => 'Optimal',
            'severity' => 'low',
            'message' => 'Temperature is inside the commodity optimum range.',
        ]
    );

    expect($result['risk_score'])->toBeLessThan(30)
        ->and($result['risk_level'])->toBe('Low')
        ->and($result['risk_severity'])->toBe('Low')
        ->and($result['intervention_required'])->toBeFalse();
});

it('forces critical operational risk when ETA exceeds the safe transit window', function () {
    $service = app(OperationalRiskService::class);

    $result = $service->assess(
        step4Shipment(),
        step4Profile(),
        step4Quality([
            'safe_transit_status' => 'ETA exceeds safe transit window',
            'transit_margin_hours' => -2,
        ]),
        [
            'status' => 'Not provided',
            'severity' => 'unknown',
        ]
    );

    expect($result['risk_score'])->toBeGreaterThanOrEqual(88)
        ->and($result['risk_severity'])->toBe('Critical')
        ->and($result['critical_override_applied'])->toBeTrue()
        ->and($result['urgency_level'])->toBe('Immediate');
});

it('keeps the score explicitly non-probabilistic and exposes weighted components', function () {
    $service = app(OperationalRiskService::class);

    $result = $service->assess(
        step4Shipment(),
        step4Profile(),
        step4Quality(),
        [
            'status' => 'Not provided',
            'severity' => 'unknown',
        ]
    );

    expect($result['model_type'])->toBe('deterministic_weighted_risk_model')
        ->and($result['components'])->toHaveCount(7)
        ->and(array_sum(array_column($result['components'], 'weight')))->toBe(100)
        ->and($result['limitations'][0])->toContain('not a spoilage probability');
});

it('treats moderate risk as monitoring rather than mandatory intervention', function () {
    $service = app(OperationalRiskService::class);

    $result = $service->assess(
        step4Shipment('Packed', 162, 2.1),
        step4Profile('High'),
        step4Quality([
            'quality_at_arrival' => 83,
            'remaining_shelf_life_at_arrival_days' => 4.0,
            'transit_margin_hours' => 74.6,
            'recorded_remaining_at_arrival_hours' => 95.9,
        ]),
        [
            'status' => 'Not provided',
            'severity' => 'unknown',
            'message' => 'No scenario temperature was provided.',
        ]
    );

expect($result['risk_severity'])->toBe('Moderate')
    ->and($result['urgency_level'])->toBe('Elevated')
    ->and($result['intervention_required'])->toBeFalse()
    ->and($result['dispatch_deadline'])->toBe('Within 24 hours');
});
