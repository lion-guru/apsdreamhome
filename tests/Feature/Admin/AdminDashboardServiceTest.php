<?php

namespace Tests\Feature\Admin;

use App\Services\Admin\AdminDashboardService;
use PHPUnit\Framework\TestCase;

/**
 * Admin Dashboard Service Test - APS Dream Home
 * Custom MVC testing without Laravel dependencies
 */
class AdminDashboardServiceTest extends TestCase
{
    private $dashboardService;
    
    protected function setUp(): void
    {
        $this->dashboardService = new AdminDashboardService();
    }
    
    /** @test */
    public function it_can_be_initialized()
    {
        $this->assertInstanceOf(AdminDashboardService::class, $this->dashboardService);
    }
    
    /** @test */
    public function it_can_get_dashboard_stats()
    {
        $result = $this->dashboardService->getDashboardStats();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        
        $stats = $result['data'];
        
        // Check required stats keys
        $requiredKeys = [
            'total_users',
            'active_properties',
            'total_properties',
            'total_bookings',
            'recent_bookings',
            'total_leads',
            'new_leads',
            'total_revenue',
            'monthly_revenue',
            'total_associates',
            'pending_tasks'
        ];
        
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $stats);
            $this->assertIsNumeric($stats[$key]);
        }
    }
    
    /** @test */
    public function it_can_get_recent_activities()
    {
        $result = $this->dashboardService->getRecentActivities(5);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
        $this->assertLessThanOrEqual(5, count($result['data']));
        
        // Check activity structure
        if (!empty($result['data'])) {
            $activity = $result['data'][0];
            $this->assertArrayHasKey('activity_type', $activity);
            $this->assertArrayHasKey('description', $activity);
            $this->assertArrayHasKey('activity_time', $activity);
            $this->assertArrayHasKey('reference_id', $activity);
            $this->assertArrayHasKey('entity_type', $activity);
        }
    }
    
    /** @test */
    public function it_can_get_property_analytics()
    {
        $result = $this->dashboardService->getPropertyAnalytics();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        
        $analytics = $result['data'];
        
        // Check required analytics keys
        $requiredKeys = [
            'properties_by_type',
            'properties_by_status',
            'properties_by_location',
            'properties_by_price_range',
            'monthly_property_additions'
        ];
        
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $analytics);
            $this->assertIsArray($analytics[$key]);
        }
    }
    
    /** @test */
    public function it_can_get_user_management_data()
    {
        $result = $this->dashboardService->getUserManagementData();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        
        $data = $result['data'];
        
        // Check required data keys
        $requiredKeys = [
            'users_by_role',
            'users_by_status',
            'recent_users',
            'user_registration_trends'
        ];
        
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $data);
            $this->assertIsArray($data[$key]);
        }
    }
    
    /** @test */
    public function it_can_get_lead_management_data()
    {
        $result = $this->dashboardService->getLeadManagementData();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        
        $data = $result['data'];
        
        // Check required data keys
        $requiredKeys = [
            'leads_by_status',
            'leads_by_source',
            'recent_leads',
            'lead_conversion_rates'
        ];
        
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $data);
            $this->assertIsArray($data[$key]);
        }
    }
    
    /** @test */
    public function it_can_get_booking_management_data()
    {
        $result = $this->dashboardService->getBookingManagementData();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        
        $data = $result['data'];
        
        // Check required data keys
        $requiredKeys = [
            'bookings_by_status',
            'bookings_by_type',
            'recent_bookings',
            'booking_trends'
        ];
        
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $data);
            $this->assertIsArray($data[$key]);
        }
    }
    
    /** @test */
    public function it_can_get_system_health()
    {
        $result = $this->dashboardService->getSystemHealth();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        
        $health = $result['data'];
        
        // Check health structure
        $this->assertArrayHasKey('status', $health);
        $this->assertArrayHasKey('checks', $health);
        $this->assertArrayHasKey('timestamp', $health);
        $this->assertContains($health['status'], ['healthy', 'unhealthy']);
        
        // Check required health checks
        $requiredChecks = [
            'database',
            'properties',
            'users',
            'errors',
            'uploads',
            'memory',
            'disk_space'
        ];
        
        foreach ($requiredChecks as $check) {
            $this->assertArrayHasKey($check, $health['checks']);
            $this->assertArrayHasKey('status', $health['checks'][$check]);
            $this->assertArrayHasKey('message', $health['checks'][$check]);
            $this->assertContains($health['checks'][$check]['status'], ['ok', 'warning', 'error']);
        }
    }
    
    /** @test */
    public function it_can_get_admin_menu_for_admin()
    {
        $result = $this->dashboardService->getAdminMenu('admin');
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        
        $menu = $result['data'];
        
        // Check required menu items
        $requiredItems = [
            'dashboard',
            'properties',
            'users',
            'associates',
            'leads',
            'bookings',
            'analytics',
            'system'
        ];
        
        foreach ($requiredItems as $item) {
            $this->assertArrayHasKey($item, $menu);
            $this->assertArrayHasKey('title', $menu[$item]);
            $this->assertArrayHasKey('icon', $menu[$item]);
            $this->assertArrayHasKey('url', $menu[$item]);
        }
        
        // Check system menu for admin
        $this->assertArrayHasKey('submenu', $menu['system']);
        $this->assertIsArray($menu['system']['submenu']);
    }
    
    /** @test */
    public function it_can_get_admin_menu_for_user()
    {
        $result = $this->dashboardService->getAdminMenu('user');
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        
        $menu = $result['data'];
        
        // Check that system menu is not included for non-admin
        $this->assertArrayNotHasKey('system', $menu);
        
        // Check other menu items are still present
        $requiredItems = [
            'dashboard',
            'properties',
            'users',
            'associates',
            'leads',
            'bookings',
            'analytics'
        ];
        
        foreach ($requiredItems as $item) {
            $this->assertArrayHasKey($item, $menu);
        }
    }
    
    /** @test */
    public function it_can_get_quick_stats()
    {
        $result = $this->dashboardService->getQuickStats();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        
        $stats = $result['data'];
        
        // Check required quick stats keys
        $requiredKeys = [
            'new_users_today',
            'new_properties_today',
            'new_leads_today',
            'new_bookings_today',
            'revenue_today',
            'new_users_week',
            'revenue_week'
        ];
        
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $stats);
            $this->assertIsNumeric($stats[$key]);
        }
    }
    
    /** @test */
    public function it_handles_recent_activities_limit_correctly()
    {
        $result1 = $this->dashboardService->getRecentActivities(5);
        $result2 = $this->dashboardService->getRecentActivities(10);
        
        $this->assertTrue($result1['success']);
        $this->assertTrue($result2['success']);
        
        $this->assertLessThanOrEqual(5, count($result1['data']));
        $this->assertLessThanOrEqual(10, count($result2['data']));
    }
    
    /** @test */
    public function it_returns_valid_property_analytics_structure()
    {
        $result = $this->dashboardService->getPropertyAnalytics();
        
        $this->assertTrue($result['success']);
        $analytics = $result['data'];
        
        // Check properties_by_type structure
        if (!empty($analytics['properties_by_type'])) {
            $type = $analytics['properties_by_type'][0];
            $this->assertArrayHasKey('name', $type);
            $this->assertArrayHasKey('count', $type);
        }
        
        // Check properties_by_status structure
        if (!empty($analytics['properties_by_status'])) {
            $status = $analytics['properties_by_status'][0];
            $this->assertArrayHasKey('status', $status);
            $this->assertArrayHasKey('count', $status);
        }
        
        // Check properties_by_location structure
        if (!empty($analytics['properties_by_location'])) {
            $location = $analytics['properties_by_location'][0];
            $this->assertArrayHasKey('city', $location);
            $this->assertArrayHasKey('count', $location);
        }
        
        // Check properties_by_price_range structure
        if (!empty($analytics['properties_by_price_range'])) {
            $range = $analytics['properties_by_price_range'][0];
            $this->assertArrayHasKey('price_range', $range);
            $this->assertArrayHasKey('count', $range);
        }
    }
    
    /** @test */
    public function it_returns_valid_user_management_structure()
    {
        $result = $this->dashboardService->getUserManagementData();
        
        $this->assertTrue($result['success']);
        $data = $result['data'];
        
        // Check recent_users structure
        if (!empty($data['recent_users'])) {
            $user = $data['recent_users'][0];
            $this->assertArrayHasKey('id', $user);
            $this->assertArrayHasKey('username', $user);
            $this->assertArrayHasKey('role', $user);
            $this->assertArrayHasKey('status', $user);
            $this->assertArrayHasKey('created_at', $user);
        }
        
        // Check users_by_role structure
        if (!empty($data['users_by_role'])) {
            $role = $data['users_by_role'][0];
            $this->assertArrayHasKey('role', $role);
            $this->assertArrayHasKey('count', $role);
        }
    }
    
    /** @test */
    public function it_returns_valid_lead_management_structure()
    {
        $result = $this->dashboardService->getLeadManagementData();
        
        $this->assertTrue($result['success']);
        $data = $result['data'];
        
        // Check recent_leads structure
        if (!empty($data['recent_leads'])) {
            $lead = $data['recent_leads'][0];
            $this->assertArrayHasKey('id', $lead);
            $this->assertArrayHasKey('name', $lead);
            $this->assertArrayHasKey('email', $lead);
            $this->assertArrayHasKey('source', $lead);
            $this->assertArrayHasKey('status', $lead);
            $this->assertArrayHasKey('created_at', $lead);
        }
        
        // Check leads_by_status structure
        if (!empty($data['leads_by_status'])) {
            $status = $data['leads_by_status'][0];
            $this->assertArrayHasKey('status', $status);
            $this->assertArrayHasKey('count', $status);
        }
    }
    
    /** @test */
    public function it_returns_valid_booking_management_structure()
    {
        $result = $this->dashboardService->getBookingManagementData();
        
        $this->assertTrue($result['success']);
        $data = $result['data'];
        
        // Check recent_bookings structure
        if (!empty($data['recent_bookings'])) {
            $booking = $data['recent_bookings'][0];
            $this->assertArrayHasKey('id', $booking);
            $this->assertArrayHasKey('booking_type', $booking);
            $this->assertArrayHasKey('status', $booking);
            $this->assertArrayHasKey('created_at', $booking);
            $this->assertArrayHasKey('property_title', $booking);
            $this->assertArrayHasKey('customer_name', $booking);
        }
        
        // Check bookings_by_status structure
        if (!empty($data['bookings_by_status'])) {
            $status = $data['bookings_by_status'][0];
            $this->assertArrayHasKey('status', $status);
            $this->assertArrayHasKey('count', $status);
        }
    }
    
    /** @test */
    public function it_handles_system_health_correctly()
    {
        $result = $this->dashboardService->getSystemHealth();
        
        $this->assertTrue($result['success']);
        $health = $result['data'];
        
        // Check that timestamp is recent (within last minute)
        $timestamp = strtotime($health['timestamp']);
        $now = time();
        $this->assertLessThan(60, $now - $timestamp);
        
        // Check that at least database check is ok (since we're connected)
        $this->assertEquals('ok', $health['checks']['database']['status']);
    }
    
    /** @test */
    public function it_provides_menu_structure_correctly()
    {
        $result = $this->dashboardService->getAdminMenu('admin');
        
        $this->assertTrue($result['success']);
        $menu = $result['data'];
        
        // Check dashboard menu item
        $this->assertEquals('Dashboard', $menu['dashboard']['title']);
        $this->assertEquals('dashboard', $menu['dashboard']['icon']);
        $this->assertEquals('/admin/dashboard', $menu['dashboard']['url']);
        $this->assertTrue($menu['dashboard']['active']);
        
        // Check properties submenu
        $this->assertArrayHasKey('submenu', $menu['properties']);
        $this->assertIsArray($menu['properties']['submenu']);
        $this->assertGreaterThan(0, count($menu['properties']['submenu']));
        
        // Check submenu item structure
        $submenuItem = $menu['properties']['submenu'][0];
        $this->assertArrayHasKey('title', $submenuItem);
        $this->assertArrayHasKey('url', $submenuItem);
    }
}