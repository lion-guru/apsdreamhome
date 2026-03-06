<?php
/**
 * Asset Optimizer Functions
 * 
 * This file contains functions to optimize CSS and JS loading
 * for improved page performance.
 */

require_once __DIR__ . '/../core/functions.php';

// Ensure core helpers are loaded
$helpersPath = __DIR__ . '/../app/helpers.php';
if (file_exists($helpersPath)) {
    require_once $helpersPath;
}

/**
 * Get asset URL based on type and filename
 * 
 * DEPRECATED: This function is now handled by app/helpers.php
 * But we keep this check to ensure no re-declaration errors
 */
if (!function_exists('get_asset_url')) {
    function get_asset_url($filename, $type = '') {
        // Fallback implementation if app/helpers.php not loaded
        // This mirrors the logic in app/helpers.php
        $filename = ltrim($filename, '/');
        $base = defined('BASE_URL') ? BASE_URL : '/';
        $base = rtrim($base, '/');
        
        if (!empty($type)) {
            if (strpos($filename, $type . '/') === 0) {
                 return $base . '/public/assets/' . $filename;
            }
            return $base . '/public/assets/' . $type . '/' . $filename;
        }
        return $base . '/public/assets/' . $filename;
    }
}

/**
 * Get the current page type based on filename
 * 
 * @return string The page type (home, property, about, etc.)
 */
if (!function_exists('get_page_type')) {
    function get_page_type() {
        $current_file = basename($_SERVER['PHP_SELF']);
        
        // Map filenames to page types
        $page_types = [
            'index.php' => 'home',
            'property.php' => 'property',
            'propertydetail.php' => 'property-detail',
            'about.php' => 'about',
            'contact.php' => 'contact',
            'career.php' => 'career',
            'legal.php' => 'legal',
            'project.php' => 'project',
            'user_dashboard.php' => 'dashboard',
            'customer_dashboard.php' => 'dashboard',
            'associate_dashboard.php' => 'dashboard',
            'builder_dashboard.php' => 'dashboard'
        ];
        
        // Return the page type if found, otherwise return 'general'
        return isset($page_types[$current_file]) ? $page_types[$current_file] : 'general';
    }
}

/**
 * Load optimized CSS based on page requirements
 * 
 * @param string $page_type The type of page (home, property, about, etc.)
 * @return string HTML for optimized CSS includes
 */
function load_optimized_css($page_type = 'general') {
    $css_output = '';
    
    // Always include these critical CSS files
    $css_output .= '<link rel="preconnect" href="https://fonts.googleapis.com">'."\n";
    $css_output .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'."\n";
    $css_output .= '<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">'."\n";
    
    // Core CSS files (always loaded)
    $css_output .= '<link rel="stylesheet" href="'.get_asset_url('bootstrap.min.css', 'vendor/bootstrap-4.1').'">'."\n";
    $css_output .= '<link rel="stylesheet" href="'.get_asset_url('fontawesome-all.min.css', 'vendor/font-awesome-5/css').'">'."\n";
    $css_output .= '<link rel="stylesheet" href="'.get_asset_url('optimized.css', 'css').'">'."\n";
    
    // Page-specific CSS
    switch ($page_type) {
        case 'home':
            $css_output .= '<link rel="stylesheet" href="'.get_asset_url('home.css', 'css').'">'."\n";
            break;
        case 'property':
        case 'property-detail':
            $css_output .= '<link rel="stylesheet" href="'.get_asset_url('property.css', 'css').'">'."\n";
            break;
        case 'about':
            $css_output .= '<link rel="stylesheet" href="'.get_asset_url('about.css', 'css').'">'."\n";
            break;
        case 'contact':
            $css_output .= '<link rel="stylesheet" href="'.get_asset_url('contact.css', 'css').'">'."\n";
            break;
        case 'dashboard':
            $css_output .= '<link rel="stylesheet" href="'.get_asset_url('dashboard.css', 'css').'">'."\n";
            break;
    }
    
    return $css_output;
}

/**
 * Load optimized JavaScript based on page requirements
 * 
 * @param string $page_type The type of page (home, property, about, etc.)
 * @return string HTML for optimized JS includes
 */
function load_optimized_js($page_type = 'general') {
    $js_output = '';
    
    // Core JS files (always loaded)
    $js_output .= '<script src="'.get_asset_url('jquery-3.6.0.min.js', 'vendor/jquery').'"></script>'."\n";
    $js_output .= '<script src="'.get_asset_url('bootstrap.bundle.min.js', 'vendor/bootstrap-4.1').'"></script>'."\n";
    $js_output .= '<script src="'.get_asset_url('main.js', 'js').'"></script>'."\n";
    
    // Page-specific JS
    switch ($page_type) {
        case 'home':
            $js_output .= '<script src="'.get_asset_url('home.js', 'js').'"></script>'."\n";
            break;
        case 'property':
        case 'property-detail':
            $js_output .= '<script src="'.get_asset_url('property.js', 'js').'"></script>'."\n";
            break;
        case 'contact':
            $js_output .= '<script src="'.get_asset_url('contact.js', 'js').'"></script>'."\n";
            break;
        case 'dashboard':
            $js_output .= '<script src="'.get_asset_url('dashboard.js', 'js').'"></script>'."\n";
            break;
    }
    
    return $js_output;
}
