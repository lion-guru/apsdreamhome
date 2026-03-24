<?php

/**
 * Direct Admin Dashboard Test - Bypass Router
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

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Admin Dashboard Test - APS Dream Home</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; background: #f8f9fa; }";
echo ".test-section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; }";
echo ".error { color: #dc3545; }";
echo ".info { color: #17a2b8; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔧 Admin Dashboard Direct Test</h1>";

// Test 1: Load AdminDashboardController directly
echo "<div class='test-section'>";
echo "<h3>🎮 AdminDashboardController Test</h3>";

try {
    $controllerClass = "App\\Http\\Controllers\\Admin\\AdminDashboardController";
    
    if (class_exists($controllerClass)) {
        echo "<p class='success'>✅ AdminDashboardController class exists</p>";
        
        $controller = new $controllerClass();
        echo "<p class='success'>✅ Controller instance created</p>";
        
        if (method_exists($controller, 'dashboard')) {
            echo "<p class='success'>✅ dashboard() method exists</p>";
            
            // Call the dashboard method
            echo "<p class='info'>🚀 Calling dashboard() method...</p>";
            $controller->dashboard();
            echo "<p class='success'>✅ dashboard() method executed successfully</p>";
        } else {
            echo "<p class='error'>❌ dashboard() method not found</p>";
        }
    } else {
        echo "<p class='error'>❌ AdminDashboardController class not found</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p class='info'>Stack trace: " . $e->getTraceAsString() . "</p>";
}

echo "</div>";

// Test 2: Check required files
echo "<div class='test-section'>";
echo "<h3>📁 Required Files Check</h3>";

$requiredFiles = [
    'AdminDashboardController' => 'app/Http/Controllers/Admin/AdminDashboardController.php',
    'Dashboard View' => 'app/views/admin/dashboard.php',
    'Base Layout' => 'app/views/layouts/base.php'
];

foreach ($requiredFiles as $name => $file) {
    $fullPath = APS_ROOT . '/' . $file;
    if (file_exists($fullPath)) {
        echo "<p class='success'>✅ $name: $file</p>";
    } else {
        echo "<p class='error'>❌ $name: $file (NOT FOUND)</p>";
    }
}

echo "</div>";

// Test 3: Database Connection
echo "<div class='test-section'>";
echo "<h3>🗄️ Database Connection Test</h3>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>✅ Database connection successful</p>";
    
    // Test a simple query
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p class='info'>📊 Found " . count($tables) . " tables</p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Test 4: Session Check
echo "<div class='test-section'>";
echo "<h3>🔐 Session Check</h3>";

if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p class='success'>✅ Session is active</p>";
    echo "<p class='info'>Session ID: " . session_id() . "</p>";
    
    // Check for admin session variables
    $adminVars = ['user_role', 'admin_logged_in', 'role'];
    foreach ($adminVars as $var) {
        if (isset($_SESSION[$var])) {
            echo "<p class='info'>$var: " . $_SESSION[$var] . "</p>";
        } else {
            echo "<p class='info'>$var: not set</p>";
        }
    }
} else {
    echo "<p class='error'>❌ Session is not active</p>";
}

echo "</div>";

echo "<div class='test-section'>";
echo "<h3>🔗 Navigation Links</h3>";
echo "<p><a href='" . BASE_URL . "' class='btn btn-primary'>🏠 Homepage</a></p>";
echo "<p><a href='admin_dashboard_test.php' class='btn btn-secondary'>🔄 Refresh Test</a></p>";
echo "<p><a href='" . BASE_URL . "/properties' class='btn btn-info'>🏢 Properties</a></p>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
