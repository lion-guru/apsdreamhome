<?php
$missing = [
    'app/Http/Controllers/AIController.php',
    'app/Http/Controllers/Land/PlottingController.php',
    'app/Http/Controllers/Media/MediaLibraryController.php',
    'app/Http/Controllers/Marketing/MarketingAutomationController.php',
    'app/Http/Controllers/Career/CareerController.php',
    'app/Http/Controllers/Security/SecurityController.php',
    'app/Http/Controllers/Event/EventController.php',
    'app/Http/Controllers/Performance/PerformanceController.php',
    'app/Http/Controllers/Communication/MediaController.php',
    'app/Http/Controllers/Communication/SmsController.php',
    'app/Http/Controllers/Async/AsyncController.php',
    'app/Http/Controllers/Utility/AlertController.php',
    'app/Http/Controllers/Backup/BackupIntegrityController.php',
    'app/Http/Controllers/Payroll/SalaryController.php',
    'app/Http/Controllers/CustomFeatures/CustomFeaturesController.php',
    'app/Http/Controllers/Associate/AssociateController.php',
    'app/Http/Controllers/User/UserController.php',
    'app/Http/Controllers/Reports/ReportController.php',
    'app/Http/Controllers/Marketing/MarketingController.php',
];

// Also check for existing controllers
$existing = [
    'app/Http/Controllers/Front/PageController.php',
    'app/Http/Controllers/Auth/AdminAuthController.php',
    'app/Http/Controllers/Admin/AdminDashboardController.php',
    'app/Http/Controllers/Employee/EmployeeController.php',
    'app/Http/Controllers/RoleBasedDashboardController.php',
];

echo "=== MISSING CONTROLLERS ===\n";
$missingCount = 0;
foreach ($missing as $f) {
    if (!file_exists($f)) {
        echo "MISSING: $f\n";
        $missingCount++;
    } else {
        echo "  EXISTS: $f\n";
    }
}
echo "\n=== KEY EXISTING CONTROLLERS ===\n";
foreach ($existing as $f) {
    echo (file_exists($f) ? "  EXISTS" : "MISSING") . ": $f\n";
}
echo "\nMissing count: $missingCount\n";

// Check for duplicate router creation issue in index.php vs web.php
echo "\n=== ROUTER BUG CHECK ===\n";
$indexContent = file_get_contents('public/index.php');
$webContent = file_get_contents('routes/web.php');
$routerInIndex = substr_count($indexContent, 'new Router()');
$routerInWeb = substr_count($webContent, 'new Router()');
echo "Router instances in index.php: $routerInIndex (should be 1)\n";
echo "Router instances in web.php: $routerInWeb (should be 0 - web.php should not create its own)\n";

// Count duplicate routes
echo "\n=== DUPLICATE ROUTE COUNT ===\n";
preg_match_all('/\$router->get\(\'([^\']+)\'/', $webContent, $getRoutes);
$duplicates = array_diff_key($getRoutes[1], array_unique($getRoutes[1]));
$unique = array_count_values($getRoutes[1]);
$dups = array_filter($unique, fn($v) => $v > 1);
echo "Duplicate GET routes: " . count($dups) . "\n";
foreach ($dups as $route => $count) {
    echo "  '$route' defined $count times\n";
}
