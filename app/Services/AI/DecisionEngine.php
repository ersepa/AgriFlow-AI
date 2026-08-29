<?php

namespace App\Services\AI;

use App\Models\Shipment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * AgriFlow Decision Engine - Step 1
 *
 * Single source of truth untuk:
 * - operational risk
 * - priority
 * - carbon impact
 * - sustainability
 * - efficiency
 * - digital twin simulation
 *
 * CATATAN:
 * Ini masih transitional heuristic model.
 * Belum diklaim sebagai statistical spoilage probability.
 *
 * Commodity-specific post-harvest model akan kita buat
 * di Step 2.
 */
class DecisionEngine
{
    public function optimizeShipments(): Collection
    {
        return Shipment::with('harvest')
            ->whereIn('status', [
                'Harvested',
                'Packed',
                'In Transit'
            ])
            ->get()
            ->map(function (Shipment $shipment) {

                $analysis = $this->analyze($shipment);

                return array_merge($analysis, [

                    'shipment' => $shipment,

                    'commodity' =>
                        $shipment->harvest?->commodity
                        ?? 'Unknown',

                    'origin' =>
                        $shipment->origin,

                    'destination' =>
                        $shipment->destination,

                    'origin_lat' =>
                        $shipment->origin_lat,

                    'origin_lng' =>
                        $shipment->origin_lng,

                    'destination_lat' =>
                        $shipment->destination_lat,

                    'destination_lng' =>
                        $shipment->destination_lng,
                ]);
            })
            ->sortByDesc('priority_score')
            ->values();
    }

    /**
     * ANALYSIS UTAMA
     *
     * Semua halaman harus mengambil angka dari sini.
     */
    public function analyze(
        Shipment $shipment,
        array $scenario = []
    ): array {

        $shipment->loadMissing('harvest');

        /*
        |--------------------------------------------------------------------------
        | REMAINING SHELF LIFE
        |--------------------------------------------------------------------------
        */

        $remainingDays =
            $this->remainingShelfLifeDays($shipment);


        /*
        |--------------------------------------------------------------------------
        | OPERATIONAL RISK
        |--------------------------------------------------------------------------
        */

        $riskComponents =
            $this->riskComponents(
                $shipment,
                $remainingDays,
                $scenario
            );

        $riskScore = $this->clamp(
            (int) round(
                array_sum($riskComponents)
            ),
            0,
            100
        );


        /*
        |--------------------------------------------------------------------------
        | PRIORITY
        |--------------------------------------------------------------------------
        */

        $priorityScore =
            $this->calculatePriorityScore(
                $shipment,
                $riskScore,
                $remainingDays
            );


        /*
        |--------------------------------------------------------------------------
        | CARBON
        |--------------------------------------------------------------------------
        */

        $carbonKg =
            $this->calculateCarbonKg(
                $shipment,
                $scenario
            );

        /*
         * Jangan campur langsung kg CO2
         * dengan score 0-100.
         *
         * Carbon dinormalisasi dulu.
         */
        $carbonImpactScore =
            $this->calculateCarbonImpactScore(
                $carbonKg
            );


        /*
        |--------------------------------------------------------------------------
        | SUSTAINABILITY
        |--------------------------------------------------------------------------
        */

        $sustainabilityScore =
            $this->calculateSustainabilityScore(
                $riskScore,
                $carbonImpactScore
            );


        /*
        |--------------------------------------------------------------------------
        | LOGISTICS EFFICIENCY
        |--------------------------------------------------------------------------
        */

        $efficiencyScore =
            $this->calculateEfficiencyScore(
                $shipment,
                $riskScore,
                $sustainabilityScore,
                $scenario
            );


        /*
        |--------------------------------------------------------------------------
        | RECOMMENDATION
        |--------------------------------------------------------------------------
        */

        $recommendation =
            $this->buildOperationalRecommendation(
                $shipment,
                $riskScore,
                $remainingDays,
                $scenario
            );


        /*
        |--------------------------------------------------------------------------
        | OUTPUT
        |--------------------------------------------------------------------------
        */

        return [

            // =========================
            // RISK
            // =========================

            'risk_score' =>
                $riskScore,

            'risk_index' =>
                $riskScore,

            'risk_level' =>
                $this->getRiskLevel($riskScore),


            // =========================
            // PRIORITY
            // =========================

            'priority_score' =>
                $priorityScore,

            'priority_level' =>
                $this->getPriorityLevel(
                    $priorityScore
                ),


            // =========================
            // ENVIRONMENT / CARBON
            // =========================

            // backwards compatibility
            'carbon_score' =>
                $carbonKg,

            'carbon_kg' =>
                $carbonKg,

            'carbon_impact_score' =>
                $carbonImpactScore,


            // =========================
            // PERFORMANCE
            // =========================

            'sustainability_score' =>
                $sustainabilityScore,

            'efficiency_score' =>
                $efficiencyScore,


            // =========================
            // CONTEXT
            // =========================

            'remaining_days' =>
                $remainingDays,

            /*
             * Data Confidence != AI Accuracy.
             *
             * Ini hanya mengukur kelengkapan
             * input shipment.
             */
            'data_confidence' =>
                $this->calculateDataConfidence(
                    $shipment
                ),


            // =========================
            // EXPLAINABILITY
            // =========================

            'risk_components' =>
                $riskComponents,

            'explainability' =>
                $this->generateExplainability(
                    $shipment,
                    $riskComponents,
                    $remainingDays
                ),

            'prediction_data' =>
                $this->buildRiskProjection(
                    $riskScore,
                    $remainingDays
                ),


            // =========================
            // DECISION
            // =========================

            'recommended_action' =>
                $recommendation['action'],

            'recommendation_reason' =>
                $recommendation['reason'],

            'dispatch_deadline' =>
                $recommendation[
                    'dispatch_deadline'
                ],

            'recommended_vehicle' =>
                $recommendation[
                    'recommended_vehicle'
                ],

            'recommended_storage' =>
                $recommendation[
                    'recommended_storage'
                ],

            /*
             * Sementara.
             *
             * Step 2 nanti diganti dengan
             * Quality-at-Arrival model sebenarnya.
             */
            'estimated_arrival_quality' =>
                $this->estimateArrivalQuality(
                    $riskScore
                ),

            'food_waste_level' =>
                $this->getRiskLevel($riskScore),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | DIGITAL TWIN
    |--------------------------------------------------------------------------
    */

    public function simulate(
        Shipment $shipment,
        array $scenario
    ): array {

        /*
         * BEFORE dan AFTER memakai engine
         * yang sama.
         */

        $before =
            $this->analyze($shipment);

        $after =
            $this->analyze(
                $shipment,
                $scenario
            );


        $baseDuration =
            (float) (
                $shipment->duration_hours
                ?? 0
            );

        $delay =
            max(
                0,
                (float) (
                    $scenario['delay']
                    ?? 0
                )
            );

        $routeOptimized =
            filter_var(
                $scenario['route']
                ?? false,
                FILTER_VALIDATE_BOOLEAN
            );


        /*
         * Untuk sementara optimized route
         * dianggap memangkas ETA 10%.
         *
         * Step route intelligence nanti
         * menggunakan real alternative route.
         */

        $afterDuration =
            $baseDuration;

        if (
            $routeOptimized
            && $afterDuration > 0
        ) {

            $afterDuration *= 0.90;
        }

        $afterDuration += $delay;


        return [

            'before' => [

                'risk_score' =>
                    $before[
                        'risk_score'
                    ],

                'sustainability_score' =>
                    $before[
                        'sustainability_score'
                    ],

                'carbon' =>
                    round(
                        $before['carbon_kg'],
                        1
                    ),

                'duration' =>
                    round(
                        $baseDuration,
                        1
                    ),

                'vehicle' =>
                    'Standard Truck',
            ],


            'after' => [

                'risk_score' =>
                    $after[
                        'risk_score'
                    ],

                'sustainability_score' =>
                    $after[
                        'sustainability_score'
                    ],

                'carbon' =>
                    round(
                        $after['carbon_kg'],
                        1
                    ),

                'carbon_saved' =>
                    round(
                        $before['carbon_kg']
                        -
                        $after['carbon_kg'],
                        1
                    ),

                'duration' =>
                    round(
                        $afterDuration,
                        1
                    ),

                'vehicle' =>
                    $this->displayVehicle(
                        $scenario['vehicle']
                        ?? 'Truck'
                    ),
            ],

            /*
             * Ini berguna nanti kalau UI
             * mau menampilkan detail engine.
             */

            'analysis_before' =>
                $before,

            'analysis_after' =>
                $after,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | REMAINING SHELF LIFE
    |--------------------------------------------------------------------------
    */

    private function remainingShelfLifeDays(
        Shipment $shipment
    ): int {

        $expiryDate =
            $shipment->harvest?->expiry_date;

        if (!$expiryDate) {
            return 0;
        }

        return (int) floor(

            Carbon::now()->diffInDays(

                Carbon::parse(
                    $expiryDate
                ),

                false
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RISK COMPONENTS
    |--------------------------------------------------------------------------
    */

    private function riskComponents(
        Shipment $shipment,
        int $remainingDays,
        array $scenario = []
    ): array {

        $distance =
            (float) (
                $shipment->distance_km
                ?? 0
            );

        $duration =
            (float) (
                $shipment->duration_hours
                ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | 1. SHELF LIFE
        |--------------------------------------------------------------------------
        |
        | Maksimum 45 point.
        |
        */

        $shelfLife =
            match (true) {

                $remainingDays <= 0 =>
                    45,

                $remainingDays <= 2 =>
                    36,

                $remainingDays <= 5 =>
                    25,

                $remainingDays <= 7 =>
                    15,

                $remainingDays <= 14 =>
                    8,

                default =>
                    4,
            };


        /*
        |--------------------------------------------------------------------------
        | 2. DISTANCE
        |--------------------------------------------------------------------------
        |
        | Maksimum 20.
        |
        */

        $distanceRisk =
            match (true) {

                $distance >= 1000 =>
                    20,

                $distance >= 500 =>
                    16,

                $distance >= 300 =>
                    12,

                $distance >= 100 =>
                    8,

                default =>
                    3,
            };


        /*
        |--------------------------------------------------------------------------
        | 3. TRANSIT TIME
        |--------------------------------------------------------------------------
        |
        | Time exposure lebih relevan untuk
        | produk perishables daripada hanya
        | melihat jarak.
        |
        */

        $durationRisk =
            match (true) {

                $duration >= 24 =>
                    15,

                $duration >= 12 =>
                    12,

                $duration >= 8 =>
                    9,

                $duration >= 4 =>
                    7,

                $duration > 0 =>
                    3,

                default =>
                    0,
            };


        /*
        |--------------------------------------------------------------------------
        | 4. SHIPMENT STATUS
        |--------------------------------------------------------------------------
        */

        $statusRisk =
            match ($shipment->status) {

                'Harvested' =>
                    10,

                'Packed' =>
                    7,

                'In Transit' =>
                    5,

                'Delivered' =>
                    0,

                default =>
                    4,
            };


        return [

            'remaining_shelf_life' =>
                $shelfLife,

            'transport_distance' =>
                $distanceRisk,

            'transit_duration' =>
                $durationRisk,

            'shipment_status' =>
                $statusRisk,

            'scenario_adjustment' =>
                $this->scenarioRiskModifier(
                    $scenario
                ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | DIGITAL TWIN RISK MODIFIER
    |--------------------------------------------------------------------------
    |
    | Ini masih placeholder.
    |
    | Step 2 akan diganti berdasarkan
    | commodity profile.
    |
    */

    private function scenarioRiskModifier(
        array $scenario
    ): int {

        if ($scenario === []) {
            return 0;
        }

        $modifier = 0;


        $vehicle =
            strtolower(
                (string) (
                    $scenario['vehicle']
                    ?? 'truck'
                )
            );


        $temperature =
            isset(
                $scenario['temperature']
            )
                ? (float) $scenario[
                    'temperature'
                ]
                : null;


        $delay =
            max(
                0,
                (float) (
                    $scenario['delay']
                    ?? 0
                )
            );


        $routeOptimized =
            filter_var(
                $scenario['route']
                ?? false,
                FILTER_VALIDATE_BOOLEAN
            );


        /*
        |--------------------------------------------------------------------------
        | VEHICLE
        |--------------------------------------------------------------------------
        */

        $modifier +=
            match ($vehicle) {

                'cold',
                'refrigerated',
                'refrigerated truck' =>
                    -10,

                'ship' =>
                    -3,

                'plane' =>
                    6,

                default =>
                    0,
            };


        /*
        |--------------------------------------------------------------------------
        | TEMPERATURE
        |--------------------------------------------------------------------------
        |
        | Masih generic placeholder.
        |
        | JANGAN interpretasikan sebagai
        | recommendation suhu pertanian.
        |
        */

        if ($temperature !== null) {

            if ($temperature >= 30) {

                $modifier += 10;

            } elseif ($temperature >= 25) {

                $modifier += 5;

            } elseif (
                $temperature >= 10
                &&
                $temperature <= 15
            ) {

                $modifier -= 4;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ROUTE
        |--------------------------------------------------------------------------
        */

        if ($routeOptimized) {
            $modifier -= 5;
        }


        /*
        |--------------------------------------------------------------------------
        | DELAY
        |--------------------------------------------------------------------------
        */

        $modifier +=
            (int) round(
                $delay * 3
            );


        return $modifier;
    }


    /*
    |--------------------------------------------------------------------------
    | PRIORITY SCORE
    |--------------------------------------------------------------------------
    */

    private function calculatePriorityScore(
        Shipment $shipment,
        int $riskScore,
        int $remainingDays
    ): int {

        /*
         * Urgency dinormalisasi 0-100.
         */

        $urgencyScore =
            match (true) {

                $remainingDays <= 0 =>
                    100,

                $remainingDays <= 1 =>
                    95,

                $remainingDays <= 2 =>
                    85,

                $remainingDays <= 5 =>
                    65,

                $remainingDays <= 7 =>
                    45,

                default =>
                    20,
            };


        $stageScore =
            match ($shipment->status) {

                'Harvested' =>
                    100,

                'Packed' =>
                    75,

                'In Transit' =>
                    55,

                'Delivered' =>
                    0,

                default =>
                    40,
            };


        /*
         * Priority =
         *
         * 65% Operational Risk
         * 25% Shelf-Life Urgency
         * 10% Logistics Stage
         */

        return $this->clamp(

            (int) round(

                ($riskScore * 0.65)

                +

                ($urgencyScore * 0.25)

                +

                ($stageScore * 0.10)
            ),

            0,

            100
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CARBON
    |--------------------------------------------------------------------------
    */

    private function calculateCarbonKg(
        Shipment $shipment,
        array $scenario
    ): float {

        $baseCarbon =
            (float) (
                $shipment->carbon_emission
                ?? 0
            );


        /*
         * Fallback sementara ke formula
         * existing project.
         *
         * Dedicated carbon model akan
         * kita upgrade kemudian.
         */

        if (
            $baseCarbon <= 0
            &&
            ($shipment->distance_km ?? 0) > 0
        ) {

            $baseCarbon =
                (float)
                $shipment->distance_km
                *
                0.12;
        }


        $vehicle =
            strtolower(
                (string) (
                    $scenario['vehicle']
                    ?? 'truck'
                )
            );


        $vehicleFactor =
            match ($vehicle) {

                /*
                 * Refrigeration membutuhkan
                 * tambahan energi.
                 */
                'cold',
                'refrigerated',
                'refrigerated truck' =>
                    1.15,

                'ship' =>
                    0.60,

                'plane' =>
                    1.50,

                default =>
                    1.00,
            };


        return round(
            max(
                0,
                $baseCarbon * $vehicleFactor
            ),
            2
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CARBON NORMALIZATION
    |--------------------------------------------------------------------------
    */

    private function calculateCarbonImpactScore(
        float $carbonKg
    ): int {

        /*
         * Temporary normalization.
         *
         * Ini mencegah:
         *
         * risk score + kg carbon
         *
         * langsung dicampur.
         */

        return $this->clamp(

            (int) round(

                ($carbonKg / 120)
                *
                100
            ),

            0,

            100
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SUSTAINABILITY
    |--------------------------------------------------------------------------
    */

    private function calculateSustainabilityScore(
        int $riskScore,
        int $carbonImpactScore
    ): int {

        return $this->clamp(

            (int) round(

                100

                -

                ($riskScore * 0.65)

                -

                ($carbonImpactScore * 0.35)
            ),

            0,

            100
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EFFICIENCY
    |--------------------------------------------------------------------------
    */

    private function calculateEfficiencyScore(
        Shipment $shipment,
        int $riskScore,
        int $sustainabilityScore,
        array $scenario
    ): int {

        $duration =
            (float) (
                $shipment->duration_hours
                ?? 0
            );


        $delay =
            max(
                0,
                (float) (
                    $scenario['delay']
                    ?? 0
                )
            );


        $durationPenalty =
            min(
                30,
                ($duration + $delay)
                *
                1.5
            );


        return $this->clamp(

            (int) round(

                ($sustainabilityScore * 0.55)

                +

                ((100 - $riskScore) * 0.35)

                +

                (
                    (100 - $durationPenalty)
                    *
                    0.10
                )
            ),

            0,

            100
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DATA CONFIDENCE
    |--------------------------------------------------------------------------
    */

    private function calculateDataConfidence(
        Shipment $shipment
    ): int {

        $checks = [

            filled(
                $shipment
                    ->harvest
                    ?->commodity
            ),

            filled(
                $shipment
                    ->harvest
                    ?->expiry_date
            ),

            filled(
                $shipment->origin
            ),

            filled(
                $shipment->destination
            ),

            $shipment->distance_km
                !== null,

            $shipment->duration_hours
                !== null,

            $shipment->origin_lat !== null
                &&
                $shipment->origin_lng !== null,

            $shipment->destination_lat !== null
                &&
                $shipment->destination_lng !== null,
        ];


        $complete =
            count(
                array_filter($checks)
            );


        return (int) round(

            ($complete / count($checks))
            *
            100
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXPLAINABILITY
    |--------------------------------------------------------------------------
    */

    private function generateExplainability(
        Shipment $shipment,
        array $components,
        int $remainingDays
    ): array {

        $factors = [

            [
                'title' =>
                    'Remaining Shelf Life',

                'icon' =>
                    '📦',

                'impact' =>
                    max(
                        0,
                        $components[
                            'remaining_shelf_life'
                        ]
                    ),

                'reason' =>
                    $remainingDays <= 0

                    ? 'The harvest has reached or exceeded the recorded expiry date.'

                    : "The harvest has approximately {$remainingDays} day(s) of recorded shelf life remaining.",
            ],


            [
                'title' =>
                    'Transportation Distance',

                'icon' =>
                    '🚚',

                'impact' =>
                    max(
                        0,
                        $components[
                            'transport_distance'
                        ]
                    ),

                'reason' =>
                    'Longer transport distance increases operational exposure before delivery.',
            ],


            [
                'title' =>
                    'Transit Duration',

                'icon' =>
                    '⏱️',

                'impact' =>
                    max(
                        0,
                        $components[
                            'transit_duration'
                        ]
                    ),

                'reason' =>
                    'Longer transit time consumes more of the product’s remaining usable life.',
            ],


            [
                'title' =>
                    'Shipment Status',

                'icon' =>
                    '📍',

                'impact' =>
                    max(
                        0,
                        $components[
                            'shipment_status'
                        ]
                    ),

                'reason' =>
                    'The current logistics stage affects how urgently the shipment should be handled.',
            ],
        ];


        if (
            ($components[
                'scenario_adjustment'
            ] ?? 0)
            !== 0
        ) {

            $factors[] = [

                'title' =>
                    'Scenario Conditions',

                'icon' =>
                    '🧪',

                'impact' =>
                    abs(
                        (int)
                        $components[
                            'scenario_adjustment'
                        ]
                    ),

                'reason' =>
                    $components[
                        'scenario_adjustment'
                    ] > 0

                    ? 'The selected scenario increases operational exposure.'

                    : 'The selected scenario reduces operational exposure.',
            ];
        }


        usort(
            $factors,

            fn (
                array $a,
                array $b
            ) =>
                $b['impact']
                <=>
                $a['impact']
        );


        return $factors;
    }


    /*
    |--------------------------------------------------------------------------
    | RISK PROJECTION
    |--------------------------------------------------------------------------
    */

    private function buildRiskProjection(
        int $riskScore,
        int $remainingDays
    ): array {

        $projection = [];


        /*
         * Ini bukan ML forecast.
         *
         * Ini hanya operational projection
         * untuk UI sementara.
         */

        $urgencyFactor =
            $remainingDays <= 0

                ? 1.5

                : max(
                    0.35,
                    1
                    -
                    min(
                        $remainingDays,
                        14
                    )
                    /
                    20
                );


        for (
            $day = 1;
            $day <= 7;
            $day++
        ) {

            $projected =
                $riskScore

                +

                (
                    ($day ** 1.45)
                    *
                    4.5
                    *
                    $urgencyFactor
                );


            $projection[] = [

                'day' =>
                    $day,

                'risk' =>
                    $this->clamp(
                        (int) round(
                            $projected
                        ),
                        0,
                        100
                    ),
            ];
        }


        return $projection;
    }


    /*
    |--------------------------------------------------------------------------
    | OPERATIONAL RECOMMENDATION
    |--------------------------------------------------------------------------
    */

    private function buildOperationalRecommendation(
        Shipment $shipment,
        int $riskScore,
        int $remainingDays,
        array $scenario
    ): array {

        if ($remainingDays <= 0) {

            return [

                'action' =>
                    'Escalate shipment immediately',

                'reason' =>
                    'The recorded shelf life has been reached or exceeded. The shipment requires immediate operational review.',

                'dispatch_deadline' =>
                    'Immediate review',

                'recommended_vehicle' =>
                    'Review cold-chain requirement',

                'recommended_storage' =>
                    'Review commodity-specific storage',
            ];
        }


        if (
            $riskScore >= 70
            ||
            $remainingDays <= 2
        ) {

            return [

                'action' =>
                    'Dispatch immediately',

                'reason' =>
                    'Short remaining shelf life and/or high operational risk makes delay undesirable.',

                'dispatch_deadline' =>
                    'Within 6 hours',

                'recommended_vehicle' =>
                    'Review refrigerated transport',

                'recommended_storage' =>
                    'Review commodity-specific cold storage',
            ];
        }


        if (
            $riskScore >= 50
            ||
            ($shipment->distance_km ?? 0)
            >= 300
        ) {

            return [

                'action' =>
                    'Review route and cold-chain',

                'reason' =>
                    'The shipment has meaningful transport exposure and should be checked for route and storage improvements.',

                'dispatch_deadline' =>
                    'Today',

                'recommended_vehicle' =>
                    'Evaluate refrigerated transport',

                'recommended_storage' =>
                    'Review commodity-specific storage',
            ];
        }


        if ($riskScore >= 30) {

            return [

                'action' =>
                    'Prioritize shipment',

                'reason' =>
                    'The shipment is currently manageable but should be processed ahead of lower-risk cargo.',

                'dispatch_deadline' =>
                    'Within 24 hours',

                'recommended_vehicle' =>
                    'Regular Truck',

                'recommended_storage' =>
                    'Standard storage; verify commodity requirement',
            ];
        }


        return [

            'action' =>
                'Monitor shipment',

            'reason' =>
                'Current operational exposure is low under the available shipment data.',

            'dispatch_deadline' =>
                'Flexible',

            'recommended_vehicle' =>
                'Regular Truck',

            'recommended_storage' =>
                'Standard storage; verify commodity requirement',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | TEMPORARY QUALITY INDICATOR
    |--------------------------------------------------------------------------
    */

    private function estimateArrivalQuality(
        int $riskScore
    ): int {

        /*
         * BUKAN biological quality model.
         *
         * Step 2 nanti fungsi ini diganti
         * dengan QualityPredictionService.
         */

        return $this->clamp(

            (int) round(

                100
                -
                ($riskScore * 0.65)
            ),

            0,

            100
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RISK LEVEL
    |--------------------------------------------------------------------------
    */

    private function getRiskLevel(
        int $riskScore
    ): string {

        return match (true) {

            $riskScore >= 70 =>
                'High',

            $riskScore >= 40 =>
                'Medium',

            default =>
                'Low',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | PRIORITY LEVEL
    |--------------------------------------------------------------------------
    */

    private function getPriorityLevel(
        int $priorityScore
    ): string {

        return match (true) {

            $priorityScore >= 80 =>
                'Critical',

            $priorityScore >= 60 =>
                'High',

            $priorityScore >= 40 =>
                'Medium',

            default =>
                'Low',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | VEHICLE DISPLAY
    |--------------------------------------------------------------------------
    */

    private function displayVehicle(
        string $vehicle
    ): string {

        return match (
            strtolower($vehicle)
        ) {

            'cold',
            'refrigerated',
            'refrigerated truck' =>
                'Refrigerated Truck',

            'ship' =>
                'Ship',

            'plane' =>
                'Plane',

            default =>
                'Standard Truck',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    private function clamp(
        int $value,
        int $min,
        int $max
    ): int {

        return max(
            $min,
            min(
                $max,
                $value
            )
        );
    }
}