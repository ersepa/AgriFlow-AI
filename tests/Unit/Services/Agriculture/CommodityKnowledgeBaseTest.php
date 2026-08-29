<?php

use App\Models\CommodityProfile;
use App\Services\Agriculture\CommodityProfileService;
use Database\Seeders\CommodityProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CommodityProfileSeeder::class);
});

it('imports a competition-ready commodity knowledge base', function () {
    expect(CommodityProfile::count())->toBeGreaterThanOrEqual(40);
});

it('keeps provenance for every commodity profile', function () {
    $invalid = CommodityProfile::query()
        ->whereNull('source_name')
        ->orWhereNull('source_url')
        ->count();

    expect($invalid)->toBe(0);
});

it('maps Indonesian bayam to amaranth instead of spinach', function () {
    $profile = app(CommodityProfileService::class)
        ->findForCommodity('Bayam');

    expect($profile)->not->toBeNull()
        ->and($profile->name)->toBe('Amaranth');
});

it('keeps spinach as a separate commodity identity', function () {
    $profile = app(CommodityProfileService::class)
        ->findForCommodity('spinach');

    expect($profile)->not->toBeNull()
        ->and($profile->name)->toBe('Spinach');
});

it('matches common Indonesian aliases', function () {
    $service = app(CommodityProfileService::class);

    expect($service->findForCommodity('MENTIMUN')?->name)->toBe('Cucumber')
        ->and($service->findForCommodity('cabe')?->name)->toBe('Chile Pepper')
        ->and($service->findForCommodity('buah naga')?->name)->toBe('Pitaya')
        ->and($service->findForCommodity('ubi kayu')?->name)->toBe('Cassava');
});

it('flags cucumber temperature below its chilling threshold', function () {
    $service = app(CommodityProfileService::class);
    $profile = $service->findForCommodity('Timun');

    $assessment = $service->assessTemperature(
        $profile,
        5,
        8
    );

    expect($assessment['status'])->toBe('Chilling risk')
        ->and($assessment['risk_modifier'])->toBeGreaterThan(0);
});

it('does not invent storage parameters for an unknown commodity', function () {
    $service = app(CommodityProfileService::class);

    $profile = $service->findForCommodity('Komoditas Eksperimental XYZ');
    $summary = $service->summary($profile);
    $assessment = $service->assessTemperature($profile, 8, 5);

    expect($profile)->toBeNull()
        ->and($summary['found'])->toBeFalse()
        ->and($summary['optimal_temp_min'])->toBeNull()
        ->and($assessment['status'])->toBe('Unknown commodity profile')
        ->and($assessment['risk_modifier'])->toBe(0);
});

it('has no normalized alias collision across profiles', function () {
    $normalize = static function (?string $value): string {
        if ($value === null) {
            return '';
        }

        return (string) \Illuminate\Support\Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish();
    };

    $seen = [];
    $collisions = [];

    CommodityProfile::all()->each(function ($profile) use (&$seen, &$collisions, $normalize) {
        $candidates = array_filter([
            $profile->name,
            $profile->local_name,
            ...($profile->aliases ?? []),
        ]);

        foreach ($candidates as $candidate) {
            $key = $normalize((string) $candidate);

            if ($key === '') {
                continue;
            }

            if (isset($seen[$key]) && $seen[$key] !== $profile->slug) {
                $collisions[] = [
                    'alias' => $key,
                    'first' => $seen[$key],
                    'second' => $profile->slug,
                ];
            }

            $seen[$key] = $profile->slug;
        }
    });

    expect($collisions)->toBe([]);
});
