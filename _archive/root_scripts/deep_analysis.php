<?php
// Deep Analysis Script
echo "=== APS DREAM HOME - DEEP ANALYSIS ===\n\n";

$routesFile = __DIR__ . '/routes/web.php';
$routesContent = file_get_contents($routesFile);

// Extract all controller references
preg_match_all("/'([^\\\\']+)\\\\([a-zA-Z0-9_]+)@([a-zA-Z0-9_]+)'/", $routesContent, $matches);

$controllers = [];
$issues = [];

foreach ($matches[1] as $i => $namespace) {
    $class = $matches[2][$i];
    $method = $matches[3][$i];
    
    $fullClass = rtrim($namespace, '\\') . '\\' . $class;
    $filePath = __DIR__ . '/app/Http/Controllers/' . str_replace('\\', '/', $fullClass) . '.php';
    
    // Also check alternate paths
    $alternatePaths = [
        __DIR__ . '/app/Http/Controllers/' . $class . '.php',
        __DIR__ . '/app/Http/Controllers/' . str_replace('\\', '/', $fullClass) . '.php',
        __DIR__ . '/app/' . str_replace('\\', '/', $fullClass) . '.php',
    ];
    
    $found = false;
    $actualPath = '';
    foreach ($alternatePaths as $path) {
        if (file_exists($path)) {
            $found = true;
            $actualPath = $path;
            break;
        }
    }
    
    if (!$found) {
        $issues[] = [
            'controller' => $fullClass,
            'method' => $method,
            'issue' => 'CONTROLLER FILE NOT FOUND',
            'path' => $actualPath ?: $filePath
        ];
    } else {
        // Check if method exists
        $content = file_get_contents($actualPath);
        if (!preg_match("/function\s+" . $method . "\s*\(/", $content)) {
            $issues[] = [
                'controller' => $fullClass,
                'method' => $method,
                'issue' => 'METHOD NOT FOUND IN CONTROLLER',
                'path' => $actualPath
            ];
        }
    }
}

echo "Total Routes: " . count($matches[0]) . "\n";
echo "Controllers Found: " . (count($matches[0]) - count($issues)) . "\n";
echo "Issues Found: " . count($issues) . "\n\n";

if (count($issues) > 0) {
    echo "=== ISSUES ===\n\n";
    foreach ($issues as $issue) {
        echo "❌ " . $issue['controller'] . "@" . $issue['method'] . "\n";
        echo "   Issue: " . $issue['issue'] . "\n";
        echo "   Path: " . $issue['path'] . "\n\n";
    }
} else {
    echo "✅ ALL ROUTES CONNECTED!\n";
}

echo "\n=== SUMMARY BY CONTROLLER ===\n";
$byController = [];
foreach ($issues as $issue) {
    $c = $issue['controller'];
    if (!isset($byController[$c])) {
        $byController[$c] = 0;
    }
    $byController[$c]++;
}

arsort($byController);
foreach ($byController as $controller => $count) {
    echo "- $controller: $count issues\n";
}
