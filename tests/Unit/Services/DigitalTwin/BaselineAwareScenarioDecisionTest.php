<?php

namespace Tests\Unit\Services\DigitalTwin;

use App\Services\DigitalTwin\ScenarioComparisonService;
use PHPUnit\Framework\TestCase;

class BaselineAwareScenarioDecisionTest extends TestCase
{
    public function test_safe_scenario_with_lower_risk_is_recommended_over_current(): void
    {
        $service =
            new ScenarioComparisonService();

        $baseline =
            $this->snapshot(
                risk: 32,
                feasibility: 'Safe',
                quality: null,
                margin: 170,
                carbon: 64.04
            );

        $scenario =
            $this->snapshot(
                risk: 21,
                feasibility: 'Safe',
                quality: null,
                margin: 170,
                carbon: 64.04,
                name: 'Good Storage'
            );

        $result =
            $service->compare(
                $baseline,
                [$scenario]
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
            'Recommended over Current Plan',
            $result['decision_status']
        );
    }

    public function test_safe_scenario_with_higher_risk_is_not_recommended(): void
    {
        $service =
            new ScenarioComparisonService();

        $baseline =
            $this->snapshot(
                risk: 32,
                feasibility: 'Safe',
                quality: null,
                margin: 170,
                carbon: 64.04
            );

        $scenario =
            $this->snapshot(
                risk: 47,
                feasibility: 'Safe',
                quality: null,
                margin: 170,
                carbon: 64.04,
                name: 'Bad Storage'
            );

        $result =
            $service->compare(
                $baseline,
                [$scenario]
            );

        $this->assertSame(
            'viable_but_not_recommended',
            $result['decision_type']
        );

        $this->assertSame(
            'current_plan',
            $result['preferred_option']
        );

        $this->assertSame(
            'Viable but Not Recommended',
            $result['decision_status']
        );

        $this->assertStringContainsString(
            '32/100 to 47/100',
            $result['decision_reason']
        );
    }

    public function test_identical_scenario_is_only_a_viable_alternative(): void
    {
        $service =
            new ScenarioComparisonService();

        $baseline =
            $this->snapshot(
                risk: 55,
                feasibility: 'Safe',
                quality: 69,
                margin: 56.8,
                carbon: 19.42
            );

        $scenario =
            $this->snapshot(
                risk: 55,
                feasibility: 'Safe',
                quality: 69,
                margin: 56.8,
                carbon: 19.42,
                name: 'Same Plan'
            );

        $result =
            $service->compare(
                $baseline,
                [$scenario]
            );

        $this->assertSame(
            'equivalent_alternative',
            $result['decision_type']
        );

        $this->assertSame(
            'Viable Alternative',
            $result['decision_status']
        );

        $this->assertSame(
            'current_plan',
            $result['preferred_option']
        );
    }

    public function test_breach_is_never_recommended_when_current_plan_is_safe(): void
    {
        $service =
            new ScenarioComparisonService();

        $baseline =
            $this->snapshot(
                risk: 40,
                feasibility: 'Safe',
                quality: 70,
                margin: 8,
                carbon: 6
            );

        $scenario =
            $this->snapshot(
                risk: 20,
                feasibility: 'Breach',
                quality: 90,
                margin: -1,
                carbon: 4,
                name: 'Low Risk But Breach'
            );

        $result =
            $service->compare(
                $baseline,
                [$scenario]
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
            $result['recommended_scenario']
        );
    }

    public function test_tight_scenario_is_conditional_when_current_plan_is_safe(): void
    {
        $service =
            new ScenarioComparisonService();

        $baseline =
            $this->snapshot(
                risk: 55,
                feasibility: 'Safe',
                quality: 69,
                margin: 10,
                carbon: 10
            );

        $scenario =
            $this->snapshot(
                risk: 50,
                feasibility: 'Tight',
                quality: 70,
                margin: 3,
                carbon: 9,
                name: 'Tight Option'
            );

        $result =
            $service->compare(
                $baseline,
                [$scenario]
            );

        $this->assertSame(
            'conditional',
            $result['decision_type']
        );

        $this->assertSame(
            'current_plan',
            $result['preferred_option']
        );
    }

    private function snapshot(
        int|float $risk,
        string $feasibility,
        int|float|null $quality,
        int|float $margin,
        int|float $carbon,
        string $name = 'Current Plan'
    ): array {
        return [
            'name' => $name,
            'analysis' => [
                'risk_score' => $risk,
                'quality_at_arrival' => $quality,
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
        ];
    }
}
