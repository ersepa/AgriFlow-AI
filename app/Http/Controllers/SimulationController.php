<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Services\AI\DecisionEngine;
use Illuminate\Http\Request;

class SimulationController extends Controller
{
    public function index()
    {
        $shipments = Shipment::with('harvest')
            ->latest()
            ->paginate(4);

        return view('simulation.index', compact('shipments'));
    }

    public function run(Request $request, DecisionEngine $engine)
    {
        $validated = $request->validate([
            'shipment' => ['required', 'integer', 'exists:shipments,id'],
            'vehicle' => ['nullable', 'string', 'in:Truck,cold,ship,plane'],
            'temperature' => ['nullable', 'numeric', 'between:-10,60'],
            'delay' => ['nullable', 'numeric', 'min:0', 'max:72'],
            'route' => ['nullable'],
        ]);

        $shipment = Shipment::with('harvest')
            ->findOrFail($validated['shipment']);

        $result = $engine->simulate($shipment, [
            'vehicle' => $validated['vehicle'] ?? 'Truck',
            'temperature' => $validated['temperature'] ?? null,
            'delay' => $validated['delay'] ?? 0,
            'route' => $request->boolean('route'),
        ]);

        return response()->json([
            'before' => $result['before'],
            'after' => $result['after'],
            'shipment' => $shipment,
        ]);
    }
}
