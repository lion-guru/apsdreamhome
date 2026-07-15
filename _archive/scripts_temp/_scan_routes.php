<?php
$lines = file('C:/xampp/htdocs/apsdreamhome/routes/web.php', FILE_IGNORE_NEW_LINES);
$routes = [];
$dupes = [];
foreach ($lines as $i => $line) {
    if (preg_match('/\$router->(get|post|put|delete|patch)\s*\(\s*[\'\"](\/[^\'\"\?]+)/', $line, $m)) {
        $method = strtoupper($m[1]);
        $path = $m[2];
        $key = $method . ' ' . $path;
        if (isset($routes[$key])) {
            $dupes[] = $key . ' (line ' . ($routes[$key]+1) . ' and line ' . ($i+1) . ')';
        }
        $routes[$key] = $i;
    }
}
if (empty($dupes)) {
    echo "No duplicate routes found (" . count($routes) . " unique routes)\n";
} else {
    echo "DUPLICATE ROUTES:\n";
    foreach ($dupes as $d) echo "  " . $d . "\n";
}
echo "Total route definitions: " . count($lines) . "\n";
