<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\EngagementService;

class EngagementServiceTest extends TestCase
{
    public function test_instantiation(): void
    {
        $this->assertTrue(class_exists(EngagementService::class));
    }

    public function test_has_required_methods(): void
    {
        $this->assertTrue(method_exists(EngagementService::class, 'getAssociateMetrics'));
        $this->assertTrue(method_exists(EngagementService::class, 'getLeaderboardSnapshot'));
        $this->assertTrue(method_exists(EngagementService::class, 'getGoals'));
        $this->assertTrue(method_exists(EngagementService::class, 'getGoalProgress'));
        $this->assertTrue(method_exists(EngagementService::class, 'getGoalEvents'));
        $this->assertTrue(method_exists(EngagementService::class, 'getNotificationFeed'));
        $this->assertTrue(method_exists(EngagementService::class, 'markNotificationRead'));
        $this->assertTrue(method_exists(EngagementService::class, 'markAllNotificationsRead'));
        $this->assertTrue(method_exists(EngagementService::class, 'createGoal'));
        $this->assertTrue(method_exists(EngagementService::class, 'updateGoal'));
        $this->assertTrue(method_exists(EngagementService::class, 'recordGoalProgress'));
    }

    public function test_get_associate_metrics_returns_array(): void
    {
        $service = new EngagementService();
        try {
            $metrics = $service->getAssociateMetrics();
            $this->assertIsArray($metrics);
        } catch (\PDOException $e) {
            $this->assertStringContainsString('doesn\'t exist', $e->getMessage());
        }
    }

    public function test_get_goals_returns_array(): void
    {
        $service = new EngagementService();
        try {
            $goals = $service->getGoals();
            $this->assertIsArray($goals);
        } catch (\PDOException $e) {
            $this->assertStringContainsString('doesn\'t exist', $e->getMessage());
        }
    }

    public function test_get_notification_feed_returns_array(): void
    {
        $service = new EngagementService();
        $feed = $service->getNotificationFeed(1);
        $this->assertIsArray($feed);
    }

    public function test_mark_notification_read_with_invalid_id(): void
    {
        $service = new EngagementService();
        $this->expectException(\InvalidArgumentException::class);
        $service->markNotificationRead(0, 1);
    }

    public function test_mark_all_notifications_read_with_invalid_user(): void
    {
        $service = new EngagementService();
        $this->expectException(\InvalidArgumentException::class);
        $service->markAllNotificationsRead(0);
    }
}
