<?php
// render_test_single.php

// Define base path
define('BASE_PATH', __DIR__);

// Include autoloader
require_once __DIR__ . '/app/core/autoload.php';

// Load helpers
require_once __DIR__ . '/app/Helpers/env.php';

use App\Core\App;

function test_render($uri)
{
    try {
        // Mock request
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_ENV['APP_ENV'] = 'development';
        $_ENV['APP_DEBUG'] = 'true';
        putenv('APP_ENV=development');
        putenv('APP_DEBUG=true');

        // Use output buffering to capture output
        ob_start();

        // Create fresh app instance
        $app = new App();
        $app->run();

        $output = ob_get_clean();

        echo "Testing URI: $uri ... \n";

        if (empty($output)) {
            echo "FAILED (Empty output)\n";
        } else {
            echo "OK (" . strlen($output) . " bytes)\n";
            echo "First 500 chars:\n" . substr($output, 0, 500) . "\n";
        }
    } catch (Throwable $e) {
        ob_end_clean();
        echo "FAILED (Exception: " . $e->getMessage() . ")\n";
        echo "Trace: " . $e->getTraceAsString() . "\n";
    }
}

test_render('/');
