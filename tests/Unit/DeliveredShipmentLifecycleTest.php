<?php

namespace Tests\Unit;

use App\Models\Harvest;
use App\Models\Shipment;
use App\Services\AI\AnalysisSnapshotService;
use Tests\TestCase;

class DeliveredShipmentLifecycleTest extends TestCase
{
    public function test_delivered_is_terminal_and_not_operationally_active(): void
    {
        $active = new Shipment(['status' => 'In Transit']);
        $delivered = new Shipment(['status' => 'Delivered']);

        $this->assertTrue($active->isOperationallyActive());
        $this->assertFalse($active->isDelivered());
        $this->assertFalse($delivered->isOperationallyActive());
        $this->assertTrue($delivered->isDelivered());
    }

    public function test_snapshot_preserves_state_at_capture_without_recomputation(): void
    {
        $harvest = new Harvest([
            'commodity' => 'Brokoli',
            'weight' => 5400,
        ]);

        $shipment = new Shipment([
            'status' => 'In Transit',
            'origin' => 'Bogor',
            'destination' => 'Jakarta',
            'distance_km' => 58,
            'duration_hours' => 0.9,
            'recorded_temperature_c' => 0,
            'recorded_relative_humidity_percent' => 97,
        ]);
        $shipment->id = 99;
        $shipment->setRelation('harvest', $harvest);

        $analysis = [
            'risk_score' => 20,
            'risk_level' => 'Low',
            'operational_readiness_score' => 80,
        ];

        $routeDecision = [
            'route_score' => 93,
            'freshness_feasibility' => 'Safe',
        ];

        $snapshot = app(AnalysisSnapshotService::class)->build(
            $shipment,
            $analysis,
            $routeDecision,
            'test_snapshot'
        );

        // Mutate the in-memory shipment after capture. The snapshot must stay
        // unchanged because it is a historical value payload, not a live link.
        $shipment->status = 'Delivered';
        $shipment->recorded_temperature_c = 8;

        $this->assertSame('In Transit', $snapshot['shipment']['status_at_capture']);
        $this->assertSame(0.0, $snapshot['shipment']['recorded_temperature_c']);
        $this->assertSame(20, $snapshot['analysis']['risk_score']);
        $this->assertSame(93, $snapshot['route_decision']['route_score']);
    }

    public function test_active_and_completed_workflows_are_separated_in_source(): void
    {
        $shipmentController = file_get_contents(
            app_path('Http/Controllers/ShipmentController.php')
        );
        $analysisController = file_get_contents(
            app_path('Http/Controllers/AiAnalysisController.php')
        );
        $optimizerController = file_get_contents(
            app_path('Http/Controllers/AIOptimizerController.php')
        );
        $digitalTwinController = file_get_contents(
            app_path('Http/Controllers/OperationalDigitalTwinController.php')
        );
        $createView = file_get_contents(
            resource_path('views/shipments/create.blade.php')
        );
        $routes = file_get_contents(base_path('routes/web.php'));

        $this->assertStringContainsString('->operationallyActive()', $shipmentController);
        $this->assertStringContainsString('->operationallyActive()', $analysisController);
        $this->assertStringContainsString("if (\$shipment->isDelivered())", $analysisController);
        $this->assertStringContainsString("if (\$shipment->isDelivered())", $optimizerController);
        $this->assertStringContainsString("if (\$shipment->isDelivered())", $digitalTwinController);
        $this->assertStringNotContainsString(
            '<option value="Delivered">Delivered</option>',
            $createView
        );
        $this->assertStringContainsString('completed-shipments.index', $routes);
        $this->assertStringContainsString('completed-shipments.show', $routes);
    }

    public function test_history_detail_does_not_recompute_legacy_records(): void
    {
        $controller = file_get_contents(
            app_path('Http/Controllers/AiAnalysisController.php')
        );

        $showMethod = substr(
            $controller,
            strpos($controller, 'public function show($id)')
        );

        $this->assertStringContainsString('analysis_snapshot', $showMethod);
        $this->assertStringContainsString("view('ai-analysis.legacy-show'", $showMethod);
        $this->assertStringNotContainsString('$engine->analyze(', $showMethod);
    }
}
