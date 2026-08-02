<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\CommissionService;
use ReflectionClass;
use PDO;

class CommissionServiceTest extends TestCase
{
    private CommissionService $service;
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create required tables for method existence tests
        $this->pdo->exec("
            CREATE TABLE agent_commission_rates (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                agent_id INTEGER NOT NULL,
                tier TEXT NOT NULL,
                commission_rate REAL NOT NULL,
                effective_from DATE NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                tenant_id INTEGER DEFAULT 1
            )
        ");
        
        $this->pdo->exec("
            CREATE TABLE hybrid_commission_records (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                agent_id INTEGER NOT NULL,
                booking_id INTEGER NOT NULL,
                sale_amount REAL NOT NULL,
                commission_rate REAL,
                commission_amount REAL NOT NULL,
                tier TEXT NOT NULL,
                status TEXT DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                tenant_id INTEGER DEFAULT 1
            )
        ");
        
        $this->pdo->exec("
            CREATE TABLE hybrid_commission_plans (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                agent_id INTEGER NOT NULL,
                fixed_amount REAL NOT NULL,
                variable_rate REAL NOT NULL,
                sales_threshold REAL NOT NULL,
                valid_from DATE NOT NULL,
                valid_to DATE,
                status TEXT DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                tenant_id INTEGER DEFAULT 1
            )
        ");
        
        $this->service = new CommissionService($this->pdo);
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(CommissionService::class, $this->service);
    }

    public function testHasKeyMethods(): void
    {
        $methods = [
            'getAgentRate',
            'setAgentRate',
            'calculateAgentCommission',
            'recordAgentCommission',
            'createHybridPlan',
            'getActiveHybridPlan',
            'calculateHybridCommission',
            'setFarmerStructure',
            'getFarmerStructures',
            'getHybridPlans',
            'recordFarmerCommission',
            'getMlmRankRates',
            'setMlmRank',
            'getRules',
            'addRule',
            'getAgentCommissions',
            'approveCommission',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists($this->service, $method),
                "Method $method should exist on CommissionService"
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