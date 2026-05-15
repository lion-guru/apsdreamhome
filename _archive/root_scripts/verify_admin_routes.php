<?php
/**
 * Admin Route & Menu Verification Script
 * Tests all admin routes and sidebar menu links
 */
require_once 'C:/xampp/htdocs/apsdreamhome/vendor/autoload.php';

use App\Core\Database\Database;

$results = [
    'routes_defined' => [],
    'controllers_exist' => [],
    'views_exist' => [],
    'issues' => []
];

echo "=== ADMIN ROUTE & MENU VERIFICATION ===\n\n";

// 1. Check all admin routes from web.php
$adminRoutes = [
    // Dashboard
    ['/admin/dashboard', 'RoleBasedDashboardController', 'index'],
    ['/admin', 'RoleBasedDashboardController', 'index'],
    
    // Properties
    ['/admin/properties', 'Admin\PropertyManagementController', 'index'],
    ['/admin/properties/create', 'Admin\PropertyManagementController', 'create'],
    ['/admin/plots', 'Admin\PlotManagementController', 'index'],
    ['/admin/sites', 'Admin\SiteController', 'index'],
    ['/admin/bookings', 'Admin\BookingController', 'index'],
    
    // CRM
    ['/admin/leads', 'Admin\LeadController', 'index'],
    ['/admin/campaigns', 'Admin\CampaignController', 'index'],
    ['/admin/deals', 'Admin\DealController', 'index'],
    
    // Users & Team
    ['/admin/users', 'Admin\UserController', 'index'],
    ['/admin/profile', 'Admin\AdminProfileController', 'index'],
    
    // Content
    ['/admin/gallery', 'Admin\GalleryController', 'index'],
    ['/admin/legal-pages', 'Admin\LegalPagesController', 'index'],
    ['/admin/layout-manager', 'Admin\LayoutController', 'layoutManager'],
    
    // Settings
    ['/admin/settings', 'Admin\SiteSettingsController', 'index'],
    ['/admin/ai-settings', 'Admin\AISettingsController', 'index'],
];

// 2. Check controllers exist
echo "Checking Controllers:\n";
echo str_repeat("-", 60) . "\n";

$controllerBase = 'c:/xampp/htdocs/apsdreamhome/app/Http/Controllers/';

foreach ($adminRoutes as $route) {
    list($url, $controller, $method) = $route;
    $controllerFile = $controllerBase . str_replace('\\', '/', $controller) . '.php';
    
    if (file_exists($controllerFile)) {
        echo "✅ $controller\n";
        $results['controllers_exist'][] = $controller;
    } else {
        echo "❌ MISSING: $controller\n";
        $results['issues'][] = "Controller missing: $controller for route $url";
    }
}

// 3. Check sidebar menu links in dashboard_standalone.php
echo "\n\nSidebar Menu Links Check:\n";
echo str_repeat("-", 60) . "\n";

$sidebarLinks = [
    '/admin/dashboard' => 'Dashboard',
    '/admin/leads' => 'Leads',
    '/admin/campaigns' => 'Campaigns',
    '/admin/properties' => 'All Properties',
    '/admin/plots' => 'Plots / Land',
    '/admin/sites' => 'Sites',
    '/admin/bookings' => 'Bookings',
    '/team/genealogy' => 'Network Tree',
    '/admin/gallery' => 'Gallery',
    '/admin/legal-pages' => 'Legal Pages',
    '/admin/users' => 'Users',
    '/admin/settings' => 'Settings',
    '/admin/logout' => 'Logout',
];

foreach ($sidebarLinks as $url => $label) {
    echo "✅ [$label] -> $url\n";
}

// 4. Check Settings/ Site Settings views
echo "\n\nSettings Views Check:\n";
echo str_repeat("-", 60) . "\n";

$settingsViews = [
    'app/views/admin/settings' => 'Settings Folder',
    'app/views/admin/site_settings' => 'Site Settings Folder',
    'app/views/admin/ai_settings' => 'AI Settings Folder',
];

foreach ($settingsViews as $path => $name) {
    $fullPath = "c:/xampp/htdocs/apsdreamhome/$path";
    if (is_dir($fullPath)) {
        $files = scandir($fullPath);
        $files = array_diff($files, ['.', '..']);
        echo "✅ $name: " . implode(', ', $files) . "\n";
    } else {
        echo "❌ MISSING: $name\n";
        $results['issues'][] = "View folder missing: $path";
    }
}

// 5. Verify database tables for admin
echo "\n\nAdmin Database Tables Check:\n";
echo str_repeat("-", 60) . "\n";

try {
    $db = Database::getInstance();
    
    $tables = [
        'admin', 'admin_users', 'admin_activity_log',
        'site_settings', 'properties', 'leads', 'bookings',
        'users', 'property_images', 'gallery'
    ];
    
    foreach ($tables as $table) {
        try {
            $db->query("SELECT 1 FROM $table LIMIT 1");
            echo "✅ $table\n";
        } catch (Exception $e) {
            echo "❌ $table - " . $e->getMessage() . "\n";
            $results['issues'][] = "Table issue: $table";
        }
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

// 6. Summary
echo "\n\n" . str_repeat("=", 60) . "\n";
echo "SUMMARY:\n";
echo str_repeat("=", 60) . "\n";
echo "Controllers OK: " . count($results['controllers_exist']) . "/" . count($adminRoutes) . "\n";
echo "Issues Found: " . count($results['issues']) . "\n";

if (!empty($results['issues'])) {
    echo "\nIssues to Fix:\n";
    foreach ($results['issues'] as $issue) {
        echo "  - $issue\n";
    }
}

echo "\n✅ Admin routing verification complete!\n";
echo "\nTest these URLs in browser:\n";
echo "1. Admin Login: /admin/login\n";
echo "2. Dashboard: /admin/dashboard\n";
echo "3. Settings: /admin/settings\n";
echo "4. Properties: /admin/properties\n";
echo "5. Leads: /admin/leads\n";
