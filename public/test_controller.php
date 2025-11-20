<?php
// Direct test of ErrorTestController
require_once '../app/core/autoload.php';

// Define BASE_URL if not defined
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost:8000/');
}

// Create controller instance
$controller = new App\Controllers\ErrorTestController();

// Test 404 method
echo "Testing ErrorTestController@test404...\n";
$controller->test404();