<?php
// Direct test of router debug
require_once '../routes/index.php';

$router = new Router();

// Add properties route directly
$router->get('/properties', 'Property\PropertyController@index');

echo "Available routes: " . print_r($router->routes, true);
echo "<br>Testing dispatch...<br>";

$router->dispatch();
?>
