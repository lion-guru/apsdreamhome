<?php
// test_single_uri.php

// Define base path
define('BASE_PATH', __DIR__);
define('APP_ROOT', __DIR__);

// Mock HTTP Host if not set
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

// Get URI from argument
$uri = $argv[1] ?? '/';
$_SERVER['REQUEST_URI'] = $uri;
$_SERVER['REQUEST_METHOD'] = 'GET';

// Set environment
putenv('APP_ENV=development');
putenv('APP_DEBUG=true');
$_ENV['APP_ENV'] = 'development';
$_ENV['APP_DEBUG'] = 'true';

// Include autoloader
require_once __DIR__ . '/app/core/Autoloader.php';

// Include helpers
require_once __DIR__ . '/app/Helpers/env.php';

use App\Core\App;

// Use output buffering to capture output
ob_start();

try {
    // Create fresh app instance
    $app = new App();
    $app->run();
} catch (Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage();
    echo "\n" . $e->getTraceAsString();
}

$output = ob_get_clean();
echo $output;
