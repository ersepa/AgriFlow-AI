<?php

namespace App\Services\AI;

use App\Models\CommodityProfile;
use App\Models\Shipment;
use App\Services\Agriculture\CommodityProfileService;
use App\Services\Agriculture\QualityPredictionService;
use App\Services\Sustainability\FreightCarbonEstimateService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DecisionEngine
{
    public function __construct(
        private readonly CommodityProfileService $commodityProfiles,
        private readonly QualityPredictionService $qualityPrediction,
        private readonly OperationalRiskService $operationalRisk,
        private readonly InterventionRecommendationService $interventionRecommendations,
        private readonly FreightCarbonEstimateService $freightCarbon
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
                    'commodity' => $shipment->harvest?->commodity ?? 'Tidak diketahui',
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

        $temperatureAssessment = $qualityPrediction['temperature_assessment'];

        $remainingDays = (float) (
            $qualityPrediction['recorded_remaining_days']
            ?? $this->remainingShelfLifeDays($shipment)
        );

        $riskAssessment = $this->operationalRisk->assess(
            $shipment,
            $commodityProfile,
            $qualityPrediction,
            $temperatureAssessment
        );

        $riskScore = $riskAssessment['risk_score'];

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

        $carbonEstimate = $this->freightCarbon->estimateForShipment($shipment);
        $carbonKg = $carbonEstimate['estimated_kg'];

        $operationalReadinessScore = $this->clamp(100 - $riskScore, 0, 100);

        $recommendationPlan = $this->interventionRecommendations->recommend(
            $shipment,
            $commodityProfile,
            $qualityPrediction,
            $riskAssessment,
            $temperatureAssessment
        );

        return [
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

            'carbon_score' => $carbonKg,
            'carbon_kg' => $carbonKg,
            'carbon_estimate' => $carbonEstimate,
            'carbon_impact_score' => null,
            'operational_readiness_score' => $operationalReadinessScore,
            'operational_readiness_basis' => '100 dikurangi Indeks Risiko Operasional; tanpa pembobotan karbon',
            'sustainability_score' => $operationalReadinessScore,
            'efficiency_score' => null,

            'commodity_profile_found' => $commodityProfile !== null,
            'commodity_profile' => $profileSummary,
            'temperature_assessment' => $temperatureAssessment,
            'condition_assessment' => $qualityPrediction['condition_assessment'] ?? null,

            'quality_prediction' => $qualityPrediction,
            'quality_at_departure' => $qualityPrediction['quality_at_departure'],
            'quality_at_arrival' => $qualityPrediction['quality_at_arrival'],
            'quality_status' => $qualityPrediction['quality_status'],
            'quality_loss_during_transit' => $qualityPrediction['quality_loss_during_transit'],
            'predicted_remaining_shelf_life_days' => $qualityPrediction['remaining_shelf_life_at_arrival_days'],
            'reference_remaining_shelf_life_days' => $qualityPrediction['reference_remaining_shelf_life_at_arrival_days'] ?? null,
            'recorded_remaining_shelf_life_days' => $qualityPrediction['recorded_remaining_at_arrival_days'] ?? null,
            'recorded_remaining_hours' => $qualityPrediction['recorded_remaining_hours'] ?? null,
            'recorded_remaining_at_arrival_hours' => $qualityPrediction['recorded_remaining_at_arrival_hours'] ?? null,
            'recorded_freshness_at_departure' => $qualityPrediction['recorded_freshness_index_at_departure'] ?? null,
            'recorded_freshness_at_arrival' => $qualityPrediction['recorded_freshness_index_at_arrival'] ?? null,
            'reference_quality_at_departure' => $qualityPrediction['reference_quality_at_departure'] ?? null,
            'reference_quality_at_arrival' => $qualityPrediction['reference_quality_at_arrival'] ?? null,
            'expiry_constraint_applied' => $qualityPrediction['expiry_constraint_applied'] ?? false,
            'shelf_life_reconciliation_status' => $qualityPrediction['shelf_life_reconciliation_status'] ?? 'Tidak diketahui',
            'shelf_life_reconciliation_message' => $qualityPrediction['shelf_life_reconciliation_message'] ?? null,
            'shelf_life_discrepancy_days' => $qualityPrediction['shelf_life_discrepancy_days'] ?? null,
            'safe_transit_window_hours' => $qualityPrediction['safe_transit_window_hours'],
            'reference_safe_transit_window_hours' => $qualityPrediction['reference_safe_transit_window_hours'] ?? null,
            'recorded_expiry_window_hours' => $qualityPrediction['recorded_expiry_window_hours'] ?? null,
            'planned_transit_hours' => $qualityPrediction['planned_transit_hours'] ?? null,
            'transit_margin_hours' => $qualityPrediction['transit_margin_hours'] ?? null,
            'safe_transit_status' => $qualityPrediction['safe_transit_status'],

            'remaining_days' => round(max(0, $remainingDays), 2),
            'remaining_hours' => $qualityPrediction['recorded_remaining_hours'] ?? null,
            'data_confidence' => $this->calculateDataConfidence($shipment, $commodityProfile, $qualityPrediction),
            'risk_components' => $riskComponents,
            'explainability' => $this->riskExplainability($riskAssessment),

            'recommendation_plan' => $recommendationPlan,
            'recommended_actions' => $recommendationPlan['actions'],
            'recommended_action' => $recommendationPlan['primary_action'],
            'recommendation_reason' => $recommendationPlan['decision_rationale'],
            'expected_outcome' => $recommendationPlan['expected_outcome'],
            'dispatch_deadline' => $recommendationPlan['action_window'],
            'recommended_vehicle' => $recommendationPlan['recommended_vehicle'],
            'recommended_storage' => $recommendationPlan['recommended_storage'],

            'estimated_arrival_quality' => $qualityPrediction['quality_at_arrival'],
            'food_waste_level' => $riskAssessment['risk_level'],
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
            filled($shipment->harvest?->weight),
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
            ? 'Kendaraan berpendingin (suhu terkontrol)'
            : 'Kendaraan standar; jaga kondisi penyimpanan sesuai acuan';

        if ($remainingDays <= 0) {
            return [
                'action' => 'Segera tinjau dan eskalasi pengiriman',
                'reason' => 'Batas masa simpan tercatat telah tercapai atau terlewati. Pengiriman memerlukan penanganan segera.',
                'dispatch_deadline' => 'Tinjauan segera',
                'recommended_vehicle' => $recommendedVehicle,
                'recommended_storage' => $storage,
            ];
        }

        if ($temperatureProblem && ($temperatureAssessment['severity'] ?? '') === 'high') {
            return [
                'action' => 'Perbaiki suhu pengiriman sebelum berangkat',
                'reason' => $temperatureAssessment['message'],
                'dispatch_deadline' => 'Sebelum keberangkatan',
                'recommended_vehicle' => $recommendedVehicle,
                'recommended_storage' => $storage,
            ];
        }

        if ($riskScore >= 70 || $remainingDays <= 2) {
            return [
                'action' => 'Kirim segera tanpa penundaan',
                'reason' => 'Sisa masa simpan yang singkat dan/atau risiko operasional yang tinggi membuat penundaan sangat berisiko.',
                'dispatch_deadline' => 'Dalam 6 jam',
                'recommended_vehicle' => $recommendedVehicle,
                'recommended_storage' => $storage,
            ];
        }

        if ($temperatureProblem) {
            return [
                'action' => 'Sesuaikan kondisi suhu pengangkutan komoditas',
                'reason' => $temperatureAssessment['message'],
                'dispatch_deadline' => 'Sebelum atau saat keberangkatan',
                'recommended_vehicle' => $recommendedVehicle,
                'recommended_storage' => $storage,
            ];
        }

        if ($riskScore >= 50 || ($shipment->distance_km ?? 0) >= 300) {
            return [
                'action' => 'Tinjau rute dan kondisi penyimpanan',
                'reason' => 'Pengiriman memiliki paparan perjalanan yang cukup tinggi dan perlu disesuaikan dengan standar penyimpanan komoditas.',
                'dispatch_deadline' => 'Hari ini',
                'recommended_vehicle' => $recommendedVehicle,
                'recommended_storage' => $storage,
            ];
        }

        if ($riskScore >= 30) {
            return [
                'action' => 'Prioritaskan pengiriman',
                'reason' => 'Kondisi pengiriman saat ini masih terkendali, namun sebaiknya diproses lebih dulu dibanding muatan berisiko rendah.',
                'dispatch_deadline' => 'Dalam 24 jam',
                'recommended_vehicle' => $recommendedVehicle,
                'recommended_storage' => $storage,
            ];
        }

        return [
            'action' => 'Pantau pengiriman',
            'reason' => $commodityProfile
                ? 'Tingkat risiko operasional saat ini rendah dan profil komoditas tersedia sebagai panduan penyimpanan.'
                : 'Tingkat risiko operasional saat ini rendah, namun profil komoditas belum tersedia di basis data AgriFlow.',
            'dispatch_deadline' => 'Fleksibel',
            'recommended_vehicle' => $recommendedVehicle,
            'recommended_storage' => $storage,
        ];
    }

    private function getRiskLevel(int $riskScore): string
    {
        return match (true) {
            $riskScore >= 60 => 'High',
            $riskScore >= 30 => 'Moderate',
            default => 'Low',
        };
    }

    private function getPriorityLevel(int $priorityScore): string
    {
        return match (true) {
            $priorityScore >= 60 => 'High',
            $priorityScore >= 40 => 'Moderate',
            default => 'Low',
        };
    }

    private function displayVehicle(string $vehicle): string
    {
        return match (strtolower($vehicle)) {
            'cold', 'refrigerated', 'refrigerated truck' => 'Truk Berpendingin',
            'ship' => 'Kapal',
            'plane' => 'Pesawat',
            default => 'Truk Standar',
        };
    }

    private function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }
}