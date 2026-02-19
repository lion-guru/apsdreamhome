<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Simulate the environment
define('APP_ROOT', dirname(__DIR__));
define('BASE_URL', 'http://localhost/apsdreamhome/');

// Define DB constants manually for testing
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'apsdreamhome');

echo "Bootstrap starting...\n";

try {
    // Load Autoloader
    require_once APP_ROOT . '/app/Core/autoload.php';
    echo "Autoloader loaded.\n";

    require_once APP_ROOT . '/config/config.php';
    echo "Config loaded.\n";

    // Skip database.php as it returns array and we defined constants
    // require_once APP_ROOT . '/config/database.php'; 

    require_once APP_ROOT . '/app/helpers.php';
    echo "Helpers loaded.\n";

    // Mock Session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    echo "Session started.\n";

    echo "Initializing PageController...\n";
    $controller = new \App\Http\Controllers\Public\PageController();

    echo "Calling index()...\n";
    // Capture output to avoid flooding terminal
    ob_start();
    $controller->index();
    $output = ob_get_clean();

    echo "Render successful! Output length: " . strlen($output) . " bytes.\n";

    if (strpos($output, 'Your Journey to the') !== false) {
        echo "✅ Hero section found.\n";
    } else {
        echo "❌ Hero section NOT found.\n";
    }

    if (strpos($output, '<header') !== false) {
        echo "✅ Header tag found.\n";
    } else {
        echo "❌ Header tag NOT found.\n";
    }
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo $e->getTraceAsString();
}
