<?php
/**
 * APS Dream Home - Simple Feature Verification
 * Standalone testing without full app dependencies
 */

echo "🔍 APS DREAM HOME - FEATURE VERIFICATION\n";
echo "========================================\n\n";

$baseDir = __DIR__;
$results = [];

// Test 1: Controllers
echo "🎮 Testing Controllers...\n";
$controllers = [
    'app/Http/Controllers/Property/CompareController.php',
    'app/Http/Controllers/Admin/LeadScoringController.php',
    'app/Http/Controllers/Admin/DealController.php',
    'app/Http/Controllers/AchievementController.php'
];

foreach ($controllers as $file) {
    $path = $baseDir . '/' . $file;
    $exists = file_exists($path);
    $size = $exists ? filesize($path) : 0;
    $results['controllers'][$file] = $exists;
    echo ($exists ? '✅' : '❌') . " {$file} (" . ($size > 0 ? round($size/1024, 1) . ' KB' : 'N/A') . ")\n";
}
echo "\n";

// Test 2: Views
echo "🎨 Testing Views...\n";
$views = [
    'app/views/properties/compare.php',
    'app/views/properties/compare_results.php',
    'app/views/admin/leads/scoring.php',
    'app/views/admin/visits/index.php',
    'app/views/admin/visits/calendar.php',
    'app/views/admin/visits/create.php',
    'app/views/admin/deals/index.php',
    'app/views/admin/deals/kanban.php',
    'app/views/admin/deals/create.php',
    'app/views/dashboard/achievements.php'
];

foreach ($views as $file) {
    $path = $baseDir . '/' . $file;
    $exists = file_exists($path);
    $size = $exists ? filesize($path) : 0;
    $results['views'][$file] = $exists;
    echo ($exists ? '✅' : '❌') . " {$file} (" . ($size > 0 ? round($size/1024, 1) . ' KB' : 'N/A') . ")\n";
}
echo "\n";

// Test 3: Routes
echo "🛣️  Testing Routes in web.php...\n";
$routesFile = $baseDir . '/routes/web.php';
$routesContent = file_exists($routesFile) ? file_get_contents($routesFile) : '';

$requiredRoutes = [
    '/compare' => 'Property Comparison',
    '/compare/results' => 'Compare Results',
    '/ai-valuation' => 'AI Valuation',
    '/admin/leads/scoring' => 'Lead Scoring',
    '/admin/visits' => 'Site Visits',
    '/admin/visits/calendar' => 'Visit Calendar',
    '/admin/deals' => 'Deal Tracking',
    '/admin/deals/kanban' => 'Deal Kanban',
    '/dashboard/achievements' => 'Achievements'
];

foreach ($requiredRoutes as $route => $name) {
    $found = strpos($routesContent, "'{$route}'") !== false || strpos($routesContent, '"' . $route . '"') !== false;
    $results['routes'][$route] = $found;
    echo ($found ? '✅' : '❌') . " {$route} => {$name}\n";
}
echo "\n";

// Test 4: Check for syntax errors in PHP files
echo "🔍 Syntax Check (Basic)...\n";
$phpFiles = array_merge($controllers, [
    'app/Http/Controllers/AIController.php',
    'app/Http/Controllers/Admin/VisitController.php',
    'app/Http/Controllers/Admin/LeadController.php'
]);

$syntaxOk = true;
foreach ($phpFiles as $file) {
    $path = $baseDir . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        // Basic checks
        $openTags = substr_count($content, '<?php');
        $closeTags = substr_count($content, '?>');
        $openBraces = substr_count($content, '{');
        $closeBraces = substr_count($content, '}');
        
        $balanced = ($openBraces == $closeBraces);
        $results['syntax'][$file] = $balanced;
        echo ($balanced ? '✅' : '⚠️') . " {$file} (braces: {$openBraces}/{$closeBraces})\n";
        if (!$balanced) $syntaxOk = false;
    }
}
echo "\n";

// Summary
echo "📊 SUMMARY\n";
echo "==========\n";

$controllerPass = count(array_filter($results['controllers'] ?? []));
$controllerTotal = count($controllers);

$viewPass = count(array_filter($results['views'] ?? []));
$viewTotal = count($views);

$routePass = count(array_filter($results['routes'] ?? []));
$routeTotal = count($requiredRoutes);

$totalPass = $controllerPass + $viewPass + $routePass;
$totalItems = $controllerTotal + $viewTotal + $routeTotal;
$percentage = round(($totalPass / $totalItems) * 100);

echo "Controllers: {$controllerPass}/{$controllerTotal}\n";
echo "Views:       {$viewPass}/{$viewTotal}\n";
echo "Routes:      {$routePass}/{$routeTotal}\n";
echo "--------------------------------\n";
echo "TOTAL:       {$totalPass}/{$totalItems} ({$percentage}%)\n\n";

// Feature Status
echo "✅ FEATURE STATUS:\n";
echo "------------------\n";

$features = [
    'Property Comparison' => [
        'controllers' => ['app/Http/Controllers/Property/CompareController.php'],
        'views' => ['app/views/properties/compare.php', 'app/views/properties/compare_results.php'],
        'routes' => ['/compare', '/compare/results']
    ],
    'AI Valuation' => [
        'controllers' => ['app/Http/Controllers/AIController.php'],
        'views' => ['app/views/pages/ai-valuation.php'],
        'routes' => ['/ai-valuation']
    ],
    'Lead Scoring' => [
        'controllers' => ['app/Http/Controllers/Admin/LeadScoringController.php'],
        'views' => ['app/views/admin/leads/scoring.php'],
        'routes' => ['/admin/leads/scoring']
    ],
    'Site Visit Tracking' => [
        'controllers' => ['app/Http/Controllers/Admin/VisitController.php'],
        'views' => ['app/views/admin/visits/index.php', 'app/views/admin/visits/calendar.php', 'app/views/admin/visits/create.php'],
        'routes' => ['/admin/visits', '/admin/visits/calendar']
    ],
    'Lead Documents' => [
        'controllers' => ['app/Http/Controllers/Admin/LeadController.php'],
        'views' => [],
        'routes' => ['/admin/leads']
    ],
    'Deal Tracking' => [
        'controllers' => ['app/Http/Controllers/Admin/DealController.php'],
        'views' => ['app/views/admin/deals/index.php', 'app/views/admin/deals/kanban.php', 'app/views/admin/deals/create.php'],
        'routes' => ['/admin/deals', '/admin/deals/kanban']
    ],
    'User Achievements' => [
        'controllers' => ['app/Http/Controllers/AchievementController.php'],
        'views' => ['app/views/dashboard/achievements.php'],
        'routes' => ['/dashboard/achievements']
    ]
];

foreach ($features as $name => $components) {
    $complete = true;
    
    foreach ($components['controllers'] as $c) {
        if (!file_exists($baseDir . '/' . $c)) $complete = false;
    }
    foreach ($components['views'] as $v) {
        if (!file_exists($baseDir . '/' . $v)) $complete = false;
    }
    foreach ($components['routes'] as $r) {
        if (strpos($routesContent, "'{$r}'") === false && strpos($routesContent, '"' . $r . '"') === false) {
            $complete = false;
        }
    }
    
    echo ($complete ? '✅' : '❌') . " {$name}\n";
}

echo "\n🚀 STATUS: " . ($percentage >= 95 ? "READY FOR TESTING!" : "NEEDS ATTENTION") . "\n";

// Create simple report file
$report = "# APS Dream Home - Feature Verification Report\n\n";
$report .= "**Date:** " . date('Y-m-d H:i:s') . "\n\n";
$report .= "## Summary\n- **Total Tests:** {$totalItems}\n";
$report .= "- **Passed:** {$totalPass}\n";
$report .= "- **Success Rate:** {$percentage}%\n\n";
$report .= "## Features\n";
foreach ($features as $name => $components) {
    $complete = true;
    foreach ($components['controllers'] as $c) {
        if (!file_exists($baseDir . '/' . $c)) $complete = false;
    }
    foreach ($components['views'] as $v) {
        if (!file_exists($baseDir . '/' . $v)) $complete = false;
    }
    foreach ($components['routes'] as $r) {
        if (strpos($routesContent, "'{$r}'") === false && strpos($routesContent, '"' . $r . '"') === false) {
            $complete = false;
        }
    }
    $report .= "- [" . ($complete ? 'x' : ' ') . "] {$name}\n";
}
$report .= "\n## Status: " . ($percentage >= 95 ? '✅ COMPLETE' : '⚠️ INCOMPLETE') . "\n";

file_put_contents($baseDir . '/FEATURE_VERIFICATION_REPORT.md', $report);
echo "\n📄 Report saved: FEATURE_VERIFICATION_REPORT.md\n";
