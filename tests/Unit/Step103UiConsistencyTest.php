<?php

namespace Tests\Unit;

use Tests\TestCase;

class Step103UiConsistencyTest extends TestCase
{
    public function test_user_facing_labels_are_consistent_and_current(): void
    {
        $views = [
            resource_path('views/dashboard.blade.php'),
            resource_path('views/harvests/index.blade.php'),
            resource_path('views/harvests/create.blade.php'),
            resource_path('views/shipments/create.blade.php'),
            resource_path('views/shipments/show.blade.php'),
            resource_path('views/ai-analysis/index.blade.php'),
            resource_path('views/ai-analysis/show.blade.php'),
            resource_path('views/ai-analysis/history.blade.php'),
            resource_path('views/ai/optimizer.blade.php'),
            resource_path('views/welcome.blade.php'),
        ];

        $combined = collect($views)
            ->map(fn (string $path) => file_get_contents($path))
            ->implode("\n");

        foreach ([
            'Log, track, and manage your field agricultural yields in real time.',
            'Condition & Cold-Chain Evidence',
            'Yield & Freshness Engine',
            'Penimbangan Presisi:',
            'Predictive Analysis Output',
            'Shipment Telemetry Summary',
            'kg CO₂ee',
            'AI Supply Chain Status',
            'Optimal Performance',
            'Active Sync',
            'Route Health Score',
            'AI Executive Summary',
            'AI Verdict',
            'Waste Prob.',
            'current live analysis is authoritative',
            'Freshness Feasibility',
            'AgriFlow does not claim live traffic, does not fabricate route alternatives, and will explicitly report when no freshness-safe route exists.',
            'Evaluating shipment telemetry...',
            'Failed to load route telemetry',
            'Freshness route optimization failed',
            'Traceability Penuh',
            'Bergabunglah dengan ratusan petani modern',
        ] as $legacyText) {
            $this->assertStringNotContainsString($legacyText, $combined);
        }

        foreach ([
            'Average Operational Readiness',
            'Current Snapshot',
            'Recorded Condition Evidence',
            'Decision Analysis Output',
            'Shipment Data Summary',
            'Stored Decision Score*',
            'Operational Risk Index*',
            'Route Feasibility',
            'Route Candidates',
            'Recorded Origin Context',
        ] as $currentText) {
            $this->assertStringContainsString($currentText, $combined);
        }
    }

    public function test_risk_contribution_bars_use_valid_percent_widths_and_grounded_labels(): void
    {
        $analysis = file_get_contents(resource_path('views/ai-analysis/index.blade.php'));

        $this->assertStringNotContainsString(
            'style="width: {{ number_format($driver[\'impact\'], 1) }} pts;"',
            $analysis
        );

        $this->assertStringContainsString(
            'style="width: {{ $impactBarWidth }}%;"',
            $analysis
        );

        $this->assertStringContainsString("\$label = 'Minimal';", $analysis);
        $this->assertStringContainsString("\$label = 'Low';", $analysis);
        $this->assertStringContainsString("\$label = 'Moderate';", $analysis);
    }
}
