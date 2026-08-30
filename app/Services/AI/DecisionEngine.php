<?php

namespace App\Services\AI;

use App\Models\CommodityProfile;
use App\Models\Shipment;
use App\Services\Agriculture\CommodityProfileService;
use App\Services\Agriculture\QualityPredictionService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * AgriFlow Decision Engine - Step 4
 *
 * Single source of truth for shipment operational intelligence.
 *
 * Step 4 keeps the Step 3.2 freshness model and adds a dedicated,
 * explainable OperationalRiskService. Freshness outputs are now the dominant
 * risk inputs rather than a post-hoc guardrail:
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
        private readonly QualityPredictionService $qualityPrediction,
        private readonly OperationalRiskService $operationalRisk,
        private readonly InterventionRecommendationService $interventionRecommendations
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

        // Step 4: freshness-aware operational risk assessment.
        // The dedicated service owns the risk model; DecisionEngine orchestrates it.
        $riskAssessment = $this->operationalRisk->assess(
            $shipment,
            $commodityProfile,
            $qualityPrediction,
            $temperatureAssessment
        );

        $riskScore = $riskAssessment['risk_score'];

        // Backwards-compatible numeric component map for older consumers.
        $riskComponents = collect($riskAssessment['components'])
            ->mapWithKeys(
                fn (array $component, string $key) => [
                    $key => $component['contribution'],
                ]
            )
            ->all();

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

        // Step 4.2: deterministic multi-action intervention plan.
        // LLMs may explain this plan elsewhere, but do not generate or replace
        // the core operational actions.
        $recommendationPlan = $this->interventionRecommendations->recommend(
            $shipment,
            $commodityProfile,
            $qualityPrediction,
            $riskAssessment,
            $temperatureAssessment
        );

        return [
            // Core operational outputs
            'risk_score' => $riskScore,
            'risk_index' => $riskScore,
            'risk_level' => $riskAssessment['risk_level'],
            'risk_severity' => $riskAssessment['risk_severity'],
            'risk_model' => $riskAssessment['model_name'],
            'risk_model_version' => $riskAssessment['model_version'],
            'base_risk_score' => $riskAssessment['base_risk_score'],
            'freshness_risk_floor' => $riskAssessment['critical_override_floor'],
            'freshness_risk_reason' => $riskAssessment['critical_override_reason'],
            'risk_assessment' => $riskAssessment,
            'urgency_level' => $riskAssessment['urgency_level'],
            'intervention_required' => $riskAssessment['intervention_required'],
            'intervention_reason' => $riskAssessment['urgency_reason'],
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
            'explainability' => $this->riskExplainability(
                $riskAssessment
            ),
            'prediction_data' => $this->buildRiskProjection(
                $riskScore,
                $remainingDays
            ),

            // Prescriptive outputs
            'recommendation_plan' => $recommendationPlan,
            'recommended_actions' => $recommendationPlan['actions'],
            'recommended_action' => $recommendationPlan['primary_action'],
            'recommendation_reason' => $recommendationPlan['decision_rationale'],
            'expected_outcome' => $recommendationPlan['expected_outcome'],
            'dispatch_deadline' => $recommendationPlan['action_window'],
            'recommended_vehicle' => $recommendationPlan['recommended_vehicle'],
            'recommended_storage' => $recommendationPlan['recommended_storage'],

            // Backwards-compatible key now powered by QualityPredictionService.
            'estimated_arrival_quality' =>
                $qualityPrediction['quality_at_arrival'],
            'food_waste_level' => $riskAssessment['risk_level'],
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

    private function riskExplainability(array $riskAssessment): array
    {
        $iconMap = [
            'quality' => 'Q',
            'shelf-life' => 'SL',
            'transit-margin' => 'TM',
            'temperature' => 'T',
            'commodity' => 'C',
            'stage' => 'S',
            'transport' => 'TR',
        ];

        return collect($riskAssessment['components'] ?? [])
            ->map(function (array $component) use ($iconMap) {
                return [
                    'title' => $component['title'],
                    'icon' => $iconMap[$component['icon'] ?? ''] ?? '•',
                    // Existing Blade expects an "impact" number. In Step 4
                    // this is weighted contribution points to the 0–100 index.
                    'impact' => $component['contribution'],
                    'pressure_score' => $component['score'],
                    'weight' => $component['weight'],
                    'reason' => $component['reason'],
                ];
            })
            ->sortByDesc('impact')
            ->values()
            ->all();
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
