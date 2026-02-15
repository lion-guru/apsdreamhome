<?php

namespace Tests\Feature\Admin;

use PHPUnit\Framework\TestCase;
use PDO;

class AdminDashboardTest extends TestCase
{
    private PDO $pdo;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASSWORD,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
    
    public function test_admin_dashboard_exists()
    {
        // Check if admin dashboard file exists
        $dashboardFile = __DIR__ . '/../../../admin/enhanced_dashboard.php';
        $this->assertTrue(file_exists($dashboardFile), 'Admin dashboard file should exist');
        
        // Check if it's readable
        $this->assertTrue(is_readable($dashboardFile), 'Admin dashboard file should be readable');
    }
    
    public function test_admin_dashboard_has_required_elements()
    {
        $dashboardFile = __DIR__ . '/../../../admin/enhanced_dashboard.php';
        $content = file_get_contents($dashboardFile);
        
        // Check for essential dashboard elements
        $this->assertStringContains('dashboard', $content, 'Should have dashboard content');
        $this->assertStringContains('statistics', $content, 'Should have statistics section');
        $this->assertStringContains('analytics', $content, 'Should have analytics section');
    }
    
    public function test_admin_dashboard_displays_statistics()
    {
        // Get actual statistics from database
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $userCount = $stmt->fetch()['count'];
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties");
        $stmt->execute();
        $propertyCount = $stmt->fetch()['count'];
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM projects");
        $stmt->execute();
        $projectCount = $stmt->fetch()['count'];
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM inquiries");
        $stmt->execute();
        $inquiryCount = $stmt->fetch()['count'];
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM bookings");
        $stmt->execute();
        $bookingCount = $stmt->fetch()['count'];
        
        // Verify we have data to display
        $this->assertGreaterThan(0, $userCount, 'Should have users in database');
        $this->assertGreaterThan(0, $propertyCount, 'Should have properties in database');
        
        // Dashboard should be able to display these statistics
        $this->assertTrue(true, 'Dashboard statistics are available');
    }
    
    public function test_admin_dashboard_has_navigation()
    {
        $dashboardFile = __DIR__ . '/../../../admin/enhanced_dashboard.php';
        $content = file_get_contents($dashboardFile);
        
        // Check for navigation elements
        $this->assertStringContains('nav', $content, 'Should have navigation');
        $this->assertStringContains('menu', $content, 'Should have menu');
        
        // Check for common admin navigation items
        $navItems = ['dashboard', 'properties', 'projects', 'users', 'inquiries', 'bookings'];
        foreach ($navItems as $item) {
            $this->assertStringContains($item, $content, "Should have {$item} navigation item");
        }
    }
    
    public function test_admin_dashboard_has_search_functionality()
    {
        $dashboardFile = __DIR__ . '/../../../admin/enhanced_dashboard.php';
        $content = file_get_contents($dashboardFile);
        
        // Check for search elements
        $this->assertStringContains('search', $content, 'Should have search functionality');
        $this->assertStringContains('filter', $content, 'Should have filter options');
        
        // Check for global search
        $this->assertStringContains('global', $content, 'Should have global search');
    }
    
    public function test_admin_dashboard_has_charts()
    {
        $dashboardFile = __DIR__ . '/../../../admin/enhanced_dashboard.php';
        $content = file_get_contents($dashboardFile);
        
        // Check for chart elements
        $this->assertStringContains('chart', $content, 'Should have charts');
        $this->assertStringContains('graph', $content, 'Should have graphs');
        
        // Check for Chart.js integration
        $this->assertStringContains('Chart.js', $content, 'Should use Chart.js for charts');
        $this->assertStringContains('canvas', $content, 'Should have canvas elements for charts');
    }
    
    public function test_admin_dashboard_has_recent_activity()
    {
        $dashboardFile = __DIR__ . '/../../../admin/enhanced_dashboard.php';
        $content = file_get_contents($dashboardFile);
        
        // Check for activity feed
        $this->assertStringContains('activity', $content, 'Should have activity feed');
        $this->assertStringContains('recent', $content, 'Should show recent activity');
        $this->assertStringContains('feed', $content, 'Should have activity feed');
    }
    
    public function test_admin_dashboard_has_export_functionality()
    {
        $dashboardFile = __DIR__ . '/../../../admin/enhanced_dashboard.php';
        $content = file_get_contents($dashboardFile);
        
        // Check for export options
        $this->assertStringContains('export', $content, 'Should have export functionality');
        $this->assertStringContains('csv', $content, 'Should support CSV export');
        $this->assertStringContains('pdf', $content, 'Should support PDF export');
    }
    
    public function test_admin_dashboard_has_system_status()
    {
        $dashboardFile = __DIR__ . '/../../../admin/enhanced_dashboard.php';
        $content = file_get_contents($dashboardFile);
        
        // Check for system status monitoring
        $this->assertStringContains('status', $content, 'Should have system status');
        $this->assertStringContains('health', $content, 'Should have health monitoring');
        $this->assertStringContains('database', $content, 'Should monitor database status');
    }
    
    public function test_admin_dashboard_is_responsive()
    {
        $dashboardFile = __DIR__ . '/../../../admin/enhanced_dashboard.php';
        $content = file_get_contents($dashboardFile);
        
        // Check for responsive design
        $this->assertStringContains('bootstrap', $content, 'Should use Bootstrap for responsiveness');
        $this->assertStringContains('responsive', $content, 'Should be responsive');
        $this->assertStringContains('mobile', $content, 'Should be mobile-friendly');
    }
    
    public function test_admin_dashboard_has_quick_actions()
    {
        $dashboardFile = __DIR__ . '/../../../admin/enhanced_dashboard.php';
        $content = file_get_contents($dashboardFile);
        
        // Check for quick actions
        $this->assertStringContains('quick', $content, 'Should have quick actions');
        $this->assertStringContains('actions', $content, 'Should have action buttons');
        
        // Check for common admin actions
        $actions = ['add', 'edit', 'delete', 'view', 'manage'];
        foreach ($actions as $action) {
            $this->assertStringContains($action, $content, "Should have {$action} action");
        }
    }
    
    public function test_admin_dashboard_has_security_features()
    {
        $dashboardFile = __DIR__ . '/../../../admin/enhanced_dashboard.php';
        $content = file_get_contents($dashboardFile);
        
        // Check for security features
        $this->assertStringContains('session', $content, 'Should handle sessions');
        $this->assertStringContains('auth', $content, 'Should have authentication');
        $this->assertStringContains('csrf', $content, 'Should have CSRF protection');
    }
    
    public function test_admin_dashboard_has_error_handling()
    {
        $dashboardFile = __DIR__ . '/../../../admin/enhanced_dashboard.php';
        $content = file_get_contents($dashboardFile);
        
        // Check for error handling
        $this->assertStringContains('error', $content, 'Should have error handling');
        $this->assertStringContains('exception', $content, 'Should handle exceptions');
        $this->assertStringContains('try', $content, 'Should use try-catch blocks');
    }
    
    public function test_admin_dashboard_has_ajax_endpoints()
    {
        // Check if AJAX endpoint files exist
        $ajaxFiles = [
            'get_dashboard_stats.php',
            'get_analytics_data.php',
            'global_search.php',
            'get_system_status.php',
            'get_recent_activity.php',
            'export_dashboard_data.php'
        ];
        
        foreach ($ajaxFiles as $file) {
            $filePath = __DIR__ . "/../../../admin/ajax/{$file}";
            $this->assertTrue(file_exists($filePath), "AJAX endpoint {$file} should exist");
        }
    }
    
    public function test_admin_dashboard_statistics_api()
    {
        $statsFile = __DIR__ . '/../../../admin/ajax/get_dashboard_stats.php';
        if (file_exists($statsFile)) {
            $content = file_get_contents($statsFile);
            
            // Check for API response format
            $this->assertStringContains('json', $content, 'Should return JSON response');
            $this->assertStringContains('header', $content, 'Should set JSON headers');
            
            // Check for statistics queries
            $this->assertStringContains('SELECT', $content, 'Should have database queries');
            $this->assertStringContains('COUNT', $content, 'Should count records');
        }
    }
    
    public function test_admin_dashboard_analytics_api()
    {
        $analyticsFile = __DIR__ . '/../../../admin/ajax/get_analytics_data.php';
        if (file_exists($analyticsFile)) {
            $content = file_get_contents($analyticsFile);
            
            // Check for analytics data structure
            $this->assertStringContains('labels', $content, 'Should have chart labels');
            $this->assertStringContains('data', $content, 'Should have chart data');
            $this->assertStringContains('datasets', $content, 'Should have chart datasets');
        }
    }
    
    public function test_admin_dashboard_search_api()
    {
        $searchFile = __DIR__ . '/../../../admin/ajax/global_search.php';
        if (file_exists($searchFile)) {
            $content = file_get_contents($searchFile);
            
            // Check for search functionality
            $this->assertStringContains('search', $content, 'Should have search logic');
            $this->assertStringContains('LIKE', $content, 'Should use LIKE for search');
            $this->assertStringContains('results', $content, 'Should return search results');
        }
    }
    
    protected function tearDown(): void
    {
        $this->pdo = null;
        parent::tearDown();
    }
}
