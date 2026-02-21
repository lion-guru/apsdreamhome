<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/App.php';
require_once __DIR__ . '/../app/Core/Router.php';
require_once __DIR__ . '/../app/Core/Http/Request.php';
require_once __DIR__ . '/../app/Core/Http/Response.php';
require_once __DIR__ . '/../app/Core/Controller.php';

use App\Core\App;

// Mock server vars for the test
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Define constants if not defined (mimic bootstrap)
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(__DIR__));
if (!defined('URL_ROOT')) define('URL_ROOT', 'http://localhost/apsdreamhome');

try {
    $app = new App();
    
    // Capture output
    ob_start();
    $app->run();
    $output = ob_get_clean();
    
    // Extract head tag to check CSS links
    if (preg_match('/<head>(.*?)<\/head>/s', $output, $matches)) {
        echo "HEAD CONTENT:\n" . $matches[1] . "\n";
    } else {
        echo "NO HEAD TAG FOUND. Output start:\n" . substr($output, 0, 500) . "\n";
    }
    
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage();
}
