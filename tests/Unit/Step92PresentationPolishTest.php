<?php

namespace Tests\Unit;

use Tests\TestCase;

class Step92PresentationPolishTest extends TestCase
{
    public function test_dry_commodity_wording_and_history_parser_are_present(): void
    {
        $shipmentView = file_get_contents(
            resource_path('views/shipments/show.blade.php')
        );

        $this->assertStringContainsString(
            'Storage-Stability Intelligence',
            $shipmentView
        );
        $this->assertStringContainsString(
            'Quality score not applicable',
            $shipmentView
        );
        $this->assertStringContainsString(
            'Historical snapshots preserve the assessment generated at that time.',
            $shipmentView
        );
        $this->assertStringNotContainsString(
            "explode('-',",
            $shipmentView
        );
    }

    public function test_condition_reassessment_snapshots_use_structured_sections(): void
    {
        $controller = file_get_contents(
            app_path('Http/Controllers/ShipmentController.php')
        );

        $this->assertStringContainsString(
            'Recommendations:\\n',
            $controller
        );
        $this->assertStringContainsString(
            'Explanation:\\n',
            $controller
        );
        $this->assertStringContainsString(
            'Conclusion:\\n',
            $controller
        );
    }

    public function test_internal_step_numbers_are_not_rendered_in_core_result_labels(): void
    {
        $files = [
            resource_path('views/ai-analysis/partials/freshness-result.blade.php'),
            resource_path('views/ai-analysis/partials/risk-engine-result.blade.php'),
            resource_path('views/ai-analysis/partials/intervention-recommendations.blade.php'),
            resource_path('views/shared/step43-risk-summary.blade.php'),
            resource_path('views/shared/step43-intervention-plan.blade.php'),
        ];

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            $this->assertStringNotContainsString('· Step 3.2', $contents);
            $this->assertStringNotContainsString('· Step 4', $contents);
            $this->assertStringNotContainsString('· Step 4.3', $contents);
        }
    }
}
