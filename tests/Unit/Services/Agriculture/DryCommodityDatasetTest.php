<?php

use Tests\TestCase;

uses(TestCase::class);

it('ships source-backed storage-stability profiles for common Indonesian dry commodities', function () {
    $payload = json_decode(
        file_get_contents(database_path('data/commodity_profiles.json')),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    $profiles = collect($payload['profiles'] ?? []);
    $dry = $profiles->where('quality_model_type', 'storage_stability');

    expect($dry->count())->toBeGreaterThanOrEqual(12);

    foreach ([
        'green-coffee',
        'milled-rice',
        'paddy-rice',
        'dried-maize',
        'soybeans',
        'cocoa-beans',
        'shelled-groundnuts',
        'common-dry-beans',
        'cowpeas',
        'sorghum-grain',
        'copra',
        'millet-grain',
    ] as $slug) {
        $profile = $dry->firstWhere('slug', $slug);

        expect($profile)->not->toBeNull()
            ->and($profile['source_url'] ?? null)->not->toBeNull()
            ->and($profile['safe_moisture_short_term_max_percent'] ?? null)->not->toBeNull()
            ->and($profile['safe_relative_humidity_max_percent'] ?? null)->not->toBeNull();
    }
});

it('keeps dry forms distinct from fresh forms', function () {
    $payload = json_decode(
        file_get_contents(database_path('data/commodity_profiles.json')),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    $profiles = collect($payload['profiles'] ?? []);

    expect($profiles->firstWhere('slug', 'sweet-corn')['quality_model_type'])->toBe('shelf_life_quality')
        ->and($profiles->firstWhere('slug', 'dried-maize')['quality_model_type'])->toBe('storage_stability')
        ->and($profiles->firstWhere('slug', 'coconut')['quality_model_type'])->toBe('shelf_life_quality')
        ->and($profiles->firstWhere('slug', 'copra')['quality_model_type'])->toBe('storage_stability');
});
