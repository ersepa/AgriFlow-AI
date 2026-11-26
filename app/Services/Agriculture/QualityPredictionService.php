<?php

namespace App\Services\Agriculture;

use App\Models\CommodityProfile;
use App\Models\Shipment;
use Carbon\Carbon;

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

        if ($profile && ($profile->quality_model_type ?? null) === 'storage_stability') {
            return $this->predictDryCommodity($shipment, $profile, $scenario);
        }

        $baselineShelfLife = $this->baselineShelfLifeDays($profile);
        $harvestAgeDays = $this->harvestAgeDays($shipment);
        $hasHarvestDate = filled($shipment->harvest?->harvest_date);
        $transitHours = $this->scenarioTransitHours($shipment, $scenario);
        $transitDays = $transitHours / 24;

        $temperatureInput = $this->resolveConditionInput(
            $scenario,
            'temperature',
            $shipment->recorded_temperature_c
        );

        $temperature = $temperatureInput['value'];
        $temperatureBasis = $temperatureInput['basis'] === 'unavailable'
            ? 'reference_neutral_fallback'
            : $temperatureInput['basis'];

        $humidityInput = $this->resolveConditionInput(
            $scenario,
            'relative_humidity_percent',
            $shipment->recorded_relative_humidity_percent
        );

        $temperatureAssessment = $this->commodityProfiles->assessTemperature(
            $profile,
            $temperature,
            $transitHours
        );

        $relativeHumidityAssessment = $this->relativeHumidityAssessment(
            $profile,
            $humidityInput['value']
        );

        $conditionAssessment = $this->freshConditionAssessment(
            $shipment,
            $profile,
            $temperatureAssessment,
            $temperatureInput,
            $relativeHumidityAssessment,
            $humidityInput
        );

        $temperatureModel = $this->temperatureDeteriorationModel(
            $profile,
            $temperature,
            $temperatureAssessment
        );

        $effectiveTransitAgeDays =
            ($transitDays * $temperatureModel['deterioration_factor'])
            + $this->chillingEquivalentAgePenaltyDays($transitDays, $temperatureAssessment);

        $effectiveAgeAtDeparture = $harvestAgeDays;
        $effectiveAgeAtArrival = $harvestAgeDays + $effectiveTransitAgeDays;

        $referenceRemainingAtDeparture = $hasHarvestDate && $baselineShelfLife !== null
            ? max(0, $baselineShelfLife - $effectiveAgeAtDeparture)
            : null;

        $referenceRemainingAtArrival = $hasHarvestDate && $baselineShelfLife !== null
            ? max(0, $baselineShelfLife - $effectiveAgeAtArrival)
            : null;

        $referenceQualityAtDeparture = $hasHarvestDate
            ? $this->qualityIndex($baselineShelfLife, $effectiveAgeAtDeparture, 0)
            : null;

        $chillingQualityPenalty = $this->chillingQualityPenalty($temperatureAssessment, $transitHours);

        $referenceQualityAtArrival = $hasHarvestDate
            ? $this->qualityIndex($baselineShelfLife, $effectiveAgeAtArrival, $chillingQualityPenalty)
            : null;

        $recordedWindow = $this->recordedFreshnessWindow($shipment, $transitHours);

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

        $qualityLoss = $qualityAtDeparture !== null && $qualityAtArrival !== null
            ? max(0, $qualityAtDeparture - $qualityAtArrival)
            : null;

        $referenceSafeTransitWindowHours = $hasHarvestDate
            ? $this->safeTransitWindowHours(
                $baselineShelfLife,
                $harvestAgeDays,
                $temperatureModel['deterioration_factor'],
                $temperatureAssessment
            )
            : null;

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

        $safeTransitStatus = $this->safeTransitStatus($safeTransitWindowHours, $transitHours);

        return [
            'model_name' => 'AgriFlow Domain-Informed Quality Model',
            'model_version' => 'step9-recorded-condition-v1',
            'model_type' => 'deterministic_domain_model',

            'baseline_shelf_life_days' => $baselineShelfLife,
            'baseline_shelf_life_basis' => $baselineShelfLife !== null ? 'commodity_profile_midpoint' : 'unavailable',

            'harvest_age_days' => $hasHarvestDate ? round($harvestAgeDays, 2) : null,
            'harvest_age_basis' => $hasHarvestDate ? 'recorded_harvest_date' : 'unavailable_missing_harvest_date',
            'recorded_declared_shelf_life_days' => $recordedDeclaredShelfLifeDays,
            'recorded_declared_shelf_life_hours' => $recordedWindow['declared_window_hours'],
            'recorded_elapsed_at_departure_hours' => $recordedWindow['elapsed_at_departure_hours'],
            'recorded_remaining_days' => $recordedRemainingAtDeparture !== null ? round($recordedRemainingAtDeparture, 2) : null,
            'recorded_remaining_hours' => $recordedWindow['remaining_at_departure_hours'],
            'recorded_remaining_at_arrival_days' => $recordedRemainingAtArrival !== null ? round($recordedRemainingAtArrival, 2) : null,
            'recorded_remaining_at_arrival_hours' => $recordedWindow['remaining_at_arrival_hours'],

            'transit_hours' => round($transitHours, 2),
            'temperature_c' => $temperature,
            'temperature_basis' => $temperatureBasis,
            'temperature_assessment' => $temperatureAssessment,
            'relative_humidity_percent' => $humidityInput['value'],
            'relative_humidity_basis' => $humidityInput['basis'],
            'relative_humidity_assessment' => $relativeHumidityAssessment,
            'condition_assessment' => $conditionAssessment,
            'q10_used' => $temperatureModel['q10_used'],
            'q10_basis' => $temperatureModel['q10_basis'],
            'reference_temperature_c' => $temperatureModel['reference_temperature_c'],
            'temperature_deterioration_factor' => round($temperatureModel['deterioration_factor'], 3),

            'effective_transit_age_days' => round($effectiveTransitAgeDays, 2),
            'effective_age_at_departure_days' => $hasHarvestDate ? round($effectiveAgeAtDeparture, 2) : null,
            'effective_age_at_arrival_days' => $hasHarvestDate ? round($effectiveAgeAtArrival, 2) : null,

            'reference_remaining_shelf_life_at_departure_days' => $referenceRemainingAtDeparture !== null ? round($referenceRemainingAtDeparture, 2) : null,
            'reference_remaining_shelf_life_at_arrival_days' => $referenceRemainingAtArrival !== null ? round($referenceRemainingAtArrival, 2) : null,

            'remaining_shelf_life_at_departure_days' => $operationalRemainingAtDeparture !== null ? round($operationalRemainingAtDeparture, 2) : null,
            'remaining_shelf_life_at_arrival_days' => $operationalRemainingAtArrival !== null ? round($operationalRemainingAtArrival, 2) : null,

            'reference_quality_at_departure' => $referenceQualityAtDeparture,
            'reference_quality_at_arrival' => $referenceQualityAtArrival,
            'recorded_freshness_index_at_departure' => $recordedFreshnessAtDeparture,
            'recorded_freshness_index_at_arrival' => $recordedFreshnessAtArrival,
            'quality_at_departure' => $qualityAtDeparture,
            'quality_at_arrival' => $qualityAtArrival,
            'quality_loss_during_transit' => $qualityLoss,
            'quality_status' => $this->qualityStatus($qualityAtArrival),
            'quality_basis' => !$hasHarvestDate
                ? 'recorded_expiry_operational_only_missing_harvest_date'
                : ($recordedFreshnessAtArrival !== null ? 'conservative_minimum_of_reference_and_recorded_expiry' : 'reference_model_only'),

            'reference_safe_transit_window_hours' => $referenceSafeTransitWindowHours,
            'recorded_expiry_window_hours' => $recordedExpiryWindowHours,
            'safe_transit_window_hours' => $safeTransitWindowHours,
            'planned_transit_hours' => round($transitHours, 2),
            'transit_margin_hours' => $transitMarginHours,
            'safe_transit_status' => $safeTransitStatus,
            'safe_transit_status_label' => $this->safeTransitStatusLabel($safeTransitStatus),

            'expiry_constraint_applied' => $expiryConstraintApplied,
            'shelf_life_reconciliation_status' => $reconciliation['status'],
            'shelf_life_reconciliation_status_label' => $this->reconciliationStatusLabel($reconciliation['status']),
            'shelf_life_reconciliation_message' => $reconciliation['message'],
            'shelf_life_discrepancy_days' => $reconciliation['discrepancy_days'],

            // A recorded harvest-to-expiry window is sufficient for a
            // transparent operational freshness index when no validated
            // commodity shelf-life profile is available.
            'prediction_available' => (
                $baselineShelfLife !== null && $hasHarvestDate
            ) || $recordedFreshnessAtArrival !== null,
            'prediction_unavailable_reason' => (
                ($baselineShelfLife !== null && $hasHarvestDate)
                || $recordedFreshnessAtArrival !== null
            ) ? null : (
                !$hasHarvestDate
                    ? 'Tanggal panen belum diisi sehingga indeks kesegaran belum dapat dihitung.'
                    : 'Profil masa simpan komoditas dan data kedaluwarsa yang diperlukan belum tersedia.'
            ),
            'condition_model_type' => 'shelf_life_quality',
            'operational_window_available' => $safeTransitWindowHours !== null,
            'prediction_basis' => $baselineShelfLife !== null && $hasHarvestDate
                ? 'Profil masa simpan komoditas + usia panen + durasi perjalanan + kondisi pengiriman tercatat atau skenario'
                : ($recordedFreshnessAtArrival !== null
                    ? 'Indeks kesegaran operasional berdasarkan tanggal panen dan kedaluwarsa tercatat; tanpa kurva acuan komoditas'
                    : 'Data acuan masa simpan atau tanggal panen belum lengkap; kualitas belum dapat diestimasi'),

            'source_name' => $profile?->source_name,
            'source_url' => $profile?->source_url,

            'limitations' => [
                'Model ini bersifat deterministik berbasis profil komoditas, bukan model machine learning.',
                'Data kondisi pengiriman merupakan catatan titik waktu operator, bukan data sensor IoT langsung.',
                'Faktor kemasan, tingkat kematangan, kerusakan mekanis, etilen, dan atmosfer belum dimodelkan.',
                'Standar masa simpan merupakan kisaran acuan dan dapat berbeda tergantung varietas serta penanganan.',
                'Tanggal kedaluwarsa tercatat diperlakukan sebagai batas operasional, bukan bukti biologis kerusakan.',
                'Jika standar komoditas dan tanggal kedaluwarsa berbeda, sistem menggunakan batas yang lebih ketat.',
                'Perkiraan kualitas memerlukan tanggal panen tercatat agar hasil tetap akurat.',
            ],
        ];
    }

    private function predictDryCommodity(
        Shipment $shipment,
        CommodityProfile $profile,
        array $scenario
    ): array {
        $transitHours = $this->scenarioTransitHours($shipment, $scenario);
        $recordedWindow = $this->recordedFreshnessWindow($shipment, $transitHours);

        $recordedRemainingAtDeparture = $recordedWindow['remaining_at_departure_days'];
        $recordedRemainingAtArrival = $recordedWindow['remaining_at_arrival_days'];
        $safeTransitWindowHours = $recordedWindow['remaining_at_departure_hours'];
        $transitMarginHours = $safeTransitWindowHours !== null ? round($safeTransitWindowHours - $transitHours, 1) : null;

        $moistureInput = $this->resolveConditionInput($scenario, 'moisture_percent', $shipment->recorded_moisture_percent);
        $humidityInput = $this->resolveConditionInput($scenario, 'relative_humidity_percent', $shipment->recorded_relative_humidity_percent);
        $temperatureInput = $this->resolveConditionInput($scenario, 'temperature', $shipment->recorded_temperature_c);

        $moisture = $moistureInput['value'];
        $relativeHumidity = $humidityInput['value'];
        $storageHorizon = (string) ($scenario['storage_horizon'] ?? 'short_term');

        $storageStability = $this->commodityProfiles->assessStorageStability(
            $profile,
            $moisture,
            $relativeHumidity,
            $storageHorizon
        );

        $conditionAssessment = $this->dryConditionAssessment(
            $shipment,
            $profile,
            $storageStability,
            $moistureInput,
            $humidityInput
        );

        $safeTransitStatus = $this->safeTransitStatus($safeTransitWindowHours, $transitHours);
        $reconciliationStatus = $safeTransitWindowHours !== null
            ? 'Recorded operational deadline only'
            : 'No operational deadline recorded';

        return [
            'model_name' => 'AgriFlow Dry Commodity Storage Stability Model',
            'model_version' => 'step9-recorded-storage-condition-v1',
            'model_type' => 'deterministic_reference_threshold_model',
            'condition_model_type' => 'storage_stability',

            'baseline_shelf_life_days' => null,
            'baseline_shelf_life_basis' => 'not_applicable_to_storage_stability_model',
            'reference_remaining_shelf_life_at_departure_days' => null,
            'reference_remaining_shelf_life_at_arrival_days' => null,
            'reference_quality_at_departure' => null,
            'reference_quality_at_arrival' => null,
            'quality_at_departure' => null,
            'quality_at_arrival' => null,
            'quality_loss_during_transit' => null,
            'quality_status' => 'Tidak diestimasi untuk komoditas kering',
            'quality_basis' => 'no_fabricated_fresh_produce_quality_curve',

            'harvest_age_days' => round($this->harvestAgeDays($shipment), 2),

            'recorded_declared_shelf_life_days' => $recordedWindow['declared_window_days'],
            'recorded_declared_shelf_life_hours' => $recordedWindow['declared_window_hours'],
            'recorded_elapsed_at_departure_hours' => $recordedWindow['elapsed_at_departure_hours'],
            'recorded_remaining_days' => $recordedRemainingAtDeparture,
            'recorded_remaining_hours' => $recordedWindow['remaining_at_departure_hours'],
            'recorded_remaining_at_arrival_days' => $recordedRemainingAtArrival,
            'recorded_remaining_at_arrival_hours' => $recordedWindow['remaining_at_arrival_hours'],

            'remaining_shelf_life_at_departure_days' => $recordedRemainingAtDeparture,
            'remaining_shelf_life_at_arrival_days' => $recordedRemainingAtArrival,

            'recorded_freshness_index_at_departure' => null,
            'recorded_freshness_index_at_arrival' => null,

            'transit_hours' => round($transitHours, 2),
            'planned_transit_hours' => round($transitHours, 2),

            'temperature_c' => $temperatureInput['value'],
            'temperature_basis' => $temperatureInput['basis'],
            'temperature_role' => 'not_used_as_primary_dry_commodity_driver',
            'temperature_assessment' => $this->commodityProfiles->assessTemperature($profile, $temperatureInput['value'], $transitHours),
            'moisture_percent' => $moisture,
            'moisture_basis' => $moistureInput['basis'],
            'relative_humidity_percent' => $relativeHumidity,
            'relative_humidity_basis' => $humidityInput['basis'],
            'condition_assessment' => $conditionAssessment,

            'q10_used' => null,
            'q10_basis' => 'not_applicable',
            'reference_temperature_c' => null,
            'temperature_deterioration_factor' => null,
            'effective_transit_age_days' => null,
            'effective_age_at_departure_days' => null,
            'effective_age_at_arrival_days' => null,

            'reference_safe_transit_window_hours' => null,
            'recorded_expiry_window_hours' => $safeTransitWindowHours,
            'safe_transit_window_hours' => $safeTransitWindowHours,
            'transit_margin_hours' => $transitMarginHours,
            'safe_transit_status' => $safeTransitStatus,
            'safe_transit_status_label' => $this->safeTransitStatusLabel($safeTransitStatus),

            'expiry_constraint_applied' => $safeTransitWindowHours !== null,
            'shelf_life_reconciliation_status' => $reconciliationStatus,
            'shelf_life_reconciliation_status_label' => $this->reconciliationStatusLabel($reconciliationStatus),
            'shelf_life_reconciliation_message' => $safeTransitWindowHours !== null
                ? 'Stabilitas komoditas kering tidak dikonversi menjadi kurva kesegaran produk segar. Tanggal kedaluwarsa hanya digunakan sebagai batas waktu operasional.'
                : 'Belum ada batas waktu tercatat. Kondisi penyimpanan dinilai dari data kadar air dan kelembapan komoditas.',
            'shelf_life_discrepancy_days' => null,

            'prediction_available' => false,
            'operational_window_available' => $safeTransitWindowHours !== null,
            'storage_stability_reference_available' => true,
            'storage_stability_assessment' => $storageStability,

            'prediction_basis' => 'Standar batas penyimpanan komoditas kering yang tervalidasi. Kualitas biologis tidak diestimasi tanpa model komoditas kering khusus.',

            'source_name' => $profile->source_name,
            'source_url' => $profile->source_url,
            'source_references' => $profile->source_references ?? [],

            'limitations' => [
                'Model ini bukan model machine learning terlatih.',
                'Batas kadar air dan kelembapan merupakan acuan penyimpanan, bukan probabilitas pembusukan.',
                'Tanpa data kadar air/kelembapan tercatat, sistem menampilkan keterbatasan data dan tidak mengarang skor kualitas.',
                'Tanggal kedaluwarsa tercatat adalah batas operasional, bukan bukti biologis kerusakan.',
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

        $routeOptimized = filter_var($scenario['route'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($routeOptimized && $duration > 0) {
            $duration *= 0.90;
        }

        $duration += max(0, (float) ($scenario['delay'] ?? 0));

        return $duration;
    }

    private function resolveConditionInput(
        array $scenario,
        string $scenarioKey,
        mixed $recordedValue
    ): array {
        if (array_key_exists($scenarioKey, $scenario) && $scenario[$scenarioKey] !== null && $scenario[$scenarioKey] !== '') {
            return ['value' => (float) $scenario[$scenarioKey], 'basis' => 'scenario_input'];
        }

        if ($recordedValue !== null && $recordedValue !== '') {
            return ['value' => (float) $recordedValue, 'basis' => 'recorded_shipment'];
        }

        return ['value' => null, 'basis' => 'unavailable'];
    }

    private function relativeHumidityAssessment(?CommodityProfile $profile, ?float $relativeHumidityPercent): array
    {
        if ($relativeHumidityPercent === null) {
            return [
                'available' => false,
                'status' => 'Not provided',
                'severity' => 'unknown',
                'relative_humidity_percent' => null,
                'message' => 'Data kelembapan pengiriman belum dicatat.',
            ];
        }

        if (!$profile || $profile->optimal_humidity_min === null || $profile->optimal_humidity_max === null) {
            return [
                'available' => true,
                'status' => 'Humidity reference unavailable',
                'severity' => 'unknown',
                'relative_humidity_percent' => round($relativeHumidityPercent, 1),
                'reference_min_percent' => null,
                'reference_max_percent' => null,
                'message' => 'Kelembapan tercatat, namun profil komoditas belum memiliki kisaran acuan kelembapan.',
            ];
        }

        $min = (float) $profile->optimal_humidity_min;
        $max = (float) $profile->optimal_humidity_max;

        if ($relativeHumidityPercent >= $min && $relativeHumidityPercent <= $max) {
            return [
                'available' => true,
                'status' => 'Within reference',
                'severity' => 'low',
                'relative_humidity_percent' => round($relativeHumidityPercent, 1),
                'reference_min_percent' => $min,
                'reference_max_percent' => $max,
                'message' => sprintf(
                    'Kelembapan %.1f%% berada di dalam kisaran acuan %.0f–%.0f%% untuk %s.',
                    $relativeHumidityPercent,
                    $min,
                    $max,
                    $profile->local_name ?: $profile->name
                ),
            ];
        }

        $direction = $relativeHumidityPercent < $min ? 'di bawah' : 'di atas';

        return [
            'available' => true,
            'status' => 'Outside reference',
            'severity' => 'attention',
            'relative_humidity_percent' => round($relativeHumidityPercent, 1),
            'reference_min_percent' => $min,
            'reference_max_percent' => $max,
            'message' => sprintf(
                'Kelembapan %.1f%% berada %s kisaran acuan %.0f–%.0f%% untuk %s.',
                $relativeHumidityPercent,
                $direction,
                $min,
                $max,
                $profile->local_name ?: $profile->name
            ),
        ];
    }

    private function freshConditionAssessment(
        Shipment $shipment,
        ?CommodityProfile $profile,
        array $temperatureAssessment,
        array $temperatureInput,
        array $humidityAssessment,
        array $humidityInput
    ): array {
        $temperatureStatus = $temperatureAssessment['status'] ?? 'Not provided';
        $humidityStatus = $humidityAssessment['status'] ?? 'Not provided';

        $temperatureOutside = in_array($temperatureStatus, ['Chilling risk', 'Above optimum', 'Below optimum'], true);
        $humidityOutside = $humidityStatus === 'Outside reference';

        $availableCount = count(array_filter([
            $temperatureInput['value'] !== null,
            $humidityInput['value'] !== null,
        ]));

        $overallStatus = match (true) {
            $temperatureOutside || $humidityOutside => 'Outside reference',
            $availableCount === 0 => 'Condition evidence unavailable',
            default => 'Within available reference inputs',
        };

        $primaryDriver = match (true) {
            $temperatureOutside => $temperatureAssessment['message'] ?? 'Suhu berada di luar batas acuan komoditas.',
            $humidityOutside => $humidityAssessment['message'] ?? 'Kelembapan berada di luar batas acuan komoditas.',
            $availableCount === 0 => 'Data suhu dan kelembapan pengiriman belum tersedia.',
            default => 'Data kondisi tercatat masih berada di dalam batas acuan komoditas.',
        };

        return [
            'model_name' => 'AgriFlow Recorded Cold-Chain Condition Assessment',
            'model_version' => 'step9-recorded-condition-v1',
            'condition_model_type' => 'cold_chain',
            'evidence_status' => $this->conditionEvidenceStatus([$temperatureInput, $humidityInput]),
            'overall_status' => $overallStatus,
            'primary_driver' => $primaryDriver,
            'temperature' => array_merge(
                $temperatureAssessment,
                [
                    'basis' => $temperatureInput['basis'],
                    'reference_min_c' => $profile?->optimal_temp_min !== null ? (float) $profile->optimal_temp_min : null,
                    'reference_max_c' => $profile?->optimal_temp_max !== null ? (float) $profile->optimal_temp_max : null,
                ]
            ),
            'relative_humidity' => array_merge($humidityAssessment, ['basis' => $humidityInput['basis']]),
            'recorded_condition_source' => $shipment->condition_source,
            'recorded_at' => $shipment->condition_recorded_at?->toIso8601String(),
            'source_name' => $profile?->source_name,
            'source_url' => $profile?->source_url,
            'is_live_sensor_data' => false,
        ];
    }

    private function dryConditionAssessment(
        Shipment $shipment,
        CommodityProfile $profile,
        array $storageStability,
        array $moistureInput,
        array $humidityInput
    ): array {
        $available = (bool) ($storageStability['available'] ?? false);
        $outside = ($storageStability['status'] ?? null) === 'Outside reference storage limits';

        return [
            'model_name' => 'AgriFlow Recorded Storage-Stability Condition Assessment',
            'model_version' => 'step9-recorded-condition-v1',
            'condition_model_type' => 'storage_stability',
            'evidence_status' => $this->conditionEvidenceStatus([$moistureInput, $humidityInput]),
            'overall_status' => match (true) {
                $outside => 'Outside reference',
                !$available => 'Condition evidence unavailable',
                default => 'Within available reference inputs',
            },
            'primary_driver' => $storageStability['message'] ?? 'Data kondisi penyimpanan komoditas kering belum tersedia.',
            'moisture' => [
                'value_percent' => $moistureInput['value'],
                'basis' => $moistureInput['basis'],
                'reference_limit_percent' => $storageStability['moisture_limit_percent'] ?? null,
            ],
            'relative_humidity' => [
                'value_percent' => $humidityInput['value'],
                'basis' => $humidityInput['basis'],
                'reference_limit_percent' => $storageStability['relative_humidity_limit_percent'] ?? null,
            ],
            'recorded_condition_source' => $shipment->condition_source,
            'recorded_at' => $shipment->condition_recorded_at?->toIso8601String(),
            'source_name' => $profile->source_name,
            'source_url' => $profile->source_url,
            'is_live_sensor_data' => false,
        ];
    }

    private function conditionEvidenceStatus(array $inputs): string
    {
        $available = array_values(array_filter($inputs, fn (array $input) => $input['value'] !== null));

        if ($available === []) {
            return 'Belum tersedia';
        }

        $hasScenario = collect($available)->contains(fn (array $input) => $input['basis'] === 'scenario_input');
        $hasRecorded = collect($available)->contains(fn (array $input) => $input['basis'] === 'recorded_shipment');
        $partial = count($available) < count($inputs);

        if ($hasScenario && $hasRecorded) {
            return $partial ? 'Sebagian skenario + data tercatat' : 'Skenario + data tercatat';
        }

        if ($hasScenario) {
            return $partial ? 'Sebagian data skenario' : 'Data skenario';
        }

        return $partial ? 'Sebagian data tercatat' : 'Data tercatat';
    }

    private function temperatureDeteriorationModel(
        ?CommodityProfile $profile,
        ?float $temperature,
        array $assessment
    ): array {
        $q10 = $profile?->q10_factor !== null ? (float) $profile->q10_factor : self::DEFAULT_Q10;
        $q10Basis = $profile?->q10_factor !== null ? 'commodity_profile' : 'generic_fao_rule_of_thumb';
        $referenceTemperature = $profile?->optimal_temp_max !== null ? (float) $profile->optimal_temp_max : null;

        if ($temperature === null || $referenceTemperature === null) {
            return [
                'q10_used' => $q10,
                'q10_basis' => $q10Basis,
                'reference_temperature_c' => $referenceTemperature,
                'deterioration_factor' => 1.0,
            ];
        }

        $status = $assessment['status'] ?? 'Unknown';

        if (in_array($status, ['Chilling risk', 'Below optimum'], true) || $temperature <= $referenceTemperature) {
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

    private function chillingEquivalentAgePenaltyDays(float $transitDays, array $assessment): float
    {
        if (($assessment['status'] ?? '') !== 'Chilling risk') {
            return 0.0;
        }

        $riskModifier = max(0, (int) ($assessment['risk_modifier'] ?? 0));
        $multiplier = min(1.5, $riskModifier / 12);

        return $transitDays * $multiplier;
    }

    private function chillingQualityPenalty(array $assessment, float $transitHours): int
    {
        if (($assessment['status'] ?? '') !== 'Chilling risk') {
            return 0;
        }

        $riskModifier = max(0, (int) ($assessment['risk_modifier'] ?? 0));
        $durationFactor = min(1.5, max(0.5, $transitHours / 12));

        return (int) round(min(25, ($riskModifier * 0.55) * $durationFactor));
    }

    private function qualityIndex(?float $baselineShelfLife, float $effectiveAgeDays, int $additionalPenalty): ?int
    {
        if ($baselineShelfLife === null || $baselineShelfLife <= 0) {
            return null;
        }

        $lifeFractionRemaining = max(0, 1 - ($effectiveAgeDays / $baselineShelfLife));
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

        $minimumFractionRemaining = (self::ACCEPTABLE_QUALITY_THRESHOLD / 100) ** (1 / 0.85);
        $maximumEffectiveAge = $baselineShelfLife * (1 - $minimumFractionRemaining);
        $remainingEffectiveAge = max(0, $maximumEffectiveAge - $harvestAgeDays);
        $effectiveRatePerDay = max(0.01, $deteriorationFactor);

        if (($assessment['status'] ?? '') === 'Chilling risk') {
            $riskModifier = max(0, (int) ($assessment['risk_modifier'] ?? 0));
            $effectiveRatePerDay += min(1.5, $riskModifier / 12);
        }

        return round(($remainingEffectiveAge / $effectiveRatePerDay) * 24, 1);
    }

    private function recordedFreshnessWindow(Shipment $shipment, float $transitHours): array
    {
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

        $remainingDepartureHours = round(max(0, $now->diffInMinutes($expiryAt, false) / 60), 2);
        $remainingArrivalHours = round(max(0, $arrivalAt->diffInMinutes($expiryAt, false) / 60), 2);

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
        $declaredWindowHours = max(1.0, $harvestedAt->diffInMinutes($expiryAt, false) / 60);
        $elapsedDepartureHours = max(0, $harvestedAt->diffInMinutes($now, false) / 60);

        return [
            'declared_window_days' => round($declaredWindowHours / 24, 2),
            'declared_window_hours' => round($declaredWindowHours, 2),
            'elapsed_at_departure_hours' => round($elapsedDepartureHours, 2),
            'remaining_at_departure_days' => round($remainingDepartureHours / 24, 3),
            'remaining_at_departure_hours' => $remainingDepartureHours,
            'remaining_at_arrival_days' => round($remainingArrivalHours / 24, 3),
            'remaining_at_arrival_hours' => $remainingArrivalHours,
            'freshness_at_departure' => $this->recordedFreshnessIndex($remainingDepartureHours, $declaredWindowHours),
            'freshness_at_arrival' => $this->recordedFreshnessIndex($remainingArrivalHours, $declaredWindowHours),
        ];
    }

    private function recordedFreshnessIndex(?float $remainingHours, ?float $declaredWindowHours): ?int
    {
        if ($remainingHours === null || $declaredWindowHours === null) {
            return null;
        }

        if ($remainingHours <= 0) {
            return 0;
        }

        $fractionRemaining = max(0, min(1, $remainingHours / max(1.0, $declaredWindowHours)));
        return $this->clamp((int) round(100 * ($fractionRemaining ** 0.85)), 0, 100);
    }

    private function conservativeRemainingDays(?float $referenceRemainingDays, ?float $recordedRemainingDays): ?float
    {
        $recorded = $recordedRemainingDays !== null ? max(0, $recordedRemainingDays) : null;

        if ($referenceRemainingDays === null) {
            return $recorded;
        }

        if ($recorded === null) {
            return max(0, $referenceRemainingDays);
        }

        return min(max(0, $referenceRemainingDays), $recorded);
    }

    private function conservativeQualityIndex(?int $referenceQuality, ?int $recordedFreshnessIndex): ?int
    {
        // If a validated commodity reference curve is unavailable, use the
        // recorded harvest-to-expiry index rather than discarding valid data.
        if ($referenceQuality === null) {
            return $recordedFreshnessIndex;
        }

        if ($recordedFreshnessIndex === null) {
            return $referenceQuality;
        }

        return min($referenceQuality, $recordedFreshnessIndex);
    }

    private function conservativeSafeWindowHours(?float $referenceWindowHours, ?float $recordedExpiryWindowHours): ?float
    {
        if ($referenceWindowHours === null) {
            return $recordedExpiryWindowHours;
        }

        if ($recordedExpiryWindowHours === null) {
            return $referenceWindowHours;
        }

        return round(min(max(0, $referenceWindowHours), max(0, $recordedExpiryWindowHours)), 1);
    }

    private function recordedConstraintIsLimiting(?float $referenceRemainingDays, ?float $recordedRemainingDays): bool
    {
        if ($recordedRemainingDays === null) {
            return false;
        }

        if ($referenceRemainingDays === null) {
            return true;
        }

        return max(0, $recordedRemainingDays) + self::RECONCILIATION_EPSILON_DAYS < max(0, $referenceRemainingDays);
    }

    private function reconciliationStatus(?float $referenceRemainingDays, ?float $recordedRemainingDays): array
    {
        if ($recordedRemainingDays === null) {
            return [
                'status' => 'Reference model only',
                'message' => 'Tanggal kedaluwarsa belum dicatat; masa simpan operasional menggunakan standar komoditas.',
                'discrepancy_days' => null,
            ];
        }

        if ($referenceRemainingDays === null) {
            return [
                'status' => 'Recorded expiry only',
                'message' => 'Standar masa simpan komoditas belum tersedia; tanggal kedaluwarsa tercatat digunakan sebagai batas operasional.',
                'discrepancy_days' => null,
            ];
        }

        $recorded = max(0, $recordedRemainingDays);
        $reference = max(0, $referenceRemainingDays);
        $difference = round($reference - $recorded, 2);

        if (abs($difference) <= self::RECONCILIATION_EPSILON_DAYS) {
            return [
                'status' => 'Aligned',
                'message' => 'Tanggal kedaluwarsa tercatat dan standar masa simpan komoditas sudah selaras.',
                'discrepancy_days' => 0.0,
            ];
        }

        if ($recorded < $reference) {
            return [
                'status' => $recorded <= 0 ? 'Recorded expiry reached' : 'Recorded expiry is limiting',
                'message' => $recorded <= 0
                    ? 'Batas kedaluwarsa tercatat telah tercapai sehingga masa simpan operasional dibatasi.'
                    : 'Tanggal kedaluwarsa tercatat lebih ketat dibandingkan standar komoditas, sehingga AgriFlow menggunakannya sebagai batas operasional.',
                'discrepancy_days' => abs($difference),
            ];
        }

        return [
            'status' => 'Reference model is limiting',
            'message' => 'Standar masa simpan komoditas lebih ketat dibandingkan tanggal kedaluwarsa tercatat, sehingga AgriFlow menggunakan standar komoditas.',
            'discrepancy_days' => abs($difference),
        ];
    }

    private function reconciliationStatusLabel(string $status): string
    {
        return match ($status) {
            'Reference model only' => 'Menggunakan standar komoditas',
            'Recorded expiry only' => 'Menggunakan tanggal kedaluwarsa tercatat',
            'Aligned' => 'Selaras',
            'Recorded expiry reached' => 'Batas kedaluwarsa tercapai',
            'Recorded expiry is limiting' => 'Dibatasi tanggal kedaluwarsa',
            'Reference model is limiting' => 'Dibatasi standar komoditas',
            'Recorded operational deadline only' => 'Menggunakan batas waktu operasional tercatat',
            'No operational deadline recorded' => 'Belum ada batas waktu operasional',
            default => 'Tidak diketahui',
        };
    }

    private function qualityStatus(?int $quality): string
    {
        if ($quality === null) {
            return 'Belum tersedia';
        }

        return match (true) {
            $quality >= 85 => 'Sangat Baik',
            $quality >= 70 => 'Baik',
            $quality >= 50 => 'Berisiko',
            $quality >= 30 => 'Kurang Baik',
            default => 'Sangat Kurang',
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

    private function safeTransitStatusLabel(string $status): string
    {
        return match ($status) {
            'Threshold already exceeded' => 'Batas aman sudah terlewati',
            'ETA exceeds safe transit window' => 'Durasi melebihi batas aman',
            'Tight margin' => 'Waktu cadangan ketat',
            'Within estimated safe window' => 'Dalam batas waktu aman',
            default => 'Belum diketahui',
        };
    }

    private function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }
}