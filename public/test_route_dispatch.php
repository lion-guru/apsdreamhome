<?php
/**
 * Test route registration and dispatch directly
 * This script tests if routes are properly registered and can be dispatched
 */

// Set HTTP_HOST to avoid warnings
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/test/error/404';

// Include necessary files
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/core/App.php';
require_once __DIR__ . '/../app/core/Http/Request.php';

use App\Core\App;
use App\Core\Http\Request;

try {
    // Create app instance
    $app = new App();
    
    echo "App created successfully\n";
    echo "Testing route registration...\n\n";
    
    // Get router instance
    $router = $app->router();
    echo "Router instance obtained\n";
    
    // Test direct dispatch
    $testRoutes = [
        '/test/error/404',
        '/test/error/500',
        '/test/error/403',
        '/test/error/401',
        '/test/error/400',
        '/test/error/generic',
        '/test/error/exception'
    ];
    
    foreach ($testRoutes as $route) {
        echo "Testing route: $route\n";
        
        // Set up request
        $_SERVER['REQUEST_URI'] = $route;
        $request = new Request();
        
        try {
            // Try to dispatch
            $response = $router->dispatch($request);
            echo "  ✅ Route dispatched successfully\n";
            
            if (is_string($response)) {
                echo "  Response type: string (" . strlen($response) . " chars)\n";
                if (strpos($response, 'error') !== false || strpos($response, 'Error') !== false) {
                    echo "  ✓ Contains error-related content\n";
                }
            } elseif (is_object($response)) {
                echo "  Response type: " . get_class($response) . "\n";
            } else {
                echo "  Response type: " . gettype($response) . "\n";
            }
            
        } catch (Exception $e) {
            echo "  ❌ Dispatch failed: " . $e->getMessage() . "\n";
        } catch (Error $e) {
            echo "  ❌ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "App creation failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "Direct route testing complete.\n";