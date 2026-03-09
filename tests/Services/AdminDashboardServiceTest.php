<?php

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use App\Services\Admin\AdminDashboardService;
use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Admin Dashboard Service Test
 * Tests all admin dashboard operations
 */
class AdminDashboardServiceTest extends TestCase
{
    private $adminService;
    private $mockDb;
    private $mockLogger;

    protected function setUp(): void
    {
        $this->mockDb = $this->createMock(Database::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        $this->adminService = new AdminDashboardService($this->mockDb, $this->mockLogger);
    }

    /**
     * Test get dashboard statistics
     */
    public function testGetDashboardStats()
    {
        // Mock database responses
        $this->mockDb->expects($this->exactly(4))
                   ->method('fetchOne')
                   ->willReturnOnConsecutiveCalls(150, 85, 42, 28);

        $stats = $this->adminService->getDashboardStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_users', $stats);
        $this->assertArrayHasKey('total_properties', $stats);
        $this->assertArrayHasKey('total_bookings', $stats);
        $this->assertArrayHasKey('total_leads', $stats);
        $this->assertEquals(150, $stats['total_users']);
        $this->assertEquals(85, $stats['total_properties']);
    }

    /**
     * Test get recent activities
     */
    public function testGetRecentActivities()
    {
        $mockBookings = [
            [
                'id' => 1,
                'customer' => 'John Doe',
                'amount' => 500000,
                'status' => 'confirmed',
                'booking_date' => '2026-03-07 10:30:00'
            ]
        ];

        $mockProperties = [
            [
                'title' => 'Luxury Apartment',
                'created_at' => '2026-03-07 09:15:00'
            ]
        ];

        $this->mockDb->expects($this->exactly(2))
                   ->method('fetchAll')
                   ->willReturnOnConsecutiveCalls($mockBookings, $mockProperties);

        $activities = $this->adminService->getRecentActivities();

        $this->assertIsArray($activities);
        $this->assertNotEmpty($activities);
        $this->assertEquals('booking', $activities[0]['type']);
        $this->assertStringContains('New Booking', $activities[0]['message']);
    }

    /**
     * Test get property analytics
     */
    public function testGetPropertyAnalytics()
    {
        $mockAnalytics = [
            'by_status' => [
                ['status' => 'active', 'count' => 50],
                ['status' => 'sold', 'count' => 25]
            ],
            'by_type' => [
                ['type' => 'apartment', 'count' => 40],
                ['type' => 'house', 'count' => 35]
            ]
        ];

        $this->mockDb->expects($this->exactly(3))
                   ->method('fetchAll')
                   ->willReturn($mockAnalytics);

        $analytics = $this->adminService->getPropertyAnalytics();

        $this->assertIsArray($analytics);
        $this->assertArrayHasKey('by_status', $analytics);
        $this->assertArrayHasKey('by_type', $analytics);
        $this->assertEquals(50, $analytics['by_status'][0]['count']);
    }

    /**
     * Test get user management data
     */
    public function testGetUserManagementData()
    {
        $mockUserData = [
            'by_role' => [
                ['role' => 'customer', 'count' => 100],
                ['role' => 'agent', 'count' => 15]
            ],
            'recent_registrations' => [
                [
                    'id' => 1,
                    'name' => 'Jane Smith',
                    'email' => 'jane@example.com',
                    'role' => 'customer',
                    'created_at' => '2026-03-07 08:00:00'
                ]
            ]
        ];

        $this->mockDb->expects($this->exactly(3))
                   ->method('fetchAll')
                   ->willReturn($mockUserData);

        $this->mockDb->expects($this->once())
                   ->method('fetchOne')
                   ->willReturn(25);

        $userData = $this->adminService->getUserManagementData();

        $this->assertIsArray($userData);
        $this->assertArrayHasKey('by_role', $userData);
        $this->assertArrayHasKey('recent_registrations', $userData);
        $this->assertArrayHasKey('active_this_month', $userData);
        $this->assertEquals(25, $userData['active_this_month']);
    }

    /**
     * Test get lead management data
     */
    public function testGetLeadManagementData()
    {
        $mockLeadData = [
            'by_status' => [
                ['status' => 'new', 'count' => 30],
                ['status' => 'converted', 'count' => 15]
            ]
        ];

        $this->mockDb->expects($this->exactly(3))
                   ->method('fetchOne')
                   ->willReturnOnConsecutiveCalls(45, 15);

        $this->mockDb->expects($this->once())
                   ->method('fetchAll')
                   ->willReturn($mockLeadData);

        $leadData = $this->adminService->getLeadManagementData();

        $this->assertIsArray($leadData);
        $this->assertArrayHasKey('by_status', $leadData);
        $this->assertArrayHasKey('conversion_rate', $leadData);
        $this->assertEquals(33.33, round($leadData['conversion_rate'], 2));
    }

    /**
     * Test get booking management data
     */
    public function testGetBookingManagementData()
    {
        $mockBookingData = [
            'by_status' => [
                ['status' => 'confirmed', 'count' => 20],
                ['status' => 'pending', 'count' => 10]
            ],
            'recent_bookings' => [
                [
                    'id' => 1,
                    'customer' => 'Bob Wilson',
                    'property' => 'Modern Villa',
                    'amount' => 750000,
                    'status' => 'confirmed',
                    'booking_date' => '2026-03-07 11:45:00'
                ]
            ]
        ];

        $this->mockDb->expects($this->exactly(3))
                   ->method('fetchAll')
                   ->willReturn($mockBookingData);

        $this->mockDb->expects($this->once())
                   ->method('fetchOne')
                   ->willReturn(2500000);

        $bookingData = $this->adminService->getBookingManagementData();

        $this->assertIsArray($bookingData);
        $this->assertArrayHasKey('by_status', $bookingData);
        $this->assertArrayHasKey('recent_bookings', $bookingData);
        $this->assertArrayHasKey('revenue_this_month', $bookingData);
        $this->assertEquals('₹25.0L', $bookingData['revenue_this_month']);
    }

    /**
     * Test get system health status
     */
    public function testGetSystemHealthStatus()
    {
        $this->mockDb->expects($this->once())
                   ->method('getConnection')
                   ->willReturn(true);

        $this->mockDb->expects($this->once())
                   ->method('fetchOne')
                   ->willReturn('2026-03-07 06:00:00');

        $healthStatus = $this->adminService->getSystemHealthStatus();

        $this->assertIsArray($healthStatus);
        $this->assertArrayHasKey('database', $healthStatus);
        $this->assertArrayHasKey('disk_space', $healthStatus);
        $this->assertArrayHasKey('memory', $healthStatus);
        $this->assertArrayHasKey('last_backup', $healthStatus);
        $this->assertArrayHasKey('overall', $healthStatus);
        $this->assertEquals('healthy', $healthStatus['database']);
        $this->assertEquals('healthy', $healthStatus['overall']);
    }

    /**
     * Test error handling in get dashboard stats
     */
    public function testGetDashboardStatsErrorHandling()
    {
        $this->mockDb->expects($this->once())
                   ->method('fetchOne')
                   ->willThrowException(new \Exception('Database error'));

        $this->mockLogger->expects($this->once())
                        ->method('error')
                        ->with($this->stringContains('Failed to get dashboard stats'));

        $stats = $this->adminService->getDashboardStats();

        $this->assertIsArray($stats);
        $this->assertEmpty($stats);
    }

    /**
     * Test error handling in get recent activities
     */
    public function testGetRecentActivitiesErrorHandling()
    {
        $this->mockDb->expects($this->once())
                   ->method('fetchAll')
                   ->willThrowException(new \Exception('Database error'));

        $this->mockLogger->expects($this->once())
                        ->method('error')
                        ->with($this->stringContains('Failed to get recent activities'));

        $activities = $this->adminService->getRecentActivities();

        $this->assertIsArray($activities);
        $this->assertEmpty($activities);
    }
}
