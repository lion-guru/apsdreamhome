<?php
$routes = file_get_contents('routes/web.php');
preg_match_all("/->(get|post|any|put|delete|patch)\(['\"]([^'\"]+)['\"]/", $routes, $matches);

$paths = [];
foreach ($matches[2] as $index => $path) {
    $method = strtoupper($matches[1][$index]);
    $key = "$method $path";
    if (isset($paths[$key])) {
        echo "Duplicate found: $key\n";
    }
    $paths[$key] = true;
}
echo "Total routes checked: " . count($matches[0]) . "\n";
