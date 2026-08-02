<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\PayoutBatchService;
use ReflectionClass;

class PayoutBatchServiceTest extends TestCase
{
    private PayoutBatchService $service;

    protected function setUp(): void
    {
        $this->service = new PayoutBatchService();
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(PayoutBatchService::class, $this->service);
    }

    public function testHasKeyMethods(): void
    {
        $methods = [
            'createBatch',
            'autoPopulateBatch',
            'submitForApproval',
            'approveBatch',
            'rejectBatch',
            'startProcessing',
            'completeEntry',
            'completeBatch',
            'getBatch',
            'getBatchEntries',
            'getBatches',
            'getStats',
            'generateBankExport',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists($this->service, $method),
                "Method $method should exist on PayoutBatchService"
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