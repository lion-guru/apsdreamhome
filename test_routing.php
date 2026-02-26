<?php
// Simple test to check if routing works
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing routing...\n";

// Simulate homepage request
$_SERVER['REQUEST_URI'] = '/apsdreamhome/';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/apsdreamhome', '', $path);
echo "Path: '$path'\n";

define('BASE_URL', '/apsdreamhome/');

switch ($path) {
    case '/':
    case '':
        echo "Would include: pages/home.php\n";
        break;
    case '/properties':
        echo "Would include: pages/properties.php\n";
        break;
    case '/about':
        echo "Would include: pages/about.php\n";
        break;
    default:
        echo "Would include: pages/404.php\n";
        break;
}

echo "Routing test complete.\n";
?>
