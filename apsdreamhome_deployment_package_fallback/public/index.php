<?php
/**
 * APS Dream Home - Main Entry Point
 * Front Controller for the Application
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Debug logging
$logFile = dirname(__DIR__) . '/logs/debug_output.log';
function debug_log($message)
{
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

debug_log("Request started: " . ($_SERVER['REQUEST_URI'] ?? 'CLI'));
debug_log("Request method: " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
debug_log("HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT_SET'));

// Load centralized path configuration
require_once __DIR__ . '/../config/paths.php';

// Set HTTP_HOST to avoid warnings in config files
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

// Define BASE_URL if not already defined
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = str_replace('/public', '', dirname($_SERVER['PHP_SELF']));
    define('BASE_URL', $protocol . '://' . $host . $path);
}

debug_log("BASE_URL defined: " . BASE_URL);

// Import the App class
use App\Core\App;

try {
    debug_log("Loading autoloader...");
    require_once BASE_PATH . '/app/core/autoload.php';
    
    debug_log("Loading App class...");
    require_once BASE_PATH . '/app/core/App.php';
    
    debug_log("Instantiating App...");
    $app = new App();
    
    debug_log("Running App...");
    $response = $app->run();
    
    // Output response
    if ($response) {
        echo $response;
    }
    
    debug_log("App run completed successfully");
    
} catch (Exception $e) {
    debug_log("Exception: " . $e->getMessage());
    debug_log("Exception in file: " . $e->getFile());
    debug_log("Exception on line: " . $e->getLine());
    
    // Handle any exceptions that occur during bootstrap
    error_log("Application Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    http_response_code(500);
    
    // Show error page
    echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Error - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">Application Error</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <h5>Something went wrong!</h5>
                            <p>An error occurred while loading the application. Our team has been notified.</p>
                        </div>
                        
                        <div class="mt-3">
                            <h6>What you can do:</h6>
                            <ul>
                                <li>Refresh the page and try again</li>
                                <li>Check your internet connection</li>
                                <li>Contact support if the problem persists</li>
                            </ul>
                        </div>
                        
                        <div class="mt-3">
                            <a href="' . BASE_URL . '" class="btn btn-primary">Go to Homepage</a>
                            <a href="mailto:support@apsdreamhome.com" class="btn btn-outline-primary">Contact Support</a>
                        </div>';
    
    // Show detailed error in development mode
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo '
                        <div class="mt-4">
                            <h6>Technical Details (Development Mode):</h6>
                            <div class="alert alert-warning">
                                <strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '<br>
                                <strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '<br>
                                <strong>Line:</strong> ' . htmlspecialchars($e->getLine()) . '
                            </div>
                            <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>
                        </div>';
    }
    
    echo '
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
}

debug_log("Error handling completed");
