<?php
require 'vendor/autoload.php';

use App\Core\Http\Request;
use App\Http\Middleware\ApiAuthMiddleware;

// Simulate a request with Authorization header
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer 7ee2eac1a05e9659f561ca8a83e0df0a02ec03222640a20a7e1a88ced1bc226f';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/apsdreamhome/api/v2/mobile/properties';

$request = Request::createFromGlobals();
echo "Authorization header: " . $request->header('authorization') . "\n";

$middleware = new ApiAuthMiddleware();
try {
    $result = $middleware->handle($request, function($req) {
        echo "Middleware passed, request would continue\n";
        return $req;
    });
    echo "Result: ";
    var_dump($result);
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}