<?php

namespace App\Services\AI;

use App\Models\Shipment;
use Carbon\Carbon;

class DecisionEngine
{
    public function optimizeShipments()
{
    return Shipment::with('harvest')
        ->whereIn('status', [
            'Harvested',
            'Packed',
            'In Transit'
        ])
        ->get()
        ->map(function ($shipment) {

            $analysis = $this->analyze($shipment);

            return [

                'shipment' => $shipment,

                'commodity' => $shipment->harvest->commodity,

                'destination' => $shipment->destination,

                'priority_score' => $analysis['priority_score'],

                'risk_score' => $analysis['risk_score'],

                'recommended_action' => $analysis['recommended_action']

            ];

        })
        ->sortByDesc('priority_score')
        ->values();
}

public function analyze(Shipment $shipment): array
{
    $risk = $this->calculateRiskScore($shipment);
    $priority = $this->calculatePriorityScore($shipment, $risk);
    $carbon = $this->calculateCarbonScore($shipment);
    $sustainability = $this->calculateSustainabilityScore($risk, $carbon);

    return [
        'risk_score' => $risk,
        'priority_score' => $priority,
        'carbon_score' => $carbon,
        'sustainability_score' => $sustainability,
        'priority_level' => $this->getPriorityLevel($priority),
    ];
}

    private function calculateRiskScore(Shipment $shipment): int
    {
        $score = 0;

        $harvest = $shipment->harvest;

        $remainingDays = Carbon::now()->diffInDays(
            Carbon::parse($harvest->expiry_date),
            false
        );

        // Remaining shelf life
        if ($remainingDays <= 0)
            $score += 40;
        elseif ($remainingDays <= 2)
            $score += 30;
        elseif ($remainingDays <= 5)
            $score += 20;
        else
            $score += 5;

        // Distance
        if ($shipment->distance_km >= 300)
            $score += 25;
        elseif ($shipment->distance_km >= 100)
            $score += 15;
        else
            $score += 5;

        // Shipment status
        switch ($shipment->status) {
            case 'Harvested':
                $score += 20;
                break;

            case 'Packed':
                $score += 15;
                break;

            case 'In Transit':
                $score += 10;
                break;

            case 'Delivered':
                $score += 0;
                break;
        }

        return min($score,100);
    }

private function calculatePriorityScore(Shipment $shipment, int $risk): int
{
    $harvest = $shipment->harvest;

    $remainingDays = Carbon::now()->diffInDays(
        Carbon::parse($harvest->expiry_date),
        false
    );

    /*
    =======================================
    WEIGHTED SCORING MODEL
    =======================================

    Remaining Shelf Life : 40%
    Commodity            : 25%
    Distance             : 20%
    Shipment Status      : 15%
    */

    $remainingScore = 0;
    $commodityScore = 15;
    $distanceScore = 0;
    $statusScore = 0;

    // -------------------------
    // Remaining Shelf Life (40)
    // -------------------------

    if ($remainingDays <= 0)
        $remainingScore = 40;
    elseif ($remainingDays <= 2)
        $remainingScore = 35;
    elseif ($remainingDays <= 5)
        $remainingScore = 20;
    else
        $remainingScore = 10;


    // -------------------------
    // Distance (20)
    // -------------------------

    if ($shipment->distance_km >= 300)
        $distanceScore = 20;
    elseif ($shipment->distance_km >= 100)
        $distanceScore = 15;
    else
        $distanceScore = 8;

    // -------------------------
    // Shipment Status (15)
    // -------------------------

    switch ($shipment->status) {

        case 'Harvested':
            $statusScore = 15;
            break;

        case 'Packed':
            $statusScore = 10;
            break;

        case 'In Transit':
            $statusScore = 5;
            break;

        default:
            $statusScore = 0;
    }

    return min(
        $remainingScore +
        $commodityScore +
        $distanceScore +
        $statusScore,
        100
    );
}

    private function calculateCarbonScore(Shipment $shipment): float
    {
        return round(($shipment->distance_km ?? 0) * 0.12,2);
    }

    private function calculateSustainabilityScore(int $risk,float $carbon): int
    {
        $score = 100;

        $score -= ($risk * 0.4);

        $score -= ($carbon * 0.5);

        return max(round($score),0);
    }

private function getPriorityLevel(int $priority): string
{
    if ($priority >= 80) {
        return 'Critical';
    }

    if ($priority >= 60) {
        return 'High';
    }

    if ($priority >= 40) {
        return 'Medium';
    }

    return 'Low';
}

private function generateExplainability(Shipment $shipment, int $risk): array
{
    $remainingDays = Carbon::now()->diffInDays(
        Carbon::parse($shipment->harvest->expiry_date),
        false
    );

    $remaining = min(45, max(5, (10 - $remainingDays) * 5));

    $distance = min(
        30,
        max(5, round(($shipment->distance_km ?? 0) / 15))
    );

    $status = match ($shipment->status) {
        'Harvested' => 18,
        'Packed' => 12,
        'In Transit' => 8,
        default => 5,
    };

    // Bobot generic untuk karakteristik komoditas
    $commodity = 15;

    $confidence = max(
        90,
        min(99, 100 - round($risk * 0.08))
    );

    $factors = [
        'Remaining Shelf Life' => $remaining,
        'Transportation Distance' => $distance,
        'Shipment Status' => $status,
        'Commodity Characteristics' => $commodity,
    ];

    arsort($factors);

    $mainFactor = array_key_first($factors);

    $conclusion = match ($mainFactor) {
        'Remaining Shelf Life' =>
            'The remaining shelf life is the dominant factor affecting shipment priority.',

        'Transportation Distance' =>
            'Transportation distance significantly contributes to shipment risk.',

        'Shipment Status' =>
            'The current shipment status increases operational exposure and affects priority.',

        default =>
            'Multiple logistics factors were evaluated by the AI to produce this prediction.',
    };

    return [
        'remaining' => $remaining,
        'distance' => $distance,
        'status' => $status,
        'commodity' => $commodity,
        'confidence' => $confidence,
        'conclusion' => $conclusion,
    ];
}
public function generateOperationalRecommendation(Shipment $shipment): array
{
    $analysis = $this->analyze($shipment);

    $harvest = $shipment->harvest;

    $remainingDays = Carbon::now()->diffInDays(
        Carbon::parse($harvest->expiry_date),
        false
    );

    // =========================
    // Dispatch Deadline
    // =========================

    if ($analysis['priority_level'] == 'Critical') {

        $deadline = "Within 6 Hours";

    } elseif ($analysis['priority_level'] == 'High') {

        $deadline = "Today";

    } elseif ($analysis['priority_level'] == 'Medium') {

        $deadline = "Within 2 Days";

    } else {

        $deadline = "Flexible";

    }

    // =========================
    // Recommended Vehicle
    // =========================

    if ($remainingDays <= 2 || $analysis['risk_score'] >= 70) {

        $vehicle = "Refrigerated Truck";

    } elseif (($shipment->distance_km ?? 0) >= 300) {

        $vehicle = "Large Truck";

    } else {

        $vehicle = "Regular Truck";

    }

    // =========================
    // Storage Recommendation
    // =========================

    if ($remainingDays <= 2) {

        $storage = "Cold Storage (4–8°C)";

    } else {

        $storage = "Dry Warehouse";

    }

    // =========================
    // Estimated Arrival Quality
    // =========================

    $quality = 100;

    $quality -= ($analysis['risk_score'] * 0.5);

    $quality -= (($shipment->distance_km ?? 0) / 25);

    $quality = max(0, round($quality));

    // =========================
    // Food Waste Level
    // =========================

    if ($analysis['risk_score'] >= 80) {

        $foodWaste = "High";

    } elseif ($analysis['risk_score'] >= 50) {

        $foodWaste = "Medium";

    } else {

        $foodWaste = "Low";

    }

    return [

        'dispatch_deadline' => $deadline,

        'recommended_vehicle' => $vehicle,

        'recommended_storage' => $storage,

        'estimated_arrival_quality' => $quality,

        'food_waste_level' => $foodWaste,

    ];
}
}