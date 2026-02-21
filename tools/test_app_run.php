<?php

// Mimic public/index.php environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';

define('BASE_PATH', dirname(__DIR__));
define('APP_ROOT', BASE_PATH);

require_once BASE_PATH . '/app/Core/autoload.php';
require_once BASE_PATH . '/app/Core/App.php';

use App\Core\App;

try {
    echo "Starting App::run() simulation...\n";
    $app = new App();

    // Capture output of run()
    ob_start();
    $app->run();
    $output = ob_get_clean();

    echo "App run completed.\n";
    echo "Output length: " . strlen($output) . "\n";
    if (strlen($output) > 0) {
        echo "Output preview:\n" . substr($output, 0, 500) . "\n";
    } else {
        echo "Output is empty!\n";
    }
} catch (Throwable $e) {
    echo "Exception/Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo $e->getTraceAsString();
}
