<?php

/**
 * INTELLIGENT DUPLICATE ANALYSIS
 * Code-level analysis to identify actual duplicates vs legitimate different functionality
 */

echo "🧠 INTELLIGENT DEEP SCAN STARTING...\n";
echo str_repeat("=", 70) . "\n\n";

$projectPath = 'C:/xampp/htdocs/apsdreamhome';

// Function to analyze controller code
function analyzeController($filePath)
{
    if (!file_exists($filePath)) return null;

    $content = file_get_contents($filePath);

    // Extract namespace
    preg_match('/namespace\s+(.+?);/', $content, $namespaceMatch);
    $namespace = $namespaceMatch[1] ?? '';

    // Extract class name
    preg_match('/class\s+(\w+)/', $content, $classMatch);
    $className = $classMatch[1] ?? '';

    // Extract methods
    preg_match_all('/public\s+function\s+(\w+)/', $content, $methodMatches);
    $methods = $methodMatches[1] ?? [];

    // Extract dependencies/use statements
    preg_match_all('/use\s+(.+?);/', $content, $useMatches);
    $dependencies = $useMatches[1] ?? [];

    // Get file size
    $fileSize = filesize($filePath);

    // Get line count
    $lineCount = substr_count($content, "\n");

    return [
        'namespace' => $namespace,
        'class' => $className,
        'methods' => $methods,
        'dependencies' => $dependencies,
        'file_size' => $fileSize,
        'line_count' => $lineCount,
        'content_hash' => md5($content)
    ];
}

// Function to compare controllers
function compareControllers($controller1, $controller2)
{
    if (!$controller1 || !$controller2) return ['duplicate' => false, 'reason' => 'one missing'];

    // Check if they have same class name in different namespaces
    if ($controller1['class'] === $controller2['class']) {
        // Compare content hash
        if ($controller1['content_hash'] === $controller2['content_hash']) {
            return ['duplicate' => true, 'reason' => 'exact duplicate', 'confidence' => 100];
        }

        // Compare methods (allow some differences)
        $methodIntersection = array_intersect($controller1['methods'], $controller2['methods']);
        $methodUnion = array_unique(array_merge($controller1['methods'], $controller2['methods']));

        // Fix division by zero
        if (count($methodUnion) == 0) {
            return ['duplicate' => false, 'reason' => 'no methods', 'confidence' => 0];
        }

        if (count($methodIntersection) / count($methodUnion) > 0.8) {
            return ['duplicate' => true, 'reason' => 'similar methods (80%+ match)', 'confidence' => 80];
        }

        if (count($methodIntersection) / count($methodUnion) > 0.5) {
            return ['duplicate' => true, 'reason' => 'similar methods (50%+ match)', 'confidence' => 50];
        }
    }

    return ['duplicate' => false, 'reason' => 'different functionality', 'confidence' => 0];
}

// Scan controllers with detailed analysis
echo "1. 🧠 DEEP CONTROLLER ANALYSIS...\n";
$controllersPath = $projectPath . '/app/Http/Controllers';
$controllerAnalysis = [];

if (is_dir($controllersPath)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllersPath));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() == 'php') {
            $analysis = analyzeController($file->getPathname());
            if ($analysis) {
                $relativePath = str_replace($projectPath . '/', '', $file->getPathname());
                $controllerAnalysis[$analysis['class']][] = [
                    'path' => $relativePath,
                    'namespace' => $analysis['namespace'],
                    'methods' => $analysis['methods'],
                    'file_size' => $analysis['file_size'],
                    'line_count' => $analysis['line_count'],
                    'content_hash' => $analysis['content_hash']
                ];
            }
        }
    }
}

echo "   Found " . count($controllerAnalysis) . " unique controller classes\n";

// Analyze potential duplicates
$actualDuplicates = [];
$differentFunctionality = [];

foreach ($controllerAnalysis as $className => $instances) {
    if (count($instances) > 1) {
        echo "\n   🔍 Analyzing: $className (" . count($instances) . " instances)\n";

        $isDuplicate = false;
        $comparisonResults = [];

        // Compare first with all others
        for ($i = 1; $i < count($instances); $i++) {
            $result = compareControllers(
                ['class' => $className, 'content_hash' => $instances[0]['content_hash'], 'methods' => $instances[0]['methods']],
                ['class' => $className, 'content_hash' => $instances[$i]['content_hash'], 'methods' => $instances[$i]['methods']]
            );

            $comparisonResults[] = [
                'comparison' => $instances[0]['path'] . " vs " . $instances[$i]['path'],
                'result' => $result
            ];

            if ($result['duplicate']) {
                $isDuplicate = true;
            }
        }

        if ($isDuplicate) {
            $actualDuplicates[$className] = [
                'instances' => $instances,
                'comparisons' => $comparisonResults
            ];
            echo "   ✅ IDENTIFIED AS DUPLICATE\n";
            foreach ($instances as $instance) {
                echo "      - {$instance['path']} ({$instance['line_count']} lines)\n";
            }
        } else {
            $differentFunctionality[$className] = [
                'instances' => $instances,
                'comparisons' => $comparisonResults
            ];
            echo "   ℹ️ DIFFERENT FUNCTIONALITY (legitimate)\n";
            foreach ($instances as $instance) {
                echo "      - {$instance['path']} (Namespace: {$instance['namespace']}, Methods: " . count($instance['methods']) . ")\n";
            }
        }
    }
}

echo "\n";
echo "📊 CONTROLLER ANALYSIS SUMMARY:\n";
echo "   Total unique classes: " . count($controllerAnalysis) . "\n";
echo "   Confirmed duplicates: " . count($actualDuplicates) . "\n";
echo "   Different functionality: " . count($differentFunctionality) . "\n";

echo "\n2. 🧠 DEEP ROUTE ANALYSIS...\n";
$routesFile = $projectPath . '/routes/web.php';
$routeAnalysis = [];

if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);

    // Parse routes with more detail
    preg_match_all('/\$router->(get|post|any|put|delete|patch)\([\'"](.*?)[\'"]\s*,\s*[\'"](.*?)[\'"]\s*,/', $routesContent, $matches);

    foreach ($matches[2] as $index => $route) {
        $method = $matches[1][$index];
        $handler = $matches[3][$index];

        if (!isset($routeAnalysis[$route])) {
            $routeAnalysis[$route] = [];
        }

        $routeAnalysis[$route][] = [
            'method' => $method,
            'handler' => $handler
        ];
    }

    echo "   Found " . count($routeAnalysis) . " unique routes\n";

    // Analyze duplicate routes
    $duplicateRoutes = [];
    $legitimateMultiMethod = [];

    foreach ($routeAnalysis as $route => $definitions) {
        if (count($definitions) > 1) {
            // Check if same handler
            $handlers = array_column($definitions, 'handler');
            $uniqueHandlers = array_unique($handlers);

            if (count($uniqueHandlers) === 1) {
                // Same handler, different methods - legitimate
                $legitimateMultiMethod[$route] = $definitions;
            } else {
                // Different handlers - potential duplicate
                $duplicateRoutes[$route] = $definitions;
            }
        }
    }

    echo "   Legitimate multi-method routes (GET + POST same handler): " . count($legitimateMultiMethod) . "\n";
    echo "   Conflicting routes (different handlers): " . count($duplicateRoutes) . "\n";
}

echo "\n3. 🧠 DEEP VIEW ANALYSIS...\n";
$viewsPath = $projectPath . '/app/views';
$viewAnalysis = [];

if (is_dir($viewsPath)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() == 'php') {
            $fileName = $file->getFilename();
            $relativePath = str_replace($projectPath . '/', '', $file->getPathname());

            if (!isset($viewAnalysis[$fileName])) {
                $viewAnalysis[$fileName] = [];
            }

            $viewAnalysis[$fileName][] = [
                'path' => $relativePath,
                'file_size' => filesize($file->getPathname()),
                'content_hash' => md5(file_get_contents($file->getPathname()))
            ];
        }
    }
}

echo "   Found " . count($viewAnalysis) . " unique view filenames\n";

// Analyze duplicate views
$actualViewDuplicates = [];
$differentViewContexts = [];

foreach ($viewAnalysis as $fileName => $instances) {
    if (count($instances) > 1) {
        $hashes = array_column($instances, 'content_hash');
        $uniqueHashes = array_unique($hashes);

        if (count($uniqueHashes) === 1) {
            // Exact duplicates
            $actualViewDuplicates[$fileName] = $instances;
        } else {
            // Different content - different contexts
            $differentViewContexts[$fileName] = $instances;
        }
    }
}

echo "   Confirmed duplicate views (exact same content): " . count($actualViewDuplicates) . "\n";
echo "   Different contexts (same name, different content): " . count($differentViewContexts) . "\n";

echo "\n" . str_repeat("=", 70) . "\n";
echo "📊 INTELLIGENT ANALYSIS SUMMARY\n";
echo str_repeat("=", 70) . "\n\n";

echo "CONTROLLERS:\n";
echo "   Total unique classes: " . count($controllerAnalysis) . "\n";
echo "   ✅ Actual duplicates (same code): " . count($actualDuplicates) . "\n";
echo "   ℹ️ Different functionality (legitimate): " . count($differentFunctionality) . "\n";
echo "\n";

echo "ROUTES:\n";
echo "   Total unique routes: " . count($routeAnalysis) . "\n";
echo "   ✅ Legitimate multi-method (GET+POST same handler): " . count($legitimateMultiMethod) . "\n";
echo "   ❌ Conflicting routes (different handlers): " . count($duplicateRoutes) . "\n";
echo "\n";

echo "VIEWS:\n";
echo "   Total unique filenames: " . count($viewAnalysis) . "\n";
echo "   ✅ Actual duplicates (exact same content): " . count($actualViewDuplicates) . "\n";
echo "   ℹ️ Different contexts (same name, different content): " . count($differentViewContexts) . "\n";
echo "\n";

// Show examples of different functionality (legitimate duplicates)
if (!empty($differentFunctionality)) {
    echo "📋 EXAMPLES OF LEGITIMATE DIFFERENT FUNCTIONALITY:\n";
    $count = 0;
    foreach ($differentFunctionality as $className => $data) {
        if ($count++ >= 5) break;
        echo "\n   $className:\n";
        foreach ($data['instances'] as $instance) {
            echo "      - {$instance['path']}\n";
            echo "        Namespace: {$instance['namespace']}\n";
            echo "        Methods: " . implode(', ', $instance['methods']) . "\n";
        }
    }
}

// Show actual duplicates
if (!empty($actualDuplicates)) {
    echo "\n🚨 ACTUAL DUPLICATES TO FIX:\n";
    $count = 0;
    foreach ($actualDuplicates as $className => $data) {
        if ($count++ >= 5) break;
        echo "\n   $className:\n";
        foreach ($data['instances'] as $instance) {
            echo "      - {$instance['path']} ({$instance['line_count']} lines)\n";
        }
    }
}

echo "\n💡 RECOMMENDATION:\n";
if (count($actualDuplicates) > 0) {
    echo "   Fix " . count($actualDuplicates) . " actual duplicate controllers\n";
}
if (count($duplicateRoutes) > 0) {
    echo "   Fix " . count($duplicateRoutes) . " conflicting routes\n";
}
if (count($actualViewDuplicates) > 0) {
    echo "   Fix " . count($actualViewDuplicates) . " actual duplicate views\n";
}
if (count($differentFunctionality) > 0) {
    echo "   Keep " . count($differentFunctionality) . " controllers (different namespaces = legitimate)\n";
}
if (count($legitimateMultiMethod) > 0) {
    echo "   Keep " . count($legitimateMultiMethod) . " multi-method routes (GET+POST = legitimate)\n";
}
if (count($differentViewContexts) > 0) {
    echo "   Keep " . count($differentViewContexts) . " views (different context = legitimate)\n";
}

echo "\n🎉 INTELLIGENT ANALYSIS COMPLETE!\n";
