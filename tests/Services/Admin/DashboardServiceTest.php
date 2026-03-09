<?php

namespace Tests\Services\Admin;

use PHPUnit\Framework\TestCase;
use App\Services\Admin\DashboardService;
use App\Core\Database;
use Psr\Log\LoggerInterface;

class DashboardServiceTest extends TestCase
{
    private DashboardService $dashboardService;
    private Database $db;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->db = $this->createMock(Database::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->dashboardService = new DashboardService($this->db, $this->logger);
    }

    public function testIsAdminSuccess(): void
    {
        $userId = 1;
        $userRole = 'admin';

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(['role' => $userRole]);

        $result = $this->dashboardService->isAdmin($userId);

        $this->assertTrue($result);
    }

    public function testIsAdminNotAdmin(): void
    {
        $userId = 2;
        $userRole = 'user';

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(['role' => $userRole]);

        $result = $this->dashboardService->isAdmin($userId);

        $this->assertFalse($result);
    }

    public function testIsAdminUserNotFound(): void
    {
        $userId = 999;

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $result = $this->dashboardService->isAdmin($userId);

        $this->assertFalse($result);
    }

    public function testIsAdminDatabaseError(): void
    {
        $userId = 1;

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $result = $this->dashboardService->isAdmin($userId);

        $this->assertFalse($result);
    }

    public function testGetDashboardStatsSuccess(): void
    {
        // Mock all the database calls needed for stats
        $this->db->expects($this->exactly(20))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(
                ['total' => 100], // users total
                ['active' => 80],  // users active
                ['new_users' => 10], // users new this month
                ['total' => 50],  // properties total
                ['available' => 30], // properties available
                ['sold' => 20],    // properties sold
                ['avg_price' => 500000], // properties avg price
                ['total' => 200], // leads total
                ['new_leads' => 25], // leads new this month
                ['converted' => 50], // leads converted
                ['total' => 1000000], // revenue total
                ['monthly' => 100000], // revenue this month
                ['last_month' => 90000], // revenue last month
                ['today' => 15],    // activities today
                ['this_week' => 75], // activities this week
                ['size_mb' => 250],  // database size
                ['tables' => 25],    // total tables
                ['total' => 1000]    // total requests for error rate
            );

        $this->db->expects($this->exactly(6))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['role' => 'admin', 'count' => 5], ['role' => 'user', 'count' => 95]], // users by role
                [['property_type' => 'apartment', 'count' => 30], ['property_type' => 'house', 'count' => 20]], // properties by type
                [['status' => 'new', 'count' => 50], ['status' => 'contacted', 'count' => 100], ['status' => 'converted', 'count' => 50]], // leads by status
                [['payment_source' => 'online', 'total' => 600000], ['payment_source' => 'offline', 'total' => 400000]], // revenue by source
                [['activity_type' => 'login', 'count' => 30], ['activity_type' => 'view', 'count' => 45]], // activities by type
                [['level' => 'ERROR', 'total' => 10]] // error logs
            );

        $stats = $this->dashboardService->getDashboardStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('users', $stats);
        $this->assertArrayHasKey('properties', $stats);
        $this->assertArrayHasKey('leads', $stats);
        $this->assertArrayHasKey('revenue', $stats);
        $this->assertArrayHasKey('activities', $stats);
        $this->assertArrayHasKey('system', $stats);
        $this->assertArrayHasKey('performance', $stats);

        $this->assertEquals(100, $stats['users']['total']);
        $this->assertEquals(50, $stats['properties']['total']);
        $this->assertEquals(200, $stats['leads']['total']);
        $this->assertEquals(1000000, $stats['revenue']['total']);
    }

    public function testGetDashboardStatsDatabaseError(): void
    {
        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $stats = $this->dashboardService->getDashboardStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('users', $stats);
        $this->assertArrayHasKey('properties', $stats);
        $this->assertArrayHasKey('leads', $stats);
        $this->assertArrayHasKey('revenue', $stats);
        $this->assertArrayHasKey('activities', $stats);
        $this->assertArrayHasKey('system', $stats);
        $this->assertArrayHasKey('performance', $stats);

        // Should return default stats
        $this->assertEquals(0, $stats['users']['total']);
        $this->assertEquals(0, $stats['properties']['total']);
    }

    public function testGetRecentActivities(): void
    {
        $activities = [
            ['id' => 1, 'activity_type' => 'login', 'description' => 'User logged in', 'user_id' => 1, 'created_at' => '2026-03-08 12:00:00'],
            ['id' => 2, 'activity_type' => 'view', 'description' => 'User viewed property', 'user_id' => 2, 'created_at' => '2026-03-08 11:30:00']
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($activities);

        $result = $this->dashboardService->getRecentActivities(10);

        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('login', $result[0]['type']);
        $this->assertEquals('User logged in', $result[0]['description']);
    }

    public function testGetRecentActivitiesDatabaseError(): void
    {
        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $result = $this->dashboardService->getRecentActivities(10);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetSystemHealth(): void
    {
        // Mock database check
        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(['1' => 1]);

        $result = $this->dashboardService->getSystemHealth();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('overall', $result);
        $this->assertArrayHasKey('database', $result);
        $this->assertArrayHasKey('storage', $result);
        $this->assertArrayHasKey('memory', $result);
        $this->assertArrayHasKey('services', $result);

        $this->assertEquals('healthy', $result['database']);
        $this->assertArrayHasKey('database', $result['services']);
    }

    public function testGetSystemHealthDatabaseUnhealthy(): void
    {
        // Mock database failure
        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willThrowException(new \Exception('Database error'));

        $result = $this->dashboardService->getSystemHealth();

        $this->assertIsArray($result);
        $this->assertEquals('unhealthy', $result['database']);
        $this->assertEquals('unhealthy', $result['overall']);
    }

    public function testGetAdminMenuForAdmin(): void
    {
        $userId = 1;

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(['role' => 'admin']);

        $menu = $this->dashboardService->getAdminMenu($userId);

        $this->assertIsArray($menu);
        $this->assertArrayHasKey('dashboard', $menu);
        $this->assertArrayHasKey('users', $menu);
        $this->assertArrayHasKey('properties', $menu);
        $this->assertArrayHasKey('leads', $menu);
        $this->assertArrayHasKey('reports', $menu);
        $this->assertArrayHasKey('settings', $menu);

        $this->assertEquals('Dashboard', $menu['dashboard']['title']);
        $this->assertEquals('/admin/dashboard', $menu['dashboard']['url']);
    }

    public function testGetAdminMenuForNonAdmin(): void
    {
        $userId = 2;

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(['role' => 'user']);

        $menu = $this->dashboardService->getAdminMenu($userId);

        $this->assertIsArray($menu);
        $this->assertEmpty($menu);
    }

    public function testLogAdminActivitySuccess(): void
    {
        $userId = 1;
        $action = 'test_action';
        $data = ['test' => 'data'];

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $this->logger->expects($this->once())
            ->method('info');

        $result = $this->dashboardService->logAdminActivity($userId, $action, $data);

        $this->assertTrue($result);
    }

    public function testLogAdminActivityFailure(): void
    {
        $userId = 1;
        $action = 'test_action';

        $this->db->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $result = $this->dashboardService->logAdminActivity($userId, $action);

        $this->assertFalse($result);
    }

    public function testGetAdminActivityLogs(): void
    {
        $logs = [
            ['id' => 1, 'user_id' => 1, 'action' => 'login', 'data' => '{"ip": "127.0.0.1"}', 'created_at' => '2026-03-08 12:00:00'],
            ['id' => 2, 'user_id' => 2, 'action' => 'logout', 'data' => '{"ip": "127.0.0.1"}', 'created_at' => '2026-03-08 11:30:00']
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($logs);

        $result = $this->dashboardService->getAdminActivityLogs(50);

        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('login', $result[0]['action']);
        $this->assertIsArray($result[0]['data']);
        $this->assertEquals('127.0.0.1', $result[0]['data']['ip']);
    }

    public function testGetAdminActivityLogsWithFilters(): void
    {
        $filters = ['user_id' => 1, 'action' => 'login'];
        $logs = [
            ['id' => 1, 'user_id' => 1, 'action' => 'login', 'data' => '{}', 'created_at' => '2026-03-08 12:00:00']
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($logs);

        $result = $this->dashboardService->getAdminActivityLogs(50, $filters);

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['user_id']);
        $this->assertEquals('login', $result[0]['action']);
    }

    public function testGetAdminActivityLogsDatabaseError(): void
    {
        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $result = $this->dashboardService->getAdminActivityLogs(50);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testParseMemoryLimit(): void
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->dashboardService);
        $method = $reflection->getMethod('parseMemoryLimit');
        $method->setAccessible(true);

        $this->assertEquals(1024 * 1024 * 1024, $method->invoke($this->dashboardService, '1G'));
        $this->assertEquals(1024 * 1024, $method->invoke($this->dashboardService, '1M'));
        $this->assertEquals(1024, $method->invoke($this->dashboardService, '1K'));
        $this->assertEquals(256, $method->invoke($this->dashboardService, '256'));
    }

    public function testDefaultStats(): void
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->dashboardService);
        $method = $reflection->getMethod('getDefaultStats');
        $method->setAccessible(true);

        $stats = $method->invoke($this->dashboardService);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('users', $stats);
        $this->assertArrayHasKey('properties', $stats);
        $this->assertArrayHasKey('leads', $stats);
        $this->assertArrayHasKey('revenue', $stats);
        $this->assertArrayHasKey('activities', $stats);
        $this->assertArrayHasKey('system', $stats);
        $this->assertArrayHasKey('performance', $stats);

        $this->assertEquals(0, $stats['users']['total']);
        $this->assertEquals(0, $stats['properties']['total']);
        $this->assertEquals(0, $stats['leads']['total']);
        $this->assertEquals(0, $stats['revenue']['total']);
    }

    public function testCheckServiceHealth(): void
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->dashboardService);
        $method = $reflection->getMethod('checkServiceHealth');
        $method->setAccessible(true);

        // Test database service
        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(['1' => 1]);

        $result = $method->invoke($this->dashboardService, 'database');
        $this->assertEquals('healthy', $result);
    }

    public function testCheckServiceHealthDatabaseFailure(): void
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->dashboardService);
        $method = $reflection->getMethod('checkServiceHealth');
        $method->setAccessible(true);

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willThrowException(new \Exception('Database error'));

        $result = $method->invoke($this->dashboardService, 'database');
        $this->assertEquals('unhealthy', $result);
    }

    public function testCheckServiceHealthUnknownService(): void
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->dashboardService);
        $method = $reflection->getMethod('checkServiceHealth');
        $method->setAccessible(true);

        $result = $method->invoke($this->dashboardService, 'unknown_service');
        $this->assertEquals('unknown', $result);
    }
}
