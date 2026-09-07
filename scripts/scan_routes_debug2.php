<?php
$projectRoot = dirname(__DIR__);
$routes = file($projectRoot . '/routes/web.php');
$missing = 0;
$total = 0;
$missingClasses = [];

foreach ($routes as $i => $line) {
    if (preg_match('/->(?:get|post|put|delete|patch)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([A-Za-z\\\\]+)@([a-zA-Z]+)/', $line, $m)) {
        $route = $m[1];
        $class = $m[2];
        $method = $m[3];
        $total++;
        
        // DEBUG: Check AdminController specifically
        if ($class === 'App\\Http\\Controllers\\Admin\\AdminController') {
            echo "DEBUG AdminController: route=$route, class=$class, method=$method\n";
            if (str_starts_with($class, 'App\\Http\\Controllers\\')) {
                $relativeClass = substr($class, strlen('App\\Http\\Controllers\\'));
                $file = $projectRoot . '/app/Http/Controllers/' . str_replace('\\', '/', $relativeClass) . '.php';
                echo "DEBUG: relativeClass=$relativeClass, file=$file, exists=" . (file_exists($file) ? 'YES' : 'NO') . "\n";
            }
        }
        
        if (str_starts_with($class, 'App\\Http\\Controllers\\')) {
            $relativeClass = substr($class, strlen('App\\Http\\Controllers\\'));
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