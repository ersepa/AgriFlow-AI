<?php

namespace App\Services\AI;

use App\Models\AiAnalysis;
use App\Models\Shipment;
use Carbon\CarbonInterface;

/**
 * Persists immutable decision snapshots for operational auditability.
 *
 * A snapshot records what AgriFlow knew and recommended at that moment.
 * Historical pages must read this payload instead of recomputing against
 * later shipment state.
 */
class AnalysisSnapshotService
{
    public const SNAPSHOT_VERSION = '10.4a-decision-snapshot-v1';

    public function build(
        Shipment $shipment,
        array $analysis,
        ?array $routeDecision = null,
        string $context = 'analysis',
        ?CarbonInterface $deliveredAt = null
    ): array {
        $shipment->loadMissing('harvest');

        $routeSnapshot = $routeDecision;

        if (is_array($routeSnapshot)) {
            // FreshnessAwareRouteService embeds the full analysis for live UI
            // convenience. The snapshot already stores analysis separately, so
            // remove the duplicate payload before persisting JSON.
            unset($routeSnapshot['analysis']);
        }

        return [
            'snapshot_version' => self::SNAPSHOT_VERSION,
            'context' => $context,
            'captured_at' => now()->toIso8601String(),
            'shipment' => [
                'id' => $shipment->id,
                'commodity' => $shipment->harvest?->commodity,
                'weight_kg' => $shipment->harvest?->weight !== null
                    ? (float) $shipment->harvest->weight
                    : null,
                'harvest_date' => $shipment->harvest?->harvest_date
                    ? (string) $shipment->harvest->harvest_date
                    : null,
                'expiry_date' => $shipment->harvest?->expiry_date
                    ? (string) $shipment->harvest->expiry_date
                    : null,
                'origin' => $shipment->origin,
                'destination' => $shipment->destination,
                'status_at_capture' => $shipment->status,
                'distance_km' => $shipment->distance_km !== null
                    ? (float) $shipment->distance_km
                    : null,
                'duration_hours' => $shipment->duration_hours !== null
                    ? (float) $shipment->duration_hours
                    : null,
                'recorded_temperature_c' => $shipment->recorded_temperature_c,
                'recorded_relative_humidity_percent' =>
                    $shipment->recorded_relative_humidity_percent,
                'recorded_moisture_percent' =>
                    $shipment->recorded_moisture_percent,
                'condition_source' => $shipment->condition_source,
                'condition_recorded_at' =>
                    $shipment->condition_recorded_at?->toIso8601String(),
            ],
            'analysis' => $analysis,
            'route_decision' => $routeSnapshot,
            'completion' => $deliveredAt
                ? [
                    'delivered_at' => $deliveredAt->toIso8601String(),
                    'meaning' =>
                        'Final active-state snapshot captured immediately before the shipment decision cycle was closed as Delivered.',
                ]
                : null,
        ];
    }

    public function persist(
        Shipment $shipment,
        array $analysis,
        string $recommendationsText,
        ?array $routeDecision = null,
        string $context = 'analysis',
        ?CarbonInterface $deliveredAt = null,
        ?array $snapshot = null
    ): AiAnalysis {
        $snapshot ??= $this->build(
            $shipment,
            $analysis,
            $routeDecision,
            $context,
            $deliveredAt
        );

        return AiAnalysis::create([
            'shipment_id' => $shipment->id,
            'risk_level' => $analysis['risk_level'] ?? 'Unknown',
            'sustainability_score' =>
                $analysis['operational_readiness_score']
                ?? $analysis['sustainability_score']
                ?? (100 - (int) ($analysis['risk_score'] ?? 0)),
            'waste_probability' =>
                ((int) ($analysis['risk_index'] ?? $analysis['risk_score'] ?? 0))
                . '/100',
            'recommendations' => $recommendationsText,
            'analysis_snapshot' => $snapshot,
        ]);
    }
}
