<?php

namespace App\Services\DigitalTwin;

use App\Models\Shipment;
use App\Services\AI\DecisionEngine;
use App\Services\Routing\FreshnessAwareRouteService;
use App\Services\Sustainability\FreightCarbonEstimateService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class ScenarioEngine
{
    public const VERSION = 'step6.2-multi-scenario-comparison-v1';

    public function __construct(
        private readonly DecisionEngine $decisionEngine,
        private readonly FreshnessAwareRouteService $freshnessRoutes,
        private readonly ScenarioComparisonService $comparison,
        private readonly FreightCarbonEstimateService $freightCarbon
    ) {
    }

    public function baseline(Shipment $shipment): array
    {
        $this->assertOperationallyActive($shipment);
        $shipment->loadMissing('harvest');

        $analysis = $this->decisionEngine->analyze($shipment);
        $route = $this->freshnessRoutes->assessCurrentRoute($shipment, $analysis);

        return $this->snapshot(
            shipment: $shipment,
            name: 'Rencana Saat Ini',
            input: [
                'route_rank' => 0,
                'delay_hours' => 0.0,
                'vehicle' => 'standard_truck',
            ],
            analysis: $analysis,
            route: $route,
            carbon: $this->carbonEstimate(
                $shipment,
                is_numeric($shipment->distance_km) ? (float) $shipment->distance_km : null,
                $analysis
            ),
            evidence: $this->evidenceCoverage($analysis, [], $route),
            notes: [
                'Baseline menggunakan data pengiriman yang saat ini tersimpan di AgriFlow.',
                'Pilihan kendaraan hanya berupa metadata perencanaan kecuali data kondisi atau faktor emisi khusus tersedia.',
            ]
        );
    }

    public function simulate(Shipment $shipment, array $input): array
    {
        $this->assertOperationallyActive($shipment);
        $this->assertValidScenarioInput($input);

        $shipment->loadMissing('harvest');

        $routeCandidate = $this->resolveRoute($shipment, (int) ($input['route_rank'] ?? 0));
        $delayHours = max(0, (float) ($input['delay_hours'] ?? 0));

        $scenarioShipment = clone $shipment;
        $scenarioShipment->setRelation('harvest', $shipment->harvest);
        $scenarioShipment->distance_km = $routeCandidate['distance_km'];

        $baseDuration = $routeCandidate['duration_hours'] ?? null;

        $scenarioShipment->duration_hours = is_numeric($baseDuration)
            ? round((float) $baseDuration + $delayHours, 4)
            : null;

        $conditionScenario = $this->conditionScenario($input);

        $analysis = $this->decisionEngine->analyze($scenarioShipment, $conditionScenario);
        $route = $this->freshnessRoutes->assessCurrentRoute($scenarioShipment, $analysis);

        $route['candidate_label'] = $routeCandidate['label'];
        $route['candidate_rank'] = $routeCandidate['rank'];
        $route['base_route_duration_hours'] = is_numeric($routeCandidate['duration_hours'] ?? null)
            ? round((float) $routeCandidate['duration_hours'], 2)
            : null;
        $route['scenario_delay_hours'] = round($delayHours, 2);
        $route['scenario_total_duration_hours'] = is_numeric($scenarioShipment->duration_hours)
            ? round((float) $scenarioShipment->duration_hours, 2)
            : null;

        $evidence = $this->evidenceCoverage($analysis, $input, $routeCandidate);
        $notes = $this->scenarioNotes($analysis, $input);

        return $this->snapshot(
            shipment: $scenarioShipment,
            name: (string) ($input['name'] ?? 'Skenario'),
            input: $this->inputSnapshot($input, $routeCandidate),
            analysis: $analysis,
            route: $route,
            carbon: $this->carbonEstimate(
                $shipment,
                is_numeric($routeCandidate['distance_km'] ?? null) ? (float) $routeCandidate['distance_km'] : null,
                $analysis
            ),
            evidence: $evidence,
            notes: $notes
        );
    }

    public function compareOne(Shipment $shipment, array $input): array
    {
        $baseline = $this->baseline($shipment);
        $scenario = $this->simulate($shipment, $input);
        $scenario['delta'] = $this->comparison->delta($baseline, $scenario);
        $comparison = $this->comparison->compare($baseline, [$scenario]);

        $evaluatedScenario = $comparison['best_available_scenario'] ?? $scenario;
        $evaluatedScenario['delta'] = $scenario['delta'];

        return [
            'engine_version' => self::VERSION,
            'baseline' => $baseline,
            'scenario' => $evaluatedScenario,
            'comparison' => $comparison,
        ];
    }

    public function compareMany(Shipment $shipment, array $scenarioInputs): array
    {
        $this->assertOperationallyActive($shipment);

        if (count($scenarioInputs) < 1 || count($scenarioInputs) > 3) {
            throw new \InvalidArgumentException(
                'Perbandingan harus berisi antara 1 hingga 3 skenario.'
            );
        }

        $scenarioInputs = array_values($scenarioInputs);
        $scenarioNames = [];

        foreach ($scenarioInputs as $index => $input) {
            if (!is_array($input)) {
                throw new \InvalidArgumentException(
                    'Setiap data skenario harus berupa array yang valid.'
                );
            }

            $this->assertValidScenarioInput($input);

            $input['name'] = trim((string) ($input['name'] ?? ''))
                ?: ('Skenario ' . chr(65 + $index));

            if (in_array($input['name'], $scenarioNames, true)) {
                throw new \InvalidArgumentException(
                    'Nama setiap skenario harus berbeda.'
                );
            }

            $scenarioNames[] = $input['name'];
            $scenarioInputs[$index] = $input;
        }

        $baseline = $this->baseline($shipment);
        $scenarios = [];

        foreach ($scenarioInputs as $input) {
            $scenario = $this->simulate($shipment, $input);
            $scenario['delta'] = $this->comparison->delta($baseline, $scenario);
            $scenarios[] = $scenario;
        }

        $comparison = $this->comparison->compare($baseline, $scenarios);
        $ranked = $comparison['ranked_scenarios'] ?? [];
        $comparison['ranked_scenarios'] = $ranked;

        $averageCoverage = count($scenarios) > 0
            ? (int) round(collect($scenarios)->avg(fn (array $scenario) => $scenario['evidence']['percent'] ?? 0))
            : 0;

        return [
            'engine_version' => self::VERSION,
            'baseline' => $baseline,
            'scenarios' => $scenarios,
            'comparison' => $comparison,
            'decision_table' => $this->comparison->decisionTable($baseline, $scenarios),
            'average_evidence_coverage' => $averageCoverage,
        ];
    }

    public function routeOptions(Shipment $shipment): array
    {
        $this->assertOperationallyActive($shipment);
        $shipment->loadMissing('harvest');

        $optimization = $this->freshnessRoutes->optimize($shipment);

        return array_values(
            array_map(
                fn (array $candidate): array => [
                    'rank' => (int) ($candidate['rank'] ?? 0),
                    'label' => (string) ($candidate['label'] ?? 'Rute'),
                    'distance_km' => is_numeric($candidate['distance_km'] ?? null) ? round((float) $candidate['distance_km'], 2) : null,
                    'duration_hours' => is_numeric($candidate['duration_hours'] ?? null) ? round((float) $candidate['duration_hours'], 2) : null,
                    'freshness_feasibility' => $candidate['freshness_feasibility'] ?? null,
                    'route_score' => $candidate['route_score'] ?? null,
                ],
                $optimization['candidates'] ?? []
            )
        );
    }

    private function resolveRoute(Shipment $shipment, int $rank): array
    {
        if ($rank <= 0) {
            return [
                'rank' => 0,
                'label' => 'Rute Saat Ini',
                'distance_km' => is_numeric($shipment->distance_km) ? (float) $shipment->distance_km : null,
                'duration_hours' => is_numeric($shipment->duration_hours) ? (float) $shipment->duration_hours : null,
            ];
        }

        $options = $this->routeOptions($shipment);
        $candidate = collect($options)->firstWhere('rank', $rank);

        if (!$candidate) {
            throw new \InvalidArgumentException(
                'Kandidat rute yang dipilih sudah tidak tersedia. Muat ulang halaman Digital Twin dan coba lagi.'
            );
        }

        return $candidate;
    }

    private function conditionScenario(array $input): array
    {
        $scenario = [];

        if (array_key_exists('temperature_c', $input) && $input['temperature_c'] !== null && $input['temperature_c'] !== '') {
            $scenario['temperature'] = (float) $input['temperature_c'];
        }

        if (array_key_exists('moisture_percent', $input) && $input['moisture_percent'] !== null && $input['moisture_percent'] !== '') {
            $scenario['moisture_percent'] = (float) $input['moisture_percent'];
        }

        if (array_key_exists('relative_humidity_percent', $input) && $input['relative_humidity_percent'] !== null && $input['relative_humidity_percent'] !== '') {
            $scenario['relative_humidity_percent'] = (float) $input['relative_humidity_percent'];
        }

        if (!empty($input['storage_horizon'])) {
            $scenario['storage_horizon'] = (string) $input['storage_horizon'];
        }

        return $scenario;
    }

    private function carbonEstimate(
        Shipment $originalShipment,
        ?float $scenarioDistanceKm,
        array $analysis
    ): array {
        if ($scenarioDistanceKm === null) {
            $estimate = $this->freightCarbon->estimateForShipment($originalShipment);
        } else {
            $estimate = $this->freightCarbon->estimateForShipment($originalShipment, max(0, $scenarioDistanceKm));
        }

        $estimate['vehicle_factor_applied'] = false;
        $estimate['scenario_note'] =
            'Digital Twin menggunakan faktor emisi ton-km yang sama untuk rencana saat ini dan skenario alternatif.';

        return $estimate;
    }

    private function evidenceCoverage(
        array $analysis,
        array $input,
        ?array $routeCandidate = null
    ): array {
        $routeEvidenceAvailable =
            $routeCandidate !== null
            && is_numeric($routeCandidate['distance_km'] ?? null)
            && (float) $routeCandidate['distance_km'] > 0
            && is_numeric($routeCandidate['duration_hours'] ?? null)
            && (float) $routeCandidate['duration_hours'] > 0;

        $modelType =
            $analysis['quality_prediction']['condition_model_type']
            ?? $analysis['commodity_profile']['quality_model_type']
            ?? null;

        $checks = [
            'commodity_profile' => (bool) ($analysis['commodity_profile_found'] ?? false),
            'route' => $routeEvidenceAvailable,
            'operational_deadline' => ($analysis['quality_prediction']['recorded_expiry_window_hours'] ?? null) !== null
                || ($analysis['remaining_days'] ?? null) !== null,
        ];

        if ($modelType === 'storage_stability') {
            $checks['cargo_moisture'] = isset($input['moisture_percent']) && $input['moisture_percent'] !== '';
            $checks['relative_humidity'] = isset($input['relative_humidity_percent']) && $input['relative_humidity_percent'] !== '';
        } else {
            $checks['cargo_temperature'] = isset($input['temperature_c']) && $input['temperature_c'] !== '';
        }

        $available = count(array_filter($checks));
        $total = max(1, count($checks));

        return [
            'percent' => (int) round($available / $total * 100),
            'available_inputs' => $available,
            'total_inputs' => $total,
            'checks' => $checks,
            'label' => 'Cakupan Data Pendukung Skenario',
            'is_accuracy_metric' => false,
        ];
    }

    private function scenarioNotes(array $analysis, array $input): array
    {
        $notes = [
            'Perhitungan skenario bersifat deterministik, bukan simulasi Monte Carlo atau prediksi probabilitas statistik.',
            'Output AI bahasa tidak digunakan untuk menghitung risiko, kondisi produk, kelayakan rute, waktu cadangan, atau emisi karbon.',
            'Pilihan kendaraan hanya berupa metadata perencanaan dan tidak memberikan bonus risiko atau karbon secara tersembunyi.',
        ];

        $modelType = $analysis['quality_prediction']['condition_model_type'] ?? null;

        if (
            $modelType === 'storage_stability'
            && (
                !array_key_exists('moisture_percent', $input)
                || $input['moisture_percent'] === null
                || $input['moisture_percent'] === ''
                || !array_key_exists('relative_humidity_percent', $input)
                || $input['relative_humidity_percent'] === null
                || $input['relative_humidity_percent'] === ''
            )
        ) {
            $notes[] = 'Penilaian stabilitas penyimpanan komoditas kering terbatas jika data kadar air atau kelembapan belum diisi.';
        }

        if (($input['vehicle'] ?? null) === 'refrigerated_truck' && !isset($input['temperature_c'])) {
            $notes[] = 'Memilih Truk Berpendingin tidak otomatis menetapkan suhu muatan. Masukkan suhu skenario untuk mengevaluasi komoditas segar.';
        }

        return $notes;
    }

    private function inputSnapshot(array $input, array $routeCandidate): array
    {
        return [
            'name' => (string) ($input['name'] ?? 'Skenario'),
            'route_rank' => $routeCandidate['rank'],
            'route_label' => $routeCandidate['label'],
            'route_distance_km' => is_numeric($routeCandidate['distance_km'] ?? null) ? round((float) $routeCandidate['distance_km'], 2) : null,
            'route_duration_hours' => is_numeric($routeCandidate['duration_hours'] ?? null) ? round((float) $routeCandidate['duration_hours'], 2) : null,
            'delay_hours' => round((float) ($input['delay_hours'] ?? 0), 2),
            'vehicle' => (string) ($input['vehicle'] ?? 'standard_truck'),
            'temperature_c' => $input['temperature_c'] ?? null,
            'moisture_percent' => $input['moisture_percent'] ?? null,
            'relative_humidity_percent' => $input['relative_humidity_percent'] ?? null,
            'storage_horizon' => $input['storage_horizon'] ?? null,
        ];
    }

    private function snapshot(
        Shipment $shipment,
        string $name,
        array $input,
        array $analysis,
        array $route,
        array $carbon,
        array $evidence,
        array $notes
    ): array {
        return [
            'name' => $name,
            'engine_version' => self::VERSION,
            'simulated_at' => Carbon::now()->toIso8601String(),
            'shipment' => [
                'id' => $shipment->id,
                'commodity' => $shipment->harvest?->commodity ?? 'Tidak diketahui',
                'origin' => $shipment->origin,
                'destination' => $shipment->destination,
                'status' => $shipment->status,
            ],
            'input' => $input,
            'analysis' => $analysis,
            'route' => $route,
            'carbon' => $carbon,
            'evidence' => $evidence,
            'notes' => $notes,
            'provenance' => $this->provenance($analysis),
        ];
    }

    private function provenance(array $analysis): array
    {
        return [
            'engine' => self::VERSION,
            'commodity_profile' => [
                'name' => $analysis['commodity_profile']['name'] ?? null,
                'source_name' => $analysis['commodity_profile']['source_name'] ?? null,
                'source_url' => $analysis['commodity_profile']['source_url'] ?? null,
            ],
            'route_provider' => 'OpenRouteService / HeiGIT melalui RouteService AgriFlow',
            'risk_model' => $analysis['risk_assessment']['model_version'] ?? 'AgriFlow Operational Risk Engine',
            'quality_model' => $analysis['quality_prediction']['model_version'] ?? null,
            'calculation_type' => 'deterministic_decision_support',
        ];
    }

    private function assertOperationallyActive(Shipment $shipment): void
    {
        if ($shipment->isDelivered()) {
            throw new \InvalidArgumentException(
                'Simulasi Digital Twin telah ditutup untuk pengiriman yang sudah selesai. Riwayat skenario tetap dapat ditinjau.'
            );
        }
    }

    private function assertValidScenarioInput(array $input): void
    {
        $validator = Validator::make($input, [
            'name' => ['nullable', 'string', 'max:120'],
            'route_rank' => ['nullable', 'integer', 'min:0', 'max:3'],
            'delay_hours' => ['nullable', 'numeric', 'min:0', 'max:168'],
            'vehicle' => ['required', 'in:standard_truck,refrigerated_truck,electric_truck'],
            'temperature_c' => ['nullable', 'numeric', 'min:-20', 'max:60'],
            'moisture_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'relative_humidity_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'storage_horizon' => ['nullable', 'in:short_term,long_term'],
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }
}