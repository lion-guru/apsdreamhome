<?php
$projectRoot = dirname(__DIR__);
$routes = file($projectRoot . '/routes/web.php');
$missing = 0;
$total = 0;

foreach ($routes as $line) {
    if (preg_match('/->(?:get|post|put|delete|patch)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([A-Za-z\\\\]+)@([a-zA-Z]+)/', $line, $m)) {
        $route = $m[1];
        $class = $m[2];
        $method = $m[3];
        $total++;
        
        $classPath = str_replace('\\', '/', $class);
        $file = $projectRoot . '/app/Http/Controllers/' . $classPath . '.php';
        
        if (!file_exists($file)) {
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
