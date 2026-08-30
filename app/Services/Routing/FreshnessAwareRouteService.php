<?php

namespace App\Services\Routing;

use App\Models\Shipment;
use App\Services\AI\DecisionEngine;
use App\Services\RouteService;

/**
 * AgriFlow Step 5 - Freshness-Aware Routing
 *
 * Ranks route candidates by their projected effect on post-harvest condition,
 * operational risk, transit margin, duration, and carbon exposure.
 *
 * This is deterministic decision support, not a traffic-prediction model.
 */
class FreshnessAwareRouteService
{
    private const WEIGHTS = [
        'freshness_preservation' => 0.40,
        'risk_protection' => 0.25,
        'transit_margin' => 0.15,
        'duration_efficiency' => 0.10,
        'carbon_efficiency' => 0.10,
    ];

    public function __construct(
        private readonly DecisionEngine $engine,
        private readonly RouteService $routes
    ) {
    }

    public function assessCurrentRoute(
        Shipment $shipment,
        ?array $analysis = null
    ): array {
        $shipment->loadMissing('harvest');

        $analysis ??= $this->engine->analyze($shipment);

        $candidate = [
            'key' => 'current',
            'label' => 'Current Route',
            'distance_km' => round((float) ($shipment->distance_km ?? 0), 2),
            'duration_hours' => round((float) ($shipment->duration_hours ?? 0), 2),
            'carbon_kg' => round((float) ($analysis['carbon_kg'] ?? 0), 2),
            'geometry' => $this->normalizeStoredGeometry(
                $shipment->route_geometry
            ),
            'analysis' => $analysis,
            'source' => 'stored_shipment_route',
        ];

        $ranked = $this->scoreCandidates([$candidate]);

        return array_merge(
            $ranked[0],
            [
                'ranking_scope' => 'current_route_only',
                'recommended' => true,
                'recommendation_reason' =>
                    $this->singleRouteReason($ranked[0]),
            ]
        );
    }

    /**
     * Optimize with real ORS alternatives when the public API supports the
     * route distance. For longer routes, AgriFlow still evaluates the current
     * route honestly instead of fabricating alternatives.
     */
    public function optimize(Shipment $shipment): array
    {
        $shipment->loadMissing('harvest');

        $baseAnalysis = $this->engine->analyze($shipment);
        $current = $this->currentCandidate($shipment, $baseAnalysis);

        $candidateInputs = [$current];
        $alternativeSource = 'not_requested';
        $alternativeNote = null;

        $hasCoordinates =
            $shipment->origin_lat !== null
            && $shipment->origin_lng !== null
            && $shipment->destination_lat !== null
            && $shipment->destination_lng !== null;

        $distance = (float) ($shipment->distance_km ?? 0);

        // ORS public alternative-route requests are restricted to 100 km.
        // Do not pretend alternatives exist for longer routes.
        if ($hasCoordinates && $distance > 0 && $distance <= 100) {
            $features = $this->routes->getAlternativeRoutes(
                [
                    'lat' => (float) $shipment->origin_lat,
                    'lon' => (float) $shipment->origin_lng,
                ],
                [
                    'lat' => (float) $shipment->destination_lat,
                    'lon' => (float) $shipment->destination_lng,
                ],
                3
            );

            if (!empty($features)) {
                $alternativeSource = 'openrouteservice';
                $candidateInputs = $this->mergeAlternativeCandidates(
                    $shipment,
                    $current,
                    $features
                );
            } else {
                $alternativeSource = 'provider_unavailable';
                $alternativeNote =
                    'Alternative-route provider returned no usable route candidates. '
                    . 'The current route is evaluated without invented alternatives.';
            }
        } elseif (!$hasCoordinates) {
            $alternativeSource = 'coordinates_unavailable';
            $alternativeNote =
                'Origin/destination coordinates are incomplete, so only the '
                . 'stored route can be assessed.';
        } elseif ($distance > 100) {
            $alternativeSource = 'public_api_distance_limit';
            $alternativeNote =
                'The public routing provider limits alternative-route requests '
                . 'to routes up to 100 km. This shipment is evaluated using its '
                . 'current route only; no synthetic alternatives are fabricated.';
        } else {
            $alternativeSource = 'insufficient_route_data';
            $alternativeNote =
                'The stored route does not contain enough distance data to '
                . 'request route alternatives.';
        }

        $ranked = $this->scoreCandidates($candidateInputs);

        foreach ($ranked as $index => &$candidate) {
            $candidate['rank'] = $index + 1;
            $candidate['recommended'] = $index === 0;
        }
        unset($candidate);

        $best = $ranked[0];

        $allBreach =
            !empty($ranked)
            && collect($ranked)->every(
                fn (array $candidate) =>
                    $candidate[
                        'freshness_feasibility'
                    ] === 'Breach'
            );

        $decisionStatus =
            $allBreach
                ? 'No Freshness-Safe Route'
                : 'Route Recommendation Available';

        $decisionType =
            $allBreach
                ? 'best_available'
                : 'recommended';

        foreach ($ranked as &$candidate) {
            $candidate['recommended'] =
                !$allBreach
                && $candidate['rank'] === 1;

            $candidate['best_available'] =
                $allBreach
                && $candidate['rank'] === 1;
        }
        unset($candidate);

        return [
            'model_name' => 'Freshness-Aware Route Ranking',
            'model_version' => '5.1',
            'deterministic' => true,
            'weights' => self::WEIGHTS,
            'alternative_source' => $alternativeSource,
            'alternative_note' => $alternativeNote,
            'candidate_count' => count($ranked),

            'decision_status' =>
                $decisionStatus,

            'decision_type' =>
                $decisionType,

            'routing_alone_sufficient' =>
                !$allBreach,

            'recommended_route' =>
                $allBreach
                    ? null
                    : $best,

            'best_available_route' =>
                $best,

            'candidates' => $ranked,

            'recommendation_reason' =>
                $allBreach
                    ? $this->allBreachReason(
                        $ranked
                    )
                    : $this->recommendationReason(
                        $ranked
                    ),

            'required_action' =>
                $allBreach
                    ? 'Immediate operational intervention required; routing alone is insufficient.'
                    : null,
        ];
    }

    private function currentCandidate(
        Shipment $shipment,
        array $analysis
    ): array {
        return [
            'key' => 'current',
            'label' => 'Current Route',
            'distance_km' => round((float) ($shipment->distance_km ?? 0), 2),
            'duration_hours' => round((float) ($shipment->duration_hours ?? 0), 2),
            'carbon_kg' => round((float) ($analysis['carbon_kg'] ?? 0), 2),
            'geometry' => $this->normalizeStoredGeometry(
                $shipment->route_geometry
            ),
            'analysis' => $analysis,
            'source' => 'stored_shipment_route',
        ];
    }

    private function mergeAlternativeCandidates(
        Shipment $shipment,
        array $current,
        array $features
    ): array {
        $candidates = [$current];
        $seen = [];

        $seen[$this->candidateFingerprint(
            $current['distance_km'],
            $current['duration_hours']
        )] = true;

        $alternativeNumber = 1;

        foreach ($features as $feature) {
            $summary = $feature['properties']['summary'] ?? [];
            $distanceKm = round(
                ((float) ($summary['distance'] ?? 0)) / 1000,
                2
            );
            $durationHours = round(
                ((float) ($summary['duration'] ?? 0)) / 3600,
                2
            );

            if ($distanceKm <= 0 || $durationHours <= 0) {
                continue;
            }

            $fingerprint = $this->candidateFingerprint(
                $distanceKm,
                $durationHours
            );

            if (isset($seen[$fingerprint])) {
                continue;
            }

            if (
                $this->isNearDuplicateCandidate(
                    $candidates,
                    $distanceKm,
                    $durationHours
                )
            ) {
                continue;
            }

            $seen[$fingerprint] = true;

            $scenarioShipment = $this->scenarioShipment(
                $shipment,
                $distanceKm,
                $durationHours
            );

            $analysis = $this->engine->analyze(
                $scenarioShipment
            );

            $candidates[] = [
                'key' => 'alternative_' . $alternativeNumber,
                'label' => 'Alternative ' . $alternativeNumber,
                'distance_km' => $distanceKm,
                'duration_hours' => $durationHours,
                'carbon_kg' => round(
                    (float) ($analysis['carbon_kg'] ?? 0),
                    2
                ),
                'geometry' =>
                    $feature['geometry']['coordinates']
                    ?? [],
                'analysis' => $analysis,
                'source' => 'openrouteservice',
            ];

            $alternativeNumber++;

            if ($alternativeNumber > 3) {
                break;
            }
        }

        return $candidates;
    }

    private function scenarioShipment(
        Shipment $shipment,
        float $distanceKm,
        float $durationHours
    ): Shipment {
        $scenario = $shipment->replicate();

        $scenario->distance_km = $distanceKm;
        $scenario->duration_hours = $durationHours;

        $baseDistance = max(
            0.01,
            (float) ($shipment->distance_km ?? 0)
        );

        $baseCarbon = (float) (
            $shipment->carbon_emission
            ?? 0
        );

        if ($baseCarbon > 0) {
            $scenario->carbon_emission = round(
                $baseCarbon
                * ($distanceKm / $baseDistance),
                2
            );
        } else {
            $scenario->carbon_emission = round(
                $distanceKm * 0.12,
                2
            );
        }

        if ($shipment->relationLoaded('harvest')) {
            $scenario->setRelation(
                'harvest',
                $shipment->harvest
            );
        }

        return $scenario;
    }

    private function scoreCandidates(array $candidates): array
    {
        if (empty($candidates)) {
            return [];
        }

        $fastest = max(
            0.01,
            min(array_map(
                fn (array $candidate) =>
                    max(
                        0.01,
                        (float) $candidate['duration_hours']
                    ),
                $candidates
            ))
        );

        $lowestCarbon = max(
            0.01,
            min(array_map(
                fn (array $candidate) =>
                    max(
                        0.01,
                        (float) $candidate['carbon_kg']
                    ),
                $candidates
            ))
        );

        $scored = array_map(
            function (array $candidate) use (
                $fastest,
                $lowestCarbon
            ) {
                $analysis = $candidate['analysis'];

                $freshness = $this->clamp(
                    (float) (
                        $analysis['quality_at_arrival']
                        ?? 0
                    )
                );

                $riskProtection = $this->clamp(
                    100
                    - (float) (
                        $analysis['risk_score']
                        ?? 100
                    )
                );

                $margin = $analysis[
                    'transit_margin_hours'
                ] ?? null;

                $marginScore =
                    $this->transitMarginScore(
                        $margin,
                        $analysis[
                            'safe_transit_status'
                        ] ?? ''
                    );

                $duration = max(
                    0.01,
                    (float) $candidate[
                        'duration_hours'
                    ]
                );

                $durationEfficiency =
                    $this->clamp(
                        ($fastest / $duration) * 100
                    );

                $carbon = max(
                    0.01,
                    (float) $candidate['carbon_kg']
                );

                $carbonEfficiency =
                    $this->clamp(
                        ($lowestCarbon / $carbon) * 100
                    );

                $components = [
                    'freshness_preservation' =>
                        round($freshness, 1),

                    'risk_protection' =>
                        round($riskProtection, 1),

                    'transit_margin' =>
                        round($marginScore, 1),

                    'duration_efficiency' =>
                        round($durationEfficiency, 1),

                    'carbon_efficiency' =>
                        round($carbonEfficiency, 1),
                ];

                $weighted = 0.0;

                foreach (
                    self::WEIGHTS
                    as $key => $weight
                ) {
                    $weighted +=
                        ($components[$key] ?? 0)
                        * $weight;
                }

                $feasibility =
                    $this->feasibility(
                        $analysis
                    );

                if ($feasibility === 'Breach') {
                    $weighted = min($weighted, 35);
                } elseif ($feasibility === 'Tight') {
                    $weighted = min($weighted, 65);
                }

                return array_merge(
                    $candidate,
                    [
                        'route_score' =>
                            (int) round(
                                $this->clamp(
                                    $weighted
                                )
                            ),

                        'score_components' =>
                            $components,

                        'projected_arrival_quality' =>
                            (int) round(
                                $freshness
                            ),

                        'projected_quality_status' =>
                            $analysis[
                                'quality_status'
                            ] ?? 'Unavailable',

                        'projected_risk_score' =>
                            (int) (
                                $analysis[
                                    'risk_score'
                                ] ?? 100
                            ),

                        'projected_risk_severity' =>
                            $analysis[
                                'risk_severity'
                            ] ?? (
                                $analysis[
                                    'risk_level'
                                ] ?? 'Unknown'
                            ),

                        'remaining_shelf_life_days' =>
                            $analysis[
                                'predicted_remaining_shelf_life_days'
                            ] ?? null,

                        'transit_margin_hours' =>
                            $margin,

                        'delay_tolerance_hours' =>
                            $margin === null
                                ? null
                                : round(
                                    max(
                                        0,
                                        (float) $margin
                                    ),
                                    1
                                ),

                        'freshness_feasibility' =>
                            $feasibility,

                        'safe_transit_status' =>
                            $analysis[
                                'safe_transit_status'
                            ] ?? 'Unavailable',
                    ]
                );
            },
            $candidates
        );

        usort(
            $scored,
            function (array $a, array $b) {
                $feasibilityOrder = [
                    'Safe' => 0,
                    'Tight' => 1,
                    'Breach' => 2,
                ];

                $aFeasibility =
                    $feasibilityOrder[
                        $a[
                            'freshness_feasibility'
                        ]
                    ] ?? 3;

                $bFeasibility =
                    $feasibilityOrder[
                        $b[
                            'freshness_feasibility'
                        ]
                    ] ?? 3;

                if ($aFeasibility !== $bFeasibility) {
                    return $aFeasibility
                        <=> $bFeasibility;
                }

                return $b['route_score']
                    <=> $a['route_score'];
            }
        );

        return array_values($scored);
    }

    private function transitMarginScore(
        ?float $marginHours,
        string $status
    ): float {
        if (
            $status ===
                'Threshold already exceeded'
            || $status ===
                'ETA exceeds safe transit window'
        ) {
            return 0;
        }

        if ($marginHours === null) {
            return 40;
        }

        if ($marginHours <= 0) {
            return 0;
        }

        if ($marginHours >= 72) {
            return 100;
        }

        return ($marginHours / 72) * 100;
    }

    private function feasibility(
        array $analysis
    ): string {
        $status =
            $analysis[
                'safe_transit_status'
            ] ?? '';

        $margin =
            $analysis[
                'transit_margin_hours'
            ] ?? null;

        if (
            $status ===
                'Threshold already exceeded'
            || $status ===
                'ETA exceeds safe transit window'
            || (
                $margin !== null
                && $margin < 0
            )
        ) {
            return 'Breach';
        }

        if (
            $margin !== null
            && $margin <= 6
        ) {
            return 'Tight';
        }

        return 'Safe';
    }

    private function recommendationReason(
        array $ranked
    ): string {
        if (count($ranked) === 1) {
            return $this->singleRouteReason(
                $ranked[0]
            );
        }

        $best = $ranked[0];
        $second = $ranked[1];

        if (
            $best['freshness_feasibility']
            !== $second[
                'freshness_feasibility'
            ]
        ) {
            return sprintf(
                '%s is recommended because it preserves a %s freshness feasibility state while the next-best route is %s.',
                $best['label'],
                strtolower(
                    $best[
                        'freshness_feasibility'
                    ]
                ),
                strtolower(
                    $second[
                        'freshness_feasibility'
                    ]
                )
            );
        }

        return sprintf(
            '%s ranks highest at %d/100 by balancing projected arrival quality (%d/100), operational risk (%d/100), transit margin, duration, and carbon exposure.',
            $best['label'],
            $best['route_score'],
            $best[
                'projected_arrival_quality'
            ],
            $best[
                'projected_risk_score'
            ]
        );
    }

    private function singleRouteReason(
        array $route
    ): string {
        return sprintf(
            'The current route scores %d/100 with %s freshness feasibility, projected arrival quality %d/100, and operational risk %d/100. No alternative route is claimed unless a real routing candidate is available.',
            $route['route_score'],
            strtolower(
                $route[
                    'freshness_feasibility'
                ]
            ),
            $route[
                'projected_arrival_quality'
            ],
            $route[
                'projected_risk_score'
            ]
        );
    }

    private function normalizeStoredGeometry(
        mixed $geometry
    ): array {
        if (is_string($geometry)) {
            $decoded = json_decode(
                $geometry,
                true
            );

            if (is_array($decoded)) {
                $geometry = $decoded;
            }
        }

        if (!is_array($geometry)) {
            return [];
        }

        if (
            isset($geometry['coordinates'])
            && is_array(
                $geometry['coordinates']
            )
        ) {
            return $geometry['coordinates'];
        }

        return $geometry;
    }

    private function isNearDuplicateCandidate(
        array $existingCandidates,
        float $distanceKm,
        float $durationHours
    ): bool {
        foreach ($existingCandidates as $candidate) {
            $existingDistance =
                (float) (
                    $candidate['distance_km']
                    ?? 0
                );

            $existingDuration =
                (float) (
                    $candidate['duration_hours']
                    ?? 0
                );

            if (
                $existingDistance <= 0
                || $existingDuration <= 0
            ) {
                continue;
            }

            $distanceDifference =
                abs(
                    $distanceKm
                    - $existingDistance
                )
                / max(
                    0.01,
                    $existingDistance
                );

            $durationDifference =
                abs(
                    $durationHours
                    - $existingDuration
                )
                / max(
                    0.01,
                    $existingDuration
                );

            /*
             * Treat routes as effectively identical when both
             * distance and duration differ by less than 2%.
             * This prevents tiny geometry variations from being
             * presented as meaningful operational alternatives.
             */
            if (
                $distanceDifference < 0.02
                && $durationDifference < 0.02
            ) {
                return true;
            }
        }

        return false;
    }

    private function allBreachReason(
        array $ranked
    ): string {
        $best = $ranked[0];

        return sprintf(
            'No freshness-safe route is available. %s is the best available route at %d/100, but it still breaches the operational freshness window with projected arrival quality %d/100 and operational risk %d/100. Routing alone cannot recover this shipment; immediate operational intervention is required.',
            $best['label'],
            $best['route_score'],
            $best[
                'projected_arrival_quality'
            ],
            $best[
                'projected_risk_score'
            ]
        );
    }

    private function candidateFingerprint(
        float $distanceKm,
        float $durationHours
    ): string {
        return sprintf(
            '%.1f-%.2f',
            $distanceKm,
            $durationHours
        );
    }

    private function clamp(
        float $value,
        float $min = 0,
        float $max = 100
    ): float {
        return max(
            $min,
            min($max, $value)
        );
    }
}
