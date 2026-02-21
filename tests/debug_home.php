<?php
define('BASE_PATH', dirname(__DIR__));
// Try to load composer autoloader
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Load core files manually if needed
require_once BASE_PATH . '/app/Core/Database/Database.php';
require_once BASE_PATH . '/app/Core/Database.php';
require_once BASE_PATH . '/app/Core/App.php';
require_once BASE_PATH . '/config/config.php';

// Mock session if needed
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Inspect Users Table
echo "--- Inspecting Users Table ---\n";
try {
    $db = \App\Core\Database::getInstance();
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns in 'users' table: " . implode(', ', $columns) . "\n";
} catch (Exception $e) {
    echo "Error describing users table: " . $e->getMessage() . "\n";
}

// 2. Debug Home Page Rendering (via App::run)
echo "\n--- Debugging Home Page (App::run) ---\n";

// Mock REQUEST_URI and SERVER vars for Router
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';

try {
    // We need to use the App class
    require_once BASE_PATH . '/app/Core/App.php';
    require_once BASE_PATH . '/app/Core/autoload.php'; // Ensure autoloader is loaded

    // Instantiate App
    $app = new \App\Core\App();

    // Capture output
    ob_start();
    $app->run();
    $output = ob_get_clean();

    echo "Total Output Length: " . strlen($output) . "\n";
    if (strlen($output) > 0) {
        echo "Output Preview (first 500 chars):\n";
        echo substr($output, 0, 500) . "\n";
    } else {
        echo "WARNING: Output is empty!\n";
    }
} catch (Exception $e) {
    echo "Exception during App::run(): " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
