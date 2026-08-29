<?php

namespace App\Services\Agriculture;

use App\Models\CommodityProfile;
use App\Models\Shipment;
use Carbon\Carbon;

/**
 * AgriFlow Quality Prediction Service - Step 3.2
 *
 * Purpose:
 * - estimate product age at departure and arrival
 * - estimate remaining reference shelf life
 * - estimate Quality-at-Arrival on a transparent 0-100 index
 * - estimate a safe transit window under the selected temperature scenario
 * - reconcile scientific reference shelf life with the recorded expiry deadline
 *
 * IMPORTANT SCIENTIFIC BOUNDARY:
 * This is a domain-informed deterministic model, NOT a trained ML model and
 * NOT a statistically calibrated spoilage probability.
 *
 * Temperature-above-optimum acceleration uses a generic Q10 rule-of-thumb.
 * FAO notes that deterioration of many perishables rises roughly 2-3x for a
 * 10°C increase. AgriFlow uses 2.0 when a commodity-specific Q10 is absent.
 *
 * Temperature below optimum never receives a shelf-life "bonus". If the
 * commodity profile defines a chilling threshold, a separate chilling
 * penalty is applied instead.
 */
class QualityPredictionService
{
    private const DEFAULT_Q10 = 2.0;
    private const ACCEPTABLE_QUALITY_THRESHOLD = 70;
    private const RECONCILIATION_EPSILON_DAYS = 0.05;

    public function __construct(
        private readonly CommodityProfileService $commodityProfiles
    ) {
    }

    public function predict(
        Shipment $shipment,
        ?CommodityProfile $profile,
        array $scenario = []
    ): array {
        $shipment->loadMissing('harvest');

        $baselineShelfLife = $this->baselineShelfLifeDays($profile);
        $harvestAgeDays = $this->harvestAgeDays($shipment);
        $transitHours = $this->scenarioTransitHours($shipment, $scenario);
        $transitDays = $transitHours / 24;

        $temperature = $this->scenarioTemperature($scenario);
        $temperatureBasis = $temperature !== null
            ? 'scenario_input'
            : 'reference_neutral_fallback';

        $temperatureAssessment = $this->commodityProfiles->assessTemperature(
            $profile,
            $temperature,
            $transitHours
        );

        $temperatureModel = $this->temperatureDeteriorationModel(
            $profile,
            $temperature,
            $temperatureAssessment
        );

        $effectiveTransitAgeDays =
            ($transitDays * $temperatureModel['deterioration_factor'])
            + $this->chillingEquivalentAgePenaltyDays(
                $transitDays,
                $temperatureAssessment
            );

        $effectiveAgeAtDeparture = $harvestAgeDays;
        $effectiveAgeAtArrival = $harvestAgeDays + $effectiveTransitAgeDays;

        $referenceRemainingAtDeparture = $baselineShelfLife !== null
            ? max(0, $baselineShelfLife - $effectiveAgeAtDeparture)
            : null;

        $referenceRemainingAtArrival = $baselineShelfLife !== null
            ? max(0, $baselineShelfLife - $effectiveAgeAtArrival)
            : null;

        $referenceQualityAtDeparture = $this->qualityIndex(
            $baselineShelfLife,
            $effectiveAgeAtDeparture,
            0
        );

        $chillingQualityPenalty = $this->chillingQualityPenalty(
            $temperatureAssessment,
            $transitHours
        );

        $referenceQualityAtArrival = $this->qualityIndex(
            $baselineShelfLife,
            $effectiveAgeAtArrival,
            $chillingQualityPenalty
        );

        /*
         * Step 3.1 reconciliation:
         * The commodity profile describes a scientific reference window.
         * expiry_date is a recorded business/operational deadline.
         * Neither should silently overwrite the other. AgriFlow keeps both
         * and uses the more conservative result for operational decisions.
         */
        $recordedWindow = $this->recordedFreshnessWindow(
            $shipment,
            $transitHours
        );

        $recordedRemainingAtDeparture = $recordedWindow['remaining_at_departure_days'];
        $recordedRemainingAtArrival = $recordedWindow['remaining_at_arrival_days'];

        $operationalRemainingAtDeparture = $this->conservativeRemainingDays(
            $referenceRemainingAtDeparture,
            $recordedRemainingAtDeparture
        );

        $operationalRemainingAtArrival = $this->conservativeRemainingDays(
            $referenceRemainingAtArrival,
            $recordedRemainingAtArrival
        );

        $recordedDeclaredShelfLifeDays = $recordedWindow['declared_window_days'];
        $recordedFreshnessAtDeparture = $recordedWindow['freshness_at_departure'];
        $recordedFreshnessAtArrival = $recordedWindow['freshness_at_arrival'];

        $qualityAtDeparture = $this->conservativeQualityIndex(
            $referenceQualityAtDeparture,
            $recordedFreshnessAtDeparture
        );

        $qualityAtArrival = $this->conservativeQualityIndex(
            $referenceQualityAtArrival,
            $recordedFreshnessAtArrival
        );

        $qualityLoss = max(0, $qualityAtDeparture - $qualityAtArrival);

        $referenceSafeTransitWindowHours = $this->safeTransitWindowHours(
            $baselineShelfLife,
            $harvestAgeDays,
            $temperatureModel['deterioration_factor'],
            $temperatureAssessment
        );

        $recordedExpiryWindowHours = $recordedWindow['remaining_at_departure_hours'];

        $safeTransitWindowHours = $this->conservativeSafeWindowHours(
            $referenceSafeTransitWindowHours,
            $recordedExpiryWindowHours
        );

        $transitMarginHours = $safeTransitWindowHours !== null
            ? round($safeTransitWindowHours - $transitHours, 1)
            : null;

        $reconciliation = $this->reconciliationStatus(
            $referenceRemainingAtArrival,
            $recordedRemainingAtArrival
        );

        $expiryConstraintApplied = $this->recordedConstraintIsLimiting(
            $referenceRemainingAtArrival,
            $recordedRemainingAtArrival
        );

        return [
            'model_name' => 'AgriFlow Domain-Informed Quality Model',
            'model_version' => 'step3.2-reconciled-v2',
            'model_type' => 'deterministic_domain_model',

            'baseline_shelf_life_days' => $baselineShelfLife,
            'baseline_shelf_life_basis' => $baselineShelfLife !== null
                ? 'commodity_profile_midpoint'
                : 'unavailable',

            'harvest_age_days' => round($harvestAgeDays, 2),
            'recorded_declared_shelf_life_days' => $recordedDeclaredShelfLifeDays,
            'recorded_declared_shelf_life_hours' => $recordedWindow['declared_window_hours'],
            'recorded_elapsed_at_departure_hours' => $recordedWindow['elapsed_at_departure_hours'],
            'recorded_remaining_days' => $recordedRemainingAtDeparture !== null
                ? round($recordedRemainingAtDeparture, 2)
                : null,
            'recorded_remaining_hours' => $recordedWindow['remaining_at_departure_hours'],
            'recorded_remaining_at_arrival_days' => $recordedRemainingAtArrival !== null
                ? round($recordedRemainingAtArrival, 2)
                : null,
            'recorded_remaining_at_arrival_hours' => $recordedWindow['remaining_at_arrival_hours'],

            'transit_hours' => round($transitHours, 2),
            'temperature_c' => $temperature,
            'temperature_basis' => $temperatureBasis,
            'temperature_assessment' => $temperatureAssessment,
            'q10_used' => $temperatureModel['q10_used'],
            'q10_basis' => $temperatureModel['q10_basis'],
            'reference_temperature_c' => $temperatureModel['reference_temperature_c'],
            'temperature_deterioration_factor' => round(
                $temperatureModel['deterioration_factor'],
                3
            ),

            'effective_transit_age_days' => round($effectiveTransitAgeDays, 2),
            'effective_age_at_departure_days' => round($effectiveAgeAtDeparture, 2),
            'effective_age_at_arrival_days' => round($effectiveAgeAtArrival, 2),

            // Scientific reference output before business-expiry reconciliation.
            'reference_remaining_shelf_life_at_departure_days' =>
                $referenceRemainingAtDeparture !== null
                    ? round($referenceRemainingAtDeparture, 2)
                    : null,
            'reference_remaining_shelf_life_at_arrival_days' =>
                $referenceRemainingAtArrival !== null
                    ? round($referenceRemainingAtArrival, 2)
                    : null,

            // Operational output: conservative minimum of reference vs expiry.
            'remaining_shelf_life_at_departure_days' =>
                $operationalRemainingAtDeparture !== null
                    ? round($operationalRemainingAtDeparture, 2)
                    : null,
            'remaining_shelf_life_at_arrival_days' =>
                $operationalRemainingAtArrival !== null
                    ? round($operationalRemainingAtArrival, 2)
                    : null,

            'reference_quality_at_departure' => $referenceQualityAtDeparture,
            'reference_quality_at_arrival' => $referenceQualityAtArrival,
            'recorded_freshness_index_at_departure' => $recordedFreshnessAtDeparture,
            'recorded_freshness_index_at_arrival' => $recordedFreshnessAtArrival,
            'quality_at_departure' => $qualityAtDeparture,
            'quality_at_arrival' => $qualityAtArrival,
            'quality_loss_during_transit' => $qualityLoss,
            'quality_status' => $this->qualityStatus($qualityAtArrival),
            'quality_basis' => $recordedFreshnessAtArrival !== null
                ? 'conservative_minimum_of_reference_and_recorded_expiry'
                : 'reference_model_only',

            'reference_safe_transit_window_hours' => $referenceSafeTransitWindowHours,
            'recorded_expiry_window_hours' => $recordedExpiryWindowHours,
            'safe_transit_window_hours' => $safeTransitWindowHours,
            'planned_transit_hours' => round($transitHours, 2),
            'transit_margin_hours' => $transitMarginHours,
            'safe_transit_status' => $this->safeTransitStatus(
                $safeTransitWindowHours,
                $transitHours
            ),

            'expiry_constraint_applied' => $expiryConstraintApplied,
            'shelf_life_reconciliation_status' => $reconciliation['status'],
            'shelf_life_reconciliation_message' => $reconciliation['message'],
            'shelf_life_discrepancy_days' => $reconciliation['discrepancy_days'],

            'prediction_available' => $baselineShelfLife !== null,
            'prediction_basis' => $profile
                ? 'Validated commodity storage profile + harvest age + transit duration + scenario temperature'
                : 'Insufficient commodity profile; quality prediction is limited',

            'source_name' => $profile?->source_name,
            'source_url' => $profile?->source_url,

            'limitations' => [
                'This is not a trained machine-learning model.',
                'The model does not yet use real cargo sensor telemetry.',
                'Packaging, maturity stage, mechanical damage, ethylene exposure, and atmosphere are not yet modeled.',
                'Reference storage life is a commodity profile range and may vary by cultivar, maturity, and handling conditions.',
                'Recorded expiry is treated as an operational deadline, not proof of biological spoilage.',
                'When reference shelf life and recorded expiry disagree, operational outputs use the more conservative constraint and expose the discrepancy.',
            ],
        ];
    }

    private function baselineShelfLifeDays(?CommodityProfile $profile): ?float
    {
        if (!$profile) {
            return null;
        }

        $min = $profile->storage_life_min_days;
        $max = $profile->storage_life_max_days;

        if ($min === null && $max === null) {
            return null;
        }

        if ($min === null) {
            return (float) $max;
        }

        if ($max === null) {
            return (float) $min;
        }

        return ((float) $min + (float) $max) / 2;
    }

    private function harvestAgeDays(Shipment $shipment): float
    {
        $harvestDate = $shipment->harvest?->harvest_date;

        if (!$harvestDate) {
            return 0.0;
        }

        $harvestedAt = Carbon::parse($harvestDate)->startOfDay();
        $now = Carbon::now();

        if ($harvestedAt->isFuture()) {
            return 0.0;
        }

        return max(0, $harvestedAt->diffInHours($now) / 24);
    }

    private function scenarioTransitHours(Shipment $shipment, array $scenario): float
    {
        $duration = max(0, (float) ($shipment->duration_hours ?? 0));

        $routeOptimized = filter_var(
            $scenario['route'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        if ($routeOptimized && $duration > 0) {
            // Transitional assumption retained from the existing Digital Twin.
            // Real alternative-route duration will replace this in the routing step.
            $duration *= 0.90;
        }

        $duration += max(0, (float) ($scenario['delay'] ?? 0));

        return $duration;
    }

    private function scenarioTemperature(array $scenario): ?float
    {
        if (!array_key_exists('temperature', $scenario)) {
            return null;
        }

        if ($scenario['temperature'] === null || $scenario['temperature'] === '') {
            return null;
        }

        return (float) $scenario['temperature'];
    }

    private function temperatureDeteriorationModel(
        ?CommodityProfile $profile,
        ?float $temperature,
        array $assessment
    ): array {
        $q10 = $profile?->q10_factor !== null
            ? (float) $profile->q10_factor
            : self::DEFAULT_Q10;

        $q10Basis = $profile?->q10_factor !== null
            ? 'commodity_profile'
            : 'generic_fao_rule_of_thumb';

        $referenceTemperature = $profile?->optimal_temp_max !== null
            ? (float) $profile->optimal_temp_max
            : null;

        if ($temperature === null || $referenceTemperature === null) {
            return [
                'q10_used' => $q10,
                'q10_basis' => $q10Basis,
                'reference_temperature_c' => $referenceTemperature,
                'deterioration_factor' => 1.0,
            ];
        }

        $status = $assessment['status'] ?? 'Unknown';

        // Never reward below-optimum temperature with artificially longer life.
        if (in_array($status, ['Chilling risk', 'Below optimum'], true)) {
            return [
                'q10_used' => $q10,
                'q10_basis' => $q10Basis,
                'reference_temperature_c' => $referenceTemperature,
                'deterioration_factor' => 1.0,
            ];
        }

        if ($temperature <= $referenceTemperature) {
            return [
                'q10_used' => $q10,
                'q10_basis' => $q10Basis,
                'reference_temperature_c' => $referenceTemperature,
                'deterioration_factor' => 1.0,
            ];
        }

        $factor = $q10 ** (($temperature - $referenceTemperature) / 10);

        return [
            'q10_used' => $q10,
            'q10_basis' => $q10Basis,
            'reference_temperature_c' => $referenceTemperature,
            'deterioration_factor' => max(1.0, min(8.0, $factor)),
        ];
    }

    private function chillingEquivalentAgePenaltyDays(
        float $transitDays,
        array $assessment
    ): float {
        if (($assessment['status'] ?? '') !== 'Chilling risk') {
            return 0.0;
        }

        $riskModifier = max(0, (int) ($assessment['risk_modifier'] ?? 0));

        // Transitional operational penalty, deliberately capped.
        // This is not claimed as a physiological calibration.
        $multiplier = min(1.5, $riskModifier / 12);

        return $transitDays * $multiplier;
    }

    private function chillingQualityPenalty(
        array $assessment,
        float $transitHours
    ): int {
        if (($assessment['status'] ?? '') !== 'Chilling risk') {
            return 0;
        }

        $riskModifier = max(0, (int) ($assessment['risk_modifier'] ?? 0));
        $durationFactor = min(1.5, max(0.5, $transitHours / 12));

        return (int) round(
            min(25, ($riskModifier * 0.55) * $durationFactor)
        );
    }

    private function qualityIndex(
        ?float $baselineShelfLife,
        float $effectiveAgeDays,
        int $additionalPenalty
    ): int {
        if ($baselineShelfLife === null || $baselineShelfLife <= 0) {
            return 50;
        }

        $lifeFractionRemaining = max(
            0,
            1 - ($effectiveAgeDays / $baselineShelfLife)
        );

        // Slightly nonlinear curve: quality remains relatively high early,
        // then declines faster near the end of reference shelf life.
        $quality = 100 * ($lifeFractionRemaining ** 0.85);
        $quality -= $additionalPenalty;

        return $this->clamp((int) round($quality), 0, 100);
    }

    private function safeTransitWindowHours(
        ?float $baselineShelfLife,
        float $harvestAgeDays,
        float $deteriorationFactor,
        array $assessment
    ): ?float {
        if ($baselineShelfLife === null || $baselineShelfLife <= 0) {
            return null;
        }

        // Invert the quality curve to find the effective age corresponding
        // to the minimum acceptable quality threshold.
        $minimumFractionRemaining =
            (self::ACCEPTABLE_QUALITY_THRESHOLD / 100) ** (1 / 0.85);

        $maximumEffectiveAge =
            $baselineShelfLife * (1 - $minimumFractionRemaining);

        $remainingEffectiveAge = max(
            0,
            $maximumEffectiveAge - $harvestAgeDays
        );

        $effectiveRatePerDay = max(0.01, $deteriorationFactor);

        if (($assessment['status'] ?? '') === 'Chilling risk') {
            $riskModifier = max(0, (int) ($assessment['risk_modifier'] ?? 0));
            $effectiveRatePerDay += min(1.5, $riskModifier / 12);
        }

        return round(
            ($remainingEffectiveAge / $effectiveRatePerDay) * 24,
            1
        );
    }

    private function recordedFreshnessWindow(
        Shipment $shipment,
        float $transitHours
    ): array {
        $harvestDate = $shipment->harvest?->harvest_date;
        $expiryDate = $shipment->harvest?->expiry_date;

        if (!$expiryDate) {
            return [
                'declared_window_days' => null,
                'declared_window_hours' => null,
                'elapsed_at_departure_hours' => null,
                'remaining_at_departure_days' => null,
                'remaining_at_departure_hours' => null,
                'remaining_at_arrival_days' => null,
                'remaining_at_arrival_hours' => null,
                'freshness_at_departure' => null,
                'freshness_at_arrival' => null,
            ];
        }

        $now = Carbon::now();
        $arrivalAt = $now->copy()->addMinutes((int) round($transitHours * 60));
        $expiryAt = Carbon::parse($expiryDate)->endOfDay();

        $remainingDepartureHours = round(
            max(0, $now->diffInMinutes($expiryAt, false) / 60),
            2
        );

        $remainingArrivalHours = round(
            max(0, $arrivalAt->diffInMinutes($expiryAt, false) / 60),
            2
        );

        if (!$harvestDate) {
            return [
                'declared_window_days' => null,
                'declared_window_hours' => null,
                'elapsed_at_departure_hours' => null,
                'remaining_at_departure_days' => round($remainingDepartureHours / 24, 3),
                'remaining_at_departure_hours' => $remainingDepartureHours,
                'remaining_at_arrival_days' => round($remainingArrivalHours / 24, 3),
                'remaining_at_arrival_hours' => $remainingArrivalHours,
                'freshness_at_departure' => null,
                'freshness_at_arrival' => null,
            ];
        }

        $harvestedAt = Carbon::parse($harvestDate)->startOfDay();
        $declaredWindowHours = max(
            1.0,
            $harvestedAt->diffInMinutes($expiryAt, false) / 60
        );

        $elapsedDepartureHours = max(
            0,
            $harvestedAt->diffInMinutes($now, false) / 60
        );

        return [
            'declared_window_days' => round($declaredWindowHours / 24, 2),
            'declared_window_hours' => round($declaredWindowHours, 2),
            'elapsed_at_departure_hours' => round($elapsedDepartureHours, 2),
            'remaining_at_departure_days' => round($remainingDepartureHours / 24, 3),
            'remaining_at_departure_hours' => $remainingDepartureHours,
            'remaining_at_arrival_days' => round($remainingArrivalHours / 24, 3),
            'remaining_at_arrival_hours' => $remainingArrivalHours,
            'freshness_at_departure' => $this->recordedFreshnessIndex(
                $remainingDepartureHours,
                $declaredWindowHours
            ),
            'freshness_at_arrival' => $this->recordedFreshnessIndex(
                $remainingArrivalHours,
                $declaredWindowHours
            ),
        ];
    }

    private function recordedFreshnessIndex(
        ?float $remainingHours,
        ?float $declaredWindowHours
    ): ?int {
        if ($remainingHours === null || $declaredWindowHours === null) {
            return null;
        }

        if ($remainingHours <= 0) {
            return 0;
        }

        $fractionRemaining = max(
            0,
            min(1, $remainingHours / max(1.0, $declaredWindowHours))
        );

        return $this->clamp(
            (int) round(100 * ($fractionRemaining ** 0.85)),
            0,
            100
        );
    }

    private function conservativeRemainingDays(
        ?float $referenceRemainingDays,
        ?float $recordedRemainingDays
    ): ?float {
        $recorded = $recordedRemainingDays !== null
            ? max(0, $recordedRemainingDays)
            : null;

        if ($referenceRemainingDays === null) {
            return $recorded;
        }

        if ($recorded === null) {
            return max(0, $referenceRemainingDays);
        }

        return min(
            max(0, $referenceRemainingDays),
            $recorded
        );
    }

    private function conservativeQualityIndex(
        int $referenceQuality,
        ?int $recordedFreshnessIndex
    ): int {
        if ($recordedFreshnessIndex === null) {
            return $referenceQuality;
        }

        return min($referenceQuality, $recordedFreshnessIndex);
    }

    private function conservativeSafeWindowHours(
        ?float $referenceWindowHours,
        ?float $recordedExpiryWindowHours
    ): ?float {
        if ($referenceWindowHours === null) {
            return $recordedExpiryWindowHours;
        }

        if ($recordedExpiryWindowHours === null) {
            return $referenceWindowHours;
        }

        return round(
            min(
                max(0, $referenceWindowHours),
                max(0, $recordedExpiryWindowHours)
            ),
            1
        );
    }

    private function recordedConstraintIsLimiting(
        ?float $referenceRemainingDays,
        ?float $recordedRemainingDays
    ): bool {
        if ($recordedRemainingDays === null) {
            return false;
        }

        if ($referenceRemainingDays === null) {
            return true;
        }

        return max(0, $recordedRemainingDays)
            + self::RECONCILIATION_EPSILON_DAYS
            < max(0, $referenceRemainingDays);
    }

    private function reconciliationStatus(
        ?float $referenceRemainingDays,
        ?float $recordedRemainingDays
    ): array {
        if ($recordedRemainingDays === null) {
            return [
                'status' => 'Reference model only',
                'message' => 'No recorded expiry is available; operational shelf life uses the commodity reference model.',
                'discrepancy_days' => null,
            ];
        }

        if ($referenceRemainingDays === null) {
            return [
                'status' => 'Recorded expiry only',
                'message' => 'No validated reference shelf life is available; the recorded expiry is the only operational deadline.',
                'discrepancy_days' => null,
            ];
        }

        $recorded = max(0, $recordedRemainingDays);
        $reference = max(0, $referenceRemainingDays);
        $difference = round($reference - $recorded, 2);

        if (abs($difference) <= self::RECONCILIATION_EPSILON_DAYS) {
            return [
                'status' => 'Aligned',
                'message' => 'Recorded expiry and reference shelf-life estimates are materially aligned.',
                'discrepancy_days' => 0.0,
            ];
        }

        if ($recorded < $reference) {
            return [
                'status' => $recorded <= 0
                    ? 'Recorded expiry reached'
                    : 'Recorded expiry is limiting',
                'message' => $recorded <= 0
                    ? 'The recorded expiry threshold has been reached; operational freshness is constrained even though the reference model may indicate remaining biological shelf life.'
                    : 'The recorded expiry is more conservative than the commodity reference model, so AgriFlow uses it as the operational constraint.',
                'discrepancy_days' => abs($difference),
            ];
        }

        return [
            'status' => 'Reference model is limiting',
            'message' => 'The commodity reference model is more conservative than the recorded expiry, so AgriFlow uses the reference result.',
            'discrepancy_days' => abs($difference),
        ];
    }

    private function qualityStatus(int $quality): string
    {
        return match (true) {
            $quality >= 85 => 'Excellent',
            $quality >= 70 => 'Good',
            $quality >= 50 => 'At Risk',
            $quality >= 30 => 'Poor',
            default => 'Critical',
        };
    }

    private function safeTransitStatus(?float $safeWindowHours, float $plannedHours): string
    {
        if ($safeWindowHours === null) {
            return 'Unknown';
        }

        if ($safeWindowHours <= 0) {
            return 'Threshold already exceeded';
        }

        if ($plannedHours > $safeWindowHours) {
            return 'ETA exceeds safe transit window';
        }

        if (($safeWindowHours - $plannedHours) <= 2) {
            return 'Tight margin';
        }

        return 'Within estimated safe window';
    }

    private function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }
}
