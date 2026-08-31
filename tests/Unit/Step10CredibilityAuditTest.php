<?php

namespace Tests\Unit;

use Tests\TestCase;

class Step10CredibilityAuditTest extends TestCase
{
    public function test_unsupported_carbon_and_sustainability_formulas_are_removed(): void
    {
        $decision = file_get_contents(app_path('Services/AI/DecisionEngine.php'));
        $shipment = file_get_contents(app_path('Http/Controllers/ShipmentController.php'));
        $routing = file_get_contents(app_path('Services/Routing/FreshnessAwareRouteService.php'));

        $this->assertStringNotContainsString('* 0.12', $decision);
        $this->assertStringNotContainsString('* 0.12', $shipment);
        $this->assertStringNotContainsString('* 0.12', $routing);
        $this->assertStringNotContainsString('1.15', $decision);
        $this->assertStringNotContainsString('1.50', $decision);
        $this->assertStringNotContainsString('($carbonKg / 120)', $decision);
        $this->assertStringContainsString('operational_readiness_score', $decision);
        $this->assertStringContainsString('FreightCarbonEstimateService', $decision);
        $this->assertStringNotContainsString('calculateSustainabilityScore', $decision);
        $this->assertStringNotContainsString('calculateCarbonImpactScore', $decision);
    }

    public function test_misleading_competition_wording_is_removed_from_core_views(): void
    {
        $paths = [
            resource_path('views/dashboard.blade.php'),
            resource_path('views/ai-analysis/index.blade.php'),
            resource_path('views/ai/optimizer.blade.php'),
            resource_path('views/auth/register.blade.php'),
            resource_path('views/shipments/create.blade.php'),
        ];

        $combined = collect($paths)
            ->map(fn (string $path) => file_get_contents($path))
            ->implode("\n");

        foreach ([
            'Predictive Sustainability Engine & Autonomous Logistics Risk Analytics',
            'AI Autonomous Navigation',
            'GIS LIVE',
            'Live Shipment Route',
            'spoilage prediction',
            'Status Real-time:',
            'Real-time risk distribution analysis',
            'Recorded Carbon',
        ] as $claim) {
            $this->assertStringNotContainsString($claim, $combined);
        }
    }
}
