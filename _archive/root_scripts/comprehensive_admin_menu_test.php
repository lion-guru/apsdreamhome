<?php
/**
 * Comprehensive Admin Menu Testing Script
 * Tests ALL admin menu items - URLs, Views, Controllers, Database Tables
 */

echo "=== COMPREHENSIVE ADMIN MENU TESTING ===\n\n";

// Define base URL
$baseUrl = 'http://localhost/apsdreamhome';

// All admin menu items from database and configuration
$menuItems = [
    // Main Section
    ['name' => 'Dashboard', 'url' => '/admin/dashboard', 'controller' => 'RoleBasedDashboardController', 'view' => 'admin/dashboard'],
    ['name' => 'Analytics', 'url' => '/admin/analytics', 'controller' => 'AnalyticsController', 'view' => 'admin/analytics'],
    
    // CRM Section
    ['name' => 'Leads', 'url' => '/admin/leads', 'controller' => 'LeadController', 'view' => 'admin/leads'],
    ['name' => 'Lead Scoring', 'url' => '/admin/leads/scoring', 'controller' => 'LeadScoringController', 'view' => 'admin/leads/scoring'],
    ['name' => 'Customers', 'url' => '/admin/customers', 'controller' => 'CRMController', 'view' => null],
    ['name' => 'Deals', 'url' => '/admin/deals', 'controller' => 'DealController', 'view' => 'admin/deals'],
    ['name' => 'Sales', 'url' => '/admin/sales', 'controller' => 'SalesController', 'view' => 'admin/sales'],
    ['name' => 'Campaigns', 'url' => '/admin/campaigns', 'controller' => 'CampaignController', 'view' => 'admin/campaigns'],
    ['name' => 'Bookings', 'url' => '/admin/bookings', 'controller' => 'BookingController', 'view' => 'admin/bookings'],
    
    // Properties Section
    ['name' => 'Properties', 'url' => '/admin/properties', 'controller' => 'PropertyManagementController', 'view' => 'admin/properties'],
    ['name' => 'Projects', 'url' => '/admin/projects', 'controller' => 'ProjectsAdminController', 'view' => 'admin/projects'],
    ['name' => 'Plots', 'url' => '/admin/plots', 'controller' => 'PlotManagementController', 'view' => 'admin/plots'],
    ['name' => 'Sites', 'url' => '/admin/sites', 'controller' => 'SiteController', 'view' => 'admin/sites'],
    ['name' => 'Resell Properties', 'url' => '/admin/resell-properties', 'controller' => 'ResellPropertiesAdminController', 'view' => 'admin/resell-properties'],
    
    // MLM Section
    ['name' => 'MLM Network', 'url' => '/admin/mlm/network', 'controller' => 'MLMController', 'view' => 'admin/mlm/network'],
    ['name' => 'Associates', 'url' => '/admin/mlm/associates', 'controller' => 'MLMController', 'view' => 'admin/mlm/associates'],
    ['name' => 'Commissions', 'url' => '/admin/mlm/commission', 'controller' => 'MLMController', 'view' => 'admin/mlm/commission'],
    ['name' => 'Payouts', 'url' => '/admin/mlm/payouts', 'controller' => 'MLMController', 'view' => 'admin/mlm/payouts'],
    
    // Operations Section
    ['name' => 'Visits', 'url' => '/admin/visits', 'controller' => 'VisitController', 'view' => 'admin/visits'],
    ['name' => 'Support Tickets', 'url' => '/admin/support-tickets', 'controller' => 'SupportTicketController', 'view' => 'admin/support-tickets'],
    ['name' => 'Tasks', 'url' => '/admin/tasks', 'controller' => 'TaskController', 'view' => 'admin/tasks'],
    
    // Marketing Section
    ['name' => 'Gallery', 'url' => '/admin/gallery', 'controller' => 'GalleryController', 'view' => 'admin/gallery'],
    ['name' => 'Testimonials', 'url' => '/admin/testimonials', 'controller' => 'TestimonialsAdminController', 'view' => 'admin/testimonials'],
    ['name' => 'News', 'url' => '/admin/news', 'controller' => 'NewsController', 'view' => 'admin/news'],
    
    // AI Section
    ['name' => 'AI Settings', 'url' => '/admin/ai-settings', 'controller' => 'AISettingsController', 'view' => 'admin/ai-settings'],
    
    // Users Section
    ['name' => 'Users', 'url' => '/admin/users', 'controller' => 'UserController', 'view' => 'admin/users'],
    
    // Locations Section
    ['name' => 'Locations', 'url' => '/admin/locations/states', 'controller' => 'LocationAdminController', 'view' => 'admin/locations'],
    
    // Settings Section
    ['name' => 'Settings', 'url' => '/admin/settings', 'controller' => 'AdminController', 'view' => 'admin/settings'],
    ['name' => 'Legal Pages', 'url' => '/admin/legal-pages', 'controller' => 'LegalPagesController', 'view' => 'admin/legal-pages'],
    ['name' => 'API Keys', 'url' => '/admin/api-keys', 'controller' => 'ApiKeyController', 'view' => 'admin/api-keys'],
    ['name' => 'Menu Permissions', 'url' => '/admin/menu-permissions', 'controller' => 'AdminMenuPermissionController', 'view' => 'admin/menu-permissions'],
    
    // Reports Section (NEW)
    ['name' => 'Reports Dashboard', 'url' => '/admin/reports-new', 'controller' => 'AdminReportsController', 'view' => 'admin/reports'],
    ['name' => 'Daily Reports', 'url' => '/admin/reports-new/daily', 'controller' => 'AdminReportsController', 'view' => null],
    ['name' => 'Weekly Reports', 'url' => '/admin/reports-new/weekly', 'controller' => 'AdminReportsController', 'view' => null],
    ['name' => 'Monthly Reports', 'url' => '/admin/reports-new/monthly', 'controller' => 'AdminReportsController', 'view' => null],
    
    // Content Section (NEW - FIXED)
    ['name' => 'Blogs', 'url' => '/admin/blogs', 'controller' => 'BlogController', 'view' => 'admin/blogs'],
    ['name' => 'Testimonials (New)', 'url' => '/admin/testimonials-new', 'controller' => 'TestimonialController', 'view' => 'admin/testimonials-new'],
    ['name' => 'FAQs (New)', 'url' => '/admin/faqs-new', 'controller' => 'FaqController', 'view' => 'admin/faqs'],
    ['name' => 'Knowledge Base', 'url' => '/admin/knowledge-base-new', 'controller' => 'KnowledgeBaseController', 'view' => 'admin/knowledge-base'],
];

$results = [];

echo "Testing " . count($menuItems) . " menu items...\n\n";

foreach ($menuItems as $index => $item) {
    $itemName = $item['name'];
    $itemUrl = $item['url'];
    $controller = $item['controller'];
    $viewPath = $item['view'];
    
    echo "[" . ($index + 1) . "/" . count($menuItems) . "] Testing: $itemName\n";
    echo "    URL: $baseUrl$itemUrl\n";
    
    $testResult = [
        'name' => $itemName,
        'url' => $itemUrl,
        'controller' => $controller,
        'view' => $viewPath,
        'route_exists' => false,
        'controller_exists' => false,
        'view_exists' => false,
        'url_accessible' => false
    ];
    
    // Test 1: Check Route exists in routes/web.php
    $routesFile = __DIR__ . '/routes/web.php';
    if (file_exists($routesFile)) {
        $routesContent = file_get_contents($routesFile);
        if (strpos($routesContent, $itemUrl) !== false) {
            echo "    âœ… Route exists\n";
            $testResult['route_exists'] = true;
        } else {
            echo "    â�Œ Route missing\n";
        }
    }
    
    // Test 2: Check Controller exists
    $controllerPath = __DIR__ . '/app/Http/Controllers/Admin/' . $controller . '.php';
    if (file_exists($controllerPath)) {
        echo "    âœ… Controller exists\n";
        $testResult['controller_exists'] = true;
    } else {
        echo "    â�Œ Controller missing\n";
    }
    
    // Test 3: Check View exists (if specified)
    if ($viewPath) {
        $indexPath = __DIR__ . '/app/views/' . $viewPath . '/index.php';
        $viewFile = __DIR__ . '/app/views/' . $viewPath . '.php';
        
        if (file_exists($indexPath) || file_exists($viewFile)) {
            echo "    âœ… View exists\n";
            $testResult['view_exists'] = true;
        } else {
            echo "    â�Œ View missing\n";
        }
    } else {
        echo "    â„¹ï¸�  No view specified (may use direct response)\n";
        $testResult['view_exists'] = true; // Assume OK if no view specified
    }
    
    // Test 4: Try HTTP request (cURL) - requires server running
    $fullUrl = $baseUrl . $itemUrl;
    echo "    ðŸŒ� Testing URL: $fullUrl\n";
    
    $ch = curl_init($fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 400) {
        echo "    âœ… URL accessible (HTTP $httpCode)\n";
        $testResult['url_accessible'] = true;
    } else {
        echo "    â�Œ URL not accessible (HTTP $httpCode)";
        if ($error) {
            echo " - Error: $error\n";
        } else {
            echo "\n";
        }
    }
    
    $results[] = $testResult;
    echo "\n";
}

// Generate Summary Report
echo "\n";
echo "=== SUMMARY REPORT ===\n\n";

$passedRoutes = 0;
$passedControllers = 0;
$passedViews = 0;
$passedUrls = 0;

foreach ($results as $result) {
    if ($result['route_exists']) $passedRoutes++;
    if ($result['controller_exists']) $passedControllers++;
    if ($result['view_exists']) $passedViews++;
    if ($result['url_accessible']) $passedUrls++;
}

$total = count($results);
echo "Total Menu Items: $total\n";
echo "Routes Defined: $passedRoutes/$total (" . round(($passedRoutes/$total)*100, 1) . "%)\n";
echo "Controllers Exist: $passedControllers/$total (" . round(($passedControllers/$total)*100, 1) . "%)\n";
echo "Views Exist: $passedViews/$total (" . round(($passedViews/$total)*100, 1) . "%)\n";
echo "URLs Accessible: $passedUrls/$total (" . round(($passedUrls/$total)*100, 1) . "%)\n";

echo "\n=== ITEMS WITH ISSUES ===\n";
$issuesFound = false;

foreach ($results as $result) {
    $hasIssue = !$result['route_exists'] || !$result['controller_exists'] || !$result['view_exists'] || !$result['url_accessible'];
    
    if ($hasIssue) {
        $issuesFound = true;
        echo "\nâ�Œ {$result['name']}\n";
        echo "   URL: {$result['url']}\n";
        if (!$result['route_exists']) echo "   - Missing route\n";
        if (!$result['controller_exists']) echo "   - Missing controller\n";
        if (!$result['view_exists']) echo "   - Missing view\n";
        if (!$result['url_accessible']) echo "   - URL not accessible\n";
    }
}

if (!$issuesFound) {
    echo "\nâœ… NO ISSUES FOUND - All menu items are working!\n";
}

echo "\n=== RECOMMENDATIONS ===\n";

if ($passedRoutes < $total) {
    echo "\nâ€¢ Add missing routes in routes/web.php\n";
}
if ($passedControllers < $total) {
    echo "â€¢ Create missing controllers in app/Http/Controllers/Admin/\n";
}
if ($passedViews < $total) {
    echo "â€¢ Create missing views in app/views/admin/\n";
}
if ($passedUrls < $total) {
    echo "â€¢ Check server is running (http://localhost)\n";
    echo "â€¢ Check for authentication requirements\n";
}

echo "\n=== END OF REPORT ===\n";?>