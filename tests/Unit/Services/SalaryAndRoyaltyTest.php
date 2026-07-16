<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\SalaryService;
use App\Services\RoyaltyService;

class SalaryAndRoyaltyTest extends TestCase
{
    private SalaryService $salaryService;
    private RoyaltyService $royaltyService;

    protected function setUp(): void
    {
        $this->salaryService = new SalaryService();
        $this->royaltyService = new RoyaltyService();
    }

    public function testSalaryServiceInstantiation(): void
    {
        $this->assertInstanceOf(SalaryService::class, $this->salaryService);
    }

    public function testRoyaltyServiceInstantiation(): void
    {
        $this->assertInstanceOf(RoyaltyService::class, $this->royaltyService);
    }

    public function testSalaryServiceHasCalculateMethod(): void
    {
        $this->assertTrue(method_exists($this->salaryService, 'calculateSalary'));
    }

    public function testRoyaltyServiceHasCalculateMethod(): void
    {
        $this->assertTrue(method_exists($this->royaltyService, 'calculateRoyaltyPool'));
    }

    public function testRoyaltyServiceHasDistributeMethod(): void
    {
        $this->assertTrue(method_exists($this->royaltyService, 'distributeRoyalty'));
    }
}