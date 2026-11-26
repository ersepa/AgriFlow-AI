<?php

namespace App\Services\Routing;

use App\Models\Shipment;
use App\Services\AI\DecisionEngine;
use App\Services\RouteService;
use App\Services\Sustainability\FreightCarbonEstimateService;

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
        private readonly RouteService $routes,
        private readonly FreightCarbonEstimateService $freightCarbon
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
            'label' => 'Rute Saat Ini',
            'distance_km' => is_numeric($shipment->distance_km) ? round((float) $shipment->distance_km, 2) : null,
            'duration_hours' => is_numeric($shipment->duration_hours) ? round((float) $shipment->duration_hours, 2) : null,
            'carbon_kg' => is_numeric($analysis['carbon_kg'] ?? null) ? round((float) $analysis['carbon_kg'], 2) : null,
            'geometry' => $this->normalizeStoredGeometry($shipment->route_geometry),
            'analysis' => $analysis,
            'source' => 'stored_shipment_route',
        ];

        $ranked = $this->scoreCandidates([$candidate]);

        return array_merge(
            $ranked[0],
            [
                'ranking_scope' => 'current_route_only',
                'recommended' => true,
                'recommendation_reason' => $this->singleRouteReason($ranked[0]),
            ]
        );
    }

    public function optimize(Shipment $shipment): array
    {
        $shipment->loadMissing('harvest');

        $baseAnalysis = $this->engine->analyze($shipment);
        $current = $this->currentCandidate($shipment, $baseAnalysis);

        $candidateInputs = [$current];
        $alternativeSource = 'not_requested';
        $alternativeNote = null;

        $hasCoordinates = $this->hasValidShipmentCoordinates($shipment);
        $start = [
            'lat' => (float) $shipment->origin_lat,
            'lon' => (float) $shipment->origin_lng,
        ];
        $end = [
            'lat' => (float) $shipment->destination_lat,
            'lon' => (float) $shipment->destination_lng,
        ];

        // If no usable route is stored, request a real driving route first.
        // This is also the single-route fallback when ORS alternatives are unavailable.
        if ($hasCoordinates && !$this->hasUsableGeometry($current['geometry'] ?? [])) {
            $routeCollection = $this->routes->getRoute($start, $end);
            $routeFeature = $routeCollection['features'][0] ?? null;
            $summary = is_array($routeFeature)
                ? ($routeFeature['properties']['summary'] ?? [])
                : [];
            $geometry = is_array($routeFeature)
                ? ($routeFeature['geometry']['coordinates'] ?? [])
                : [];

            if (
                $this->hasUsableGeometry($geometry)
                && is_numeric($summary['distance'] ?? null)
                && is_numeric($summary['duration'] ?? null)
                && (float) $summary['distance'] > 0
                && (float) $summary['duration'] > 0
            ) {
                $distanceKm = round((float) $summary['distance'] / 1000, 2);
                $durationHours = round((float) $summary['duration'] / 3600, 2);
                $scenarioShipment = $this->scenarioShipment($shipment, $distanceKm, $durationHours);
                $routeAnalysis = $this->engine->analyze($scenarioShipment);

                $current = array_merge($current, [
                    'distance_km' => $distanceKm,
                    'duration_hours' => $durationHours,
                    'carbon_kg' => is_numeric($routeAnalysis['carbon_kg'] ?? null)
                        ? round((float) $routeAnalysis['carbon_kg'], 2)
                        : null,
                    'geometry' => $geometry,
                    'analysis' => $routeAnalysis,
                    'source' => 'openrouteservice',
                ]);
                $candidateInputs = [$current];
            }
        }

        if ($hasCoordinates) {
            // Do not gate route requests using the shipment's manually entered distance.
            // That value may be absent, stale, or a straight-line estimate.
            $features = $this->routes->getAlternativeRoutes($start, $end, 3);

            if (!empty($features)) {
                $alternativeSource = 'openrouteservice';
                $candidateInputs = $this->mergeAlternativeCandidates($shipment, $current, $features);
            } else {
                $alternativeSource = 'provider_unavailable';
                $alternativeNote = $this->hasUsableGeometry($current['geometry'] ?? [])
                    ? 'Rute jalan utama berhasil dimuat, tetapi ORS tidak menyediakan rute alternatif. Rute utama tetap dapat ditinjau.'
                    : 'ORS belum mengembalikan geometri rute yang valid. Periksa konfigurasi API, koordinat, dan Laravel log.';
            }
        } else {
            $alternativeSource = 'coordinates_unavailable';
            $alternativeNote = 'Koordinat asal atau tujuan belum lengkap. Lengkapi koordinat sebelum meminta optimasi rute.';
        }

        // Never rank a candidate that cannot actually be drawn on the map.
        $candidateInputs = array_values(array_filter(
            $candidateInputs,
            fn (array $candidate) => $this->hasUsableGeometry($candidate['geometry'] ?? [])
        ));

        $ranked = $this->scoreCandidates($candidateInputs);

        if ($ranked === []) {
            return [
                'model_name' => 'Pemeringkatan Rute Berbasis Kesegaran',
                'model_version' => '5.2',
                'deterministic' => true,
                'weights' => self::WEIGHTS,
                'alternative_source' => $alternativeSource,
                'alternative_note' => $alternativeNote ?? 'Belum ada geometri rute jalan yang valid untuk dievaluasi.',
                'candidate_count' => 0,
                'decision_status' => 'Rute Belum Dapat Dianalisis',
                'decision_type' => 'unavailable',
                'routing_alone_sufficient' => false,
                'recommended_route' => null,
                'best_available_route' => null,
                'candidates' => [],
                'recommendation_reason' => 'Optimasi belum dapat dilakukan karena layanan rute belum mengembalikan geometri jalan yang valid.',
                'required_action' => 'Periksa koordinat asal/tujuan dan konfigurasi OpenRouteService, lalu coba lagi.',
            ];
        }

        foreach ($ranked as $index => &$candidate) {
            $candidate['rank'] = $index + 1;
            $candidate['recommended'] = $index === 0;
        }
        unset($candidate);

        $best = $ranked[0];

        $allBreach =
            !empty($ranked)
            && collect($ranked)->every(
                fn (array $candidate) => $candidate['freshness_feasibility'] === 'Breach'
            );

        $decisionStatus = $allBreach
            ? 'Tidak Ada Rute Aman Kesegaran'
            : 'Rekomendasi Rute Tersedia';

        $decisionType = $allBreach ? 'best_available' : 'recommended';

        foreach ($ranked as &$candidate) {
            $candidate['recommended'] = !$allBreach && $candidate['rank'] === 1;
            $candidate['best_available'] = $allBreach && $candidate['rank'] === 1;
        }
        unset($candidate);

        return [
            'model_name' => 'Pemeringkatan Rute Berbasis Kesegaran',
            'model_version' => '5.1',
            'deterministic' => true,
            'weights' => self::WEIGHTS,
            'alternative_source' => $alternativeSource,
            'alternative_note' => $alternativeNote,
            'candidate_count' => count($ranked),
            'decision_status' => $decisionStatus,
            'decision_type' => $decisionType,
            'routing_alone_sufficient' => !$allBreach,
            'recommended_route' => $allBreach ? null : $best,
            'best_available_route' => $best,
            'candidates' => $ranked,
            'recommendation_reason' => $allBreach
                ? $this->allBreachReason($ranked)
                : $this->recommendationReason($ranked),
            'required_action' => $allBreach
                ? 'Intervensi operasional segera diperlukan; perubahan rute saja tidak cukup.'
                : null,
        ];
    }

    private function currentCandidate(Shipment $shipment, array $analysis): array
    {
        return [
            'key' => 'current',
            'label' => 'Rute Saat Ini',
            'distance_km' => is_numeric($shipment->distance_km) ? round((float) $shipment->distance_km, 2) : null,
            'duration_hours' => is_numeric($shipment->duration_hours) ? round((float) $shipment->duration_hours, 2) : null,
            'carbon_kg' => is_numeric($analysis['carbon_kg'] ?? null) ? round((float) $analysis['carbon_kg'], 2) : null,
            'geometry' => $this->normalizeStoredGeometry($shipment->route_geometry),
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

        if (
            is_numeric($current['distance_km'] ?? null)
            && (float) $current['distance_km'] > 0
            && is_numeric($current['duration_hours'] ?? null)
            && (float) $current['duration_hours'] > 0
        ) {
            $seen[$this->candidateFingerprint(
                (float) $current['distance_km'],
                (float) $current['duration_hours']
            )] = true;
        }

        $alternativeNumber = 1;

        foreach ($features as $feature) {
            $summary = $feature['properties']['summary'] ?? [];
            $distanceKm = round(((float) ($summary['distance'] ?? 0)) / 1000, 2);
            $durationHours = round(((float) ($summary['duration'] ?? 0)) / 3600, 2);

            if ($distanceKm <= 0 || $durationHours <= 0) {
                continue;
            }

            $fingerprint = $this->candidateFingerprint($distanceKm, $durationHours);

            if (isset($seen[$fingerprint])) {
                continue;
            }

            if ($this->isNearDuplicateCandidate($candidates, $distanceKm, $durationHours)) {
                continue;
            }

            $seen[$fingerprint] = true;

            $scenarioShipment = $this->scenarioShipment($shipment, $distanceKm, $durationHours);
            $analysis = $this->engine->analyze($scenarioShipment);

            $candidates[] = [
                'key' => 'alternative_' . $alternativeNumber,
                'label' => 'Alternatif ' . $alternativeNumber,
                'distance_km' => $distanceKm,
                'duration_hours' => $durationHours,
                'carbon_kg' => is_numeric($analysis['carbon_kg'] ?? null) ? round((float) $analysis['carbon_kg'], 2) : null,
                'geometry' => $feature['geometry']['coordinates'] ?? [],
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

        if ($shipment->relationLoaded('harvest')) {
            $scenario->setRelation('harvest', $shipment->harvest);
        }

        $carbonEstimate = $this->freightCarbon->estimateForShipment($scenario, $distanceKm);
        $scenario->carbon_emission = $carbonEstimate['estimated_kg'];

        return $scenario;
    }

    private function scoreCandidates(array $candidates): array
    {
        if (empty($candidates)) {
            return [];
        }

        $durations = array_values(array_filter(
            array_map(
                fn (array $candidate) =>
                    is_numeric($candidate['duration_hours'] ?? null) && (float) $candidate['duration_hours'] > 0
                        ? (float) $candidate['duration_hours']
                        : null,
                $candidates
            ),
            fn ($value) => $value !== null
        ));

        $fastest = !empty($durations) ? min($durations) : null;

        $carbonValues = array_values(array_filter(
            array_map(
                fn (array $candidate) =>
                    is_numeric($candidate['carbon_kg'] ?? null) && (float) $candidate['carbon_kg'] > 0
                        ? (float) $candidate['carbon_kg']
                        : null,
                $candidates
            ),
            fn ($value) => $value !== null
        ));

        $lowestCarbon = !empty($carbonValues) ? min($carbonValues) : null;

        $scored = array_map(
            function (array $candidate) use ($fastest, $lowestCarbon) {
                $analysis = $candidate['analysis'];

                $qualityAvailable = isset($analysis['quality_at_arrival']) && is_numeric($analysis['quality_at_arrival']);
                $freshness = $qualityAvailable ? $this->clamp((float) $analysis['quality_at_arrival']) : null;
                $riskProtection = $this->clamp(100 - (float) ($analysis['risk_score'] ?? 100));
                $margin = $analysis['transit_margin_hours'] ?? null;

                $marginScore = $this->transitMarginScore($margin, $analysis['safe_transit_status'] ?? '');

                $durationValue = $candidate['duration_hours'] ?? null;
                $durationEfficiency = $fastest !== null && is_numeric($durationValue) && (float) $durationValue > 0
                    ? $this->clamp(($fastest / (float) $durationValue) * 100)
                    : null;

                $carbonValue = $candidate['carbon_kg'] ?? null;
                $carbonEfficiency = $lowestCarbon !== null && is_numeric($carbonValue) && (float) $carbonValue > 0
                    ? $this->clamp(($lowestCarbon / (float) $carbonValue) * 100)
                    : null;

                $components = [
                    'freshness_preservation' => $freshness !== null ? round($freshness, 1) : null,
                    'risk_protection' => round($riskProtection, 1),
                    'transit_margin' => $marginScore !== null ? round($marginScore, 1) : null,
                    'duration_efficiency' => $durationEfficiency !== null ? round($durationEfficiency, 1) : null,
                    'carbon_efficiency' => $carbonEfficiency !== null ? round($carbonEfficiency, 1) : null,
                ];

                $weighted = 0.0;
                $availableWeight = 0.0;

                foreach (self::WEIGHTS as $key => $weight) {
                    $component = $components[$key] ?? null;
                    if ($component === null) {
                        continue;
                    }
                    $weighted += $component * $weight;
                    $availableWeight += $weight;
                }

                $weighted = $availableWeight > 0 ? $weighted / $availableWeight : 0.0;
                $feasibility = $this->feasibility($analysis);

                if ($feasibility === 'Breach') {
                    $weighted = min($weighted, 35);
                } elseif ($feasibility === 'Tight') {
                    $weighted = min($weighted, 65);
                }

                return array_merge(
                    $candidate,
                    [
                        'route_score' => (int) round($this->clamp($weighted)),
                        'score_components' => $components,
                        'projected_arrival_quality' => $freshness !== null ? (int) round($freshness) : null,
                        'arrival_quality_available' => $freshness !== null,
                        'route_score_basis' => $freshness !== null
                            ? 'all_step5_components'
                            : 'available_evidence_reweighted_without_arrival_quality',
                        'projected_quality_status' => $analysis['quality_status'] ?? 'Unavailable',
                        'projected_risk_score' => (int) ($analysis['risk_score'] ?? 100),
                        'projected_risk_severity' => $analysis['risk_severity'] ?? ($analysis['risk_level'] ?? 'Unknown'),
                        'remaining_shelf_life_days' => $analysis['predicted_remaining_shelf_life_days'] ?? null,
                        'transit_margin_hours' => $margin,
                        'delay_tolerance_hours' => $margin === null ? null : round(max(0, (float) $margin), 1),
                        'freshness_feasibility' => $feasibility,
                        'safe_transit_status' => $analysis['safe_transit_status'] ?? 'Unavailable',
                    ]
                );
            },
            $candidates
        );

        usort($scored, function (array $a, array $b) {
            $feasibilityOrder = [
                'Safe' => 0,
                'Tight' => 1,
                'Breach' => 2,
            ];

            $aFeasibility = $feasibilityOrder[$a['freshness_feasibility']] ?? 3;
            $bFeasibility = $feasibilityOrder[$b['freshness_feasibility']] ?? 3;

            if ($aFeasibility !== $bFeasibility) {
                return $aFeasibility <=> $bFeasibility;
            }

            return $b['route_score'] <=> $a['route_score'];
        });

        return array_values($scored);
    }

    private function transitMarginScore(?float $marginHours, string $status): ?float
    {
        if (
            $status === 'Threshold already exceeded'
            || $status === 'ETA exceeds safe transit window'
        ) {
            return 0;
        }

        if ($marginHours === null) {
            return null;
        }

        if ($marginHours <= 0) {
            return 0;
        }

        if ($marginHours >= 72) {
            return 100;
        }

        return ($marginHours / 72) * 100;
    }

    private function feasibility(array $analysis): string
    {
        $status = $analysis['safe_transit_status'] ?? '';
        $margin = $analysis['transit_margin_hours'] ?? null;

        if (
            $status === 'Threshold already exceeded'
            || $status === 'ETA exceeds safe transit window'
            || ($margin !== null && is_numeric($margin) && (float) $margin < 0)
        ) {
            return 'Breach';
        }

        if ($margin === null || !is_numeric($margin)) {
            return 'Unavailable';
        }

        if ((float) $margin <= 6) {
            return 'Tight';
        }

        return 'Safe';
    }

    private function feasibilityLabel(string $feasibility): string
    {
        return match ($feasibility) {
            'Safe' => 'aman',
            'Tight' => 'ketat',
            'Breach' => 'melewati batas',
            default => 'belum tersedia',
        };
    }

    private function recommendationReason(array $ranked): string
    {
        if (count($ranked) === 1) {
            return $this->singleRouteReason($ranked[0]);
        }

        $best = $ranked[0];
        $second = $ranked[1];

        if ($best['freshness_feasibility'] !== $second['freshness_feasibility']) {
            return sprintf(
                '%s direkomendasikan karena menjaga kelayakan kesegaran pada status %s, sedangkan rute berikutnya berstatus %s.',
                $best['label'],
                $this->feasibilityLabel($best['freshness_feasibility']),
                $this->feasibilityLabel($second['freshness_feasibility'])
            );
        }

        $qualityPhrase = $best['projected_arrival_quality'] !== null
            ? sprintf('perkiraan kondisi saat tiba (%d/100)', $best['projected_arrival_quality'])
            : 'data kondisi penyimpanan yang tersedia';

        return sprintf(
            '%s menempati peringkat tertinggi dengan skor %d/100 dengan menyeimbangkan %s, risiko operasional (%d/100), waktu cadangan, durasi, dan emisi karbon.',
            $best['label'],
            $best['route_score'],
            $qualityPhrase,
            $best['projected_risk_score']
        );
    }

    private function singleRouteReason(array $route): string
    {
        $qualityPhrase = $route['projected_arrival_quality'] !== null
            ? sprintf('perkiraan kondisi saat tiba %d/100', $route['projected_arrival_quality'])
            : 'kondisi saat tiba tidak diestimasi karena model komoditas ini tidak menggunakan kurva produk segar';

        return sprintf(
            'Rute saat ini memperoleh skor %d/100 dengan status kelayakan %s, %s, dan risiko operasional %d/100. Tidak ada rute alternatif yang ditampilkan kecuali kandidat rute nyata tersedia.',
            $route['route_score'],
            $this->feasibilityLabel($route['freshness_feasibility']),
            $qualityPhrase,
            $route['projected_risk_score']
        );
    }

    private function hasValidShipmentCoordinates(Shipment $shipment): bool
    {
        foreach (['origin_lat', 'origin_lng', 'destination_lat', 'destination_lng'] as $field) {
            if (!is_numeric($shipment->{$field})) {
                return false;
            }
        }

        $originLat = (float) $shipment->origin_lat;
        $originLng = (float) $shipment->origin_lng;
        $destinationLat = (float) $shipment->destination_lat;
        $destinationLng = (float) $shipment->destination_lng;

        return is_finite($originLat) && is_finite($originLng)
            && is_finite($destinationLat) && is_finite($destinationLng)
            && $originLat >= -90 && $originLat <= 90
            && $destinationLat >= -90 && $destinationLat <= 90
            && $originLng >= -180 && $originLng <= 180
            && $destinationLng >= -180 && $destinationLng <= 180
            && !($originLat === $destinationLat && $originLng === $destinationLng);
    }

    private function hasUsableGeometry(mixed $geometry): bool
    {
        if (!is_array($geometry) || count($geometry) < 2) {
            return false;
        }

        foreach ($geometry as $position) {
            if (
                !is_array($position)
                || !isset($position[0], $position[1])
                || !is_numeric($position[0])
                || !is_numeric($position[1])
            ) {
                return false;
            }

            $lon = (float) $position[0];
            $lat = (float) $position[1];
            if (!is_finite($lon) || !is_finite($lat)
                || $lon < -180 || $lon > 180
                || $lat < -90 || $lat > 90) {
                return false;
            }
        }

        return true;
    }

    private function normalizeStoredGeometry(mixed $geometry): array
    {
        if (is_string($geometry)) {
            $decoded = json_decode($geometry, true);
            if (is_array($decoded)) {
                $geometry = $decoded;
            }
        }

        if (!is_array($geometry)) {
            return [];
        }

        if (isset($geometry['coordinates']) && is_array($geometry['coordinates'])) {
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
            $existingDistance = (float) ($candidate['distance_km'] ?? 0);
            $existingDuration = (float) ($candidate['duration_hours'] ?? 0);

            if ($existingDistance <= 0 || $existingDuration <= 0) {
                continue;
            }

            $distanceDifference = abs($distanceKm - $existingDistance) / max(0.01, $existingDistance);
            $durationDifference = abs($durationHours - $existingDuration) / max(0.01, $existingDuration);

            if ($distanceDifference < 0.02 && $durationDifference < 0.02) {
                return true;
            }
        }

        return false;
    }

    private function allBreachReason(array $ranked): string
    {
        $best = $ranked[0];

        $qualityPhrase = $best['projected_arrival_quality'] !== null
            ? sprintf('perkiraan kondisi saat tiba %d/100', $best['projected_arrival_quality'])
            : 'kondisi saat tiba tidak tersedia pada model komoditas saat ini';

        return sprintf(
            'Tidak ada rute yang aman terhadap batas kesegaran. %s adalah opsi terbaik yang tersedia dengan skor %d/100, namun tetap melewati batas waktu kesegaran dengan %s dan risiko operasional %d/100. Perubahan rute saja tidak cukup; intervensi operasional segera diperlukan.',
            $best['label'],
            $best['route_score'],
            $qualityPhrase,
            $best['projected_risk_score']
        );
    }

    private function candidateFingerprint(float $distanceKm, float $durationHours): string
    {
        return sprintf('%.1f-%.2f', $distanceKm, $durationHours);
    }

    private function clamp(float $value, float $min = 0, float $max = 100): float
    {
        return max($min, min($max, $value));
    }
}