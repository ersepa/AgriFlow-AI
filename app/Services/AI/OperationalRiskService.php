<?php

namespace App\Services\AI;

use App\Models\CommodityProfile;
use App\Models\Shipment;

/**
 * AgriFlow Operational Risk Engine - Step 4
 *
 * Transparent deterministic risk model for post-harvest shipment decisions.
 *
 * IMPORTANT:
 * - This is NOT a statistical spoilage probability.
 * - This is NOT a trained machine-learning model.
 * - Scores are normalized operational pressure indicators.
 * - Freshness intelligence from Step 3.2 is the dominant input.
 */
class OperationalRiskService
{
    /**
     * Component weights sum to 1.00.
     *
     * 75% of total weight comes directly from freshness condition,
     * remaining shelf life, and transit margin. This prevents distance/status
     * heuristics from overpowering post-harvest condition signals.
     */
    private const WEIGHTS = [
        'arrival_quality' => 0.32,
        'shelf_life_pressure' => 0.28,
        'transit_margin' => 0.15,
        'temperature_exposure' => 0.10,
        'commodity_perishability' => 0.07,
        'shipment_stage' => 0.05,
        'transport_exposure' => 0.03,
    ];

    public function assess(
        Shipment $shipment,
        ?CommodityProfile $profile,
        array $qualityPrediction,
        array $temperatureAssessment = []
    ): array {
        $components = [
            'arrival_quality' => $this->arrivalQualityPressure(
                $qualityPrediction['quality_at_arrival'] ?? null,
                $profile,
                $qualityPrediction
            ),
            'shelf_life_pressure' => $this->shelfLifePressure(
                $qualityPrediction['remaining_shelf_life_at_arrival_days'] ?? null,
                (bool) ($qualityPrediction['expiry_constraint_applied'] ?? false),
                $profile
            ),
            'transit_margin' => $this->transitMarginPressure(
                $qualityPrediction['transit_margin_hours'] ?? null,
                $qualityPrediction['safe_transit_status'] ?? 'Unknown'
            ),
            'temperature_exposure' => $this->temperaturePressure(
                $temperatureAssessment
            ),
            'commodity_perishability' => $this->perishabilityPressure($profile),
            'shipment_stage' => $this->shipmentStagePressure($shipment->status),
            'transport_exposure' => $this->transportExposurePressure($shipment),
        ];

        $weighted = [];
        $score = 0.0;

        foreach ($components as $key => $component) {
            $weight = self::WEIGHTS[$key];
            $contribution = $component['score'] * $weight;

            $weighted[$key] = [
                'title' => $component['title'],
                'icon' => $component['icon'],
                'score' => $component['score'],
                'weight' => (int) round($weight * 100),
                'contribution' => round($contribution, 1),
                'reason' => $component['reason'],
            ];

            $score += $contribution;
        }

        $baseScore = $this->clamp((int) round($score), 0, 100);
        $override = $this->criticalOverride($qualityPrediction);
        $finalScore = max($baseScore, $override['floor']);

        $severity = $this->severity($finalScore);
        $urgency = $this->urgency(
            $finalScore,
            $qualityPrediction
        );

        $topDrivers = collect($weighted)
            ->sortByDesc('contribution')
            ->take(3)
            ->values()
            ->all();

        return [
            'model_name' => 'AgriFlow Operational Risk Engine',
            'model_version' => 'step5.2.2-dry-decision-polish',
            'model_type' => 'deterministic_weighted_risk_model',
            'risk_score' => $finalScore,
            'base_risk_score' => $baseScore,
            'risk_level' => $this->compatibilityLevel($finalScore),
            'risk_severity' => $severity,
            'critical_override_applied' => $override['floor'] > $baseScore,
            'critical_override_floor' => $override['floor'],
            'critical_override_reason' => $override['reason'],
            'components' => $weighted,
            'top_drivers' => $topDrivers,
            'urgency_level' => $urgency['level'],
            'urgency_rank' => $urgency['rank'],
            'intervention_required' => $urgency['intervention_required'],
            'dispatch_deadline' => $urgency['dispatch_deadline'],
            'urgency_reason' => $urgency['reason'],
            'limitations' => [
                'The score is an operational risk index, not a spoilage probability.',
                'The model is deterministic and has not been statistically calibrated against outcome labels.',
                'Actual cargo sensor telemetry, packaging condition, mechanical damage, atmosphere, and microbial measurements are not yet included.',
                'Temperature exposure uses scenario data when available; otherwise uncertainty is represented explicitly.',
                'For dry commodities, missing moisture/RH telemetry is represented as evidence uncertainty rather than a fabricated arrival-quality score.',
            ],
        ];
    }

    private function arrivalQualityPressure(
        ?float $quality,
        ?CommodityProfile $profile,
        array $qualityPrediction = []
    ): array {
        $dryModel =
            ($profile?->quality_model_type ?? null)
                === 'storage_stability';

        if ($dryModel) {
            $storageAssessment =
                $qualityPrediction[
                    'storage_stability_assessment'
                ] ?? [];

            $available =
                (bool) (
                    $storageAssessment[
                        'available'
                    ] ?? false
                );

            $status =
                $storageAssessment[
                    'status'
                ] ?? 'Storage telemetry required';

            $score = match (true) {
                !$available => 45,
                $status ===
                    'Outside reference storage limits'
                    => 90,
                $status ===
                    'Within available reference limits'
                    => 10,
                default => 45,
            };

            return [
                'title' =>
                    'Storage Condition Evidence',
                'icon' => 'quality',
                'score' => $score,
                'reason' =>
                    $storageAssessment[
                        'message'
                    ]
                    ?? 'Dry-commodity condition requires moisture/RH evidence; no synthetic arrival-quality score is created.',
            ];
        }

        $score = match (true) {
            $quality === null => 45,
            $quality >= 85 => 10,
            $quality >= 70 => 35,
            $quality >= 50 => 75,
            $quality >= 30 => 90,
            default => 100,
        };

        return [
            'title' => 'Arrival Quality Pressure',
            'icon' => 'quality',
            'score' => $score,
            'reason' => $quality === null
                ? 'Arrival quality is unavailable, so AgriFlow applies a conservative uncertainty pressure.'
                : sprintf(
                    'Operational arrival quality is %.0f/100; lower predicted condition increases post-harvest loss exposure.',
                    $quality
                ),
        ];
    }

    private function shelfLifePressure(
        ?float $remainingDays,
        bool $expiryConstraintApplied,
        ?CommodityProfile $profile = null
    ): array {
        $score = match (true) {
            $remainingDays === null => 45,
            $remainingDays <= 0 => 100,
            $remainingDays <= 0.5 => 100,
            $remainingDays <= 1.0 => 95,
            $remainingDays <= 2.0 => 85,
            $remainingDays <= 3.0 => 70,
            $remainingDays <= 5.0 => 55,
            $remainingDays <= 7.0 => 40,
            $remainingDays <= 14.0 => 20,
            default => 8,
        };

        if ($expiryConstraintApplied) {
            $score = min(100, $score + 5);
        }

        $dryModel =
            ($profile?->quality_model_type ?? null)
                === 'storage_stability';

        return [
            'title' => $dryModel
                ? 'Operational Deadline Pressure'
                : 'Shelf-Life Pressure',
            'icon' => 'shelf-life',
            'score' => $score,
            'reason' => $remainingDays === null
                ? (
                    $dryModel
                        ? 'No recorded operational deadline is available; biological shelf life is not fabricated for this dry commodity.'
                        : 'Operational remaining shelf life is unavailable.'
                )
                : (
                    $dryModel
                        ? sprintf(
                            'Approximately %.2f day(s) remain before the recorded operational deadline. This is not claimed as biological shelf life.',
                            max(0, $remainingDays)
                        )
                        : sprintf(
                            'Approximately %.2f day(s) of operational shelf life are expected to remain at arrival%s.',
                            max(0, $remainingDays),
                            $expiryConstraintApplied
                                ? ', with recorded expiry acting as the limiting constraint'
                                : ''
                        )
                ),
        ];
    }

    private function transitMarginPressure(
        ?float $marginHours,
        string $safeTransitStatus
    ): array {
        if ($safeTransitStatus === 'Threshold already exceeded') {
            $score = 100;
        } elseif ($safeTransitStatus === 'ETA exceeds safe transit window') {
            $score = 100;
        } else {
            $score = match (true) {
                $marginHours === null => 40,
                $marginHours < 0 => 100,
                $marginHours <= 2 => 95,
                $marginHours <= 6 => 85,
                $marginHours <= 12 => 70,
                $marginHours <= 24 => 50,
                $marginHours <= 48 => 25,
                default => 10,
            };
        }

        return [
            'title' => 'Transit Margin Pressure',
            'icon' => 'transit-margin',
            'score' => $score,
            'reason' => $marginHours === null
                ? 'Transit margin cannot be calculated from the available shelf-life window.'
                : sprintf(
                    'The shipment is expected to have %.1f hour(s) of operational margin after planned transit.',
                    $marginHours
                ),
        ];
    }

    private function temperaturePressure(array $assessment): array
    {
        $status = $assessment['status'] ?? 'Not provided';
        $severity = strtolower((string) ($assessment['severity'] ?? 'unknown'));

        $score = match ($status) {
            'Optimal' => 0,
            'Chilling risk' => $severity === 'high' ? 95 : 80,
            'Above optimum' => $severity === 'high' ? 85 : ($severity === 'medium' ? 65 : 40),
            'Below optimum' => $severity === 'high' ? 80 : ($severity === 'medium' ? 60 : 35),
            'Unknown commodity profile' => 45,
            'Temperature reference unavailable' => 35,
            'Not provided' => 35,
            default => 40,
        };

        return [
            'title' => 'Temperature Exposure',
            'icon' => 'temperature',
            'score' => $score,
            'reason' => $assessment['message']
                ?? 'No validated cargo temperature assessment is available.',
        ];
    }

    private function perishabilityPressure(?CommodityProfile $profile): array
    {
        $level = strtolower((string) ($profile?->perishability_level ?? 'unknown'));

        $score = match ($level) {
            'very high' => 100,
            'high' => 80,
            'moderate' => 45,
            'low' => 20,
            default => 50,
        };

        $dryModel =
            ($profile?->quality_model_type ?? null)
                === 'storage_stability';

        return [
            'title' => $dryModel
                ? 'Commodity Storage Sensitivity'
                : 'Commodity Perishability',
            'icon' => 'commodity',
            'score' => $score,
            'reason' => $profile
                ? (
                    $dryModel
                        ? sprintf(
                            '%s uses dry-commodity storage thresholds; this component is an AgriFlow operational sensitivity class, not a biological spoilage probability.',
                            $profile->local_name ?: $profile->name
                        )
                        : sprintf(
                            '%s is classified as %s perishability in the current AgriFlow commodity profile.',
                            $profile->local_name ?: $profile->name,
                            $profile->perishability_level
                        )
                )
                : 'No validated commodity profile is available, so AgriFlow applies a neutral-conservative pressure.',
        ];
    }

    private function shipmentStagePressure(?string $status): array
    {
        $score = match ($status) {
            'Harvested' => 70,
            'Packed' => 55,
            'In Transit' => 45,
            'Delivered' => 5,
            default => 40,
        };

        return [
            'title' => 'Shipment Stage',
            'icon' => 'stage',
            'score' => $score,
            'reason' => sprintf(
                'The shipment is currently in the %s stage; earlier active stages generally leave more operational decisions still unresolved.',
                $status ?: 'unknown'
            ),
        ];
    }

    private function transportExposurePressure(Shipment $shipment): array
    {
        $distance = max(0, (float) ($shipment->distance_km ?? 0));
        $duration = max(0, (float) ($shipment->duration_hours ?? 0));

        $distanceScore = match (true) {
            $distance >= 1000 => 100,
            $distance >= 500 => 80,
            $distance >= 300 => 65,
            $distance >= 100 => 35,
            $distance > 0 => 15,
            default => 0,
        };

        $durationScore = match (true) {
            $duration >= 24 => 100,
            $duration >= 12 => 80,
            $duration >= 8 => 65,
            $duration >= 4 => 45,
            $duration > 0 => 20,
            default => 0,
        };

        $score = (int) round(($distanceScore + $durationScore) / 2);

        return [
            'title' => 'Transport Exposure',
            'icon' => 'transport',
            'score' => $score,
            'reason' => sprintf(
                'Planned transport exposure is %.0f km over approximately %.1f hour(s).',
                $distance,
                $duration
            ),
        ];
    }

    private function criticalOverride(array $qualityPrediction): array
    {
        $expiryStatus = $qualityPrediction['shelf_life_reconciliation_status'] ?? '';
        $safeStatus = $qualityPrediction['safe_transit_status'] ?? '';
        $quality = $qualityPrediction['quality_at_arrival'] ?? null;
        $remainingDays = $qualityPrediction['remaining_shelf_life_at_arrival_days'] ?? null;

        if ($expiryStatus === 'Recorded expiry reached') {
            return [
                'floor' => 95,
                'reason' => 'Recorded expiry has already been reached.',
            ];
        }

        if ($safeStatus === 'Threshold already exceeded') {
            return [
                'floor' => 92,
                'reason' => 'The operational safe transit threshold has already been exceeded.',
            ];
        }

        if ($safeStatus === 'ETA exceeds safe transit window') {
            return [
                'floor' => 88,
                'reason' => 'Planned transit exceeds the estimated operational safe transit window.',
            ];
        }

        if ($remainingDays !== null && $remainingDays <= 0) {
            return [
                'floor' => 90,
                'reason' => 'No operational shelf life is expected to remain at arrival.',
            ];
        }

        if ($quality !== null && $quality < 30) {
            return [
                'floor' => 85,
                'reason' => 'Predicted operational arrival quality is critical.',
            ];
        }

        return [
            'floor' => 0,
            'reason' => 'No critical override required.',
        ];
    }

    private function urgency(int $riskScore, array $qualityPrediction): array
    {
        $remainingHours = $qualityPrediction['recorded_remaining_at_arrival_hours'] ?? null;
        $marginHours = $qualityPrediction['transit_margin_hours'] ?? null;
        $quality = $qualityPrediction['quality_at_arrival'] ?? null;

        if (
            $riskScore >= 85
            || ($marginHours !== null && $marginHours < 0)
            || ($remainingHours !== null && $remainingHours <= 0)
            || ($quality !== null && $quality < 30)
        ) {
            return [
                'level' => 'Immediate',
                'rank' => 4,
                'intervention_required' => true,
                'dispatch_deadline' => 'Immediate operational review',
                'reason' => 'Critical freshness or transit constraints require immediate intervention.',
            ];
        }

        if ($riskScore >= 60 || ($remainingHours !== null && $remainingHours <= 24)) {
            return [
                'level' => 'High',
                'rank' => 3,
                'intervention_required' => true,
                'dispatch_deadline' => 'Within 6 hours',
                'reason' => 'High operational risk and/or a short remaining shelf-life window makes delay undesirable.',
            ];
        }

        if ($riskScore >= 30 || ($remainingHours !== null && $remainingHours <= 72)) {
            return [
                'level' => 'Elevated',
                'rank' => 2,

                /*
                 * Step 4.1 final mapping:
                 * Moderate operational risk means monitor/review,
                 * not mandatory intervention.
                 */
                'intervention_required' => false,
                'dispatch_deadline' => 'Within 24 hours',
                'reason' =>
                    'The shipment should be monitored and reviewed within 24 hours, but immediate intervention is not required under the current conditions.',
            ];
        }

        return [
            'level' => 'Routine',
            'rank' => 1,
            'intervention_required' => false,
            'dispatch_deadline' => 'Flexible',
            'reason' => 'Current operational exposure is low under the available inputs.',
        ];
    }

    private function severity(int $score): string
    {
        return match (true) {
            $score >= 80 => 'Critical',
            $score >= 60 => 'High',
            $score >= 30 => 'Moderate',
            default => 'Low',
        };
    }

    /**
     * Keep legacy Low/Medium/High values because existing DB rows and Blade
     * color logic currently assume these three labels.
     */
    private function compatibilityLevel(int $score): string
    {
        return match (true) {
            $score >= 60 => 'High',
            $score >= 30 => 'Medium',
            default => 'Low',
        };
    }

    private function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }
}
