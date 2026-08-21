<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
use App\Services\AI\DecisionEngine;

class SimulationController extends Controller
{

    public function index()
    {

$shipments = Shipment::with('harvest')
    ->latest()
    ->paginate(4);

        return view(
            'simulation.index',
            compact('shipments')
        );

    }
public function run(Request $request)
{
    $shipment = Shipment::with('harvest')
        ->findOrFail($request->shipment);

    $engine = new DecisionEngine();

    // =========================
    // BEFORE: kondisi asli shipment
    // =========================
    $before = $engine->analyze($shipment);

    // AFTER dimulai dari kondisi BEFORE
    $after = $before;

    // =========================
    // VEHICLE
    // =========================
    switch ($request->vehicle) {

        case 'Refrigerated Truck':
            $after['risk_score'] -= 20;
            $after['sustainability_score'] += 15;
            break;

        case 'Electric Truck':
            $after['risk_score'] -= 8;
            $after['sustainability_score'] += 20;
            break;
    }

    // =========================
    // TEMPERATURE
    // =========================
    if ($request->temperature <= 5) {
        $after['risk_score'] -= 15;
    }

    // =========================
    // ROUTE OPTIMIZATION
    // =========================
    if ($request->route) {
        $after['risk_score'] -= 10;
    }

    // =========================
    // DELAY
    // =========================
    $after['risk_score'] += ($request->delay * 8);

    // =========================
    // BATASI RISK 0-100
    // =========================
    $after['risk_score'] = max(
        0,
        min(
            100,
            round($after['risk_score'])
        )
    );

    // =========================
    // SUSTAINABILITY AFTER
    // =========================
    $after['sustainability_score'] = max(
        0,
        min(
            100,
            round(
                $before['sustainability_score']
                + ($before['risk_score'] - $after['risk_score'])
            )
        )
    );

    // =========================
    // CARBON AFTER
    // =========================
    $afterCarbon = match ($request->vehicle) {

        'Electric Truck' =>
            round($shipment->carbon_emission * 0.45, 1),

        'Refrigerated Truck' =>
            round($shipment->carbon_emission * 0.85, 1),

        default =>
            round($shipment->carbon_emission, 1),
    };

    // =========================
    // DURATION AFTER
    // =========================
    $afterDuration = $request->route
        ? round($shipment->duration_hours * 0.8, 1)
        : round($shipment->duration_hours, 1);

    // =========================
    // CARBON SAVED
    // =========================
    $carbonSaved = round(
        $shipment->carbon_emission - $afterCarbon,
        1
    );

    // =========================
    // RETURN RESULT
    // =========================
    return response()->json([

        'before' => [

            'risk_score' =>
                $before['risk_score'],

            'sustainability_score' =>
                $before['sustainability_score'],

            'carbon' =>
                round($shipment->carbon_emission, 1),

            'duration' =>
                round($shipment->duration_hours, 1),

            'vehicle' =>
                'Standard Truck',
        ],

        'after' => [

            'risk_score' =>
                $after['risk_score'],

            'sustainability_score' =>
                $after['sustainability_score'],

            'carbon' =>
                $afterCarbon,

            'carbon_saved' =>
                $carbonSaved,

            'duration' =>
                $afterDuration,

            'vehicle' =>
                $request->vehicle,
        ],

        'shipment' =>
            $shipment,
    ]);
}

}