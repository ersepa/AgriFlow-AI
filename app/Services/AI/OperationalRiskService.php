<?php

namespace App\Services\AI;

use App\Models\CommodityProfile;
use App\Models\Shipment;

class OperationalRiskService
{
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
            'temperature_exposure' => $this->temperaturePressure($temperatureAssessment),
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
        $urgency = $this->urgency($finalScore, $qualityPrediction);

        $topDrivers = collect($weighted)
            ->sortByDesc('contribution')
            ->take(3)
            ->values()
            ->all();

        return [
            'model_name' => 'AgriFlow Operational Risk Engine',
'model_version' => 'step10.4c-three-level-risk-v1',
'model_type' => 'deterministic_weighted_risk_model',

// Explicit score interpretation
'score_methodology' => 'Deterministic weighted operational risk index',
'score_is_probability' => false,
'score_interpretation' => 'Non-probabilistic operational risk index (0-100), not a statistical probability of spoilage.',

'risk_score' => $finalScore,
            'base_risk_score' => $baseScore,
            'risk_level' => $this->compatibilityLevel($finalScore),
            'risk_severity' => $severity,
            'critical_override_applied' => $override['floor'] > $baseScore,
            'critical_override_floor' => $override['floor'],
            'critical_override_reason' => $override['reason'],
            'components' => $weighted,
'weighted_components' => $weighted,
'top_drivers' => $topDrivers,
            'urgency_level' => $urgency['level'],
            'urgency_rank' => $urgency['rank'],
            'intervention_required' => $urgency['intervention_required'],
            'dispatch_deadline' => $urgency['dispatch_deadline'],
            'urgency_reason' => $urgency['reason'],
            'limitations' => [
                'Skor ini adalah indeks risiko operasional, bukan probabilitas pembusukan statistik.',
                'Model bersifat deterministik dan bukan hasil pelatihan machine learning.',
                'Data sensor kontinu, kondisi kemasan, kerusakan mekanis, atmosfer, dan mikroba belum termasuk dalam perhitungan.',
                'Paparan suhu menggunakan nilai skenario Digital Twin jika diisi, atau suhu pengiriman tercatat jika tersedia.',
                'Untuk komoditas kering, data kadar air/kelembapan yang belum tercatat ditampilkan sebagai keterbatasan data, bukan skor kesegaran buatan.',
            ],
        ];
    }

    private function arrivalQualityPressure(
        ?float $quality,
        ?CommodityProfile $profile,
        array $qualityPrediction = []
    ): array {
        $dryModel = ($profile?->quality_model_type ?? null) === 'storage_stability';

        if ($dryModel) {
            $storageAssessment = $qualityPrediction['storage_stability_assessment'] ?? [];
            $available = (bool) ($storageAssessment['available'] ?? false);
            $status = $storageAssessment['status'] ?? 'Storage condition evidence required';

            $score = match (true) {
                !$available => 45,
                $status === 'Outside reference storage limits' => 90,
                $status === 'Within available reference limits' => 10,
                default => 45,
            };

            return [
                'title' => 'Data Kondisi Penyimpanan',
                'icon' => 'quality',
                'score' => $score,
                'reason' => $storageAssessment['message']
                    ?? 'Penilaian komoditas kering memerlukan data kadar air atau kelembapan; skor kesegaran produk segar tidak dibuat secara buatan.',
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
            'title' => 'Tekanan Kondisi Saat Tiba',
            'icon' => 'quality',
            'score' => $score,
            'reason' => $quality === null
                ? 'Perkiraan kondisi saat tiba belum tersedia, sehingga sistem menerapkan nilai kehati-hatian.'
                : sprintf(
                    'Perkiraan kondisi produk saat tiba adalah %.0f/100; kondisi yang lebih rendah meningkatkan risiko penurunan mutu pascapanen.',
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

        $dryModel = ($profile?->quality_model_type ?? null) === 'storage_stability';

        return [
            'title' => $dryModel ? 'Tekanan Batas Waktu Operasional' : 'Tekanan Masa Simpan',
            'icon' => 'shelf-life',
            'score' => $score,
            'reason' => $remainingDays === null
                ? (
                    $dryModel
                        ? 'Belum ada batas waktu operasional tercatat untuk komoditas kering ini.'
                        : 'Sisa masa simpan operasional belum tersedia.'
                )
                : (
                    $dryModel
                        ? sprintf(
                            'Tersisa sekitar %.2f hari sebelum batas waktu operasional tercatat.',
                            max(0, $remainingDays)
                        )
                        : sprintf(
                            'Diperkirakan tersisa sekitar %.2f hari masa simpan operasional saat tiba%s.',
                            max(0, $remainingDays),
                            $expiryConstraintApplied ? ', dengan tanggal kedaluwarsa tercatat sebagai batas utama' : ''
                        )
                ),
        ];
    }

    private function transitMarginPressure(?float $marginHours, string $safeTransitStatus): array
    {
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
            'title' => 'Tekanan Waktu Cadangan Perjalanan',
            'icon' => 'transit-margin',
            'score' => $score,
            'reason' => $marginHours === null
                ? 'Waktu cadangan perjalanan belum dapat dihitung dari data masa simpan yang tersedia.'
                : sprintf(
                    'Pengiriman diperkirakan memiliki sisa waktu cadangan operasional sebesar %.1f jam setelah durasi perjalanan.',
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
            'title' => 'Paparan Suhu',
            'icon' => 'temperature',
            'score' => $score,
            'reason' => $assessment['message'] ?? 'Penilaian suhu muatan belum tersedia.',
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

        $levelIndo = match ($level) {
            'very high' => 'sangat tinggi',
            'high' => 'tinggi',
            'moderate' => 'sedang',
            'low' => 'rendah',
            default => 'belum diketahui',
        };

        $dryModel = ($profile?->quality_model_type ?? null) === 'storage_stability';

        return [
            'title' => $dryModel ? 'Sensitivitas Penyimpanan Komoditas' : 'Tingkat Kerentanan Komoditas',
            'icon' => 'commodity',
            'score' => $score,
            'reason' => $profile
                ? (
                    $dryModel
                        ? sprintf(
                            '%s menggunakan batas penyimpanan komoditas kering sebagai indikator sensitivitas operasional.',
                            $profile->local_name ?: $profile->name
                        )
                        : sprintf(
                            '%s termasuk komoditas dengan tingkat kerentanan %s dalam profil AgriFlow.',
                            $profile->local_name ?: $profile->name,
                            $levelIndo
                        )
                )
                : 'Profil komoditas tervalidasi belum tersedia, sehingga sistem menerapkan nilai kehati-hatian.',
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

        $statusIndo = match ($status) {
            'Harvested' => 'Baru Dipanen',
            'Packed' => 'Dikemas (Siap Kirim)',
            'In Transit' => 'Dalam Perjalanan',
            'Delivered' => 'Selesai Dikirim',
            default => 'tidak diketahui',
        };

        return [
            'title' => 'Tahap Pengiriman',
            'icon' => 'stage',
            'score' => $score,
            'reason' => sprintf(
                'Pengiriman saat ini berada pada tahap %s; tahap awal umumnya masih memiliki lebih banyak risiko keputusan operasional.',
                $statusIndo
            ),
        ];
    }

    private function transportExposurePressure(Shipment $shipment): array
    {
        $distanceAvailable = $shipment->distance_km !== null;
        $durationAvailable = $shipment->duration_hours !== null;

        $distance = $distanceAvailable ? max(0, (float) $shipment->distance_km) : null;
        $duration = $durationAvailable ? max(0, (float) $shipment->duration_hours) : null;

        $distanceScore = $distanceAvailable
            ? match (true) {
                $distance >= 1000 => 100,
                $distance >= 500 => 80,
                $distance >= 300 => 65,
                $distance >= 100 => 35,
                $distance > 0 => 15,
                default => 0,
            }
            : 40;

        $durationScore = $durationAvailable
            ? match (true) {
                $duration >= 24 => 100,
                $duration >= 12 => 80,
                $duration >= 8 => 65,
                $duration >= 4 => 45,
                $duration > 0 => 20,
                default => 0,
            }
            : 40;

        $score = (int) round(($distanceScore + $durationScore) / 2);

        $distanceText = $distanceAvailable ? sprintf('%.0f km', $distance) : 'belum tersedia';
        $durationText = $durationAvailable ? sprintf('%.1f jam', $duration) : 'belum tersedia';

        return [
            'title' => 'Paparan Perjalanan',
            'icon' => 'transport',
            'score' => $score,
            'reason' => sprintf(
                'Data paparan perjalanan: jarak %s dan durasi %s. Data yang kosong diperlakukan sebagai ketidakpastian, bukan nol risiko.',
                $distanceText,
                $durationText
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
                'reason' => 'Tanggal kedaluwarsa tercatat sudah tercapai.',
            ];
        }

        if ($safeStatus === 'Threshold already exceeded') {
            return [
                'floor' => 92,
                'reason' => 'Batas waktu tempuh aman operasional sudah terlewati.',
            ];
        }

        if ($safeStatus === 'ETA exceeds safe transit window') {
            return [
                'floor' => 88,
                'reason' => 'Estimasi durasi perjalanan melebihi batas waktu tempuh aman operasional.',
            ];
        }

        if ($remainingDays !== null && $remainingDays <= 0) {
            return [
                'floor' => 90,
                'reason' => 'Diperkirakan tidak ada sisa masa simpan operasional saat produk tiba.',
            ];
        }

        if ($quality !== null && $quality < 30) {
            return [
                'floor' => 85,
                'reason' => 'Perkiraan kondisi produk saat tiba mengalami penurunan mutu yang sangat parah.',
            ];
        }

        return [
            'floor' => 0,
            'reason' => 'Tidak diperlukan batas pengaman risiko tambahan.',
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
                'dispatch_deadline' => 'Tinjauan operasional segera',
                'reason' => 'Keterbatasan kesegaran atau waktu tempuh yang serius memerlukan intervensi segera.',
            ];
        }

if ($riskScore >= 60) {
    return [
        'level' => 'High',
        'rank' => 3,
        'intervention_required' => true,
        'dispatch_deadline' => 'Dalam 6 jam',
        'reason' => 'Risiko operasional tinggi memerlukan prioritas penanganan dan percepatan dispatch.',
    ];
}

        if ($riskScore >= 30 || ($remainingHours !== null && $remainingHours <= 72)) {
            return [
                'level' => 'Elevated',
                'rank' => 2,
                'intervention_required' => false,
                'dispatch_deadline' => 'Dalam 24 jam',
                'reason' => 'Pengiriman perlu dipantau dan ditinjau ulang dalam 24 jam, namun belum memerlukan intervensi segera.',
            ];
        }

        return [
            'level' => 'Routine',
            'rank' => 1,
            'intervention_required' => false,
            'dispatch_deadline' => 'Fleksibel',
            'reason' => 'Tingkat paparan risiko operasional saat ini rendah berdasarkan data yang tersedia.',
        ];
    }

    private function severity(int $score): string
    {
        return match (true) {
            $score >= 60 => 'High',
            $score >= 30 => 'Moderate',
            default => 'Low',
        };
    }

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