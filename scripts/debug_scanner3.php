<?php
$projectRoot = dirname(__DIR__);
$routes = file($projectRoot . '/routes/web.php');

$count = 0;
foreach ($routes as $i => $line) {
    if (strpos($line, 'AdminController') !== false || strpos($line, 'MLMCommissionController') !== false || strpos($line, 'BackofficeController') !== false) {
        echo "Line $i: " . trim($line) . "\n";
        
        $pattern = '/->(?:get|post|put|delete|patch)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([A-Za-z\\\\]+)@([a-zA-Z]+)/';
        if (preg_match($pattern, $line, $m)) {
            echo "  MATCH: Route={$m[1]}, Class={$m[2]}, Method={$m[3]}\n";
            $count++;
        } else {
            echo "  NO MATCH\n";
        }
        echo "\n";
        
        if ($count >= 10) break;
    }
}