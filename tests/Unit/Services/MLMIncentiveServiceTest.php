<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\MLMIncentiveService;

class MLMIncentiveServiceTest extends TestCase
{
    public function test_instantiation(): void
    {
        $this->assertTrue(class_exists(MLMIncentiveService::class));
    }

    public function test_has_required_methods(): void
    {
        $this->assertTrue(method_exists(MLMIncentiveService::class, 'calculateMonthlyIncentive'));
        $this->assertTrue(method_exists(MLMIncentiveService::class, 'getMonthlyBusinessVolume'));
        $this->assertTrue(method_exists(MLMIncentiveService::class, 'getIncentiveSummary'));
        $this->assertTrue(method_exists(MLMIncentiveService::class, 'getMonthlyTargets'));
    }

    public function test_get_monthly_targets_returns_array(): void
    {
        $service = new MLMIncentiveService();
        $targets = $service->getMonthlyTargets();
        $this->assertIsArray($targets);
        $this->assertArrayHasKey('Associate', $targets);
        $this->assertArrayHasKey('target', $targets['Associate']);
        $this->assertArrayHasKey('reward', $targets['Associate']);
    }

    public function test_monthly_targets_has_seven_ranks(): void
    {
        $service = new MLMIncentiveService();
        $targets = $service->getMonthlyTargets();
        $this->assertCount(7, $targets);
    }

    public function test_calculate_incentive_for_nonexistent_user(): void
    {
        $service = new MLMIncentiveService();
        $result = $service->calculateMonthlyIncentive(999999);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }
}
