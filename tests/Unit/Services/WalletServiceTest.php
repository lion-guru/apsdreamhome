<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\WalletService;
use ReflectionClass;

class WalletServiceTest extends TestCase
{
    private WalletService $service;

    protected function setUp(): void
    {
        $this->service = new WalletService();
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(WalletService::class, $this->service);
    }

    public function testHasKeyMethods(): void
    {
        $methods = [
            'ensureWallet',
            'getBalance',
            'credit',
            'debit',
            'getTransactions',
            'transferToEmi',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists($this->service, $method),
                "Method $method should exist on WalletService"
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