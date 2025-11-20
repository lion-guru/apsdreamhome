<?php
// Test generic error handler - show more content
require_once '../app/core/autoload.php';

// Define BASE_URL if not defined
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost:8000/');
}

echo "Testing generic error handler...\n\n";

ob_start();
App\Core\ErrorHandler::render(418, "I'm a teapot!");
$content = ob_get_clean();

// Look for specific content
echo "Looking for custom message:\n";
echo "Contains 'I'm a teapot!': " . (strpos($content, "I'm a teapot!") !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'Error 418': " . (strpos($content, "Error 418") !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'I'm a teapot': " . (strpos($content, "I'm a teapot") !== false ? 'YES' : 'NO') . "\n";

// Look for the message in the content
$pos = strpos($content, "display-4");
if ($pos !== false) {
    $start = strpos($content, ">", $pos) + 1;
    $end = strpos($content, "</h1>", $start);
    $title = substr($content, $start, $end - $start);
    echo "Title found: '$title'\n";
}

$pos = strpos($content, "lead text-muted");
if ($pos !== false) {
    $start = strpos($content, ">", $pos) + 1;
    $end = strpos($content, "</p>", $start);
    $message = substr($content, $start, $end - $start);
    echo "Message found: '$message'\n";
}