<?php

namespace Tests\Unit\Services\DigitalTwin;

use App\Services\DigitalTwin\ScenarioComparisonService;
use PHPUnit\Framework\TestCase;

class ScenarioComparisonServiceTest extends TestCase
{
    public function test_safe_scenario_ranks_above_breach_and_can_improve_current_plan(): void
    {
        $service =
            new ScenarioComparisonService();

        $baseline = [
            'name' => 'Current Plan',
            'analysis' => [
                'risk_score' => 55,
                'quality_at_arrival' => 65,
            ],
            'route' => [
                'freshness_feasibility' => 'Safe',
                'transit_margin_hours' => 6,
            ],
            'carbon' => [
                'estimated_kg' => 7,
            ],
        ];

        $safe = [
            'name' => 'Safe Scenario',
            'analysis' => [
                'risk_score' => 40,
                'quality_at_arrival' => 70,
            ],
            'route' => [
                'freshness_feasibility' => 'Safe',
                'transit_margin_hours' => 8,
            ],
            'carbon' => [
                'estimated_kg' => 6,
            ],
        ];

        $breach = [
            'name' => 'Breach Scenario',
            'analysis' => [
                'risk_score' => 20,
                'quality_at_arrival' => 90,
            ],
            'route' => [
                'freshness_feasibility' => 'Breach',
                'transit_margin_hours' => -1,
            ],
            'carbon' => [
                'estimated_kg' => 4,
            ],
        ];

        $result =
            $service->compare(
                $baseline,
                [
                    $breach,
                    $safe,
                ]
            );

        $this->assertSame(
            'recommended_over_current',
            $result['decision_type']
        );

        $this->assertSame(
            'scenario',
            $result['preferred_option']
        );

        $this->assertSame(
            'Safe Scenario',
            $result[
                'recommended_scenario'
            ]['name']
        );
    }

    public function test_all_breach_scenarios_are_not_viable_and_current_plan_remains_preferred(): void
    {
        $service =
            new ScenarioComparisonService();

        $baseline = [
            'name' => 'Current Plan',
            'analysis' => [
                'risk_score' => 45,
                'quality_at_arrival' => 60,
            ],
            'route' => [
                'freshness_feasibility' => 'Safe',
                'transit_margin_hours' => 8,
            ],
            'carbon' => [
                'estimated_kg' => 6,
            ],
        ];

        $result =
            $service->compare(
                $baseline,
                [
                    [
                        'name' => 'A',
                        'analysis' => [
                            'risk_score' => 50,
                            'quality_at_arrival' => 40,
                        ],
                        'route' => [
                            'freshness_feasibility' =>
                                'Breach',
                            'transit_margin_hours' =>
                                -1,
                        ],
                        'carbon' => [
                            'estimated_kg' => 5,
                        ],
                    ],
                ]
            );

        $this->assertSame(
            'not_viable',
            $result['decision_type']
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

        $this->assertSame(
            'Not Viable',
            $result[
                'decision_status'
            ]
        );
    }

    public function test_delta_uses_points_and_signed_relative_change(): void
    {
        $service =
            new ScenarioComparisonService();

        $delta =
            $service->delta(
                [
                    'analysis' => [
                        'risk_score' => 58,
                        'quality_at_arrival' => 61,
                    ],
                    'route' => [
                        'transit_margin_hours' => 10,
                    ],
                    'carbon' => [
                        'estimated_kg' => 4.56,
                    ],
                ],
                [
                    'analysis' => [
                        'risk_score' => 34,
                        'quality_at_arrival' => 78,
                    ],
                    'route' => [
                        'transit_margin_hours' => 7,
                    ],
                    'carbon' => [
                        'estimated_kg' => 5.18,
                    ],
                ]
            );

        $this->assertSame(
            -24.0,
            $delta['risk_points']
        );

        $this->assertSame(
            17.0,
            $delta['quality_points']
        );

        $this->assertSame(
            -3.0,
            $delta[
                'transit_margin_hours'
            ]
        );

        $this->assertLessThan(
            0,
            $delta[
                'risk_relative_percent'
            ]
        );
    }
}