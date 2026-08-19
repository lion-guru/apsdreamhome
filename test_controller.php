<?php
require 'vendor/autoload.php';

use App\Core\Http\Request;
use App\Core\Database\Database;

// Set up the request
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer 7ee2eac1a05e9659f561ca8a83e0df0a02ec03222640a20a7e1a88ced1bc226f';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/apsdreamhome/api/v2/mobile/properties';

// Need to register the Router class autoloader or include it manually
require_once 'routes/router.php';

// Now create the router
$router = new Router();
$router->dispatch();

// Manually test the controller
$controller = new \App\Http\Controllers\Api\MobilePropertyApiController();
try {
    $controller->syncProperties();
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
} catch (\Error $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}