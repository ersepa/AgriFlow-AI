<?php

namespace Tests\Unit\Services\Sustainability;

use App\Services\Sustainability\FreightCarbonEstimateService;
use PHPUnit\Framework\TestCase;

class FreightCarbonEstimateServiceTest extends TestCase
{
    public function test_it_uses_tonne_km_activity_data_with_explicit_factor(): void
    {
        $service = new FreightCarbonEstimateService();

        $result = $service->estimate(500, 58);

        $this->assertTrue($result['available']);
        $this->assertSame('activity_based_tonne_km', $result['method']);
        $this->assertSame(0.10356, $result['emission_factor']);
        $this->assertSame(29.0, $result['tonne_km']);
        $this->assertSame(3.0, $result['estimated_kg']);
        $this->assertSame('tank_to_wheel', $result['system_boundary']);
        $this->assertFalse($result['is_measured']);
    }

    public function test_it_scales_with_shipment_mass_and_distance(): void
    {
        $service = new FreightCarbonEstimateService();

        $result = $service->estimate(4100, 534);

        $this->assertSame(226.73, $result['estimated_kg']);
        $this->assertSame(2189.4, $result['tonne_km']);
    }

    public function test_it_does_not_invent_carbon_when_activity_data_is_missing(): void
    {
        $service = new FreightCarbonEstimateService();

        $result = $service->estimate(null, 100);

        $this->assertFalse($result['available']);
        $this->assertNull($result['estimated_kg']);
    }
}
