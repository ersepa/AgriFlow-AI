<?php

namespace App\Http\Controllers;

use App\Models\AiAnalysis;
use App\Models\Harvest;
use App\Models\Shipment;
use App\Services\AI\DecisionEngine;
use App\Services\RouteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\Routing\FreshnessAwareRouteService;

class ShipmentController extends Controller
{
    private function getCoordinates(string $location): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'AgriFlow/1.0'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $location,
                    'format' => 'json',
                    'limit' => 1,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            if (empty($data)) {
                return null;
            }

            return [
                'lat' => (float) $data[0]['lat'],
                'lon' => (float) $data[0]['lon'],
            ];
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    public function index()
    {
        $shipments = Shipment::with('harvest')->latest()->get();

        return view('shipments.index', compact('shipments'));
    }

    public function update(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                'in:Harvested,Packed,In Transit,Delivered',
            ],
        ]);

        $shipment->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('shipments.index')
            ->with('success', 'Status updated successfully!');
    }

    public function create()
    {
        $harvests = Harvest::all();

        return view('shipments.create', compact('harvests'));
    }

    public function store(
        Request $request,
        RouteService $routeService,
        DecisionEngine $engine
    ) {
        $validated = $request->validate([
            'harvest_id' => [
                'required',
                'integer',
                'exists:harvests,id',
            ],
            'origin' => [
                'required',
                'string',
                'max:255',
            ],
            'destination' => [
                'required',
                'string',
                'max:255',
                'different:origin',
            ],
            'status' => [
                'required',
                'string',
                'in:Harvested,Packed,In Transit,Delivered',
            ],
        ]);

        $originCoords = $this->getCoordinates($validated['origin']);
        $destinationCoords = $this->getCoordinates($validated['destination']);

        $routeData = null;

        if ($originCoords && $destinationCoords) {
            try {
                $routeData = $routeService->getRoute(
                    $originCoords,
                    $destinationCoords
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $routeGeometry = null;
        $distanceKm = null;
        $durationHours = null;

        if (isset($routeData['features'][0]['geometry']['coordinates'])) {
            $routeGeometry = json_encode(
                $routeData['features'][0]['geometry']['coordinates']
            );
        }

        if (isset($routeData['features'][0]['properties']['summary'])) {
            $summary = $routeData['features'][0]['properties']['summary'];

            $distanceKm = round($summary['distance'] / 1000, 1);
            $durationHours = round($summary['duration'] / 3600, 1);
        }

        $carbonEmission = $distanceKm !== null
            ? round($distanceKm * 0.12, 2)
            : null;

        $routeScore = null;

        if ($distanceKm !== null && $durationHours !== null) {
            $routeScore = max(
                0,
                min(
                    100,
                    round(
                        100
                        - ($distanceKm / 100)
                        - ($durationHours * 1.2)
                    )
                )
            );
        }

        $shipment = Shipment::create([
            'harvest_id' => $validated['harvest_id'],
            'origin' => $validated['origin'],
            'destination' => $validated['destination'],
            'distance_km' => $distanceKm,
            'duration_hours' => $durationHours,
            'carbon_emission' => $carbonEmission,
            'route_score' => $routeScore,
            'status' => $validated['status'],
            'origin_lat' => $originCoords['lat'] ?? null,
            'origin_lng' => $originCoords['lon'] ?? null,
            'destination_lat' => $destinationCoords['lat'] ?? null,
            'destination_lng' => $destinationCoords['lon'] ?? null,
            'route_geometry' => $routeGeometry,
        ]);

        $shipment->load('harvest');

        $analysis = $engine->analyze($shipment);

        AiAnalysis::create([
            'shipment_id' => $shipment->id,
            'risk_level' => $analysis['risk_level'],
            'waste_probability' => $analysis['risk_index'] . '/100',
            'sustainability_score' => $analysis['sustainability_score'],
            'recommendations' => $analysis['recommended_action']
                . ' — '
                . $analysis['recommendation_reason'],
        ]);

        return redirect()
            ->route('shipments.index')
            ->with(
                'success',
                'Shipment created and analyzed successfully.'
            );
    }

    public function destroy($id)
    {
        $shipment = Shipment::findOrFail($id);
        $shipment->delete();

        return redirect()
            ->route('shipments.index')
            ->with('success', 'Shipment berhasil dihapus!');
    }

public function show(
    $id,
    DecisionEngine $engine,
    FreshnessAwareRouteService $freshnessRoutes
) {
    $shipment = Shipment::with([
        'harvest',
        'aiAnalyses'
    ])->findOrFail($id);

    $analysis =
        $engine->analyze(
            $shipment
        );

    $routeDecision =
        $freshnessRoutes
            ->assessCurrentRoute(
                $shipment,
                $analysis
            );

    return view(
        'shipments.show',
        compact(
            'shipment',
            'analysis',
            'routeDecision'
        )
    );
}
    }
