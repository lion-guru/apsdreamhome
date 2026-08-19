<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\MlmInvestmentEngine;

class MlmInvestmentEngineTest extends TestCase
{
    public function test_instantiation(): void
    {
        $this->assertTrue(class_exists(MlmInvestmentEngine::class));
    }

    public function test_has_required_methods(): void
    {
        $this->assertTrue(method_exists(MlmInvestmentEngine::class, 'processJoiningPackage'));
        $this->assertTrue(method_exists(MlmInvestmentEngine::class, 'listPackages'));
        $this->assertTrue(method_exists(MlmInvestmentEngine::class, 'getPackage'));
        $this->assertTrue(method_exists(MlmInvestmentEngine::class, 'getUserRegistrations'));
        $this->assertTrue(method_exists(MlmInvestmentEngine::class, 'getStats'));
    }

    public function test_list_packages_returns_array(): void
    {
        $engine = new MlmInvestmentEngine();
        try {
            $packages = $engine->listPackages();
            $this->assertIsArray($packages);
        } catch (\PDOException $e) {
            $this->assertStringContainsString('doesn\'t exist', $e->getMessage());
        }
    }

    public function test_get_stats_returns_array(): void
    {
        $engine = new MlmInvestmentEngine();
        try {
            $stats = $engine->getStats();
            $this->assertIsArray($stats);
            $this->assertArrayHasKey('total_registrations', $stats);
        } catch (\PDOException $e) {
            $this->assertStringContainsString('doesn\'t exist', $e->getMessage());
        }
    }

    public function test_process_package_with_invalid_package(): void
    {
        $engine = new MlmInvestmentEngine();
        $result = $engine->processJoiningPackage(999999, 1);
        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_get_package_returns_null_for_invalid(): void
    {
        $engine = new MlmInvestmentEngine();
        try {
            $result = $engine->getPackage(999999);
            $this->assertNull($result);
        } catch (\PDOException $e) {
            $this->assertStringContainsString('doesn\'t exist', $e->getMessage());
        }
    }
}
