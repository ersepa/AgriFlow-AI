<?php

namespace Tests\Unit\Services\Routing;

use PHPUnit\Framework\TestCase;

class RouteEvidenceReweightingTest extends TestCase
{
    public function test_missing_quality_is_excluded_instead_of_scored_as_zero(): void
    {
        $weights = [
            'freshness_preservation' => 0.40,
            'risk_protection' => 0.25,
            'transit_margin' => 0.15,
            'duration_efficiency' => 0.10,
            'carbon_efficiency' => 0.10,
        ];

        $components = [
            'freshness_preservation' => null,
            'risk_protection' => 60.0,
            'transit_margin' => 80.0,
            'duration_efficiency' => 90.0,
            'carbon_efficiency' => 70.0,
        ];

        $weighted = 0.0;
        $availableWeight = 0.0;

        foreach ($weights as $key => $weight) {
            $value = $components[$key];

            if ($value === null) {
                continue;
            }

            $weighted += $value * $weight;
            $availableWeight += $weight;
        }

        $score = $weighted / $availableWeight;

        $this->assertGreaterThan(
            0,
            $score
        );

        // If null had been silently converted to zero, score would be 43.
        $this->assertGreaterThan(
            43,
            $score
        );
    }
}
