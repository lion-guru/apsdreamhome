<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\EMIAutomationService;
use ReflectionClass;

class EMIAutomationServiceTest extends TestCase
{
    private EMIAutomationService $service;

    protected function setUp(): void
    {
        $this->service = new EMIAutomationService();
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(EMIAutomationService::class, $this->service);
    }

    public function testHasKeyMethods(): void
    {
        $methods = [
            'runAll',
            'runAutoPaymentCron',
            'updateInstallmentStatus',
            'applyDailyPenalties',
            'sendUpcomingPaymentReminders',
            'sendDunningEmails',
            'checkDefaultedBookings',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists($this->service, $method),
                "Method $method should exist on EMIAutomationService"
            );
        }
    }

    public function testServiceUsesServiceTenantTrait(): void
    {
        $reflection = new ReflectionClass($this->service);
        $traits = $reflection->getTraits();
        $this->assertArrayHasKey(\App\Traits\ServiceTenantTrait::class, $traits);
    }
}