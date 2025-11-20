<?php
// Test file to debug error pages
require_once '../app/core/autoload.php';

// Test BASE_URL
echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED') . "\n";

// Test ErrorHandler
try {
    App\Core\ErrorHandler::handle404();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}