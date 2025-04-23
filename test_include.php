<?php
/**
 * Test script to verify that both files with get_asset_url() function can be loaded without errors
 */

// Define BASE_URL if not already defined (required by asset-optimizer.php)
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/march2025apssite');
}

// Include the files that previously had conflicting function declarations
require_once __DIR__ . '/includes/functions/common-functions.php';
require_once __DIR__ . '/functions/asset-optimizer.php';

// Test the function with different asset types
$css_url = get_asset_url('style.css', 'css');
$js_url = get_asset_url('main.js', 'js');
$image_url = get_asset_url('logo.png', 'images');
$default_url = get_asset_url('file.txt');

// Output success message
echo "Successfully loaded both files without errors!\n";
echo "Function exists check: " . (function_exists('get_asset_url') ? 'Yes' : 'No') . "\n";
echo "CSS URL: {$css_url}\n";
echo "JS URL: {$js_url}\n";
echo "Image URL: {$image_url}\n";
echo "Default URL: {$default_url}\n";