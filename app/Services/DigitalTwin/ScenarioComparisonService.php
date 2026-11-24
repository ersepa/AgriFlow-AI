<?php

namespace App\Services\DigitalTwin;

class ScenarioComparisonService
{
    public function compare(array $baseline, array $scenarios): array
    {
        $evaluated = array_map(
            fn (array $scenario): array => $this->evaluateAgainstBaseline($baseline, $scenario),
            $scenarios
        );

        $ranked = collect($evaluated)
            ->sort(function (array $a, array $b): int {
                $decisionRank =
                    $this->decisionRank($b['baseline_decision']['classification'])
                    <=>
                    $this->decisionRank($a['baseline_decision']['classification']);

                if ($decisionRank !== 0) {
                    return $decisionRank;
                }

                $feasibility =
                    $this->feasibilityRank($b['route']['freshness_feasibility'] ?? 'Unavailable')
                    <=>
                    $this->feasibilityRank($a['route']['freshness_feasibility'] ?? 'Unavailable');

                if ($feasibility !== 0) {
                    return $feasibility;
                }

                $risk =
                    ($a['analysis']['risk_score'] ?? 100)
                    <=>
                    ($b['analysis']['risk_score'] ?? 100);

                if ($risk !== 0) {
                    return $risk;
                }

                $aQuality = $a['analysis']['quality_at_arrival'] ?? null;
                $bQuality = $b['analysis']['quality_at_arrival'] ?? null;

                if ($aQuality !== null && $bQuality !== null) {
                    $quality = $bQuality <=> $aQuality;
                    if ($quality !== 0) {
                        return $quality;
                    }
                }

                $margin =
                    ($b['route']['transit_margin_hours'] ?? -INF)
                    <=>
                    ($a['route']['transit_margin_hours'] ?? -INF);

                if ($margin !== 0) {
                    return $margin;
                }

                return
                    ($a['carbon']['estimated_kg'] ?? INF)
                    <=>
                    ($b['carbon']['estimated_kg'] ?? INF);
            })
            ->values()
            ->all();

        $bestScenario = $ranked[0] ?? null;

        $baselineOption = [
            'name' => $baseline['name'] ?? 'Rencana Saat Ini',
            'type' => 'baseline',
            'snapshot' => $baseline,
        ];

        $decision = $this->overallDecision($baseline, $bestScenario);

        return [
            'decision_type' => $decision['type'],
            'decision_status' => $decision['status'],
            'recommended_scenario' => $decision['type'] === 'recommended_over_current' ? $bestScenario : null,
            'preferred_option' => $decision['preferred_option'],
            'current_plan' => $baselineOption,
            'best_available_scenario' => $bestScenario,
            'ranked_scenarios' => $ranked,
            'baseline' => $baseline,
            'decision_reason' => $decision['reason'],
            'baseline_comparison' => $bestScenario['baseline_decision'] ?? null,
        ];
    }

    public function delta(array $baseline, array $scenario): array
    {
        $beforeRisk = $baseline['analysis']['risk_score'] ?? null;
        $afterRisk = $scenario['analysis']['risk_score'] ?? null;
        $beforeQuality = $baseline['analysis']['quality_at_arrival'] ?? null;
        $afterQuality = $scenario['analysis']['quality_at_arrival'] ?? null;
        $beforeCarbon = $baseline['carbon']['estimated_kg'] ?? null;
        $afterCarbon = $scenario['carbon']['estimated_kg'] ?? null;

        return [
            'risk_points' => $this->numericDelta($beforeRisk, $afterRisk),
            'risk_relative_percent' => $this->relativeChangePercent($beforeRisk, $afterRisk),
            'quality_points' => $this->numericDelta($beforeQuality, $afterQuality),
            'transit_margin_hours' => $this->numericDelta(
                $baseline['route']['transit_margin_hours'] ?? null,
                $scenario['route']['transit_margin_hours'] ?? null
            ),
            'carbon_kg' => $this->numericDelta($beforeCarbon, $afterCarbon),
        ];
    }

    public function evaluateAgainstBaseline(array $baseline, array $scenario): array
    {
        $delta = $this->delta($baseline, $scenario);

        $baselineFeasibility = $baseline['route']['freshness_feasibility'] ?? 'Unavailable';
        $scenarioFeasibility = $scenario['route']['freshness_feasibility'] ?? 'Unavailable';

        $baselineRank = $this->feasibilityRank($baselineFeasibility);
        $scenarioRank = $this->feasibilityRank($scenarioFeasibility);

        $classification = 'equivalent_alternative';
        $status = 'Alternatif Setara';
        $preferred = 'current_plan';
        $reason = 'Skenario ini tetap layak dijalankan, namun tidak memberikan peningkatan yang signifikan dibandingkan rencana saat ini.';

        $routeEvidenceUnavailable = $baselineFeasibility === 'Unavailable' || $scenarioFeasibility === 'Unavailable';

        if ($scenarioFeasibility === 'Breach') {
            $classification = 'not_viable';
            $status = 'Tidak Layak';
            $preferred = 'current_plan';
            $reason = 'Skenario ini melewati batas waktu kesegaran produk sehingga tidak direkomendasikan dibandingkan rencana saat ini.';
        } elseif ($routeEvidenceUnavailable) {
            $classification = 'insufficient_evidence';
            $status = 'Data Rute Belum Mencukupi';
            $preferred = 'current_plan';
            $reason = 'Kelayakan rute belum dapat dibandingkan secara akurat karena data waktu cadangan tidak tersedia. Pertahankan rencana saat ini hingga data lengkap.';
        } elseif ($scenarioRank > $baselineRank) {
            $classification = 'recommended_over_current';
            $status = 'Lebih Direkomendasikan';
            $preferred = 'scenario';
            $reason = sprintf(
                'Skenario ini meningkatkan kelayakan rute dari %s menjadi %s sehingga lebih diutamakan dibandingkan rencana saat ini.',
                $this->feasibilityLabel($baselineFeasibility),
                $this->feasibilityLabel($scenarioFeasibility)
            );
        } elseif ($scenarioRank < $baselineRank) {
            $classification = $scenarioFeasibility === 'Tight' ? 'conditional' : 'viable_but_not_recommended';
            $status = $scenarioFeasibility === 'Tight' ? 'Skenario Bersyarat' : 'Layak, Namun Tidak Disarankan';
            $preferred = 'current_plan';
            $reason = sprintf(
                'Skenario ini menurunkan kelayakan rute dari %s menjadi %s, sehingga rencana saat ini tetap lebih baik.',
                $this->feasibilityLabel($baselineFeasibility),
                $this->feasibilityLabel($scenarioFeasibility)
            );
        } else {
            $riskDelta = $delta['risk_points'];
            $qualityDelta = $delta['quality_points'];
            $marginDelta = $delta['transit_margin_hours'];
            $carbonDelta = $delta['carbon_kg'];

            if ($riskDelta !== null && $riskDelta <= -1.0) {
                $classification = 'recommended_over_current';
                $status = 'Lebih Direkomendasikan';
                $preferred = 'scenario';
                $reason = sprintf(
                    'Skenario ini menjaga kelayakan rute pada status %s sekaligus menurunkan risiko operasional sebesar %.1f poin (dari %s/100 menjadi %s/100).',
                    $this->feasibilityLabel($scenarioFeasibility),
                    abs($riskDelta),
                    $baseline['analysis']['risk_score'] ?? '—',
                    $scenario['analysis']['risk_score'] ?? '—'
                );
            } elseif ($riskDelta !== null && $riskDelta >= 1.0) {
                $classification = $scenarioFeasibility === 'Tight' ? 'conditional' : 'viable_but_not_recommended';
                $status = $scenarioFeasibility === 'Tight' ? 'Skenario Bersyarat' : 'Layak, Namun Tidak Disarankan';
                $preferred = 'current_plan';
                $reason = sprintf(
                    'Skenario ini masih layak secara rute (%s), namun meningkatkan risiko operasional sebesar %.1f poin (dari %s/100 menjadi %s/100). Pertahankan rencana saat ini.',
                    $this->feasibilityLabel($scenarioFeasibility),
                    $riskDelta,
                    $baseline['analysis']['risk_score'] ?? '—',
                    $scenario['analysis']['risk_score'] ?? '—'
                );
            } elseif ($qualityDelta !== null && $qualityDelta >= 1.0) {
                $classification = 'recommended_over_current';
                $status = 'Lebih Direkomendasikan';
                $preferred = 'scenario';
                $reason = sprintf(
                    'Tingkat risiko tidak berubah, tetapi perkiraan kondisi produk saat tiba meningkat sebesar %.1f poin.',
                    $qualityDelta
                );
            } elseif ($qualityDelta !== null && $qualityDelta <= -1.0) {
                $classification = 'viable_but_not_recommended';
                $status = 'Layak, Namun Tidak Disarankan';
                $preferred = 'current_plan';
                $reason = sprintf(
                    'Tingkat risiko tidak berubah, tetapi perkiraan kondisi produk saat tiba menurun sebesar %.1f poin sehingga rencana saat ini tetap lebih baik.',
                    abs($qualityDelta)
                );
            } elseif ($marginDelta !== null && $marginDelta >= 0.5) {
                $classification = 'recommended_over_current';
                $status = 'Lebih Direkomendasikan';
                $preferred = 'scenario';
                $reason = sprintf(
                    'Risiko dan kondisi produk relatif sama, tetapi skenario ini memberikan tambahan waktu cadangan pengiriman sebesar %.1f jam.',
                    $marginDelta
                );
            } elseif ($marginDelta !== null && $marginDelta <= -0.5) {
                $classification = 'viable_but_not_recommended';
                $status = 'Layak, Namun Tidak Disarankan';
                $preferred = 'current_plan';
                $reason = sprintf(
                    'Risiko utama tidak berubah, tetapi waktu cadangan pengiriman berkurang sebesar %.1f jam sehingga rencana saat ini tetap lebih baik.',
                    abs($marginDelta)
                );
            } elseif ($carbonDelta !== null && $carbonDelta <= -0.01) {
                $classification = 'recommended_over_current';
                $status = 'Lebih Direkomendasikan';
                $preferred = 'scenario';
                $reason = sprintf(
                    'Keamanan, risiko, kondisi produk, dan waktu cadangan relatif setara, tetapi perkiraan emisi karbon %.2f kg lebih rendah.',
                    abs($carbonDelta)
                );
            } elseif ($carbonDelta !== null && $carbonDelta >= 0.01) {
                $classification = 'viable_but_not_recommended';
                $status = 'Layak, Namun Tidak Disarankan';
                $preferred = 'current_plan';
                $reason = sprintf(
                    'Skenario ini tidak memberikan manfaat operasional tambahan dan justru meningkatkan perkiraan emisi karbon sebesar %.2f kg.',
                    $carbonDelta
                );
            }
        }

        $scenario['baseline_decision'] = [
            'classification' => $classification,
            'status' => $status,
            'preferred_option' => $preferred,
            'reason' => $reason,
            'baseline_feasibility' => $baselineFeasibility,
            'scenario_feasibility' => $scenarioFeasibility,
            'delta' => $delta,
        ];

        return $scenario;
    }

    private function overallDecision(array $baseline, ?array $bestScenario): array
    {
        if (!$bestScenario) {
            return [
                'type' => 'unavailable',
                'status' => 'Perbandingan Skenario Belum Tersedia',
                'preferred_option' => 'current_plan',
                'reason' => 'Belum ada hasil skenario yang tersedia untuk dibandingkan.',
            ];
        }

        $decision = $bestScenario['baseline_decision'];

        return [
            'type' => $decision['classification'],
            'status' => $decision['status'],
            'preferred_option' => $decision['preferred_option'],
            'reason' => $decision['reason'],
        ];
    }

    public function decisionTable(array $baseline, array $scenarios): array
    {
        $evaluated = array_map(
            fn (array $scenario): array => $this->evaluateAgainstBaseline($baseline, $scenario),
            $scenarios
        );

        return array_values(
            array_map(
                function (array $scenario, int $index): array {
                    return [
                        'position' => $index + 1,
                        'name' => $scenario['name'] ?? ('Skenario ' . chr(65 + $index)),
                        'decision_status' => $scenario['baseline_decision']['status'] ?? 'Belum tersedia',
                        'preferred_option' => $scenario['baseline_decision']['preferred_option'] ?? 'current_plan',
                        'risk_score' => $scenario['analysis']['risk_score'] ?? null,
                        'quality_at_arrival' => $scenario['analysis']['quality_at_arrival'] ?? null,
                        'storage_status' => $scenario['analysis']['quality_prediction']['storage_stability_assessment']['status'] ?? null,
                        'feasibility' => $scenario['route']['freshness_feasibility'] ?? 'Unavailable',
                        'transit_margin_hours' => $scenario['route']['transit_margin_hours'] ?? null,
                        'carbon_kg' => $scenario['carbon']['estimated_kg'] ?? null,
                        'evidence_coverage' => $scenario['evidence']['percent'] ?? null,
                        'delta' => $scenario['baseline_decision']['delta'] ?? null,
                    ];
                },
                $evaluated,
                array_keys($evaluated)
            )
        );
    }

    private function feasibilityLabel(string $feasibility): string
    {
        return match ($feasibility) {
            'Safe' => 'Aman',
            'Tight' => 'Ketat',
            'Breach' => 'Melewati Batas',
            default => 'Belum Tersedia',
        };
    }

    private function feasibilityRank(string $feasibility): int
    {
        return match ($feasibility) {
            'Safe' => 3,
            'Tight' => 2,
            'Breach' => 1,
            default => 0,
        };
    }

    private function decisionRank(string $classification): int
    {
        return match ($classification) {
            'recommended_over_current' => 6,
            'equivalent_alternative' => 5,
            'conditional' => 4,
            'viable_but_not_recommended' => 3,
            'insufficient_evidence' => 2,
            'not_viable' => 1,
            default => 0,
        };
    }

    private function numericDelta(mixed $before, mixed $after): ?float
    {
        if (!is_numeric($before) || !is_numeric($after)) {
            return null;
        }

        return round((float) $after - (float) $before, 2);
    }

    private function relativeChangePercent(mixed $before, mixed $after): ?float
    {
        if (!is_numeric($before) || !is_numeric($after) || (float) $before == 0.0) {
            return null;
        }

        return round((((float) $after - (float) $before) / abs((float) $before)) * 100, 1);
    }
}