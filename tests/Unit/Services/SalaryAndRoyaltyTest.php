<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\RoyaltyService;

class SalaryAndRoyaltyTest extends TestCase
{
    private RoyaltyService $royaltyService;

    protected function setUp(): void
    {
        $this->royaltyService = new RoyaltyService();
    }

    public function testRoyaltyServiceInstantiation(): void
    {
        $this->assertInstanceOf(RoyaltyService::class, $this->royaltyService);
    }

    public function testRoyaltyServiceHasCalculateMethod(): void
    {
        $this->assertTrue(method_exists($this->royaltyService, 'calculateRoyaltyPool'));
    }

    public function testRoyaltyServiceHasDistributeMethod(): void
    {
        $this->assertTrue(method_exists($this->royaltyService, 'distributeRoyalty'));
    }

    // SalaryService was archived (unused) - test removed
    // @see _archive/dead_services/SalaryService.php
}