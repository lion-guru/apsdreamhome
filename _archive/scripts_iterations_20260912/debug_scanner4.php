<?php
$projectRoot = dirname(__DIR__);
$routes = file($projectRoot . '/routes/web.php');

$count = 0;
foreach ($routes as $i => $line) {
    if (strpos($line, 'MLMCommissionController') !== false || strpos($line, 'BackofficeController') !== false || strpos($line, 'DepartmentController') !== false || strpos($line, 'DesignationController') !== false || strpos($line, 'LegalDocumentController') !== false) {
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
            $count++;
        } else {
            echo "  NO MATCH\n\n";
        }
        
        if ($count >= 10) break;
    }
}