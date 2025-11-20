<?php
// Test generic error handler
require_once '../app/core/autoload.php';

// Define BASE_URL if not defined
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost:8000/');
}

echo "Testing generic error handler...\n\n";

ob_start();
App\Core\ErrorHandler::render(418, "I'm a teapot!");
$content = ob_get_clean();

echo "Content length: " . strlen($content) . "\n";
echo "Contains 'I'm a teapot!': " . (strpos($content, "I'm a teapot!") !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'Error 418': " . (strpos($content, "Error 418") !== false ? 'YES' : 'NO') . "\n";

// Show first 500 characters
echo "First 500 characters:\n";
echo substr($content, 0, 500) . "\n";