<?php

namespace App\Services\AI;

use App\Models\CommodityProfile;
use App\Models\Shipment;

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
        $deadline = $riskAssessment['dispatch_deadline'] ?? 'Fleksibel';
        $interventionRequired = (bool) ($riskAssessment['intervention_required'] ?? false);
        $remainingDays = $qualityPrediction['remaining_shelf_life_at_arrival_days'] ?? null;
        $marginHours = $qualityPrediction['transit_margin_hours'] ?? null;
        $safeStatus = $qualityPrediction['safe_transit_status'] ?? 'Unknown';
        $expiryConstraint = (bool) ($qualityPrediction['expiry_constraint_applied'] ?? false);
        $temperatureStatus = $temperatureAssessment['status'] ?? 'Not provided';

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
            'version' => 'step10.4c-three-level-risk-v1',
            'actions' => $actions,
            'primary_action' => $actions[0]['action'] ?? 'Pantau kondisi pengiriman',
            'primary_reason' => $actions[0]['reason'] ?? 'Lanjutkan pemantauan operasional secara berkala.',
            'decision_rationale' => $this->decisionRationale(
                $severity,
                $urgency,
                $qualityPrediction,
                $riskAssessment
            ),
            'expected_outcome' => $this->expectedOutcome($severity, $urgency),
            'action_window' => $deadline,
            'intervention_status' => $interventionRequired
                ? 'Perlu intervensi'
                : 'Pantau',

            'recommended_vehicle' => $this->recommendedVehicle($profile),
            'recommended_storage' => $this->storageSummary($profile),
            'recommended_temperature_range' => $this->temperatureRange($profile),
            'recommended_humidity_range' => $this->humidityRange($profile),
            'chilling_threshold' => $this->chillingThreshold($profile),
            'temperature_control_recommended' => (bool) ($profile?->temperature_control_recommended ?? false),
            'commodity_profile_name' => $profile?->local_name ?: $profile?->name,
            'reference_source_name' => $profile?->source_name,
            'reference_source_url' => $profile?->source_url,

            'quality_model_type' => $profile?->quality_model_type,
            'commodity_class' => $profile?->commodity_class,
            'safe_moisture_short_term_max_percent' => $profile?->safe_moisture_short_term_max_percent,
            'safe_moisture_long_term_max_percent' => $profile?->safe_moisture_long_term_max_percent,
            'safe_relative_humidity_max_percent' => $profile?->safe_relative_humidity_max_percent,
            'reference_storage_max_months' => $profile?->reference_storage_max_months,
            'storage_science_note' => $profile?->storage_science_note,
            'storage_evidence_status' => $this->storageEvidenceStatus($profile, $qualityPrediction),
        ];
    }

    private function primaryAction(string $severity, string $urgency, string $deadline): array
    {
        if ($urgency === 'Immediate') {
            return [
                'type' => 'primary',
                'label' => 'Tindakan operasional segera',
                'action' => 'Segera eskalasi dan tinjau pengiriman ini',
                'reason' => 'Risiko operasional tinggi yang disertai keterbatasan kesegaran atau waktu tempuh memerlukan penanganan segera sebelum terjadi penundaan tambahan.',
                'window' => $deadline,
                'priority' => 1,
            ];
        }

        return match ($severity) {
            'High' => [
                'type' => 'primary',
                'label' => 'Prioritas keberangkatan',
                'action' => 'Prioritaskan keberangkatan dalam rentang waktu mendesak',
                'reason' => 'Risiko operasional yang tinggi membuat waktu tunggu tambahan sangat berisiko bagi mutu produk.',
                'window' => $deadline,
                'priority' => 1,
            ],
            'Moderate' => [
                'type' => 'primary',
                'label' => 'Pemantauan prioritas',
                'action' => 'Utamakan pengiriman ini dibanding muatan berisiko rendah dan periksa ulang dalam 24 jam',
                'reason' => 'Kondisi saat ini masih terkendali, namun pengiriman sebaiknya tidak mengalami penundaan yang dapat dihindari.',
                'window' => $deadline,
                'priority' => 1,
            ],
            default => [
                'type' => 'primary',
                'label' => 'Penanganan rutin',
                'action' => 'Pertahankan jadwal pengiriman yang direncanakan',
                'reason' => 'Tingkat risiko operasional saat ini tergolong rendah berdasarkan data yang tersedia.',
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
        $isDryCommodity = ($profile?->quality_model_type ?? null) === 'storage_stability';

        if (in_array($safeStatus, ['Threshold already exceeded', 'ETA exceeds safe transit window'], true)) {
            return [
                'type' => 'freshness',
                'label' => 'Perlindungan waktu tempuh',
                'action' => 'Kurangi penundaan atau sesuaikan rencana pengangkutan sebelum berangkat',
                'reason' => 'Rencana perjalanan saat ini berada di luar perkiraan waktu tempuh aman operasional.',
                'window' => 'Sebelum keberangkatan',
                'priority' => 2,
            ];
        }

        if ($remainingDays !== null && $remainingDays <= 1.0) {
            return [
                'type' => 'freshness',
                'label' => $isDryCommodity ? 'Perlindungan batas waktu operasional' : 'Perlindungan masa simpan',
                'action' => 'Minimalkan waktu tunggu dan hindari penundaan penanganan yang tidak perlu',
                'reason' => $isDryCommodity
                    ? 'Tersisa satu hari atau kurang sebelum batas waktu operasional tercatat. Nilai ini bukan klaim masa simpan biologis.'
                    : 'Sisa masa simpan operasional saat tiba diperkirakan satu hari atau kurang.',
                'window' => 'Selama siklus pengiriman saat ini',
                'priority' => 2,
            ];
        }

        if ($expiryConstraint) {
            return [
                'type' => 'freshness',
                'label' => $isDryCommodity ? 'Perlindungan batas waktu tercatat' : 'Perlindungan batas kedaluwarsa',
                'action' => $isDryCommodity
                    ? 'Jaga batas waktu operasional tercatat dari penundaan tambahan'
                    : 'Jaga batas masa simpan tercatat dari penundaan tambahan',
                'reason' => $marginHours !== null
                    ? (
$isDryCommodity
    ? sprintf(
        'Batas waktu tercatat menjadi kendala operasional utama dengan sisa waktu cadangan sekitar %.1f jam. Batas ini merupakan acuan operasional, bukan klaim masa simpan biologis.',
        max(0, $marginHours)
    )
    : sprintf(
        'Tanggal kedaluwarsa tercatat menjadi batas utama dengan sisa waktu cadangan perjalanan sekitar %.1f jam.',
        max(0, $marginHours)
    )
                    )
                    : (
                        $isDryCommodity
                            ? 'Terdapat batas waktu operasional tercatat sebagai acuan pengiriman komoditas kering.'
                            : 'Tanggal kedaluwarsa tercatat lebih ketat dibandingkan standar masa simpan komoditas.'
                    ),
                'window' => 'Hingga produk tiba',
                'priority' => 2,
            ];
        }

        return [
            'type' => 'freshness',
            'label' => $isDryCommodity ? 'Pemeriksaan batas operasional' : 'Pemeriksaan kesegaran',
            'action' => $isDryCommodity
                ? 'Periksa ulang batas waktu operasional dan kondisi penyimpanan jika jadwal keberangkatan berubah'
                : 'Periksa ulang kondisi kesegaran jika jadwal keberangkatan berubah signifikan',
            'reason' => $isDryCommodity
                ? 'Stabilitas penyimpanan komoditas kering bergantung pada data kondisi dan batas waktu operasional.'
                : 'Masa simpan dan waktu cadangan perjalanan perlu dihitung ulang jika jadwal keberangkatan berubah.',
            'window' => 'Sebelum perubahan jadwal signifikan',
            'priority' => 2,
        ];
    }

    private function conditionAction(
        ?CommodityProfile $profile,
        string $temperatureStatus,
        array $temperatureAssessment,
        array $qualityPrediction = []
    ): array {
        $isDryCommodity = ($profile?->quality_model_type ?? null) === 'storage_stability';

        if ($isDryCommodity) {
            $storageAssessment = $qualityPrediction['storage_stability_assessment'] ?? [];
            $available = (bool) ($storageAssessment['available'] ?? false);
            $status = $storageAssessment['status'] ?? 'Storage condition evidence required';

            if (!$available) {
                return [
                    'type' => 'condition',
                    'label' => 'Verifikasi kondisi penyimpanan',
                    'action' => 'Periksa kadar air muatan dan kelembapan ruang penyimpanan sebelum berangkat',
                    'reason' => 'Standar kadar air dan kelembapan tersedia untuk komoditas kering ini, tetapi data pengukuran aktual belum dicatat.',
                    'window' => 'Sebelum keberangkatan',
                    'priority' => 3,
                ];
            }

            if ($status === 'Outside reference storage limits') {
                return [
                    'type' => 'condition',
                    'label' => 'Penyesuaian kondisi kering',
                    'action' => 'Sesuaikan paparan kadar air atau kelembapan agar masuk batas acuan penyimpanan sebelum terjadi penundaan',
                    'reason' => $storageAssessment['message'] ?? 'Kondisi penyimpanan tercatat berada di luar batas acuan komoditas kering.',
                    'window' => 'Sebelum atau saat keberangkatan',
                    'priority' => 3,
                ];
            }

            return [
                'type' => 'condition',
                'label' => 'Penjagaan kondisi kering',
                'action' => 'Pertahankan penanganan kering dan lindungi muatan dari kelembapan hingga tiba',
                'reason' => 'Kondisi penyimpanan tercatat masih dalam batas acuan; menjaga kondisi kering mencegah penurunan mutu.',
                'window' => 'Hingga produk tiba',
                'priority' => 3,
            ];
        }

        if ($temperatureStatus === 'Not provided') {
            return [
                'type' => 'condition',
                'label' => 'Verifikasi data suhu',
                'action' => 'Periksa dan catat suhu muatan sebelum berangkat bila memungkinkan',
                'reason' => 'Suhu pengiriman belum dicatat, sehingga masih terdapat ketidakpastian terkait kondisi suhu.',
                'window' => 'Sebelum keberangkatan',
                'priority' => 3,
            ];
        }

        if (in_array($temperatureStatus, ['Chilling risk', 'Above optimum', 'Below optimum'], true)) {
            return [
                'type' => 'condition',
                'label' => 'Penyesuaian suhu',
                'action' => 'Sesuaikan suhu muatan agar mendekati kisaran acuan komoditas',
                'reason' => $temperatureAssessment['message'] ?? 'Suhu produk saat ini berada di luar kisaran acuan komoditas.',
                'window' => 'Sebelum atau saat keberangkatan',
                'priority' => 3,
            ];
        }

        if ($profile?->temperature_control_recommended) {
            return [
                'type' => 'condition',
                'label' => 'Penjagaan suhu dingin',
                'action' => 'Pertahankan penanganan dengan kontrol suhu hingga tiba di tujuan',
                'reason' => 'Profil komoditas menyarankan pengaturan suhu selama penanganan dan pengiriman pascapanen.',
                'window' => 'Hingga produk tiba',
                'priority' => 3,
            ];
        }

        return [
            'type' => 'condition',
            'label' => 'Kontrol penanganan',
            'action' => 'Pertahankan kondisi penyimpanan dan penanganan sesuai standar komoditas',
            'reason' => 'Menjaga kondisi penanganan sesuai profil komoditas membantu mengurangi risiko kerusakan selama perjalanan.',
            'window' => 'Hingga produk tiba',
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
        $primaryDriver = $riskAssessment['top_drivers'][0]['title'] ?? 'kondisi operasional saat ini';

        $isDryCommodity = ($qualityPrediction['condition_model_type'] ?? null) === 'storage_stability';

        $qualityText = $quality !== null
            ? sprintf('perkiraan kondisi saat tiba %.0f/100', $quality)
            : (
                $isDryCommodity
                    ? 'kondisi saat tiba tidak diestimasi untuk model penyimpanan kering'
                    : 'perkiraan kondisi saat tiba belum tersedia'
            );

        $remainingText = $remaining !== null
            ? (
                $isDryCommodity
                    ? sprintf('sisa %.2f hari sebelum batas waktu operasional tercatat', max(0, $remaining))
                    : sprintf('sisa masa simpan operasional %.2f hari', max(0, $remaining))
            )
            : (
                $isDryCommodity
                    ? 'belum ada batas waktu operasional tercatat'
                    : 'sisa masa simpan belum dapat dipastikan'
            );

        return sprintf(
            '%s dengan tingkat urgensi %s terutama dipengaruhi oleh %s, disertai %s dan %s.',
            $this->severityLabel($severity),
            $this->urgencyLabel($urgency),
            strtolower($primaryDriver),
            $qualityText,
            $remainingText
        );
    }

    private function expectedOutcome(string $severity, string $urgency): string
    {
        if ($urgency === 'Immediate') {
            return 'Tindakan ini bertujuan mencegah penundaan tambahan saat pengiriman ditinjau ulang secara segera.';
        }

        return match ($severity) {
            'High' => 'Tindakan ini bertujuan menjaga sisa masa simpan produk dan mencegah penundaan pengiriman yang tidak perlu.',
            'Moderate' => 'Tindakan ini bertujuan menjaga pengiriman tetap dalam batas waktu aman serta mencegah risiko meningkat.',
            default => 'Tindakan ini bertujuan mempertahankan kondisi risiko rendah dan menjaga kelengkapan data untuk pemantauan berikutnya.',
        };
    }

    private function recommendedVehicle(?CommodityProfile $profile): string
    {
        if (($profile?->quality_model_type ?? null) === 'storage_stability') {
            return 'Transportasi kering dan terlindung dari cuaca; cegah masuknya air atau kelembapan';
        }

        return $profile?->temperature_control_recommended
            ? 'Disarankan menggunakan transportasi berpendingin (suhu terkontrol)'
            : 'Transportasi standar dapat digunakan; tetap jaga kondisi penanganan sesuai standar komoditas';
    }

    private function storageSummary(?CommodityProfile $profile): string
    {
        if (!$profile) {
            return 'Gunakan panduan penanganan khusus komoditas yang tervalidasi bila tersedia.';
        }

        if (($profile->quality_model_type ?? null) === 'storage_stability') {
            $parts = [];

            if ($profile->safe_moisture_short_term_max_percent !== null) {
                $parts[] = sprintf('Kadar air ≤ %.1f%% (acuan jangka pendek)', (float) $profile->safe_moisture_short_term_max_percent);
            }

            if ($profile->safe_relative_humidity_max_percent !== null) {
                $parts[] = sprintf('Kelembapan ≤ %.0f%% (acuan)', (float) $profile->safe_relative_humidity_max_percent);
            }

            return $parts !== []
                ? implode('; ', $parts)
                : 'Pertahankan kondisi penyimpanan kering sesuai standar komoditas.';
        }

        $parts = array_values(array_filter([
            $this->temperatureRange($profile),
            $this->humidityRange($profile),
        ]));

        return !empty($parts)
            ? implode('; ', $parts)
            : 'Ikuti standar penyimpanan sesuai profil komoditas.';
    }

    private function temperatureRange(?CommodityProfile $profile): ?string
    {
        if (!$profile || $profile->optimal_temp_min === null || $profile->optimal_temp_max === null) {
            return null;
        }

        $min = (float) $profile->optimal_temp_min;
        $max = (float) $profile->optimal_temp_max;

        if (abs($min - $max) < 0.001) {
            return sprintf('%.1f°C (acuan)', $min);
        }

        return sprintf('%.1f–%.1f°C', $min, $max);
    }

    private function humidityRange(?CommodityProfile $profile): ?string
    {
        if (!$profile || $profile->optimal_humidity_min === null || $profile->optimal_humidity_max === null) {
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
            'Suhu di bawah %.1f°C berisiko menyebabkan kerusakan dingin pada komoditas ini',
            (float) $profile->chilling_threshold_c
        );
    }

    private function storageEvidenceStatus(?CommodityProfile $profile, array $qualityPrediction): ?string
    {
        if (!$profile || ($profile->quality_model_type ?? null) !== 'storage_stability') {
            return null;
        }

        $assessment = $qualityPrediction['storage_stability_assessment'] ?? [];

        if (!($assessment['available'] ?? false)) {
            return 'Data kadar air/kelembapan muatan belum dicatat';
        }

        return match ($assessment['status'] ?? '') {
            'Outside reference storage limits' => 'Di luar batas acuan penyimpanan',
            'Within available reference limits' => 'Sesuai batas acuan penyimpanan',
            default => 'Data kondisi penyimpanan tersedia',
        };
    }

    private function severityLabel(string $severity): string
    {
        return match ($severity) {
            'High' => 'Risiko tinggi',
            'Moderate' => 'Risiko sedang',
            default => 'Risiko rendah',
        };
    }

    private function urgencyLabel(string $urgency): string
    {
        return match ($urgency) {
            'Immediate' => 'segera',
            'High' => 'tinggi',
            'Elevated' => 'meningkat',
            default => 'rutin',
        };
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