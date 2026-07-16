<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\PricingService;

class PricingServiceTest extends TestCase
{
    private PricingService $service;

    protected function setUp(): void
    {
        $this->service = new PricingService();
    }

    public function testGetPricingMatrix(): void
    {
        $matrix = $this->service->getPricingMatrix();
        
        $this->assertIsArray($matrix);
        $this->assertArrayHasKey('block_a', $matrix);
        $this->assertArrayHasKey('block_b', $matrix);
        $this->assertArrayHasKey('block_c', $matrix);
        $this->assertArrayHasKey('corner_1500', $matrix);
        $this->assertArrayHasKey('corner_1000', $matrix);
    }

    public function testGetBlockPricing(): void
    {
        $pricing = $this->service->getBlockPricing('block_a');
        
        $this->assertNotNull($pricing);
        $this->assertEquals('Block A', $pricing['label']);
        $this->assertEquals(1000, $pricing['area_sqft']);
        $this->assertEquals(950, $pricing['final_rate']);
    }

    public function testGetBlockPricingCaseInsensitive(): void
    {
        $pricing1 = $this->service->getBlockPricing('BLOCK_A');
        $pricing2 = $this->service->getBlockPricing('Block A');
        $pricing3 = $this->service->getBlockPricing('block-a');
        
        $this->assertNotNull($pricing1);
        $this->assertEquals($pricing1, $pricing2);
        $this->assertEquals($pricing2, $pricing3);
    }

    public function testGetBlockPricingInvalid(): void
    {
        $pricing = $this->service->getBlockPricing('invalid_block');
        $this->assertNull($pricing);
    }

    public function testCalculatePlotValue(): void
    {
        $result = $this->service->calculatePlotValue('block_a');
        
        $this->assertArrayHasKey('block', $result);
        $this->assertArrayHasKey('area_sqft', $result);
        $this->assertArrayHasKey('rate_per_sqft', $result);
        $this->assertArrayHasKey('total_value', $result);
        $this->assertArrayHasKey('emi_allowed', $result);
        $this->assertArrayHasKey('payment_plan', $result);
        $this->assertArrayHasKey('booking_amount', $result);
        
        $this->assertEquals('Block A', $result['block']);
        $this->assertEquals(1000, $result['area_sqft']);
        $this->assertEquals(950, $result['rate_per_sqft']);
        $this->assertEquals(950000, $result['total_value']);
        $this->assertFalse($result['emi_allowed']);
    }

    public function testCalculatePlotValueWithAreaOverride(): void
    {
        $result = $this->service->calculatePlotValue('block_b', 1500);
        
        $this->assertEquals(1500, $result['area_sqft']);
        $this->assertEquals(1275000, $result['total_value']); // 1500 * 850
    }

    public function testCalculatePlotValueInvalidBlock(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->calculatePlotValue('invalid_block');
    }

    public function testGetDefaultBookingAmount(): void
    {
        $amount = $this->service->getDefaultBookingAmount();
        $this->assertEquals(51000, $amount);
    }
}