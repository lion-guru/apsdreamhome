<?php
/**
 * APS Dream Home - Large Controller Refactoring Analysis
 * Analyze and provide refactoring recommendations for large controllers
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'phase' => 'large_controller_analysis',
    'large_controllers' => [],
    'refactoring_recommendations' => [],
    'action_plan' => []
];

echo "🎯 LARGE CONTROLLER REFACTORING ANALYSIS\n";
echo "=======================================\n\n";

// Function to analyze a controller for refactoring opportunities
function analyzeController($filePath, &$results) {
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return false;
    }

    $content = file_get_contents($filePath);
    if ($content === false) return false;

    $relativePath = str_replace(dirname(__FILE__) . '/', '', $filePath);
    $lines = explode("\n", $content);
    $lineCount = count($lines);

    // Skip if not a large controller
    if ($lineCount < 300) {
        return false;
    }

    $analysis = [
        'file' => $relativePath,
        'total_lines' => $lineCount,
        'methods' => [],
        'dependencies' => [],
        'refactoring_opportunities' => []
    ];

    // Extract method signatures
    preg_match_all('/public\s+function\s+(\w+)\s*\([^)]*\)/', $content, $methodMatches);
    $methods = $methodMatches[1];

    foreach ($methods as $method) {
        // Count lines per method (rough estimate)
        $methodPattern = '/public\s+function\s+' . preg_quote($method) . '\s*\([^)]*\).*?(?=public\s+function|\}\s*$)/s';
        if (preg_match($methodPattern, $content, $methodMatch)) {
            $methodLines = substr_count($methodMatch[0], "\n") + 1;
            $analysis['methods'][] = [
                'name' => $method,
                'lines' => $methodLines
            ];
        }
    }

    // Analyze method complexity
    $largeMethods = array_filter($analysis['methods'], function($method) {
        return $method['lines'] > 50;
    });

    if (!empty($largeMethods)) {
        $analysis['refactoring_opportunities'][] = "Extract " . count($largeMethods) . " large methods (" . implode(', ', array_column($largeMethods, 'name')) . ")";
    }

    // Check for multiple responsibilities
    $responsibilities = [];

    if (preg_match('/database|query|DB::/', $content)) {
        $responsibilities[] = 'Database operations';
    }
    if (preg_match('/validation|validate|rules/', $content)) {
        $responsibilities[] = 'Input validation';
    }
    if (preg_match('/file|upload|storage/', $content)) {
        $responsibilities[] = 'File handling';
    }
    if (preg_match('/email|mail|notification/', $content)) {
        $responsibilities[] = 'Communication';
    }
    if (preg_match('/response|json|redirect|view/', $content)) {
        $responsibilities[] = 'Response handling';
    }

    if (count($responsibilities) > 3) {
        $analysis['refactoring_opportunities'][] = "Multiple responsibilities detected: " . implode(', ', $responsibilities);
        $analysis['refactoring_opportunities'][] = "Consider extracting to service classes or separate controllers";
    }

    // Check for code duplication
    $duplicatePatterns = [
        '/\$this->validate\s*\(/',
        '/return\s+(view|response|redirect)/',
        '/try\s*\{/',
        '/catch\s*\(/'
    ];

    foreach ($duplicatePatterns as $pattern) {
        $count = preg_match_all($pattern, $content);
        if ($count > 5) {
            $analysis['refactoring_opportunities'][] = "High duplication of " . str_replace(['\$this->', '/'], '', $pattern) . " patterns ({$count} occurrences)";
        }
    }

    // Check for missing dependency injection
    if (preg_match('/new\s+[A-Z]\w*\s*\(/', $content)) {
        $analysis['refactoring_opportunities'][] = "Direct instantiation found - consider dependency injection";
    }

    return $analysis;
}

// Scan controllers
echo "🔍 Analyzing Controller Files\n";
echo "=============================\n";

$controllerDir = $projectRoot . '/app/Http/Controllers';
$controllers = glob($controllerDir . '/*.php');
$controllers = array_merge($controllers, glob($controllerDir . '/**/*.php'));

$largeControllerCount = 0;

foreach ($controllers as $controller) {
    $analysis = analyzeController($controller, $results);

    if ($analysis !== false) {
        $largeControllerCount++;
        $results['large_controllers'][] = $analysis;

        echo "📁 Large Controller: {$analysis['file']} ({$analysis['total_lines']} lines)\n";
        echo "   📊 Methods: " . count($analysis['methods']) . "\n";

        if (!empty($analysis['refactoring_opportunities'])) {
            echo "   🔧 Refactoring opportunities:\n";
            foreach ($analysis['refactoring_opportunities'] as $opportunity) {
                echo "      • {$opportunity}\n";
            }
        }
        echo "\n";
    }
}

echo "\n📊 Analysis Summary\n";
echo "==================\n";
echo "🎯 Large controllers found: {$largeControllerCount}\n";

if (!empty($results['large_controllers'])) {
    // Generate action plan
    $results['action_plan'] = [
        "Phase 1: Extract Service Classes",
        "  • Create service classes for business logic",
        "  • Move database operations to repositories",
        "  • Extract validation logic to request classes",
        "",
        "Phase 2: Break Down Large Methods",
        "  • Extract private methods for complex operations",
        "  • Create dedicated action classes for complex workflows",
        "  • Use traits for shared functionality",
        "",
        "Phase 3: Implement Proper Architecture",
        "  • Use dependency injection throughout",
        "  • Implement repository pattern for data access",
        "  • Add proper error handling and logging",
        "",
        "Phase 4: Testing & Validation",
        "  • Write unit tests for extracted classes",
        "  • Integration tests for controller endpoints",
        "  • Performance testing for refactored code"
    ];

    echo "\n🔧 Refactoring Action Plan\n";
    echo "==========================\n";
    foreach ($results['action_plan'] as $step) {
        echo "{$step}\n";
    }
}

echo "\n📋 General Recommendations\n";
echo "==========================\n";
echo "• Aim for controllers under 200 lines\n";
echo "• Each controller method should have a single responsibility\n";
echo "• Use service classes for business logic\n";
echo "• Implement proper dependency injection\n";
echo "• Add comprehensive error handling\n";
echo "• Write tests for all new classes\n";
echo "• 🔄 Next: Fix N+1 query problems\n";

$results['summary'] = [
    'large_controllers_found' => $largeControllerCount,
    'total_refactoring_opportunities' => array_sum(array_map(function($controller) {
        return count($controller['refactoring_opportunities']);
    }, $results['large_controllers']))
];

// Save results
$resultsFile = $projectRoot . '/controller_refactoring_analysis.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n✅ Large controller analysis completed!\n";

?>
