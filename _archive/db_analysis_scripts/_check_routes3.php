<?php
$routes = file_get_contents('routes/web.php') . file_get_contents('routes/api.php');
preg_match_all("/['\"]([A-Z][a-zA-Z\\\\]+@[a-zA-Z_]+)['\"]/", $routes, $matches);
$unique = array_unique($matches[1]);

$missing = [];
foreach ($unique as $r) {
    [$class, $method] = explode('@', $r);
    // The class is PHP-escaped: App\\Http\\Controllers\\...
    // Convert to file path
    $filePath = str_replace('\\\\', '\\', $class);
    $filePath = str_replace('\\', '/', $filePath);
    $relPath = "app/" . $filePath . ".php";
    if (!file_exists($relPath)) {
        $missing[] = "MISSING CLASS: $r (looking for $relPath)";
        continue;
    }
    $content = file_get_contents($relPath);
    if (!preg_match('/function\s+' . preg_quote($method, '/') . '\s*\(/', $content)) {
        $missing[] = "MISSING METHOD: $r ($relPath)";
    }
}

echo "Found " . count($missing) . " issues:\n";
foreach ($missing as $m) echo "  $m\n";?>