<?php
$routes = file_get_contents('routes/web.php') . file_get_contents('routes/api.php');
preg_match_all("/['\"]([A-Z][a-zA-Z\\\\]+@[a-zA-Z_]+)['\"]/", $routes, $matches);
$unique = array_unique($matches[1]);

$missing = [];
foreach ($unique as $r) {
    [$class, $method] = explode('@', $r);
    $parts = explode('\\', $class);
    $shortName = end($parts);
    $file = findControllerFile($class);
    if (!$file) {
        $missing[] = ['type' => 'class', 'ref' => $r];
        continue;
    }
    $content = file_get_contents($file);
    if (!preg_match('/function\s+' . preg_quote($method, '/') . '\s*\(/', $content)) {
        $missing[] = ['type' => 'method', 'ref' => $r . ' (' . $file . ')'];
    }
}

echo "Found " . count($missing) . " missing classes/methods:\n";
foreach ($missing as $m) echo "  [{$m['type']}] {$m['ref']}\n";

function findControllerFile($class) {
    $relPath = str_replace('\\', '/', $class) . '.php';
    $paths = [
        "app/Http/Controllers/$relPath",
        "app/$relPath",
    ];
    foreach ($paths as $p) {
        if (file_exists($p)) return $p;
    }
    return null;
}?>