<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EnvironmentalService
{
    public function getEnvironment($shipment)
    {
$dashboardLat = env('DASHBOARD_LAT', -6.2088);
$dashboardLng = env('DASHBOARD_LNG', 106.8456);

$weather = $this->getWeather(
    $dashboardLat,
    $dashboardLng
);

if (!$weather) {

    return [
        'location' => env('DASHBOARD_LOCATION', 'Jakarta'),
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
        'weather_score' => 0,
        'route_score' => 0,
        'route_condition' => 'Unavailable',
        'carbon_condition' => 'Unavailable',
        'carbon_index' => 0,
        'confidence' => 0,
        'eta' => null,
        'environmental_risk' => 0,
        'risk_level' => 'Unknown',
        'recommendation' => 'Weather service is temporarily unavailable.',
        'updated_at' => now()->format('H:i'),
    ];

}

$current = $weather['current'];
$hourly  = $weather['hourly'];

/*
|--------------------------------------------------------------------------
| AI Confidence
|--------------------------------------------------------------------------
*/

$confidence = 100;

// Data tidak lengkap
if (!isset($current['temperature_2m'])) $confidence -= 8;
if (!isset($current['relative_humidity_2m'])) $confidence -= 6;
if (!isset($current['rain'])) $confidence -= 6;
if (!isset($current['wind_speed_10m'])) $confidence -= 5;
if (!isset($current['cloud_cover'])) $confidence -= 5;

// Cuaca ekstrem → prediksi lebih sulit
if (($current['rain'] ?? 0) > 5) {
    $confidence -= 4;
}

if (($current['wind_speed_10m'] ?? 0) > 20) {
    $confidence -= 3;
}

if (($current['cloud_cover'] ?? 0) > 90) {
    $confidence -= 2;
}

$confidence = max(82, min(99, round($confidence)));

/*
|--------------------------------------------------------------------------
| Estimated Travel Time
|--------------------------------------------------------------------------
*/

$eta = 25;

// Hujan
$eta += ($current['rain'] ?? 0) * 2;

// Angin
$eta += ($current['wind_speed_10m'] ?? 0) * 0.3;

// Cloud
$eta += ($current['cloud_cover'] ?? 0) * 0.05;

$eta = round($eta);


        /*
        |--------------------------------------------------------------------------
        | Route Condition
        |--------------------------------------------------------------------------
        */

$routeScore = 100;

// Rain
if (($current['rain'] ?? 0) > 5) {

    $routeScore -= 35;

} elseif (($current['rain'] ?? 0) > 1) {

    $routeScore -= 15;

}

// Wind
if (($current['wind_speed_10m'] ?? 0) > 25) {

    $routeScore -= 20;

} elseif (($current['wind_speed_10m'] ?? 0) > 15) {

    $routeScore -= 10;

}

// Cloud
if (($current['cloud_cover'] ?? 0) > 80) {

    $routeScore -= 10;

}

$routeScore = max(0,min(100,$routeScore));

if ($routeScore >= 90) {

    $routeCondition = "Excellent";

} elseif ($routeScore >= 75) {

    $routeCondition = "Good";

} elseif ($routeScore >= 60) {

    $routeCondition = "Moderate";

} else {

    $routeCondition = "Poor";

}

        /*
        |--------------------------------------------------------------------------
        | Carbon Condition
        |--------------------------------------------------------------------------
        */

        $wind = $current['wind_speed_10m'];

        if ($wind < 5) {

            $carbon = "Medium";

        } elseif ($wind < 15) {

            $carbon = "Low";

        } else {

            $carbon = "Excellent";

        }

        /*
        |--------------------------------------------------------------------------
        | Environmental Risk
        |--------------------------------------------------------------------------
        */

$risk = 0;

// Humidity
$risk += ($current['relative_humidity_2m'] ?? 0) * 0.25;

// Rain
$risk += ($current['rain'] ?? 0) * 12;

// Wind
$risk += ($current['wind_speed_10m'] ?? 0) * 1.3;

// Cloud
$risk += ($current['cloud_cover'] ?? 0) * 0.12;

$risk = round(min(100,$risk));

        $risk = min(100, round($risk));

        if ($risk >= 70) {

            $riskLevel = "High";

        } elseif ($risk >= 40) {

            $riskLevel = "Medium";

        } else {

            $riskLevel = "Low";

        }

        /*
        |--------------------------------------------------------------------------
        | Recommendation
        |--------------------------------------------------------------------------
        */

        if ($riskLevel == "High") {

            $recommendation =
                "Heavy environmental risk detected. AI recommends immediate route optimization and refrigerated transportation.";

        } elseif ($riskLevel == "Medium") {

            $recommendation =
                "Environmental conditions remain acceptable, but AI suggests continuous monitoring.";

        } else {

            $recommendation =
                "Current environmental conditions are optimal. No operational adjustment required.";

        }

$weatherScore = 100;

$weatherScore -= ($current['rain'] ?? 0) * 5;

$weatherScore -= ($current['wind_speed_10m'] ?? 0);

$weatherScore -= ($current['cloud_cover'] ?? 0) * 0.15;

$weatherScore = max(0,min(100,round($weatherScore)));

$carbonIndex = round(

    100 -

    ($current['wind_speed_10m']*2)

    -

    ($current['rain']*3)

);

$carbonIndex = max(45,min(100,$carbonIndex));

return [

    'location'=>env('DASHBOARD_LOCATION','Jakarta'),

    'weather'=>$current,

'forecast'=>$hourly,

    'weather_score'=>$weatherScore,

    'route_score'=>$routeScore,

    'route_condition'=>$routeCondition,

    'carbon_condition'=>$carbon,

    'carbon_index'=>$carbonIndex,

    'confidence'=>$confidence,

    'eta'=>$eta,

    'environmental_risk'=>$risk,

    'risk_level'=>$riskLevel,

    'recommendation'=>$recommendation,

    'updated_at'=>now()->format('H:i')

];
    }

private function getWeather($lat, $lng)
{
    $response = Http::get(env('OPEN_METEO_URL'), [

        'latitude' => $lat,

        'longitude' => $lng,

        'current' =>
            'temperature_2m,relative_humidity_2m,rain,cloud_cover,wind_speed_10m',

        'hourly' =>
'temperature_2m,relative_humidity_2m,precipitation_probability,cloud_cover,wind_speed_10m',

        'forecast_days' => 1

    ]);

    if (!$response->successful()) {
        return null;
    }

    $data = $response->json();

    if (
    !isset($data['current']) ||
    !isset($data['hourly'])
) {
    return null;
}

return [

    'current' => $data['current'] ?? [],

    'hourly' => $data['hourly'] ?? [],

];
}
}