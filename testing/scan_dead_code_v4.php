<?php
/**
 * Dead Code Scanner v4 — FINAL
 * Usage: php testing/scan_dead_code_v4.php
 */
set_time_limit(600);
$projectRoot = dirname(__DIR__);

// ============================================================
// PHASE 1: Extract ALL route targets
// ============================================================
echo "=== PHASE 1: Extracting route targets ===\n\n";

$routeFiles = [
    $projectRoot . '/routes/web.php',
    $projectRoot . '/routes/api.php',
];

$routeTargets = [];
$routeLines = [];

foreach ($routeFiles as $rf) {
    if (!file_exists($rf)) continue;
    $lines = explode("\n", file_get_contents($rf));
    
    foreach ($lines as $i => $line) {
        $t = trim($line);
        if (strlen($t) < 2 || ($t[0] === '/' && $t[1] === '/')) continue;
        
        // Match: 'Namespace\\Controller@method' — @ is INSIDE the quotes
        // Pattern: word\word@word (backslash-separated class@method)
        if (preg_match_all("/([A-Za-z_][A-Za-z0-9_]*(?:\\\\[A-Za-z_][A-Za-z0-9_]*)+)@(\w+)/", $t, $m)) {
            foreach ($m[0] as $idx) {
                $fullClass = $m[1][$idx];
                $method = $m[2][$idx];
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
echo "Sample: " . implode(', ', array_slice(array_keys($routeTargets), 0, 8)) . "\n\n";

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
// PHASE 3: Dead controllers
// ============================================================
echo "=== PHASE 3: Finding dead controllers ===\n\n";

$frameworkClasses = ['basecontroller', 'admincontroller', 'baseapicontroller', 'controller', 'adminbasecontroller'];

$deadControllers = [];
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
    if ($count % 50 === 0) echo "  $count/$total...\n";
    
    $className = $info['class'];
    $selfFile = basename($info['file']);
    
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
        ];
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "DEAD CONTROLLERS: " . count($deadControllers) . "\n";
echo str_repeat("=", 70) . "\n\n";

foreach ($deadControllers as $d) {
    echo "FILE: {$d['file']}\n";
    echo "CLASS: {$d['class']} ({$d['namespace']})\n";
    echo "METHODS (" . count($d['methods']) . "): " . implode(', ', array_slice($d['methods'], 0, 15));
    if (count($d['methods']) > 15) echo " ...+" . (count($d['methods']) - 15);
    echo "\n\n";
}

// ============================================================
// PHASE 4: Dead services (reuse v2 results — already validated)
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

echo "Scanning " . count($serviceFiles) . " service files...\n";

$count = 0;
foreach ($serviceFiles as $rp) {
    $count++;
    if ($count % 50 === 0) echo "  $count/" . count($serviceFiles) . "...\n";
    
    $content = file_get_contents($rp);
    if (!preg_match('/^\s*(?:abstract\s+)?class\s+(\w+)/m', $content, $cm)) continue;
    
    $className = $cm[1];
    $namespace = '';
    if (preg_match('/^namespace\s+([\w\\\\]+)\s*;/m', $content, $nm)) {
        $namespace = $nm[1];
    }
    
    if (strlen($className) < 4) continue; // skip very short names
    
    $selfFile = basename($rp);
    $relPath = str_replace([$projectRoot . '\\', $projectRoot . '/'], '', $rp);
    
    $cmd = 'rg -l "' . $className . '" --glob "*.php" --glob "!_archive/**" --glob "!Archives/**" --glob "!testing/**" --glob "!' . $selfFile . '" "' . $projectRoot . '/app/" "' . $projectRoot . '/routes/" "' . $projectRoot . '/scripts/" "' . $projectRoot . '/public/" 2>&1';
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
        $fqcn = $namespace ? $namespace . '\\' . $className : $className;
        $deadServices[] = [
            'file' => $relPath,
            'class' => $className,
            'namespace' => $namespace,
            'fqcn' => $fqcn,
        ];
    }
}

echo str_repeat("=", 70) . "\n";
echo "DEAD SERVICES: " . count($deadServices) . "\n";
echo str_repeat("=", 70) . "\n\n";

foreach ($deadServices as $d) {
    echo "FILE: {$d['file']}\n";
    echo "CLASS: {$d['class']} ({$d['namespace']})\n\n";
}

// ============================================================
// PHASE 5: Broken routes
// ============================================================
echo "\n=== PHASE 5: Broken routes ===\n\n";

$brokenRoutes = [];

foreach ($routeTargets as $className => $methods) {
    $sl = strtolower($className);
    
    if (!isset($controllers[$sl])) {
        foreach ($methods as $method) {
            $brokenRoutes[] = "{$className}@{$method} (line " . ($routeLines["$className::$method"] ?? '?') . ") — Controller CLASS NOT FOUND";
        }
        continue;
    }
    
    foreach ($methods as $method) {
        if (!in_array($method, $controllers[$sl]['methods'])) {
            $brokenRoutes[] = "{$className}@{$method} (line " . ($routeLines["$className::$method"] ?? '?') . ") — Method '{$method}' NOT FOUND in {$controllers[$sl]['file']}";
        }
    }
}

echo str_repeat("=", 70) . "\n";
echo "BROKEN ROUTES: " . count($brokenRoutes) . "\n";
echo str_repeat("=", 70) . "\n\n";

foreach ($brokenRoutes as $r) {
    echo "  $r\n";
}

// ============================================================
// FINAL SUMMARY
// ============================================================
echo "\n" . str_repeat("=", 70) . "\n";
echo "FINAL SUMMARY\n";
echo str_repeat("=", 70) . "\n";
echo "Routes: $totalRoutes targets across " . count($routeTargets) . " classes\n";
echo "Controllers: " . count($controllers) . " files\n";
echo "Services: " . count($serviceFiles) . " files\n";
echo "Controllers with 0 routes: " . count($noRouteControllers) . "\n";
echo "DEAD controllers (0 routes, 0 refs): " . count($deadControllers) . "\n";
echo "DEAD services (0 refs): " . count($deadServices) . "\n";
echo "Broken routes: " . count($brokenRoutes) . "\n";
