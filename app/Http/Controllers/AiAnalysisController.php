<?php

namespace App\Http\Controllers;

use App\Models\AiAnalysis;
use App\Models\Shipment;
use App\Services\AI\DecisionEngine;
use App\Services\GeminiService;
use Illuminate\Http\Request;

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
     * Controllers no longer calculate risk themselves.
     * All numbers come from DecisionEngine.
     */
    public function analyze(
        Shipment $shipment,
        DecisionEngine $engine,
        GeminiService $ai
    ) {
        $shipment->loadMissing('harvest');

        $analysis = $engine->analyze($shipment);

        // LLM explains deterministic engine output; it does not generate the score.
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

        $recommendation = $explanation['recommendation'] ?? $analysis['recommended_action'];
        $decisionReason = $explanation['decision_reason'] ?? $analysis['recommendation_reason'];
        $conclusion = $explanation['conclusion'] ?? $analysis['recommendation_reason'];

        // Keep a text block because the current redesigned Blade parses/display this value.
        $aiResult = "Recommendations:\n- {$recommendation}\n\n"
            . "Explanation:\n{$decisionReason}\n\n"
            . "Conclusion:\n{$conclusion}";

        AiAnalysis::create([
            'shipment_id' => $shipment->id,
            'risk_level' => $analysis['risk_level'],
            // Existing DB column retained for compatibility; semantic is Risk Index in Step 1.
            'waste_probability' => $analysis['risk_index'] . '/100',
            'sustainability_score' => $analysis['sustainability_score'],
            'recommendations' => $recommendation . ' — ' . $decisionReason,
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
            ->with('total_impact', collect($analysis['explainability'])->sum('impact'))
            ->with(
    'quality_prediction',
    $analysis['quality_prediction'] ?? []
)
->with(
    'quality_at_departure',
    $analysis['quality_at_departure'] ?? null
)
->with(
    'quality_at_arrival',
    $analysis['quality_at_arrival'] ?? null
)
->with(
    'quality_status',
    $analysis['quality_status'] ?? null
)
->with(
    'quality_loss_during_transit',
    $analysis['quality_loss_during_transit'] ?? null
)
->with(
    'predicted_remaining_shelf_life_days',
    $analysis[
        'predicted_remaining_shelf_life_days'
    ] ?? null
)
->with(
    'safe_transit_window_hours',
    $analysis[
        'safe_transit_window_hours'
    ] ?? null
)
->with(
    'safe_transit_status',
    $analysis['safe_transit_status'] ?? null
)
->with(
    'temperature_assessment',
    $analysis['temperature_assessment'] ?? []
)
->with(
    'data_confidence',
    $analysis['data_confidence'] ?? 0
)
->with(
    'recommended_action',
    $analysis['recommended_action']
        ?? 'Review shipment'
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
    DecisionEngine $engine
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

    $decisionAnalysis = $engine->analyze(
        $shipment
    );

    return view(
        'ai-analysis.show',
        [
            'shipment' => $shipment,
            'analysisRecord' => $analysis,
            'decisionAnalysis' => $decisionAnalysis,
        ]
    );
}
}
