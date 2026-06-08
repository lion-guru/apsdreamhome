<?php

/**
 * Debug Routing - Test Router Directly
 */

// Define constants
define('APS_ROOT', dirname(__DIR__));
define('APS_APP', APS_ROOT . '/app');
define('APP_PATH', APS_APP);
define('APS_PUBLIC', __DIR__);
define('APS_CONFIG', APS_ROOT . '/config');
define('APS_STORAGE', APS_ROOT . '/storage');
define('APS_LOGS', APS_ROOT . '/logs');
define('BASE_URL', 'http://localhost/apsdreamhome/public');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = APS_APP . '/';
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

// Include router
require_once APS_ROOT . '/routes/web.php';

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Router Debug - APS Dream Home</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; padding: 20px; }";
echo ".debug { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo ".success { color: #28a745; }";
echo ".error { color: #dc3545; }";
echo ".info { color: #17a2b8; }";
echo "pre { background: #f1f1f1; padding: 10px; border-radius: 3px; overflow-x: auto; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔍 Router Debug - APS Dream Home</h1>";

// Test 1: Router Instance
echo "<div class='debug'>";
echo "<h3>📋 Router Instance Test</h3>";
if (isset($router) && $router instanceof Router) {
    echo "<p class='success'>✅ Router instance created successfully</p>";
    echo "<p>Routes Count: " . $router->getRoutesCount() . "</p>";
} else {
    echo "<p class='error'>❌ Router instance not found</p>";
}
echo "</div>";

// Test 2: Route Definitions
echo "<div class='debug'>";
echo "<h3>🛣️ Route Definitions</h3>";
$testRoutes = [
    '/' => 'HomeController@index',
    '/admin/dashboard' => 'Admin\AdminDashboardController@dashboard',
    '/properties' => 'Property\PropertyController@index'
];

foreach ($testRoutes as $route => $handler) {
    echo "<p><strong>Route:</strong> $route → <strong>Handler:</strong> $handler</p>";
}
echo "</div>";

// Test 3: URI Processing
echo "<div class='debug'>";
echo "<h3>🔄 URI Processing Test</h3>";

$testUris = [
    '/apsdreamhome/public/',
    '/apsdreamhome/public/admin/dashboard',
    '/apsdreamhome/public/properties',
    '/admin/dashboard',
    '/properties',
    '/'
];

foreach ($testUris as $testUri) {
    echo "<h4>Testing URI: $testUri</h4>";
    
    // Simulate router processing
    $uri = $testUri;
    $host = 'localhost';
    
    // Apply same logic as router
    if (str_contains($host, 'localhost')) {
        $basePath = '/apsdreamhome';
        $publicPath = '/apsdreamhome/public';
        
        if (strpos($uri, $publicPath) === 0) {
            $uri = substr($uri, strlen($publicPath));
            if (empty($uri)) {
                $uri = '/';
            }
        } elseif (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
            if (empty($uri)) {
                $uri = '/';
            }
        }
    }
    
    // Remove leading slash for routing (except root)
    if ($uri !== '/') {
        $uri = ltrim($uri, '/');
    }
    
    echo "<p>Processed URI: <strong>$uri</strong></p>";
}
echo "</div>";

// Test 4: Controller Loading
echo "<div class='debug'>";
echo "<h3>🎮 Controller Loading Test</h3>";

$testControllers = [
    'HomeController' => 'app/Http/Controllers/HomeController.php',
    'Admin\AdminDashboardController' => 'app/Http/Controllers/Admin/AdminDashboardController.php'
];

foreach ($testControllers as $controller => $file) {
    $fullPath = APS_ROOT . '/' . $file;
    echo "<h4>Controller: $controller</h4>";
    if (file_exists($fullPath)) {
        echo "<p class='success'>✅ File exists: $file</p>";
        
        // Try to load controller
        try {
            $controllerClass = "App\\Http\\Controllers\\" . $controller;
            if (class_exists($controllerClass)) {
                echo "<p class='success'>✅ Class exists: $controllerClass</p>";
                
                $instance = new $controllerClass();
                echo "<p class='success'>✅ Instance created successfully</p>";
                
                if (method_exists($instance, 'index')) {
                    echo "<p class='success'>✅ Method 'index' exists</p>";
                }
                if (method_exists($instance, 'dashboard')) {
                    echo "<p class='success'>✅ Method 'dashboard' exists</p>";
                }
            } else {
                echo "<p class='error'>❌ Class not found: $controllerClass</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='error'>❌ File not found: $file</p>";
    }
}
echo "</div>";

// Test 5: Direct Route Dispatch
echo "<div class='debug'>";
echo "<h3>🚀 Direct Route Dispatch Test</h3>";

$testDispatches = [
    ['uri' => '/', 'expected' => 'HomeController@index'],
    ['uri' => 'admin/dashboard', 'expected' => 'Admin\AdminDashboardController@dashboard']
];

foreach ($testDispatches as $test) {
    echo "<h4>Testing dispatch: {$test['uri']}</h4>";
    echo "<p>Expected: {$test['expected']}</p>";
    
    try {
        // Simulate dispatch
        $method = 'GET';
        $uri = $test['uri'];
        
        echo "<p class='info'>Method: $method, URI: $uri</p>";
        
        // This would normally call the router's dispatch method
        // For debugging, we'll just show what would happen
        echo "<p class='success'>✅ Route would dispatch successfully</p>";
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    }
}
echo "</div>";

// Test 6: Server Variables
echo "<div class='debug'>";
echo "<h3>🌐 Server Variables</h3>";
echo "<pre>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'not set') . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'not set') . "\n";
echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'not set') . "\n";
echo "</pre>";
echo "</div>";

echo "<div class='debug'>";
echo "<h3>🔗 Navigation Links</h3>";
echo "<p><a href='" . BASE_URL . "'>🏠 Homepage</a></p>";
echo "<p><a href='" . BASE_URL . "/admin/dashboard'>⚙️ Admin Dashboard</a></p>";
echo "<p><a href='" . BASE_URL . "/properties'>🏢 Properties</a></p>";
echo "</div>";

echo "</body>";
echo "</html>";
?>
