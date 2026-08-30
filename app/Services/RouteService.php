<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RouteService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            (string) env(
                'ORS_BASE_URL',
                'https://api.heigit.org/openrouteservice'
            ),
            '/'
        );
    }

    public function getRoute(
        array $start,
        array $end
    ): array {
        $cacheKey = sprintf(
            'ors-route:%s:%s:%s:%s',
            $start['lat'],
            $start['lon'],
            $end['lat'],
            $end['lon']
        );

        $cached = Cache::get($cacheKey);

        if (
            is_array($cached)
            && !empty($cached['features'])
        ) {
            return $cached;
        }

        $response = $this->request([
            'coordinates' => [
                [
                    (float) $start['lon'],
                    (float) $start['lat'],
                ],
                [
                    (float) $end['lon'],
                    (float) $end['lat'],
                ],
            ],
        ]);

        if (!$response) {
            return [];
        }

        $data = $response->json();

        if (
            !is_array($data)
            || empty($data['features'])
        ) {
            Log::warning(
                'ORS returned a successful response without route features.',
                [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]
            );

            return [];
        }

        Cache::put(
            $cacheKey,
            $data,
            now()->addMinutes(30)
        );

        return $data;
    }

    public function getAlternativeRoutes(
        array $start,
        array $end,
        int $targetCount = 3
    ): array {
        $targetCount = max(
            1,
            min(3, $targetCount)
        );

        $cacheKey = sprintf(
            'ors-alt:%s:%s:%s:%s:%d',
            $start['lat'],
            $start['lon'],
            $end['lat'],
            $end['lon'],
            $targetCount
        );

        $cached = Cache::get($cacheKey);

        if (
            is_array($cached)
            && !empty($cached)
        ) {
            return $cached;
        }

        $response = $this->request([
            'coordinates' => [
                [
                    (float) $start['lon'],
                    (float) $start['lat'],
                ],
                [
                    (float) $end['lon'],
                    (float) $end['lat'],
                ],
            ],

            'alternative_routes' => [
                'target_count' => $targetCount,
                'weight_factor' => 1.4,
                'share_factor' => 0.6,
            ],
        ]);

        if (!$response) {
            return [];
        }

        $features = $response->json(
            'features',
            []
        );

        if (!is_array($features)) {
            return [];
        }

        if (!empty($features)) {
            Cache::put(
                $cacheKey,
                $features,
                now()->addMinutes(30)
            );
        }

        return $features;
    }

    private function request(
        array $payload
    ): ?Response {
        $apiKey = env('ORS_API_KEY');

        if (!$apiKey) {
            Log::error(
                'ORS_API_KEY is missing.'
            );

            return null;
        }

        $url =
            $this->baseUrl
            . '/v2/directions/'
            . 'driving-car/geojson';

        $response = Http::withHeaders([
            'Authorization' => $apiKey,
            'Content-Type' => 'application/json',

            // Important:
            // This matches the direct Tinker request
            // that successfully returned HTTP 200.
            'Accept' => 'application/geo+json',
        ])
            ->timeout(20)
            ->post(
                $url,
                $payload
            );

        if (!$response->successful()) {
            Log::error(
                'ORS route request failed.',
                [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]
            );

            return null;
        }

        return $response;
    }
}
