<?php

namespace App\Http\Controllers;

use App\Models\Harvest;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Services\RouteService;

class ShipmentController extends Controller
{
    private function getCoordinates($location)
{
    $response = Http::withHeaders([
        'User-Agent' => 'EcoLogix/1.0'
    ])->get('https://nominatim.openstreetmap.org/search', [
        'q' => $location,
        'format' => 'json',
        'limit' => 1
    ]);

    $data = $response->json();

    if (empty($data)) {
        return null;
    }

    return [
        'lat' => $data[0]['lat'],
        'lon' => $data[0]['lon']
    ];
}

private function calculateDistance($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371;

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a =
        sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) *
        cos(deg2rad($lat2)) *
        sin($dLon / 2) *
        sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return round($earthRadius * $c);
}
    public function index()
    {
        $shipments = Shipment::with('harvest')->latest()->get();

        return view('shipments.index', compact('shipments'));
    }

    public function update(Request $request, Shipment $shipment)
{
    $request->validate(['status' => 'required']);
    $shipment->update(['status' => $request->status]);

    return redirect()->route('shipments.index')->with('success', 'Status updated successfully!');
}

    public function create()
    {
        $harvests = Harvest::all();

        return view('shipments.create', compact('harvests'));
    }

public function store(Request $request)
{
    $originCoords = $this->getCoordinates($request->origin);
    $destinationCoords = $this->getCoordinates($request->destination);

    $routeService = new RouteService();

$routeData = $routeService->getRoute(
    $originCoords,
    $destinationCoords
);

$routeGeometry = null;

if (isset($routeData['features'][0]['geometry']['coordinates'])) {
    $routeGeometry = json_encode(
        $routeData['features'][0]['geometry']['coordinates']
    );
}

$distanceKm = null;
$durationHours = null;

if (isset($routeData['features'][0]['properties']['summary'])) {

    $summary = $routeData['features'][0]['properties']['summary'];

    $distanceKm = round($summary['distance'] / 1000);

    $durationHours = round($summary['duration'] / 3600, 1);
}

$carbonEmission = $distanceKm
    ? round($distanceKm * 0.12, 2)
    : null;

$routeScore = 100;

$routeScore -= ($distanceKm / 100);
$routeScore -= ($durationHours * 1.2);

$routeScore = max(0, round($routeScore));

    $distance = null;

    if ($originCoords && $destinationCoords) {
        $distance = $this->calculateDistance(
            $originCoords['lat'],
            $originCoords['lon'],
            $destinationCoords['lat'],
            $destinationCoords['lon']
        );
    }

$shipment = Shipment::create([
    'harvest_id' => $request->harvest_id,
    'origin' => $request->origin,
    'destination' => $request->destination,
    'distance_km' => $distanceKm,
    'duration_hours' => $durationHours,
    'carbon_emission' => $carbonEmission,
    'route_score' => $routeScore,
    'status' => $request->status,
    'origin_lat' => $originCoords['lat'] ?? null,
    'origin_lng' => $originCoords['lon'] ?? null,
    'destination_lat' => $destinationCoords['lat'] ?? null,
    'destination_lng' => $destinationCoords['lon'] ?? null,
    'route_geometry' => $routeGeometry,
]);

    $aiService = new \App\Services\GeminiService();

    $remainingDays = now()->diffInDays(
        Carbon::parse($shipment->harvest->expiry_date),
        false
    );

    $result = $aiService->analyzeShipment([
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

    $decoded = $result;

    \App\Models\AiAnalysis::create([
        'shipment_id' => $shipment->id,
        'risk_level' => $decoded['risk_level'] ?? 'Low',
        'waste_probability' => $decoded['waste_probability'] ?? '0%',
        'sustainability_score' => $decoded['sustainability_score'] ?? 0,
        'recommendations' => $decoded['recommendations'] ?? '',
    ]);

    return redirect()->route('shipments.index');
}
public function destroy($id)
{
    // Cari data shipment berdasarkan ID
    $shipment = \App\Models\Shipment::findOrFail($id);
    
    // Hapus datanya
    $shipment->delete();

    // Redirect balik ke halaman daftar shipments dengan pesan sukses
    return redirect()->route('shipments.index')->with('success', 'Shipment berhasil dihapus!');
}

public function show($id) 
{
    // Pastikan model Shipment punya relasi ke harvest dan aiAnalyses
    $shipment = \App\Models\Shipment::with(['harvest', 'aiAnalyses'])->findOrFail($id);
    return view('shipments.show', compact('shipment'));
}

}