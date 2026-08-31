<?php

namespace App\Services\Sustainability;

use App\Models\Shipment;

/**
 * Source-backed road-freight CO2e estimator used by AgriFlow.
 *
 * Activity method:
 *   shipment mass (tonnes) x route distance (km) x emission factor
 *
 * Reference factor:
 *   UK DESNZ 2026 GHG Conversion Factors, "Freighting goods",
 *   all HGV types, non-refrigerated, average laden, tank-to-wheel (TTW):
 *   0.10356 kg CO2e / tonne-km.
 *
 * This is an operational estimate, not a measured emissions inventory and not
 * an Indonesia-specific vehicle/fuel calibration. AgriFlow deliberately does
 * not apply hidden vehicle multipliers when the exact vehicle/fuel class is
 * unknown.
 */
class FreightCarbonEstimateService
{
    public const ROAD_FREIGHT_FACTOR_KG_CO2E_PER_TONNE_KM = 0.10356;

    public const FACTOR_YEAR = 2026;

    public const FACTOR_SOURCE =
        'UK Department for Energy Security and Net Zero (DESNZ) — 2026 GHG Conversion Factors, Freighting goods';

    public const FACTOR_URL =
        'https://www.gov.uk/government/publications/greenhouse-gas-reporting-conversion-factors-2026';

    public const FACTOR_SCOPE =
        'All HGV types, non-refrigerated, average laden; tank-to-wheel (TTW)';

    public function estimate(
        ?float $weightKg,
        ?float $distanceKm
    ): array {
        if (
            $weightKg === null
            || $distanceKm === null
            || $weightKg <= 0
            || $distanceKm <= 0
        ) {
            return $this->unavailable();
        }

        $weightTonnes = $weightKg / 1000;
        $tonneKm = $weightTonnes * $distanceKm;
        $estimatedKg = $tonneKm
            * self::ROAD_FREIGHT_FACTOR_KG_CO2E_PER_TONNE_KM;

        return [
            'estimated_kg' => round($estimatedKg, 2),
            'available' => true,
            'method' => 'activity_based_tonne_km',
            'weight_kg' => round($weightKg, 2),
            'weight_tonnes' => round($weightTonnes, 4),
            'distance_km' => round($distanceKm, 2),
            'tonne_km' => round($tonneKm, 4),
            'emission_factor' =>
                self::ROAD_FREIGHT_FACTOR_KG_CO2E_PER_TONNE_KM,
            'emission_factor_unit' => 'kg CO2e / tonne-km',
            'factor_year' => self::FACTOR_YEAR,
            'factor_source' => self::FACTOR_SOURCE,
            'factor_url' => self::FACTOR_URL,
            'factor_scope' => self::FACTOR_SCOPE,
            'system_boundary' => 'tank_to_wheel',
            'is_measured' => false,
            'is_indonesia_specific' => false,
            'note' =>
                'Activity-based road-freight estimate using shipment mass and route distance. The DESNZ factor is used as a transparent reference because exact Indonesian vehicle, fuel, load-factor, and refrigeration telemetry are not currently stored.',
        ];
    }

    public function estimateForShipment(
        Shipment $shipment,
        ?float $distanceKm = null
    ): array {
        $shipment->loadMissing('harvest');

        return $this->estimate(
            $shipment->harvest?->weight !== null
                ? (float) $shipment->harvest->weight
                : null,
            $distanceKm
                ?? ($shipment->distance_km !== null
                    ? (float) $shipment->distance_km
                    : null)
        );
    }

    private function unavailable(): array
    {
        return [
            'estimated_kg' => null,
            'available' => false,
            'method' => 'activity_based_tonne_km',
            'weight_kg' => null,
            'weight_tonnes' => null,
            'distance_km' => null,
            'tonne_km' => null,
            'emission_factor' =>
                self::ROAD_FREIGHT_FACTOR_KG_CO2E_PER_TONNE_KM,
            'emission_factor_unit' => 'kg CO2e / tonne-km',
            'factor_year' => self::FACTOR_YEAR,
            'factor_source' => self::FACTOR_SOURCE,
            'factor_url' => self::FACTOR_URL,
            'factor_scope' => self::FACTOR_SCOPE,
            'system_boundary' => 'tank_to_wheel',
            'is_measured' => false,
            'is_indonesia_specific' => false,
            'note' =>
                'Carbon estimate unavailable because shipment mass or route distance is missing.',
        ];
    }
}
