<?php
/**
 * Asset Optimizer Functions
 * 
 * This file contains functions to optimize CSS and JS loading
 * for improved page performance.
 */

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
            // Home page specific CSS is already in optimized.css
            break;
            
        case 'property':
            // Property listing specific CSS
            $css_output .= '<link rel="stylesheet" href="'.get_asset_url('property.css', 'css').'">'."\n";
            break;
            
        case 'about':
            // About page specific CSS
            break;
            
        case 'contact':
            // Contact page specific CSS
            break;
            
        case 'admin':
            // Admin dashboard specific CSS
            $css_output .= '<link rel="stylesheet" href="'.get_asset_url('admin-style.css', 'css').'">'."\n";
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
    $js_output .= '<script src="'.get_asset_url('jquery.min.js', 'js').'"></script>'."\n";
    $js_output .= '<script src="'.get_asset_url('bootstrap.min.js', 'vendor/bootstrap-4.1').'"></script>'."\n";
    $js_output .= '<script src="'.get_asset_url('optimized.js', 'js').'"></script>'."\n";
    
    // Page-specific JS
    switch ($page_type) {
        case 'home':
            // Home page specific JS
            break;
            
        case 'property':
            // Property listing specific JS
            $js_output .= '<script src="'.get_asset_url('property.js', 'js').'"></script>'."\n";
            break;
            
        case 'about':
            // About page specific JS
            break;
            
        case 'contact':
            // Contact page specific JS
            break;
            
        case 'admin':
            // Admin dashboard specific JS
            $js_output .= '<script src="'.get_asset_url('admin.js', 'js').'"></script>'."\n";
            break;
    }
    
    return $js_output;
}

/**
 * Determine if a script should be loaded asynchronously
 * 
 * @param string $script_url URL of the script
 * @return bool True if script should be loaded async
 */
function should_load_async($script_url) {
    $async_scripts = [
        'fontawesome',
        'analytics',
        'chat',
        'map'
    ];
    
    foreach ($async_scripts as $script) {
        if (strpos($script_url, $script) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Get page type based on current URL
 * 
 * @return string The page type
 */
function get_page_type() {
    $current_page = basename($_SERVER['PHP_SELF']);
    
    if ($current_page === 'index.php') {
        return 'home';
    } elseif (strpos($current_page, 'property') !== false) {
        return 'property';
    } elseif ($current_page === 'about.php') {
        return 'about';
    } elseif ($current_page === 'contact.php') {
        return 'contact';
    } elseif (strpos($current_page, 'admin') !== false) {
        return 'admin';
    }
    
    return 'general';
}