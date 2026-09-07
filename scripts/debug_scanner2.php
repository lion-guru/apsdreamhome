<?php
$projectRoot = dirname(__DIR__);
$routes = file($projectRoot . '/routes/web.php');

foreach ($routes as $i => $line) {
    if (strpos($line, 'AdminController@') !== false && strpos($line, '->get') !== false) {
        echo "Line $i: " . trim($line) . "\n";
        
        $pattern = '/->(?:get|post|put|delete|patch)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([A-Za-z\\\\]+)@([a-zA-Z]+)/';
        if (preg_match($pattern, $line, $m)) {
            echo "  MATCH: Route={$m[1]}, Class={$m[2]}, Method={$m[3]}\n";
            
            $class = $m[2];
            if (str_starts_with($class, 'App\\Http\\Controllers\\')) {
                $relativeClass = substr($class, strlen('App\\Http\\Controllers\\'));
                $file = $projectRoot . '/app/Http/Controllers/' . str_replace('\\', '/', $relativeClass) . '.php';
                echo "  File: $file\n";
                echo "  Exists: " . (file_exists($file) ? "YES" : "NO") . "\n";
            }
            echo "\n";
        } else {
            echo "  NO MATCH\n\n";
        }
        
        // Just check first 5 matches
        if ($i > 20) break;
    }
}