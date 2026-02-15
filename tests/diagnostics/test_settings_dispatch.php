<?php
define('SECURE_ACCESS', true);
require_once __DIR__ . '/bootstrap.php';

use App\Core\App;
use App\Core\Http\Request;

// Mock the request for /dashboard/settings
$_SERVER['REQUEST_URI'] = '/apsdreamhome/dashboard/settings';
$_SERVER['SCRIPT_NAME'] = '/apsdreamhome/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';

try {
    $app = new App(__DIR__);
    echo "App initialized\n";

    $request = Request::createFromGlobals();
    echo "Path Info: " . $request->getPathInfo() . "\n";

    $router = $app->getRouter();

    // We need to bypass the redirect in Middleware if we're not logged in
    // But let's see if it actually matches the route first

    // Run the app in a try-catch to handle the 'exit' from redirect middleware
    try {
        $app->run(); // This will call dispatch
    } catch (Exception $e) {
        // If it was just the exit call, we can catch it if it throws,
        // but PHP 'exit' doesn't throw. We need to mock header/exit or just
        // be aware that the script will end here if it redirects.
        echo "Script execution continued/ended.\n";
    }
} catch (Throwable $e) {
    if (strpos($e->getMessage(), 'exit') !== false) {
        echo "Middleware triggered redirect (exit).\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
        echo $e->getTraceAsString() . "\n";
    }
}
