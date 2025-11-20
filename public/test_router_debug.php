<?php
/**
 * Test router registration and route matching
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Set up basic constants
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

echo "Testing router registration...\n\n";

// Create App instance
$app = new \App\Core\App();

// Get the router
$router = $app->router();

echo "Router class: " . get_class($router) . "\n";

// Test if we can get registered routes
$reflection = new ReflectionClass($router);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$routes = $routesProperty->getValue($router);

echo "Registered routes: " . count($routes) . "\n";

// Look for error test routes
$errorRoutes = [];
foreach ($routes as $method => $methodRoutes) {
    foreach ($methodRoutes as $route => $handler) {
        if (strpos($route, 'test/error') !== false) {
            $errorRoutes[] = "$method $route => " . (is_array($handler) ? json_encode($handler) : $handler);
        }
    }
}

echo "Error test routes found: " . count($errorRoutes) . "\n";
if (!empty($errorRoutes)) {
    foreach ($errorRoutes as $route) {
        echo "  $route\n";
    }
} else {
    echo "  (none found)\n";
}

// Test direct route matching
echo "\nTesting route matching...\n";
$testUri = '/test/error/404';
$testMethod = 'GET';

echo "Testing: $testMethod $testUri\n";

// Try to match the route
$matchMethod = $reflection->getMethod('matchRoute');
$matchMethod->setAccessible(true);

try {
    $result = $matchMethod->invoke($router, $testMethod, $testUri);
    if ($result) {
        echo "✓ Route matched!\n";
        echo "Handler: " . json_encode($result) . "\n";
    } else {
        echo "✗ Route not matched\n";
    }
} catch (Exception $e) {
    echo "✗ Error matching route: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";