<?php

// Debug logging
$logFile = dirname(__DIR__) . '/logs/debug_output.log';
function debug_log($message)
{
    global $logFile;
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] " . $message . "\n", FILE_APPEND);
}

debug_log("Request started: " . ($_SERVER['REQUEST_URI'] ?? 'CLI'));

// Load centralized path configuration
require_once __DIR__ . '/../config/paths.php';

// Set HTTP_HOST to avoid warnings in config files
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

// Import the App class
use App\Core\App;

try {
    debug_log("Loading autoloader...");
    require_once BASE_PATH . '/app/core/autoload.php';

    debug_log("Loading App class...");
    require_once BASE_PATH . '/app/core/App.php';

    debug_log("Instantiating App...");
    $app = new App();

    $app->run();
} catch (Exception $e) {
    debug_log("Exception: " . $e->getMessage());
    // Handle any exceptions that occur during bootstrap
    error_log("Application Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    http_response_code(500);
    echo "<h1>Application Error</h1>";
    echo "<p>An error occurred while loading the application.</p>";
    if ($__env === 'development') {
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . htmlspecialchars($e->getLine()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
}
