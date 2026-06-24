<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RouteService
{
    public function getRoute($start, $end)
    {
$response = Http::withHeaders([
    'Authorization' => env('ORS_API_KEY'),
])->post(
    'https://api.openrouteservice.org/v2/directions/driving-car/geojson',
    [
        'coordinates' => [
            [$start['lon'], $start['lat']],
            [$end['lon'], $end['lat']]
        ]
    ]
);

        return $response->json();
    }
}