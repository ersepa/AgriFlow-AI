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

class OperationalDigitalTwinController extends Controller
{
    public function index(
        Request $request,
        ScenarioEngine $engine
    ): View {
        $shipments =
            Shipment::with('harvest')
                ->whereIn(
                    'status',
                    [
                        'Harvested',
                        'Packed',
                        'In Transit',
                    ]
                )
                ->latest()
                ->get();

        $shipment =
            $request->integer('shipment')
                ? $shipments->firstWhere(
                    'id',
                    $request->integer(
                        'shipment'
                    )
                )
                : $shipments->first();

        $baseline = null;
        $routeOptions = [];

        if ($shipment) {
            $baseline =
                $engine->baseline(
                    $shipment
                );

            $routeOptions =
                $engine->routeOptions(
                    $shipment
                );
        }

        return view(
            'digital-twin.operational',
            compact(
                'shipments',
                'shipment',
                'baseline',
                'routeOptions'
            )
        );
    }

    public function simulate(
        Request $request,
        Shipment $shipment,
        ScenarioEngine $engine
    ): JsonResponse {
        $validated =
            $this->validateScenario(
                $request
            );

        try {
            return response()->json(
                $engine->compareOne(
                    $shipment,
                    $validated
                )
            );
        } catch (
            \InvalidArgumentException $e
        ) {
            return response()->json(
                [
                    'message' =>
                        $e->getMessage(),
                ],
                422
            );
        }
    }

    public function store(
        Request $request,
        Shipment $shipment,
        ScenarioEngine $engine
    ): RedirectResponse {
        $validated =
            $this->validateScenario(
                $request
            );

        $result =
            $engine->compareOne(
                $shipment,
                $validated
            );

        $scenario =
            DigitalTwinScenario::create([
                'shipment_id' =>
                    $shipment->id,
                'name' =>
                    $validated['name']
                    ?? 'Scenario',
                'engine_version' =>
                    $result[
                        'engine_version'
                    ],
                'input_snapshot' =>
                    $result[
                        'scenario'
                    ]['input'],
                'baseline_snapshot' =>
                    $result[
                        'baseline'
                    ],
                'result_snapshot' =>
                    $result[
                        'scenario'
                    ],
                'comparison_snapshot' =>
                    $result[
                        'comparison'
                    ],
                'evidence_coverage' =>
                    $result[
                        'scenario'
                    ]['evidence']['percent']
                    ?? 0,
                'is_preferred' =>
                    false,
            ]);

        return redirect()
            ->route(
                'digital-twin.scenarios.show',
                $scenario
            )
            ->with(
                'success',
                'Digital Twin scenario saved.'
            );
    }

    public function compare(
        Request $request,
        Shipment $shipment,
        ScenarioEngine $engine
    ): JsonResponse {
        $validated =
            $request->validate([
                'scenarios' => [
                    'required',
                    'array',
                    'min:1',
                    'max:3',
                ],
                'scenarios.*.name' => [
                    'nullable',
                    'string',
                    'max:120',
                ],
                'scenarios.*.route_rank' => [
                    'nullable',
                    'integer',
                    'min:0',
                    'max:3',
                ],
                'scenarios.*.delay_hours' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:168',
                ],
                'scenarios.*.vehicle' => [
                    'required',
                    'in:standard_truck,refrigerated_truck,electric_truck',
                ],
                'scenarios.*.temperature_c' => [
                    'nullable',
                    'numeric',
                    'min:-20',
                    'max:60',
                ],
                'scenarios.*.moisture_percent' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:100',
                ],
                'scenarios.*.relative_humidity_percent' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:100',
                ],
                'scenarios.*.storage_horizon' => [
                    'nullable',
                    'in:short_term,long_term',
                ],
            ]);

        try {
            return response()->json(
                $engine->compareMany(
                    $shipment,
                    $validated['scenarios']
                )
            );
        } catch (
            \InvalidArgumentException $e
        ) {
            return response()->json(
                [
                    'message' =>
                        $e->getMessage(),
                ],
                422
            );
        }
    }

    public function storeComparison(
        Request $request,
        Shipment $shipment,
        ScenarioEngine $engine
    ): RedirectResponse {
        $validated =
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:120',
                ],
                'scenarios_json' => [
                    'required',
                    'string',
                ],
            ]);

        $scenarioInputs =
            json_decode(
                $validated[
                    'scenarios_json'
                ],
                true
            );

        if (
            !is_array($scenarioInputs)
            || count($scenarioInputs) < 1
            || count($scenarioInputs) > 3
        ) {
            return back()
                ->withErrors([
                    'scenarios_json' =>
                        'Comparison set must contain between 1 and 3 scenarios.',
                ]);
        }

        $result =
            $engine->compareMany(
                $shipment,
                $scenarioInputs
            );

        $preferredOption =
            $result['comparison'][
                'preferred_option'
            ] ?? 'current_plan';

        if (
            $preferredOption === 'scenario'
        ) {
            $preferredOption =
                $result['comparison'][
                    'recommended_scenario'
                ]['name']
                ?? 'scenario';
        }

        $set =
            DigitalTwinComparisonSet::create([
                'shipment_id' =>
                    $shipment->id,
                'name' =>
                    $validated['name'],
                'engine_version' =>
                    $result['engine_version'],
                'baseline_snapshot' =>
                    $result['baseline'],
                'scenarios_snapshot' =>
                    $result['scenarios'],
                'comparison_snapshot' =>
                    $result['comparison'],
                'preferred_option' =>
                    $preferredOption,
                'evidence_coverage' =>
                    $result[
                        'average_evidence_coverage'
                    ],
            ]);

        return redirect()
            ->route(
                'digital-twin.comparisons.show',
                $set
            )
            ->with(
                'success',
                'Digital Twin comparison set saved.'
            );
    }

public function comparisonHistory(): View
{
    $comparisonSets =
        DigitalTwinComparisonSet::query()
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
            ->with(
                'shipment.harvest'
            )
            ->orderByDesc(
                'created_at'
            )
            ->paginate(15);

    return view(
        'digital-twin.comparison-history',
        compact(
            'comparisonSets'
        )
    );
}

    public function comparisonShow(
        DigitalTwinComparisonSet $comparisonSet
    ): View {
        $comparisonSet
            ->loadMissing(
                'shipment.harvest'
            );

        return view(
            'digital-twin.comparison-show',
            compact(
                'comparisonSet'
            )
        );
    }

    public function history(): View
    {
        $scenarios =
            DigitalTwinScenario::with(
                'shipment.harvest'
            )
                ->latest()
                ->paginate(15);

        return view(
            'digital-twin.history',
            compact('scenarios')
        );
    }

    public function show(
        DigitalTwinScenario $scenario
    ): View {
        $scenario->loadMissing(
            'shipment.harvest'
        );

        return view(
            'digital-twin.show',
            compact('scenario')
        );
    }

    public function prefer(
        DigitalTwinScenario $scenario
    ): RedirectResponse {
        DigitalTwinScenario::where(
            'shipment_id',
            $scenario->shipment_id
        )->update([
            'is_preferred' => false,
        ]);

        $scenario->update([
            'is_preferred' => true,
        ]);

        return back()->with(
            'success',
            'Scenario marked as preferred. This does not modify the live shipment plan.'
        );
    }

    private function validateScenario(
        Request $request
    ): array {
        return $request->validate([
            'name' => [
                'nullable',
                'string',
                'max:120',
            ],
            'route_rank' => [
                'nullable',
                'integer',
                'min:0',
                'max:3',
            ],
            'delay_hours' => [
                'nullable',
                'numeric',
                'min:0',
                'max:168',
            ],
            'vehicle' => [
                'required',
                'in:standard_truck,refrigerated_truck,electric_truck',
            ],
            'temperature_c' => [
                'nullable',
                'numeric',
                'min:-20',
                'max:60',
            ],
            'moisture_percent' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'relative_humidity_percent' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'storage_horizon' => [
                'nullable',
                'in:short_term,long_term',
            ],
        ]);
    }
}
