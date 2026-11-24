<?php

namespace App\Http\Controllers;

use App\Models\AiAnalysis;
use App\Models\Shipment;
use App\Services\AI\AnalysisSnapshotService;
use App\Services\AI\DecisionEngine;
use App\Services\GeminiService;
use App\Services\Routing\FreshnessAwareRouteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAnalysisController extends Controller
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

        return view('ai-analysis.index', compact('shipments'));
    }

    public function bulkDestroy(Request $request)
    {
        $ids = json_decode($request->ids, true);

        if (is_array($ids) && !empty($ids)) {
            AiAnalysis::query()
                ->whereIn('id', $ids)
                ->whereHas('shipment.harvest', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->delete();
        }

        return redirect()->back()->with(
            'success',
            'Data terpilih berhasil dihapus.'
        );
    }

    public function truncate()
    {
        AiAnalysis::query()
            ->whereHas('shipment.harvest', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->delete();

        return redirect()->back()->with(
            'success',
            'Seluruh riwayat analisis berhasil dikosongkan.'
        );
    }

    public function analyze(
        Shipment $shipment,
        DecisionEngine $engine,
        GeminiService $ai,
        FreshnessAwareRouteService $freshnessRoutes,
        AnalysisSnapshotService $snapshots
    ) {
        $this->ensureShipmentOwnership($shipment);
        if ($shipment->isDelivered()) {
            return redirect()
                ->route('completed-shipments.show', $shipment)
                ->with(
                    'warning',
                    'Pengiriman ini sudah selesai dikirim. Analisis operasional telah ditutup, namun riwayat penilaian tetap dapat ditinjau.'
                );
        }

        $shipment->loadMissing('harvest');

        $analysis = $engine->analyze($shipment);
        $routeDecision = $freshnessRoutes
            ->assessCurrentRoute($shipment, $analysis);
        $plan = $analysis['recommendation_plan'] ?? [];
        $actions = $analysis['recommended_actions'] ?? [];

        // LLM explanation only. Deterministic actions remain authoritative.
        $explanation = $ai->generateShipmentExplanation([
            'commodity' => $shipment->harvest?->commodity ?? 'Tidak diketahui',
            'origin' => $shipment->origin,
            'destination' => $shipment->destination,
            'status' => $shipment->status,
            'remaining_days' => $analysis['remaining_days'],
            'distance' => $shipment->distance_km ?? 0,
            'risk_score' => $analysis['risk_score'],
            'priority_score' => $analysis['priority_score'],
            'operational_readiness_score' =>
                $analysis['operational_readiness_score'],
            'recommended_action' => $analysis['recommended_action'],
            'recommendation_reason' => $analysis['recommendation_reason'],
        ]);

        $decisionReason = $explanation['decision_reason']
            ?? $analysis['recommendation_reason'];

        $expectedOutcome = $analysis['expected_outcome']
            ?? 'Lanjutkan pemantauan pengiriman sesuai batas operasional saat ini.';

        $actionLines = collect($actions)
            ->map(fn (array $action) => '- ' . ($action['action'] ?? 'Tinjau kondisi pengiriman'))
            ->implode("\n");

        if ($actionLines === '') {
            $actionLines = '- ' . ($analysis['recommended_action'] ?? 'Tinjau kondisi pengiriman');
        }

        $aiResult = "Recommendations:\n{$actionLines}\n\n"
            . "Explanation:\n{$decisionReason}\n\n"
            . "Conclusion:\n{$expectedOutcome}";

        $persistedRecommendation = "Recommendations:\n{$actionLines}\n\n"
            . "Explanation:\n{$analysis['recommendation_reason']}\n\n"
            . "Conclusion:\n{$expectedOutcome}";

        $snapshots->persist(
            $shipment,
            $analysis,
            $persistedRecommendation,
            $routeDecision,
            'analyze_now'
        );

        return redirect()
            ->route('ai-analysis.index')
            ->with('ai_result', $aiResult)
            ->with('shipment_data', [
                'commodity' => $shipment->harvest?->commodity ?? 'Tidak diketahui',
                'origin' => $shipment->origin,
                'destination' => $shipment->destination,
                'status' => $shipment->status,
                'distance' => $shipment->distance_km,
                'remaining_days' => $analysis['remaining_days'],
                'expiry_date' => $shipment->harvest?->expiry_date,
                'duration' => $shipment->duration_hours,
                'carbon_emission' => $analysis['carbon_kg'],
                'route_score' => $routeDecision['route_score'] ?? null,
                'origin_lat' => $shipment->origin_lat ?? 0,
                'origin_lng' => $shipment->origin_lng ?? 0,
                'destination_lat' => $shipment->destination_lat ?? 0,
                'destination_lng' => $shipment->destination_lng ?? 0,
                'route_geometry' => $shipment->route_geometry,
                'data_confidence' => $analysis['data_confidence'],
            ])
            ->with('risk_level', $analysis['risk_level'])
            ->with('risk_index_value', $analysis['risk_index'])
            ->with('operational_readiness_score', $analysis['operational_readiness_score'])
            ->with('sustainability_score', $analysis['sustainability_score'])
            ->with('explainability', $analysis['explainability'])
            ->with('priority_score', $analysis['priority_score'])
            ->with(
                'total_impact',
                collect($analysis['explainability'])->sum('impact')
            )
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
            ->with('risk_assessment', $analysis['risk_assessment'] ?? [])
            ->with('risk_severity', $analysis['risk_severity'] ?? null)
            ->with('urgency_level', $analysis['urgency_level'] ?? null)
            ->with('intervention_required', $analysis['intervention_required'] ?? false)
            ->with('intervention_reason', $analysis['intervention_reason'] ?? null)
            ->with('dispatch_deadline', $analysis['dispatch_deadline'] ?? null)
            ->with('recommendation_plan', $plan)
            ->with('recommended_actions', $actions)
            ->with('recommended_action', $analysis['recommended_action'])
            ->with('recommendation_reason', $analysis['recommendation_reason'])
            ->with('expected_outcome', $expectedOutcome)
            ->with('route_decision', $routeDecision);
    }

    public function history()
    {
        $analyses = AiAnalysis::query()
            ->with('shipment.harvest')
            ->whereHas('shipment.harvest', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->latest()
            ->get();

        return view('ai-analysis.history', compact('analyses'));
    }

    public function show($id)
    {
        $analysis = AiAnalysis::query()
            ->with([
                'shipment.harvest',
                'shipment.aiAnalyses',
            ])
            ->whereHas('shipment.harvest', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->findOrFail($id);

        $shipment = $analysis->shipment;

        abort_if(
            !$shipment,
            404,
            'Data pengiriman untuk analisis ini tidak ditemukan.'
        );

        $snapshot = $analysis->analysis_snapshot;

        if (empty($snapshot)) {
            return view('ai-analysis.legacy-show', [
                'shipment' => $shipment,
                'analysisRecord' => $analysis,
            ]);
        }

        $snapshotShipment = $this->shipmentFromSnapshot(
            $shipment,
            $snapshot['shipment'] ?? []
        );

        return view('ai-analysis.show', [
            'shipment' => $snapshotShipment,
            'analysisRecord' => $analysis,
            'decisionAnalysis' => $snapshot['analysis'] ?? [],
            'routeDecision' => $snapshot['route_decision'] ?? null,
            'isHistoricalSnapshot' => true,
            'snapshotMeta' => [
                'version' => $snapshot['snapshot_version'] ?? null,
                'context' => $snapshot['context'] ?? null,
                'captured_at' => $snapshot['captured_at'] ?? null,
            ],
        ]);
    }

    private function shipmentFromSnapshot(
        Shipment $shipment,
        array $snapshot
    ): Shipment {
        $historical = clone $shipment;

        foreach ([
            'origin',
            'destination',
            'distance_km',
            'duration_hours',
            'recorded_temperature_c',
            'recorded_relative_humidity_percent',
            'recorded_moisture_percent',
            'condition_source',
        ] as $field) {
            if (array_key_exists($field, $snapshot)) {
                $historical->{$field} = $snapshot[$field];
            }
        }

        if (array_key_exists('status_at_capture', $snapshot)) {
            $historical->status = $snapshot['status_at_capture'];
        }

        if (array_key_exists('condition_recorded_at', $snapshot)) {
            $historical->condition_recorded_at =
                $snapshot['condition_recorded_at']
                    ? \Carbon\Carbon::parse($snapshot['condition_recorded_at'])
                    : null;
        }

        if ($shipment->harvest) {
            $historicalHarvest = clone $shipment->harvest;

            if (array_key_exists('commodity', $snapshot)) {
                $historicalHarvest->commodity = $snapshot['commodity'];
            }
            if (array_key_exists('weight_kg', $snapshot)) {
                $historicalHarvest->weight = $snapshot['weight_kg'];
            }
            if (array_key_exists('harvest_date', $snapshot)) {
                $historicalHarvest->harvest_date = $snapshot['harvest_date'];
            }
            if (array_key_exists('expiry_date', $snapshot)) {
                $historicalHarvest->expiry_date = $snapshot['expiry_date'];
            }

            $historical->setRelation('harvest', $historicalHarvest);
        }

        return $historical;
    }
}