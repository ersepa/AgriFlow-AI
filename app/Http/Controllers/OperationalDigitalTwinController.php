<?php

namespace App\Http\Controllers;

use App\Models\DigitalTwinScenario;
use App\Models\DigitalTwinComparisonSet;
use App\Models\Shipment;
use App\Services\DigitalTwin\ScenarioEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OperationalDigitalTwinController extends Controller
{
    private function ensureShipmentOwnership(Shipment $shipment): void
    {
        abort_unless(
            $shipment->harvest()
                ->where('user_id', Auth::id())
                ->exists(),
            404
        );
    }

    private function ensureScenarioOwnership(DigitalTwinScenario $scenario): void
    {
        abort_unless(
            $scenario->shipment()
                ->whereHas('harvest', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->exists(),
            404
        );
    }

    private function ensureComparisonOwnership(DigitalTwinComparisonSet $comparisonSet): void
    {
        abort_unless(
            $comparisonSet->shipment()
                ->whereHas('harvest', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->exists(),
            404
        );
    }

    public function index(Request $request, ScenarioEngine $engine): View
    {
        $shipments = Shipment::query()
            ->with('harvest')
            ->whereHas('harvest', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->whereIn('status', [
                'Harvested',
                'Packed',
                'In Transit',
            ])
            ->latest()
            ->get();

        $shipment = $request->integer('shipment')
            ? $shipments->firstWhere('id', $request->integer('shipment'))
            : $shipments->first();

        $baseline = null;
        $routeOptions = [];

        if ($shipment) {
            $baseline = $engine->baseline($shipment);
            $routeOptions = $engine->routeOptions($shipment);
        }

        return view(
            'digital-twin.operational',
            compact('shipments', 'shipment', 'baseline', 'routeOptions')
        );
    }

    public function simulate(
        Request $request,
        Shipment $shipment,
        ScenarioEngine $engine
    ): JsonResponse {
        $this->ensureShipmentOwnership($shipment);

        if ($shipment->isDelivered()) {
            return response()->json([
                'message' => 'Pengiriman ini sudah selesai dikirim. Pembuatan skenario Digital Twin baru telah ditutup, namun riwayat skenario tetap dapat dilihat.',
            ], 409);
        }

        $validated = $this->validateScenario($request);

        try {
            return response()->json($engine->compareOne($shipment, $validated));
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function store(
        Request $request,
        Shipment $shipment,
        ScenarioEngine $engine
    ): RedirectResponse {
        $this->ensureShipmentOwnership($shipment);

        if ($shipment->isDelivered()) {
            return redirect()
                ->route('completed-shipments.show', $shipment)
                ->with(
                    'warning',
                    'Pengiriman ini sudah selesai dikirim. Pembuatan skenario Digital Twin baru telah ditutup, namun riwayat skenario tetap dapat dilihat.'
                );
        }

        $validated = $this->validateScenario($request);

        try {
            $result = $engine->compareOne($shipment, $validated);
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withErrors(['scenario' => $e->getMessage()])
                ->withInput();
        }

        $scenario = DigitalTwinScenario::create([
            'shipment_id' => $shipment->id,
            'name' => $validated['name'] ?? 'Skenario',
            'engine_version' => $result['engine_version'],
            'input_snapshot' => $result['scenario']['input'],
            'baseline_snapshot' => $result['baseline'],
            'result_snapshot' => $result['scenario'],
            'comparison_snapshot' => $result['comparison'],
            'evidence_coverage' => $result['scenario']['evidence']['percent'] ?? 0,
            'is_preferred' => false,
        ]);

        return redirect()
            ->route('digital-twin.scenarios.show', $scenario)
            ->with('success', 'Skenario Digital Twin berhasil disimpan.');
    }

    public function compare(
        Request $request,
        Shipment $shipment,
        ScenarioEngine $engine
    ): JsonResponse {
        $this->ensureShipmentOwnership($shipment);

        if ($shipment->isDelivered()) {
            return response()->json([
                'message' => 'Pengiriman ini sudah selesai dikirim. Pembuatan skenario Digital Twin baru telah ditutup, namun riwayat skenario tetap dapat dilihat.',
            ], 409);
        }

        $validated = $request->validate([
            'scenarios' => ['required', 'array', 'min:1', 'max:3'],
            'scenarios.*.name' => ['nullable', 'string', 'max:120'],
            'scenarios.*.route_rank' => ['nullable', 'integer', 'min:0', 'max:3'],
            'scenarios.*.delay_hours' => ['nullable', 'numeric', 'min:0', 'max:168'],
            'scenarios.*.vehicle' => ['required', 'in:standard_truck,refrigerated_truck,electric_truck'],
            'scenarios.*.temperature_c' => ['nullable', 'numeric', 'min:-20', 'max:60'],
            'scenarios.*.moisture_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'scenarios.*.relative_humidity_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'scenarios.*.storage_horizon' => ['nullable', 'in:short_term,long_term'],
        ]);

        try {
            return response()->json($engine->compareMany($shipment, $validated['scenarios']));
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function storeComparison(
        Request $request,
        Shipment $shipment,
        ScenarioEngine $engine
    ): RedirectResponse {
        $this->ensureShipmentOwnership($shipment);

        if ($shipment->isDelivered()) {
            return redirect()
                ->route('completed-shipments.show', $shipment)
                ->with(
                    'warning',
                    'Pengiriman ini sudah selesai dikirim. Pembuatan skenario Digital Twin baru telah ditutup, namun riwayat skenario tetap dapat dilihat.'
                );
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'scenarios_json' => ['required', 'string'],
        ]);

        $scenarioInputs = json_decode($validated['scenarios_json'], true);

        if (!is_array($scenarioInputs) || count($scenarioInputs) < 1 || count($scenarioInputs) > 3) {
            return back()->withErrors([
                'scenarios_json' => 'Set perbandingan harus berisi antara 1 hingga 3 skenario.',
            ]);
        }

        $validator = Validator::make(
            ['scenarios' => $scenarioInputs],
            [
                'scenarios' => ['required', 'array', 'min:1', 'max:3'],
                'scenarios.*.name' => ['nullable', 'string', 'max:120'],
                'scenarios.*.route_rank' => ['nullable', 'integer', 'min:0', 'max:3'],
                'scenarios.*.delay_hours' => ['nullable', 'numeric', 'min:0', 'max:168'],
                'scenarios.*.vehicle' => ['required', 'in:standard_truck,refrigerated_truck,electric_truck'],
                'scenarios.*.temperature_c' => ['nullable', 'numeric', 'min:-20', 'max:60'],
                'scenarios.*.moisture_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'scenarios.*.relative_humidity_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'scenarios.*.storage_horizon' => ['nullable', 'in:short_term,long_term'],
            ]
        );

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $scenarioInputs = $validator->validated()['scenarios'];

        try {
            $result = $engine->compareMany($shipment, $scenarioInputs);
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withErrors(['scenarios_json' => $e->getMessage()])
                ->withInput();
        }

        $preferredOption = $result['comparison']['preferred_option'] ?? 'current_plan';

        if ($preferredOption === 'scenario') {
            $preferredOption = $result['comparison']['recommended_scenario']['name'] ?? 'scenario';
        }

        $set = DigitalTwinComparisonSet::create([
            'shipment_id' => $shipment->id,
            'name' => $validated['name'],
            'engine_version' => $result['engine_version'],
            'baseline_snapshot' => $result['baseline'],
            'scenarios_snapshot' => $result['scenarios'],
            'comparison_snapshot' => $result['comparison'],
            'preferred_option' => $preferredOption,
            'evidence_coverage' => $result['average_evidence_coverage'],
        ]);

        return redirect()
            ->route('digital-twin.comparisons.show', $set)
            ->with('success', 'Set perbandingan Digital Twin berhasil disimpan.');
    }

    public function comparisonHistory(): View
    {
        $comparisonSets = DigitalTwinComparisonSet::query()
            ->select([
                'id',
                'shipment_id',
                'name',
                'engine_version',
                'preferred_option',
                'evidence_coverage',
                'created_at',
                'updated_at',
            ])
            ->selectRaw('COALESCE(JSON_LENGTH(scenarios_snapshot), 0) as scenarios_count')
            ->with('shipment.harvest')
            ->whereHas('shipment.harvest', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('digital-twin.comparison-history', compact('comparisonSets'));
    }

    public function comparisonShow(DigitalTwinComparisonSet $comparisonSet): View
    {
        $this->ensureComparisonOwnership($comparisonSet);
        $comparisonSet->loadMissing('shipment.harvest');

        return view('digital-twin.comparison-show', compact('comparisonSet'));
    }

    public function history(): View
    {
        $scenarios = DigitalTwinScenario::query()
            ->with('shipment.harvest')
            ->whereHas('shipment.harvest', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->latest()
            ->paginate(15);

        return view('digital-twin.history', compact('scenarios'));
    }

    public function show(DigitalTwinScenario $scenario): View
    {
        $this->ensureScenarioOwnership($scenario);
        $scenario->loadMissing('shipment.harvest');

        return view('digital-twin.show', compact('scenario'));
    }

    public function prefer(DigitalTwinScenario $scenario): RedirectResponse
    {
        $this->ensureScenarioOwnership($scenario);
        $scenario->loadMissing('shipment');

        if ($shipment = $scenario->shipment) {
            if ($shipment->isDelivered()) {
                return redirect()
                    ->route('digital-twin.scenarios.show', $scenario)
                    ->with(
                        'warning',
                        'Skenario ini milik pengiriman yang sudah selesai. Pilihan skenario historis terkunci setelah pengiriman selesai.'
                    );
            }
        }

        DigitalTwinScenario::where('shipment_id', $scenario->shipment_id)->update([
            'is_preferred' => false,
        ]);

        $scenario->update([
            'is_preferred' => true,
        ]);

        return back()->with(
            'success',
            'Skenario ditandai sebagai pilihan utama. Perubahan ini tidak mengubah rencana pengiriman yang tercatat.'
        );
    }

    private function validateScenario(Request $request): array
    {
        return $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'route_rank' => ['nullable', 'integer', 'min:0', 'max:3'],
            'delay_hours' => ['nullable', 'numeric', 'min:0', 'max:168'],
            'vehicle' => ['required', 'in:standard_truck,refrigerated_truck,electric_truck'],
            'temperature_c' => ['nullable', 'numeric', 'min:-20', 'max:60'],
            'moisture_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'relative_humidity_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'storage_horizon' => ['nullable', 'in:short_term,long_term'],
        ]);
    }
}