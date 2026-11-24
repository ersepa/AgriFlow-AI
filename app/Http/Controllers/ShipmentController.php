<?php

namespace App\Http\Controllers;

use App\Models\AiAnalysis;
use App\Models\Harvest;
use App\Models\Shipment;
use App\Services\Agriculture\CommodityProfileService;
use App\Services\AI\AnalysisSnapshotService;
use App\Services\AI\DecisionEngine;
use App\Services\RouteService;
use App\Services\Routing\FreshnessAwareRouteService;
use App\Services\Sustainability\FreightCarbonEstimateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ShipmentController extends Controller
{
    private function ensureShipmentOwnership(Shipment $shipment): void
    {
        abort_unless(
            $shipment->harvest()
                ->where('user_id', Auth::id())
                ->exists(),
            404
        );
    }

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
        $shipments = Shipment::query()
            ->with('harvest')
            ->whereHas('harvest', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->operationallyActive()
            ->latest()
            ->get();

        return view('shipments.index', compact('shipments'));
    }

    public function update(
        Request $request,
        Shipment $shipment,
        DecisionEngine $engine,
        FreshnessAwareRouteService $freshnessRoutes,
        AnalysisSnapshotService $snapshots
    ) {
        $this->ensureShipmentOwnership($shipment);
        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                'in:Harvested,Packed,In Transit,Delivered',
            ],
        ]);

        if ($shipment->isDelivered()) {
            if ($validated['status'] !== 'Delivered') {
                return redirect()
                    ->route('completed-shipments.show', $shipment)
                    ->with(
                        'warning',
                        'Status Selesai Dikirim bersifat final dalam alur kerja ini. Membuka kembali pengiriman memerlukan tindakan terpisah.'
                    );
            }

            return redirect()
                ->route('completed-shipments.show', $shipment)
                ->with('info', 'Pengiriman ini sudah berstatus selesai.');
        }

        if ($validated['status'] === 'Delivered') {
            $shipment->loadMissing('harvest');

            $analysis = $engine->analyze($shipment);
            $routeDecision = $freshnessRoutes->assessCurrentRoute($shipment, $analysis);
            $deliveredAt = now();

            $completionSnapshot = $snapshots->build(
                $shipment,
                $analysis,
                $routeDecision,
                'delivery_completion',
                $deliveredAt
            );

            $snapshots->persist(
                $shipment,
                $analysis,
                $this->buildRecommendationText($analysis),
                $routeDecision,
                'delivery_completion',
                $deliveredAt,
                $completionSnapshot
            );

            $shipment->update([
                'status' => 'Delivered',
                'delivered_at' => $deliveredAt,
                'completion_snapshot' => $completionSnapshot,
            ]);

            return redirect()
                ->route('completed-shipments.show', $shipment)
                ->with(
                    'success',
                    'Pengiriman ditandai sebagai Selesai Dikirim. Siklus keputusan aktif telah ditutup dan ringkasan operasional akhir telah diarsipkan.'
                );
        }

        $shipment->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('shipments.index')
            ->with('success', 'Status pengiriman berhasil diperbarui.');
    }

    public function create(CommodityProfileService $commodityProfiles)
    {
        $harvests = Harvest::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $conditionProfiles = $harvests
            ->mapWithKeys(function (Harvest $harvest) use ($commodityProfiles) {
                $profile = $commodityProfiles->findForCommodity($harvest->commodity);
                $summary = $commodityProfiles->summary($profile);

                return [
                    $harvest->id => [
                        'quality_model_type' => $summary['quality_model_type'] ?? 'shelf_life_quality',
                        'optimal_temp_min' => $summary['optimal_temp_min'] ?? null,
                        'optimal_temp_max' => $summary['optimal_temp_max'] ?? null,
                        'optimal_humidity_min' => $summary['optimal_humidity_min'] ?? null,
                        'optimal_humidity_max' => $summary['optimal_humidity_max'] ?? null,
                        'safe_moisture_short_term_max_percent' => $summary['safe_moisture_short_term_max_percent'] ?? null,
                        'safe_relative_humidity_max_percent' => $summary['safe_relative_humidity_max_percent'] ?? null,
                        'source_name' => $summary['source_name'] ?? null,
                    ],
                ];
            })
            ->all();

        return view('shipments.create', compact('harvests', 'conditionProfiles'));
    }

    public function store(
        Request $request,
        RouteService $routeService,
        DecisionEngine $engine,
        FreshnessAwareRouteService $freshnessRoutes,
        FreightCarbonEstimateService $freightCarbon,
        AnalysisSnapshotService $snapshots
    ) {
        $validated = $request->validate(array_merge(
            [
                'harvest_id' => [
                    'required',
                    'integer',
                    Rule::exists('harvests', 'id')
                        ->where(function ($query) {
                            $query->where('user_id', Auth::id());
                        }),
                ],
                'origin' => ['required', 'string', 'max:255'],
                'destination' => ['required', 'string', 'max:255', 'different:origin'],
                'status' => ['required', 'string', 'in:Harvested,Packed,In Transit'],
            ],
            $this->conditionValidationRules()
        ));

        $harvest = Harvest::query()
            ->where('user_id', Auth::id())
            ->findOrFail($validated['harvest_id']);

        $originCoords = $this->getCoordinates($validated['origin']);
        $destinationCoords = $this->getCoordinates($validated['destination']);

        $routeData = null;

        if ($originCoords && $destinationCoords) {
            try {
                $routeData = $routeService->getRoute($originCoords, $destinationCoords);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $routeGeometry = null;
        $distanceKm = null;
        $durationHours = null;

        if (isset($routeData['features'][0]['geometry']['coordinates'])) {
            $routeGeometry = json_encode($routeData['features'][0]['geometry']['coordinates']);
        }

        if (isset($routeData['features'][0]['properties']['summary'])) {
            $summary = $routeData['features'][0]['properties']['summary'];
            $distanceKm = round($summary['distance'] / 1000, 1);
            $durationHours = round($summary['duration'] / 3600, 1);
        }

        $carbonEstimate = $freightCarbon->estimate(
            $harvest->weight !== null ? (float) $harvest->weight : null,
            $distanceKm
        );

        $carbonEmission = $carbonEstimate['estimated_kg'];
        $routeScore = null;

        $shipment = Shipment::create(array_merge(
            [
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
            ],
            $this->conditionPayload($validated)
        ));

        $shipment->load('harvest');

        $analysis = $engine->analyze($shipment);
        $routeDecision = $freshnessRoutes->assessCurrentRoute($shipment, $analysis);

        $snapshots->persist(
            $shipment,
            $analysis,
            $this->buildRecommendationText($analysis),
            $routeDecision,
            'shipment_created'
        );

        return redirect()
            ->route('shipments.index')
            ->with('success', 'Pengiriman berhasil dibuat dan dianalisis.');
    }

    public function updateConditions(
        Request $request,
        Shipment $shipment,
        DecisionEngine $engine,
        FreshnessAwareRouteService $freshnessRoutes,
        AnalysisSnapshotService $snapshots
    ) {
        $this->ensureShipmentOwnership($shipment);
        if ($shipment->isDelivered()) {
            return redirect()
                ->route('completed-shipments.show', $shipment)
                ->with(
                    'warning',
                    'Data kondisi yang tercatat terkunci setelah pengiriman selesai. Bukti historis tetap tersedia di arsip pengiriman selesai.'
                );
        }

        $validated = $request->validate($this->conditionValidationRules());
        $shipment->update($this->conditionPayload($validated));
        $shipment->loadMissing('harvest');

        $analysis = $engine->analyze($shipment);
        $routeDecision = $freshnessRoutes->assessCurrentRoute($shipment, $analysis);

        $snapshots->persist(
            $shipment,
            $analysis,
            $this->buildRecommendationText($analysis),
            $routeDecision,
            'condition_update'
        );

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('success', 'Kondisi pengiriman berhasil diperbarui dan dianalisis ulang.');
    }

    public function destroy(int $id)
    {
        $shipment = Shipment::query()
            ->whereHas('harvest', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->findOrFail($id);

        if ($shipment->isDelivered()) {
            return redirect()
                ->route('completed-shipments.show', $shipment)
                ->with(
                    'warning',
                    'Data pengiriman yang sudah selesai disimpan sebagai arsip audit operasional dan tidak dapat dihapus dari daftar pengiriman aktif.'
                );
        }

        $shipment->delete();

        return redirect()
            ->route('shipments.index')
            ->with('success', 'Data pengiriman berhasil dihapus.');
    }

    public function show(
        int $id,
        DecisionEngine $engine,
        FreshnessAwareRouteService $freshnessRoutes
    ) {
        $shipment = Shipment::query()
            ->with(['harvest', 'aiAnalyses'])
            ->whereHas('harvest', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->findOrFail($id);

        if ($shipment->isDelivered()) {
            return redirect()->route('completed-shipments.show', $shipment);
        }

        $analysis = $engine->analyze($shipment);
        $routeDecision = $freshnessRoutes->assessCurrentRoute($shipment, $analysis);

        return view('shipments.show', compact('shipment', 'analysis', 'routeDecision'));
    }

    private function conditionValidationRules(): array
    {
        return [
            'recorded_temperature_c' => ['nullable', 'numeric', 'between:-50,80'],
            'recorded_relative_humidity_percent' => ['nullable', 'numeric', 'between:0,100'],
            'recorded_moisture_percent' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }

    private function conditionPayload(array $validated): array
    {
        $temperature = $this->nullableFloat($validated['recorded_temperature_c'] ?? null);
        $humidity = $this->nullableFloat($validated['recorded_relative_humidity_percent'] ?? null);
        $moisture = $this->nullableFloat($validated['recorded_moisture_percent'] ?? null);

        $hasCondition = $temperature !== null || $humidity !== null || $moisture !== null;

        return [
            'recorded_temperature_c' => $temperature,
            'recorded_relative_humidity_percent' => $humidity,
            'recorded_moisture_percent' => $moisture,
            'condition_source' => $hasCondition ? 'manual_entry' : null,
            'condition_recorded_at' => $hasCondition ? now() : null,
        ];
    }

    private function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function buildRecommendationText(array $analysis): string
    {
        $actionLines = collect($analysis['recommended_actions'] ?? [])
            ->map(static fn (array $action): string => '- ' . ($action['action'] ?? 'Tinjau kondisi pengiriman'))
            ->filter()
            ->implode("\n");

        if ($actionLines === '') {
            $actionLines = '- ' . ($analysis['recommended_action'] ?? 'Tinjau kondisi pengiriman');
        }

        $decisionReason = $analysis['recommendation_reason']
            ?? 'Lanjutkan pemantauan pengiriman sesuai batas operasional saat ini.';

        $expectedOutcome = $analysis['expected_outcome']
            ?? 'Pertahankan jadwal pengiriman saat ini dan evaluasi ulang saat kondisi berubah.';

        return
            "Recommendations:\n{$actionLines}\n\n"
            . "Explanation:\n{$decisionReason}\n\n"
            . "Conclusion:\n{$expectedOutcome}";
    }
}