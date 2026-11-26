<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RouteService
{
    private string $baseUrl;

    private ?array $lastError = null;

public function __construct()
    {
        $configuredBaseUrl = trim((string) config(
            'services.ors.base_url',
            'https://api.heigit.org/openrouteservice'
        ));

        // Redirect deprecated api.openrouteservice.org (HTTP 410 Gone)
        // to the active HeiGIT endpoint.
        if (
            $configuredBaseUrl === ''
            || str_contains($configuredBaseUrl, 'api.openrouteservice.org')
        ) {
            $configuredBaseUrl = 'https://api.heigit.org/openrouteservice';
        }

        $this->baseUrl = rtrim($configuredBaseUrl, '/');
    }

    public function getLastError(): ?array
    {
        return $this->lastError;
    }

    public function getRoute(
        array $start,
        array $end
    ): array {
        $this->lastError = null;

        if (
            !$this->hasValidCoordinates($start) ||
            !$this->hasValidCoordinates($end)
        ) {
            $this->setLastError(
                'invalid_coordinates',
                422,
                'Koordinat asal atau tujuan tidak valid.'
            );
            Log::warning('ORS received invalid route coordinates.', [
                'start' => $start,
                'end' => $end,
            ]);

            return [];
        }

        $startLat = (float) $start['lat'];
        $startLon = (float) $start['lon'];
        $endLat = (float) $end['lat'];
        $endLon = (float) $end['lon'];

        $cacheKey = sprintf(
            'ors-route:v2:%.8F:%.8F:%.8F:%.8F',
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
            // Allow snapping up to 5 km for rural/agricultural farm coordinates.
            'radiuses' => [5000, 5000],
        ]);

        if (!$response) {
            return [];
        }

        $data = $this->normalizeFeatureCollection(
            $response->json()
        );

        if ($data === []) {
            $this->setLastError(
                'invalid_geometry',
                $response->status(),
                'OpenRouteService mengembalikan struktur geometri rute yang kosong atau tidak valid.'
            );
            Log::warning(
                'ORS returned an invalid or empty route FeatureCollection.',
                [
                    'status' => $response->status(),
                    'coordinates' => [
                        [$startLon, $startLat],
                        [$endLon, $endLat],
                    ],
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
        $this->lastError = null;

        if (
            !$this->hasValidCoordinates($start) ||
            !$this->hasValidCoordinates($end)
        ) {
            $this->setLastError(
                'invalid_coordinates',
                422,
                'Koordinat rute alternatif tidak valid.'
            );
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
            'ors-alt:v2:%.8F:%.8F:%.8F:%.8F:%d',
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
            'radiuses' => [5000, 5000],
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
            $this->setLastError(
                'invalid_alternatives_structure',
                $response->status(),
                'Struktur rute alternatif dari OpenRouteService tidak valid.'
            );
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
            $this->setLastError(
                'missing_api_key',
                500,
                'Konfigurasi ORS_API_KEY belum tersedia di server.'
            );
            Log::error('ORS API key is missing.');

            return null;
        }

        $url = $this->baseUrl . '/v2/directions/driving-car/geojson';

        try {
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json, application/geo+json',
            ])
                ->timeout(20)
                ->post($url, $payload);
        } catch (\Throwable $e) {
            $this->setLastError(
                'connection_exception',
                504,
                'Gagal terhubung ke OpenRouteService (timeout atau kendala jaringan/SSL).'
            );
            Log::error(
                'ORS route request encountered an exception.',
                [
                    'url' => $url,
                    'coordinates' => $payload['coordinates'] ?? null,
                    'message' => $e->getMessage(),
                ]
            );

            return null;
        }

        if (!$response->successful()) {
            $status = $response->status();
            $errorBody = $response->json();
            $orsCode = $errorBody['error']['code'] ?? null;
            $orsMessage = $errorBody['error']['message'] ?? null;

            $this->setLastError(
                $this->mapHttpErrorCode($status, $orsCode),
                $status,
                $this->mapHttpErrorMessage($status, $orsCode, $orsMessage)
            );

            Log::error(
                'ORS route request failed.',
                [
                    'url' => $url,
                    'status' => $status,
                    'ors_code' => $orsCode,
                    'coordinates' => $payload['coordinates'] ?? null,
                    // ORS error bodies explain invalid keys, limits and bad requests.
                    // Do not log request headers or API keys.
                    'body' => mb_substr((string) $response->body(), 0, 2000),
                ]
            );

            return null;
        }

        return $response;
    }

    private function setLastError(string $code, int $status, string $message): void
    {
        $this->lastError = [
            'code' => $code,
            'status' => $status,
            'message' => $message,
        ];
    }

    private function mapHttpErrorCode(int $status, mixed $orsCode): string
    {
        if (in_array($status, [401, 403], true)) {
            return 'ors_auth_failed';
        }
        if ($status === 429) {
            return 'ors_rate_limited';
        }
        if ($status === 404 || (int) $orsCode === 2010) {
            return 'ors_point_not_routable';
        }
        if ($status === 400) {
            return 'ors_bad_request';
        }

        return 'ors_http_error';
    }

    private function mapHttpErrorMessage(int $status, mixed $orsCode, ?string $orsMessage): string
    {
        if (in_array($status, [401, 403], true)) {
            return 'Autentikasi OpenRouteService ditolak (HTTP ' . $status . '). Periksa ORS_API_KEY di environment.';
        }
        if ($status === 429) {
            return 'Batas kuota permintaan OpenRouteService tercapai (HTTP 429). Coba lagi beberapa saat lagi.';
        }
        if ($status === 404 || (int) $orsCode === 2010) {
            return 'Titik koordinat asal atau tujuan tidak ditemukan di jaringan jalan yang dapat dilalui kendaraan.';
        }
        if ($status === 400) {
            return 'Permintaan rute ditolak oleh OpenRouteService (HTTP 400)' . ($orsMessage ? ': ' . $orsMessage : '.');
        }

        return 'OpenRouteService gagal mengembalikan rute (HTTP ' . $status . ').';
    }
}