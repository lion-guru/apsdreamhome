<?php
// Setup minimal autoloader
spl_autoload_register(function ($class) {
    $rel = str_replace('\\', '/', $class) . '.php';
    $paths = [
        "app/Http/Controllers/$rel",
        "app/$rel",
    ];
    foreach ($paths as $p) {
        if (file_exists($p)) {
            require_once $p;
            return;
        }
    }
});

$routes = file_get_contents('routes/web.php') . file_get_contents('routes/api.php');
preg_match_all("/['\"]([A-Z][a-zA-Z0-9\\\\]+@[a-zA-Z_]+)['\"]/", $routes, $matches);
$unique = array_unique($matches[1]);

$missing = [];
foreach ($unique as $r) {
    [$class, $method] = explode('@', $r);
    // The class has literal \\ in it
    // Resolve to file path
    $rel = str_replace('\\\\', '/', $class);
    $candidates = [
        "app/Http/Controllers/$rel.php",
        "app/$rel.php",
    ];

    $found = false;
    foreach ($candidates as $p) {
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
        $missing[] = "MISSING CLASS: $r (tried: " . implode(', ', $candidates) . ")";
    }
}

echo "Found " . count($missing) . " issues:\n";
foreach ($missing as $m) echo "  $m\n";?>