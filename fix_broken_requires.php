<?php
// APS Dream Home - Broken Require/Include Fixer
// Removes old/legacy require statements that reference non-existent files

$projectRoot = __DIR__;
$viewsDir = $projectRoot . '/app/views/pages';
$fixedCount = 0;
$errors = [];

$brokenPatterns = [
    "require_once 'includes/",
    'require_once "includes/',
    "require_once __DIR__ . '/../../../includes/",
    "require_once __DIR__ . '/../../includes/",
    "require_once 'core/",
    "require_once 'includes/config/",
    "require_once 'includes/functions/",
    "require_once 'includes/db_connection.php'",
    "require_once __DIR__ . '/../../../core/",
    "require_once 'core/init.php'",
    "require_once __DIR__ . '/../../core/init.php'",
    "require_once __DIR__ . '/../../core/",
    "require_once ABSPATH",
];

$brokenFiles = [
    $viewsDir . '/whatsapp_chat.php',
    $viewsDir . '/user_ai_suggestions.php',
    $viewsDir . '/system/launch_system.php',
    $viewsDir . '/support.php',
    $viewsDir . '/properties/list.php',
    $viewsDir . '/properties/book_plot.php',
    $viewsDir . '/properties/book.php',
    $viewsDir . '/builder_registration.php',
    $viewsDir . '/auth/password_update.php',
    $viewsDir . '/auth/login.php',
    $viewsDir . '/aps_portfolio.php',
    $viewsDir . '/aps_official_info.php',
    $viewsDir . '/analytics.php',
    $viewsDir . '/admin/update_company_info.php',
    $viewsDir . '/admin/manage_colonies.php',
    $viewsDir . '/admin/add_colony_tables.php',
    $projectRoot . '/app/views/dashboard/management_dashboard.php',
    $projectRoot . '/app/views/dashboard/hybrid_commission_dashboard.php',
    $projectRoot . '/app/views/dashboard/commission_dashboard.php',
    $projectRoot . '/app/views/dashboard/builder_dashboard.php',
    $projectRoot . '/app/views/dashboard/clean_dashboard.php',
    $projectRoot . '/app/views/commission/commission_plan_manager.php',
    $projectRoot . '/app/views/commission/commission_plan_calculator.php',
    $projectRoot . '/app/views/commission/commission-opportunity.php',
    $projectRoot . '/app/views/tools/development_cost_calculator.php',
];

function fixFile($filePath) {
    global $brokenPatterns, $fixedCount;
    
    if (!file_exists($filePath)) {
        return "SKIP - file not found";
    }
    
    $content = file_get_contents($filePath);
    $original = $content;
    
    foreach ($brokenPatterns as $pattern) {
        // Match require_once statements with this pattern
        $regex = '/\s*require_once\s+[\'"]' . preg_quote($pattern, '/') . '[^\'\"]*[\'"]\s*;?\s*\n?/i';
        $content = preg_replace($regex, "\n", $content);
    }
    
    // Clean up multiple blank lines
    $content = preg_replace("/\n{3,}/", "\n\n", $content);
    
    if ($content !== $original) {
        file_put_contents($filePath, $content);
        $fixedCount++;
        return "FIXED";
    }
    
    return "NO CHANGE";
}

echo "=== APS DREAM HOME - BROKEN REQUIRE FIXER ===\n\n";
echo "Scanning " . count($brokenFiles) . " known broken files...\n\n";

foreach ($brokenFiles as $file) {
    $result = fixFile($file);
    $shortFile = str_replace($projectRoot . '/', '', $file);
    if ($result !== "NO CHANGE") {
        echo "$shortFile: $result\n";
    }
}

// Also scan all PHP files in views for any remaining broken requires
echo "\n--- Scanning all view files for broken requires ---\n";
$allViews = glob($viewsDir . '/**/*.php', GLOB_BRACE);
if (empty($allViews)) {
    $allViews = [];
    function scanDir($dir, &$files) {
        $items = glob($dir . '/*.php');
        if ($items) $files = array_merge($files, $items);
        $dirs = glob($dir . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $d) scanDir($d, $files);
    }
    scanDir($viewsDir, $allViews);
}

foreach ($allViews as $file) {
    $result = fixFile($file);
    if ($result !== "NO CHANGE" && $result !== "SKIP - file not found") {
        $shortFile = str_replace($projectRoot . '/', '', $file);
        echo "$shortFile: $result\n";
    }
}

echo "\n=== COMPLETE ===\n";
echo "Total files fixed: $fixedCount\n";
