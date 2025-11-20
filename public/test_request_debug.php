<?php

// Test script to debug request creation
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Helpers/env.php';

use App\Core\Http\Request;

// Set up server variables
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/test/error/404';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';

echo "Server variables set:\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n\n";

// Create request
try {
    $request = Request::createFromGlobals();
    
    echo "Request created successfully\n";
    echo "Request URI: " . $request->getUri() . "\n";
    echo "Request path: " . $request->path() . "\n";
    echo "Request method: " . $request->getMethod() . "\n";
    
    // Debug the internal state
    $reflection = new ReflectionClass($request);
    
    // Get the uri property
    $uriProperty = $reflection->getProperty('uri');
    $uriProperty->setAccessible(true);
    $uriValue = $uriProperty->getValue($request);
    echo "Internal uri property: '$uriValue'\n";
    
    // Get the path property
    $pathProperty = $reflection->getProperty('path');
    $pathProperty->setAccessible(true);
    $pathValue = $pathProperty->getValue($request);
    echo "Internal path property: '$pathValue'\n";
    
} catch (Exception $e) {
    echo "Error creating request: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}