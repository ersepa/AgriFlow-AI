<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Services\AI\DecisionEngine;
use App\Services\GeminiService;
use Illuminate\Pagination\LengthAwarePaginator;

class AIOptimizerController extends Controller
{
public function index(
    DecisionEngine $engine
)
{
    $shipments = Shipment::with('harvest')
        ->whereIn('status', [
            'Harvested',
            'Packed',
            'In Transit'
        ])
        ->get();

    $results = $shipments->map(function ($shipment) use ($engine) {

        $analysis = $engine->analyze($shipment);
        $estimatedWaste = min(
    round($analysis['risk_score'] * 0.8),
    100
);

$efficiency = max(
    0,
    round(
        $analysis['sustainability_score']
        - ($analysis['risk_score'] * 0.2)
    )
);

return [
    'shipment' => $shipment,

    'commodity' => $shipment->harvest->commodity,

    'origin' => $shipment->origin,
    'destination' => $shipment->destination,

    // Koordinat untuk Leaflet
    'origin_lat' => $shipment->origin_lat ?? -6.2,
    'origin_lng' => $shipment->origin_lng ?? 106.8,
    'destination_lat' => $shipment->destination_lat ?? -6.2,
    'destination_lng' => $shipment->destination_lng ?? 106.8,

    'priority_score' => $analysis['priority_score'],
    'risk_score' => $analysis['risk_score'],
    'sustainability_score' => $analysis['sustainability_score'],
    'priority_level' => $analysis['priority_level'],

    // Tambahan yang sebelumnya sudah ada
    'estimated_waste' => $estimatedWaste,
    'efficiency_score' => $efficiency,
];
})->sortByDesc('priority_score')->values();


$page = request()->get('page', 1);

$perPage = 3;

$results = new LengthAwarePaginator(
    $results->forPage($page, $perPage),
    $results->count(),
    $perPage,
    $page,
    [
        'path' => request()->url(),
        'query' => request()->query(),
    ]
);

        $totalShipments = $results->total();

$criticalCount = $results->where('priority_level', 'Critical')->count();

$highCount = $results->where('priority_level', 'High')->count();

$averageSustainability = round(
    $results->avg('sustainability_score'),
    1
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
)
{

    $analysis = $engine->analyze($shipment);


    $result = $gemini->generateShipmentExplanation([

        'commodity' => $shipment->harvest->commodity,

        'origin' => $shipment->origin,

        'destination' => $shipment->destination,

        'status' => $shipment->status,

'remaining_days' => (int) now()->diffInDays(
    $shipment->harvest->expiry_date,
    false
),

        'distance' => $shipment->distance_km ?? 0,

        'risk_score' => $analysis['risk_score'],

        'priority_score' => $analysis['priority_score'],

        'sustainability_score' => $analysis['sustainability_score'],

    ]);

if(!$result){

    return response()->json([
        'recommendation'=>'AI unavailable',
        'decision_reason'=>'No response from AI',
        'conclusion'=>'Try again',
        'confidence'=>0
    ]);

}
    return response()->json([

        'recommendation' => $result['recommendation'] ?? '-',

        'decision_reason' => $result['decision_reason'] ?? '-',

        'conclusion' => $result['conclusion'] ?? '-',

        'confidence' => $result['confidence'] ?? 0,

    ]);

}
}