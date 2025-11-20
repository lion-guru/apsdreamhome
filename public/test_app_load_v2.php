<?php
// Set HTTP_HOST to avoid warnings
$_SERVER['HTTP_HOST'] = 'localhost';

// Simple test to see if App.php loads correctly
require_once __DIR__ . '/../app/core/autoload.php';
require_once __DIR__ . '/../app/core/App.php';

try {
    $app = new App\Core\App();
    echo "App created successfully\n";
    
    // Test if router is available
    if (method_exists($app, 'router')) {
        $router = $app->router();
        echo "Router retrieved successfully\n";
        echo "Router class: " . get_class($router) . "\n";
    } else {
        echo "Router method not found\n";
    }
    
} catch (Exception $e) {
    echo "Error creating App: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>