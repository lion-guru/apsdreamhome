<?php
/**
 * Dead Code Scanner v3 — Fix route parsing
 * Usage: php testing/scan_dead_code_v3.php
 */
set_time_limit(600);
$projectRoot = dirname(__DIR__);

// ============================================================
// PHASE 1: Extract ALL route targets properly
// ============================================================
echo "=== PHASE 1: Extracting route targets ===\n\n";

$routeFiles = [
    $projectRoot . '/routes/web.php',
    $projectRoot . '/routes/api.php',
];

$routeTargets = []; // short class => [methods]
$routeLines = [];   // shortClass::method => line

foreach ($routeFiles as $rf) {
    if (!file_exists($rf)) continue;
    $content = file_get_contents($rf);
    $lines = explode("\n", $content);
    
    foreach ($lines as $i => $line) {
        $t = trim($line);
        if (strlen($t) < 2 || ($t[0] === '/' && $t[1] === '/')) continue;
        
        // Match all 'Something@method' patterns
        if (preg_match_all("/'([^'\\\\]*(?:\\\\.[^'\\\\]*)*)'@(\w+)/", $t, $m)) {
            foreach ($m[1] as $idx) {
                $fullClass = $m[1][$idx];
                $method = $m[2][$idx];
                // Unescape PHP string escapes
                $fullClass = str_replace('\\\\', '\\', $fullClass);
                $parts = explode('\\', $fullClass);
                $shortClass = end($parts);
                if (!isset($routeTargets[$shortClass])) $routeTargets[$shortClass] = [];
                $routeTargets[$shortClass][] = $method;
                $routeLines["$shortClass::$method"] = ($i + 1);
            }
        }
        
        // Also match double-quoted strings
        if (preg_match_all('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"@(\w+)/', $t, $m)) {
            foreach ($m[1] as $idx) {
                $fullClass = $m[1][$idx];
                $method = $m[2][$idx];
                $fullClass = str_replace('\\\\', '\\', $fullClass);
                $parts = explode('\\', $fullClass);
                $shortClass = end($parts);
                if (!isset($routeTargets[$shortClass])) $routeTargets[$shortClass] = [];
                $routeTargets[$shortClass][] = $method;
                $routeLines["$shortClass::$method"] = ($i + 1);
            }
        }
    }
}

foreach ($routeTargets as $c => &$methods) {
    $methods = array_unique($methods);
}
unset($methods);

$totalRoutes = array_sum(array_map('count', $routeTargets));
echo "Found " . count($routeTargets) . " unique controller classes in routes ($totalRoutes method targets)\n";
echo "Sample classes: " . implode(', ', array_slice(array_keys($routeTargets), 0, 10)) . "\n\n";

// ============================================================
// PHASE 2: Build controller index
// ============================================================
echo "=== PHASE 2: Building controller index ===\n\n";

$controllerDir = $projectRoot . '/app/Http/Controllers';
$controllers = [];

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
    
    preg_match_all('/public\s+(?:static\s+)?function\s+(\w+)\s*\(/', $content, $mm);
    $methods = array_unique($mm[1]);
    
    $relPath = str_replace([$projectRoot . '\\', $projectRoot . '/'], '', $rp);
    
    $controllers[strtolower($className)] = [
        'file' => $relPath,
        'class' => $className,
        'namespace' => $namespace,
        'methods' => $methods,
    ];
}

echo "Indexed " . count($controllers) . " controller classes\n\n";

// ============================================================
// PHASE 3: Find dead controllers
// ============================================================
echo "=== PHASE 3: Finding dead controllers (0 routes + 0 refs) ===\n\n";

$frameworkClasses = ['basecontroller', 'admincontroller', 'baseapicontroller', 'controller', 'adminbasecontroller'];

$deadControllers = [];

// Build list of controllers with 0 routes
$noRouteControllers = [];
foreach ($controllers as $sl => $info) {
    if (in_array($sl, $frameworkClasses)) continue;
    if (!isset($routeTargets[$info['class']]) || count($routeTargets[$info['class']]) === 0) {
        $noRouteControllers[$sl] = $info;
    }
}
echo count($noRouteControllers) . " controllers have 0 routes. Checking cross-references...\n";

$count = 0;
$total = count($noRouteControllers);
foreach ($noRouteControllers as $sl => $info) {
    $count++;
    if ($count % 50 === 0) echo "  Processing $count/$total...\n";
    
    $className = $info['class'];
    $selfFile = basename($info['file']);
    
    // Use rg for fast string search
    $cmd = 'rg -l "' . $className . '" --glob "*.php" --glob "!_archive/**" --glob "!Archives/**" --glob "!testing/**" --glob "!' . $selfFile . '" "' . $projectRoot . '/app/" "' . $projectRoot . '/routes/" 2>&1';
    $output = shell_exec($cmd);
    
    $referencedBy = [];
    if ($output && trim($output) !== '') {
        $lines = array_filter(explode("\n", trim($output)));
        foreach ($lines as $l) {
            $l = trim($l);
            if ($l !== '') {
                $referencedBy[] = str_replace([$projectRoot . '\\', $projectRoot . '/'], '', $l);
            }
        }
    }
    
    if (count($referencedBy) === 0) {
        $deadControllers[] = [
            'file' => $info['file'],
            'class' => $className,
            'namespace' => $info['namespace'],
            'methods' => $info['methods'],
            'reason' => '0 routes, 0 cross-references',
        ];
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "DEAD CONTROLLERS (0 routes, 0 cross-references): " . count($deadControllers) . "\n";
echo str_repeat("=", 70) . "\n\n";

foreach ($deadControllers as $d) {
    echo "FILE: {$d['file']}\n";
    echo "CLASS: {$d['class']}\n";
    echo "NAMESPACE: {$d['namespace']}\n";
    echo "PUBLIC METHODS (" . count($d['methods']) . "): " . implode(', ', array_slice($d['methods'], 0, 15));
    if (count($d['methods']) > 15) echo " ...+" . (count($d['methods']) - 15);
    echo "\n\n";
}

// ============================================================
// PHASE 4: Find broken routes
// ============================================================
echo "\n=== PHASE 4: Finding broken routes ===\n\n";

$brokenRoutes = [];

foreach ($routeTargets as $className => $methods) {
    $sl = strtolower($className);
    
    if (!isset($controllers[$sl])) {
        foreach ($methods as $method) {
            $brokenRoutes[] = [
                'class' => $className,
                'method' => $method,
                'line' => $routeLines["$className::$method"] ?? '?',
                'reason' => 'Controller class NOT FOUND in app/Http/Controllers/',
            ];
        }
        continue;
    }
    
    $ctrlMethods = $controllers[$sl]['methods'];
    foreach ($methods as $method) {
        if (!in_array($method, $ctrlMethods)) {
            $brokenRoutes[] = [
                'class' => $className,
                'method' => $method,
                'line' => $routeLines["$className::$method"] ?? '?',
                'reason' => "Method '{$method}' not found in {$className} (file: {$controllers[$sl]['file']})",
            ];
        }
    }
}

echo str_repeat("=", 70) . "\n";
echo "BROKEN ROUTES: " . count($brokenRoutes) . "\n";
echo str_repeat("=", 70) . "\n\n";

foreach ($brokenRoutes as $r) {
    echo "ROUTE: {$r['class']}@{$r['method']} (line {$r['line']})\n";
    echo "  REASON: {$r['reason']}\n\n";
}

// ============================================================
// SUMMARY
// ============================================================
echo "\n" . str_repeat("=", 70) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 70) . "\n";
echo "Routes parsed: $totalRoutes across " . count($routeTargets) . " controller classes\n";
echo "Controller files: " . count($controllers) . "\n";
echo "Controllers with 0 routes: " . count($noRouteControllers) . "\n";
echo "DEAD controllers (0 routes, 0 refs): " . count($deadControllers) . "\n";
echo "Broken routes: " . count($brokenRoutes) . "\n";
