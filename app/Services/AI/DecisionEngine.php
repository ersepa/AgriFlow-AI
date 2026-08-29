<?php

namespace App\Services\AI;

use App\Models\CommodityProfile;
use App\Models\Shipment;
use App\Services\Agriculture\CommodityProfileService;
use App\Services\Agriculture\QualityPredictionService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * AgriFlow Decision Engine - Step 3.2
 *
 * Single source of truth for shipment operational intelligence.
 *
 * Step 3.1 keeps the domain-informed Quality-at-Arrival model and adds
 * reconciliation between commodity reference shelf life and recorded expiry, plus freshness consistency guardrails:
 * - baseline reference shelf life from the commodity knowledge base
 * - harvest age
 * - temperature-aware effective transit aging
 * - chilling penalties for sensitive commodities
 * - predicted remaining shelf life at arrival
 * - Quality-at-Arrival and Safe Transit Window
 *
 * IMPORTANT:
 * This is still NOT a statistical spoilage probability model and NOT ML.
 * It is a transparent deterministic decision-support model.
 */
class DecisionEngine
{
    public function __construct(
        private readonly CommodityProfileService $commodityProfiles,
        private readonly QualityPredictionService $qualityPrediction
    ) {
    }

    public function optimizeShipments(): Collection
    {
        return Shipment::with('harvest')
            ->whereIn('status', ['Harvested', 'Packed', 'In Transit'])
            ->get()
            ->map(function (Shipment $shipment) {
                $analysis = $this->analyze($shipment);

                return array_merge($analysis, [
                    'shipment' => $shipment,
                    'commodity' => $shipment->harvest?->commodity ?? 'Unknown',
                    'origin' => $shipment->origin,
                    'destination' => $shipment->destination,
                    'origin_lat' => $shipment->origin_lat,
                    'origin_lng' => $shipment->origin_lng,
                    'destination_lat' => $shipment->destination_lat,
                    'destination_lng' => $shipment->destination_lng,
                ]);
            })
            ->sortByDesc('priority_score')
            ->values();
    }

    public function analyze(Shipment $shipment, array $scenario = []): array
    {
        $shipment->loadMissing('harvest');

        $commodityName = $shipment->harvest?->commodity;
        $commodityProfile = $this->commodityProfiles->findForCommodity($commodityName);
        $profileSummary = $this->commodityProfiles->summary($commodityProfile);

        $qualityPrediction = $this->qualityPrediction->predict(
            $shipment,
            $commodityProfile,
            $scenario
        );

        $temperatureAssessment =
            $qualityPrediction['temperature_assessment'];

        // Step 3.2 uses the precise recorded time window from the quality
        // model instead of flooring calendar days to an integer.
        $remainingDays = (float) (
            $qualityPrediction['recorded_remaining_days']
            ?? $this->remainingShelfLifeDays($shipment)
        );

        $riskComponents = $this->riskComponents(
            $shipment,
            $remainingDays,
            $scenario,
            $commodityProfile,
            $temperatureAssessment
        );

        $baseRiskScore = $this->clamp(
            (int) round(array_sum($riskComponents)),
            0,
            100
        );

        $freshnessGuardrail = $this->freshnessRiskGuardrail(
            $qualityPrediction
        );

        $riskScore = max(
            $baseRiskScore,
            $freshnessGuardrail['floor']
        );

        $riskComponents['freshness_guardrail'] = max(
            0,
            $riskScore - $baseRiskScore
        );

        $priorityScore = $this->calculatePriorityScore(
            $shipment,
            $riskScore,
            $remainingDays
        );

        $carbonKg = $this->calculateCarbonKg($shipment, $scenario);
        $carbonImpactScore = $this->calculateCarbonImpactScore($carbonKg);
        $sustainabilityScore = $this->calculateSustainabilityScore(
            $riskScore,
            $carbonImpactScore
        );
        $efficiencyScore = $this->calculateEfficiencyScore(
            $shipment,
            $riskScore,
            $sustainabilityScore,
            $scenario
        );

        $recommendation = $this->buildOperationalRecommendation(
            $shipment,
            $riskScore,
            $remainingDays,
            $commodityProfile,
            $temperatureAssessment
        );

        return [
            // Core operational outputs
            'risk_score' => $riskScore,
            'risk_index' => $riskScore,
            'risk_level' => $this->getRiskLevel($riskScore),
            'base_risk_score' => $baseRiskScore,
            'freshness_risk_floor' => $freshnessGuardrail['floor'],
            'freshness_risk_reason' => $freshnessGuardrail['reason'],
            'priority_score' => $priorityScore,
            'priority_level' => $this->getPriorityLevel($priorityScore),

            // Sustainability outputs
            'carbon_score' => $carbonKg, // backwards-compatible key
            'carbon_kg' => $carbonKg,
            'carbon_impact_score' => $carbonImpactScore,
            'sustainability_score' => $sustainabilityScore,
            'efficiency_score' => $efficiencyScore,

            // Agriculture intelligence
            'commodity_profile_found' => $commodityProfile !== null,
            'commodity_profile' => $profileSummary,
            'temperature_assessment' => $temperatureAssessment,

            // Quality-at-Arrival intelligence (Step 3)
            'quality_prediction' => $qualityPrediction,
            'quality_at_departure' => $qualityPrediction['quality_at_departure'],
            'quality_at_arrival' => $qualityPrediction['quality_at_arrival'],
            'quality_status' => $qualityPrediction['quality_status'],
            'quality_loss_during_transit' => $qualityPrediction['quality_loss_during_transit'],
            // Operational values after Step 3.1 reconciliation.
            'predicted_remaining_shelf_life_days' =>
                $qualityPrediction['remaining_shelf_life_at_arrival_days'],
            'reference_remaining_shelf_life_days' =>
                $qualityPrediction['reference_remaining_shelf_life_at_arrival_days']
                ?? null,
            'recorded_remaining_shelf_life_days' =>
                $qualityPrediction['recorded_remaining_at_arrival_days']
                ?? null,
            'recorded_remaining_hours' =>
                $qualityPrediction['recorded_remaining_hours']
                ?? null,
            'recorded_remaining_at_arrival_hours' =>
                $qualityPrediction['recorded_remaining_at_arrival_hours']
                ?? null,
            'recorded_freshness_at_departure' =>
                $qualityPrediction['recorded_freshness_index_at_departure']
                ?? null,
            'recorded_freshness_at_arrival' =>
                $qualityPrediction['recorded_freshness_index_at_arrival']
                ?? null,
            'reference_quality_at_departure' =>
                $qualityPrediction['reference_quality_at_departure']
                ?? null,
            'reference_quality_at_arrival' =>
                $qualityPrediction['reference_quality_at_arrival']
                ?? null,
            'expiry_constraint_applied' =>
                $qualityPrediction['expiry_constraint_applied']
                ?? false,
            'shelf_life_reconciliation_status' =>
                $qualityPrediction['shelf_life_reconciliation_status']
                ?? 'Unknown',
            'shelf_life_reconciliation_message' =>
                $qualityPrediction['shelf_life_reconciliation_message']
                ?? null,
            'shelf_life_discrepancy_days' =>
                $qualityPrediction['shelf_life_discrepancy_days']
                ?? null,
            'safe_transit_window_hours' =>
                $qualityPrediction['safe_transit_window_hours'],
            'reference_safe_transit_window_hours' =>
                $qualityPrediction['reference_safe_transit_window_hours']
                ?? null,
            'recorded_expiry_window_hours' =>
                $qualityPrediction['recorded_expiry_window_hours']
                ?? null,
            'planned_transit_hours' =>
                $qualityPrediction['planned_transit_hours']
                ?? null,
            'transit_margin_hours' =>
                $qualityPrediction['transit_margin_hours']
                ?? null,
            'safe_transit_status' =>
                $qualityPrediction['safe_transit_status'],

            // Context
            'remaining_days' => round(max(0, $remainingDays), 2),
            'remaining_hours' => $qualityPrediction['recorded_remaining_hours'] ?? null,
            'data_confidence' => $this->calculateDataConfidence(
                $shipment,
                $commodityProfile,
                $qualityPrediction
            ),
            'risk_components' => $riskComponents,
            'explainability' => $this->generateExplainability(
                $shipment,
                $riskComponents,
                $remainingDays,
                $commodityProfile,
                $temperatureAssessment
            ),
            'prediction_data' => $this->buildRiskProjection(
                $riskScore,
                $remainingDays
            ),

            // Prescriptive outputs
            'recommended_action' => $recommendation['action'],
            'recommendation_reason' => $recommendation['reason'],
            'dispatch_deadline' => $recommendation['dispatch_deadline'],
            'recommended_vehicle' => $recommendation['recommended_vehicle'],
            'recommended_storage' => $recommendation['recommended_storage'],

            // Backwards-compatible key now powered by QualityPredictionService.
            'estimated_arrival_quality' =>
                $qualityPrediction['quality_at_arrival'],
            'food_waste_level' => $this->getRiskLevel($riskScore),
        ];
    }

    /**
     * Digital Twin scenario through the same engine used everywhere else.
     */
    public function simulate(Shipment $shipment, array $scenario): array
    {
        $before = $this->analyze($shipment);
        $after = $this->analyze($shipment, $scenario);

        $baseDuration = (float) ($shipment->duration_hours ?? 0);
        $delay = max(0, (float) ($scenario['delay'] ?? 0));
        $routeOptimized = filter_var(
            $scenario['route'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        $afterDuration = $baseDuration;

        // Temporary route scenario assumption. Real alternative-route ETA
        // comes in the routing step later in the roadmap.
        if ($routeOptimized && $afterDuration > 0) {
            $afterDuration *= 0.90;
        }

        $afterDuration += $delay;

        return [
            'before' => [
                'risk_score' => $before['risk_score'],
                'sustainability_score' => $before['sustainability_score'],
                'carbon' => round($before['carbon_kg'], 1),
                'duration' => round($baseDuration, 1),
                'vehicle' => 'Standard Truck',
                'quality_at_arrival' => $before['quality_at_arrival'],
                'remaining_shelf_life_days' =>
                    $before['predicted_remaining_shelf_life_days'],
                'safe_transit_window_hours' =>
                    $before['safe_transit_window_hours'],
            ],
            'after' => [
                'risk_score' => $after['risk_score'],
                'sustainability_score' => $after['sustainability_score'],
                'carbon' => round($after['carbon_kg'], 1),
                'carbon_saved' => round(
                    $before['carbon_kg'] - $after['carbon_kg'],
                    1
                ),
                'duration' => round($afterDuration, 1),
                'vehicle' => $this->displayVehicle($scenario['vehicle'] ?? 'Truck'),
                'quality_at_arrival' => $after['quality_at_arrival'],
                'quality_change' =>
                    $after['quality_at_arrival']
                    - $before['quality_at_arrival'],
                'remaining_shelf_life_days' =>
                    $after['predicted_remaining_shelf_life_days'],
                'safe_transit_window_hours' =>
                    $after['safe_transit_window_hours'],
            ],
            'analysis_before' => $before,
            'analysis_after' => $after,
        ];
    }

    private function remainingShelfLifeDays(Shipment $shipment): int
    {
        $expiryDate = $shipment->harvest?->expiry_date;

        if (!$expiryDate) {
            return 0;
        }

        return (int) floor(
            Carbon::now()->diffInDays(Carbon::parse($expiryDate), false)
        );
    }

    private function buildTemperatureAssessment(
        Shipment $shipment,
        ?CommodityProfile $profile,
        array $scenario
    ): array {
        $temperature = array_key_exists('temperature', $scenario)
            && $scenario['temperature'] !== null
                ? (float) $scenario['temperature']
                : null;

        $exposureHours = (float) ($shipment->duration_hours ?? 0)
            + max(0, (float) ($scenario['delay'] ?? 0));

        return $this->commodityProfiles->assessTemperature(
            $profile,
            $temperature,
            $exposureHours
        );
    }

    /**
     * Base score remains operational, but Step 2 adds commodity perishability.
     */
    private function riskComponents(
        Shipment $shipment,
        float $remainingDays,
        array $scenario,
        ?CommodityProfile $commodityProfile,
        array $temperatureAssessment
    ): array {
        $distance = (float) ($shipment->distance_km ?? 0);
        $duration = (float) ($shipment->duration_hours ?? 0);

        $shelfLife = match (true) {
            $remainingDays <= 0 => 45,
            $remainingDays <= 2 => 36,
            $remainingDays <= 5 => 25,
            $remainingDays <= 7 => 15,
            $remainingDays <= 14 => 8,
            default => 4,
        };

        $commodityPerishability = $this->commodityProfiles
            ->perishabilityRisk($commodityProfile);

        $distanceRisk = match (true) {
            $distance >= 1000 => 20,
            $distance >= 500 => 16,
            $distance >= 300 => 12,
            $distance >= 100 => 8,
            default => 3,
        };

        $durationRisk = match (true) {
            $duration >= 24 => 15,
            $duration >= 12 => 12,
            $duration >= 8 => 9,
            $duration >= 4 => 7,
            $duration > 0 => 3,
            default => 0,
        };

        $statusRisk = match ($shipment->status) {
            'Harvested' => 10,
            'Packed' => 7,
            'In Transit' => 5,
            'Delivered' => 0,
            default => 4,
        };

        return [
            'remaining_shelf_life' => $shelfLife,
            'commodity_perishability' => $commodityPerishability,
            'transport_distance' => $distanceRisk,
            'transit_duration' => $durationRisk,
            'shipment_status' => $statusRisk,
            'scenario_adjustment' => $this->scenarioRiskModifier(
                $scenario,
                $commodityProfile,
                $temperatureAssessment
            ),
        ];
    }

    /**
     * Step 2 scenario modifier.
     *
     * Temperature benefit/penalty now comes from CommodityProfileService.
     * A refrigerated vehicle is NOT automatically considered safe unless the
     * scenario temperature itself is appropriate for the commodity.
     */
    private function scenarioRiskModifier(
        array $scenario,
        ?CommodityProfile $commodityProfile,
        array $temperatureAssessment
    ): int {
        if ($scenario === []) {
            return 0;
        }

        $modifier = 0;
        $vehicle = strtolower((string) ($scenario['vehicle'] ?? 'truck'));
        $delay = max(0, (float) ($scenario['delay'] ?? 0));
        $routeOptimized = filter_var(
            $scenario['route'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        // Temperature is the actual biological condition we care about.
        $modifier += (int) ($temperatureAssessment['risk_modifier'] ?? 0);

        // Refrigerated capability gets only a small operational benefit when
        // temperature control is recommended. The actual temperature still
        // determines whether conditions are safe.
        if (
            in_array($vehicle, ['cold', 'refrigerated', 'refrigerated truck'], true)
            && $commodityProfile?->temperature_control_recommended
        ) {
            $modifier -= 2;
        }

        if ($routeOptimized) {
            $modifier -= 5;
        }

        $modifier += (int) round($delay * 3);

        return $modifier;
    }

    private function freshnessRiskGuardrail(array $qualityPrediction): array
    {
        $quality = $qualityPrediction['quality_at_arrival'] ?? null;
        $recordedArrivalDays = $qualityPrediction['recorded_remaining_at_arrival_days'] ?? null;
        $safeStatus = $qualityPrediction['safe_transit_status'] ?? 'Unknown';
        $expiryStatus = $qualityPrediction['shelf_life_reconciliation_status'] ?? '';

        $floor = 0;
        $reason = 'No freshness guardrail required.';

        $apply = function (int $candidate, string $candidateReason) use (&$floor, &$reason): void {
            if ($candidate > $floor) {
                $floor = $candidate;
                $reason = $candidateReason;
            }
        };

        if ($expiryStatus === 'Recorded expiry reached') {
            $apply(95, 'Recorded expiry has been reached; immediate operational review is required.');
        }

        if ($safeStatus === 'Threshold already exceeded') {
            $apply(92, 'The operational safe transit threshold has already been exceeded.');
        }

        if ($safeStatus === 'ETA exceeds safe transit window') {
            $apply(88, 'Planned transit exceeds the estimated operational safe transit window.');
        }

        if ($quality !== null) {
            if ($quality < 30) {
                $apply(88, 'Predicted operational arrival quality is critical.');
            } elseif ($quality < 50) {
                $apply(80, 'Predicted operational arrival quality is poor.');
            } elseif ($quality < 70) {
                $apply(72, 'Predicted operational arrival quality is at risk.');
            }
        }

        if ($recordedArrivalDays !== null) {
            if ($recordedArrivalDays <= 0) {
                $apply(92, 'No recorded shelf life remains at the predicted arrival time.');
            } elseif ($recordedArrivalDays <= 0.5) {
                $apply(82, 'Less than 12 hours of recorded shelf life is expected to remain at arrival.');
            } elseif ($recordedArrivalDays <= 1.0) {
                $apply(76, 'One day or less of recorded shelf life is expected to remain at arrival.');
            } elseif (
                $recordedArrivalDays <= 2.0
                && ($qualityPrediction['expiry_constraint_applied'] ?? false)
            ) {
                $apply(70, 'Recorded expiry is the limiting constraint with two days or less remaining at arrival.');
            }
        }

        return [
            'floor' => $floor,
            'reason' => $reason,
        ];
    }

    private function calculatePriorityScore(
        Shipment $shipment,
        int $riskScore,
        float $remainingDays
    ): int {
        $urgencyScore = match (true) {
            $remainingDays <= 0 => 100,
            $remainingDays <= 1 => 95,
            $remainingDays <= 2 => 85,
            $remainingDays <= 5 => 65,
            $remainingDays <= 7 => 45,
            default => 20,
        };

        $stageScore = match ($shipment->status) {
            'Harvested' => 100,
            'Packed' => 75,
            'In Transit' => 55,
            'Delivered' => 0,
            default => 40,
        };

        return $this->clamp((int) round(
            ($riskScore * 0.65)
            + ($urgencyScore * 0.25)
            + ($stageScore * 0.10)
        ), 0, 100);
    }

    private function calculateCarbonKg(Shipment $shipment, array $scenario): float
    {
        $baseCarbon = (float) ($shipment->carbon_emission ?? 0);

        if ($baseCarbon <= 0 && ($shipment->distance_km ?? 0) > 0) {
            $baseCarbon = (float) $shipment->distance_km * 0.12;
        }

        $vehicle = strtolower((string) ($scenario['vehicle'] ?? 'truck'));

        $vehicleFactor = match ($vehicle) {
            'cold', 'refrigerated', 'refrigerated truck' => 1.15,
            'ship' => 0.60,
            'plane' => 1.50,
            default => 1.00,
        };

        return round(max(0, $baseCarbon * $vehicleFactor), 2);
    }

    private function calculateCarbonImpactScore(float $carbonKg): int
    {
        return $this->clamp(
            (int) round(($carbonKg / 120) * 100),
            0,
            100
        );
    }

    private function calculateSustainabilityScore(
        int $riskScore,
        int $carbonImpactScore
    ): int {
        return $this->clamp((int) round(
            100
            - ($riskScore * 0.65)
            - ($carbonImpactScore * 0.35)
        ), 0, 100);
    }

    private function calculateEfficiencyScore(
        Shipment $shipment,
        int $riskScore,
        int $sustainabilityScore,
        array $scenario
    ): int {
        $duration = (float) ($shipment->duration_hours ?? 0);
        $delay = max(0, (float) ($scenario['delay'] ?? 0));
        $durationPenalty = min(30, ($duration + $delay) * 1.5);

        return $this->clamp((int) round(
            ($sustainabilityScore * 0.55)
            + ((100 - $riskScore) * 0.35)
            + ((100 - $durationPenalty) * 0.10)
        ), 0, 100);
    }

    private function calculateDataConfidence(
        Shipment $shipment,
        ?CommodityProfile $commodityProfile,
        array $qualityPrediction
    ): int {
        $checks = [
            filled($shipment->harvest?->commodity),
            $commodityProfile !== null,
            filled($shipment->harvest?->harvest_date),
            filled($shipment->harvest?->expiry_date),
            ($qualityPrediction['baseline_shelf_life_days'] ?? null) !== null,
            filled($shipment->origin),
            filled($shipment->destination),
            $shipment->distance_km !== null,
            $shipment->duration_hours !== null,
            $shipment->origin_lat !== null && $shipment->origin_lng !== null,
            $shipment->destination_lat !== null && $shipment->destination_lng !== null,
        ];

        $complete = count(array_filter($checks));
        $score = (int) round(($complete / count($checks)) * 100);

        // Step 3 predictions without an explicit temperature scenario use a
        // neutral reference-temperature assumption. Keep the result usable,
        // but reduce data confidence because actual cargo temperature is unknown.
        if (($qualityPrediction['temperature_basis'] ?? '') === 'reference_neutral_fallback') {
            $score -= 8;
        }

        return $this->clamp($score, 0, 100);
    }

    private function generateExplainability(
        Shipment $shipment,
        array $components,
        float $remainingDays,
        ?CommodityProfile $commodityProfile,
        array $temperatureAssessment
    ): array {
        $commodityName = $shipment->harvest?->commodity ?? 'Unknown commodity';
        $profileLabel = $commodityProfile
            ? ($commodityProfile->local_name ?: $commodityProfile->name)
            : $commodityName;

        $factors = [
            [
                'title' => 'Remaining Shelf Life',
                'icon' => '📦',
                'impact' => max(0, $components['remaining_shelf_life']),
                'reason' => $remainingDays <= 0
                    ? 'The harvest has reached or exceeded the recorded expiry deadline.'
                    : sprintf(
                        'The harvest has approximately %.1f day(s) of recorded shelf life remaining before departure.',
                        $remainingDays
                    ),
            ],
            [
                'title' => 'Freshness Constraint',
                'icon' => '🧭',
                'impact' => max(0, $components['freshness_guardrail'] ?? 0),
                'reason' => ($components['freshness_guardrail'] ?? 0) > 0
                    ? 'The final operational risk was raised by a freshness guardrail because quality, expiry, or transit margin is more critical than the base logistics score alone.'
                    : 'No additional freshness guardrail was required beyond the base operational risk score.',
            ],
            [
                'title' => 'Commodity Perishability',
                'icon' => '🌱',
                'impact' => max(0, $components['commodity_perishability']),
                'reason' => $commodityProfile
                    ? sprintf(
                        '%s is classified as %s perishability in the current AgriFlow post-harvest profile.',
                        $profileLabel,
                        strtolower($commodityProfile->perishability_level)
                    )
                    : 'No validated commodity profile was found, so AgriFlow applies a neutral fallback perishability score.',
            ],
            [
                'title' => 'Transportation Distance',
                'icon' => '🚚',
                'impact' => max(0, $components['transport_distance']),
                'reason' => 'Longer transport distance increases operational exposure before delivery.',
            ],
            [
                'title' => 'Transit Duration',
                'icon' => '⏱️',
                'impact' => max(0, $components['transit_duration']),
                'reason' => 'Longer transit time consumes more of the product’s remaining usable life.',
            ],
            [
                'title' => 'Shipment Status',
                'icon' => '📍',
                'impact' => max(0, $components['shipment_status']),
                'reason' => 'The current logistics stage affects how urgently the shipment should be handled.',
            ],
        ];

        if (($temperatureAssessment['available'] ?? false) === true) {
            $temperatureImpact = (int) ($temperatureAssessment['risk_modifier'] ?? 0);

            $factors[] = [
                'title' => 'Commodity Temperature Fit',
                'icon' => '🌡️',
                'impact' => abs($temperatureImpact),
                'reason' => $temperatureAssessment['message'] ?? 'Temperature was assessed against the commodity profile.',
            ];
        }

        usort(
            $factors,
            fn (array $a, array $b) => $b['impact'] <=> $a['impact']
        );

        return $factors;
    }

    private function buildRiskProjection(int $riskScore, float $remainingDays): array
    {
        $projection = [];
        $urgencyFactor = $remainingDays <= 0
            ? 1.5
            : max(0.35, 1 - min($remainingDays, 14) / 20);

        for ($day = 1; $day <= 7; $day++) {
            $projected = $riskScore + (($day ** 1.45) * 4.5 * $urgencyFactor);

            $projection[] = [
                'day' => $day,
                'risk' => $this->clamp((int) round($projected), 0, 100),
            ];
        }

        return $projection;
    }

    private function buildOperationalRecommendation(
        Shipment $shipment,
        int $riskScore,
        float $remainingDays,
        ?CommodityProfile $commodityProfile,
        array $temperatureAssessment
    ): array {
        $storage = $this->commodityProfiles->storageRecommendation($commodityProfile);
        $temperatureStatus = $temperatureAssessment['status'] ?? 'Not provided';
        $temperatureProblem = in_array(
            $temperatureStatus,
            ['Chilling risk', 'Above optimum', 'Below optimum'],
            true
        );

        $recommendedVehicle = $commodityProfile?->temperature_control_recommended
            ? 'Temperature-controlled vehicle'
            : 'Standard vehicle; preserve recommended storage conditions';

        if ($remainingDays <= 0) {
            return [
                'action' => 'Escalate shipment immediately',
                'reason' => 'The recorded shelf life has been reached or exceeded. The shipment requires immediate operational review.',
                'dispatch_deadline' => 'Immediate review',
                'recommended_vehicle' => $recommendedVehicle,
                'recommended_storage' => $storage,
            ];
        }

        if ($temperatureProblem && ($temperatureAssessment['severity'] ?? '') === 'high') {
            return [
                'action' => 'Correct transport temperature before dispatch',
                'reason' => $temperatureAssessment['message'],
                'dispatch_deadline' => 'Before dispatch',
                'recommended_vehicle' => $recommendedVehicle,
                'recommended_storage' => $storage,
            ];
        }

        if ($riskScore >= 70 || $remainingDays <= 2) {
            return [
                'action' => 'Dispatch immediately',
                'reason' => 'Short remaining shelf life and/or high operational risk makes delay undesirable.',
                'dispatch_deadline' => 'Within 6 hours',
                'recommended_vehicle' => $recommendedVehicle,
                'recommended_storage' => $storage,
            ];
        }

        if ($temperatureProblem) {
            return [
                'action' => 'Adjust commodity transport conditions',
                'reason' => $temperatureAssessment['message'],
                'dispatch_deadline' => 'Before or during dispatch',
                'recommended_vehicle' => $recommendedVehicle,
                'recommended_storage' => $storage,
            ];
        }

        if ($riskScore >= 50 || ($shipment->distance_km ?? 0) >= 300) {
            return [
                'action' => 'Review route and storage conditions',
                'reason' => 'The shipment has meaningful transport exposure and should be checked against its commodity-specific storage profile.',
                'dispatch_deadline' => 'Today',
                'recommended_vehicle' => $recommendedVehicle,
                'recommended_storage' => $storage,
            ];
        }

        if ($riskScore >= 30) {
            return [
                'action' => 'Prioritize shipment',
                'reason' => 'The shipment is currently manageable but should be processed ahead of lower-risk cargo.',
                'dispatch_deadline' => 'Within 24 hours',
                'recommended_vehicle' => $recommendedVehicle,
                'recommended_storage' => $storage,
            ];
        }

        return [
            'action' => 'Monitor shipment',
            'reason' => $commodityProfile
                ? 'Current operational exposure is low and the commodity profile is available for storage guidance.'
                : 'Current operational exposure is low, but the commodity is not yet present in AgriFlow’s validated knowledge base.',
            'dispatch_deadline' => 'Flexible',
            'recommended_vehicle' => $recommendedVehicle,
            'recommended_storage' => $storage,
        ];
    }


    private function getRiskLevel(int $riskScore): string
    {
        return match (true) {
            $riskScore >= 70 => 'High',
            $riskScore >= 40 => 'Medium',
            default => 'Low',
        };
    }

    private function getPriorityLevel(int $priorityScore): string
    {
        return match (true) {
            $priorityScore >= 80 => 'Critical',
            $priorityScore >= 60 => 'High',
            $priorityScore >= 40 => 'Medium',
            default => 'Low',
        };
    }

    private function displayVehicle(string $vehicle): string
    {
        return match (strtolower($vehicle)) {
            'cold', 'refrigerated', 'refrigerated truck' => 'Refrigerated Truck',
            'ship' => 'Ship',
            'plane' => 'Plane',
            default => 'Standard Truck',
        };
    }

    private function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }
}
