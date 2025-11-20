\u003c?php
echo "INDEX.PHP EXECUTED\n";

// Set HTTP_HOST to avoid warnings in config files
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

// Set the default timezone
date_default_timezone_set('Asia/Manila');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log'); // Custom error log file

// Define the base path
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Import the App class
use App\Core\App;

try {
    // Load the autoloader
    require_once BASE_PATH . '/app/core/autoload.php';
    
    // Load the application
    require_once BASE_PATH . '/app/core/App.php';
    
    // Create the application instance
    $app = new App();
    
    // Debugging: Log the REQUEST_URI
    error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
    file_put_contents(__DIR__ . '/request_uri.log', "REQUEST_URI (file_put_contents): " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);

    // Run the application
    $app-\u003erun();
    
} catch (Exception $e) {
    // Handle any exceptions that occur during bootstrap
    error_log("Application Error: " . $e-\u003egetMessage() . " in " . $e-\u003egetFile() . " on line " . $e-\u003egetLine());
    http_response_code(500);
    echo "\u003ch1\u003eApplication Error\u003c/h1\u003e";
    echo "\u003cp\u003eAn error occurred while loading the application.\u003c/p\u003e";
    if (getenv('APP_ENV') === 'development' || true) { // Show errors in development
        echo "\u003cp\u003e\u003cstrong\u003eError:\u003c/strong\u003e " . htmlspecialchars($e-\u003egetMessage()) . "\u003c/p\u003e";
        echo "\u003cp\u003e\u003cstrong\u003eFile:\u003c/strong\u003e " . htmlspecialchars($e-\u003egetFile()) . "\u003c/p\u003e";
        echo "\u003cp\u003e\u003cstrong\u003eLine:\u003c/strong\u003e " . htmlspecialchars($e-\u003egetLine()) . "\u003c/p\u003e";
        echo "\u003cpre\u003e" . htmlspecialchars($e-\u003egetTraceAsString()) . "\u003c/pre\u003e";
    }
}
?\u003e