<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ThreeLevelOperationalRiskContractTest extends TestCase
{
    public function test_operational_risk_exposes_exactly_three_user_facing_bands(): void
    {
        $root = dirname(__DIR__, 2);

        $riskService = file_get_contents(
            $root . '/app/Services/AI/OperationalRiskService.php'
        );

        $this->assertIsString($riskService);
        $this->assertStringContainsString("\$score >= 60 => 'High'", $riskService);
        $this->assertStringContainsString("\$score >= 30 => 'Moderate'", $riskService);
        $this->assertStringNotContainsString("\$score >= 80 => 'Critical'", $riskService);
    }

    public function test_dashboard_uses_current_active_three_level_risk_state(): void
    {
        $root = dirname(__DIR__, 2);

        $routeSource = file_get_contents($root . '/routes/web.php');
        $dashboardSource = file_get_contents(
            $root . '/resources/views/dashboard.blade.php'
        );

        $this->assertIsString($routeSource);
        $this->assertIsString($dashboardSource);

        $this->assertStringContainsString(
            "->where('risk_severity', 'High')",
            $routeSource
        );
        $this->assertStringContainsString(
            "->where('risk_severity', 'Moderate')",
            $routeSource
        );
        $this->assertStringContainsString(
            "->where('risk_severity', 'Low')",
            $routeSource
        );

$this->assertStringContainsString(
    'Sebaran Risiko Aktif',
    $dashboardSource
);
$this->assertStringContainsString(
    '{{ $highRisk }} Risiko Tinggi',
    $dashboardSource
);
        $this->assertStringNotContainsString(
            'Active Critical',
            $dashboardSource
        );
        $this->assertStringNotContainsString(
            'Critical Shipments',
            $dashboardSource
        );
    }

    public function test_immediate_urgency_remains_separate_from_high_risk_band(): void
    {
        $root = dirname(__DIR__, 2);

        $riskService = file_get_contents(
            $root . '/app/Services/AI/OperationalRiskService.php'
        );
        $recommendationService = file_get_contents(
            $root . '/app/Services/AI/InterventionRecommendationService.php'
        );

        $this->assertStringContainsString(
            "'level' => 'Immediate'",
            $riskService
        );
        $this->assertStringContainsString(
            "if (\$urgency === 'Immediate')",
            $recommendationService
        );
$this->assertStringContainsString(
    'Risiko operasional tinggi yang disertai keterbatasan kesegaran atau waktu tempuh',
    $recommendationService
);
    }
}
