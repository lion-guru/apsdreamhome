<?php
/**
 * Test script to verify the legacy route processing fix
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Set up basic constants for testing
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Create App instance
$app = new \App\Core\App();

// Test legacy route loading
echo "Testing legacy route processing fix...\n\n";

// Get the router
$router = $app->router();

// Test if error test routes are accessible via legacy fallback
echo "Testing legacy fallback for /test/error/404:\n";
try {
    // Simulate a request to the error test route
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/test/error/404';
    
    // Test the handleLegacyFallback method directly
    $reflection = new ReflectionClass($router);
    $method = $reflection->getMethod('handleLegacyFallback');
    $method->setAccessible(true);
    
    $result = $method->invoke($router, 'GET', '/test/error/404');
    echo "Legacy fallback result: " . (is_array($result) ? "Route found!" : "Route not found") . "\n";
    
    if (is_array($result)) {
        echo "Handler: " . $result['handler'] . "\n";
        echo "Parameters: " . json_encode($result['parameters'] ?? []) . "\n";
    }
} catch (Exception $e) {
    echo "Error testing legacy fallback: " . $e->getMessage() . "\n";
}

echo "\nTesting web.php structure:\n";
$legacyRoutesFile = __DIR__ . '/../routes/web.php';
if (file_exists($legacyRoutesFile)) {
    $webRoutes = [];
    require $legacyRoutesFile;
    
    echo "Public GET routes found: " . (isset($webRoutes['public']['GET']) ? count($webRoutes['public']['GET']) : 0) . "\n";
    echo "Error test routes:\n";
    
    if (isset($webRoutes['public']['GET'])) {
        foreach ($webRoutes['public']['GET'] as $route => $handler) {
            if (strpos($route, 'test/error') !== false) {
                echo "  $route => $handler\n";
            }
        }
    }
}

echo "\nTest completed.\n";