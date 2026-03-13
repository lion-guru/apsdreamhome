<?php
// Debug router path generation
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/admin/login';
$_SERVER['HTTP_HOST'] = 'localhost:8000';

require_once 'routes/router.php';
require_once 'routes/web.php';

echo "=== ROUTER PATH DEBUG ===\n";

// Test the controller path generation
$handler = 'App\Http\Controllers\Auth\AdminAuthController@adminLogin';
if (strpos($handler, '@') !== false) {
    list($controller, $method) = explode('@', $handler);
    echo "Controller: $controller\n";
    echo "Method: $method\n";

    if (strpos($controller, '\\') !== false) {
        $controllerClass = $controller;

        // Test different path patterns
        $patterns = [
            'Pattern 1' => __DIR__ . '/../app/' . str_replace('\\', '/', $controller) . '.php',
            'Pattern 2' => __DIR__ . '/../app/Http/Controllers/' . str_replace('\\', '/', $controller) . '.php',
            'Pattern 3' => __DIR__ . '/../app/' . str_replace('App\\', '', str_replace('\\', '/', $controller)) . '.php',
            'Pattern 4' => __DIR__ . '/../app/Http/Controllers/' . str_replace('App\\Http\\Controllers\\', '', str_replace('\\', '/', $controller)) . '.php',
        ];

        foreach ($patterns as $name => $path) {
            echo "$name: $path\n";
            echo "  Exists: " . (file_exists($path) ? 'YES' : 'NO') . "\n";
        }

        // Check actual file location
        $actualPath = 'app/Http/Controllers/Auth/AdminAuthController.php';
        echo "Actual Path: $actualPath\n";
        echo "Actual Exists: " . (file_exists($actualPath) ? 'YES' : 'NO') . "\n";

        // Check absolute path
        $absPath = __DIR__ . '/' . $actualPath;
        echo "Absolute Path: $absPath\n";
        echo "Absolute Exists: " . (file_exists($absPath) ? 'YES' : 'NO') . "\n";
    }
}
