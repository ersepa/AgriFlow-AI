<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Services\AI\DecisionEngine;
use App\Services\GeminiService;
use App\Services\RouteService;
use App\Services\Routing\FreshnessAwareRouteService;
use Illuminate\Pagination\LengthAwarePaginator;

class AIOptimizerController extends Controller
{
    public function index(
        DecisionEngine $engine,
        FreshnessAwareRouteService $freshnessRoutes
    ) {
        $shipments = Shipment::with('harvest')
            ->whereIn('status', [
                'Harvested',
                'Packed',
                'In Transit',
            ])
            ->get();

        $collection = $shipments
            ->map(function (
                Shipment $shipment
            ) use (
                $engine,
                $freshnessRoutes
            ) {
                $analysis =
                    $engine->analyze(
                        $shipment
                    );

                $routeAssessment =
                    $freshnessRoutes
                        ->assessCurrentRoute(
                            $shipment,
                            $analysis
                        );

                return [
                    'shipment' => $shipment,

                    'commodity' =>
                        $shipment->harvest?->commodity
                        ?? 'Unknown',

                    'origin' => $shipment->origin,
                    'destination' =>
                        $shipment->destination,

                    'origin_lat' =>
                        $shipment->origin_lat
                        ?? -6.2,

                    'origin_lng' =>
                        $shipment->origin_lng
                        ?? 106.8,

                    'destination_lat' =>
                        $shipment->destination_lat
                        ?? -6.2,

                    'destination_lng' =>
                        $shipment->destination_lng
                        ?? 106.8,

                    'priority_score' =>
                        $analysis[
                            'priority_score'
                        ],

                    'priority_level' =>
                        $analysis[
                            'priority_level'
                        ],

                    'risk_score' =>
                        $analysis[
                            'risk_score'
                        ],

                    'risk_severity' =>
                        $analysis[
                            'risk_severity'
                        ] ?? (
                            $analysis[
                                'risk_level'
                            ] ?? 'Unknown'
                        ),

                    'sustainability_score' =>
                        $analysis[
                            'sustainability_score'
                        ],

                    'quality_at_arrival' =>
                        $analysis[
                            'quality_at_arrival'
                        ] ?? null,

                    'quality_status' =>
                        $analysis[
                            'quality_status'
                        ] ?? 'Unavailable',

                    'transit_margin_hours' =>
                        $analysis[
                            'transit_margin_hours'
                        ] ?? null,

                    'safe_transit_status' =>
                        $analysis[
                            'safe_transit_status'
                        ] ?? 'Unavailable',

                    'route_assessment' =>
                        $routeAssessment,

                    'freshness_route_score' =>
                        $routeAssessment[
                            'route_score'
                        ],

                    'freshness_feasibility' =>
                        $routeAssessment[
                            'freshness_feasibility'
                        ],

                    'delay_tolerance_hours' =>
                        $routeAssessment[
                            'delay_tolerance_hours'
                        ],
                ];
            })
            ->sortByDesc(
                'priority_score'
            )
            ->values();

        $totalShipments =
            $collection->count();

        $criticalCount =
            $collection->where(
                'risk_severity',
                'Critical'
            )->count();

        $highCount =
            $collection->where(
                'risk_severity',
                'High'
            )->count();

        $freshnessSafeCount =
            $collection->where(
                'freshness_feasibility',
                'Safe'
            )->count();

        $averageRouteScore =
            $totalShipments > 0
                ? round(
                    $collection->avg(
                        'freshness_route_score'
                    ),
                    1
                )
                : 0;

        $averageSustainability =
            $totalShipments > 0
                ? round(
                    $collection->avg(
                        'sustainability_score'
                    ),
                    1
                )
                : 0;

        $page =
            (int) request()->get(
                'page',
                1
            );

        $perPage = 3;

        $results =
            new LengthAwarePaginator(
                $collection->forPage(
                    $page,
                    $perPage
                )->values(),
                $collection->count(),
                $perPage,
                $page,
                [
                    'path' =>
                        request()->url(),

                    'query' =>
                        request()->query(),
                ]
            );

        return view(
            'ai.optimizer',
            compact(
                'results',
                'totalShipments',
                'criticalCount',
                'highCount',
                'freshnessSafeCount',
                'averageRouteScore',
                'averageSustainability'
            )
        );
    }

    /**
     * Step 5 on-demand route comparison.
     *
     * Real alternatives are requested only when supported by the route
     * provider. Otherwise the current route is assessed without fabricated
     * alternatives.
     */
    public function freshnessRoutes(
        Shipment $shipment,
        FreshnessAwareRouteService $freshnessRoutes
    ) {
        $shipment->loadMissing(
            'harvest'
        );

        return response()->json(
            $freshnessRoutes->optimize(
                $shipment
            )
        );
    }

    /**
     * Server-side route geometry endpoint.
     * Keeps ORS_API_KEY out of Blade/JavaScript.
     */
    public function routeGeometry(
        Shipment $shipment,
        RouteService $routes
    ) {
        if (
            $shipment->origin_lat === null
            || $shipment->origin_lng === null
            || $shipment->destination_lat === null
            || $shipment->destination_lng === null
        ) {
            return response()->json(
                [
                    'message' =>
                        'Route coordinates are incomplete.',
                ],
                422
            );
        }

        $route = $routes->getRoute(
            [
                'lat' =>
                    (float) $shipment->origin_lat,

                'lon' =>
                    (float) $shipment->origin_lng,
            ],
            [
                'lat' =>
                    (float) $shipment->destination_lat,

                'lon' =>
                    (float) $shipment->destination_lng,
            ]
        );

        $feature =
            $route['features'][0]
            ?? null;

        if (!$feature) {
            return response()->json(
                [
                    'message' =>
                        'Routing provider did not return a usable route.',
                ],
                502
            );
        }

        return response()->json([
            'geometry' =>
                $feature[
                    'geometry'
                ]['coordinates']
                ?? [],

            'summary' =>
                $feature[
                    'properties'
                ]['summary']
                ?? [],
        ]);
    }

    public function explain(
        Shipment $shipment,
        DecisionEngine $engine,
        GeminiService $gemini
    ) {
        $shipment->loadMissing(
            'harvest'
        );

        $analysis =
            $engine->analyze(
                $shipment
            );

        $result =
            $gemini
                ->generateShipmentExplanation([
                    'commodity' =>
                        $shipment->harvest?->commodity
                        ?? 'Unknown',

                    'origin' =>
                        $shipment->origin,

                    'destination' =>
                        $shipment->destination,

                    'status' =>
                        $shipment->status,

                    'remaining_days' =>
                        $analysis[
                            'remaining_days'
                        ] ?? 0,

                    'distance' =>
                        $shipment->distance_km
                        ?? 0,

                    'risk_score' =>
                        $analysis[
                            'risk_score'
                        ],

                    'priority_score' =>
                        $analysis[
                            'priority_score'
                        ],

                    'sustainability_score' =>
                        $analysis[
                            'sustainability_score'
                        ],

                    'recommended_action' =>
                        $analysis[
                            'recommended_action'
                        ] ?? null,

                    'recommendation_reason' =>
                        $analysis[
                            'recommendation_reason'
                        ] ?? null,
                ]);

        if (!$result) {
            return response()->json([
                'recommendation' =>
                    $analysis[
                        'recommended_action'
                    ] ?? 'Review shipment',

                'decision_reason' =>
                    $analysis[
                        'recommendation_reason'
                    ] ?? 'No explanation available.',

                'conclusion' =>
                    'Use the deterministic AgriFlow decision outputs as the operational source of truth.',

                'confidence' => 0,
            ]);
        }

        return response()->json([
            'recommendation' =>
                $result[
                    'recommendation'
                ] ?? (
                    $analysis[
                        'recommended_action'
                    ] ?? '-'
                ),

            'decision_reason' =>
                $result[
                    'decision_reason'
                ] ?? (
                    $analysis[
                        'recommendation_reason'
                    ] ?? '-'
                ),

            'conclusion' =>
                $result[
                    'conclusion'
                ] ?? '-',

            'confidence' =>
                $result[
                    'confidence'
                ] ?? 0,
        ]);
    }
}
