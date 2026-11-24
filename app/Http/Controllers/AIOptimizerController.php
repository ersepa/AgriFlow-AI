<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Services\AI\DecisionEngine;
use App\Services\GeminiService;
use App\Services\RouteService;
use App\Services\Routing\FreshnessAwareRouteService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class AIOptimizerController extends Controller
{
    public function index(
        DecisionEngine $engine,
        FreshnessAwareRouteService $freshnessRoutes
    ) {
        abort_unless(Auth::check(), 401);

        $shipments = Shipment::with('harvest')
            ->whereHas('harvest', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->whereIn('status', [
                'Harvested',
                'Packed',
                'In Transit',
            ])
            ->get();

        $collection = $shipments
            ->map(function (
                Shipment $shipment
            ) use (
                $engine,
                $freshnessRoutes
            ) {
                $analysis = $engine->analyze($shipment);

                $routeAssessment = $freshnessRoutes->assessCurrentRoute(
                    $shipment,
                    $analysis
                );

                return [
                    'shipment' => $shipment,
                    'commodity' => $shipment->harvest?->commodity ?? 'Tidak diketahui',
                    'origin' => $shipment->origin,
                    'destination' => $shipment->destination,
                    'origin_lat' => $shipment->origin_lat,
                    'origin_lng' => $shipment->origin_lng,
                    'destination_lat' => $shipment->destination_lat,
                    'destination_lng' => $shipment->destination_lng,
                    'priority_score' => $analysis['priority_score'],
                    'priority_level' => $analysis['priority_level'],
                    'risk_score' => $analysis['risk_score'],
                    'risk_severity' => $analysis['risk_severity'] ?? ($analysis['risk_level'] ?? 'Tidak diketahui'),
                    'sustainability_score' => $analysis['sustainability_score'],
                    'quality_at_arrival' => $analysis['quality_at_arrival'] ?? null,
                    'quality_status' => $analysis['quality_status'] ?? 'Belum tersedia',
                    'transit_margin_hours' => $analysis['transit_margin_hours'] ?? null,
                    'safe_transit_status' => $analysis['safe_transit_status'] ?? 'Belum tersedia',
                    'route_assessment' => $routeAssessment,
                    'freshness_route_score' => $routeAssessment['route_score'] ?? null,
                    'freshness_feasibility' => $routeAssessment['freshness_feasibility'] ?? 'Belum tersedia',
                    'delay_tolerance_hours' => $routeAssessment['delay_tolerance_hours'] ?? null,
                ];
            })
            ->sortByDesc('priority_score')
            ->values();

        $totalShipments = $collection->count();

        $highCount = $collection
            ->where('risk_severity', 'High')
            ->count();

        $freshnessSafeCount = $collection
            ->where('freshness_feasibility', 'Safe')
            ->count();

        $routeScores = $collection
            ->pluck('freshness_route_score')
            ->filter(fn ($value) => $value !== null);

        $averageRouteScore = $routeScores->isNotEmpty()
            ? round($routeScores->avg(), 1)
            : 0;

        $averageSustainability = $totalShipments > 0
            ? round($collection->avg('sustainability_score'), 1)
            : 0;

        $page = (int) request()->get('page', 1);
        $perPage = 3;

        $results = new LengthAwarePaginator(
            $collection->forPage($page, $perPage)->values(),
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view(
            'ai.optimizer',
            compact(
                'results',
                'totalShipments',
                'highCount',
                'freshnessSafeCount',
                'averageRouteScore',
                'averageSustainability'
            )
        );
    }

    public function freshnessRoutes(
        Shipment $shipment,
        FreshnessAwareRouteService $freshnessRoutes
    ) {
        $this->ensureShipmentOwnership($shipment);

        if ($shipment->isDelivered()) {
            return response()->json([
                'message' => 'Pengiriman ini sudah selesai dikirim. Fitur optimasi aktif telah ditutup; silakan gunakan menu Pengiriman Selesai untuk meninjau riwayat.',
            ], 409);
        }

        $shipment->loadMissing('harvest');

        return response()->json(
            $freshnessRoutes->optimize($shipment)
        );
    }

    public function routeGeometry(
        Shipment $shipment,
        RouteService $routes
    ) {
        $this->ensureShipmentOwnership($shipment);

        if ($shipment->isDelivered()) {
            return response()->json([
                'message' => 'Pengiriman ini sudah selesai dikirim. Fitur optimasi aktif telah ditutup; silakan gunakan menu Pengiriman Selesai untuk meninjau riwayat.',
            ], 409);
        }

        if (
            $shipment->origin_lat === null
            || $shipment->origin_lng === null
            || $shipment->destination_lat === null
            || $shipment->destination_lng === null
        ) {
            return response()->json(
                [
                    'message' => 'Koordinat rute pengiriman belum lengkap.',
                ],
                422
            );
        }

        $route = $routes->getRoute(
            [
                'lat' => (float) $shipment->origin_lat,
                'lon' => (float) $shipment->origin_lng,
            ],
            [
                'lat' => (float) $shipment->destination_lat,
                'lon' => (float) $shipment->destination_lng,
            ]
        );

        $feature = $route['features'][0] ?? null;

        if (!$feature) {
            return response()->json(
                [
                    'message' => 'Penyedia layanan rute belum dapat mengembalikan rute yang dapat digunakan.',
                ],
                502
            );
        }

        return response()->json([
            'geometry' => $feature['geometry']['coordinates'] ?? [],
            'summary' => $feature['properties']['summary'] ?? [],
        ]);
    }

    public function explain(
        Shipment $shipment,
        DecisionEngine $engine,
        GeminiService $gemini
    ) {
        $this->ensureShipmentOwnership($shipment);
        if ($shipment->isDelivered()) {
            return response()->json([
                'message' => 'Pengiriman ini sudah selesai dikirim. Fitur optimasi dan penjelasan aktif telah ditutup; silakan gunakan menu Pengiriman Selesai untuk meninjau riwayat.',
            ], 409);
        }

        $shipment->loadMissing('harvest');

        $analysis = $engine->analyze($shipment);

        $result = $gemini->generateShipmentExplanation([
            'commodity' => $shipment->harvest?->commodity ?? 'Tidak diketahui',
            'origin' => $shipment->origin,
            'destination' => $shipment->destination,
            'status' => $shipment->status,
            'remaining_days' => $analysis['remaining_days'] ?? 0,
            'distance' => $shipment->distance_km ?? 0,
            'risk_score' => $analysis['risk_score'],
            'priority_score' => $analysis['priority_score'],
            'sustainability_score' => $analysis['sustainability_score'],
            'recommended_action' => $analysis['recommended_action'] ?? null,
            'recommendation_reason' => $analysis['recommendation_reason'] ?? null,
        ]);

        if (!$result) {
            return response()->json([
                'recommendation' => $analysis['recommended_action'] ?? 'Tinjau kondisi pengiriman',
                'decision_reason' => $analysis['recommendation_reason'] ?? 'Belum ada alasan penilaian yang tersedia.',
                'conclusion' => $analysis['expected_outcome'] ?? 'Gunakan hasil penilaian AgriFlow sebagai acuan utama operasional.',
                'data_confidence' => $analysis['data_confidence'] ?? 0,
            ]);
        }

        return response()->json([
            'recommendation' => $analysis['recommended_action'] ?? 'Tinjau kondisi pengiriman',
            'decision_reason' => $result['decision_reason'] ?? ($analysis['recommendation_reason'] ?? 'Belum ada alasan penilaian yang tersedia.'),
            'conclusion' => $result['conclusion'] ?? ($analysis['expected_outcome'] ?? 'Tinjau kembali hasil penilaian operasional yang tersedia.'),
            'data_confidence' => $analysis['data_confidence'] ?? 0,
        ]);
    }

    private function ensureShipmentOwnership(Shipment $shipment): void
    {
        abort_unless(Auth::check(), 401);

        abort_unless(
            $shipment->harvest()
                ->where('user_id', Auth::id())
                ->exists(),
            404
        );
    }
}