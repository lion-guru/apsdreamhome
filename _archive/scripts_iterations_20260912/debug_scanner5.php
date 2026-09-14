<?php
$projectRoot = dirname(__DIR__);
$routes = file($projectRoot . '/routes/web.php');

foreach ($routes as $i => $line) {
    if (strpos($line, 'AdminController@erpOverview') !== false) {
        echo "Found line $i:\n";
        echo "Raw: " . json_encode($line) . "\n";
        echo "Trimmed: " . json_encode(trim($line)) . "\n";
        
        $pattern = '/->(?:get|post|put|delete|patch)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([A-Za-z\\\\]+)@([a-zA-Z]+)/';
        if (preg_match($pattern, $line, $m)) {
            echo "MATCHED!\n";
            echo "  Route: " . json_encode($m[1]) . "\n";
            echo "  Class: " . json_encode($m[2]) . "\n";
            echo "  Method: " . json_encode($m[3]) . "\n";
            
            $class = $m[2];
            if (str_starts_with($class, 'App\\Http\\Controllers\\')) {
                $relativeClass = substr($class, strlen('App\\Http\\Controllers\\'));
                $file = $projectRoot . '/app/Http/Controllers/' . str_replace('\\', '/', $relativeClass) . '.php';
                echo "  Relative: " . json_encode($relativeClass) . "\n";
                echo "  File: " . json_encode($file) . "\n";
                echo "  Exists: " . (file_exists($file) ? "YES" : "NO") . "\n";
            }
        } else {
            echo "NO MATCH!\n";
        }
        break;
    }
}