<?php
// Test MVC routing
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing MVC routing...\n";

// Test homepage
$_SERVER['REQUEST_URI'] = '/apsdreamhome/';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/apsdreamhome', '', $path);

define('BASE_URL', '/apsdreamhome/');

echo "Path: '$path'\n";

switch ($path) {
    case '/':
    case '':
        echo "✅ Homepage route matched\n";
        ob_start();
        include 'views/home.php';
        $output = ob_get_clean();
        echo "Output length: " . strlen($output) . " bytes\n";
        if (strpos($output, 'APS Dream Home') !== false) {
            echo "✅ MVC content found!\n";
        }
        break;
    case '/properties':
        echo "✅ Properties route matched\n";
        break;
    case '/about':
        echo "✅ About route matched\n";
        break;
    default:
        echo "❌ Route not found\n";
        break;
}

echo "MVC routing test complete.\n";
?>
