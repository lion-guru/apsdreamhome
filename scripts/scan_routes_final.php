<?php
$projectRoot = dirname(__DIR__);
$routes = file($projectRoot . '/routes/web.php');
$missing = 0;
$total = 0;
$missingClasses = [];

// Prefix with DOUBLE backslashes to match captured format
$prefix = 'App\\\\Http\\\\Controllers\\\\';

foreach ($routes as $line) {
    if (preg_match('/->(?:get|post|put|delete|patch)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([A-Za-z\\\\]+)@([a-zA-Z]+)/', $line, $m)) {
        $route = $m[1];
        $class = $m[2];
        $method = $m[3];
        $total++;
        
        // Normalize: replace double backslashes with single for file path
        $normalizedClass = str_replace('\\\\', '\\', $class);
        
        if (str_starts_with($normalizedClass, 'App\\Http\\Controllers\\')) {
            $relativeClass = substr($normalizedClass, strlen('App\\Http\\Controllers\\'));
            $file = $projectRoot . '/app/Http/Controllers/' . str_replace('\\', '/', $relativeClass) . '.php';
        } else {
            $file = $projectRoot . '/app/Http/Controllers/' . $class . '.php';
        }
        
        if (!file_exists($file)) {
            $missingClasses[$class] = true;
            echo "MISSING CLASS: {$class}@{$method} => {$route}\n";
            $missing++;
            continue;
        }
        
        $content = file_get_contents($file);
        if (strpos($content, 'function ' . $method) === false) {
            echo "MISSING METHOD: {$class}@{$method} => {$route}\n";
            $missing++;
        }
    }
}

echo "\nTotal routes scanned: {$total}\n";
echo "Missing: {$missing}\n";
echo "Unique missing classes: " . count($missingClasses) . "\n";
if (count($missingClasses) > 0) {
    echo "Classes:\n";
    foreach (array_keys($missingClasses) as $cls) {
        echo "  - {$cls}\n";
    }
}