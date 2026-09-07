<?php
/**
 * Dead Code Scanner v5 — Fast batch approach
 * 1. Extract all controller classes from routes (parse double-backslash bytes)
 * 2. Check files exist
 * 3. Batch rg call: count ALL references across codebase in ONE pass
 * 4. Subtract route file refs + self-refs = dead
 */

$root = dirname(__DIR__);
$routeFiles = [$root . '/routes/web.php', $root . '/routes/api.php'];

echo "=== STEP 1: Extract controllers from routes ===\n";

$allRouteClasses = [];

foreach ($routeFiles as $routeFile) {
    $content = file_get_contents($routeFile);
    $lines = explode("\n", $content);
    $fname = basename($routeFile);
    
    foreach ($lines as $i => $line) {
        if (preg_match_all("/'([^'\\\\]*(?:\\\\\\\\[^'\\\\]*)*)@(\w+)'/", $line, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $fullClassRaw = $m[1];
                $method = $m[2];
                
                // Convert double-backslash (0x5c5c) separators to /
                $normalized = preg_replace('/\\\\\\\\/', '/', $fullClassRaw);
                // Strip App/Http/Controllers/ prefix
                $normalized = preg_replace('#^App/Http/Controllers/#', '', $normalized);
                
                if (!isset($allRouteClasses[$normalized])) {
                    $allRouteClasses[$normalized] = [
                        'raw' => $fullClassRaw,
                        'file' => $fname,
                        'methods' => [],
                    ];
                }
                $allRouteClasses[$normalized]['methods'][] = $method;
            }
        }
    }
}

foreach ($allRouteClasses as &$info) {
    $info['methods'] = array_unique($info['methods']);
}
unset($info);

echo "Found " . count($allRouteClasses) . " unique controller classes in routes\n\n";

echo "=== STEP 2: Map to actual files ===\n";

$found = [];
$notFound = [];

foreach ($allRouteClasses as $class => $info) {
    $relativePath = $class . '.php';
    $fullPath = $root . '/app/Http/Controllers/' . $relativePath;
    
    if (file_exists($fullPath)) {
        $lineCount = count(file($fullPath));
        $found[$class] = [
            'path' => $relativePath,
            'fullPath' => $fullPath,
            'methods' => $info['methods'],
            'source' => $info['file'],
            'lineCount' => $lineCount,
        ];
    } else {
        $notFound[$class] = [
            'expected' => $relativePath,
            'methods' => $info['methods'],
            'source' => $info['file'],
        ];
    }
}

echo "Files found: " . count($found) . "\n";
echo "Files NOT found (broken routes): " . count($notFound) . "\n\n";

if (count($notFound) > 0) {
    echo "--- BROKEN ROUTES ---\n";
    foreach ($notFound as $class => $info) {
        echo "  {$class} ({$info['source']})\n";
        echo "    Expected: {$info['expected']}\n";
        echo "    Methods: " . implode(', ', $info['methods']) . "\n\n";
    }
}

echo "\n=== STEP 3: Cross-reference for dead controllers ===\n";

// Build short class names for rg search (Admin/ColonyController -> ColonyController)
$shortNames = [];
foreach ($found as $class => $info) {
    $parts = explode('/', $class);
    $shortName = end($parts);
    $shortNames[$class] = $shortName;
}

// Build a SINGLE rg pattern that matches ALL short class names
// This way we do ONE rg call instead of 350
$shortNameList = array_values(array_unique($shortNames));
// rg -o with alternation: find each match with filename
$pattern = implode('|', array_map(function($n) { return preg_quote($n, '/'); }, $shortNameList));

echo "Running single batch rg scan across all PHP files...\n";

// Use rg to count all references, output as count per file
$cmd = "rg -o \"$pattern\" --no-filename --type php \"$root/app\" 2>nul";
$allMatches = shell_exec($cmd);
$matchLines = explode("\n", trim($allMatches));

// Count total occurrences of each short name across ALL app/ PHP files
$totalRefs = [];
foreach ($shortNameList as $name) {
    $totalRefs[$name] = 0;
}
foreach ($matchLines as $line) {
    $line = trim($line);
    if (isset($totalRefs[$line])) {
        $totalRefs[$line]++;
    }
}

echo "Total references counted. Now subtracting route-file + self refs...\n\n";

// Now count references in route files and in the controller file itself
$deadControllers = [];
$baseClasses = ['BaseController', 'AdminController', 'BaseApiController', 'Controller', 'AdminBaseController'];

foreach ($found as $class => $info) {
    $shortName = $shortNames[$class];
    
    // Skip base classes
    if (in_array($shortName, $baseClasses)) {
        continue;
    }
    
    // Skip very short names that would have many false positives (e.g., "AI")
    if (strlen($shortName) < 8) {
        continue;
    }
    
    $totalInApp = $totalRefs[$shortName] ?? 0;
    
    // Count references in route files
    $routeCmd = "rg -c \"$shortName\" \"$root/routes\" --type php 2>nul";
    $routeCount = intval(trim(shell_exec($routeCmd) ?: '0'));
    
    // Count references in the controller file itself (self-references)
    $selfCmd = "rg -c \"$shortName\" \"{$info['fullPath']}\" 2>nul";
    $selfCount = intval(trim(shell_exec($selfCmd) ?: '0'));
    
    // External references = total - routes - self
    $externalRefs = $totalInApp - $routeCount - $selfCount;
    
    if ($externalRefs <= 0) {
        $deadControllers[$class] = [
            'path' => $info['path'],
            'methods' => $info['methods'],
            'lineCount' => $info['lineCount'],
            'source' => $info['source'],
            'totalRefs' => $totalInApp,
            'routeRefs' => $routeCount,
            'selfRefs' => $selfCount,
        ];
    }
}

echo "Dead controllers found: " . count($deadControllers) . "\n\n";

if (count($deadControllers) > 0) {
    uasort($deadControllers, function($a, $b) { return $b['lineCount'] - $a['lineCount']; });
    
    echo "=== DEAD CONTROLLERS (0 external references) ===\n\n";
    $totalLines = 0;
    foreach ($deadControllers as $class => $info) {
        echo "  {$class}\n";
        echo "    File: {$info['path']} ({$info['lineCount']} lines)\n";
        echo "    Route methods: " . implode(', ', $info['methods']) . "\n";
        echo "    Refs: total={$info['totalRefs']}, routes={$info['routeRefs']}, self={$info['selfRefs']}\n\n";
        $totalLines += $info['lineCount'];
    }
    echo "Total dead lines: {$totalLines}\n";
}
