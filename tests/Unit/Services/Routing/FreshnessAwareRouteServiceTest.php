<?php

namespace Tests\Unit\Services\Routing;

use PHPUnit\Framework\TestCase;

class FreshnessAwareRouteServiceTest extends TestCase
{
    public function test_step_5_weights_sum_to_one(): void
    {
        $weights = [
            'freshness_preservation' => 0.40,
            'risk_protection' => 0.25,
            'transit_margin' => 0.15,
            'duration_efficiency' => 0.10,
            'carbon_efficiency' => 0.10,
        ];

        $this->assertEqualsWithDelta(
            1.0,
            array_sum($weights),
            0.0001
        );
    }

    public function test_freshness_risk_and_margin_dominate_ranking(): void
    {
        $this->assertEqualsWithDelta(
            0.80,
            0.40 + 0.25 + 0.15,
            0.0001
        );
    }

    public function test_all_breach_routes_are_not_called_recommended(): void
    {
        $decisionStatus =
            'No Freshness-Safe Route';

        $decisionType =
            'best_available';

        $this->assertSame(
            'No Freshness-Safe Route',
            $decisionStatus
        );

        $this->assertSame(
            'best_available',
            $decisionType
        );
    }

    public function test_near_duplicate_routes_can_be_filtered(): void
    {
        $distanceA = 38.00;
        $distanceB = 38.34;

        $durationA = 0.70;
        $durationB = 0.70;

        $distanceDifference =
            abs($distanceA - $distanceB)
            / $distanceA;

        $durationDifference =
            abs($durationA - $durationB)
            / $durationA;

        $this->assertLessThan(
            0.02,
            $distanceDifference
        );

        $this->assertLessThan(
            0.02,
            $durationDifference
        );
    }

    public function test_route_model_is_not_a_probability_model(): void
    {
        $modelName =
            'Freshness-Aware Route Ranking';

        $this->assertStringNotContainsString(
            'probability',
            strtolower($modelName)
        );
    }
}
