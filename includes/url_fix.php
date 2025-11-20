<?php
/**
 * URL Fix for APS Dream Home
 * This file fixes the URL processing issue in the admin section
 */

// Function to properly generate admin URLs
function generate_admin_url($url = '') {
    $base_url = 'http://localhost/apsdreamhome/admin/';
    
    // Clean the URL to prevent injection
    $clean_url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    
    // Properly concatenate the URL
    return $base_url . $clean_url;
}

// Apply this fix to all admin pages
if (!function_exists('fix_admin_urls')) {
    function fix_admin_urls() {
        // Check if we're in an admin page
        $current_url = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($current_url, '/admin/') !== false) {
            // Register this function to run at the end of script execution
            register_shutdown_function(function() {
                // Get the output buffer content
                $content = ob_get_contents();
                
                // Fix the unprocessed PHP code in URLs
                $pattern = "/'%20\.%20htmlspecialchars\\\(\\\$url\\\)%20\.%20'/";
                $replacement = "' . htmlspecialchars(\$url) . '";
                $fixed_content = preg_replace($pattern, $replacement, $content);
                
                // Clear the buffer and output the fixed content
                ob_clean();
                echo $fixed_content;
            });
            
            // Start output buffering
            ob_start();
        }
    }
    
    // Initialize the fix
    fix_admin_urls();
}
?>