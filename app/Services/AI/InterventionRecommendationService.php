<?php

namespace App\Services\AI;

use App\Models\CommodityProfile;
use App\Models\Shipment;

/**
 * AgriFlow Intervention Recommendation Engine - Step 4.3
 *
 * Produces deterministic operational actions from Step 3.2 freshness
 * intelligence and Step 4 operational risk. Handling guidance is sourced
 * from the validated commodity profile; it is recommendation/reference
 * guidance, not a regulatory transport mandate.
 */
class InterventionRecommendationService
{
    public function recommend(
        Shipment $shipment,
        ?CommodityProfile $profile,
        array $qualityPrediction,
        array $riskAssessment,
        array $temperatureAssessment = []
    ): array {
        $severity = $riskAssessment['risk_severity'] ?? 'Low';
        $urgency = $riskAssessment['urgency_level'] ?? 'Routine';
        $deadline = $riskAssessment['dispatch_deadline'] ?? 'Flexible';
        $remainingDays = $qualityPrediction['remaining_shelf_life_at_arrival_days'] ?? null;
        $marginHours = $qualityPrediction['transit_margin_hours'] ?? null;
        $safeStatus = $qualityPrediction['safe_transit_status'] ?? 'Unknown';
        $expiryConstraint = (bool) ($qualityPrediction['expiry_constraint_applied'] ?? false);
        $temperatureStatus = $temperatureAssessment['status'] ?? 'Not provided';

        $isDryCommodity =
            ($profile?->quality_model_type ?? null)
                === 'storage_stability';

        $actions = [
            $this->primaryAction($severity, $urgency, $deadline),
            $this->freshnessAction(
                $remainingDays,
                $marginHours,
                $safeStatus,
                $expiryConstraint,
                $profile
            ),
            $this->conditionAction(
                $profile,
                $temperatureStatus,
                $temperatureAssessment,
                $qualityPrediction
            ),
        ];

        $actions = array_values(array_slice($this->deduplicate($actions), 0, 3));

        return [
            'engine' => 'AgriFlow Intervention Recommendation Engine',
            'version' => 'step5.2.2-dry-decision-polish',
            'actions' => $actions,
            'primary_action' => $actions[0]['action'] ?? 'Monitor shipment',
            'primary_reason' => $actions[0]['reason'] ?? 'Continue operational monitoring.',
            'decision_rationale' => $this->decisionRationale(
                $severity,
                $urgency,
                $qualityPrediction,
                $riskAssessment
            ),
            'expected_outcome' => $this->expectedOutcome($severity),
            'action_window' => $deadline,
            'intervention_status' => $riskAssessment['intervention_status'] ?? 'Routine monitoring',

            // Step 4.3 enriched handling guidance.
            'recommended_vehicle' => $this->recommendedVehicle($profile),
            'recommended_storage' => $this->storageSummary($profile),
            'recommended_temperature_range' => $this->temperatureRange($profile),
            'recommended_humidity_range' => $this->humidityRange($profile),
            'chilling_threshold' => $this->chillingThreshold($profile),
            'temperature_control_recommended' => (bool) ($profile?->temperature_control_recommended ?? false),
            'commodity_profile_name' => $profile?->local_name ?: $profile?->name,
            'reference_source_name' => $profile?->source_name,
            'reference_source_url' => $profile?->source_url,

            // Step 5.2.2 dry-commodity guidance.
            'quality_model_type' =>
                $profile?->quality_model_type,
            'commodity_class' =>
                $profile?->commodity_class,
            'safe_moisture_short_term_max_percent' =>
                $profile?->safe_moisture_short_term_max_percent,
            'safe_moisture_long_term_max_percent' =>
                $profile?->safe_moisture_long_term_max_percent,
            'safe_relative_humidity_max_percent' =>
                $profile?->safe_relative_humidity_max_percent,
            'reference_storage_max_months' =>
                $profile?->reference_storage_max_months,
            'storage_science_note' =>
                $profile?->storage_science_note,
            'storage_evidence_status' =>
                $this->storageEvidenceStatus(
                    $profile,
                    $qualityPrediction
                ),
        ];
    }

    private function primaryAction(string $severity, string $urgency, string $deadline): array
    {
        return match ($severity) {
            'Critical' => [
                'type' => 'primary',
                'label' => 'Immediate operational action',
                'action' => 'Escalate and review shipment immediately',
                'reason' => 'Critical operational risk requires immediate review before additional delay is accepted.',
                'window' => $deadline,
                'priority' => 1,
            ],
            'High' => [
                'type' => 'primary',
                'label' => 'Dispatch priority',
                'action' => 'Prioritize dispatch within the high-urgency window',
                'reason' => 'High operational risk makes additional dwell time undesirable.',
                'window' => $deadline,
                'priority' => 1,
            ],
            'Moderate' => [
                'type' => 'primary',
                'label' => 'Priority monitoring',
                'action' => 'Process ahead of lower-risk cargo and review within 24 hours',
                'reason' => 'Current conditions are manageable, but the shipment should not accumulate avoidable delay.',
                'window' => $deadline,
                'priority' => 1,
            ],
            default => [
                'type' => 'primary',
                'label' => 'Routine handling',
                'action' => 'Maintain the planned shipment schedule',
                'reason' => 'Current operational risk is low under the available inputs.',
                'window' => $deadline,
                'priority' => 1,
            ],
        };
    }

    private function freshnessAction(
        ?float $remainingDays,
        ?float $marginHours,
        string $safeStatus,
        bool $expiryConstraint,
        ?CommodityProfile $profile = null
    ): array {
        $isDryCommodity =
            ($profile?->quality_model_type ?? null)
                === 'storage_stability';
        if (in_array($safeStatus, ['Threshold already exceeded', 'ETA exceeds safe transit window'], true)) {
            return [
                'type' => 'freshness',
                'label' => 'Transit window protection',
                'action' => 'Reduce delay or revise the transport plan before dispatch',
                'reason' => 'The planned movement is outside the current estimated operational safe transit window.',
                'window' => 'Before dispatch',
                'priority' => 2,
            ];
        }

        if ($remainingDays !== null && $remainingDays <= 1.0) {
            return [
                'type' => 'freshness',
                'label' => $isDryCommodity
                    ? 'Operational deadline protection'
                    : 'Shelf-life protection',
                'action' =>
                    'Minimize dwell time and avoid non-essential handling delays',
                'reason' => $isDryCommodity
                    ? 'One day or less remains before the recorded operational deadline. AgriFlow does not claim this as biological shelf life.'
                    : 'One day or less of operational shelf life is expected to remain at arrival.',
                'window' =>
                    'During the current shipment cycle',
                'priority' => 2,
            ];
        }

        if ($expiryConstraint) {
            return [
                'type' => 'freshness',
                'label' => $isDryCommodity
                    ? 'Recorded deadline protection'
                    : 'Recorded expiry protection',
                'action' => $isDryCommodity
                    ? 'Protect the recorded operational deadline from additional delay'
                    : 'Protect the recorded shelf-life window from additional delay',
                'reason' => $marginHours !== null
                    ? (
                        $isDryCommodity
                            ? sprintf(
                                'The recorded deadline is the current operational constraint, with approximately %.1f hour(s) of transit margin available. This is not claimed as biological shelf life.',
                                max(0, $marginHours)
                            )
                            : sprintf(
                                'Recorded expiry is the limiting constraint, with approximately %.1f hour(s) of transit margin currently available.',
                                max(0, $marginHours)
                            )
                    )
                    : (
                        $isDryCommodity
                            ? 'A recorded operational deadline exists; AgriFlow does not convert it into a biological dry-commodity shelf-life claim.'
                            : 'Recorded expiry is more conservative than the commodity reference shelf-life model.'
                    ),
                'window' => 'Until arrival',
                'priority' => 2,
            ];
        }

        return [
            'type' => 'freshness',
            'label' => $isDryCommodity
                ? 'Operational window checkpoint'
                : 'Freshness checkpoint',
            'action' => $isDryCommodity
                ? 'Recheck the operational deadline and storage evidence if the dispatch plan changes materially'
                : 'Recheck freshness if the planned dispatch time changes materially',
            'reason' => $isDryCommodity
                ? 'Dry-commodity storage stability depends on condition evidence and the recorded operational window; it should be reassessed after meaningful schedule changes.'
                : 'Shelf-life and transit margin should be recalculated when the actual dispatch plan changes.',
            'window' => 'Before material schedule changes',
            'priority' => 2,
        ];
    }

    private function conditionAction(
        ?CommodityProfile $profile,
        string $temperatureStatus,
        array $temperatureAssessment,
        array $qualityPrediction = []
    ): array {
        $isDryCommodity =
            ($profile?->quality_model_type ?? null)
                === 'storage_stability';

        if ($isDryCommodity) {
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
                ] ?? 'Storage condition evidence required';

            if (!$available) {
                return [
                    'type' => 'condition',
                    'label' =>
                        'Storage condition verification',
                    'action' =>
                        'Verify cargo moisture and storage/transport relative humidity before dispatch where possible',
                    'reason' =>
                        'Validated moisture/RH reference thresholds are available for this dry commodity, but recorded cargo moisture/RH condition evidence was not provided.',
                    'window' =>
                        'Before dispatch',
                    'priority' => 3,
                ];
            }

            if (
                $status ===
                    'Outside reference storage limits'
            ) {
                return [
                    'type' => 'condition',
                    'label' =>
                        'Dry-condition intervention',
                    'action' =>
                        'Correct moisture or humidity exposure toward the validated storage reference limits before accepting additional dwell time',
                    'reason' =>
                        $storageAssessment[
                            'message'
                        ]
                        ?? 'Recorded storage condition is outside the validated dry-commodity reference limits.',
                    'window' =>
                        'Before or during dispatch',
                    'priority' => 3,
                ];
            }

            return [
                'type' => 'condition',
                'label' =>
                    'Dry-condition preservation',
                'action' =>
                    'Maintain dry handling and protect the cargo from moisture ingress or re-wetting through arrival',
                'reason' =>
                    'Recorded storage condition does not exceed the current reference limits; preserving dry conditions reduces avoidable storage exposure.',
                'window' => 'Through arrival',
                'priority' => 3,
            ];
        }

        if ($temperatureStatus === 'Not provided') {
            return [
                'type' => 'condition',
                'label' => 'Data verification',
                'action' => 'Verify cargo temperature before dispatch when possible',
                'reason' => 'No recorded shipment temperature or scenario override was provided, so temperature-related uncertainty remains in the assessment.',
                'window' => 'Before dispatch',
                'priority' => 3,
            ];
        }

        if (in_array($temperatureStatus, ['Chilling risk', 'Above optimum', 'Below optimum'], true)) {
            return [
                'type' => 'condition',
                'label' => 'Temperature intervention',
                'action' => 'Correct the cargo temperature condition toward the commodity reference range',
                'reason' => $temperatureAssessment['message'] ?? 'The current temperature condition is outside the commodity reference range.',
                'window' => 'Before or during dispatch',
                'priority' => 3,
            ];
        }

        if ($profile?->temperature_control_recommended) {
            return [
                'type' => 'condition',
                'label' => 'Condition preservation',
                'action' => 'Maintain temperature-controlled handling through arrival where operationally available',
                'reason' => 'The validated commodity profile recommends temperature control during post-harvest handling.',
                'window' => 'Through arrival',
                'priority' => 3,
            ];
        }

        return [
            'type' => 'condition',
            'label' => 'Handling control',
            'action' => 'Maintain the commodity reference storage and handling conditions',
            'reason' => 'Keeping handling conditions aligned with the commodity profile reduces avoidable operational exposure.',
            'window' => 'Through arrival',
            'priority' => 3,
        ];
    }

    private function decisionRationale(
        string $severity,
        string $urgency,
        array $qualityPrediction,
        array $riskAssessment
    ): string {
        $quality = $qualityPrediction['quality_at_arrival'] ?? null;
        $remaining = $qualityPrediction['remaining_shelf_life_at_arrival_days'] ?? null;
        $primaryDriver = $riskAssessment['top_drivers'][0]['title'] ?? 'current operational conditions';

        $isDryCommodity =
            ($qualityPrediction[
                'condition_model_type'
            ] ?? null) === 'storage_stability';

        $qualityText = $quality !== null
            ? sprintf(
                'arrival quality %.0f/100',
                $quality
            )
            : (
                $isDryCommodity
                    ? 'arrival quality intentionally not estimated for the dry-commodity storage model'
                    : 'unavailable arrival quality'
            );

        $remainingText = $remaining !== null
            ? (
                $isDryCommodity
                    ? sprintf(
                        '%.2f day(s) before the recorded operational deadline',
                        max(0, $remaining)
                    )
                    : sprintf(
                        '%.2f day(s) of remaining operational life',
                        max(0, $remaining)
                    )
            )
            : (
                $isDryCommodity
                    ? 'no recorded operational deadline'
                    : 'an uncertain remaining shelf-life window'
            );

        return sprintf(
            '%s risk with %s urgency is driven primarily by %s, alongside %s and %s.',
            $severity,
            strtolower($urgency),
            strtolower($primaryDriver),
            $qualityText,
            $remainingText
        );
    }

    private function expectedOutcome(string $severity): string
    {
        return match ($severity) {
            'Critical' => 'The actions are intended to prevent additional avoidable delay while the shipment is immediately reassessed.',
            'High' => 'The actions are intended to preserve the remaining operational freshness window and reduce avoidable handling or dispatch delay.',
            'Moderate' => 'The actions are intended to keep the shipment within its current operational window while preventing risk from escalating.',
            default => 'The actions are intended to maintain the current low-risk operating condition and preserve data quality for future reassessment.',
        };
    }

    private function recommendedVehicle(?CommodityProfile $profile): string
    {
        if (
            ($profile?->quality_model_type ?? null)
                === 'storage_stability'
        ) {
            return
                'Dry, weather-protected transport; prevent moisture ingress and re-wetting';
        }

        return $profile?->temperature_control_recommended
            ? 'Temperature-controlled transport recommended'
            : 'Standard transport may be suitable; preserve commodity reference handling conditions';
    }

    private function storageSummary(?CommodityProfile $profile): string
    {
        if (!$profile) {
            return 'Use validated commodity-specific handling guidance when available.';
        }

        if (
            ($profile->quality_model_type ?? null)
                === 'storage_stability'
        ) {
            $parts = [];

            if (
                $profile->safe_moisture_short_term_max_percent
                !== null
            ) {
                $parts[] = sprintf(
                    'Moisture ≤ %.1f%% short-term reference',
                    (float) $profile->safe_moisture_short_term_max_percent
                );
            }

            if (
                $profile->safe_relative_humidity_max_percent
                !== null
            ) {
                $parts[] = sprintf(
                    'RH ≤ %.0f%% reference',
                    (float) $profile->safe_relative_humidity_max_percent
                );
            }

            return $parts !== []
                ? implode('; ', $parts)
                : 'Preserve dry storage conditions using the validated commodity reference.';
        }

        $parts = array_values(array_filter([
            $this->temperatureRange($profile),
            $this->humidityRange($profile),
        ]));

        return !empty($parts)
            ? implode('; ', $parts)
            : 'Follow the validated commodity reference storage profile.';
    }

    private function temperatureRange(?CommodityProfile $profile): ?string
    {
        if (
            !$profile
            || $profile->optimal_temp_min === null
            || $profile->optimal_temp_max === null
        ) {
            return null;
        }

        return sprintf(
            '%.1f–%.1f°C',
            (float) $profile->optimal_temp_min,
            (float) $profile->optimal_temp_max
        );
    }

    private function humidityRange(?CommodityProfile $profile): ?string
    {
        if (
            !$profile
            || $profile->optimal_humidity_min === null
            || $profile->optimal_humidity_max === null
        ) {
            return null;
        }

        return sprintf(
            '%.0f–%.0f%% RH',
            (float) $profile->optimal_humidity_min,
            (float) $profile->optimal_humidity_max
        );
    }

    private function chillingThreshold(?CommodityProfile $profile): ?string
    {
        if (!$profile || $profile->chilling_threshold_c === null) {
            return null;
        }

        return sprintf(
            'Below %.1f°C may present chilling risk under the current commodity profile',
            (float) $profile->chilling_threshold_c
        );
    }

    private function storageEvidenceStatus(
        ?CommodityProfile $profile,
        array $qualityPrediction
    ): ?string {
        if (
            !$profile
            || ($profile->quality_model_type ?? null)
                !== 'storage_stability'
        ) {
            return null;
        }

        $assessment =
            $qualityPrediction[
                'storage_stability_assessment'
            ] ?? [];

        if (
            !($assessment['available'] ?? false)
        ) {
            return
                'Recorded cargo moisture/RH condition not provided';
        }

        return
            $assessment['status']
            ?? 'Storage condition evidence available';
    }

    private function deduplicate(array $actions): array
    {
        $seen = [];
        $result = [];

        foreach ($actions as $action) {
            $key = strtolower(trim((string) ($action['action'] ?? '')));
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $result[] = $action;
        }

        return $result;
    }
}
