<?php
$routes = ['simple-test'];
$uri = 'simple-test';

echo "URI: '$uri'<br>";
echo "Routes array: " . print_r($routes, true) . "<br>";
echo "In array: " . (array_key_exists($uri, $routes) ? 'YES' : 'NO') . "<br>";

if (array_key_exists($uri, $routes)) {
    echo "Route found!";
} else {
    echo "Route NOT found!";
}
?>
