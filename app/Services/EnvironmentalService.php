<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EnvironmentalService
{
    public function getEnvironment($shipment)
    {
        $dashboardLat = env(
            'DASHBOARD_LAT',
            -6.2088
        );

        $dashboardLng = env(
            'DASHBOARD_LNG',
            106.8456
        );

        $weather = $this->getWeather(
            $dashboardLat,
            $dashboardLng
        );

        if (!$weather) {
            return [
                'location' => env(
                    'DASHBOARD_LOCATION',
                    'Jakarta'
                ),

                'weather' => [
                    'temperature_2m' => null,
                    'relative_humidity_2m' => null,
                    'rain' => null,
                    'cloud_cover' => null,
                    'wind_speed_10m' => null,
                ],

                'forecast' => [
                    'time' => [],
                    'temperature_2m' => [],
                    'relative_humidity_2m' => [],
                    'precipitation_probability' => [],
                    'cloud_cover' => [],
                    'wind_speed_10m' => [],
                ],

                'weather_suitability_score' => null,
                'weather_suitability' => 'Unavailable',

                'data_coverage' => 0,

                'eta' => null,

                'environmental_condition_index' => null,
                'environmental_condition_level' => 'Unavailable',

                'recommendation' =>
                    'Weather service is temporarily unavailable.',

                'updated_at' =>
                    now()->format('H:i'),
            ];
        }

        $current =
            $weather['current'];

        $hourly =
            $weather['hourly'];

        /*
        |--------------------------------------------------------------------------
        | Environmental Data Coverage
        |--------------------------------------------------------------------------
        |
        | Measures completeness of required current-weather fields.
        | This is input coverage, not AI/model accuracy.
        |
        */

        $requiredCurrentFields = [
            'temperature_2m',
            'relative_humidity_2m',
            'rain',
            'wind_speed_10m',
            'cloud_cover',
        ];

        $availableCurrentFields =
            collect(
                $requiredCurrentFields
            )
                ->filter(
                    fn ($field) =>
                        array_key_exists(
                            $field,
                            $current
                        )
                        && $current[$field] !== null
                )
                ->count();

        $dataCoverage = round(
            (
                $availableCurrentFields
                / count(
                    $requiredCurrentFields
                )
            ) * 100
        );

        /*
        |--------------------------------------------------------------------------
        | Route ETA
        |--------------------------------------------------------------------------
        |
        | EnvironmentalService does not invent travel-time estimates.
        | Route ETA belongs to the routing/ORS layer.
        |
        */

        $eta = null;

        /*
        |--------------------------------------------------------------------------
        | Weather Suitability Index
        |--------------------------------------------------------------------------
        |
        | Deterministic operational weather heuristic.
        | This is not route travel-time accuracy or failure probability.
        |
        */

        $weatherSuitabilityScore = 100;

        $rain =
            $current['rain']
            ?? 0;

        $wind =
            $current['wind_speed_10m']
            ?? 0;

        $cloud =
            $current['cloud_cover']
            ?? 0;

        if ($rain > 5) {
            $weatherSuitabilityScore -= 35;
        } elseif ($rain > 1) {
            $weatherSuitabilityScore -= 15;
        }

        if ($wind > 25) {
            $weatherSuitabilityScore -= 20;
        } elseif ($wind > 15) {
            $weatherSuitabilityScore -= 10;
        }

        if ($cloud > 80) {
            $weatherSuitabilityScore -= 10;
        }

        $weatherSuitabilityScore =
            max(
                0,
                min(
                    100,
                    $weatherSuitabilityScore
                )
            );

        if ($weatherSuitabilityScore >= 90) {
            $weatherSuitability =
                'Excellent';
        } elseif ($weatherSuitabilityScore >= 75) {
            $weatherSuitability =
                'Good';
        } elseif ($weatherSuitabilityScore >= 60) {
            $weatherSuitability =
                'Moderate';
        } else {
            $weatherSuitability =
                'Poor';
        }

        /*
        |--------------------------------------------------------------------------
        | Environmental Condition Index
        |--------------------------------------------------------------------------
        |
        | Deterministic environmental condition indicator.
        | It is not a probability of spoilage or route failure.
        |
        */

        $environmentalConditionIndex = 0;

        $environmentalConditionIndex +=
            (
                $current[
                    'relative_humidity_2m'
                ]
                ?? 0
            ) * 0.25;

        $environmentalConditionIndex +=
            $rain * 12;

        $environmentalConditionIndex +=
            $wind * 1.3;

        $environmentalConditionIndex +=
            $cloud * 0.12;

        $environmentalConditionIndex =
            round(
                min(
                    100,
                    $environmentalConditionIndex
                )
            );

        if (
            $environmentalConditionIndex
            >= 70
        ) {
            $environmentalConditionLevel =
                'High';
        } elseif (
            $environmentalConditionIndex
            >= 40
        ) {
            $environmentalConditionLevel =
                'Medium';
        } else {
            $environmentalConditionLevel =
                'Low';
        }

        /*
        |--------------------------------------------------------------------------
        | Operational Recommendation
        |--------------------------------------------------------------------------
        */

        if (
            $environmentalConditionLevel
            === 'High'
        ) {
            $recommendation =
                'Adverse weather conditions detected. Review shipment timing, route feasibility, and commodity-specific handling requirements before dispatch.';
        } elseif (
            $environmentalConditionLevel
            === 'Medium'
        ) {
            $recommendation =
                'Weather conditions may affect operations. Continue monitoring and review active shipment constraints.';
        } else {
            $recommendation =
                'No major weather-related operational concern is currently detected.';
        }

        return [
            'location' => env(
                'DASHBOARD_LOCATION',
                'Jakarta'
            ),

            'weather' =>
                $current,

            'forecast' =>
                $hourly,

            'weather_suitability_score' =>
                $weatherSuitabilityScore,

            'weather_suitability' =>
                $weatherSuitability,

            'data_coverage' =>
                $dataCoverage,

            'eta' =>
                $eta,

            'environmental_condition_index' =>
                $environmentalConditionIndex,

            'environmental_condition_level' =>
                $environmentalConditionLevel,

            'recommendation' =>
                $recommendation,

            'updated_at' =>
                now()->format('H:i'),
        ];
    }

    private function getWeather(
        $lat,
        $lng
    ) {
        try {
            $url =
                env('OPEN_METEO_URL');

            if (!$url) {
                return null;
            }

            $response =
                Http::timeout(15)
                    ->retry(
                        2,
                        500
                    )
                    ->get(
                        $url,
                        [
                            'latitude' =>
                                $lat,

                            'longitude' =>
                                $lng,

                            'current' =>
                                'temperature_2m,relative_humidity_2m,rain,cloud_cover,wind_speed_10m',

                            'hourly' =>
                                'temperature_2m,relative_humidity_2m,precipitation_probability,cloud_cover,wind_speed_10m',

                            'forecast_days' =>
                                2,
                        ]
                    );

            if (
                !$response->successful()
            ) {
                return null;
            }

            $data =
                $response->json();

            if (
                !isset(
                    $data['current']
                )
                ||
                !isset(
                    $data['hourly']
                )
            ) {
                return null;
            }

            return [
                'current' =>
                    $data['current'],

                'hourly' =>
                    $data['hourly'],
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}