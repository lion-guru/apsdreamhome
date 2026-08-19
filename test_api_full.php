<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

// Simulate the request
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer 7ee2eac1a05e9659f561ca8a83e0df0a02ec03222640a20a7e1a88ced1bc226f';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/apsdreamhome/api/v2/mobile/properties';

echo "=== Testing MobilePropertyApiController::syncProperties() ===\n\n";

try {
    // Run the middleware first
    require_once 'routes/router.php';
    $middleware = new \App\Http\Middleware\ApiAuthMiddleware();
    $request = \App\Core\Http\Request::createFromGlobals();
    
    echo "1. Testing middleware...\n";
    $middleware->handle($request, function($req) {
        echo "   Middleware passed\n";
        return $req;
    });
    
    echo "2. Globals after middleware:\n";
    echo "   api_user_id: " . ($GLOBALS['api_user_id'] ?? 'NOT SET') . "\n";
    echo "   api_user_role: " . ($GLOBALS['api_user_role'] ?? 'NOT SET') . "\n";
    
    // Now test the controller
    echo "\n3. Testing controller...\n";
    $controller = new \App\Http\Controllers\Api\MobilePropertyApiController();
    $controller->syncProperties();
    
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
} catch (\Error $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
