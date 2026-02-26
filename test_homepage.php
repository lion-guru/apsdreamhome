<?php
// Direct test without Apache
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Direct test of homepage...\n";

// Simulate homepage request
$_SERVER['REQUEST_URI'] = '/apsdreamhome/';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/apsdreamhome', '', $path);

define('BASE_URL', '/apsdreamhome/');

if ($path === '/' || $path === '') {
    echo "Including homepage...\n";
    ob_start();
    include 'pages/home.php';
    $output = ob_get_clean();
    
    echo "Output length: " . strlen($output) . " bytes\n";
    
    if (strpos($output, 'APS Dream Home') !== false) {
        echo "✅ SUCCESS: Homepage content found!\n";
    } else {
        echo "❌ ERROR: Homepage content not found\n";
    }
    
    echo "First 200 characters:\n";
    echo substr($output, 0, 200) . "\n";
} else {
    echo "Wrong path: $path\n";
}
?>
