<?php
// Test script for APS Dream Home

// Simple autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = 'app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

require_once 'app/Http/Controllers/BaseController.php';
require_once 'app/Http/Controllers/Front/PageController.php';
require_once 'app/Core/Http/Request.php';
require_once 'app/Core/Database/Database.php';

try {
    // Define constants needed by views
    define('BASE_URL', 'http://localhost:8000');

    // Mock some server variables for Request
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['REQUEST_URI'] = '/';

    $controller = new App\Http\Controllers\Front\PageController();
    echo "✅ PageController instantiated successfully\n";

    // Test home method
    ob_start();
    $controller->home();
    $output = ob_get_clean();

    echo "✅ Home method executed successfully\n";
    echo "Output length: " . strlen($output) . " characters\n";

    if (strpos($output, '<!DOCTYPE html') !== false) {
        echo "✅ HTML output generated correctly\n";
    } else {
        echo "❌ HTML output not found\n";
        echo "First 200 chars: " . substr($output, 0, 200) . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
