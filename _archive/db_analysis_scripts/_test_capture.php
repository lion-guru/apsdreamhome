<?php
$routes = file_get_contents('routes/web.php');
preg_match_all("/['\"]([A-Z][a-zA-Z0-9\\\\]+@[a-zA-Z_]+)['\"]/", $routes, $matches);
$unique = array_slice(array_unique($matches[1]), 0, 5);

foreach ($unique as $r) {
    echo "R: $r\n";
    echo "  Length: " . strlen($r) . "\n";
    echo "  Hex: " . bin2hex($r) . "\n";
    [$class, $method] = explode('@', $r);
    echo "  Class: $class (len=" . strlen($class) . ")\n";
    echo "  Class hex: " . bin2hex($class) . "\n";
}?>