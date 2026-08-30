<?php

namespace App\Http\Controllers;

use App\Models\AiAnalysis;
use App\Models\Shipment;
use App\Services\AI\DecisionEngine;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use App\Services\Routing\FreshnessAwareRouteService;

class AiAnalysisController extends Controller
{
    public function index()
    {
        $shipments = Shipment::with('harvest')->latest()->get();
        return view('ai-analysis.index', compact('shipments'));
    }

    public function bulkDestroy(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (is_array($ids) && !empty($ids)) {
            AiAnalysis::whereIn('id', $ids)->delete();
        }
        return redirect()->back()->with('success', 'Data terpilih berhasil dihapus.');
    }

    public function truncate()
    {
        AiAnalysis::query()->delete();
        return redirect()->back()->with('success', 'Seluruh riwayat analisis berhasil dikosongkan.');
    }

    /**
     * Core scores and recommended actions come from deterministic services.
     * The LLM is only an explanation layer and cannot replace those actions.
     */
    public function analyze(
    Shipment $shipment,
    DecisionEngine $engine,
    GeminiService $ai,
    FreshnessAwareRouteService $freshnessRoutes
)
 {
        $shipment->loadMissing('harvest');

        $analysis = $engine->analyze($shipment);
        $routeDecision =
    $freshnessRoutes
        ->assessCurrentRoute(
            $shipment,
            $analysis
        );
        $plan = $analysis['recommendation_plan'] ?? [];
        $actions = $analysis['recommended_actions'] ?? [];

        // LLM explanation only. The deterministic primary action remains authoritative.
        $explanation = $ai->generateShipmentExplanation([
            'commodity' => $shipment->harvest?->commodity ?? 'Unknown',
            'origin' => $shipment->origin,
            'destination' => $shipment->destination,
            'status' => $shipment->status,
            'remaining_days' => $analysis['remaining_days'],
            'distance' => $shipment->distance_km ?? 0,
            'risk_score' => $analysis['risk_score'],
            'priority_score' => $analysis['priority_score'],
            'sustainability_score' => $analysis['sustainability_score'],
            'recommended_action' => $analysis['recommended_action'],
            'recommendation_reason' => $analysis['recommendation_reason'],
        ]);

        $decisionReason = $explanation['decision_reason']
            ?? $analysis['recommendation_reason'];

        $expectedOutcome = $analysis['expected_outcome']
            ?? 'Continue monitoring the shipment against its current operational constraints.';

        $actionLines = collect($actions)
            ->map(fn (array $action) => '- ' . ($action['action'] ?? 'Review shipment'))
            ->implode("\n");

        if ($actionLines === '') {
            $actionLines = '- ' . $analysis['recommended_action'];
        }

        // Current result UI still consumes a text block for backward compatibility.
        $aiResult = "Recommendations:\n{$actionLines}\n\n"
            . "Explanation:\n{$decisionReason}\n\n"
            . "Conclusion:\n{$expectedOutcome}";

        // Persist all deterministic actions so new History Detail records retain
        // the complete recommendation plan without requiring a schema migration.
        $persistedRecommendation = "Recommendations:\n{$actionLines}\n\n"
            . "Explanation:\n{$analysis['recommendation_reason']}\n\n"
            . "Conclusion:\n{$expectedOutcome}";

        AiAnalysis::create([
            'shipment_id' => $shipment->id,
            'risk_level' => $analysis['risk_level'],
            // Existing DB column retained for compatibility; semantic is Risk Index.
            'waste_probability' => $analysis['risk_index'] . '/100',
            'sustainability_score' => $analysis['sustainability_score'],
            'recommendations' => $persistedRecommendation,
        ]);

        return redirect()
            ->route('ai-analysis.index')
            ->with('ai_result', $aiResult)
            ->with('shipment_data', [
                'commodity' => $shipment->harvest?->commodity ?? 'Unknown',
                'origin' => $shipment->origin,
                'destination' => $shipment->destination,
                'status' => $shipment->status,
                'distance' => $shipment->distance_km,
                'remaining_days' => $analysis['remaining_days'],
                'expiry_date' => $shipment->harvest?->expiry_date,
                'duration' => $shipment->duration_hours,
                'carbon_emission' => $analysis['carbon_kg'],
                'route_score' => $shipment->route_score,
                'origin_lat' => $shipment->origin_lat ?? 0,
                'origin_lng' => $shipment->origin_lng ?? 0,
                'destination_lat' => $shipment->destination_lat ?? 0,
                'destination_lng' => $shipment->destination_lng ?? 0,
                'route_geometry' => $shipment->route_geometry,
                'data_confidence' => $analysis['data_confidence'],
            ])
            ->with('risk_level', $analysis['risk_level'])
            ->with('waste_probability', $analysis['risk_index'] . '/100')
            ->with('sustainability_score', $analysis['sustainability_score'])
            ->with('prediction_data', $analysis['prediction_data'])
            ->with('explainability', $analysis['explainability'])
            ->with('priority_score', $analysis['priority_score'])
            ->with(
                'total_impact',
                collect($analysis['explainability'])->sum('impact')
            )
            // Step 3 freshness intelligence
            ->with('quality_prediction', $analysis['quality_prediction'] ?? [])
            ->with('quality_at_departure', $analysis['quality_at_departure'] ?? null)
            ->with('quality_at_arrival', $analysis['quality_at_arrival'] ?? null)
            ->with('quality_status', $analysis['quality_status'] ?? null)
            ->with('quality_loss_during_transit', $analysis['quality_loss_during_transit'] ?? null)
            ->with('predicted_remaining_shelf_life_days', $analysis['predicted_remaining_shelf_life_days'] ?? null)
            ->with('safe_transit_window_hours', $analysis['safe_transit_window_hours'] ?? null)
            ->with('safe_transit_status', $analysis['safe_transit_status'] ?? null)
            ->with('temperature_assessment', $analysis['temperature_assessment'] ?? [])
            ->with('data_confidence', $analysis['data_confidence'] ?? 0)
            // Step 4 risk intelligence
            ->with('risk_assessment', $analysis['risk_assessment'] ?? [])
            ->with('risk_severity', $analysis['risk_severity'] ?? null)
            ->with('urgency_level', $analysis['urgency_level'] ?? null)
            ->with('intervention_required', $analysis['intervention_required'] ?? false)
            ->with('intervention_reason', $analysis['intervention_reason'] ?? null)
            ->with('dispatch_deadline', $analysis['dispatch_deadline'] ?? null)
            // Step 4.2 deterministic recommendation plan
            ->with('recommendation_plan', $plan)
            ->with('recommended_actions', $actions)
            ->with('recommended_action', $analysis['recommended_action'])
            ->with('recommendation_reason', $analysis['recommendation_reason'])
            ->with('expected_outcome', $expectedOutcome)
            ->with(
    'route_decision',
    $routeDecision
);;
    }

    public function history()
    {
        $analyses = AiAnalysis::with('shipment.harvest')
            ->latest()
            ->get();

        return view('ai-analysis.history', compact('analyses'));
    }

    public function destroy($id)
    {
        $analysis = AiAnalysis::findOrFail($id);
        $analysis->delete();

        return redirect()
            ->route('ai-analysis.history')
            ->with('success', 'Data berhasil dihapus!');
    }

public function show(
    $id,
    DecisionEngine $engine,
    FreshnessAwareRouteService $freshnessRoutes
) {
    $analysis = AiAnalysis::with([
        'shipment.harvest',
        'shipment.aiAnalyses',
    ])->findOrFail($id);

    $shipment = $analysis->shipment;

    abort_if(
        !$shipment,
        404,
        'Shipment not found for this analysis.'
    );

    $decisionAnalysis =
        $engine->analyze(
            $shipment
        );

    $routeDecision =
        $freshnessRoutes
            ->assessCurrentRoute(
                $shipment,
                $decisionAnalysis
            );

    return view(
        'ai-analysis.show',
        [
            'shipment' => $shipment,
            'analysisRecord' => $analysis,
            'decisionAnalysis' => $decisionAnalysis,
            'routeDecision' => $routeDecision,
        ]
    );
}
}
