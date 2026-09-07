<?php
/**
 * Dead Code Scanner — APS Dream Home
 * Scans controllers, services, and routes for unused/orphaned code.
 * Usage: php testing/scan_dead_code.php
 */

set_time_limit(300);
$projectRoot = dirname(__DIR__);
$archivePath = $projectRoot . '/_archive';

// ============================================================
// PHASE 1: Collect all route targets from web.php and api.php
// ============================================================
echo "=== PHASE 1: Scanning routes ===\n\n";

$routeFiles = [
    $projectRoot . '/routes/web.php',
    $projectRoot . '/routes/api.php',
];

$routeTargets = []; // [ControllerClass] => [method1, method2, ...]
$routeLines = [];   // [ControllerClass::method] => line number
$allRouteRaw = '';

foreach ($routeFiles as $rf) {
    if (!file_exists($rf)) { echo "SKIP: $rf not found\n"; continue; }
    $content = file_get_contents($rf);
    $allRouteRaw .= $content;
    $lines = explode("\n", $content);
    foreach ($lines as $lineNum => $line) {
        $trimmed = trim($line);
        // Skip comments
        if (strpos($trimmed, '//') === 0 || strpos($trimmed, '#') === 0) continue;
        
        // Match various route patterns:
        // $router->get('/path', 'Controller@method');
        // $router->post('/path', 'Controller@method');
        // $router->put(...), $router->delete(...), $router->patch(...)
        // route()->get(...), Route::get(...)
        // Also match: 'uses' => 'Controller@method'
        // Also match: [Controller::class, 'method']
        // Also match: redirect patterns
        
        // Pattern 1: 'Controller@method' in string
        if (preg_match_all("/['\"]([A-Za-z\\\\]+(?:Controller|Service|Repository))@(\w+)['\"]/", $trimmed, $m)) {
            foreach ($m[1] as $idx) {
                $fqcn = $m[1][$idx];
                $method = $m[2][$idx];
                // Extract short class name
                $parts = explode('\\', $fqcn);
                $shortClass = end($parts);
                $routeTargets[$shortClass][] = $method;
                $routeLines["$shortClass::$method"] = $lineNum + 1;
            }
        }
        
        // Pattern 2: [SomeController::class, 'method']
        if (preg_match_all("/([A-Za-z\\\\]*(?:Controller|Service))::class\s*,\s*['\"](\w+)['\"]/", $trimmed, $m)) {
            foreach ($m[1] as $idx) {
                $fqcn = $m[1][$idx];
                $method = $m[2][$idx];
                $parts = explode('\\', $fqcn);
                $shortClass = end($parts);
                $routeTargets[$shortClass][] = $method;
                $routeLines["$shortClass::$method"] = $lineNum + 1;
            }
        }
        
        // Pattern 3: 'uses' => 'SomeController@method'
        if (preg_match_all("/['\"]uses['\"]\s*=>\s*['\"]([A-Za-z\\\\]+)@(\w+)['\"]/", $trimmed, $m)) {
            foreach ($m[1] as $idx) {
                $fqcn = $m[1][$idx];
                $method = $m[2][$idx];
                $parts = explode('\\', $fqcn);
                $shortClass = end($parts);
                $routeTargets[$shortClass][] = $method;
                $routeLines["$shortClass::$method"] = $lineNum + 1;
            }
        }
        
        // Pattern 4: Callable string 'SomeController@method' (in route groups)
        if (preg_match_all("/['\"]([A-Z][A-Za-z0-9]+(?:Controller))@(\w+)['\"]/", $trimmed, $m)) {
            foreach ($m[1] as $idx) {
                $fqcn = $m[1][$idx];
                $method = $m[2][$idx];
                $parts = explode('\\', $fqcn);
                $shortClass = end($parts);
                $routeTargets[$shortClass][] = $method;
                $routeLines["$shortClass::$method"] = $lineNum + 1;
            }
        }
    }
    // Deduplicate
    foreach ($routeTargets as $class => &$methods) {
        $methods = array_unique($methods);
    }
    unset($methods);
}

echo "Found " . count($routeTargets) . " unique controller classes in routes\n";
echo "Total route->method mappings: " . array_sum(array_map('count', $routeTargets)) . "\n\n";

// ============================================================
// PHASE 2: Scan ALL controller files
// ============================================================
echo "=== PHASE 2: Scanning controllers ===\n\n";

$controllerDir = $projectRoot . '/app/Http/Controllers';
$controllerFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($controllerDir)
);
foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') continue;
    // Skip archived files
    $realPath = $file->getRealPath();
    if (strpos($realPath, '_archive') !== false) continue;
    $controllerFiles[] = $realPath;
}

echo "Found " . count($controllerFiles) . " controller files\n\n";

$deadControllers = [];

foreach ($controllerFiles as $filePath) {
    $content = file_get_contents($filePath);
    
    // Extract class name
    if (!preg_match('/^\s*(?:abstract\s+)?class\s+(\w+)/m', $content, $classMatch)) {
        continue; // Skip files without class declarations
    }
    $className = $classMatch[1];
    
    // Skip framework files that are loaded by convention
    $skipClasses = [
        'BaseController', 'AdminController', 'BaseApiController',
        'Controller', 'AuthController',
    ];
    if (in_array($className, $skipClasses)) continue;
    
    // Extract all public methods
    if (!preg_match_all('/public\s+function\s+(\w+)\s*\(/', $content, $methodMatches)) {
        continue;
    }
    $publicMethods = array_unique($methodMatches[1]);
    
    // Check if ANY route references this controller
    $hasRoutes = isset($routeTargets[$className]) && count($routeTargets[$className]) > 0;
    
    // Check cross-references from other files (use statements, new instantiation, :: calls)
    $shortName = $className;
    // Build grep patterns for this class
    $refPatterns = [
        "new\s+{$shortName}",
        "use\s+.*\\\\{$shortName}",
        "{$shortName}::",
        "\\${$shortName}",
        "instanceof\s+{$shortName}",
    ];
    
    $refCount = 0;
    $referencedBy = [];
    
    foreach ($refPatterns as $pattern) {
        // Use ripgrep for speed
        $cmd = "rg -c \"$pattern\" --include=\"*.php\" --glob=\"!_archive/\" --glob=\"!testing/scan_dead_code.php\" \"$projectRoot/app/\" 2>&1";
        $output = shell_exec($cmd);
        if ($output) {
            $lines = array_filter(explode("\n", trim($output)));
            foreach ($lines as $line) {
                if (preg_match('/^([^:]+):(\d+)$/', $line, $lm)) {
                    $refCount += (int)$lm[2];
                    $referencedBy[] = basename($lm[1]);
                }
            }
        }
    }
    
    // Remove self-references
    $selfFile = basename($filePath);
    $referencedBy = array_diff(array_unique($referencedBy), [$selfFile]);
    
    if (!$hasRoutes && $refCount === 0) {
        // Extract namespace
        $namespace = '';
        if (preg_match('/^namespace\s+([\w\\\\]+)\s*;/m', $content, $nsMatch)) {
            $namespace = $nsMatch[1];
        }
        
        $relativePath = str_replace($projectRoot . '\\', '', $filePath);
        $relativePath = str_replace($projectRoot . '/', '', $relativePath);
        
        $deadControllers[] = [
            'file' => $relativePath,
            'class' => $className,
            'namespace' => $namespace,
            'methods' => $publicMethods,
            'reason' => '0 routes, 0 cross-references',
            'referencedBy' => $referencedBy,
        ];
    } elseif (!$hasRoutes && $refCount <= 2 && !empty($referencedBy)) {
        // Low reference count — might still be dead
        $relativePath = str_replace($projectRoot . '\\', '', $filePath);
        $relativePath = str_replace($projectRoot . '/', '', $relativePath);
        
        $deadControllers[] = [
            'file' => $relativePath,
            'class' => $className,
            'namespace' => $namespace ?? '',
            'methods' => $publicMethods,
            'reason' => "0 routes, only $refCount cross-ref(s): " . implode(', ', $referencedBy),
            'referencedBy' => $referencedBy,
            'low_ref' => true,
        ];
    }
}

// ============================================================
// PHASE 3: Scan ALL service files
// ============================================================
echo "\n=== PHASE 3: Scanning services ===\n\n";

$serviceDir = $projectRoot . '/app/Services';
$serviceFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($serviceDir)
);
foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') continue;
    $realPath = $file->getRealPath();
    if (strpos($realPath, '_archive') !== false) continue;
    $serviceFiles[] = $realPath;
}

echo "Found " . count($serviceFiles) . " service files\n\n";

$deadServices = [];

foreach ($serviceFiles as $filePath) {
    $content = file_get_contents($filePath);
    
    // Extract class name
    if (!preg_match('/^\s*(?:abstract\s+)?class\s+(\w+)/m', $content, $classMatch)) {
        continue;
    }
    $className = $classMatch[1];
    
    // Skip if class name is too generic (trait files, abstract bases)
    if (preg_match('/^Base|Abstract|Interface|Trait/i', $className)) continue;
    
    $selfFile = basename($filePath);
    
    // Search for references across ENTIRE app/ directory (excluding this file and _archive)
    $cmd = "rg -l \"\\b{$className}\\b\" --include=\"*.php\" --glob=\"!_archive/\" --glob=\"!$selfFile\" \"$projectRoot/app/\" 2>&1";
    $output = shell_exec($cmd);
    
    $referencedBy = [];
    if ($output && trim($output) !== '') {
        $lines = array_filter(explode("\n", trim($output)));
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $referencedBy[] = str_replace($projectRoot . '\\', '', str_replace($projectRoot . '/', '', $line));
            }
        }
    }
    
    if (count($referencedBy) === 0) {
        $relativePath = str_replace($projectRoot . '\\', '', $filePath);
        $relativePath = str_replace($projectRoot . '/', '', $relativePath);
        
        // Extract namespace
        $namespace = '';
        if (preg_match('/^namespace\s+([\w\\\\]+)\s*;/m', $content, $nsMatch)) {
            $namespace = $nsMatch[1];
        }
        
        $deadServices[] = [
            'file' => $relativePath,
            'class' => $className,
            'namespace' => $namespace,
            'reason' => '0 references across entire codebase',
        ];
    }
}

// ============================================================
// PHASE 4: Check routes pointing to non-existent methods
// ============================================================
echo "\n=== PHASE 4: Checking broken route targets ===\n\n";

$brokenRoutes = [];

foreach ($routeTargets as $className => $methods) {
    foreach ($methods as $method) {
        $key = "$className::$method";
        
        // Find the controller file
        $found = false;
        foreach ($controllerFiles as $filePath) {
            $content = file_get_contents($filePath);
            if (preg_match('/class\s+' . preg_quote($className) . '\b/', $content)) {
                if (preg_match('/function\s+' . preg_quote($method) . '\s*\(/', $content)) {
                    $found = true;
                    break;
                }
            }
        }
        
        if (!$found) {
            $brokenRoutes[] = [
                'class' => $className,
                'method' => $method,
                'line' => $routeLines[$key] ?? '?',
            ];
        }
    }
}

// ============================================================
// OUTPUT
// ============================================================

echo "\n" . str_repeat("=", 70) . "\n";
echo "RESULTS SUMMARY\n";
echo str_repeat("=", 70) . "\n\n";

// --- Dead Controllers (0 routes, 0 refs) ---
$zeroRef = array_filter($deadControllers, fn($c) => !isset($c['low_ref']));
$lowRef = array_filter($deadControllers, fn($c) => isset($c['low_ref']));

echo "DEAD CONTROLLERS (0 routes, 0 references): " . count($zeroRef) . "\n";
echo str_repeat("-", 70) . "\n";
foreach ($zeroRef as $d) {
    echo "  FILE: {$d['file']}\n";
    echo "  CLASS: {$d['class']}\n";
    echo "  NAMESPACE: {$d['namespace']}\n";
    echo "  METHODS: " . implode(', ', array_slice($d['methods'], 0, 10));
    if (count($d['methods']) > 10) echo " ...+" . (count($d['methods']) - 10) . " more";
    echo "\n  REASON: {$d['reason']}\n\n";
}

echo "\nLOW-REFERENCE CONTROLLERS (0 routes, few cross-refs): " . count($lowRef) . "\n";
echo str_repeat("-", 70) . "\n";
foreach ($lowRef as $d) {
    echo "  FILE: {$d['file']}\n";
    echo "  CLASS: {$d['class']}\n";
    echo "  REASON: {$d['reason']}\n\n";
}

echo "\n\nDEAD SERVICES (0 references): " . count($deadServices) . "\n";
echo str_repeat("-", 70) . "\n";
foreach ($deadServices as $d) {
    echo "  FILE: {$d['file']}\n";
    echo "  CLASS: {$d['class']}\n";
    echo "  NAMESPACE: {$d['namespace']}\n";
    echo "  REASON: {$d['reason']}\n\n";
}

echo "\n\nBROKEN ROUTES (point to non-existent methods): " . count($brokenRoutes) . "\n";
echo str_repeat("-", 70) . "\n";
foreach ($brokenRoutes as $r) {
    echo "  {$r['class']}@{$r['method']} (line {$r['line']})\n";
}
echo "\n";

// Final summary
echo str_repeat("=", 70) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 70) . "\n";
echo "Total controller files scanned: " . count($controllerFiles) . "\n";
echo "Total service files scanned: " . count($serviceFiles) . "\n";
echo "Total routes checked: " . array_sum(array_map('count', $routeTargets)) . "\n";
echo "Dead controllers (0 routes, 0 refs): " . count($zeroRef) . "\n";
echo "Low-ref controllers (0 routes, few refs): " . count($lowRef) . "\n";
echo "Dead services (0 references): " . count($deadServices) . "\n";
echo "Broken routes: " . count($brokenRoutes) . "\n";
