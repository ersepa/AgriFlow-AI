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
        $configuredBaseUrl = trim((string) config(
            'services.ors.base_url',
            'https://api.openrouteservice.org'
        ));

        // Normalize the legacy public endpoint previously used by this project.
        // Keep custom ORS-compatible endpoints intact.
        if (
            $configuredBaseUrl === ''
            || str_contains($configuredBaseUrl, 'api.heigit.org/openrouteservice')
        ) {
            $configuredBaseUrl = 'https://api.openrouteservice.org';
        }

        $this->baseUrl = rtrim($configuredBaseUrl, '/');
    }

    public function getRoute(
        array $start,
        array $end
    ): array {
        if (
            !$this->hasValidCoordinates($start) ||
            !$this->hasValidCoordinates($end)
        ) {
            Log::warning('ORS received invalid route coordinates.');

            return [];
        }

        $startLat = (float) $start['lat'];
        $startLon = (float) $start['lon'];
        $endLat = (float) $end['lat'];
        $endLon = (float) $end['lon'];

        $cacheKey = sprintf(
            'ors-route:%.8F:%.8F:%.8F:%.8F',
            $startLat,
            $startLon,
            $endLat,
            $endLon
        );

        $cached = Cache::get($cacheKey);

        if (
            is_array($cached) &&
            !empty($cached['features'])
        ) {
            $cached = $this->normalizeFeatureCollection($cached);

            if ($cached !== []) {
                return $cached;
            }

            Cache::forget($cacheKey);
        }

        $response = $this->request([
            'coordinates' => [
                [$startLon, $startLat],
                [$endLon, $endLat],
            ],
        ]);

        if (!$response) {
            return [];
        }

        $data = $this->normalizeFeatureCollection(
            $response->json()
        );

        if ($data === []) {
            Log::warning(
                'ORS returned an invalid or empty route FeatureCollection.',
                ['status' => $response->status()]
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
        if (
            !$this->hasValidCoordinates($start) ||
            !$this->hasValidCoordinates($end)
        ) {
            Log::warning(
                'ORS received invalid alternative route coordinates.'
            );

            return [];
        }

        $targetCount = max(1, min(3, $targetCount));

        $startLat = (float) $start['lat'];
        $startLon = (float) $start['lon'];
        $endLat = (float) $end['lat'];
        $endLon = (float) $end['lon'];

        $cacheKey = sprintf(
            'ors-alt:%.8F:%.8F:%.8F:%.8F:%d',
            $startLat,
            $startLon,
            $endLat,
            $endLon,
            $targetCount
        );

        $cached = Cache::get($cacheKey);

        if (is_array($cached) && !empty($cached)) {
            $validCached = $this->filterValidFeatures($cached);

            if ($validCached !== []) {
                return $validCached;
            }

            Cache::forget($cacheKey);
        }

        $response = $this->request([
            'coordinates' => [
                [$startLon, $startLat],
                [$endLon, $endLat],
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

        $features = $response->json('features', []);

        if (!is_array($features)) {
            Log::warning(
                'ORS returned an invalid alternative routes structure.',
                ['status' => $response->status()]
            );

            return [];
        }

        $features = $this->filterValidFeatures($features);

        if ($features !== []) {
            Cache::put(
                $cacheKey,
                $features,
                now()->addMinutes(30)
            );
        }

        return $features;
    }

    private function hasValidCoordinates(array $coordinates): bool
    {
        if (
            !isset($coordinates['lat'], $coordinates['lon']) ||
            !is_numeric($coordinates['lat']) ||
            !is_numeric($coordinates['lon'])
        ) {
            return false;
        }

        $lat = (float) $coordinates['lat'];
        $lon = (float) $coordinates['lon'];

        return is_finite($lat) &&
            is_finite($lon) &&
            $lat >= -90 &&
            $lat <= 90 &&
            $lon >= -180 &&
            $lon <= 180;
    }

    private function normalizeFeatureCollection(mixed $data): array
    {
        if (
            !is_array($data) ||
            ($data['type'] ?? null) !== 'FeatureCollection' ||
            !isset($data['features']) ||
            !is_array($data['features'])
        ) {
            return [];
        }

        $features = $this->filterValidFeatures($data['features']);

        if ($features === []) {
            return [];
        }

        $data['features'] = $features;

        return $data;
    }

    private function filterValidFeatures(array $features): array
    {
        return array_values(array_filter(
            $features,
            fn ($feature) => $this->isValidRouteFeature($feature)
        ));
    }

    private function isValidRouteFeature(mixed $feature): bool
    {
        if (
            !is_array($feature) ||
            ($feature['type'] ?? null) !== 'Feature' ||
            !isset($feature['geometry']) ||
            !is_array($feature['geometry'])
        ) {
            return false;
        }

        $geometry = $feature['geometry'];
        $type = $geometry['type'] ?? null;
        $coordinates = $geometry['coordinates'] ?? null;

        if (!is_array($coordinates)) {
            return false;
        }

        if ($type === 'LineString') {
            return $this->hasValidLineCoordinates($coordinates);
        }

        if ($type === 'MultiLineString') {
            if ($coordinates === []) {
                return false;
            }

            foreach ($coordinates as $line) {
                if (
                    !is_array($line) ||
                    !$this->hasValidLineCoordinates($line)
                ) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    private function hasValidLineCoordinates(array $coordinates): bool
    {
        if (count($coordinates) < 2) {
            return false;
        }

        foreach ($coordinates as $position) {
            if (
                !is_array($position) ||
                !isset($position[0], $position[1]) ||
                !is_numeric($position[0]) ||
                !is_numeric($position[1])
            ) {
                return false;
            }

            $lon = (float) $position[0];
            $lat = (float) $position[1];

            if (
                !is_finite($lon) ||
                !is_finite($lat) ||
                $lon < -180 ||
                $lon > 180 ||
                $lat < -90 ||
                $lat > 90
            ) {
                return false;
            }
        }

        return true;
    }

    private function request(array $payload): ?Response
    {
        $apiKey = trim(
            (string) config('services.ors.api_key', '')
        );

        if ($apiKey === '') {
            Log::error('ORS API key is missing.');

            return null;
        }

        $url = $this->baseUrl . '/v2/directions/driving-car/geojson';

        try {
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/geo+json',
            ])
                ->timeout(20)
                ->post($url, $payload);
        } catch (\Throwable $e) {
            Log::error(
                'ORS route request encountered an exception.',
                [
                    'url' => $url,
                    'message' => $e->getMessage(),
                ]
            );

            return null;
        }

        if (!$response->successful()) {
            Log::error(
                'ORS route request failed.',
                [
                    'url' => $url,
                    'status' => $response->status(),
                    // ORS error bodies explain invalid keys, limits and bad requests.
                    // Do not log request headers or API keys.
                    'body' => mb_substr((string) $response->body(), 0, 2000),
                ]
            );

            return null;
        }

        return $response;
    }
}
