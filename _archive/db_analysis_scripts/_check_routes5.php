<?php
$routes = file_get_contents('routes/web.php') . file_get_contents('routes/api.php');
preg_match_all("/['\"]([A-Z][a-zA-Z0-9\\\\]+@[a-zA-Z_]+)['\"]/", $routes, $matches);
$unique = array_unique($matches[1]);

$missing = [];
foreach ($unique as $r) {
    [$class, $method] = explode('@', $r);
    // Replace double-backslash with single
    $class = str_replace('\\\\', '\\', $class);
    $relPath = str_replace('\\', '/', $class) . '.php';

    $found = false;
    foreach (["app/Http/Controllers/$relPath", "app/$relPath"] as $p) {
        if (file_exists($p)) {
            $found = true;
            $content = file_get_contents($p);
            if (!preg_match('/function\s+' . preg_quote($method, '/') . '\s*\(/', $content)) {
                $missing[] = "MISSING METHOD: $r (in $p)";
            }
            break;
        }
    }
    if (!$found) {
        $missing[] = "MISSING CLASS: $r (tried: app/Http/Controllers/$relPath)";
    }
}

echo "Found " . count($missing) . " issues:\n";
foreach ($missing as $m) echo "  $m\n";?>