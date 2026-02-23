<?php
/**
 * APS Dream Home - Phase 8 Deep Scan: Routing and Controller Analysis
 * Comprehensive analysis of routing, middleware, and controller logic
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'phase' => 'routing_scan',
    'summary' => [],
    'issues' => [],
    'security_concerns' => [],
    'performance_issues' => [],
    'recommendations' => []
];

echo "🛣️  Phase 8: Routing & Controller Deep Analysis\n";
echo "==============================================\n\n";

// Check routing files
echo "🛣️  Analyzing Routing Files\n";
echo "==========================\n";

$routesDir = $projectRoot . '/routes';
if (is_dir($routesDir)) {
    $routeFiles = glob($routesDir . '/*.php');
    echo "✅ Found " . count($routeFiles) . " route files\n";

    $totalRoutes = 0;
    $routeIssues = [];

    foreach ($routeFiles as $routeFile) {
        $filename = basename($routeFile);
        echo "📄 Analyzing {$filename}...\n";

        $content = file_get_contents($routeFile);
        if ($content === false) continue;

        // Count routes (basic pattern matching)
        $routePatterns = [
            '/Route::get\s*\(/',
            '/Route::post\s*\(/',
            '/Route::put\s*\(/',
            '/Route::patch\s*\(/',
            '/Route::delete\s*\(/',
            '/Route::any\s*\(/',
            '/Route::match\s*\(/'
        ];

        $fileRoutes = 0;
        foreach ($routePatterns as $pattern) {
            $matches = preg_match_all($pattern, $content);
            $fileRoutes += $matches;
        }

        echo "  🛣️  Routes in {$filename}: {$fileRoutes}\n";
        $totalRoutes += $fileRoutes;

        // Check for potential issues
        if (preg_match('/Route::.*\{\{.*\}\}/', $content)) {
            $routeIssues[] = "Blade syntax in routes file: {$filename}";
        }

        // Check for missing middleware
        $getRoutes = preg_match_all('/Route::get\s*\(/', $content);
        $protectedRoutes = preg_match_all('/middleware\s*\(/', $content);
        if ($getRoutes > 0 && $protectedRoutes === 0) {
            $results['security_concerns'][] = "No middleware protection detected in: {$filename}";
        }

        // Check for route model binding issues
        if (preg_match('/Route::.*\{.*\}/', $content)) {
            // This is normal route model binding, but check for potential issues
            if (preg_match('/Route::.*function\s*\([^}]*\{[^}]*\}/', $content)) {
                $routeIssues[] = "Complex route definitions in: {$filename}";
            }
        }
    }

    echo "\n📊 Total routes found: {$totalRoutes}\n";

    if (!empty($routeIssues)) {
        $results['issues'] = array_merge($results['issues'], $routeIssues);
    }

} else {
    $results['issues'][] = "Routes directory not found";
    echo "❌ Routes directory not found\n";
}

echo "\n";

// Check middleware
echo "🛡️  Analyzing Middleware\n";
echo "=======================\n";

$middlewareDir = $projectRoot . '/app/Http/Middleware';
if (is_dir($middlewareDir)) {
    $middlewareFiles = glob($middlewareDir . '/*.php');
    echo "✅ Found " . count($middlewareFiles) . " middleware files\n";

    foreach ($middlewareFiles as $middlewareFile) {
        $filename = basename($middlewareFile);
        echo "🛡️  {$filename}\n";

        $content = file_get_contents($middlewareFile);
        if ($content === false) continue;

        // Check for basic middleware structure
        if (!preg_match('/class\s+\w+.*Middleware/', $content)) {
            $results['issues'][] = "Invalid middleware class name in: {$filename}";
        }

        if (!preg_match('/public\s+function\s+handle/', $content)) {
            $results['issues'][] = "Missing handle method in middleware: {$filename}";
        }

        // Check for security issues
        if (preg_match('/\$request.*password/i', $content) && !preg_match('/Hash::/', $content)) {
            $results['security_concerns'][] = "Password handling without hashing in middleware: {$filename}";
        }
    }
} else {
    echo "ℹ️  Middleware directory not found (may be custom framework)\n";
}

echo "\n";

// Check controllers
echo "🎮 Analyzing Controllers\n";
echo "=======================\n";

$controllerDir = $projectRoot . '/app/Http/Controllers';
if (is_dir($controllerDir)) {
    $controllerFiles = glob($controllerDir . '/*.php');
    $totalControllers = count($controllerFiles);
    echo "✅ Found {$totalControllers} controller files\n";

    $largeControllers = 0;
    $controllersWithIssues = [];

    foreach ($controllerFiles as $controllerFile) {
        $filename = basename($controllerFile);
        $content = file_get_contents($controllerFile);

        if ($content === false) continue;

        $lines = explode("\n", $content);
        $lineCount = count($lines);

        // Check for large controllers
        if ($lineCount > 300) {
            $largeControllers++;
            $results['performance_issues'][] = "Large controller ({$lineCount} lines): {$filename}";
        }

        // Check for basic controller structure
        if (!preg_match('/class\s+\w+Controller/', $content)) {
            $controllersWithIssues[] = "Invalid controller class name: {$filename}";
        }

        // Check for security issues
        if (preg_match('/\$_GET|\$_POST|\$_REQUEST/i', $content) && !preg_match('/filter_var|htmlspecialchars|strip_tags/', $content)) {
            $results['security_concerns'][] = "Potential XSS vulnerability in controller: {$filename}";
        }

        // Check for SQL injection risks
        if (preg_match('/\$query.*SELECT|\$query.*INSERT|\$query.*UPDATE|\$query.*DELETE/i', $content) && !preg_match('/prepare|bind|PDO/', $content)) {
            $results['security_concerns'][] = "Potential SQL injection in controller: {$filename}";
        }

        // Check for proper error handling
        if (!preg_match('/try\s*\{|catch\s*\(/', $content)) {
            $results['issues'][] = "No error handling in controller: {$filename}";
        }

        // Check for validation
        if (preg_match('/store|update/i', $content) && !preg_match('/validate|Validator/', $content)) {
            $results['issues'][] = "No validation in store/update method: {$filename}";
        }

        // Performance checks
        if (preg_match('/for\s*\(|while\s*\(/', $content) && preg_match('/database|query/i', $content)) {
            $results['performance_issues'][] = "Potential N+1 query in loop: {$filename}";
        }
    }

    echo "📊 Large controllers: {$largeControllers}\n";
    echo "⚠️  Controllers with issues: " . count($controllersWithIssues) . "\n";

    if (!empty($controllersWithIssues)) {
        $results['issues'] = array_merge($results['issues'], $controllersWithIssues);
    }

} else {
    $results['issues'][] = "Controllers directory not found";
    echo "❌ Controllers directory not found\n";
}

echo "\n";

// Check for API controllers
echo "🔌 Analyzing API Controllers\n";
echo "===========================\n";

$apiControllerDir = $projectRoot . '/app/Http/Controllers/Api';
if (is_dir($apiControllerDir)) {
    $apiControllers = glob($apiControllerDir . '/*.php');
    echo "✅ Found " . count($apiControllers) . " API controllers\n";

    foreach ($apiControllers as $apiController) {
        $filename = basename($apiController);
        $content = file_get_contents($apiController);

        if ($content === false) continue;

        // Check for API-specific issues
        if (!preg_match('/return\s+response\s*->\s*json/i', $content) && preg_match('/echo|print/i', $content)) {
            $results['issues'][] = "API controller using echo/print instead of JSON response: {$filename}";
        }

        // Check for proper HTTP status codes
        if (preg_match('/return\s+response\s*\(/', $content) && !preg_match('/\d{3}/', $content)) {
            $results['issues'][] = "API controller missing HTTP status codes: {$filename}";
        }

        // Check for authentication
        if (!preg_match('/auth\s*->\s*|Auth::/', $content)) {
            $results['security_concerns'][] = "No authentication check in API controller: {$filename}";
        }
    }
} else {
    echo "ℹ️  API controllers directory not found\n";
}

echo "\n";

// Performance analysis
echo "⚡ Performance Analysis\n";
echo "=====================\n";

$performanceIssues = count($results['performance_issues']);
if ($performanceIssues > 0) {
    echo "⚠️  Found {$performanceIssues} potential performance issues\n";
    foreach ($results['performance_issues'] as $issue) {
        echo "• {$issue}\n";
    }
} else {
    echo "✅ No major performance issues detected\n";
}

echo "\n";

// Generate summary
echo "📊 Analysis Summary\n";
echo "==================\n";

$totalIssues = count($results['issues']);
$securityConcerns = count($results['security_concerns']);

if ($totalIssues === 0 && $securityConcerns === 0) {
    echo "🎉 Routing and controllers appear well-structured!\n";
} else {
    echo "⚠️  Found {$totalIssues} issues and {$securityConcerns} security concerns\n";
}

echo "\n📋 Recommendations\n";
echo "=================\n";
echo "• Implement proper middleware for route protection\n";
echo "• Break large controllers into smaller, focused classes\n";
echo "• Add input validation to all controller methods\n";
echo "• Use proper error handling and exception management\n";
echo "• Implement API rate limiting and authentication\n";
echo "• Consider using resource controllers for CRUD operations\n";
echo "• Run Phase 9: Final system testing and verification\n";

$results['summary'] = [
    'route_files' => isset($routeFiles) ? count($routeFiles) : 0,
    'middleware_files' => isset($middlewareFiles) ? count($middlewareFiles) : 0,
    'controller_files' => isset($controllerFiles) ? count($controllerFiles) : 0,
    'total_issues' => $totalIssues,
    'security_concerns' => $securityConcerns,
    'performance_issues' => $performanceIssues
];

// Save results
$resultsFile = $projectRoot . '/deep_scan_phase8_results.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n✅ Phase 8 Complete - Ready for Phase 9!\n";

?>
