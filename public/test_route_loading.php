<?php
/**
 * Test route loading process
 */

// Set up minimal environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'localhost';

define('APP_ROOT', dirname(__DIR__));

require_once __DIR__ . '/../vendor/autoload.php';

echo "Testing route loading process...\n\n";

// Test loading web.php directly
echo "Testing web.php route registration...\n";

$webRoutesFile = __DIR__ . '/../routes/web.php';
if (file_exists($webRoutesFile)) {
    echo "✓ web.php file exists\n";
    
    // Test the route structure
    $webRoutes = [];
    require $webRoutesFile;
    
    echo "Route groups found: " . count($webRoutes) . "\n";
    echo "Groups: " . implode(', ', array_keys($webRoutes)) . "\n";
    
    if (isset($webRoutes['public']['GET'])) {
        echo "✓ Public GET routes: " . count($webRoutes['public']['GET']) . "\n";
        
        // Check for error test routes
        $errorRoutes = [];
        foreach ($webRoutes['public']['GET'] as $route => $handler) {
            if (strpos($route, 'test/error') !== false) {
                $errorRoutes[$route] = $handler;
            }
        }
        
        echo "Error test routes: " . count($errorRoutes) . "\n";
        foreach ($errorRoutes as $route => $handler) {
            echo "  $route => $handler\n";
        }
    }
    
    // Test if $app would be available during normal loading
    echo "\nTesting with App context...\n";
    
    // Create App instance
    $app = new \App\Core\App();
    
    // Test if routes get registered
    $router = $app->router();
    
    // Manually register a test route
    $router->get('/manual-test', function() {
        return 'Manual test route works!';
    });
    
    echo "✓ Manual route registered\n";
    
    // Try to dispatch the manual route
    try {
        $request = new \App\Core\Http\Request();
        $response = $router->dispatch($request);
        echo "Manual route dispatched successfully\n";
    } catch (Exception $e) {
        echo "Manual route dispatch failed: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "✗ web.php file not found\n";
}

echo "\nTest completed.\n";