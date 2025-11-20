<?php
// Test the router directly
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/core/autoload.php';
require_once __DIR__ . '/../app/core/App.php';

// Set up basic environment
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost:8000');
}

// Set up $_SERVER variables to simulate web request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/test/error/404';
$_SERVER['HTTP_HOST'] = 'localhost:8000';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

echo "=== Testing Router directly ===\n";

try {
    $app = new \App\Core\App();
    echo "App created successfully\n";
    
    echo "Getting router...\n";
    $router = $app->router();
    echo "Router obtained: " . get_class($router) . "\n";
    
    // Check if routes are loaded
    echo "Checking routes...\n";
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    echo "Routes object: " . get_class($routes) . "\n";
    
    // Check GET routes
    $getRoutes = $routes->get('GET');
    echo "GET routes count: " . count($getRoutes) . "\n";
    if (isset($getRoutes['/test/error/404'])) {
        echo "Found /test/error/404 route!\n";
    } else {
        echo "Route /test/error/404 not found in loaded routes\n";
        echo "Available GET routes: " . implode(', ', array_keys($getRoutes)) . "\n";
    }
    
    echo "Testing dispatch...\n";
    
    // Create a Request object
    $request = new \App\Core\Http\Request(
        $_GET,
        $_POST,
        [],
        $_COOKIE,
        $_FILES,
        $_SERVER
    );
    echo "Request created\n";
    
    // Debug: Check what URI the request is using
    echo "Request URI: " . $request->path() . "\n";
    echo "Request Method: " . $request->getMethod() . "\n";
    
    try {
        ob_start();
        $response = $router->dispatch($request);
        $output = ob_get_clean();
        
        echo "Dispatch completed\n";
        if (is_object($response)) {
            echo "Response type: " . get_class($response) . "\n";
        } else {
            echo "Response type: " . gettype($response) . "\n";
        }
        echo "Output length: " . strlen($output) . " bytes\n";
        if (strlen($output) > 0) {
            echo "Output preview: " . substr($output, 0, 200) . "...\n";
        }
    } catch (Exception $e) {
        ob_end_clean(); // Clean any output buffer
        echo "Dispatch failed: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";