<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\AiAnalysis;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class AiAnalysisController extends Controller
{
    // halaman AI analysis
    public function index()
    {
        $shipments = Shipment::with('harvest')->latest()->get();

        return view('ai-analysis.index', compact('shipments'));
    }

    // proses AI analyze
public function analyze(Shipment $shipment, GeminiService $ai)
{
$remainingDays = floor(
    now()->diffInDays(
        Carbon::parse($shipment->harvest->expiry_date),
        false
    )
);

$response = $ai->analyzeShipment([
    'commodity' => $shipment->harvest->commodity,
    'origin' => $shipment->origin,
    'destination' => $shipment->destination,
    'status' => $shipment->status,
    'remaining_days' => $remainingDays,
    'distance' => $shipment->distance_km,
    'duration' => $shipment->duration_hours,
    'carbon_emission' => $shipment->carbon_emission,
    'route_score' => $shipment->route_score,
]);


    $result = $response['choices'][0]['message']['content'] ?? null;

    preg_match('/Commodity Perishability:\s*(.*)/i', $result, $perishabilityMatch);

$perishability = trim($perishabilityMatch[1] ?? 'Medium');

$wasteProbability = 0;

// Remaining Days
if ($remainingDays <= 0) {
    $wasteProbability += 50;
} elseif ($remainingDays <= 3) {
    $wasteProbability += 40;
} elseif ($remainingDays <= 7) {
    $wasteProbability += 15;
} elseif ($remainingDays <= 14) {
    $wasteProbability += 5;
}

// Distance
if ($shipment->distance_km > 1000) {
    $wasteProbability += 30;
} elseif ($shipment->distance_km > 500) {
    $wasteProbability += 20;
} elseif ($shipment->distance_km > 100) {
    $wasteProbability += 10;
}

// Status
if ($shipment->status === 'Harvested') {
    $wasteProbability += 7;
} elseif ($shipment->status === 'Packed') {
    $wasteProbability += 5;
} elseif ($shipment->status === 'In Transit') {
    $wasteProbability += 3;
}

// Perishability dari AI
if (str_contains(strtolower($perishability), 'high')) {
    $wasteProbability += 10;
} elseif (str_contains(strtolower($perishability), 'medium')) {
    $wasteProbability += 5;
}

$wasteProbability = min(100, max(0, $wasteProbability));

$sustainabilityScore = 100 - $wasteProbability;

$riskLevel =
    $wasteProbability >= 70
        ? 'High'
        : ($wasteProbability >= 40 ? 'Medium' : 'Low');

        $predictionData = [];

for ($day = 1; $day <= 7; $day++) {

    $urgency =
        1 - min(
            1,
            $remainingDays / 14
        );

    $predictedRisk = round(
        $wasteProbability +
        (
            pow($day, 1.8)
            * 8
            * (1 + $urgency)
        )
    );

    $predictionData[] = [
        'day' => $day,
        'risk' => min(100, $predictedRisk)
    ];
}

    if (!$result) {
        return back()->with('error', 'AI gagal merespons');
    }


    $recommendation = strstr($result, 'Recommendations:');
    
AiAnalysis::create([
    'shipment_id' => $shipment->id,
    'risk_level' => $riskLevel,
    'waste_probability' => $wasteProbability . '%',
    'sustainability_score' => $sustainabilityScore,
    'recommendations' => $recommendation,
]);

$originCoords = Http::withHeaders([
    'User-Agent' => 'EcoLogix/1.0'
])->get('https://nominatim.openstreetmap.org/search', [
    'q' => $shipment->origin,
    'format' => 'json',
    'limit' => 1
])->json();

$destinationCoords = Http::withHeaders([
    'User-Agent' => 'EcoLogix/1.0'
])->get('https://nominatim.openstreetmap.org/search', [
    'q' => $shipment->destination,
    'format' => 'json',
    'limit' => 1
])->json();

return redirect()
    ->route('ai-analysis.index')
    ->with('ai_result', $result)
    ->with('shipment_data', [
        'commodity' => $shipment->harvest->commodity,
        'origin' => $shipment->origin,
        'destination' => $shipment->destination,
        'status' => $shipment->status,
        'distance' => $shipment->distance_km,
        'remaining_days' => $remainingDays,
        'expiry_date' => $shipment->harvest->expiry_date,
        'duration' => $shipment->duration_hours,
        'carbon_emission' => $shipment->carbon_emission,
        'route_score' => $shipment->route_score,
        'origin_lat' => $originCoords[0]['lat'] ?? 0,
        'origin_lng' => $originCoords[0]['lon'] ?? 0,
        'destination_lat' => $destinationCoords[0]['lat'] ?? 0,
        'destination_lng' => $destinationCoords[0]['lon'] ?? 0,
        'route_geometry' => $shipment->route_geometry,
    ])
    ->with('risk_level', $riskLevel)
    ->with('waste_probability', $wasteProbability . '%')
    ->with('sustainability_score', $sustainabilityScore)
    ->with('prediction_data', $predictionData);
}

public function history()
{
    $analyses = AiAnalysis::with('shipment.harvest')
        ->latest()
        ->get();

    return view('ai-analysis.history', compact('analyses'));

    $analysis = AiAnalysis::with('shipment.harvest')->findOrFail($id);
}

public function destroy($id)
{
    // Cari data berdasarkan ID
    $analysis = \App\Models\AiAnalysis::findOrFail($id);
    
    // Hapus data
    $analysis->delete();

    // Redirect kembali dengan pesan sukses
    return redirect()->route('ai-analysis.history')->with('success', 'Data berhasil dihapus!');
}

public function show($id)
{
    $analysis = \App\Models\AiAnalysis::with('shipment.harvest')->findOrFail($id);
    
    // Kirim data shipment-nya sekalian
    return view('ai-analysis.show', [
        'analysis' => $analysis,
        'shipment' => $analysis->shipment // Ini yang bikin variabel $shipment tersedia
    ]);
}

}