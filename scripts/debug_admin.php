<?php
$projectRoot = dirname(__DIR__);

// Test the specific class that's reported missing but we know exists
$class = 'App\Http\Controllers\Admin\AdminController';
$method = 'erpOverview';
$route = '/admin/erp';

echo "Testing: $class@$method => $route\n";

if (str_starts_with($class, 'App\Http\Controllers\\')) {
    $relativeClass = substr($class, strlen('App\Http\Controllers\\'));
    $file = $projectRoot . '/app/Http/Controllers/' . str_replace('\\', '/', $relativeClass) . '.php';
} else {
    $file = $projectRoot . '/app/Http/Controllers/' . $class . '.php';
}

echo "File path: $file\n";
echo "File exists: " . (file_exists($file) ? "YES" : "NO") . "\n";

if (file_exists($file)) {
    $content = file_get_contents($file);
    $hasMethod = strpos($content, 'function ' . $method) !== false;
    echo "Has method '$method': " . ($hasMethod ? "YES" : "NO") . "\n";
    
    // Check what methods exist
    preg_match_all('/function\s+(\w+)/', $content, $matches);
    echo "Methods found: " . implode(', ', array_slice($matches[1], 0, 10)) . "...\n";
}