<?php
/**
 * APS Dream Home - Diagnostic Script
 * Tests the full request flow to identify issues
 */

echo "<h1>APS Dream Home - System Diagnostic</h1>";
echo "<pre>";

// 1. Check PHP version
echo "PHP Version: " . PHP_VERSION . "\n\n";

// 2. Check required extensions
$required = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl'];
foreach ($required as $ext) {
    echo "Extension $ext: " . (extension_loaded($ext) ? "OK" : "MISSING") . "\n";
}
echo "\n";

// 3. Check database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=apsdreamhome", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database Connection: OK\n";
    
    // Check tables
    $tables = ['users', 'admin_users', 'properties', 'leads', 'gallery_images'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
            $count = $stmt->fetchColumn();
            echo "Table `$table`: EXISTS ($count rows)\n";
        } catch (Exception $e) {
            echo "Table `$table`: MISSING\n";
        }
    }
} catch (PDOException $e) {
    echo "Database Connection: FAILED - " . $e->getMessage() . "\n";
}
echo "\n";

// 4. Check session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "Session: " . (session_status() === PHP_SESSION_ACTIVE ? "ACTIVE" : "INACTIVE") . "\n";
echo "Session ID: " . session_id() . "\n\n";

// 5. Check key files
$files = [
    'routes/web.php' => 'Routes',
    'routes/router.php' => 'Router',
    'app/helpers.php' => 'Helpers',
    'app/core/Database/Database.php' => 'Database Class',
    'app/core/Http/Request.php' => 'Request Class',
    'app/core/ErrorHandler.php' => 'ErrorHandler',
    'app/Http/Controllers/BaseController.php' => 'BaseController',
    'app/Http/Controllers/RoleBasedDashboardController.php' => 'Dashboard Controller',
    'app/Http/Controllers/Auth/AdminAuthController.php' => 'Auth Controller',
    'app/views/layouts/admin_header.php' => 'Admin Layout',
    'app/views/dashboard/index.php' => 'Dashboard View',
    'app/views/auth/admin_login.php' => 'Login View',
];

foreach ($files as $file => $label) {
    echo "$label ($file): " . (file_exists(__DIR__ . '/' . $file) ? "EXISTS" : "MISSING") . "\n";
}
echo "\n";

// 6. Test router
echo "--- Testing Router ---\n";
try {
    require_once __DIR__ . '/routes/router.php';
    $router = new Router();
    echo "Router class: OK\n";
    
    // Register a test route
    $router->get('/test-diagnostic', function() {
        echo "Test route works!";
    });
    
    // Test dispatch
    $routes = $router->getRoutes();
    echo "Routes registered: GET routes = " . (isset($routes['GET']) ? count($routes['GET']) : 0) . "\n";
    
    // Try to dispatch the admin/dashboard route
    echo "\n--- Testing Admin Dashboard Route ---\n";
    require_once __DIR__ . '/routes/web.php';
    
    // web.php creates a new $router - check if it has routes
    if (isset($router)) {
        $allRoutes = $router->getRoutes();
        echo "Total GET routes: " . (isset($allRoutes['GET']) ? count($allRoutes['GET']) : 0) . "\n";
        echo "Total POST routes: " . (isset($allRoutes['POST']) ? count($allRoutes['POST']) : 0) . "\n";
        
        // Check specific routes
        $checkRoutes = ['/', 'admin/login', 'admin/dashboard', 'properties', 'about', 'contact'];
        foreach ($checkRoutes as $route) {
            echo "Route '$route': " . (isset($allRoutes['GET'][$route]) ? "REGISTERED -> " . $allRoutes['GET'][$route]['handler'] : "NOT FOUND") . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Router Error: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "Router Fatal Error: " . $e->getMessage() . "\n";
}

echo "\n--- Testing Controller Loading ---\n";
try {
    // Load base controllers first
    require_once __DIR__ . '/app/Http/Controllers/BaseController.php';
    require_once __DIR__ . '/app/Http/Controllers/AdminBaseController.php';
    
    $controllerFile = __DIR__ . '/app/Http/Controllers/RoleBasedDashboardController.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        echo "RoleBasedDashboardController loaded: OK\n";
        
        if (class_exists('App\Http\Controllers\RoleBasedDashboardController')) {
            echo "Class exists: OK\n";
            
            // Try to instantiate
            $ctrl = new App\Http\Controllers\RoleBasedDashboardController();
            echo "Instantiation: OK\n";
            
            if (method_exists($ctrl, 'index')) {
                echo "Method index(): EXISTS\n";
            } else {
                echo "Method index(): MISSING\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Controller Error: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "Controller Fatal Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

echo "\n--- Testing Admin Login Controller ---\n";
try {
    $controllerFile = __DIR__ . '/app/Http/Controllers/Auth/AdminAuthController.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        echo "AdminAuthController loaded: OK\n";
        
        if (class_exists('App\Http\Controllers\Auth\AdminAuthController')) {
            echo "Class exists: OK\n";
            $ctrl = new App\Http\Controllers\Auth\AdminAuthController();
            echo "Instantiation: OK\n";
            
            if (method_exists($ctrl, 'adminLogin')) {
                echo "Method adminLogin(): EXISTS\n";
            }
            if (method_exists($ctrl, 'authenticateAdmin')) {
                echo "Method authenticateAdmin(): EXISTS\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Auth Controller Error: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "Auth Controller Fatal Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

echo "\n--- Check mod_rewrite ---\n";
echo "mod_rewrite: " . (function_exists('apache_get_modules') ? (in_array('mod_rewrite', apache_get_modules()) ? "ENABLED" : "DISABLED") : "Cannot check (not running as Apache module)") . "\n";

echo "\n--- Apache/Server Info ---\n";
echo "SERVER_SOFTWARE: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";

echo "\n</pre>";
