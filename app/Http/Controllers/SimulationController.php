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

    $before = $engine->analyze($shipment);

    $after = $before;

    /*
    |--------------------------------------------------------------------------
    | Vehicle
    |--------------------------------------------------------------------------
    */

    switch($request->vehicle){

        case 'Refrigerated Truck':

            $after['risk_score'] -= 20;
            $after['sustainability_score'] += 15;

        break;

        case 'Electric Truck':

            $after['risk_score'] -= 8;
            $after['sustainability_score'] += 20;

        break;

    }

    /*
    |--------------------------------------------------------------------------
    | Temperature
    |--------------------------------------------------------------------------
    */

    if($request->temperature <= 5){

        $after['risk_score'] -= 15;

    }

    /*
    |--------------------------------------------------------------------------
    | Route
    |--------------------------------------------------------------------------
    */

    if($request->route){

        $after['risk_score'] -= 10;

    }

    /*
    |--------------------------------------------------------------------------
    | Delay
    |--------------------------------------------------------------------------
    */

    $after['risk_score'] +=
        ($request->delay * 8);

    /*
    |--------------------------------------------------------------------------
    */

    $after['risk_score'] = max(
        0,
        min(
            100,
            round($after['risk_score'])
        )
    );

    $after['sustainability_score'] = max(
        0,
        min(
            100,
            round(
                100-$after['risk_score']
            )
        )
    );
return response()->json([

    'before'=>[

        'risk_score'=>$before['risk_score'],

        'sustainability_score'=>$before['sustainability_score'],

        'carbon'=>round($shipment->carbon_emission,1),

        'duration'=>round($shipment->duration_hours,1),

        'vehicle'=>'Standard Truck'

    ],

    'after'=>[

        'risk_score'=>$after['risk_score'],

        'sustainability_score'=>$after['sustainability_score'],

        'carbon'=>match($request->vehicle){

            'Electric Truck'
                =>round($shipment->carbon_emission*0.45,1),

            'Refrigerated Truck'
                =>round($shipment->carbon_emission*0.85,1),

            default
                =>round($shipment->carbon_emission,1)

        },

        'duration'=>$request->route
            ?round($shipment->duration_hours*0.8,1)
            :round($shipment->duration_hours,1),

        'vehicle'=>$request->vehicle

    ],

    'shipment'=>$shipment

]);

}

}