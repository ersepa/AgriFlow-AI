<?php

namespace App\Services\DigitalTwin;

use App\Models\Shipment;
use App\Services\AI\DecisionEngine;
use App\Services\Routing\FreshnessAwareRouteService;
use App\Services\Sustainability\FreightCarbonEstimateService;
use Carbon\Carbon;

class ScenarioEngine
{
    public const VERSION =
        'step6.2-multi-scenario-comparison-v1';

    public function __construct(
        private readonly DecisionEngine $decisionEngine,
        private readonly FreshnessAwareRouteService $freshnessRoutes,
        private readonly ScenarioComparisonService $comparison,
        private readonly FreightCarbonEstimateService $freightCarbon
    ) {
    }

    public function baseline(
        Shipment $shipment
    ): array {
        $this->assertOperationallyActive($shipment);

        $shipment->loadMissing('harvest');

        $analysis =
            $this->decisionEngine->analyze(
                $shipment
            );

        $route =
            $this->freshnessRoutes
                ->assessCurrentRoute(
                    $shipment,
                    $analysis
                );

        return $this->snapshot(
            shipment: $shipment,
            name: 'Current Plan',
            input: [
                'route_rank' => 0,
                'delay_hours' => 0.0,
                'vehicle' => 'standard_truck',
            ],
            analysis: $analysis,
            route: $route,
            carbon: $this->carbonEstimate(
                $shipment,
                (float) (
                    $shipment->distance_km
                    ?? 0
                ),
                $analysis
            ),
            evidence: $this->evidenceCoverage(
                $analysis,
                []
            ),
            notes: [
                'Baseline uses the shipment data currently stored in AgriFlow.',
                'Vehicle choice is metadata only unless a validated condition input or emission factor is explicitly available.',
            ]
        );
    }

    public function simulate(
        Shipment $shipment,
        array $input
    ): array {
        $this->assertOperationallyActive($shipment);

        $shipment->loadMissing('harvest');

        $routeCandidate =
            $this->resolveRoute(
                $shipment,
                (int) (
                    $input['route_rank']
                    ?? 0
                )
            );

        $delayHours =
            max(
                0,
                (float) (
                    $input['delay_hours']
                    ?? 0
                )
            );

        $scenarioShipment =
            clone $shipment;

        $scenarioShipment->setRelation(
            'harvest',
            $shipment->harvest
        );

        $scenarioShipment->distance_km =
            $routeCandidate['distance_km'];

        /*
         * The scenario duration is explicit:
         * actual/planned route duration + user-selected delay.
         * No hidden "-10% optimized route" modifier exists.
         */
        $scenarioShipment->duration_hours =
            round(
                (float) $routeCandidate['duration_hours']
                + $delayHours,
                4
            );

        $conditionScenario =
            $this->conditionScenario(
                $input
            );

        /*
         * Vehicle is intentionally NOT passed to DecisionEngine.
         * Step 6 does not allow a vehicle label to directly change
         * risk, quality, operational readiness, or carbon.
         */
        $analysis =
            $this->decisionEngine->analyze(
                $scenarioShipment,
                $conditionScenario
            );

        $route =
            $this->freshnessRoutes
                ->assessCurrentRoute(
                    $scenarioShipment,
                    $analysis
                );

        $route['candidate_label'] =
            $routeCandidate['label'];

        $route['candidate_rank'] =
            $routeCandidate['rank'];

        $route['base_route_duration_hours'] =
            round(
                (float) $routeCandidate[
                    'duration_hours'
                ],
                2
            );

        $route['scenario_delay_hours'] =
            round(
                $delayHours,
                2
            );

        $route['scenario_total_duration_hours'] =
            round(
                (float) $scenarioShipment
                    ->duration_hours,
                2
            );

        $evidence =
            $this->evidenceCoverage(
                $analysis,
                $input
            );

        $notes =
            $this->scenarioNotes(
                $analysis,
                $input
            );

        return $this->snapshot(
            shipment: $scenarioShipment,
            name: (string) (
                $input['name']
                ?? 'Scenario'
            ),
            input: $this->inputSnapshot(
                $input,
                $routeCandidate
            ),
            analysis: $analysis,
            route: $route,
            carbon: $this->carbonEstimate(
                $shipment,
                (float) $routeCandidate[
                    'distance_km'
                ],
                $analysis
            ),
            evidence: $evidence,
            notes: $notes
        );
    }

    public function compareOne(
        Shipment $shipment,
        array $input
    ): array {
        $baseline =
            $this->baseline(
                $shipment
            );

        $scenario =
            $this->simulate(
                $shipment,
                $input
            );

        $scenario['delta'] =
            $this->comparison->delta(
                $baseline,
                $scenario
            );

        $comparison =
            $this->comparison->compare(
                $baseline,
                [$scenario]
            );

        /*
         * Return the baseline-aware evaluation with the scenario so the UI
         * and stored snapshot carry the same decision semantics.
         */
        $evaluatedScenario =
            $comparison[
                'best_available_scenario'
            ] ?? $scenario;

        $evaluatedScenario['delta'] =
            $scenario['delta'];

        return [
            'engine_version' =>
                self::VERSION,
            'baseline' =>
                $baseline,
            'scenario' =>
                $evaluatedScenario,
            'comparison' =>
                $comparison,
        ];
    }

    public function compareMany(
        Shipment $shipment,
        array $scenarioInputs
    ): array {
        $baseline =
            $this->baseline(
                $shipment
            );

        $scenarioInputs =
            array_values(
                array_slice(
                    $scenarioInputs,
                    0,
                    3
                )
            );

        $scenarios = [];

        foreach (
            $scenarioInputs
            as $index => $input
        ) {
            $input['name'] =
                trim(
                    (string) (
                        $input['name']
                        ?? ''
                    )
                )
                ?: (
                    'Scenario '
                    . chr(65 + $index)
                );

            $scenario =
                $this->simulate(
                    $shipment,
                    $input
                );

            $scenario['delta'] =
                $this->comparison->delta(
                    $baseline,
                    $scenario
                );

            $scenarios[] =
                $scenario;
        }

        $comparison =
            $this->comparison->compare(
                $baseline,
                $scenarios
            );

        $ranked =
            $comparison[
                'ranked_scenarios'
            ] ?? [];

        $ranked = array_map(
            function (
                array $scenario
            ) use ($scenarios): array {
                $matched =
                    collect($scenarios)
                        ->first(
                            fn (
                                array $candidate
                            ): bool =>
                                ($candidate['name'] ?? null)
                                ===
                                ($scenario['name'] ?? null)
                        );

                if ($matched) {
                    $scenario['delta'] =
                        $matched['delta']
                        ?? null;
                }

                return $scenario;
            },
            $ranked
        );

        $comparison[
            'ranked_scenarios'
        ] = $ranked;

        $averageCoverage =
            count($scenarios) > 0
                ? (int) round(
                    collect($scenarios)
                        ->avg(
                            fn (
                                array $scenario
                            ) =>
                                $scenario[
                                    'evidence'
                                ]['percent']
                                ?? 0
                        )
                )
                : 0;

        return [
            'engine_version' =>
                self::VERSION,
            'baseline' =>
                $baseline,
            'scenarios' =>
                $scenarios,
            'comparison' =>
                $comparison,
            'decision_table' =>
                $this->comparison
                    ->decisionTable(
                        $baseline,
                        $scenarios
                    ),
            'average_evidence_coverage' =>
                $averageCoverage,
        ];
    }

    public function routeOptions(
        Shipment $shipment
    ): array {
        $this->assertOperationallyActive($shipment);

        $shipment->loadMissing('harvest');

        $optimization =
            $this->freshnessRoutes
                ->optimize(
                    $shipment
                );

        return array_values(
            array_map(
                fn (array $candidate): array => [
                    'rank' =>
                        (int) (
                            $candidate['rank']
                            ?? 0
                        ),
                    'label' =>
                        (string) (
                            $candidate['label']
                            ?? 'Route'
                        ),
                    'distance_km' =>
                        round(
                            (float) (
                                $candidate[
                                    'distance_km'
                                ] ?? 0
                            ),
                            2
                        ),
                    'duration_hours' =>
                        round(
                            (float) (
                                $candidate[
                                    'duration_hours'
                                ] ?? 0
                            ),
                            2
                        ),
                    'freshness_feasibility' =>
                        $candidate[
                            'freshness_feasibility'
                        ] ?? null,
                    'route_score' =>
                        $candidate[
                            'route_score'
                        ] ?? null,
                ],
                $optimization['candidates']
                    ?? []
            )
        );
    }

    private function resolveRoute(
        Shipment $shipment,
        int $rank
    ): array {
        if ($rank <= 0) {
            return [
                'rank' => 0,
                'label' => 'Current Route',
                'distance_km' =>
                    (float) (
                        $shipment->distance_km
                        ?? 0
                    ),
                'duration_hours' =>
                    (float) (
                        $shipment->duration_hours
                        ?? 0
                    ),
            ];
        }

        $options =
            $this->routeOptions(
                $shipment
            );

        $candidate =
            collect($options)
                ->firstWhere(
                    'rank',
                    $rank
                );

        if (!$candidate) {
            throw new \InvalidArgumentException(
                'The selected route candidate is no longer available. Refresh the Digital Twin and try again.'
            );
        }

        return $candidate;
    }

    private function conditionScenario(
        array $input
    ): array {
        $scenario = [];

        if (
            array_key_exists(
                'temperature_c',
                $input
            )
            && $input[
                'temperature_c'
            ] !== null
            && $input[
                'temperature_c'
            ] !== ''
        ) {
            $scenario['temperature'] =
                (float) $input[
                    'temperature_c'
                ];
        }

        if (
            array_key_exists(
                'moisture_percent',
                $input
            )
            && $input[
                'moisture_percent'
            ] !== null
            && $input[
                'moisture_percent'
            ] !== ''
        ) {
            $scenario['moisture_percent'] =
                (float) $input[
                    'moisture_percent'
                ];
        }

        if (
            array_key_exists(
                'relative_humidity_percent',
                $input
            )
            && $input[
                'relative_humidity_percent'
            ] !== null
            && $input[
                'relative_humidity_percent'
            ] !== ''
        ) {
            $scenario[
                'relative_humidity_percent'
            ] =
                (float) $input[
                    'relative_humidity_percent'
                ];
        }

        if (!empty($input['storage_horizon'])) {
            $scenario['storage_horizon'] =
                (string) $input[
                    'storage_horizon'
                ];
        }

        return $scenario;
    }

    private function carbonEstimate(
        Shipment $originalShipment,
        float $scenarioDistanceKm,
        array $analysis
    ): array {
        $estimate = $this->freightCarbon->estimateForShipment(
            $originalShipment,
            max(0, $scenarioDistanceKm)
        );

        $estimate['vehicle_factor_applied'] = false;
        $estimate['scenario_note'] =
            'Digital Twin uses the same explicit tonne-km freight factor for baseline and scenarios. Vehicle labels do not change carbon without a validated vehicle/fuel-specific factor.';

        return $estimate;
    }

    private function evidenceCoverage(
        array $analysis,
        array $input
    ): array {
        $modelType =
            $analysis[
                'quality_prediction'
            ][
                'condition_model_type'
            ]
            ?? $analysis[
                'commodity_profile'
            ][
                'quality_model_type'
            ]
            ?? null;

        $checks = [
            'commodity_profile' =>
                (bool) (
                    $analysis[
                        'commodity_profile_found'
                    ] ?? false
                ),
            'route' => true,
            'operational_deadline' =>
                (
                    $analysis[
                        'quality_prediction'
                    ][
                        'recorded_expiry_window_hours'
                    ] ?? null
                ) !== null
                || (
                    $analysis[
                        'remaining_days'
                    ] ?? null
                ) !== null,
        ];

        if (
            $modelType ===
                'storage_stability'
        ) {
            $checks['cargo_moisture'] =
                isset(
                    $input[
                        'moisture_percent'
                    ]
                )
                && $input[
                    'moisture_percent'
                ] !== '';

            $checks['relative_humidity'] =
                isset(
                    $input[
                        'relative_humidity_percent'
                    ]
                )
                && $input[
                    'relative_humidity_percent'
                ] !== '';
        } else {
            $checks['cargo_temperature'] =
                isset(
                    $input[
                        'temperature_c'
                    ]
                )
                && $input[
                    'temperature_c'
                ] !== '';
        }

        $available =
            count(
                array_filter(
                    $checks
                )
            );

        $total =
            max(
                1,
                count($checks)
            );

        return [
            'percent' =>
                (int) round(
                    $available
                    / $total
                    * 100
                ),
            'available_inputs' =>
                $available,
            'total_inputs' =>
                $total,
            'checks' =>
                $checks,
            'label' =>
                'Scenario Evidence Coverage',
            'is_accuracy_metric' =>
                false,
        ];
    }

    private function scenarioNotes(
        array $analysis,
        array $input
    ): array {
        $notes = [
            'Scenario calculations are deterministic. This is not Monte Carlo simulation and not a statistical probability forecast.',
            'LLM output is not used to calculate risk, condition, route feasibility, transit margin, or carbon.',
            'Vehicle selection is planning metadata only; it does not apply hidden risk, readiness, or carbon bonuses.',
        ];

        $modelType =
            $analysis[
                'quality_prediction'
            ][
                'condition_model_type'
            ]
            ?? null;

        if (
            $modelType ===
                'storage_stability'
            && (
                empty(
                    $input[
                        'moisture_percent'
                    ]
                )
                || empty(
                    $input[
                        'relative_humidity_percent'
                    ]
                )
            )
        ) {
            $notes[] =
                'Dry-commodity storage stability is evidence-limited when cargo moisture or RH telemetry is missing.';
        }

        if (
            ($input['vehicle'] ?? null)
                === 'refrigerated_truck'
            && !isset(
                $input[
                    'temperature_c'
                ]
            )
        ) {
            $notes[] =
                'Selecting Refrigerated Truck does not imply a cargo temperature. Enter a scenario temperature to evaluate temperature-sensitive fresh produce.';
        }

        return $notes;
    }

    private function inputSnapshot(
        array $input,
        array $routeCandidate
    ): array {
        return [
            'name' =>
                (string) (
                    $input['name']
                    ?? 'Scenario'
                ),
            'route_rank' =>
                $routeCandidate['rank'],
            'route_label' =>
                $routeCandidate['label'],
            'route_distance_km' =>
                round(
                    (float) $routeCandidate[
                        'distance_km'
                    ],
                    2
                ),
            'route_duration_hours' =>
                round(
                    (float) $routeCandidate[
                        'duration_hours'
                    ],
                    2
                ),
            'delay_hours' =>
                round(
                    (float) (
                        $input['delay_hours']
                        ?? 0
                    ),
                    2
                ),
            'vehicle' =>
                (string) (
                    $input['vehicle']
                    ?? 'standard_truck'
                ),
            'temperature_c' =>
                $input[
                    'temperature_c'
                ] ?? null,
            'moisture_percent' =>
                $input[
                    'moisture_percent'
                ] ?? null,
            'relative_humidity_percent' =>
                $input[
                    'relative_humidity_percent'
                ] ?? null,
            'storage_horizon' =>
                $input[
                    'storage_horizon'
                ] ?? null,
        ];
    }

    private function snapshot(
        Shipment $shipment,
        string $name,
        array $input,
        array $analysis,
        array $route,
        array $carbon,
        array $evidence,
        array $notes
    ): array {
        return [
            'name' => $name,
            'engine_version' =>
                self::VERSION,
            'simulated_at' =>
                Carbon::now()
                    ->toIso8601String(),
            'shipment' => [
                'id' =>
                    $shipment->id,
                'commodity' =>
                    $shipment->harvest?->commodity
                    ?? 'Unknown',
                'origin' =>
                    $shipment->origin,
                'destination' =>
                    $shipment->destination,
                'status' =>
                    $shipment->status,
            ],
            'input' =>
                $input,
            'analysis' =>
                $analysis,
            'route' =>
                $route,
            'carbon' =>
                $carbon,
            'evidence' =>
                $evidence,
            'notes' =>
                $notes,
            'provenance' =>
                $this->provenance(
                    $analysis
                ),
        ];
    }

    private function provenance(
        array $analysis
    ): array {
        return [
            'engine' =>
                self::VERSION,
            'commodity_profile' => [
                'name' =>
                    $analysis[
                        'commodity_profile'
                    ]['name']
                    ?? null,
                'source_name' =>
                    $analysis[
                        'commodity_profile'
                    ]['source_name']
                    ?? null,
                'source_url' =>
                    $analysis[
                        'commodity_profile'
                    ]['source_url']
                    ?? null,
            ],
            'route_provider' =>
                'OpenRouteService / HeiGIT via AgriFlow server-side RouteService',
            'risk_model' =>
                $analysis[
                    'risk_assessment'
                ]['model_version']
                ?? 'AgriFlow Operational Risk Engine',
            'quality_model' =>
                $analysis[
                    'quality_prediction'
                ]['model_version']
                ?? null,
            'calculation_type' =>
                'deterministic_decision_support',
        ];
    }
    private function assertOperationallyActive(Shipment $shipment): void
    {
        if ($shipment->isDelivered()) {
            throw new \InvalidArgumentException(
                'Digital Twin simulation is closed for delivered shipments. Historical scenarios remain available for review.'
            );
        }
    }

}
