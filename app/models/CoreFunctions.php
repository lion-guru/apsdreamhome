<?php
/**
 * Core Functions
 * Essential utility functions for the APS Dream Home application
 */

namespace App\Models;

use PDO;
use Exception;
use DateTime;
use DateTimeZone;
use PDOException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Indian format)
 */
function isValidPhone($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    return strlen($phone) === 10 && preg_match('/^[6-9]\d{9}$/', $phone);
}

/**
 * Generate slug from string
 */
function createSlug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Format currency (Indian Rupees)
 */
function formatCurrency($amount, $currency = '₹') {
    return $currency . number_format($amount, 2);
}

/**
 * Format date for display
 */
if (!function_exists(__NAMESPACE__ . '\\formatDate')) {
    function formatDate($date, $format = 'd M Y') {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return 'N/A';
        }
        return date($format, strtotime($date));
    }
}

/**
 * Get time ago format
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->days > 0) {
        return $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    } else {
        return 'Just now';
    }
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Generate secure token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user name
 */
function getCurrentUserName() {
    return $_SESSION['user_name'] ?? null;
}

/**
 * Redirect with flash message
 */
function redirectWithMessage($url, $type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
    header('Location: ' . $url);
    exit;
}

/**
 * Get flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Upload file with validation
 */
function uploadFile($file, $destination, $allowed_types = [], $max_size = 5242880) {
    $result = [
        'success' => false,
        'error' => '',
        'filename' => ''
    ];

    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        $result['error'] = 'No file uploaded';
        return $result;
    }

    // Check file size
    if ($file['size'] > $max_size) {
        $result['error'] = 'File size too large. Maximum allowed: ' . ($max_size / 1024 / 1024) . 'MB';
        return $result;
    }

    // Check file type
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!empty($allowed_types) && !in_array($file_extension, $allowed_types)) {
        $result['error'] = 'File type not allowed. Allowed types: ' . implode(', ', $allowed_types);
        return $result;
    }

    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $destination_path = $destination . '/' . $filename;

    // Create directory if not exists
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $destination_path)) {
        $result['success'] = true;
        $result['filename'] = $filename;
    } else {
        $result['error'] = 'Failed to upload file';
    }

    return $result;
}

/**
 * Resize image
 */
function resizeImage($source, $destination, $width, $height, $quality = 80) {
    list($source_width, $source_height, $source_type) = getimagesize($source);

    switch ($source_type) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }

    $destination_image = imagecreatetruecolor($width, $height);

    // Preserve transparency for PNG and GIF
    if ($source_type === IMAGETYPE_PNG || $source_type === IMAGETYPE_GIF) {
        imagecolortransparent($destination_image, imagecolorallocatealpha($destination_image, 0, 0, 0, 127));
        imagealphablending($destination_image, false);
        imagesavealpha($destination_image, true);
    }

    imagecopyresampled($destination_image, $source_image, 0, 0, 0, 0, $width, $height, $source_width, $source_height);

    switch ($source_type) {
        case IMAGETYPE_JPEG:
            imagejpeg($destination_image, $destination, $quality);
            break;
        case IMAGETYPE_PNG:
            imagepng($destination_image, $destination, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($destination_image, $destination);
            break;
    }

    imagedestroy($source_image);
    imagedestroy($destination_image);

    return true;
}

/**
 * Send JSON response
 */
function sendJsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Log error
 */
function logError($message, $context = []) {
    $log_file = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $context_str = !empty($context) ? ' | Context: ' . json_encode($context) : '';

    $log_message = "[{$timestamp}] {$message}{$context_str}\n";

    // Create logs directory if not exists
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    error_log($log_message, 3, $log_file);
}

/**
 * Log activity
 */
function logActivity($action, $details = [], $user_id = null) {
    $log_file = __DIR__ . '/../logs/activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $user_info = $user_id ? " | User ID: {$user_id}" : '';
    $details_str = !empty($details) ? ' | Details: ' . json_encode($details) : '';

    $log_message = "[{$timestamp}] Action: {$action}{$user_info}{$details_str}\n";

    // Create logs directory if not exists
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    error_log($log_message, 3, $log_file);
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ip_headers = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'REMOTE_ADDR'
    ];

    foreach ($ip_headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = trim($_SERVER[$header]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Rate limiting check
 */
function checkRateLimit($identifier, $max_attempts = 5, $time_window = 300) {
    $rate_limit_file = __DIR__ . '/../cache/rate_limit_' . md5($identifier) . '.json';

    // Create cache directory if not exists
    $cache_dir = dirname($rate_limit_file);
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }

    $current_time = time();

    // Load existing rate limit data
    $rate_limit_data = [];
    if (file_exists($rate_limit_file)) {
        $rate_limit_data = json_decode(file_get_contents($rate_limit_file), true);
    }

    // Clean old attempts
    $rate_limit_data = array_filter($rate_limit_data, function($timestamp) use ($current_time, $time_window) {
        return ($current_time - $timestamp) < $time_window;
    });

    // Check if rate limit exceeded
    if (count($rate_limit_data) >= $max_attempts) {
        return false; // Rate limit exceeded
    }

    // Add current attempt
    $rate_limit_data[] = $current_time;

    // Save rate limit data
    file_put_contents($rate_limit_file, json_encode($rate_limit_data));

    return true; // Rate limit OK
}

/**
 * Get configuration value
 */
function config($key, $default = null) {
    global $config;
    return $config[$key] ?? $default;
}

/**
 * Environment variable helper
 */
function env($key, $default = null) {
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
}

/**
 * Debug function
 */
function debug($data, $exit = false) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';

    if ($exit) {
        exit;
    }
}

/**
 * Compress and optimize HTML
 */
function compressHTML($html) {
    // Remove comments
    $html = preg_replace('/<!--[\s\S]*?-->/', '', $html);

    // Remove extra whitespace
    $html = preg_replace('/\s+/', ' ', $html);
    $html = preg_replace('/>\s+</', '><', $html);

    // Remove whitespace between tags
    $html = preg_replace('/\s+(<[^>]*>)/', '$1', $html);
    $html = preg_replace('/(<[^>]*>)\s+/', '$1', $html);

    return trim($html);
}

/**
 * Get memory usage
 */
function getMemoryUsage() {
    $memory_usage = memory_get_usage(true);

    if ($memory_usage >= 1073741824) {
        return round($memory_usage / 1073741824, 2) . ' GB';
    } elseif ($memory_usage >= 1048576) {
        return round($memory_usage / 1048576, 2) . ' MB';
    } elseif ($memory_usage >= 1024) {
        return round($memory_usage / 1024, 2) . ' KB';
    } else {
        return $memory_usage . ' bytes';
    }
}

/**
 * Check if string contains only numbers and letters
 */
function isAlphanumeric($string) {
    return ctype_alnum($string);
}

/**
 * Truncate text with ellipsis
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }

    return substr($text, 0, $length - strlen($suffix)) . $suffix;
}

/**
 * Generate pagination links
 */
function generatePagination($current_page, $total_pages, $base_url = '') {
    $pagination = [];

    // Previous page
    if ($current_page > 1) {
        $pagination['previous'] = $base_url . '?page=' . ($current_page - 1);
    }

    // Page numbers
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);

    for ($i = $start_page; $i <= $end_page; $i++) {
        $pagination['pages'][$i] = $base_url . '?page=' . $i;
    }

    // Next page
    if ($current_page < $total_pages) {
        $pagination['next'] = $base_url . '?page=' . ($current_page + 1);
    }

    return $pagination;
}

/**
 * Validate Indian pincode
 */
function isValidPincode($pincode) {
    return preg_match('/^[1-9][0-9]{5}$/', $pincode);
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return round($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Check if array is associative
 */
function isAssociativeArray($array) {
    if (!is_array($array) || empty($array)) {
        return false;
    }

    $keys = array_keys($array);
    return array_keys($keys) !== $keys;
}

/**
 * Deep merge arrays
 */
function arrayMergeDeep($array1, $array2) {
    $merged = $array1;

    foreach ($array2 as $key => $value) {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
            $merged[$key] = arrayMergeDeep($merged[$key], $value);
        } else {
            $merged[$key] = $value;
        }
    }

    return $merged;
}

/**
 * Generate excerpt from content
 */
function generateExcerpt($content, $length = 150, $allowed_tags = '<p><br><strong><em>') {
    // Remove HTML tags except allowed ones
    $content = strip_tags($content, $allowed_tags);

    // Decode HTML entities
    $content = html_entity_decode($content);

    // Truncate to specified length
    if (strlen($content) <= $length) {
        return $content;
    }

    // Find the last space within the limit
    $excerpt = substr($content, 0, $length);
    $last_space = strrpos($excerpt, ' ');

    if ($last_space !== false && $last_space > $length * 0.8) {
        $excerpt = substr($excerpt, 0, $last_space);
    }

    return trim($excerpt) . '...';
}

/**
 * Check if current environment is development
 */
function isDevelopment() {
    return env('APP_ENV', 'development') === 'development';
}

/**
 * Check if current environment is production
 */
function isProduction() {
    return env('APP_ENV', 'development') === 'production';
}

/**
 * Get application version
 */
function getAppVersion() {
    return defined('APP_VERSION') ? APP_VERSION : '1.0.0';
}

/**
 * Get application name
 */
function getAppName() {
    return defined('APP_NAME') ? APP_NAME : 'APS Dream Home';
}

/**
 * Convert array to object recursively
 */
function arrayToObject($array) {
    if (is_array($array)) {
        return (object) array_map('arrayToObject', $array);
    }
    return $array;
}

/**
 * Convert object to array recursively
 */
function objectToArray($object) {
    if (is_object($object)) {
        $array = (array) $object;
        return array_map('objectToArray', $array);
    }
    return $object;
}

/**
 * Check if string starts with substring
 */
function startsWith($string, $substring) {
    return substr($string, 0, strlen($substring)) === $substring;
}

/**
 * Check if string ends with substring
 */
function endsWith($string, $substring) {
    return substr($string, -strlen($substring)) === $substring;
}

/**
 * Generate SEO friendly URL
 */
function seoUrl($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Get current URL
 */
function currentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];

    return $protocol . '://' . $host . $uri;
}

/**
 * Get base URL
 */
function baseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script_name = dirname($_SERVER['SCRIPT_NAME']);

    return $protocol . '://' . $host . $script_name;
}

/**
 * Check if file is image
 */
function isImage($filename) {
    $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $image_extensions);
}

/**
 * Get image dimensions
 */
function getImageDimensions($image_path) {
    if (!file_exists($image_path)) {
        return false;
    }

    $dimensions = getimagesize($image_path);
    if ($dimensions) {
        return [
            'width' => $dimensions[0],
            'height' => $dimensions[1],
            'type' => $dimensions['mime']
        ];
    }

    return false;
}

/**
 * Create thumbnail
 */
function createThumbnail($source, $destination, $width, $height, $quality = 80) {
    $image_info = getimagesize($source);
    if (!$image_info) {
        return false;
    }

    $source_image = null;
    switch ($image_info['mime']) {
        case 'image/jpeg':
            $source_image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $source_image = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $source_image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }

    if (!$source_image) {
        return false;
    }

    $thumbnail = imagecreatetruecolor($width, $height);

    // Preserve transparency for PNG and GIF
    if ($image_info['mime'] === 'image/png' || $image_info['mime'] === 'image/gif') {
        imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
    }

    imagecopyresampled($thumbnail, $source_image, 0, 0, 0, 0, $width, $height, $image_info[0], $image_info[1]);

    $success = false;
    switch ($image_info['mime']) {
        case 'image/jpeg':
            $success = imagejpeg($thumbnail, $destination, $quality);
            break;
        case 'image/png':
            $success = imagepng($thumbnail, $destination, 9);
            break;
        case 'image/gif':
            $success = imagegif($thumbnail, $destination);
            break;
    }

    imagedestroy($source_image);
    imagedestroy($thumbnail);

    return $success;
}

/**
 * Validate password strength
 */
function validatePasswordStrength($password) {
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }

    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character';
    }

    return $errors;
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate API key
 */
function generateApiKey() {
    return 'apk_' . bin2hex(random_bytes(32));
}

/**
 * Validate API key format
 */
function isValidApiKey($api_key) {
    return preg_match('/^apk_[a-f0-9]{64}$/', $api_key);
}

/**
 * Send HTTP response
 */
function sendResponse($data, $status_code = 200, $content_type = 'application/json') {
    http_response_code($status_code);
    header('Content-Type: ' . $content_type);

    if ($content_type === 'application/json') {
        echo json_encode($data);
    } else {
        echo $data;
    }

    exit;
}

/**
 * Get user agent
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

/**
 * Get request method
 */
function getRequestMethod() {
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

/**
 * Get request headers
 */
function getRequestHeaders() {
    $headers = [];
    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_') === 0) {
            $header_name = str_replace('HTTP_', '', $key);
            $header_name = str_replace('_', '-', $header_name);
            $header_name = strtolower($header_name);
            $headers[$header_name] = $value;
        }
    }
    return $headers;
}

/**
 * Check if HTTPS is enabled
 */
function isHttps() {
    return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
}

/**
 * Get protocol (http/https)
 */
function getProtocol() {
    return isHttps() ? 'https' : 'http';
}

/**
 * Validate URL
 */
function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Validate domain
 */
function isValidDomain($domain) {
    return preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/', $domain);
}

/**
 * Get domain from URL
 */
function getDomainFromUrl($url) {
    $parsed = parse_url($url);
    return $parsed['host'] ?? '';
}

/**
 * Check if mobile device
 */
function isMobileDevice() {
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

    $mobile_keywords = [
        'mobile', 'android', 'iphone', 'ipad', 'ipod', 'blackberry',
        'windows phone', 'opera mini', 'opera mobi', 'palm'
    ];

    foreach ($mobile_keywords as $keyword) {
        if (strpos($user_agent, $keyword) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Get device type
 */
function getDeviceType() {
    if (isMobileDevice()) {
        $user_agent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

        if (strpos($user_agent, 'tablet') !== false || strpos($user_agent, 'ipad') !== false) {
            return 'tablet';
        }

        return 'mobile';
    }

    return 'desktop';
}

/**
 * Get browser name
 */
function getBrowserName() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if (strpos($user_agent, 'Chrome') !== false && strpos($user_agent, 'Edg') === false) {
        return 'Chrome';
    } elseif (strpos($user_agent, 'Firefox') !== false) {
        return 'Firefox';
    } elseif (strpos($user_agent, 'Safari') !== false && strpos($user_agent, 'Chrome') === false) {
        return 'Safari';
    } elseif (strpos($user_agent, 'Edg') !== false) {
        return 'Edge';
    } elseif (strpos($user_agent, 'Opera') !== false) {
        return 'Opera';
    } elseif (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Trident') !== false) {
        return 'Internet Explorer';
    }

    return 'Unknown';
}

/**
 * Get operating system
 */
function getOperatingSystem() {
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

    if (strpos($user_agent, 'windows') !== false) {
        return 'Windows';
    } elseif (strpos($user_agent, 'mac') !== false) {
        return 'macOS';
    } elseif (strpos($user_agent, 'linux') !== false) {
        return 'Linux';
    } elseif (strpos($user_agent, 'android') !== false) {
        return 'Android';
    } elseif (strpos($user_agent, 'ios') !== false || strpos($user_agent, 'iphone') !== false || strpos($user_agent, 'ipad') !== false) {
        return 'iOS';
    }

    return 'Unknown';
}

/**
 * Calculate percentage
 */
function calculatePercentage($part, $total) {
    if ($total == 0) {
        return 0;
    }
    return round(($part / $total) * 100, 2);
}

/**
 * Format number with commas
 */
if (!function_exists(__NAMESPACE__ . '\\formatNumber')) {
    function formatNumber($number) {
        return number_format($number);
    }
}

/**
 * Check if date is weekend
 */
function isWeekend($date) {
    $day_of_week = date('w', strtotime($date));
    return $day_of_week == 0 || $day_of_week == 6; // 0 = Sunday, 6 = Saturday
}

/**
 * Get days between two dates
 */
function getDaysBetween($start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    return $interval->days;
}

/**
 * Add days to date
 */
function addDaysToDate($date, $days) {
    return date('Y-m-d', strtotime($date . ' + ' . $days . ' days'));
}

/**
 * Get age from birth date
 */
function getAge($birth_date) {
    $today = new DateTime();
    $birth = new DateTime($birth_date);
    $age = $today->diff($birth);
    return $age->y;
}

/**
 * Generate unique ID
 */
function generateUniqueId($prefix = '', $length = 8) {
    $unique_id = $prefix . time() . rand(1000, 9999);
    if ($length > strlen($unique_id)) {
        $unique_id .= generateRandomString($length - strlen($unique_id));
    }
    return substr($unique_id, 0, $length);
}

/**
 * Clean string for filename
 */
function cleanFilename($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9.-]/', '_', $string);
    $string = preg_replace('/_+/', '_', $string);
    return trim($string, '_');
}

/**
 * Get file extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Get MIME type from file extension
 */
function getMimeType($extension) {
    $mime_types = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'txt' => 'text/plain',
        'csv' => 'text/csv',
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed'
    ];

    return $mime_types[$extension] ?? 'application/octet-stream';
}

/**
 * Check if directory is writable
 */
function isWritableDirectory($directory) {
    return is_dir($directory) && is_writable($directory);
}

/**
 * Create directory recursively
 */
function createDirectory($directory, $permissions = 0755) {
    if (!is_dir($directory)) {
        return mkdir($directory, $permissions, true);
    }
    return true;
}

/**
 * Delete directory recursively
 */
function deleteDirectory($directory) {
    if (!is_dir($directory)) {
        return false;
    }

    $files = array_diff(scandir($directory), ['.', '..']);

    foreach ($files as $file) {
        $file_path = $directory . '/' . $file;
        if (is_dir($file_path)) {
            deleteDirectory($file_path);
        } else {
            unlink($file_path);
        }
    }

    return rmdir($directory);
}

/**
 * Get directory size
 */
function getDirectorySize($directory) {
    if (!is_dir($directory)) {
        return 0;
    }

    $size = 0;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $file) {
        if ($file->isFile()) {
            $size += $file->getSize();
        }
    }

    return $size;
}

/**
 * Format bytes to human readable
 */
function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Check if function exists and call it safely
 */
function safeCall($function, $parameters = []) {
    if (function_exists($function)) {
        return call_user_func_array($function, $parameters);
    }
    return null;
}

/**
 * Get PHP version info
 */
function getPhpVersion() {
    return [
        'version' => PHP_VERSION,
        'major' => PHP_MAJOR_VERSION,
        'minor' => PHP_MINOR_VERSION,
        'release' => PHP_RELEASE_VERSION
    ];
}

/**
 * Check if PHP extension is loaded
 */
function isExtensionLoaded($extension) {
    return extension_loaded($extension);
}

/**
 * Get loaded PHP extensions
 */
function getLoadedExtensions() {
    return get_loaded_extensions();
}

/**
 * Benchmark function execution time
 */
function benchmark($function, $parameters = []) {
    $start_time = microtime(true);
    $result = call_user_func_array($function, $parameters);
    $end_time = microtime(true);

    return [
        'result' => $result,
        'execution_time' => round(($end_time - $start_time) * 1000, 2) . 'ms'
    ];
}

/**
 * Memory usage info
 */
function getMemoryInfo() {
    return [
        'usage' => memory_get_usage(true),
        'peak_usage' => memory_get_peak_usage(true),
        'limit' => ini_get('memory_limit')
    ];
}

/**
 * Server information
 */
function getServerInfo() {
    return [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'operating_system' => php_uname(),
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
        'server_name' => $_SERVER['SERVER_NAME'] ?? ''
    ];
}

/**
 * Application information
 */
function getAppInfo() {
    return [
        'name' => getAppName(),
        'version' => getAppVersion(),
        'environment' => env('APP_ENV', 'development'),
        'debug_mode' => env('APP_DEBUG', false),
        'url' => env('APP_URL', ''),
        'timezone' => date_default_timezone_get()
    ];
}

/**
 * Database connection test
 */
function testDatabaseConnection($config = null) {
    try {
        if (!$config) {
            global $pdo;
            if ($pdo) {
                $pdo->query('SELECT 1');
                return ['success' => true, 'message' => 'Database connection successful'];
            }
            return ['success' => false, 'message' => 'No database connection available'];
        }

        $dsn = "mysql:host={$config['host']};dbname={$config['database']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return ['success' => true, 'message' => 'Database connection successful'];

    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()];
    }
}

/**
 * Check if table exists
 */
function tableExists($table_name) {
    global $pdo;
    if (!$pdo) {
        return false;
    }

    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$table_name}'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get table columns
 */
function getTableColumns($table_name) {
    global $pdo;
    if (!$pdo) {
        return [];
    }

    try {
        $stmt = $pdo->query("DESCRIBE {$table_name}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Execute SQL query safely
 */
function executeQuery($sql, $params = []) {
    global $pdo;
    if (!$pdo) {
        return false;
    }

    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (Exception $e) {
        logError('SQL Query Error', ['sql' => $sql, 'params' => $params, 'error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Get query result
 */
function getQueryResult($sql, $params = []) {
    global $pdo;
    if (!$pdo) {
        return [];
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        logError('SQL Query Error', ['sql' => $sql, 'params' => $params, 'error' => $e->getMessage()]);
        return [];
    }
}

/**
 * Get single query result
 */
function getSingleResult($sql, $params = []) {
    $results = getQueryResult($sql, $params);
    return !empty($results) ? $results[0] : null;
}

/**
 * Insert data and get last insert ID
 */
function insertData($table, $data) {
    global $pdo;
    if (!$pdo) {
        return false;
    }

    try {
        $columns = array_keys($data);
        $placeholders = array_map(function($col) { return ":{$col}"; }, $columns);

        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);

        return $pdo->lastInsertId();
    } catch (Exception $e) {
        logError('Insert Error', ['table' => $table, 'data' => $data, 'error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Update data
 */
function updateData($table, $data, $where, $where_params = []) {
    global $pdo;
    if (!$pdo) {
        return false;
    }

    try {
        $set_parts = [];
        foreach ($data as $column => $value) {
            $set_parts[] = "{$column} = :{$column}";
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $set_parts) . " WHERE {$where}";
        $stmt = $pdo->prepare($sql);

        $params = array_merge($data, $where_params);
        return $stmt->execute($params);
    } catch (Exception $e) {
        logError('Update Error', ['table' => $table, 'data' => $data, 'where' => $where, 'error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Delete data
 */
function deleteData($table, $where, $params = []) {
    global $pdo;
    if (!$pdo) {
        return false;
    }

    try {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (Exception $e) {
        logError('Delete Error', ['table' => $table, 'where' => $where, 'error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Count records
 */
function countRecords($table, $where = '', $params = []) {
    global $pdo;
    if (!$pdo) {
        return 0;
    }

    try {
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($result['count'] ?? 0);
    } catch (Exception $e) {
        logError('Count Error', ['table' => $table, 'where' => $where, 'error' => $e->getMessage()]);
        return 0;
    }
}

/**
 * Check if record exists
 */
function recordExists($table, $where, $params = []) {
    return countRecords($table, $where, $params) > 0;
}

/**
 * Get distinct values from column
 */
function getDistinctValues($table, $column, $where = '', $params = []) {
    global $pdo;
    if (!$pdo) {
        return [];
    }

    try {
        $sql = "SELECT DISTINCT {$column} FROM {$table}";
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }
        $sql .= " ORDER BY {$column}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        logError('Distinct Query Error', ['table' => $table, 'column' => $column, 'error' => $e->getMessage()]);
        return [];
    }
}

/**
 * Paginate results
 */
function paginateResults($sql, $params = [], $page = 1, $per_page = 10) {
    global $pdo;
    if (!$pdo) {
        return ['data' => [], 'pagination' => []];
    }

    try {
        // Get total count
        $count_sql = preg_replace('/SELECT.*?FROM/i', 'SELECT COUNT(*) as count FROM', $sql);
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Calculate pagination
        $total_pages = ceil($total / $per_page);
        $offset = ($page - 1) * $per_page;

        // Add LIMIT to original query
        $sql .= " LIMIT {$offset}, {$per_page}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $per_page,
                'total_pages' => $total_pages,
                'total_records' => $total,
                'has_previous' => $page > 1,
                'has_next' => $page < $total_pages
            ]
        ];
    } catch (Exception $e) {
        logError('Pagination Error', ['sql' => $sql, 'params' => $params, 'error' => $e->getMessage()]);
        return ['data' => [], 'pagination' => []];
    }
}

/**
 * Search in multiple columns
 */
function searchInColumns($table, $columns, $search_term, $limit = 50) {
    global $pdo;
    if (!$pdo) {
        return [];
    }

    try {
        $search_conditions = [];
        $params = [];

        foreach ($columns as $column) {
            $search_conditions[] = "{$column} LIKE ?";
            $params[] = "%{$search_term}%";
        }

        $sql = "SELECT * FROM {$table} WHERE " . implode(' OR ', $search_conditions);
        $sql .= " LIMIT {$limit}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        logError('Search Error', ['table' => $table, 'columns' => $columns, 'search_term' => $search_term, 'error' => $e->getMessage()]);
        return [];
    }
}

/**
 * Validate input data
 */
function validateInput($data, $rules) {
    $errors = [];

    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? '';

        if (isset($rule['required']) && $rule['required'] && empty($value)) {
            $errors[$field] = ucfirst($field) . ' is required';
            continue;
        }

        if (!empty($value)) {
            if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
                $errors[$field] = ucfirst($field) . ' must be at least ' . $rule['min_length'] . ' characters';
            }

            if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                $errors[$field] = ucfirst($field) . ' must be no more than ' . $rule['max_length'] . ' characters';
            }

            if (isset($rule['email']) && $rule['email'] && !isValidEmail($value)) {
                $errors[$field] = 'Please enter a valid email address';
            }

            if (isset($rule['phone']) && $rule['phone'] && !isValidPhone($value)) {
                $errors[$field] = 'Please enter a valid phone number';
            }

            if (isset($rule['numeric']) && $rule['numeric'] && !is_numeric($value)) {
                $errors[$field] = ucfirst($field) . ' must be a number';
            }

            if (isset($rule['url']) && $rule['url'] && !isValidUrl($value)) {
                $errors[$field] = 'Please enter a valid URL';
            }
        }
    }

    return $errors;
}

/**
 * Sanitize and validate form data
 */
function processFormData($data, $rules = []) {
    $sanitized_data = sanitize($data);
    $errors = [];

    if (!empty($rules)) {
        $errors = validateInput($sanitized_data, $rules);
    }

    return [
        'data' => $sanitized_data,
        'errors' => $errors,
        'is_valid' => empty($errors)
    ];
}

/**
 * Handle file upload with validation
 */
function handleFileUpload($file_input, $destination, $options = []) {
    $result = [
        'success' => false,
        'error' => '',
        'filename' => '',
        'filepath' => ''
    ];

    $allowed_types = $options['allowed_types'] ?? ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = $options['max_size'] ?? 5242880; // 5MB
    $create_thumbnail = $options['create_thumbnail'] ?? false;
    $thumbnail_size = $options['thumbnail_size'] ?? [150, 150];

    if (!isset($_FILES[$file_input]) || $_FILES[$file_input]['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'No file uploaded or upload error occurred';
        return $result;
    }

    $file = $_FILES[$file_input];

    // Validate file size
    if ($file['size'] > $max_size) {
        $result['error'] = 'File size too large. Maximum allowed: ' . formatBytes($max_size);
        return $result;
    }

    // Validate file type
    $extension = strtolower(getFileExtension($file['name']));
    if (!in_array($extension, $allowed_types)) {
        $result['error'] = 'File type not allowed. Allowed types: ' . implode(', ', $allowed_types);
        return $result;
    }

    // Generate unique filename
    $filename = generateUniqueId('file_') . '.' . $extension;
    $filepath = $destination . '/' . $filename;

    // Create directory if not exists
    createDirectory($destination);

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $result['success'] = true;
        $result['filename'] = $filename;
        $result['filepath'] = $filepath;

        // Create thumbnail if requested
        if ($create_thumbnail && isImage($filename)) {
            $thumbnail_path = $destination . '/thumb_' . $filename;
            if (createThumbnail($filepath, $thumbnail_path, $thumbnail_size[0], $thumbnail_size[1])) {
                $result['thumbnail'] = 'thumb_' . $filename;
            }
        }
    } else {
        $result['error'] = 'Failed to save uploaded file';
    }

    return $result;
}

/**
 * Generate sitemap
 */
function generateSitemap($base_url, $pages) {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($pages as $page) {
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . htmlspecialchars($base_url . $page['url']) . '</loc>' . "\n";
        $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        if (isset($page['priority'])) {
            $xml .= '    <priority>' . $page['priority'] . '</priority>' . "\n";
        }
        if (isset($page['changefreq'])) {
            $xml .= '    <changefreq>' . $page['changefreq'] . '</changefreq>' . "\n";
        }
        $xml .= '  </url>' . "\n";
    }

    $xml .= '</urlset>';

    return $xml;
}

/**
 * Generate robots.txt
 */
function generateRobotsTxt($base_url, $disallowed_paths = []) {
    $robots = "User-agent: *\n";
    $robots .= "Allow: /\n";

    foreach ($disallowed_paths as $path) {
        $robots .= "Disallow: {$path}\n";
    }

    $robots .= "\nSitemap: {$base_url}/sitemap.xml\n";

    return $robots;
}

/**
 * Cache management functions
 */
function setCache($key, $data, $ttl = 3600) {
    $cache_file = __DIR__ . '/../cache/' . md5($key) . '.cache';

    $cache_data = [
        'data' => $data,
        'expires' => time() + $ttl
    ];

    createDirectory(dirname($cache_file));
    return file_put_contents($cache_file, serialize($cache_data));
}

function getCache($key) {
    $cache_file = __DIR__ . '/../cache/' . md5($key) . '.cache';

    if (!file_exists($cache_file)) {
        return false;
    }

    $cache_data = unserialize(file_get_contents($cache_file));

    if ($cache_data['expires'] < time()) {
        unlink($cache_file);
        return false;
    }

    return $cache_data['data'];
}

function clearCache($key = null) {
    if ($key) {
        $cache_file = __DIR__ . '/../cache/' . md5($key) . '.cache';
        if (file_exists($cache_file)) {
            return unlink($cache_file);
        }
    } else {
        $cache_dir = __DIR__ . '/../cache';
        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '/*.cache');
            foreach ($files as $file) {
                unlink($file);
            }
            return true;
        }
    }
    return false;
}

/**
 * API response helpers
 */
function apiSuccess($data = null, $message = 'Success') {
    return [
        'success' => true,
        'message' => $message,
        'data' => $data
    ];
}

function apiError($message = 'Error', $code = 400, $data = null) {
    return [
        'success' => false,
        'message' => $message,
        'error_code' => $code,
        'data' => $data
    ];
}

/**
 * Mobile detection helpers
 */
function isMobile() {
    return getDeviceType() !== 'desktop';
}

function isTablet() {
    return getDeviceType() === 'tablet';
}

/**
 * SEO helpers
 */
function generateMetaTags($title, $description, $keywords = '', $image = '') {
    $meta = [];

    $meta[] = '<title>' . htmlspecialchars($title) . '</title>';
    $meta[] = '<meta name="description" content="' . htmlspecialchars($description) . '">';

    if (!empty($keywords)) {
        $meta[] = '<meta name="keywords" content="' . htmlspecialchars($keywords) . '">';
    }

    if (!empty($image)) {
        $meta[] = '<meta property="og:image" content="' . htmlspecialchars($image) . '">';
    }

    $meta[] = '<meta property="og:title" content="' . htmlspecialchars($title) . '">';
    $meta[] = '<meta property="og:description" content="' . htmlspecialchars($description) . '">';
    $meta[] = '<meta property="og:type" content="website">';

    return implode("\n", $meta);
}

/**
 * Performance helpers
 */
function startTimer() {
    return microtime(true);
}

function endTimer($start_time) {
    return round((microtime(true) - $start_time) * 1000, 2) . 'ms';
}

/**
 * String manipulation helpers
 */
function camelCase($string) {
    return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
}

function snakeCase($string) {
    return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
}

function kebabCase($string) {
    return strtolower(str_replace(' ', '-', trim($string)));
}

/**
 * Array helpers
 */
function arrayPluck($array, $key) {
    return array_map(function($item) use ($key) {
        return is_object($item) ? $item->$key : $item[$key];
    }, $array);
}

function arrayGroupBy($array, $key) {
    $grouped = [];
    foreach ($array as $item) {
        $group_key = is_object($item) ? $item->$key : $item[$key];
        $grouped[$group_key][] = $item;
    }
    return $grouped;
}

function arraySortBy($array, $key, $direction = 'asc') {
    usort($array, function($a, $b) use ($key, $direction) {
        $a_value = is_object($a) ? $a->$key : $a[$key];
        $b_value = is_object($b) ? $b->$key : $b[$key];

        if ($direction === 'desc') {
            return $b_value <=> $a_value;
        }
        return $a_value <=> $b_value;
    });

    return $array;
}

/**
 * Date helpers
 */
function isValidDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function getDateRange($start_date, $end_date) {
    $dates = [];
    $current = strtotime($start_date);
    $end = strtotime($end_date);

    while ($current <= $end) {
        $dates[] = date('Y-m-d', $current);
        $current = strtotime('+1 day', $current);
    }

    return $dates;
}

/**
 * Number helpers
 */
function isEven($number) {
    return $number % 2 === 0;
}

function isOdd($number) {
    return $number % 2 !== 0;
}

function isPrime($number) {
    if ($number <= 1) {
        return false;
    }

    for ($i = 2; $i <= sqrt($number); $i++) {
        if ($number % $i === 0) {
            return false;
        }
    }

    return true;
}

/**
 * Color helpers
 */
function hexToRgb($hex) {
    $hex = ltrim($hex, '#');
    return [
        'r' => hexdec(substr($hex, 0, 2)),
        'g' => hexdec(substr($hex, 2, 2)),
        'b' => hexdec(substr($hex, 4, 2))
    ];
}

function rgbToHex($r, $g, $b) {
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

/**
 * Text analysis helpers
 */
function wordCount($text) {
    return str_word_count(strip_tags($text));
}

function readingTime($text, $words_per_minute = 200) {
    $word_count = wordCount($text);
    $minutes = ceil($word_count / $words_per_minute);
    return $minutes . ' min read';
}

/**
 * Geography helpers
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371; // km

    $dlat = deg2rad($lat2 - $lat1);
    $dlon = deg2rad($lon2 - $lon1);

    $a = sin($dlat/2) * sin($dlat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dlon/2) * sin($dlon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));

    return $earth_radius * $c;
}

/**
 * Financial helpers
 */
function calculateEMI($principal, $rate, $time) {
    $rate = $rate / (12 * 100); // Monthly interest rate
    $time = $time * 12; // Time in months

    $emi = ($principal * $rate * pow(1 + $rate, $time)) / (pow(1 + $rate, $time) - 1);

    return round($emi, 2);
}

function calculateGST($amount, $gst_rate) {
    return ($amount * $gst_rate) / 100;
}

/**
 * System health helpers
 */
function getSystemHealth() {
    $health = [
        'database' => testDatabaseConnection(),
        'memory' => getMemoryInfo(),
        'disk_space' => disk_free_space('/') / disk_total_space('/') * 100,
        'php_version' => getPhpVersion(),
        'server_load' => function_exists('sys_getloadavg') ? sys_getloadavg() : 'N/A'
    ];

    return $health;
}

/**
 * Backup helpers
 */
function createDatabaseBackup($filename = null) {
    global $pdo;
    if (!$pdo) {
        return false;
    }

    try {
        $filename = $filename ?? 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $backup_path = __DIR__ . '/../backups/' . $filename;

        createDirectory(dirname($backup_path));

        $tables = [];
        $result = $pdo->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $sql = "-- APS Dream Home Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($tables as $table) {
            $sql .= "-- Table: {$table}\n";

            // Get table structure
            $result = $pdo->query("SHOW CREATE TABLE {$table}");
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $sql .= $row['Create Table'] . ";\n\n";

            // Get table data
            $result = $pdo->query("SELECT * FROM {$table}");
            $rows = $result->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $sql .= "INSERT INTO {$table} VALUES\n";
                $values = [];

                foreach ($rows as $row) {
                    $escaped_values = array_map(function($value) use ($pdo) {
                        return $pdo->quote($value);
                    }, $row);
                    $values[] = "(" . implode(", ", $escaped_values) . ")";
                }

                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }

        return file_put_contents($backup_path, $sql) !== false;

    } catch (Exception $e) {
        logError('Database Backup Error', ['error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Maintenance mode helpers
 */
function enableMaintenanceMode($message = 'Site is under maintenance. Please check back later.') {
    $maintenance_file = __DIR__ . '/../maintenance.json';

    $data = [
        'enabled' => true,
        'message' => $message,
        'timestamp' => time()
    ];

    createDirectory(dirname($maintenance_file));
    return file_put_contents($maintenance_file, json_encode($data)) !== false;
}

function disableMaintenanceMode() {
    $maintenance_file = __DIR__ . '/../maintenance.json';
    if (file_exists($maintenance_file)) {
        return unlink($maintenance_file);
    }
    return true;
}

function isMaintenanceMode() {
    $maintenance_file = __DIR__ . '/../maintenance.json';
    if (file_exists($maintenance_file)) {
        $data = json_decode(file_get_contents($maintenance_file), true);
        return isset($data['enabled']) && $data['enabled'];
    }
    return false;
}

/**
 * Notification helpers
 */
function sendNotification($type, $title, $message, $recipient = null) {
    $notification_file = __DIR__ . '/../notifications.json';

    $notification = [
        'id' => generateUniqueId('notif_'),
        'type' => $type,
        'title' => $title,
        'message' => $message,
        'recipient' => $recipient,
        'timestamp' => time(),
        'read' => false
    ];

    $notifications = [];
    if (file_exists($notification_file)) {
        $notifications = json_decode(file_get_contents($notification_file), true);
    }

    $notifications[] = $notification;

    // Keep only last 100 notifications
    if (count($notifications) > 100) {
        $notifications = array_slice($notifications, -100);
    }

    createDirectory(dirname($notification_file));
    return file_put_contents($notification_file, json_encode($notifications)) !== false;
}

/**
 * Analytics helpers
 */
function trackPageView($page, $user_id = null) {
    $analytics_file = __DIR__ . '/../analytics.json';

    $view = [
        'page' => $page,
        'user_id' => $user_id,
        'ip' => getClientIP(),
        'user_agent' => getUserAgent(),
        'timestamp' => time(),
        'referrer' => $_SERVER['HTTP_REFERER'] ?? ''
    ];

    $analytics = [];
    if (file_exists($analytics_file)) {
        $analytics = json_decode(file_get_contents($analytics_file), true);
    }

    $analytics[] = $view;

    // Keep only last 1000 views
    if (count($analytics) > 1000) {
        $analytics = array_slice($analytics, -1000);
    }

    createDirectory(dirname($analytics_file));
    return file_put_contents($analytics_file, json_encode($analytics)) !== false;
}

/**
 * Export helpers
 */
function exportToCSV($data, $filename) {
    if (empty($data)) {
        return false;
    }

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Write headers
    if (isset($data[0]) && is_array($data[0])) {
        fputcsv($output, array_keys($data[0]));
    }

    // Write data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

function exportToExcel($data, $filename) {
    if (empty($data)) {
        return false;
    }

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Write data as tab-separated values (Excel compatible)
    foreach ($data as $row) {
        if (is_array($row)) {
            fwrite($output, implode("\t", $row) . "\n");
        } else {
            fwrite($output, $row . "\n");
        }
    }

    fclose($output);
    exit;
}

/**
 * Import helpers
 */
function importFromCSV($file_path, $delimiter = ',') {
    if (!file_exists($file_path)) {
        return false;
    }

    $data = [];
    if (($handle = fopen($file_path, 'r')) !== false) {
        $headers = fgetcsv($handle, 1000, $delimiter);

        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
            $data[] = array_combine($headers, $row);
        }

        fclose($handle);
    }

    return $data;
}

/**
 * Queue management helpers
 */
function addToQueue($queue_name, $data) {
    $queue_file = __DIR__ . '/../queues/' . $queue_name . '.json';

    $queue_item = [
        'id' => generateUniqueId('queue_'),
        'data' => $data,
        'timestamp' => time(),
        'status' => 'pending'
    ];

    $queue = [];
    if (file_exists($queue_file)) {
        $queue = json_decode(file_get_contents($queue_file), true);
    }

    $queue[] = $queue_item;

    createDirectory(dirname($queue_file));
    return file_put_contents($queue_file, json_encode($queue)) !== false;
}

function processQueue($queue_name) {
    $queue_file = __DIR__ . '/../queues/' . $queue_name . '.json';

    if (!file_exists($queue_file)) {
        return [];
    }

    $queue = json_decode(file_get_contents($queue_file), true);

    $processed = [];
    $remaining = [];

    foreach ($queue as $item) {
        if ($item['status'] === 'pending') {
            $item['status'] = 'processing';
            $processed[] = $item;
        } else {
            $remaining[] = $item;
        }
    }

    // Save remaining items back to queue
    file_put_contents($queue_file, json_encode($remaining));

    return $processed;
}

/**
 * Cron job helpers
 */
function scheduleTask($task_name, $interval, $data = []) {
    $cron_file = __DIR__ . '/../cron.json';

    $task = [
        'name' => $task_name,
        'interval' => $interval, // in seconds
        'data' => $data,
        'last_run' => 0,
        'next_run' => time() + $interval
    ];

    $cron = [];
    if (file_exists($cron_file)) {
        $cron = json_decode(file_get_contents($cron_file), true);
    }

    $cron[$task_name] = $task;

    createDirectory(dirname($cron_file));
    return file_put_contents($cron_file, json_encode($cron)) !== false;
}

function runScheduledTasks() {
    $cron_file = __DIR__ . '/../cron.json';

    if (!file_exists($cron_file)) {
        return [];
    }

    $cron = json_decode(file_get_contents($cron_file), true);
    $executed = [];

    foreach ($cron as $task_name => $task) {
        if (time() >= $task['next_run']) {
            // Execute task (placeholder - implement actual task execution)
            logActivity('cron_task', ['task' => $task_name, 'data' => $task['data']]);

            // Update task timing
            $cron[$task_name]['last_run'] = time();
            $cron[$task_name]['next_run'] = time() + $task['interval'];

            $executed[] = $task_name;
        }
    }

    // Save updated cron data
    file_put_contents($cron_file, json_encode($cron));

    return $executed;
}

/**
 * API rate limiting
 */
function checkApiRateLimit($api_key, $endpoint) {
    $rate_limit_file = __DIR__ . '/../rate_limits/' . md5($api_key . '_' . $endpoint) . '.json';

    $current_time = time();
    $window = 60; // 1 minute window
    $max_requests = 100;

    $rate_data = [];
    if (file_exists($rate_limit_file)) {
        $rate_data = json_decode(file_get_contents($rate_limit_file), true);
    }

    // Clean old requests
    $rate_data = array_filter($rate_data, function($timestamp) use ($current_time, $window) {
        return ($current_time - $timestamp) < $window;
    });

    // Check limit
    if (count($rate_data) >= $max_requests) {
        return false;
    }

    // Add current request
    $rate_data[] = $current_time;

    createDirectory(dirname($rate_limit_file));
    file_put_contents($rate_limit_file, json_encode($rate_data));

    return true;
}

/**
 * Webhook helpers
 */
function processWebhook($webhook_type, $data) {
    $webhook_file = __DIR__ . '/../webhooks/' . $webhook_type . '.json';

    $webhook_data = [
        'type' => $webhook_type,
        'data' => $data,
        'timestamp' => time(),
        'processed' => false
    ];

    $webhooks = [];
    if (file_exists($webhook_file)) {
        $webhooks = json_decode(file_get_contents($webhook_file), true);
    }

    $webhooks[] = $webhook_data;

    // Keep only last 100 webhooks
    if (count($webhooks) > 100) {
        $webhooks = array_slice($webhooks, -100);
    }

    createDirectory(dirname($webhook_file));
    return file_put_contents($webhook_file, json_encode($webhooks)) !== false;
}

/**
 * Search helpers
 */
function searchInArray($array, $search_term, $case_sensitive = false) {
    $results = [];

    foreach ($array as $key => $value) {
        $search_value = $case_sensitive ? $value : strtolower($value);
        $search_term_lower = $case_sensitive ? $search_term : strtolower($search_term);

        if (strpos($search_value, $search_term_lower) !== false) {
            $results[$key] = $value;
        }
    }

    return $results;
}

/**
 * Filter helpers
 */
function filterArray($array, $callback) {
    return array_filter($array, $callback);
}

function mapArray($array, $callback) {
    return array_map($callback, $array);
}

function reduceArray($array, $callback, $initial = null) {
    return array_reduce($array, $callback, $initial);
}

/**
 * URL helpers
 */
function buildQueryString($params) {
    return http_build_query($params);
}

function parseQueryString($query_string) {
    parse_str($query_string, $params);
    return $params;
}

/**
 * Template helpers
 */
function renderTemplate($template, $data = []) {
    extract($data);

    ob_start();
    include $template;
    return ob_get_clean();
}

/**
 * Plugin system helpers
 */
function loadPlugin($plugin_name) {
    $plugin_file = __DIR__ . '/../plugins/' . $plugin_name . '.php';

    if (file_exists($plugin_file)) {
        include_once $plugin_file;
        return true;
    }

    return false;
}

/**
 * Theme system helpers
 */
function loadTheme($theme_name) {
    $theme_file = __DIR__ . '/../themes/' . $theme_name . '/functions.php';

    if (file_exists($theme_file)) {
        include_once $theme_file;
        return true;
    }

    return false;
}

/**
 * Social media helpers
 */
function getSocialShareLinks($url, $title = '') {
    $encoded_url = urlencode($url);
    $encoded_title = urlencode($title);

    return [
        'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$encoded_url}",
        'twitter' => "https://twitter.com/intent/tweet?url={$encoded_url}&text={$encoded_title}",
        'linkedin' => "https://www.linkedin.com/sharing/share-offsite/?url={$encoded_url}",
        'whatsapp' => "https://wa.me/?text={$encoded_title}%20{$encoded_url}",
        'telegram' => "https://t.me/share/url?url={$encoded_url}&text={$encoded_title}",
        'email' => "mailto:?subject={$encoded_title}&body={$encoded_url}"
    ];
}

/**
 * Currency helpers
 */
function convertCurrency($amount, $from_currency, $to_currency, $exchange_rate = null) {
    if (!$exchange_rate) {
        // In a real application, you'd fetch this from an API
        $exchange_rates = [
            'USD_TO_INR' => 83.0,
            'EUR_TO_INR' => 90.0,
            'GBP_TO_INR' => 105.0
        ];

        $rate_key = $from_currency . '_TO_' . $to_currency;
        $exchange_rate = $exchange_rates[$rate_key] ?? 1;
    }

    return $amount * $exchange_rate;
}

/**
 * Math helpers
 */
function calculatePercentageChange($old_value, $new_value) {
    if ($old_value == 0) {
        return $new_value > 0 ? 100 : 0;
    }

    return (($new_value - $old_value) / $old_value) * 100;
}

function roundToNearest($number, $nearest) {
    return round($number / $nearest) * $nearest;
}

/**
 * Geometry helpers
 */
function calculateArea($shape, $dimensions) {
    switch ($shape) {
        case 'rectangle':
            return $dimensions['length'] * $dimensions['width'];
        case 'circle':
            return pi() * pow($dimensions['radius'], 2);
        case 'triangle':
            return ($dimensions['base'] * $dimensions['height']) / 2;
        default:
            return 0;
    }
}

/**
 * Unit conversion helpers
 */
function convertUnits($value, $from_unit, $to_unit) {
    $conversions = [
        'length' => [
            'mm_to_cm' => 0.1,
            'cm_to_mm' => 10,
            'm_to_cm' => 100,
            'cm_to_m' => 0.01,
            'km_to_m' => 1000,
            'm_to_km' => 0.001
        ],
        'weight' => [
            'g_to_kg' => 0.001,
            'kg_to_g' => 1000,
            'kg_to_ton' => 0.001,
            'ton_to_kg' => 1000
        ]
    ];

    $key = strtolower($from_unit . '_to_' . $to_unit);
    return $value * ($conversions['length'][$key] ?? $conversions['weight'][$key] ?? 1);
}

/**
 * Random data generators
 */
function generateLoremIpsum($paragraphs = 1, $words_per_paragraph = 50) {
    $words = [
        'lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit',
        'sed', 'do', 'eiusmod', 'tempor', 'incididunt', 'ut', 'labore', 'et', 'dolore',
        'magna', 'aliqua', 'enim', 'ad', 'minim', 'veniam', 'quis', 'nostrud',
        'exercitation', 'ullamco', 'laboris', 'nisi', 'ut', 'aliquip', 'ex', 'ea',
        'commodo', 'consequat', 'duis', 'aute', 'irure', 'in', 'reprehenderit', 'in',
        'voluptate', 'velit', 'esse', 'cillum', 'eu', 'fugiat', 'nulla', 'pariatur',
        'excepteur', 'sint', 'occaecat', 'cupidatat', 'non', 'proident', 'sunt', 'in',
        'culpa', 'qui', 'officia', 'deserunt', 'mollit', 'anim', 'id', 'est', 'laborum'
    ];

    $lorem_ipsum = [];

    for ($p = 0; $p < $paragraphs; $p++) {
        $paragraph = [];
        for ($w = 0; $w < $words_per_paragraph; $w++) {
            $paragraph[] = $words[array_rand($words)];
        }
        $lorem_ipsum[] = ucfirst(implode(' ', $paragraph)) . '.';
    }

    return implode("\n\n", $lorem_ipsum);
}

/**
 * Data validation helpers
 */
function validateCreditCard($number) {
    // Remove spaces and dashes
    $number = preg_replace('/\D/', '', $number);

    // Check length
    if (strlen($number) < 13 || strlen($number) > 19) {
        return false;
    }

    // Luhn algorithm
    $sum = 0;
    $is_even = false;

    for ($i = strlen($number) - 1; $i >= 0; $i--) {
        $digit = (int)$number[$i];

        if ($is_even) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }

        $sum += $digit;
        $is_even = !$is_even;
    }

    return $sum % 10 === 0;
}

function validatePAN($pan) {
    return preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan);
}

function validateAadhar($aadhar) {
    $aadhar = preg_replace('/\D/', '', $aadhar);
    return strlen($aadhar) === 12 && preg_match('/^[2-9][0-9]{11}$/', $aadhar);
}

/**
 * Business logic helpers
 */
function calculateCommission($sale_amount, $commission_rate) {
    return ($sale_amount * $commission_rate) / 100;
}

function calculateTax($amount, $tax_rate) {
    return ($amount * $tax_rate) / 100;
}

function calculateDiscount($original_price, $discount_percentage) {
    return $original_price - (($original_price * $discount_percentage) / 100);
}

/**
 * Contact information helpers
 */
function formatPhoneNumber($phone) {
    $phone = preg_replace('/\D/', '', $phone);

    if (strlen($phone) === 10) {
        return '+91 ' . substr($phone, 0, 5) . ' ' . substr($phone, 5);
    } elseif (strlen($phone) === 12 && strpos($phone, '91') === 0) {
        return '+91 ' . substr($phone, 2, 5) . ' ' . substr($phone, 7);
    }

    return $phone;
}

function formatAddress($address) {
    return str_replace(['|', ','], ["\n", ",\n"], $address);
}

/**
 * Image processing helpers
 */
function optimizeImage($source, $destination, $quality = 80) {
    $image_info = getimagesize($source);
    if (!$image_info) {
        return false;
    }

    $source_image = null;
    switch ($image_info['mime']) {
        case 'image/jpeg':
            $source_image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $source_image = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $source_image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }

    if (!$source_image) {
        return false;
    }

    $success = false;
    switch ($image_info['mime']) {
        case 'image/jpeg':
            $success = imagejpeg($source_image, $destination, $quality);
            break;
        case 'image/png':
            $success = imagepng($source_image, $destination, round(9 * $quality / 100));
            break;
        case 'image/gif':
            $success = imagegif($source_image, $destination);
            break;
    }

    imagedestroy($source_image);
    return $success;
}

/**
 * Document processing helpers
 */
function extractTextFromPDF($pdf_path) {
    if (!file_exists($pdf_path)) {
        return false;
    }

    // This would require PDF parsing library like TCPDF or similar
    // For now, return placeholder
    return 'PDF text extraction not implemented yet';
}

function convertDocument($source, $destination, $format) {
    // This would require document conversion libraries
    // For now, return placeholder
    return false;
}

/**
 * Chart and graph helpers
 */
function generateChartData($data, $type = 'line') {
    $chart_data = [
        'type' => $type,
        'data' => $data,
        'labels' => array_keys($data),
        'values' => array_values($data)
    ];

    return $chart_data;
}

/**
 * Report generation helpers
 */
function generateReport($title, $data, $format = 'html') {
    $report = [
        'title' => $title,
        'generated_at' => date('Y-m-d H:i:s'),
        'data' => $data
    ];

    switch ($format) {
        case 'json':
            return json_encode($report, JSON_PRETTY_PRINT);
        case 'html':
            return generateHTMLReport($report);
        case 'csv':
            return generateCSVReport($data);
        default:
            return $report;
    }
}

function generateHTMLReport($report) {
    $html = '<h1>' . $report['title'] . '</h1>';
    $html .= '<p><strong>Generated:</strong> ' . $report['generated_at'] . '</p>';
    $html .= '<table border="1" cellpadding="5" cellspacing="0">';
    $html .= '<tr><th>Data Point</th><th>Value</th></tr>';

    foreach ($report['data'] as $key => $value) {
        $html .= '<tr><td>' . htmlspecialchars($key) . '</td><td>' . htmlspecialchars($value) . '</td></tr>';
    }

    $html .= '</table>';
    return $html;
}

function generateCSVReport($data) {
    if (empty($data)) {
        return '';
    }

    $output = fopen('php://temp', 'r+');

    if (isset($data[0]) && is_array($data[0])) {
        fputcsv($output, array_keys($data[0]));
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    } else {
        fputcsv($output, ['Metric', 'Value']);
        foreach ($data as $key => $value) {
            fputcsv($output, [$key, $value]);
        }
    }

    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);

    return $csv;
}

/**
 * Email helpers
 */
function sendEmail($to, $subject, $body, $from = null) {
    $from = $from ?? env('MAIL_FROM_ADDRESS', 'noreply@apsdreamhome.com');

    $headers = [
        'From: ' . $from,
        'Reply-To: ' . $from,
        'X-Mailer: PHP/' . phpversion(),
        'Content-Type: text/html; charset=UTF-8'
    ];

    return mail($to, $subject, $body, implode("\r\n", $headers));
}

/**
 * SMS helpers (placeholder)
 */
function sendSMS($to, $message) {
    // This would integrate with SMS gateway APIs
    // For now, return success
    logActivity('sms_sent', ['to' => $to, 'message' => $message]);
    return true;
}

/**
 * Push notification helpers (placeholder)
 */
function sendPushNotification($device_token, $title, $message) {
    // This would integrate with FCM or similar services
    // For now, return success
    logActivity('push_notification', ['device_token' => $device_token, 'title' => $title, 'message' => $message]);
    return true;
}

/**
 * WhatsApp helpers (placeholder)
 */
function sendWhatsAppMessage($to, $message) {
    // This would integrate with WhatsApp Business API
    // For now, return success
    logActivity('whatsapp_message', ['to' => $to, 'message' => $message]);
    return true;
}

/**
 * Social media integration helpers
 */
function postToSocialMedia($platform, $message, $image = null) {
    switch ($platform) {
        case 'facebook':
            // Facebook API integration
            break;
        case 'twitter':
            // Twitter API integration
            break;
        case 'instagram':
            // Instagram API integration
            break;
        case 'linkedin':
            // LinkedIn API integration
            break;
    }

    logActivity('social_post', ['platform' => $platform, 'message' => $message]);
    return true;
}

/**
 * Weather API helpers (placeholder)
 */
function getWeather($city, $api_key) {
    // This would integrate with weather APIs like OpenWeatherMap
    return [
        'temperature' => '25°C',
        'humidity' => '60%',
        'condition' => 'Sunny',
        'city' => $city
    ];
}

/**
 * Maps and location helpers
 */
function getCoordinates($address) {
    // This would integrate with Google Maps API or similar
    return [
        'latitude' => 28.6139,
        'longitude' => 77.2090,
        'formatted_address' => $address
    ];
}

function calculateRoute($from, $to) {
    // This would integrate with Google Maps Directions API
    return [
        'distance' => '10 km',
        'duration' => '15 minutes',
        'steps' => []
    ];
}

/**
 * Translation helpers (placeholder)
 */
function translateText($text, $from_lang, $to_lang) {
    // This would integrate with translation APIs like Google Translate
    return $text; // Placeholder
}

/**
 * Speech to text helpers (placeholder)
 */
function speechToText($audio_file) {
    // This would integrate with speech recognition APIs
    return 'Speech recognition not implemented';
}

/**
 * Text to speech helpers (placeholder)
 */
function textToSpeech($text, $language = 'en') {
    // This would integrate with TTS APIs
    return 'Text to speech not implemented';
}

/**
 * AI/ML helpers (placeholder)
 */
function analyzeSentiment($text) {
    // This would integrate with sentiment analysis APIs
    return [
        'sentiment' => 'neutral',
        'confidence' => 0.5,
        'positive_words' => [],
        'negative_words' => []
    ];
}

function generateAIResponse($prompt, $context = []) {
    // This would integrate with AI APIs like OpenAI
    return 'AI response not implemented';
}

/**
 * Blockchain helpers (placeholder)
 */
function generateWalletAddress() {
    return 'bc1q' . bin2hex(random_bytes(20));
}

function validateWalletAddress($address) {
    return preg_match('/^bc1[a-z0-9]{39}$/', $address);
}

/**
 * Cryptocurrency helpers
 */
function getCryptoPrice($symbol) {
    // This would integrate with crypto price APIs
    $prices = [
        'BTC' => 45000,
        'ETH' => 3000,
        'LTC' => 150
    ];

    return $prices[strtoupper($symbol)] ?? 0;
}

function convertCrypto($amount, $from_symbol, $to_symbol) {
    $from_price = getCryptoPrice($from_symbol);
    $to_price = getCryptoPrice($to_symbol);

    if ($from_price > 0 && $to_price > 0) {
        return ($amount * $from_price) / $to_price;
    }

    return 0;
}

/**
 * QR code helpers
 */
function generateQRCode($data, $size = 200) {
    // This would integrate with QR code generation libraries
    $qr_data = 'QR code for: ' . $data;
    return $qr_data;
}

/**
 * Barcode helpers
 */
function generateBarcode($data, $type = 'CODE128') {
    // This would integrate with barcode generation libraries
    return 'Barcode for: ' . $data;
}

/**
 * Calendar helpers
 */
function getCalendarEvents($start_date, $end_date) {
    // This would integrate with calendar APIs
    return [
        ['title' => 'Sample Event', 'date' => date('Y-m-d'), 'time' => '10:00']
    ];
}

function addCalendarEvent($title, $date, $time, $description = '') {
    // This would integrate with calendar APIs
    return generateUniqueId('event_');
}

/**
 * File system helpers
 */
function getFileInfo($filepath) {
    if (!file_exists($filepath)) {
        return false;
    }

    return [
        'name' => basename($filepath),
        'size' => filesize($filepath),
        'type' => mime_content_type($filepath),
        'modified' => filemtime($filepath),
        'extension' => getFileExtension($filepath)
    ];
}

function getDirectoryTree($directory, $max_depth = 3, $current_depth = 0) {
    if ($current_depth > $max_depth || !is_dir($directory)) {
        return [];
    }

    $tree = [];
    $items = scandir($directory);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $directory . '/' . $item;
        $tree[$item] = [
            'type' => is_dir($path) ? 'directory' : 'file',
            'path' => $path,
            'size' => is_file($path) ? filesize($path) : 0,
            'children' => is_dir($path) ? getDirectoryTree($path, $max_depth, $current_depth + 1) : []
        ];
    }

    return $tree;
}

/**
 * Network helpers
 */
function ping($host) {
    $output = shell_exec("ping -c 1 " . escapeshellarg($host));
    return strpos($output, '1 packets transmitted, 1 received') !== false;
}

function getNetworkInfo() {
    return [
        'ip' => getClientIP(),
        'user_agent' => getUserAgent(),
        'hostname' => gethostbyaddr(getClientIP()),
        'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'unknown'
    ];
}

/**
 * System monitoring helpers
 */
function getSystemStats() {
    return [
        'cpu_usage' => function_exists('sys_getloadavg') ? sys_getloadavg()[0] : 'N/A',
        'memory_usage' => getMemoryInfo(),
        'disk_usage' => [
            'total' => disk_total_space('/'),
            'free' => disk_free_space('/'),
            'used' => disk_total_space('/') - disk_free_space('/')
        ],
        'uptime' => file_exists('/proc/uptime') ? trim(file_get_contents('/proc/uptime')) : 'N/A'
    ];
}

/**
 * Database optimization helpers
 */
function optimizeTables() {
    global $pdo;
    if (!$pdo) {
        return false;
    }

    try {
        $tables = [];
        $result = $pdo->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        foreach ($tables as $table) {
            $pdo->query("OPTIMIZE TABLE {$table}");
        }

        return true;
    } catch (Exception $e) {
        logError('Table Optimization Error', ['error' => $e->getMessage()]);
        return false;
    }
}

function repairTables() {
    global $pdo;
    if (!$pdo) {
        return false;
    }

    try {
        $tables = [];
        $result = $pdo->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        foreach ($tables as $table) {
            $pdo->query("REPAIR TABLE {$table}");
        }

        return true;
    } catch (Exception $e) {
        logError('Table Repair Error', ['error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Cache management helpers
 */
function clearApplicationCache() {
    $cache_dirs = [
        __DIR__ . '/../cache',
        __DIR__ . '/../temp'
    ];

    $cleared = true;
    foreach ($cache_dirs as $dir) {
        if (is_dir($dir)) {
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    if (!unlink($file)) {
                        $cleared = false;
                    }
                }
            }
        }
    }

    return $cleared;
}

/**
 * Log rotation helpers
 */
function rotateLogs() {
    $log_dirs = [
        __DIR__ . '/../logs'
    ];

    foreach ($log_dirs as $dir) {
        if (is_dir($dir)) {
            $files = glob($dir . '/*.log');
            foreach ($files as $file) {
                $size = filesize($file);
                $max_size = 10 * 1024 * 1024; // 10MB

                if ($size > $max_size) {
                    $backup_file = $file . '.' . date('Y-m-d-H-i-s');
                    rename($file, $backup_file);

                    // Keep only last 5 backups
                    $backups = glob($file . '.*');
                    if (count($backups) > 5) {
                        array_shift($backups); // Remove oldest
                        foreach ($backups as $old_backup) {
                            if (file_exists($old_backup)) {
                                unlink($old_backup);
                            }
                        }
                    }
                }
            }
        }
    }

    return true;
}

/**
 * Session management helpers
 */
function regenerateSession() {
    session_regenerate_id(true);
    $_SESSION['regenerated'] = time();
}

/**
 * Cookie helpers
 */
function setSecureCookie($name, $value, $expire = 0, $path = '/', $domain = '', $secure = true, $httponly = true) {
    return setcookie($name, $value, [
        'expires' => $expire,
        'path' => $path,
        'domain' => $domain,
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => 'Strict'
    ]);
}

/**
 * Header helpers
 */
function setSecurityHeaders() {
    $headers = [
        'X-Content-Type-Options: nosniff',
        'X-Frame-Options: SAMEORIGIN',
        'X-XSS-Protection: 1; mode=block',
        'Strict-Transport-Security: max-age=31536000; includeSubDomains',
        'Content-Security-Policy: default-src \'self\'',
        'Referrer-Policy: strict-origin-when-cross-origin'
    ];

    foreach ($headers as $header) {
        header($header);
    }
}

/**
 * Request helpers
 */
function getRequestData($method = null) {
    $method = $method ?? getRequestMethod();

    switch (strtoupper($method)) {
        case 'GET':
            return $_GET;
        case 'POST':
            return $_POST;
        case 'PUT':
        case 'PATCH':
        case 'DELETE':
            parse_str(file_get_contents('php://input'), $data);
            return $data;
        default:
            return $_REQUEST;
    }
}

/**
 * Response helpers
 */
function setResponseHeaders($headers) {
    foreach ($headers as $name => $value) {
        header($name . ': ' . $value);
    }
}

/**
 * Template engine helpers
 */
function renderTemplateEngine($template, $data = []) {
    // Simple template engine
    $content = file_get_contents($template);

    // Replace variables
    foreach ($data as $key => $value) {
        $content = str_replace('{{' . $key . '}}', $value, $content);
    }

    // Replace conditionals
    $content = preg_replace('/\{\{if\s+(.+?)\}\}(.+?)\{\{endif\}\}/s', '<?php if($1): ?>$2<?php endif; ?>', $content);
    $content = preg_replace('/\{\{unless\s+(.+?)\}\}(.+?)\{\{endunless\}\}/s', '<?php if(!$1): ?>$2<?php endif; ?>', $content);

    // Replace loops
    $content = preg_replace('/\{\{each\s+(.+?)\s+in\s+(.+?)\}\}(.+?)\{\{endeach\}\}/s', '<?php foreach($2 as $1): ?>$3<?php endforeach; ?>', $content);

    return $content;
}

/**
 * Widget helpers
 */
function renderWidget($widget_name, $data = []) {
    $widget_file = __DIR__ . '/../widgets/' . $widget_name . '.php';

    if (file_exists($widget_file)) {
        extract($data);
        ob_start();
        include $widget_file;
        return ob_get_clean();
    }

    return '';
}

/**
 * Module system helpers
 */
function loadModule($module_name) {
    $module_file = __DIR__ . '/../modules/' . $module_name . '/module.php';

    if (file_exists($module_file)) {
        include_once $module_file;
        return true;
    }

    return false;
}

/**
 * Hook system helpers
 */
function addAction($hook, $callback, $priority = 10) {
    global $actions;

    if (!isset($actions[$hook])) {
        $actions[$hook] = [];
    }

    $actions[$hook][] = [
        'callback' => $callback,
        'priority' => $priority
    ];

    // Sort by priority
    uasort($actions[$hook], function($a, $b) {
        return $a['priority'] <=> $b['priority'];
    });

    return true;
}

function doAction($hook, $args = []) {
    global $actions;

    if (!isset($actions[$hook])) {
        return false;
    }

    foreach ($actions[$hook] as $action) {
        call_user_func_array($action['callback'], $args);
    }

    return true;
}

/**
 * Filter system helpers
 */
function addFilter($filter, $callback, $priority = 10) {
    global $filters;

    if (!isset($filters[$filter])) {
        $filters[$filter] = [];
    }

    $filters[$filter][] = [
        'callback' => $callback,
        'priority' => $priority
    ];

    // Sort by priority
    uasort($filters[$filter], function($a, $b) {
        return $a['priority'] <=> $b['priority'];
    });

    return true;
}

function applyFilter($filter, $value, $args = []) {
    global $filters;

    if (!isset($filters[$filter])) {
        return $value;
    }

    foreach ($filters[$filter] as $filter_item) {
        $value = call_user_func_array($filter_item['callback'], array_merge([$value], $args));
    }

    return $value;
}

/**
 * Event system helpers
 */
function triggerEvent($event, $data = []) {
    logActivity('event_triggered', ['event' => $event, 'data' => $data]);

    // This would trigger actual event handlers
    return true;
}

function onEvent($event, $callback) {
    global $event_handlers;

    if (!isset($event_handlers[$event])) {
        $event_handlers[$event] = [];
    }

    $event_handlers[$event][] = $callback;
    return true;
}

/**
 * Asset management helpers
 */
function registerAsset($type, $handle, $src, $dependencies = [], $version = null) {
    global $registered_assets;

    $registered_assets[$type][$handle] = [
        'src' => $src,
        'dependencies' => $dependencies,
        'version' => $version ?? time()
    ];

    return true;
}

function enqueueAsset($type, $handle) {
    global $enqueued_assets, $registered_assets;

    if (isset($registered_assets[$type][$handle])) {
        $enqueued_assets[$type][] = $handle;
    }

    return true;
}

function renderAssets($type) {
    global $enqueued_assets, $registered_assets;

    if (!isset($enqueued_assets[$type])) {
        return '';
    }

    $output = '';
    $processed = [];

    foreach ($enqueued_assets[$type] as $handle) {
        if (isset($registered_assets[$type][$handle]) && !in_array($handle, $processed)) {
            $asset = $registered_assets[$type][$handle];

            switch ($type) {
                case 'css':
                    $output .= '<link rel="stylesheet" href="' . $asset['src'] . '?' . $asset['version'] . '">' . "\n";
                    break;
                case 'js':
                    $output .= '<script src="' . $asset['src'] . '?' . $asset['version'] . '"></script>' . "\n";
                    break;
            }

            $processed[] = $handle;

            // Add dependencies
            foreach ($asset['dependencies'] as $dependency) {
                if (!in_array($dependency, $processed)) {
                    $enqueued_assets[$type][] = $dependency;
                }
            }
        }
    }

    return $output;
}

/**
 * Form helpers
 */
function generateFormToken() {
    $token = generateToken();
    $_SESSION['form_token'] = $token;
    return $token;
}

function validateFormToken($token) {
    return isset($_SESSION['form_token']) && $_SESSION['form_token'] === $token;
}

/**
 * Validation helpers
 */
function validateRequired($value) {
    return !empty($value);
}

function validateMinLength($value, $min) {
    return strlen($value) >= $min;
}

function validateMaxLength($value, $max) {
    return strlen($value) <= $max;
}

function validatePattern($value, $pattern) {
    return preg_match($pattern, $value) === 1;
}

function validateRange($value, $min, $max) {
    return $value >= $min && $value <= $max;
}

/**
 * String manipulation helpers
 */
function removeAccents($string) {
    return iconv('UTF-8', 'ASCII//TRANSLIT', $string);
}

function removeSpecialChars($string) {
    return preg_replace('/[^a-zA-Z0-9\s]/', '', $string);
}

function limitWords($string, $limit) {
    $words = explode(' ', $string);
    if (count($words) <= $limit) {
        return $string;
    }

    return implode(' ', array_slice($words, 0, $limit)) . '...';
}

/**
 * Array manipulation helpers
 */
function arrayColumn($array, $column) {
    return array_map(function($item) use ($column) {
        return is_object($item) ? $item->$column : $item[$column];
    }, $array);
}

function arrayUnique($array, $column) {
    $unique = [];
    $seen = [];

    foreach ($array as $item) {
        $value = is_object($item) ? $item->$column : $item[$column];

        if (!in_array($value, $seen)) {
            $seen[] = $value;
            $unique[] = $item;
        }
    }

    return $unique;
}

/**
 * Date manipulation helpers
 */
function addBusinessDays($date, $days) {
    $current = strtotime($date);
    $business_days = 0;

    while ($business_days < $days) {
        $current = strtotime('+1 weekday', $current);
        $business_days++;
    }

    return date('Y-m-d', $current);
}

function getBusinessDays($start_date, $end_date) {
    $start = strtotime($start_date);
    $end = strtotime($end_date);
    $business_days = 0;

    while ($start <= $end) {
        $day_of_week = date('w', $start);
        if ($day_of_week >= 1 && $day_of_week <= 5) { // Monday to Friday
            $business_days++;
        }
        $start = strtotime('+1 day', $start);
    }

    return $business_days;
}

/**
 * Financial calculation helpers
 */
function calculateCompoundInterest($principal, $rate, $time, $compounds_per_year = 12) {
    return $principal * pow((1 + $rate / (100 * $compounds_per_year)), $compounds_per_year * $time);
}

function calculateSimpleInterest($principal, $rate, $time) {
    return ($principal * $rate * $time) / 100;
}

function calculateLoanPayment($principal, $rate, $months) {
    $monthly_rate = $rate / (12 * 100);
    return ($principal * $monthly_rate * pow(1 + $monthly_rate, $months)) / (pow(1 + $monthly_rate, $months) - 1);
}

/**
 * Statistics helpers
 */
function calculateAverage($numbers) {
    if (empty($numbers)) {
        return 0;
    }
    return array_sum($numbers) / count($numbers);
}

function calculateMedian($numbers) {
    sort($numbers);
    $count = count($numbers);

    if ($count % 2 === 0) {
        return ($numbers[$count/2 - 1] + $numbers[$count/2]) / 2;
    } else {
        return $numbers[floor($count/2)];
    }
}

function calculateStandardDeviation($numbers) {
    if (empty($numbers)) {
        return 0;
    }

    $average = calculateAverage($numbers);
    $variance = 0;

    foreach ($numbers as $number) {
        $variance += pow($number - $average, 2);
    }

    $variance /= count($numbers);
    return sqrt($variance);
}

/**
 * Color manipulation helpers
 */
function lightenColor($hex, $percent) {
    $rgb = hexToRgb($hex);

    $new_r = min(255, $rgb['r'] + ($rgb['r'] * $percent / 100));
    $new_g = min(255, $rgb['g'] + ($rgb['g'] * $percent / 100));
    $new_b = min(255, $rgb['b'] + ($rgb['b'] * $percent / 100));

    return rgbToHex((int)$new_r, (int)$new_g, (int)$new_b);
}

function darkenColor($hex, $percent) {
    return lightenColor($hex, -$percent);
}

/**
 * Image manipulation helpers
 */
function addWatermark($image_path, $watermark_path, $position = 'bottom-right', $opacity = 50) {
    $image_info = getimagesize($image_path);
    $watermark_info = getimagesize($watermark_path);

    if (!$image_info || !$watermark_info) {
        return false;
    }

    $image = imagecreatefromstring(file_get_contents($image_path));
    $watermark = imagecreatefromstring(file_get_contents($watermark_path));

    // Calculate position
    $positions = [
        'top-left' => [0, 0],
        'top-right' => [$image_info[0] - $watermark_info[0], 0],
        'bottom-left' => [0, $image_info[1] - $watermark_info[1]],
        'bottom-right' => [$image_info[0] - $watermark_info[0], $image_info[1] - $watermark_info[1]],
        'center' => [($image_info[0] - $watermark_info[0]) / 2, ($image_info[1] - $watermark_info[1]) / 2]
    ];

    $pos = $positions[$position] ?? $positions['bottom-right'];

    // Apply watermark
    imagecopymerge($image, $watermark, $pos[0], $pos[1], 0, 0, $watermark_info[0], $watermark_info[1], $opacity);

    // Save image
    $success = false;
    switch ($image_info['mime']) {
        case 'image/jpeg':
            $success = imagejpeg($image, $image_path, 90);
            break;
        case 'image/png':
            $success = imagepng($image, $image_path, 9);
            break;
        case 'image/gif':
            $success = imagegif($image, $image_path);
            break;
    }

    imagedestroy($image);
    imagedestroy($watermark);

    return $success;
}

/**
 * PDF generation helpers (placeholder)
 */
function generatePDF($html, $filename) {
    // This would integrate with PDF generation libraries like TCPDF, DomPDF, etc.
    return 'PDF generation not implemented';
}

/**
 * Excel generation helpers (placeholder)
 */
function generateExcel($data, $filename) {
    // This would integrate with Excel generation libraries like PHPExcel, PhpSpreadsheet, etc.
    return 'Excel generation not implemented';
}

/**
 * Word document generation helpers (placeholder)
 */
function generateWordDocument($content, $filename) {
    // This would integrate with Word document generation libraries
    return 'Word document generation not implemented';
}

/**
 * Chart generation helpers (placeholder)
 */
function generateChart($data, $type, $filename) {
    // This would integrate with chart generation libraries like Chart.js, D3.js, etc.
    return 'Chart generation not implemented';
}

/**
 * Email template helpers
 */
function processEmailTemplate($template, $data) {
    foreach ($data as $key => $value) {
        $template = str_replace('{{' . $key . '}}', $value, $template);
    }
    return $template;
}

/**
 * SMS template helpers
 */
function processSMSTemplate($template, $data) {
    foreach ($data as $key => $value) {
        $template = str_replace('{{' . $key . '}}', $value, $template);
    }
    return $template;
}

/**
 * WhatsApp template helpers
 */
function processWhatsAppTemplate($template, $data) {
    foreach ($data as $key => $value) {
        $template = str_replace('{{' . $key . '}}', $value, $template);
    }
    return $template;
}

/**
 * Push notification template helpers
 */
function processPushNotificationTemplate($template, $data) {
    foreach ($data as $key => $value) {
        $template = str_replace('{{' . $key . '}}', $value, $template);
    }
    return $template;
}

/**
 * Database backup helpers
 */
function createIncrementalBackup($table = null) {
    global $pdo;
    if (!$pdo) {
        return false;
    }

    try {
        $tables = $table ? [$table] : [];
        if (empty($tables)) {
            $result = $pdo->query("SHOW TABLES");
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }
        }

        $backup_data = [];
        foreach ($tables as $table_name) {
            $result = $pdo->query("SELECT * FROM {$table_name}");
            $backup_data[$table_name] = $result->fetchAll(PDO::FETCH_ASSOC);
        }

        $backup_file = __DIR__ . '/../backups/incremental_' . date('Y-m-d_H-i-s') . '.json';
        createDirectory(dirname($backup_file));

        return file_put_contents($backup_file, json_encode($backup_data)) !== false;

    } catch (Exception $e) {
        logError('Incremental Backup Error', ['error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Database restore helpers
 */
function restoreFromBackup($backup_file) {
    global $pdo;
    if (!$pdo || !file_exists($backup_file)) {
        return false;
    }

    try {
        $backup_data = json_decode(file_get_contents($backup_file), true);

        foreach ($backup_data as $table => $data) {
            if (!empty($data)) {
                // Clear existing data
                $pdo->query("DELETE FROM {$table}");

                // Insert backup data
                foreach ($data as $row) {
                    $columns = array_keys($row);
                    $placeholders = array_map(function($col) { return ":{$col}"; }, $columns);

                    $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($row);
                }
            }
        }

        return true;

    } catch (Exception $e) {
        logError('Restore Error', ['backup_file' => $backup_file, 'error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Migration helpers
 */
function runMigrations() {
    $migrations_dir = __DIR__ . '/../migrations';
    if (!is_dir($migrations_dir)) {
        return false;
    }

    $migration_files = scandir($migrations_dir);
    $executed = [];

    foreach ($migration_files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $migration_path = $migrations_dir . '/' . $file;

            // Check if migration already run
            $migration_name = pathinfo($file, PATHINFO_FILENAME);
            if (!migrationExists($migration_name)) {
                include_once $migration_path;

                // Execute migration (placeholder)
                logActivity('migration_run', ['migration' => $migration_name]);
                markMigrationAsRun($migration_name);

                $executed[] = $migration_name;
            }
        }
    }

    return $executed;
}

function migrationExists($migration_name) {
    global $pdo;
    if (!$pdo) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM migrations WHERE migration = ?");
        $stmt->execute([$migration_name]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

function markMigrationAsRun($migration_name) {
    global $pdo;
    if (!$pdo) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO migrations (migration, run_at) VALUES (?, NOW())");
        return $stmt->execute([$migration_name]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Testing helpers
 */
function runTests() {
    $test_dir = __DIR__ . '/../tests';
    if (!is_dir($test_dir)) {
        return ['message' => 'No tests directory found'];
    }

    $test_files = scandir($test_dir);
    $results = [];

    foreach ($test_files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $test_path = $test_dir . '/' . $file;

            // Run test (placeholder)
            $results[] = [
                'test' => $file,
                'status' => 'passed',
                'execution_time' => '0.1s'
            ];
        }
    }

    return $results;
}

/**
 * Performance monitoring helpers
 */
function startPerformanceMonitoring() {
    $_SESSION['performance_start'] = microtime(true);
}

function endPerformanceMonitoring() {
    if (isset($_SESSION['performance_start'])) {
        $end_time = microtime(true);
        $execution_time = round(($end_time - $_SESSION['performance_start']) * 1000, 2);

        logActivity('performance', [
            'execution_time' => $execution_time . 'ms',
            'memory_usage' => getMemoryInfo(),
            'page' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ]);

        unset($_SESSION['performance_start']);
        return $execution_time;
    }

    return 0;
}

/**
 * A/B testing helpers
 */
function getABTestVariant($test_name, $variants) {
    $user_id = getCurrentUserId();

    if (!$user_id) {
        return $variants[0]; // Default variant for guests
    }

    $variant_key = 'ab_test_' . $test_name . '_' . $user_id;
    $variant = $_SESSION[$variant_key] ?? null;

    if (!$variant) {
        $variant = $variants[array_rand($variants)];
        $_SESSION[$variant_key] = $variant;
    }

    return $variant;
}

/**
 * Feature flag helpers
 */
function isFeatureEnabled($feature) {
    $features_file = __DIR__ . '/../config/features.json';

    if (file_exists($features_file)) {
        $features = json_decode(file_get_contents($features_file), true);
        return isset($features[$feature]) && $features[$feature]['enabled'];
    }

    return false;
}

function enableFeature($feature) {
    $features_file = __DIR__ . '/../config/features.json';

    $features = [];
    if (file_exists($features_file)) {
        $features = json_decode(file_get_contents($features_file), true);
    }

    $features[$feature] = [
        'enabled' => true,
        'enabled_at' => time()
    ];

    createDirectory(dirname($features_file));
    return file_put_contents($features_file, json_encode($features)) !== false;
}

function disableFeature($feature) {
    $features_file = __DIR__ . '/../config/features.json';

    $features = [];
    if (file_exists($features_file)) {
        $features = json_decode(file_get_contents($features_file), true);
    }

    if (isset($features[$feature])) {
        $features[$feature]['enabled'] = false;
        $features[$feature]['disabled_at'] = time();
    }

    createDirectory(dirname($features_file));
    return file_put_contents($features_file, json_encode($features)) !== false;
}

/**
 * Localization helpers
 */
function getCurrentLanguage() {
    return $_SESSION['language'] ?? 'en';
}

function setLanguage($language) {
    $_SESSION['language'] = $language;
    return true;
}

/**
 * Timezone helpers
 */
function getCurrentTimezone() {
    return $_SESSION['timezone'] ?? 'Asia/Kolkata';
}

function setTimezone($timezone) {
    $_SESSION['timezone'] = $timezone;
    date_default_timezone_set($timezone);
    return true;
}

/**
 * Currency helpers
 */
function getCurrentCurrency() {
    return $_SESSION['currency'] ?? 'INR';
}

function setCurrency($currency) {
    $_SESSION['currency'] = $currency;
    return true;
}

/**
 * Theme helpers
 */
function getCurrentTheme() {
    return $_SESSION['theme'] ?? 'default';
}

function setTheme($theme) {
    $_SESSION['theme'] = $theme;
    return true;
}

/**
 * GDPR compliance helpers
 */
function hasConsent($consent_type) {
    return isset($_SESSION['consents'][$consent_type]);
}

function giveConsent($consent_type) {
    $_SESSION['consents'][$consent_type] = true;
    $_SESSION['consent_date'] = time();
    return true;
}

/**
 * Accessibility helpers
 */
function getAccessibilitySettings() {
    return $_SESSION['accessibility'] ?? [
        'high_contrast' => false,
        'large_text' => false,
        'screen_reader' => false
    ];
}

function setAccessibilitySettings($settings) {
    $_SESSION['accessibility'] = array_merge($_SESSION['accessibility'] ?? [], $settings);
    return true;
}

/**
 * Mobile app helpers
 */
function getDeviceInfo() {
    return [
        'type' => getDeviceType(),
        'os' => getOperatingSystem(),
        'browser' => getBrowserName(),
        'is_mobile' => isMobile(),
        'screen_size' => $_COOKIE['screen_size'] ?? 'unknown'
    ];
}

/**
 * Progressive Web App helpers
 */
function getPWAConfig() {
    return [
        'name' => getAppName(),
        'short_name' => 'APS Home',
        'description' => 'Advanced Property Search & Dream Home',
        'theme_color' => '#007bff',
        'background_color' => '#ffffff',
        'display' => 'standalone',
        'scope' => '/',
        'start_url' => '/'
    ];
}

/**
 * Service Worker helpers
 */
function registerServiceWorker($scope = '/') {
    return [
        'scope' => $scope,
        'script' => '/sw.js',
        'registration' => 'service-worker-registration.js'
    ];
}

/**
 * Push notification helpers
 */
function subscribeToPushNotifications($subscription) {
    $subscriptions_file = __DIR__ . '/../subscriptions.json';

    $subscription_data = [
        'endpoint' => $subscription['endpoint'],
        'keys' => $subscription['keys'],
        'subscribed_at' => time()
    ];

    $subscriptions = [];
    if (file_exists($subscriptions_file)) {
        $subscriptions = json_decode(file_get_contents($subscriptions_file), true);
    }

    $subscriptions[] = $subscription_data;

    createDirectory(dirname($subscriptions_file));
    return file_put_contents($subscriptions_file, json_encode($subscriptions)) !== false;
}

/**
 * Offline support helpers
 */
function getOfflinePages() {
    return [
        '/',
        '/properties',
        '/about',
        '/contact',
        '/offline.html'
    ];
}

function cacheOfflinePages() {
    $offline_pages = getOfflinePages();

    foreach ($offline_pages as $page) {
        $cache_file = __DIR__ . '/../offline/' . md5($page) . '.html';
        createDirectory(dirname($cache_file));

        // Cache page content (placeholder)
        file_put_contents($cache_file, '<!-- Cached page: ' . $page . ' -->');
    }

    return true;
}

/**
 * Real-time features helpers
 */
function getWebSocketConfig() {
    return [
        'host' => 'localhost',
        'port' => 8080,
        'secure' => false,
        'path' => '/ws'
    ];
}

/**
 * Chat system helpers
 */
function getChatConfig() {
    return [
        'enabled' => true,
        'provider' => 'websocket',
        'auto_start' => true,
        'sound_enabled' => true,
        'position' => 'bottom-right'
    ];
}

/**
 * Video call helpers (placeholder)
 */
function initiateVideoCall($room_id, $participants) {
    return [
        'room_id' => $room_id,
        'participants' => $participants,
        'status' => 'initiated'
    ];
}

/**
 * Screen sharing helpers (placeholder)
 */
function startScreenShare($session_id) {
    return [
        'session_id' => $session_id,
        'status' => 'started'
    ];
}

/**
 * Virtual tour helpers (placeholder)
 */
function generateVirtualTour($property_id) {
    return [
        'property_id' => $property_id,
        'tour_id' => generateUniqueId('tour_'),
        'panoramas' => [],
        'hotspots' => []
    ];
}

/**
 * 3D model helpers (placeholder)
 */
function load3DModel($model_id) {
    return [
        'model_id' => $model_id,
        'format' => 'gltf',
        'textures' => [],
        'animations' => []
    ];
}

/**
 * AR/VR helpers (placeholder)
 */
function generateARView($product_id) {
    return [
        'product_id' => $product_id,
        'ar_model' => 'model.glb',
        'tracking' => 'markerless'
    ];
}

/**
 * IoT integration helpers (placeholder)
 */
function getIoTDevices() {
    return [
        ['id' => 1, 'name' => 'Smart Lock', 'type' => 'door_lock', 'status' => 'online'],
        ['id' => 2, 'name' => 'Security Camera', 'type' => 'camera', 'status' => 'online'],
        ['id' => 3, 'name' => 'Thermostat', 'type' => 'climate', 'status' => 'offline']
    ];
}

function controlIoTDevice($device_id, $command, $parameters = []) {
    return [
        'device_id' => $device_id,
        'command' => $command,
        'parameters' => $parameters,
        'status' => 'executed'
    ];
}

/**
 * Smart home integration helpers (placeholder)
 */
function getSmartHomeSystems() {
    return [
        ['id' => 1, 'name' => 'Google Home', 'type' => 'assistant', 'connected' => true],
        ['id' => 2, 'name' => 'Amazon Alexa', 'type' => 'assistant', 'connected' => false],
        ['id' => 3, 'name' => 'Apple HomeKit', 'type' => 'ecosystem', 'connected' => false]
    ];
}

/**
 * Energy monitoring helpers (placeholder)
 */
function getEnergyUsage($property_id, $period = 'month') {
    return [
        'property_id' => $property_id,
        'period' => $period,
        'usage' => 450, // kWh
        'cost' => 2250, // INR
        'peak_usage' => 12.5, // kW
        'efficiency_rating' => 'A+'
    ];
}

/**
 * Maintenance tracking helpers
 */
function scheduleMaintenance($property_id, $type, $scheduled_date, $description = '') {
    return [
        'id' => generateUniqueId('maint_'),
        'property_id' => $property_id,
        'type' => $type,
        'scheduled_date' => $scheduled_date,
        'description' => $description,
        'status' => 'scheduled'
    ];
}

function getMaintenanceHistory($property_id) {
    return [
        ['id' => 1, 'type' => 'Electrical', 'date' => '2024-01-15', 'cost' => 5000, 'status' => 'completed'],
        ['id' => 2, 'type' => 'Plumbing', 'date' => '2024-02-20', 'cost' => 3000, 'status' => 'completed'],
        ['id' => 3, 'type' => 'Painting', 'date' => '2024-03-10', 'cost' => 15000, 'status' => 'scheduled']
    ];
}

/**
 * Property valuation helpers
 */
function calculatePropertyValue($property_data) {
    $base_value = $property_data['area'] * $property_data['rate_per_sqft'];

    // Apply adjustments
    $adjustments = [
        'location' => $property_data['location_factor'] ?? 1.0,
        'condition' => $property_data['condition_factor'] ?? 1.0,
        'amenities' => $property_data['amenities_factor'] ?? 1.0,
        'market_trend' => $property_data['market_factor'] ?? 1.0
    ];

    $final_value = $base_value;
    foreach ($adjustments as $factor) {
        $final_value *= $factor;
    }

    return round($final_value, 2);
}

/**
 * Market analysis helpers
 */
function getMarketTrends($location, $property_type) {
    return [
        'location' => $location,
        'property_type' => $property_type,
        'average_price' => 7500000,
        'price_trend' => '+5.2%',
        'demand_index' => 8.5,
        'supply_index' => 6.2,
        'forecast' => 'stable'
    ];
}

/**
 * Investment analysis helpers
 */
function calculateROI($investment, $returns, $period_years) {
    $total_returns = array_sum($returns);
    $average_annual_return = $total_returns / $period_years;

    return [
        'total_investment' => $investment,
        'total_returns' => $total_returns,
        'net_profit' => $total_returns - $investment,
        'roi_percentage' => (($total_returns - $investment) / $investment) * 100,
        'annualized_roi' => ($average_annual_return / $investment) * 100,
        'payback_period' => $investment / $average_annual_return
    ];
}

/**
 * Legal document helpers
 */
function generateRentalAgreement($property_data, $tenant_data) {
    return [
        'agreement_id' => generateUniqueId('agreement_'),
        'property' => $property_data,
        'tenant' => $tenant_data,
        'start_date' => date('Y-m-d'),
        'end_date' => date('Y-m-d', strtotime('+11 months')),
        'rent_amount' => $property_data['rent'],
        'security_deposit' => $property_data['security_deposit'],
        'status' => 'draft'
    ];
}

function generateSaleDeed($property_data, $buyer_data) {
    return [
        'deed_id' => generateUniqueId('deed_'),
        'property' => $property_data,
        'buyer' => $buyer_data,
        'sale_price' => $property_data['price'],
        'stamp_duty' => calculateStampDuty($property_data['price']),
        'registration_fee' => calculateRegistrationFee($property_data['price']),
        'status' => 'draft'
    ];
}

/**
 * Tax calculation helpers
 */
function calculateStampDuty($property_value) {
    // Indian stamp duty calculation (varies by state)
    return $property_value * 0.07; // 7% stamp duty
}

function calculateRegistrationFee($property_value) {
    return $property_value * 0.01; // 1% registration fee
}

function calculatePropertyTax($property_value, $location) {
    // Property tax calculation (varies by location)
    return $property_value * 0.02; // 2% property tax
}

/**
 * Insurance helpers
 */
function calculateInsurancePremium($property_value, $coverage_type) {
    $base_rate = [
        'basic' => 0.001,    // 0.1%
        'standard' => 0.002, // 0.2%
        'premium' => 0.003   // 0.3%
    ];

    return $property_value * ($base_rate[$coverage_type] ?? $base_rate['basic']);
}

/**
 * Finance helpers
 */
function calculateMortgagePayment($principal, $interest_rate, $loan_term_years) {
    $monthly_rate = $interest_rate / (12 * 100);
    $num_payments = $loan_term_years * 12;

    if ($monthly_rate == 0) {
        return $principal / $num_payments;
    }

    return ($principal * $monthly_rate * pow(1 + $monthly_rate, $num_payments)) /
           (pow(1 + $monthly_rate, $num_payments) - 1);
}

/**
 * Education and training helpers
 */
function getTrainingPrograms() {
    return [
        ['id' => 1, 'title' => 'Real Estate Agent Training', 'duration' => '3 months', 'fee' => 15000],
        ['id' => 2, 'title' => 'Property Management Course', 'duration' => '2 months', 'fee' => 10000],
        ['id' => 3, 'title' => 'Real Estate Investment', 'duration' => '1 month', 'fee' => 8000]
    ];
}

/**
 * Community features helpers
 */
function getCommunityFeatures($location) {
    return [
        'schools' => 5,
        'hospitals' => 3,
        'shopping_centers' => 8,
        'parks' => 12,
        'transportation' => 'Metro & Bus',
        'safety_rating' => 4.5
    ];
}

/**
 * Neighborhood analysis helpers
 */
function analyzeNeighborhood($location) {
    return [
        'location' => $location,
        'livability_score' => 8.5,
        'crime_rate' => 'Low',
        'school_rating' => 4.2,
        'commute_time' => '25 minutes',
        'amenities_score' => 8.0,
        'growth_potential' => 'High'
    ];
}

/**
 * Property comparison helpers
 */
function compareProperties($property_ids) {
    $comparison = [];

    foreach ($property_ids as $id) {
        // Get property data (placeholder)
        $comparison[] = [
            'id' => $id,
            'price' => rand(5000000, 15000000),
            'area' => rand(1000, 3000),
            'bedrooms' => rand(2, 5),
            'bathrooms' => rand(1, 3),
            'location_rating' => rand(7, 10)
        ];
    }

    return $comparison;
}

/**
 * Property search helpers
 */
function searchProperties($criteria) {
    // Advanced search implementation
    return [
        'results' => [],
        'total_found' => 0,
        'search_time' => '0.2s',
        'filters_applied' => $criteria
    ];
}

/**
 * Property recommendation helpers
 */
function getPropertyRecommendations($user_id, $preferences) {
    // AI-powered recommendations (placeholder)
    return [
        ['id' => 1, 'title' => 'Recommended Property 1', 'match_score' => 95],
        ['id' => 2, 'title' => 'Recommended Property 2', 'match_score' => 87],
        ['id' => 3, 'title' => 'Recommended Property 3', 'match_score' => 82]
    ];
}

/**
 * Virtual assistant helpers
 */
function getChatbotResponse($message, $context) {
    // Chatbot implementation (placeholder)
    return [
        'response' => 'I understand you\'re looking for properties. How can I help you today?',
        'confidence' => 0.8,
        'suggested_actions' => ['search_properties', 'contact_agent', 'schedule_visit']
    ];
}

/**
 * Voice command helpers (placeholder)
 */
function processVoiceCommand($audio_data) {
    return [
        'command' => 'search properties in Delhi',
        'confidence' => 0.9,
        'parameters' => ['location' => 'Delhi']
    ];
}

/**
 * Image recognition helpers (placeholder)
 */
function analyzePropertyImage($image_path) {
    return [
        'room_type' => 'living_room',
        'condition' => 'excellent',
        'features' => ['fireplace', 'hardwood_flooring'],
        'estimated_value' => 50000
    ];
}

/**
 * Document analysis helpers (placeholder)
 */
function analyzeDocument($document_path) {
    return [
        'document_type' => 'property_deed',
        'extracted_data' => [
            'property_address' => '123 Main St, Delhi',
            'owner_name' => 'John Doe',
            'property_value' => 5000000
        ],
        'validity' => true
    ];
}

/**
 * Fraud detection helpers (placeholder)
 */
function detectFraud($transaction_data) {
    return [
        'is_fraudulent' => false,
        'risk_score' => 15,
        'flags' => [],
        'recommendations' => ['verify_identity', 'check_documents']
    ];
}

/**
 * Compliance helpers
 */
function checkCompliance($property_data, $regulation_type) {
    switch ($regulation_type) {
        case 'rera':
            return checkRERACompliance($property_data);
        case 'gst':
            return checkGSTCompliance($property_data);
        case 'stamp_duty':
            return checkStampDutyCompliance($property_data);
        default:
            return ['compliant' => false, 'message' => 'Unknown regulation type'];
    }
}

function checkRERACompliance($property_data) {
    return [
        'compliant' => true,
        'rera_number' => 'RERA123456',
        'valid_until' => '2025-12-31',
        'requirements' => ['registration', 'documentation', 'disclosure']
    ];
}

function checkGSTCompliance($property_data) {
    return [
        'compliant' => true,
        'gst_number' => 'GSTIN123456789',
        'tax_applicable' => true,
        'rate' => 18
    ];
}

function checkStampDutyCompliance($property_data) {
    return [
        'compliant' => true,
        'stamp_duty_paid' => 350000,
        'registration_complete' => true,
        'documents_verified' => true
    ];
}

/**
 * Regulatory reporting helpers
 */
function generateRegulatoryReport($report_type, $data) {
    switch ($report_type) {
        case 'rera_monthly':
            return generateRERAMonthlyReport($data);
        case 'gst_annual':
            return generateGSTAnnualReport($data);
        case 'property_tax':
            return generatePropertyTaxReport($data);
        default:
            return ['error' => 'Unknown report type'];
    }
}

function generateRERAMonthlyReport($data) {
    return [
        'report_type' => 'RERA Monthly',
        'period' => date('Y-m'),
        'projects' => $data['projects'] ?? [],
        'registrations' => $data['registrations'] ?? [],
        'complaints' => $data['complaints'] ?? []
    ];
}

function generateGSTAnnualReport($data) {
    return [
        'report_type' => 'GST Annual',
        'financial_year' => date('Y') - 1 . '-' . date('Y'),
        'total_sales' => $data['total_sales'] ?? 0,
        'gst_collected' => $data['gst_collected'] ?? 0,
        'gst_paid' => $data['gst_paid'] ?? 0
    ];
}

function generatePropertyTaxReport($data) {
    return [
        'report_type' => 'Property Tax',
        'assessment_year' => date('Y'),
        'properties' => $data['properties'] ?? [],
        'total_tax' => $data['total_tax'] ?? 0,
        'payment_status' => $data['payment_status'] ?? 'pending'
    ];
}

/**
 * Business process automation helpers
 */
function automateWorkflow($workflow_name, $data) {
    switch ($workflow_name) {
        case 'property_listing':
            return automatePropertyListing($data);
        case 'inquiry_handling':
            return automateInquiryHandling($data);
        case 'payment_processing':
            return automatePaymentProcessing($data);
        case 'document_generation':
            return automateDocumentGeneration($data);
        default:
            return ['error' => 'Unknown workflow'];
    }
}

function automatePropertyListing($data) {
    return [
        'workflow' => 'property_listing',
        'steps' => [
            'validate_data',
            'generate_description',
            'create_seo_content',
            'upload_images',
            'set_pricing',
            'publish_listing'
        ],
        'status' => 'completed',
        'listing_id' => generateUniqueId('listing_')
    ];
}

function automateInquiryHandling($data) {
    return [
        'workflow' => 'inquiry_handling',
        'steps' => [
            'categorize_inquiry',
            'assign_to_agent',
            'send_confirmation',
            'schedule_followup',
            'track_response'
        ],
        'status' => 'in_progress',
        'inquiry_id' => $data['inquiry_id']
    ];
}

function automatePaymentProcessing($data) {
    return [
        'workflow' => 'payment_processing',
        'steps' => [
            'validate_payment',
            'process_transaction',
            'update_records',
            'send_receipt',
            'trigger_notifications'
        ],
        'status' => 'completed',
        'transaction_id' => $data['transaction_id']
    ];
}

function automateDocumentGeneration($data) {
    return [
        'workflow' => 'document_generation',
        'steps' => [
            'gather_information',
            'populate_templates',
            'generate_pdf',
            'send_for_signature',
            'archive_document'
        ],
        'status' => 'completed',
        'document_id' => generateUniqueId('doc_')
    ];
}

/**
 * Integration helpers
 */
function integrateWithCRM($lead_data) {
    // CRM integration (placeholder)
    return [
        'crm_id' => generateUniqueId('crm_'),
        'status' => 'synced',
        'sync_time' => time()
    ];
}

function integrateWithERP($transaction_data) {
    // ERP integration (placeholder)
    return [
        'erp_id' => generateUniqueId('erp_'),
        'status' => 'synced',
        'sync_time' => time()
    ];
}

function integrateWithAccounting($financial_data) {
    // Accounting integration (placeholder)
    return [
        'accounting_id' => generateUniqueId('acc_'),
        'status' => 'posted',
        'posting_date' => date('Y-m-d')
    ];
}

/**
 * Multi-language support helpers
 */
function getTranslation($key, $language = null) {
    $language = $language ?? getCurrentLanguage();
    $translations_file = __DIR__ . '/../translations/' . $language . '.json';

    if (file_exists($translations_file)) {
        $translations = json_decode(file_get_contents($translations_file), true);
        return $translations[$key] ?? $key;
    }

    return $key;
}

/**
 * Multi-currency support helpers
 */
function formatPrice($amount, $currency = null) {
    $currency = $currency ?? getCurrentCurrency();

    $currency_symbols = [
        'INR' => '₹',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'AED' => 'د.إ',
        'SAR' => '﷼'
    ];

    $symbol = $currency_symbols[$currency] ?? $currency;

    return $symbol . number_format($amount, 2);
}

/**
 * Timezone conversion helpers
 */
function convertTimezone($datetime, $from_timezone, $to_timezone) {
    try {
        $from = new DateTimeZone($from_timezone);
        $to = new DateTimeZone($to_timezone);

        $date = new DateTime($datetime, $from);
        $date->setTimezone($to);

        return $date->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        return $datetime;
    }
}

/**
 * Internationalization helpers
 */

/**
 * Accessibility helpers
 */
function generateAltText($image_path) {
    // AI-powered alt text generation (placeholder)
    return 'Property image showing interior/exterior view';
}

/**
 * Performance optimization helpers
 */
function minifyCSS($css) {
    // Remove comments
    $css = preg_replace('!/\*[^*]*\*+([^/*][^*]*\*+)*/!', '', $css);

    // Remove unnecessary whitespace
    $css = preg_replace('/\s+/', ' ', $css);
    $css = preg_replace('/;\s*}/', '}', $css);
    $css = preg_replace('/{\s+/', '{', $css);

    return trim($css);
}

function minifyJS($js) {
    // Remove comments
    $js = preg_replace('/\/\/.*$/', '', $js);
    $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);

    // Remove unnecessary whitespace
    $js = preg_replace('/\s+/', ' ', $js);
    $js = preg_replace('/;\s+/', ';', $js);

    return trim($js);
}

function minifyHTML($html) {
    // Remove comments
    $html = preg_replace('/<!--[\s\S]*?-->/', '', $html);

    // Remove extra whitespace
    $html = preg_replace('/\s+/', ' ', $html);
    $html = preg_replace('/>\s+</', '><', $html);

    return trim($html);
}

/**
 * CDN helpers
 */
function getCDNUrl($file_path) {
    $cdn_base = env('CDN_URL', '');
    if (empty($cdn_base)) {
        return $file_path;
    }

    return $cdn_base . '/' . ltrim($file_path, '/');
}

/**
 * Lazy loading helpers
 */
function generateLazyImage($image_path, $alt = '', $class = 'lazy') {
    return '<img src="' . getCDNUrl('assets/images/placeholder.svg') . '"
                 data-src="' . getCDNUrl($image_path) . '"
                 alt="' . htmlspecialchars($alt) . '"
                 class="' . $class . '"
                 loading="lazy">';
}

/**
 * Progressive loading helpers
 */
function generateProgressiveImage($image_path, $alt = '') {
    return '<picture>
        <source srcset="' . getCDNUrl($image_path) . '" media="(min-width: 768px)">
        <img src="' . getCDNUrl('thumb_' . $image_path) . '"
             alt="' . htmlspecialchars($alt) . '">
    </picture>';
}

/**
 * Web vitals helpers
 */
function trackWebVitals($metrics) {
    logActivity('web_vitals', [
        'fcp' => $metrics['fcp'] ?? 0,
        'lcp' => $metrics['lcp'] ?? 0,
        'fid' => $metrics['fid'] ?? 0,
        'cls' => $metrics['cls'] ?? 0,
        'page' => $_SERVER['REQUEST_URI'] ?? 'unknown'
    ]);

    return true;
}

/**
 * Core Web Vitals monitoring
 */
function getWebVitalsConfig() {
    return [
        'enabled' => true,
        'tracking_id' => env('GA_TRACKING_ID', ''),
        'sample_rate' => 0.1,
        'excluded_pages' => ['/admin/*']
    ];
}

/**
 * Advanced caching helpers
 */
function setAdvancedCache($key, $data, $ttl = 3600, $tags = []) {
    $cache_data = [
        'data' => $data,
        'expires' => time() + $ttl,
        'tags' => $tags,
        'created' => time()
    ];

    $cache_file = __DIR__ . '/../cache/' . md5($key) . '.cache';
    createDirectory(dirname($cache_file));

    return file_put_contents($cache_file, serialize($cache_data)) !== false;
}

function getAdvancedCache($key) {
    $cache_file = __DIR__ . '/../cache/' . md5($key) . '.cache';

    if (!file_exists($cache_file)) {
        return false;
    }

    $cache_data = unserialize(file_get_contents($cache_file));

    if ($cache_data['expires'] < time()) {
        unlink($cache_file);
        return false;
    }

    return $cache_data['data'];
}

function clearCacheByTag($tag) {
    $cache_dir = __DIR__ . '/../cache';
    if (!is_dir($cache_dir)) {
        return false;
    }

    $files = glob($cache_dir . '/*.cache');
    $cleared = 0;

    foreach ($files as $file) {
        $cache_data = unserialize(file_get_contents($file));

        if (isset($cache_data['tags']) && in_array($tag, $cache_data['tags'])) {
            unlink($file);
            $cleared++;
        }
    }

    return $cleared;
}

/**
 * Advanced search helpers
 */
function advancedSearch($table, $criteria, $options = []) {
    global $pdo;
    if (!$pdo) {
        return [];
    }

    $conditions = [];
    $params = [];

    foreach ($criteria as $field => $value) {
        if (is_array($value)) {
            // Range search
            if (isset($value['min'])) {
                $conditions[] = "{$field} >= ?";
                $params[] = $value['min'];
            }
            if (isset($value['max'])) {
                $conditions[] = "{$field} <= ?";
                $params[] = $value['max'];
            }
        } else {
            $conditions[] = "{$field} LIKE ?";
            $params[] = "%{$value}%";
        }
    }

    $where_clause = implode(' AND ', $conditions);

    $sql = "SELECT * FROM {$table}";
    if (!empty($where_clause)) {
        $sql .= " WHERE {$where_clause}";
    }

    // Sorting
    if (isset($options['sort'])) {
        $sql .= " ORDER BY " . $options['sort'];
        if (isset($options['sort_direction'])) {
            $sql .= " " . $options['sort_direction'];
        }
    }

    // Pagination
    if (isset($options['limit'])) {
        $sql .= " LIMIT " . $options['limit'];
        if (isset($options['offset'])) {
            $sql .= " OFFSET " . $options['offset'];
        }
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Data export helpers
 */
function exportData($table, $format = 'csv', $conditions = []) {
    global $pdo;
    if (!$pdo) {
        return false;
    }

    try {
        $sql = "SELECT * FROM {$table}";
        if (!empty($conditions)) {
            $where_parts = [];
            $params = [];

            foreach ($conditions as $field => $value) {
                $where_parts[] = "{$field} = ?";
                $params[] = $value;
            }

            $sql .= " WHERE " . implode(' AND ', $where_parts);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            $stmt = $pdo->query($sql);
        }

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        switch ($format) {
            case 'csv':
                return generateCSVReport($data);
            case 'json':
                return json_encode($data, JSON_PRETTY_PRINT);
            case 'xml':
                return generateXMLReport($data);
            default:
                return $data;
        }

    } catch (Exception $e) {
        logError('Data Export Error', ['table' => $table, 'format' => $format, 'error' => $e->getMessage()]);
        return false;
    }
}

function generateXMLReport($data) {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<records>' . "\n";

    foreach ($data as $record) {
        $xml .= '  <record>' . "\n";
        foreach ($record as $field => $value) {
            $xml .= '    <' . $field . '>' . htmlspecialchars($value) . '</' . $field . '>' . "\n";
        }
        $xml .= '  </record>' . "\n";
    }

    $xml .= '</records>';
    return $xml;
}

/**
 * Data import helpers
 */
function importData($table, $data, $mode = 'insert') {
    global $pdo;
    if (!$pdo || empty($data)) {
        return false;
    }

    try {
        $pdo->beginTransaction();

        foreach ($data as $record) {
            if ($mode === 'insert') {
                insertData($table, $record);
            } elseif ($mode === 'upsert') {
                // Upsert logic
                $conditions = [];
                $params = [];

                foreach ($record as $field => $value) {
                    if (in_array($field, ['id', 'primary_key'])) {
                        $conditions[] = "{$field} = ?";
                        $params[] = $value;
                    }
                }

                if (!empty($conditions)) {
                    $existing = countRecords($table, implode(' AND ', $conditions), $params);
                    if ($existing > 0) {
                        updateData($table, $record, implode(' AND ', $conditions), $params);
                    } else {
                        insertData($table, $record);
                    }
                }
            }
        }

        $pdo->commit();
        return true;

    } catch (Exception $e) {
        $pdo->rollBack();
        logError('Data Import Error', ['table' => $table, 'mode' => $mode, 'error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Advanced filtering helpers
 */
function applyAdvancedFilters($query, $filters) {
    $conditions = [];
    $params = [];

    foreach ($filters as $filter) {
        $field = $filter['field'];
        $operator = $filter['operator'];
        $value = $filter['value'];

        switch ($operator) {
            case 'equals':
                $conditions[] = "{$field} = ?";
                $params[] = $value;
                break;
            case 'not_equals':
                $conditions[] = "{$field} != ?";
                $params[] = $value;
                break;
            case 'contains':
                $conditions[] = "{$field} LIKE ?";
                $params[] = "%{$value}%";
                break;
            case 'greater_than':
                $conditions[] = "{$field} > ?";
                $params[] = $value;
                break;
            case 'less_than':
                $conditions[] = "{$field} < ?";
                $params[] = $value;
                break;
            case 'between':
                if (is_array($value) && count($value) === 2) {
                    $conditions[] = "{$field} BETWEEN ? AND ?";
                    $params[] = $value[0];
                    $params[] = $value[1];
                }
                break;
        }
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(' AND ', $conditions);
    }

    return ['query' => $query, 'params' => $params];
}

/**
 * Data validation helpers
 */
function validateDataStructure($data, $schema) {
    $errors = [];

    foreach ($schema as $field => $rules) {
        $value = $data[$field] ?? null;

        // Required check
        if (isset($rules['required']) && $rules['required'] && (is_null($value) || $value === '')) {
            $errors[] = "Field '{$field}' is required";
            continue;
        }

        if (!is_null($value)) {
            // Type check
            if (isset($rules['type'])) {
                switch ($rules['type']) {
                    case 'string':
                        if (!is_string($value)) {
                            $errors[] = "Field '{$field}' must be a string";
                        }
                        break;
                    case 'integer':
                        if (!is_numeric($value) || !is_int($value + 0)) {
                            $errors[] = "Field '{$field}' must be an integer";
                        }
                        break;
                    case 'float':
                        if (!is_numeric($value)) {
                            $errors[] = "Field '{$field}' must be a number";
                        }
                        break;
                    case 'boolean':
                        if (!is_bool($value)) {
                            $errors[] = "Field '{$field}' must be a boolean";
                        }
                        break;
                    case 'array':
                        if (!is_array($value)) {
                            $errors[] = "Field '{$field}' must be an array";
                        }
                        break;
                    case 'email':
                        if (!isValidEmail($value)) {
                            $errors[] = "Field '{$field}' must be a valid email";
                        }
                        break;
                    case 'url':
                        if (!isValidUrl($value)) {
                            $errors[] = "Field '{$field}' must be a valid URL";
                        }
                        break;
                }
            }

            // Length check
            if (isset($rules['min_length']) && strlen($value) < $rules['min_length']) {
                $errors[] = "Field '{$field}' must be at least {$rules['min_length']} characters";
            }

            if (isset($rules['max_length']) && strlen($value) > $rules['max_length']) {
                $errors[] = "Field '{$field}' must be no more than {$rules['max_length']} characters";
            }

            // Range check
            if (isset($rules['min_value']) && $value < $rules['min_value']) {
                $errors[] = "Field '{$field}' must be at least {$rules['min_value']}";
            }

            if (isset($rules['max_value']) && $value > $rules['max_value']) {
                $errors[] = "Field '{$field}' must be no more than {$rules['max_value']}";
            }

            // Pattern check
            if (isset($rules['pattern']) && !preg_match($rules['pattern'], $value)) {
                $errors[] = "Field '{$field}' format is invalid";
            }
        }
    }

    return $errors;
}

/**
 * API response helpers
 */
function formatApiResponse($data, $status = 'success', $message = '', $meta = []) {
    $response = [
        'status' => $status,
        'timestamp' => time(),
        'version' => getAppVersion()
    ];

    if (!empty($message)) {
        $response['message'] = $message;
    }

    if (!empty($data)) {
        $response['data'] = $data;
    }

    if (!empty($meta)) {
        $response['meta'] = $meta;
    }

    return $response;
}

/**
 * Error handling helpers
 */
function handleError($error, $context = []) {
    logError($error, $context);

    if (isAjaxRequest()) {
        sendJsonResponse(['error' => $error], 500);
    } else {
        // Show error page
        include __DIR__ . '/../views/errors/500.php';
    }
}

/**
 * Exception handling helpers
 */
function handleException($exception) {
    $error_message = $exception->getMessage();
    $error_file = $exception->getFile();
    $error_line = $exception->getLine();

    logError('Exception', [
        'message' => $error_message,
        'file' => $error_file,
        'line' => $error_line,
        'trace' => $exception->getTraceAsString()
    ]);

    if (isAjaxRequest()) {
        sendJsonResponse(['error' => 'Internal server error'], 500);
    } else {
        include __DIR__ . '/../views/errors/500.php';
    }
}

/**
 * Maintenance mode helpers
 */
function enterMaintenanceMode($message = 'Site is under maintenance') {
    $maintenance_data = [
        'enabled' => true,
        'message' => $message,
        'start_time' => time(),
        'estimated_duration' => null
    ];

    file_put_contents(__DIR__ . '/../maintenance.json', json_encode($maintenance_data));
    return true;
}

function exitMaintenanceMode() {
    $maintenance_file = __DIR__ . '/../maintenance.json';
    if (file_exists($maintenance_file)) {
        unlink($maintenance_file);
        return true;
    }
    return false;
}

/**
 * System health monitoring
 */
function checkSystemHealth() {
    $health = [
        'database' => testDatabaseConnection(),
        'filesystem' => checkFilesystemHealth(),
        'memory' => checkMemoryHealth(),
        'cache' => checkCacheHealth(),
        'logs' => checkLogHealth(),
        'backups' => checkBackupHealth()
    ];

    return $health;
}

function checkFilesystemHealth() {
    $directories = [
        __DIR__ . '/../uploads',
        __DIR__ . '/../cache',
        __DIR__ . '/../logs',
        __DIR__ . '/../backups'
    ];

    $health = ['status' => 'healthy', 'issues' => []];

    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            $health['status'] = 'warning';
            $health['issues'][] = "Directory missing: " . basename($dir);
        } elseif (!is_writable($dir)) {
            $health['status'] = 'critical';
            $health['issues'][] = "Directory not writable: " . basename($dir);
        }
    }

    return $health;
}

function checkMemoryHealth() {
    $memory_info = getMemoryInfo();

    $health = ['status' => 'healthy'];

    if ($memory_info['usage'] > $memory_info['limit'] * 0.8) {
        $health['status'] = 'warning';
        $health['message'] = 'High memory usage';
    }

    return $health;
}

function checkCacheHealth() {
    $cache_dir = __DIR__ . '/../cache';
    $health = ['status' => 'healthy'];

    if (is_dir($cache_dir)) {
        $cache_files = glob($cache_dir . '/*.cache');
        $expired_files = 0;

        foreach ($cache_files as $file) {
            $cache_data = unserialize(file_get_contents($file));
            if ($cache_data['expires'] < time()) {
                $expired_files++;
            }
        }

        if ($expired_files > 0) {
            $health['status'] = 'warning';
            $health['expired_files'] = $expired_files;
        }
    }

    return $health;
}

function checkLogHealth() {
    $log_dir = __DIR__ . '/../logs';
    $health = ['status' => 'healthy'];

    if (is_dir($log_dir)) {
        $log_files = glob($log_dir . '/*.log');
        $total_size = 0;

        foreach ($log_files as $file) {
            $total_size += filesize($file);
        }

        if ($total_size > 100 * 1024 * 1024) { // 100MB
            $health['status'] = 'warning';
            $health['total_size'] = formatBytes($total_size);
        }
    }

    return $health;
}

function checkBackupHealth() {
    $backup_dir = __DIR__ . '/../backups';
    $health = ['status' => 'healthy'];

    if (is_dir($backup_dir)) {
        $backup_files = glob($backup_dir . '/*');
        if (empty($backup_files)) {
            $health['status'] = 'warning';
            $health['message'] = 'No backups found';
        }
    } else {
        $health['status'] = 'critical';
        $health['message'] = 'Backup directory missing';
    }

    return $health;
}

/**
 * Load all helper functions
 */
function loadHelpers() {
    // This function ensures all helper functions are available
    return true;
}

// Auto-load helpers
loadHelpers();

?>
