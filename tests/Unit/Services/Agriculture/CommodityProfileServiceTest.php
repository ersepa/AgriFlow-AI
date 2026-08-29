<?php

use App\Models\CommodityProfile;
use App\Services\Agriculture\CommodityProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('matches a commodity using its Indonesian alias', function () {
    CommodityProfile::create([
        'name' => 'Cucumber',
        'local_name' => 'Timun',
        'slug' => 'cucumber',
        'category' => 'Vegetable',
        'optimal_temp_min' => 10,
        'optimal_temp_max' => 12.5,
        'perishability_level' => 'High',
        'temperature_control_recommended' => true,
        'aliases' => ['timun', 'mentimun'],
        'source_name' => 'Test source',
        'source_url' => 'https://example.com',
    ]);

    $profile = app(CommodityProfileService::class)->findForCommodity('TIMUN');

    expect($profile)->not->toBeNull()
        ->and($profile->name)->toBe('Cucumber');
});

it('flags cucumber temperature below its chilling threshold', function () {
    $profile = CommodityProfile::create([
        'name' => 'Cucumber',
        'local_name' => 'Timun',
        'slug' => 'cucumber',
        'category' => 'Vegetable',
        'optimal_temp_min' => 10,
        'optimal_temp_max' => 12.5,
        'chilling_threshold_c' => 10,
        'perishability_level' => 'High',
        'temperature_control_recommended' => true,
        'source_name' => 'Test source',
        'source_url' => 'https://example.com',
    ]);

    $assessment = app(CommodityProfileService::class)
        ->assessTemperature($profile, 5, 8);

    expect($assessment['status'])->toBe('Chilling risk')
        ->and($assessment['risk_modifier'])->toBeGreaterThan(0);
});

it('does not invent a temperature recommendation for an unknown commodity', function () {
    $service = app(CommodityProfileService::class);

    $profile = $service->findForCommodity('Dragon Vegetable X');
    $assessment = $service->assessTemperature($profile, 8, 5);

    expect($profile)->toBeNull()
        ->and($assessment['status'])->toBe('Unknown commodity profile')
        ->and($assessment['risk_modifier'])->toBe(0);
});
