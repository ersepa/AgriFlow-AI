<?php

namespace App\Services\DigitalTwin;

class ScenarioComparisonService
{
    /**
     * Step 6.1 baseline-aware decision semantics.
     *
     * The current plan is always treated as a real decision option.
     * A scenario is never called "recommended" merely because it is
     * the only simulated scenario.
     */
    public function compare(
        array $baseline,
        array $scenarios
    ): array {
        $evaluated = array_map(
            fn (array $scenario): array =>
                $this->evaluateAgainstBaseline(
                    $baseline,
                    $scenario
                ),
            $scenarios
        );

        $ranked = collect($evaluated)
            ->sort(function (
                array $a,
                array $b
            ): int {
                $decisionRank =
                    $this->decisionRank(
                        $b['baseline_decision'][
                            'classification'
                        ]
                    )
                    <=>
                    $this->decisionRank(
                        $a['baseline_decision'][
                            'classification'
                        ]
                    );

                if ($decisionRank !== 0) {
                    return $decisionRank;
                }

                $feasibility =
                    $this->feasibilityRank(
                        $b['route'][
                            'freshness_feasibility'
                        ] ?? 'Unavailable'
                    )
                    <=>
                    $this->feasibilityRank(
                        $a['route'][
                            'freshness_feasibility'
                        ] ?? 'Unavailable'
                    );

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

                $aQuality =
                    $a['analysis'][
                        'quality_at_arrival'
                    ] ?? null;

                $bQuality =
                    $b['analysis'][
                        'quality_at_arrival'
                    ] ?? null;

                if (
                    $aQuality !== null
                    && $bQuality !== null
                ) {
                    $quality =
                        $bQuality
                        <=>
                        $aQuality;

                    if ($quality !== 0) {
                        return $quality;
                    }
                }

                $margin =
                    ($b['route'][
                        'transit_margin_hours'
                    ] ?? -INF)
                    <=>
                    ($a['route'][
                        'transit_margin_hours'
                    ] ?? -INF);

                if ($margin !== 0) {
                    return $margin;
                }

                return
                    ($a['carbon'][
                        'estimated_kg'
                    ] ?? INF)
                    <=>
                    ($b['carbon'][
                        'estimated_kg'
                    ] ?? INF);
            })
            ->values()
            ->all();

        $bestScenario =
            $ranked[0]
            ?? null;

        $baselineOption = [
            'name' =>
                $baseline['name']
                ?? 'Current Plan',
            'type' =>
                'baseline',
            'snapshot' =>
                $baseline,
        ];

        $decision =
            $this->overallDecision(
                $baseline,
                $bestScenario
            );

        return [
            'decision_type' =>
                $decision['type'],
            'decision_status' =>
                $decision['status'],
            'recommended_scenario' =>
                $decision['type']
                    === 'recommended_over_current'
                        ? $bestScenario
                        : null,
            'preferred_option' =>
                $decision['preferred_option'],
            'current_plan' =>
                $baselineOption,
            'best_available_scenario' =>
                $bestScenario,
            'ranked_scenarios' =>
                $ranked,
            'baseline' =>
                $baseline,
            'decision_reason' =>
                $decision['reason'],
            'baseline_comparison' =>
                $bestScenario[
                    'baseline_decision'
                ] ?? null,
        ];
    }

    public function delta(
        array $baseline,
        array $scenario
    ): array {
        $beforeRisk =
            $baseline['analysis']['risk_score']
            ?? null;

        $afterRisk =
            $scenario['analysis']['risk_score']
            ?? null;

        $beforeQuality =
            $baseline['analysis'][
                'quality_at_arrival'
            ] ?? null;

        $afterQuality =
            $scenario['analysis'][
                'quality_at_arrival'
            ] ?? null;

        $beforeCarbon =
            $baseline['carbon'][
                'estimated_kg'
            ] ?? null;

        $afterCarbon =
            $scenario['carbon'][
                'estimated_kg'
            ] ?? null;

        return [
            'risk_points' =>
                $this->numericDelta(
                    $beforeRisk,
                    $afterRisk
                ),
            'risk_relative_percent' =>
                $this->relativeChangePercent(
                    $beforeRisk,
                    $afterRisk
                ),
            'quality_points' =>
                $this->numericDelta(
                    $beforeQuality,
                    $afterQuality
                ),
            'transit_margin_hours' =>
                $this->numericDelta(
                    $baseline['route'][
                        'transit_margin_hours'
                    ] ?? null,
                    $scenario['route'][
                        'transit_margin_hours'
                    ] ?? null
                ),
            'carbon_kg' =>
                $this->numericDelta(
                    $beforeCarbon,
                    $afterCarbon
                ),
        ];
    }

    public function evaluateAgainstBaseline(
        array $baseline,
        array $scenario
    ): array {
        $delta =
            $this->delta(
                $baseline,
                $scenario
            );

        $baselineFeasibility =
            $baseline['route'][
                'freshness_feasibility'
            ] ?? 'Unavailable';

        $scenarioFeasibility =
            $scenario['route'][
                'freshness_feasibility'
            ] ?? 'Unavailable';

        $baselineRank =
            $this->feasibilityRank(
                $baselineFeasibility
            );

        $scenarioRank =
            $this->feasibilityRank(
                $scenarioFeasibility
            );

        $classification =
            'equivalent_alternative';

        $status =
            'Viable Alternative';

        $preferred =
            'current_plan';

        $reason =
            'The scenario remains operationally viable but does not provide a material improvement over the current plan.';

        if ($scenarioFeasibility === 'Breach') {
            $classification =
                'not_viable';
            $status =
                'Not Viable';
            $preferred =
                'current_plan';
            $reason =
                'The scenario breaches the current operational freshness window and must not be recommended over the current plan.';
        } elseif (
            $scenarioRank > $baselineRank
        ) {
            $classification =
                'recommended_over_current';
            $status =
                'Recommended over Current Plan';
            $preferred =
                'scenario';
            $reason =
                sprintf(
                    'The scenario improves route feasibility from %s to %s and is therefore preferred before secondary trade-offs are considered.',
                    $baselineFeasibility,
                    $scenarioFeasibility
                );
        } elseif (
            $scenarioRank < $baselineRank
        ) {
            $classification =
                $scenarioFeasibility === 'Tight'
                    ? 'conditional'
                    : 'viable_but_not_recommended';
            $status =
                $scenarioFeasibility === 'Tight'
                    ? 'Conditional Scenario'
                    : 'Viable but Not Recommended';
            $preferred =
                'current_plan';
            $reason =
                sprintf(
                    'The scenario reduces route feasibility from %s to %s, so the current plan remains preferable.',
                    $baselineFeasibility,
                    $scenarioFeasibility
                );
        } else {
            $riskDelta =
                $delta['risk_points'];

            $qualityDelta =
                $delta['quality_points'];

            $marginDelta =
                $delta[
                    'transit_margin_hours'
                ];

            $carbonDelta =
                $delta['carbon_kg'];

            if (
                $riskDelta !== null
                && $riskDelta <= -1.0
            ) {
                $classification =
                    'recommended_over_current';
                $status =
                    'Recommended over Current Plan';
                $preferred =
                    'scenario';
                $reason =
                    sprintf(
                        'The scenario keeps route feasibility at %s while reducing operational risk by %.1f point(s), from %s/100 to %s/100.',
                        $scenarioFeasibility,
                        abs($riskDelta),
                        $baseline['analysis'][
                            'risk_score'
                        ] ?? '—',
                        $scenario['analysis'][
                            'risk_score'
                        ] ?? '—'
                    );
            } elseif (
                $riskDelta !== null
                && $riskDelta >= 1.0
            ) {
                $classification =
                    $scenarioFeasibility === 'Tight'
                        ? 'conditional'
                        : 'viable_but_not_recommended';
                $status =
                    $scenarioFeasibility === 'Tight'
                        ? 'Conditional Scenario'
                        : 'Viable but Not Recommended';
                $preferred =
                    'current_plan';
                $reason =
                    sprintf(
                        'The scenario remains route-feasible (%s) but increases operational risk by %.1f point(s), from %s/100 to %s/100. Keep the current plan unless another operational constraint requires this change.',
                        $scenarioFeasibility,
                        $riskDelta,
                        $baseline['analysis'][
                            'risk_score'
                        ] ?? '—',
                        $scenario['analysis'][
                            'risk_score'
                        ] ?? '—'
                    );
            } elseif (
                $qualityDelta !== null
                && $qualityDelta >= 1.0
            ) {
                $classification =
                    'recommended_over_current';
                $status =
                    'Recommended over Current Plan';
                $preferred =
                    'scenario';
                $reason =
                    sprintf(
                        'Operational risk is unchanged while estimated arrival quality improves by %.1f point(s).',
                        $qualityDelta
                    );
            } elseif (
                $qualityDelta !== null
                && $qualityDelta <= -1.0
            ) {
                $classification =
                    'viable_but_not_recommended';
                $status =
                    'Viable but Not Recommended';
                $preferred =
                    'current_plan';
                $reason =
                    sprintf(
                        'Operational risk is unchanged but estimated arrival quality decreases by %.1f point(s), so the current plan remains preferable.',
                        abs($qualityDelta)
                    );
            } elseif (
                $marginDelta !== null
                && $marginDelta >= 0.5
            ) {
                $classification =
                    'recommended_over_current';
                $status =
                    'Recommended over Current Plan';
                $preferred =
                    'scenario';
                $reason =
                    sprintf(
                        'Primary risk and condition outcomes are materially unchanged while transit margin improves by %.1f hour(s).',
                        $marginDelta
                    );
            } elseif (
                $marginDelta !== null
                && $marginDelta <= -0.5
            ) {
                $classification =
                    'viable_but_not_recommended';
                $status =
                    'Viable but Not Recommended';
                $preferred =
                    'current_plan';
                $reason =
                    sprintf(
                        'Primary risk remains unchanged but transit margin decreases by %.1f hour(s), so the current plan remains preferable.',
                        abs($marginDelta)
                    );
            } elseif (
                $carbonDelta !== null
                && $carbonDelta <= -0.01
            ) {
                $classification =
                    'recommended_over_current';
                $status =
                    'Recommended over Current Plan';
                $preferred =
                    'scenario';
                $reason =
                    sprintf(
                        'Safety, risk, condition, and transit margin are materially equivalent while estimated carbon is %.2f kg lower.',
                        abs($carbonDelta)
                    );
            } elseif (
                $carbonDelta !== null
                && $carbonDelta >= 0.01
            ) {
                $classification =
                    'viable_but_not_recommended';
                $status =
                    'Viable but Not Recommended';
                $preferred =
                    'current_plan';
                $reason =
                    sprintf(
                        'The scenario provides no material operational benefit and increases estimated carbon by %.2f kg.',
                        $carbonDelta
                    );
            }
        }

        $scenario['baseline_decision'] = [
            'classification' =>
                $classification,
            'status' =>
                $status,
            'preferred_option' =>
                $preferred,
            'reason' =>
                $reason,
            'baseline_feasibility' =>
                $baselineFeasibility,
            'scenario_feasibility' =>
                $scenarioFeasibility,
            'delta' =>
                $delta,
        ];

        return $scenario;
    }

    private function overallDecision(
        array $baseline,
        ?array $bestScenario
    ): array {
        if (!$bestScenario) {
            return [
                'type' =>
                    'unavailable',
                'status' =>
                    'Scenario Comparison Unavailable',
                'preferred_option' =>
                    'current_plan',
                'reason' =>
                    'No scenario result is available for comparison.',
            ];
        }

        $decision =
            $bestScenario[
                'baseline_decision'
            ];

        return [
            'type' =>
                $decision[
                    'classification'
                ],
            'status' =>
                $decision['status'],
            'preferred_option' =>
                $decision[
                    'preferred_option'
                ],
            'reason' =>
                $decision['reason'],
        ];
    }

    public function decisionTable(
        array $baseline,
        array $scenarios
    ): array {
        $evaluated = array_map(
            fn (array $scenario): array =>
                $this->evaluateAgainstBaseline(
                    $baseline,
                    $scenario
                ),
            $scenarios
        );

        return array_values(
            array_map(
                function (
                    array $scenario,
                    int $index
                ): array {
                    return [
                        'position' =>
                            $index + 1,
                        'name' =>
                            $scenario['name']
                            ?? (
                                'Scenario '
                                . chr(
                                    65 + $index
                                )
                            ),
                        'decision_status' =>
                            $scenario[
                                'baseline_decision'
                            ]['status']
                            ?? 'Unavailable',
                        'preferred_option' =>
                            $scenario[
                                'baseline_decision'
                            ]['preferred_option']
                            ?? 'current_plan',
                        'risk_score' =>
                            $scenario['analysis'][
                                'risk_score'
                            ] ?? null,
                        'quality_at_arrival' =>
                            $scenario['analysis'][
                                'quality_at_arrival'
                            ] ?? null,
                        'storage_status' =>
                            $scenario['analysis'][
                                'quality_prediction'
                            ][
                                'storage_stability_assessment'
                            ]['status']
                            ?? null,
                        'feasibility' =>
                            $scenario['route'][
                                'freshness_feasibility'
                            ] ?? 'Unavailable',
                        'transit_margin_hours' =>
                            $scenario['route'][
                                'transit_margin_hours'
                            ] ?? null,
                        'carbon_kg' =>
                            $scenario['carbon'][
                                'estimated_kg'
                            ] ?? null,
                        'evidence_coverage' =>
                            $scenario['evidence'][
                                'percent'
                            ] ?? null,
                        'delta' =>
                            $scenario[
                                'baseline_decision'
                            ]['delta']
                            ?? null,
                    ];
                },
                $evaluated,
                array_keys($evaluated)
            )
        );
    }

    private function feasibilityRank(
        string $feasibility
    ): int {
        return match ($feasibility) {
            'Safe' => 3,
            'Tight' => 2,
            'Breach' => 1,
            default => 0,
        };
    }

    private function decisionRank(
        string $classification
    ): int {
        return match ($classification) {
            'recommended_over_current' => 6,
            'equivalent_alternative' => 5,
            'conditional' => 4,
            'viable_but_not_recommended' => 3,
            'not_viable' => 1,
            default => 0,
        };
    }

    private function numericDelta(
        mixed $before,
        mixed $after
    ): ?float {
        if (
            !is_numeric($before)
            || !is_numeric($after)
        ) {
            return null;
        }

        return round(
            (float) $after
            - (float) $before,
            2
        );
    }

    private function relativeChangePercent(
        mixed $before,
        mixed $after
    ): ?float {
        if (
            !is_numeric($before)
            || !is_numeric($after)
            || (float) $before == 0.0
        ) {
            return null;
        }

        return round(
            (
                (
                    (float) $after
                    - (float) $before
                )
                / abs(
                    (float) $before
                )
            )
            * 100,
            1
        );
    }
}
