<?php
/**
 * Common functions for the APS Dream Homes website
 * This file contains all utility functions used across the website
 */

/**
 * Generate a URL for an asset file
 * 
 * @param string $filename The name of the asset file
 * @param string $type The type of asset (images, css, js, etc.)
 * @return string The complete URL to the asset
 */
if (!function_exists('get_asset_url')) {
    function get_asset_url($filename, $type = '') {
        // Get the base URL from configuration
        if (defined('BASE_URL')) {
            $base_url = rtrim(BASE_URL, '/');
        } else {
            // Fallback to constructing the base URL
            $base_url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $base_url .= $_SERVER['HTTP_HOST'];
        }
        
        // Handle vendor assets
        if ($type === 'vendor') {
            // Extract the file type (css/js) from the filename
            $file_parts = explode('/', $filename);
            $file_type = pathinfo(end($file_parts), PATHINFO_EXTENSION);
            return $base_url . '/assets/vendor/' . ($file_type === 'css' ? 'css/' : 'js/') . ltrim($filename, '/');
        }
        
        // For regular assets
        return $base_url . '/assets/' . ($type ? trim($type, '/') . '/' : '') . ltrim($filename, '/');
    }
}

/**
 * Check if the current page matches the given page name
 * Used for highlighting active navigation links
 * 
 * @param string $page The page name to check against
 * @return string Returns 'active' if current page matches, empty string otherwise
 */
if (!function_exists('active_class')) {
    function active_class($page) {
        $current_page = basename($_SERVER['PHP_SELF']);
        return ($current_page == $page) ? 'active' : '';
    }
}

/**
 * Format currency values consistently across the site
 * 
 * @param float $amount The amount to format
 * @param string $currency The currency symbol (default: ₹)
 * @return string Formatted currency string
 */
function format_currency($amount, $currency = '₹') {
    return $currency . ' ' . number_format($amount, 2);
}

// Duplicate sanitize_input removed. See includes/functions/functions.php for the main definition.

/**
 * Generate a random string for tokens, etc.
 * 
 * @param int $length The length of the random string
 * @return string Random string
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $random_string;
}

/**
 * Get subcategories based on parent category ID
 * 
 * @param string $parentId The parent category ID
 * @param int $level The current level in the hierarchy
 * @return array Array of subcategories
 */
function getSubCategories($parentId, $level) {
    global $con; // Use the global database connection
    
    if (!$con) {
        // If connection is not available, try to reconnect
        include(dirname(__DIR__) . "/config.php");
    }
    
    $subcategories = array();
    
    if ($con) {
        $stmt = $con->prepare("SELECT * FROM categories WHERE parent_id = ?");
        if ($stmt) {
            $stmt->bind_param("s", $parentId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $category = array(
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'level' => $level,
                    'children' => getSubCategories($row['id'], $level + 1)
                );
                $subcategories[] = $category;
            }
            
            $stmt->close();
        }
    }
    
    return $subcategories;
}

/**
 * Truncate text to a specified length and append ellipsis
 * 
 * @param string $text The text to truncate
 * @param int $length The maximum length
 * @param string $append The string to append (default: '...')
 * @return string Truncated text
 */
function truncate_text($text, $length = 100, $append = '...') {
    if (strlen($text) > $length) {
        $text = substr($text, 0, $length);
        $text = substr($text, 0, strrpos($text, ' '));
        $text .= $append;
    }
    return $text;
}

/**
 * Format date consistently across the site
 * 
 * @param string $date The date string to format
 * @param string $format The format to use (default: 'd M, Y')
 * @return string Formatted date
 */
function format_date($date, $format = 'd M, Y') {
    return date($format, strtotime($date));
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['uemail']);
}

/**
 * Get user role
 * 
 * @return string User role or empty string if not logged in
 */
function get_user_role() {
    return isset($_SESSION['usertype']) ? $_SESSION['usertype'] : '';
}

/**
 * Check if image file is valid
 * 
 * @param array $file The uploaded file array
 * @return bool True if valid image, false otherwise
 */
function is_valid_image($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }
    
    if ($file['size'] > $max_size) {
        return false;
    }
    
    return true;
}

/**
 * Debug function to print variables in a readable format
 * 
 * @param mixed $var The variable to debug
 * @param bool $die Whether to die after printing (default: false)
 */
function debug($var, $die = false) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
    
    if ($die) {
        die();
    }
}

// Dummy function for featured properties
if (!function_exists('get_featured_properties')) {
    function get_featured_properties() {
        return [
            [
                'id' => 1,
                'title' => 'Luxury Villa in Gorakhpur',
                'location' => 'Mohaddipur, Gorakhpur',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area' => 2500,
                'type' => 'For Sale',
                'price' => 12000000,
                'image' => 'properties/property-1.jpg',
            ],
            [
                'id' => 2,
                'title' => 'Modern Apartment in Lucknow',
                'location' => 'Gomti Nagar, Lucknow',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area' => 1800,
                'type' => 'For Sale',
                'price' => 7500000,
                'image' => 'properties/property-2.jpg',
            ],
            [
                'id' => 3,
                'title' => 'Commercial Space in Gorakhpur',
                'location' => 'Golghar, Gorakhpur',
                'bedrooms' => 0,
                'bathrooms' => 0,
                'area' => 5000,
                'type' => 'For Sale',
                'price' => 25000000,
                'image' => 'properties/property-3.jpg',
            ],
        ];
    }
}

// Dummy function for testimonials
if (!function_exists('get_testimonials')) {
    function get_testimonials() {
        return [
            [
                'name' => 'Rajesh Kumar',
                'location' => 'Gorakhpur',
                'message' => 'APS Dream Homes helped me find my perfect home in Gorakhpur. Their team was professional and supportive throughout the process.',
                'image' => 'testimonials/client-1.jpg',
            ],
            [
                'name' => 'Priya Singh',
                'location' => 'Lucknow',
                'message' => 'Excellent service and great properties. I found my dream commercial space through APS Dream Homes.',
                'image' => 'testimonials/client-2.jpg',
            ],
            [
                'name' => 'Amit Sharma',
                'location' => 'Gorakhpur',
                'message' => 'The team at APS Dream Homes is knowledgeable and professional. They made my property purchase journey smooth.',
                'image' => 'testimonials/client-3.jpg',
            ],
        ];
    }
}

// Dummy price formatting
if (!function_exists('format_price')) {
    function format_price($price) {
        return number_format($price);
    }
}

// Fetch all projects grouped by city for header menu
if (!function_exists('get_projects_grouped_by_city')) {
    function get_projects_grouped_by_city($conn) {
        $projects = [];
        $sql = "SELECT id, name, city FROM project_master WHERE status = 1 ORDER BY city, name";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $city = $row['city'];
                if (!isset($projects[$city])) {
                    $projects[$city] = [];
                }
                $projects[$city][] = $row;
            }
        }
        return $projects;
    }
}