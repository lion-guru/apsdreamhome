<?php
$projectRoot = dirname(__DIR__);
$routes = file($projectRoot . '/routes/web.php');

foreach ($routes as $i => $line) {
    if (strpos($line, 'AdminController@') !== false && preg_match('/->(?:get|post|put|delete|patch)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([A-Za-z\\\\]+)@([a-zA-Z]+)/', $line, $m)) {
        $class = $m[2];
        $method = $m[3];
        
        echo "Class: " . json_encode($class) . "\n";
        echo "Method: " . json_encode($method) . "\n";
        
        // Check prefix match
        $prefix = 'App\\Http\\Controllers\\';
        echo "Prefix: " . json_encode($prefix) . "\n";
        echo "str_starts_with: " . (str_starts_with($class, $prefix) ? 'YES' : 'NO') . "\n";
        
        // Check byte by byte
        echo "Class bytes: " . bin2hex($class) . "\n";
        echo "Prefix bytes: " . bin2hex($prefix) . "\n";
        
        if (str_starts_with($class, $prefix)) {
            $relativeClass = substr($class, strlen($prefix));
            echo "Relative: " . json_encode($relativeClass) . "\n";
            $file = $projectRoot . '/app/Http/Controllers/' . str_replace('\\', '/', $relativeClass) . '.php';
            echo "File: " . json_encode($file) . "\n";
            echo "Exists: " . (file_exists($file) ? 'YES' : 'NO') . "\n";
        } else {
            echo "PREFIX MISMATCH!\n";
        }
        echo "\n";
    }
}