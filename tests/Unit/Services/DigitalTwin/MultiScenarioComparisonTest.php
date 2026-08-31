<?php

namespace Tests\Unit\Services\DigitalTwin;

use App\Services\DigitalTwin\ScenarioComparisonService;
use PHPUnit\Framework\TestCase;

class MultiScenarioComparisonTest extends TestCase
{
    public function test_best_improving_scenario_wins_over_worse_and_equivalent_options(): void
    {
        $service =
            new ScenarioComparisonService();

        $baseline =
            $this->snapshot(
                'Current Plan',
                32,
                'Safe',
                null,
                170,
                64.04
            );

        $good =
            $this->snapshot(
                'Scenario A',
                21,
                'Safe',
                null,
                170,
                64.04
            );

        $bad =
            $this->snapshot(
                'Scenario B',
                47,
                'Safe',
                null,
                170,
                64.04
            );

        $same =
            $this->snapshot(
                'Scenario C',
                32,
                'Safe',
                null,
                170,
                64.04
            );

        $result =
            $service->compare(
                $baseline,
                [
                    $bad,
                    $same,
                    $good,
                ]
            );

        $this->assertSame(
            'recommended_over_current',
            $result['decision_type']
        );

        $this->assertSame(
            'Scenario A',
            $result[
                'recommended_scenario'
            ]['name']
        );

        $this->assertSame(
            'scenario',
            $result['preferred_option']
        );
    }

    public function test_current_plan_remains_preferred_when_all_scenarios_are_worse(): void
    {
        $service =
            new ScenarioComparisonService();

        $baseline =
            $this->snapshot(
                'Current Plan',
                32,
                'Safe',
                null,
                170,
                64.04
            );

        $worseA =
            $this->snapshot(
                'Scenario A',
                47,
                'Safe',
                null,
                170,
                64.04
            );

        $worseB =
            $this->snapshot(
                'Scenario B',
                60,
                'Tight',
                null,
                4,
                64.04
            );

        $result =
            $service->compare(
                $baseline,
                [
                    $worseA,
                    $worseB,
                ]
            );

        $this->assertSame(
            'current_plan',
            $result['preferred_option']
        );

        $this->assertNull(
            $result[
                'recommended_scenario'
            ]
        );
    }

    public function test_decision_table_keeps_each_scenario_separate(): void
    {
        $service =
            new ScenarioComparisonService();

        $baseline =
            $this->snapshot(
                'Current Plan',
                55,
                'Safe',
                69,
                56.8,
                19.42
            );

        $a =
            $this->snapshot(
                'Scenario A',
                55,
                'Safe',
                69,
                56.8,
                19.42
            );

        $b =
            $this->snapshot(
                'Scenario B',
                67,
                'Safe',
                68,
                54.8,
                19.42
            );

        $table =
            $service->decisionTable(
                $baseline,
                [$a, $b]
            );

        $this->assertCount(
            2,
            $table
        );

        $this->assertSame(
            'Scenario A',
            $table[0]['name']
        );

        $this->assertSame(
            'Scenario B',
            $table[1]['name']
        );
    }

    private function snapshot(
        string $name,
        int|float $risk,
        string $feasibility,
        int|float|null $quality,
        int|float $margin,
        int|float $carbon
    ): array {
        return [
            'name' => $name,
            'analysis' => [
                'risk_score' => $risk,
                'quality_at_arrival' =>
                    $quality,
            ],
            'route' => [
                'freshness_feasibility' =>
                    $feasibility,
                'transit_margin_hours' =>
                    $margin,
            ],
            'carbon' => [
                'estimated_kg' =>
                    $carbon,
            ],
            'evidence' => [
                'percent' => 100,
            ],
        ];
    }
}
