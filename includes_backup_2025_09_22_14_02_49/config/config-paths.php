<?php
/**
 * Configuration file for paths and common settings
 * This file defines constants and functions for consistent path handling across the project
 */

// Define base paths
define('BASE_URL', '/march2025apssite'); // Update this based on your server configuration
define('ASSETS_PATH', BASE_URL . '/assets');
define('CSS_PATH', BASE_URL . '/css');
define('JS_PATH', BASE_URL . '/js');
define('IMAGES_PATH', ASSETS_PATH . '/images');

/**
 * Get the URL for an asset file
 * 
 * @param string $filename The filename within the asset directory
 * @param string $type The type of asset (css, js, images, fonts)
 * @return string The complete URL to the asset
 */
function get_asset_url($filename, $type = '') {
    switch ($type) {
        case 'css':
            return ASSETS_PATH . '/css/' . $filename;
        case 'js':
            return ASSETS_PATH . '/js/' . $filename;
        case 'images':
            return ASSETS_PATH . '/images/' . $filename;
        case 'fonts':
            return ASSETS_PATH . '/fonts/' . $filename;
        default:
            return ASSETS_PATH . '/' . $filename;
    }
}

/**
 * Get the URL for a custom CSS file
 * 
 * @param string $filename The CSS filename
 * @return string The complete URL to the CSS file
 */
function get_css_url($filename) {
    return CSS_PATH . '/' . $filename;
}

/**
 * Get the URL for a custom JS file
 * 
 * @param string $filename The JS filename
 * @return string The complete URL to the JS file
 */
function get_js_url($filename) {
    return JS_PATH . '/' . $filename;
}

/**
 * Check if the current page matches a specific page
 * 
 * @param string $page The page name to check against
 * @return boolean True if current page matches, false otherwise
 */
function is_current_page($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page == $page);
}

/**
 * Add active class if current page matches
 * 
 * @param string $page The page name to check against
 * @return string The 'active' class if current page matches, empty string otherwise
 */
function active_class($page) {
    return is_current_page($page) ? 'active' : '';
}
?>