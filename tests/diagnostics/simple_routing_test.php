<?php

// Simple test to verify routing works
require_once 'app/core/Route.php';
require_once 'app/core/Router.php';

use App\Core\Router;

$router = new Router();

// Test with a simple callback
$router->group(['prefix' => 'api'], function($router) {
    $router->group(['prefix' => 'test'], function($router) {
        $router->get('/', function() {
            return 'API Test Root';
        });
    });
});

// Test dispatching
echo "Testing /api/test/ route:\n";
try {
    $result = $router->dispatch('GET', '/api/test/');
    echo "✓ SUCCESS: " . $result . "\n";
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}

echo "\nTesting /api/test route:\n";
try {
    $result = $router->dispatch('GET', '/api/test');
    echo "✓ SUCCESS: " . $result . "\n";
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}