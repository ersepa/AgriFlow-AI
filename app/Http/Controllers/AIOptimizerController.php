<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Services\AI\DecisionEngine;
use App\Services\GeminiService;
use Illuminate\Pagination\LengthAwarePaginator;

class AIOptimizerController extends Controller
{
    public function index(DecisionEngine $engine)
    {
        $allResults = Shipment::with('harvest')
            ->whereIn('status', ['Harvested', 'Packed', 'In Transit'])
            ->get()
            ->map(function (Shipment $shipment) use ($engine) {
                $analysis = $engine->analyze($shipment);

                return [
                    'shipment' => $shipment,
                    'commodity' => $shipment->harvest?->commodity ?? 'Unknown',
                    'origin' => $shipment->origin,
                    'destination' => $shipment->destination,
                    'origin_lat' => $shipment->origin_lat ?? -6.2,
                    'origin_lng' => $shipment->origin_lng ?? 106.8,
                    'destination_lat' => $shipment->destination_lat ?? -6.2,
                    'destination_lng' => $shipment->destination_lng ?? 106.8,
                    'priority_score' => $analysis['priority_score'],
                    'risk_score' => $analysis['risk_score'],
                    'sustainability_score' => $analysis['sustainability_score'],
                    'priority_level' => $analysis['priority_level'],
                    'estimated_waste' => $analysis['risk_index'],
                    'efficiency_score' => $analysis['efficiency_score'],
                    'recommended_action' => $analysis['recommended_action'],
                    'data_confidence' => $analysis['data_confidence'],
                ];
            })
            ->sortByDesc('priority_score')
            ->values();

        // KPI must be calculated from the full collection, not only current page.
        $totalShipments = $allResults->count();
        $criticalCount = $allResults->where('priority_level', 'Critical')->count();
        $highCount = $allResults->where('priority_level', 'High')->count();
        $averageSustainability = $totalShipments > 0
            ? round($allResults->avg('sustainability_score'), 1)
            : 0;

        $page = (int) request()->get('page', 1);
        $perPage = 3;

        $results = new LengthAwarePaginator(
            $allResults->forPage($page, $perPage)->values(),
            $allResults->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('ai.optimizer', compact(
            'results',
            'totalShipments',
            'criticalCount',
            'highCount',
            'averageSustainability'
        ));
    }

    public function explain(
        Shipment $shipment,
        DecisionEngine $engine,
        GeminiService $gemini
    ) {
        $shipment->loadMissing('harvest');
        $analysis = $engine->analyze($shipment);

        $result = $gemini->generateShipmentExplanation([
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

        return response()->json([
            'recommendation' => $result['recommendation'] ?? $analysis['recommended_action'],
            'decision_reason' => $result['decision_reason'] ?? $analysis['recommendation_reason'],
            'conclusion' => $result['conclusion'] ?? $analysis['recommendation_reason'],
            // Kept for existing UI; this is DATA confidence, not LLM certainty.
            'confidence' => $analysis['data_confidence'],
        ]);
    }
}
