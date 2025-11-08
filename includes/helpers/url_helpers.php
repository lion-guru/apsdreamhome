<?php
/**
 * URL Helper Functions
 * 
 * Contains various URL-related functions used throughout the application
 */

if (!function_exists('get_property_url')) {
    /**
     * Generate a URL for a property
     * 
     * @param int $property_id The property ID
     * @param string $slug The property slug (optional)
     * @return string The property URL
     */
    function get_property_url($property_id, $slug = '') {
        $base_url = rtrim(SITE_URL, '/');
        $property_id = (int)$property_id;
        
        if (!empty($slug)) {
            // If we have a slug, use pretty URL format
            return $base_url . '/property/' . $property_id . '/' . urlencode($slug);
        } else {
            // Fallback to query parameter format
            return $base_url . '/property-details.php?id=' . $property_id;
        }
    }
}

// End of file url_helpers.php
