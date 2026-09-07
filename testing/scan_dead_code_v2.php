<?php
/**
 * Dead Code Scanner v2 — APS Dream Home
 * Usage: php testing/scan_dead_code_v2.php
 */
set_time_limit(600);
$projectRoot = dirname(__DIR__);

// ============================================================
// PHASE 1: Extract all route targets
// ============================================================
echo "=== PHASE 1: Extracting route targets ===\n\n";

$routeFiles = [
    $projectRoot . '/routes/web.php',
    $projectRoot . '/routes/api.php',
];

// Map: short class name => [methods]
$routeTargets = [];
// Map: short class name => full namespace\class
$routeFullNames = [];
// Map: Class::method => [line numbers]
$routeLines = [];

foreach ($routeFiles as $rf) {
    if (!file_exists($rf)) continue;
    $lines = explode("\n", file_get_contents($rf));
    foreach ($lines as $i => $line) {
        $t = trim($line);
        if ($t[0] === '/' && $t[1] === '/') continue; // skip comments
        
        // Match: 'Namespace\\Controller@method'  
        if (preg_match_all("/['\"]([A-Za-z_\\\\]+?)@(\w+)['\"]/", $t, $m)) {
            foreach ($m[0] as $idx) {
                $fullClass = $m[1][$idx];
                $method = $m[2][$idx];
                $parts = explode('\\', $fullClass);
                $shortClass = end($parts);
                $routeTargets[$shortClass][] = $method;
                $routeFullNames[$shortClass] = $fullClass;
                $routeLines["$shortClass::$method"] = ($i + 1);
            }
        }
    }
}

// Deduplicate
foreach ($routeTargets as $c => &$methods) {
    $methods = array_unique($methods);
}
unset($methods);

$totalRoutes = array_sum(array_map('count', $routeTargets));
echo "Found " . count($routeTargets) . " unique classes in routes ($totalRoutes method targets)\n\n";

// ============================================================
// PHASE 2: Collect all controller files and extract class info
// ============================================================
echo "=== PHASE 2: Building controller index ===\n\n";

$controllerDir = $projectRoot . '/app/Http/Controllers';
$controllers = []; // shortName => ['file', 'class', 'namespace', 'methods', 'fqcn']

$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllerDir));
foreach ($iter as $file) {
    if ($file->getExtension() !== 'php') continue;
    $rp = $file->getRealPath();
    if (strpos($rp, '_archive') !== false || strpos($rp, 'Archives') !== false) continue;
    
    $content = file_get_contents($rp);
    if (!preg_match('/^\s*(?:abstract\s+)?class\s+(\w+)/m', $content, $cm)) continue;
    
    $className = $cm[1];
    $namespace = '';
    if (preg_match('/^namespace\s+([\w\\\\]+)\s*;/m', $content, $nm)) {
        $namespace = $nm[1];
    }
    
    // Extract public methods
    preg_match_all('/public\s+(?:static\s+)?function\s+(\w+)\s*\(/', $content, $mm);
    $methods = array_unique($mm[1]);
    
    // Build FQCN for matching
    $fqcn = $namespace ? $namespace . '\\' . $className : $className;
    
    // Relative path
    $relPath = str_replace($projectRoot . '\\', '', str_replace($projectRoot . '/', '', $rp));
    
    $shortLower = strtolower($className);
    $controllers[$shortLower] = [
        'file' => $relPath,
        'class' => $className,
        'namespace' => $namespace,
        'fqcn' => $fqcn,
        'methods' => $methods,
    ];
}

echo "Indexed " . count($controllers) . " controller classes\n\n";

// ============================================================
// PHASE 3: Find dead controllers
// ============================================================
echo "=== PHASE 3: Finding dead controllers ===\n\n";

// Known framework files that are always needed
$frameworkClasses = [
    'basecontroller', 'admincontroller', 'baseapicontroller',
    'controller', 'adminbasecontroller',
];

$deadControllers = [];
$controllersWithNoRoutes = [];

foreach ($controllers as $shortLower => $info) {
    if (in_array($shortLower, $frameworkClasses)) continue;
    
    $className = $info['class'];
    
    // Check if this controller has routes
    $hasRoutes = isset($routeTargets[$className]) && count($routeTargets[$className]) > 0;
    
    if (!$hasRoutes) {
        $controllersWithNoRoutes[$shortLower] = $info;
    }
}

echo count($controllersWithNoRoutes) . " controllers have ZERO routes\n";
echo "Now checking cross-references for each...\n\n";

// For controllers with no routes, check if they're referenced anywhere
foreach ($controllersWithNoRoutes as $shortLower => $info) {
    $className = $info['class'];
    $selfFile = basename($info['file']);
    
    // Search for the class name across all PHP files in app/ (excluding self and archive)
    $cmd = 'rg -l --no-heading "' . $className . '" --glob "*.php" --glob "!_archive/**" --glob "!Archives/**" --glob "!testing/**" "' . $projectRoot . '/app/" 2>&1';
    $output = shell_exec($cmd);
    
    $referencedBy = [];
    if ($output && trim($output) !== '') {
        $lines = array_filter(explode("\n", trim($output)));
        foreach ($lines as $l) {
            $l = trim($l);
            if ($l === '' || basename($l) === $selfFile) continue;
            $referencedBy[] = str_replace([$projectRoot . '\\', $projectRoot . '/'], '', $l);
        }
    }
    
    // Also check routes/ directory for references
    $cmd2 = 'rg -l --no-heading "' . $className . '" --glob "*.php" "' . $projectRoot . '/routes/" 2>&1';
    $output2 = shell_exec($cmd2);
    if ($output2 && trim($output2) !== '') {
        $lines2 = array_filter(explode("\n", trim($output2)));
        foreach ($lines2 as $l) {
            $l = trim($l);
            if ($l !== '') {
                $referencedBy[] = 'routes/' . basename($l);
            }
        }
    }
    
    $referencedBy = array_unique($referencedBy);
    
    if (count($referencedBy) === 0) {
        $deadControllers[] = array_merge($info, [
            'reason' => '0 routes in web.php/api.php, 0 cross-references from any file',
            'referencedBy' => [],
        ]);
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "DEAD CONTROLLERS (0 routes, 0 cross-references): " . count($deadControllers) . "\n";
echo str_repeat("=", 70) . "\n\n";

foreach ($deadControllers as $d) {
    echo "FILE: {$d['file']}\n";
    echo "CLASS: {$d['class']}\n";
    echo "FQCN: {$d['fqcn']}\n";
    echo "PUBLIC METHODS (" . count($d['methods']) . "): " . implode(', ', array_slice($d['methods'], 0, 15));
    if (count($d['methods']) > 15) echo " ...+" . (count($d['methods']) - 15) . " more";
    echo "\n";
    echo "REASON: {$d['reason']}\n\n";
}

// ============================================================
// PHASE 4: Find dead services
// ============================================================
echo "\n=== PHASE 4: Finding dead services ===\n\n";

$serviceDir = $projectRoot . '/app/Services';
$deadServices = [];

$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($serviceDir));
$serviceFiles = [];
foreach ($iter as $file) {
    if ($file->getExtension() !== 'php') continue;
    $rp = $file->getRealPath();
    if (strpos($rp, '_archive') !== false || strpos($rp, 'Archives') !== false) continue;
    $serviceFiles[] = $rp;
}

echo "Scanning " . count($serviceFiles) . " service files for references...\n";

foreach ($serviceFiles as $rp) {
    $content = file_get_contents($rp);
    if (!preg_match('/^\s*(?:abstract\s+)?class\s+(\w+)/m', $content, $cm)) continue;
    
    $className = $cm[1];
    $namespace = '';
    if (preg_match('/^namespace\s+([\w\\\\]+)\s*;/m', $content, $nm)) {
        $namespace = $nm[1];
    }
    
    // Skip very generic names that would have too many false positives
    if (in_array(strtolower($className), ['base', 'abstract', 'interface', 'trait', 'helper'])) continue;
    
    $selfFile = basename($rp);
    $relPath = str_replace([$projectRoot . '\\', $projectRoot . '/'], '', $rp);
    
    // Search for class name references across the ENTIRE project (app/ + routes/ + public/ + scripts/)
    $cmd = 'rg -l --no-heading "' . $className . '" --glob "*.php" --glob "!_archive/**" --glob "!Archives/**" --glob "!testing/**" --glob "!' . $selfFile . '" "' . $projectRoot . '/app/" 2>&1';
    $output = shell_exec($cmd);
    
    $refCount = 0;
    $referencedBy = [];
    if ($output && trim($output) !== '') {
        $lines = array_filter(explode("\n", trim($output)));
        $refCount = count($lines);
        foreach ($lines as $l) {
            $l = trim($l);
            if ($l !== '') {
                $referencedBy[] = str_replace([$projectRoot . '\\', $projectRoot . '/'], '', $l);
            }
        }
    }
    
    // Also check routes, scripts, public
    $cmd2 = 'rg -l --no-heading "' . $className . '" --glob "*.php" "' . $projectRoot . '/routes/" "' . $projectRoot . '/scripts/" "' . $projectRoot . '/public/" 2>&1';
    $output2 = shell_exec($cmd2);
    if ($output2 && trim($output2) !== '') {
        $lines2 = array_filter(explode("\n", trim($output2)));
        $refCount += count($lines2);
        foreach ($lines2 as $l) {
            $l = trim($l);
            if ($l !== '') {
                $referencedBy[] = str_replace([$projectRoot . '\\', $projectRoot . '/'], '', $l);
            }
        }
    }
    
    $referencedBy = array_unique($referencedBy);
    
    if (count($referencedBy) === 0) {
        $fqcn = $namespace ? $namespace . '\\' . $className : $className;
        $deadServices[] = [
            'file' => $relPath,
            'class' => $className,
            'namespace' => $namespace,
            'fqcn' => $fqcn,
            'reason' => '0 references from any PHP file in the project',
        ];
    }
}

echo str_repeat("=", 70) . "\n";
echo "DEAD SERVICES (0 references): " . count($deadServices) . "\n";
echo str_repeat("=", 70) . "\n\n";

foreach ($deadServices as $d) {
    echo "FILE: {$d['file']}\n";
    echo "CLASS: {$d['class']}\n";
    echo "FQCN: {$d['fqcn']}\n";
    echo "REASON: {$d['reason']}\n\n";
}

// ============================================================
// PHASE 5: Find broken routes (point to non-existent methods)
// ============================================================
echo "\n=== PHASE 5: Checking broken routes ===\n\n";

$brokenRoutes = [];

foreach ($routeTargets as $className => $methods) {
    $shortLower = strtolower($className);
    
    if (!isset($controllers[$shortLower])) {
        // Controller class doesn't exist at all
        foreach ($methods as $method) {
            $key = "$className::$method";
            $brokenRoutes[] = [
                'class' => $className,
                'method' => $method,
                'line' => $routeLines[$key] ?? '?',
                'reason' => 'Controller class not found in app/Http/Controllers/',
            ];
        }
        continue;
    }
    
    $controllerInfo = $controllers[$shortLower];
    foreach ($methods as $method) {
        if (!in_array($method, $controllerInfo['methods'])) {
            $key = "$className::$method";
            $brokenRoutes[] = [
                'class' => $className,
                'method' => $method,
                'line' => $routeLines[$key] ?? '?',
                'reason' => "Method '$method' does not exist in {$controllerInfo['class']} (file: {$controllerInfo['file']})",
            ];
        }
    }
}

echo str_repeat("=", 70) . "\n";
echo "BROKEN ROUTES (point to non-existent methods/classes): " . count($brokenRoutes) . "\n";
echo str_repeat("=", 70) . "\n\n";

foreach ($brokenRoutes as $r) {
    echo "Route: {$r['class']}@{$r['method']} (line {$r['line']})\n";
    echo "  REASON: {$r['reason']}\n\n";
}

// ============================================================
// FINAL SUMMARY
// ============================================================
echo "\n" . str_repeat("=", 70) . "\n";
echo "FINAL SUMMARY\n";
echo str_repeat("=", 70) . "\n";
echo "Controller files scanned: " . count($controllers) . "\n";
echo "Service files scanned: " . count($serviceFiles) . "\n";
echo "Routes checked: $totalRoutes\n";
echo "Controllers with 0 routes: " . count($controllersWithNoRoutes) . "\n";
echo "DEAD controllers (0 routes, 0 refs): " . count($deadControllers) . "\n";
echo "DEAD services (0 references): " . count($deadServices) . "\n";
echo "Broken routes: " . count($brokenRoutes) . "\n";
echo str_repeat("=", 70) . "\n";
