<?php
/**
 * Updated Configuration file for paths and common settings
 * This file defines constants and functions for consistent path handling across the project
 * 
 * This is the centralized configuration file that replaces duplicate path functions
 * across the project after reorganization.
 */

// Define base paths with proper directory structure
// BASE_URL is already defined elsewhere, so we're checking if it exists first
if (!defined('BASE_URL')) {
    define('BASE_URL', '/apsdreamhomefinal'); // Update this based on your server configuration
}
define('ASSETS_PATH', BASE_URL . '/assets');
define('CSS_PATH', BASE_URL . '/css');
define('JS_PATH', BASE_URL . '/js');
define('IMAGES_PATH', ASSETS_PATH . '/images');
define('UPLOADS_PATH', BASE_URL . '/uploads');
define('VENDOR_PATH', ASSETS_PATH . '/vendor');

// Define modern styles path
define('MODERN_STYLES_PATH', CSS_PATH . '/modern-styles.css');

/**
 * Get the URL for an asset file
 * 
 * @param string $filename The filename within the asset directory
 * @param string $type The type of asset (css, js, images, fonts, vendor)
 * @return string The complete URL to the asset
 */
if (!function_exists('get_asset_url')) {
    function get_asset_url($filename, $type = '') {
        // Remove leading slash if present
        $filename = ltrim($filename, '/');
        
        // Get the base URL from configuration or construct it
        if (defined('BASE_URL')) {
            $base_url = rtrim(BASE_URL, '/');
        } else {
            // Fallback to constructing the base URL
            $base_url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $base_url .= $_SERVER['HTTP_HOST'];
            $base_url .= '/apsdreamhomefinal'; // Default application directory
        }
        
        // Handle different asset types
        switch ($type) {
            case 'css':
                return $base_url . '/css/' . $filename;
            case 'js':
                return $base_url . '/js/' . $filename;
            case 'images':
                return $base_url . '/images/' . $filename;
            case 'fonts':
                return $base_url . '/fonts/' . $filename;
            case 'vendor':
                return $base_url . '/vendor/' . $filename;
            default:
                return $base_url . '/assets/' . $filename;
        }
    }
}

/**
 * Get the URL for a custom CSS file
 * 
 * @param string $filename The CSS filename
 * @return string The complete URL to the CSS file
 */
if (!function_exists('get_css_url')) {
    function get_css_url($filename) {
        return get_asset_url($filename, 'css');
    }
}

/**
 * Get the URL for a custom JS file
 * 
 * @param string $filename The JS filename
 * @return string The complete URL to the JS file
 */
if (!function_exists('get_js_url')) {
    function get_js_url($filename) {
        return get_asset_url($filename, 'js');
    }
}

/**
 * Get the URL for an image file
 * 
 * @param string $filename The image filename
 * @param string $subfolder Optional subfolder within images directory
 * @return string The complete URL to the image file
 */
function get_image_url($filename, $subfolder = '') {
    if (!empty($subfolder)) {
        return IMAGES_PATH . '/' . $subfolder . '/' . $filename;
    }
    return IMAGES_PATH . '/' . $filename;
}

/**
 * Get the URL for an upload file
 * 
 * @param string $filename The upload filename
 * @param string $subfolder Optional subfolder within uploads directory
 * @return string The complete URL to the upload file
 */
function get_upload_url($filename, $subfolder = '') {
    if (!empty($subfolder)) {
        return UPLOADS_PATH . '/' . $subfolder . '/' . $filename;
    }
    return UPLOADS_PATH . '/' . $filename;
}

/**
 * Check if the current page matches the given page name
 * Used for highlighting active navigation links
 * 
 * @param string $page The page name to check against
 * @return string Returns 'active' if current page matches, empty string otherwise
 */
// Function moved to common-functions.php to avoid duplication

/**
 * Get the full server path for a file
 * 
 * @param string $relative_path The relative path from the document root
 * @return string The full server path
 */
function get_server_path($relative_path) {
    return $_SERVER['DOCUMENT_ROOT'] . $relative_path;
}