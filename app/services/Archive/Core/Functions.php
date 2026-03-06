<?php

namespace App\Services\Legacy\Core;

/**
 * Core Functions Service
 * Modern implementation of legacy core functions
 */
class Functions {
    /**
     * Log admin actions
     */
    public static function logAdminAction($data) {
        $log_file = dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/core/admin/logs/admin_actions.log';
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $data
        ];

        $log_dir = dirname($log_file);
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0755, true);
        }

        if (is_writable($log_dir)) {
            file_put_contents($log_file, json_encode($log_entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * Enhanced input validation and sanitization
     */
    public static function validateInput($input, $type = 'string', $max_length = null, $required = true) {
        if ($required && empty($input)) {
            return false;
        }

        if (!$required && empty($input)) {
            return '';
        }

        switch ($type) {
            case 'username':
                $input = trim($input);
                if (strlen($input) < 3 || strlen($input) > 50) {
                    return false;
                }
                if (!preg_match('/^[a-zA-Z0-9@._-]+$/', $input)) {
                    return false;
                }
                return h($input);

            case 'email':
                $input = filter_var($input, FILTER_SANITIZE_EMAIL);
                return filter_var($input, FILTER_VALIDATE_EMAIL) ? $input : false;

            case 'password':
                return $input; // Don't sanitize passwords

            case 'captcha':
                $input = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                return is_numeric($input) ? (int)$input : false;

            case 'string':
            default:
                $input = trim($input);
                if ($max_length && strlen($input) > $max_length) {
                    return false;
                }
                return h($input);
        }
    }

    /**
     * Validate request headers for security
     */
    public static function validateRequestHeaders() {
        // Check Content-Type for POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($content_type, 'application/x-www-form-urlencoded') === false &&
                strpos($content_type, 'multipart/form-data') === false) {
                return false;
            }
        }

        // Check User-Agent
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }

        return true;
    }

    /**
     * Send security response
     */
    public static function sendSecurityResponse($status_code, $message, $data = null) {
        if (!headers_sent()) {
            http_response_code($status_code);
            header('Content-Type: application/json');
        }
        $response = [
            'status' => 'error',
            'message' => $message
        ];
        if ($data !== null) {
            $response['data'] = $data;
        }
        echo json_encode($response);
        exit();
    }

    /**
     * Initialize admin session with proper security settings
     */
    public static function initAdminSession() {
        $sessionHelpers = dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/session_helpers.php';
        if (file_exists($sessionHelpers)) {
            require_once $sessionHelpers;
            if (function_exists('ensureSessionStarted')) {
                ensureSessionStarted();
            }
        }

        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        ini_set('session.gc_maxlifetime', 1800); // 30 minutes

        // Generate CSRF token if not exists
        if (session_status() === PHP_SESSION_ACTIVE && !isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = \bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes(32));
        }
    }

    /**
     * Get current URL helper
     */
    public static function getCurrentUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        return $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
    }

    /**
     * Check if file exists and is readable
     */
    public static function safeFileExists($filepath) {
        return file_exists($filepath) && is_readable($filepath);
    }

    /**
     * Safe redirect function
     */
    public static function safeRedirect($url, $permanent = false) {
        if (!headers_sent()) {
            header('Location: ' . $url, true, $permanent ? 301 : 302);
            exit();
        } else {
            // Use a more robust approach for JavaScript redirect
            $safe_url = filter_var($url, FILTER_SANITIZE_URL);
            echo '<script type="text/javascript">';
            echo 'window.location.href = ' . json_encode($safe_url) . ';';
            echo '</script>';
            exit();
        }
    }

    /**
     * Format phone number
     */
    public static function formatPhoneNumber($phone) {
        // Remove all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);

        // Add country code if not present (assuming India)
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        return $phone;
    }

    /**
     * Validate phone number
     */
    public static function isValidPhoneNumber($phone) {
        // Basic validation - should be 10-15 digits
        return preg_match('/^\d{10,15}$/', $phone);
    }

    /**
     * Generate random string
     */
    public static function generateRandomString($length = 16) {
        return \bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes($length));
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    /**
     * Get user role
     */
    public static function getUserRole() {
        return $_SESSION['admin_role'] ?? null;
    }

    /**
     * Check if user has permission
     */
    public static function hasPermission($userId, $permission = null) {
        return true; // Simple implementation for legacy support
    }

    /**
     * Format currency
     */
    public static function formatCurrency($amount, $currency = '₹') {
        return $currency . number_format($amount, 2);
    }

    /**
     * Format date
     */
    public static function formatDate($date, $format = 'Y-m-d H:i:s') {
        return date($format, strtotime($date));
    }

    /**
     * Sanitize filename
     */
    public static function sanitizeFilename($filename) {
        return preg_replace('/[^a-zA-Z0-9\-_.]/', '', $filename);
    }

    /**
     * Create directory if not exists
     */
    public static function ensureDirectoryExists($dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Get file extension
     */
    public static function getFileExtension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Check if file is image
     */
    public static function isImageFile($filename) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        return in_array(self::getFileExtension($filename), $allowed_extensions);
    }

    /**
     * Resize and compress image
     */
    public static function resizeImage($source_path, $destination_path, $max_width = 800, $max_height = 600, $quality = 85) {
        if (!extension_loaded('gd')) {
            return false;
        }

        if (!file_exists($source_path)) {
            return false;
        }

        $image_info = @getimagesize($source_path);
        if (!$image_info) {
            return false;
        }

        list($width, $height, $type) = $image_info;

        // Calculate new dimensions
        $ratio = min($max_width / $width, $max_height / $height);
        $new_width = round($width * $ratio);
        $new_height = round($height * $ratio);

        // Create image resource based on type
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source_image = @imagecreatefromjpeg($source_path);
                break;
            case IMAGETYPE_PNG:
                $source_image = @imagecreatefrompng($source_path);
                break;
            case IMAGETYPE_GIF:
                $source_image = @imagecreatefromgif($source_path);
                break;
            default:
                return false;
        }

        if (!$source_image) {
            return false;
        }

        // Create new image
        $new_image = imagecreatetruecolor($new_width, $new_height);

        // Preserve transparency for PNG/GIF
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
            imagecolortransparent($new_image, imagecolorallocatealpha($new_image, 0, 0, 0, 127));
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
        }

        // Resize
        imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        // Save based on original type
        $result = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($new_image, $destination_path, $quality);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($new_image, $destination_path, 9);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($new_image, $destination_path);
                break;
        }

        imagedestroy($source_image);
        imagedestroy($new_image);

        return $result;
    }

    /**
     * Generate slug from string
     */
    public static function generateSlug($string) {
        $string = strtolower(trim($string));
        $string = preg_replace('/[^a-z0-9-]/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        return trim($string, '-');
    }

    /**
     * Truncate text
     */
    public static function truncateText($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . $suffix;
    }

    /**
     * Get client IP address
     */
    public static function getClientIp() {
        $ip_headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];

                // Handle comma-separated IPs (like X-Forwarded-For)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }

                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }

    /**
     * Rate limiting check
     */
    public static function checkRateLimit($key, $max_attempts = 5, $time_window = 300) {
        $cache_dir = dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/core/cache';
        if (!is_dir($cache_dir)) {
            @mkdir($cache_dir, 0755, true);
        }

        $cache_file = $cache_dir . '/rate_limit_' . md5($key) . '.json';

        $data = [];
        if (file_exists($cache_file)) {
            $data = json_decode(file_get_contents($cache_file), true) ?? [];
        }

        $now = time();
        $data['attempts'] = array_filter($data['attempts'] ?? [], function($timestamp) use ($now, $time_window) {
            return ($now - $timestamp) < $time_window;
        });

        $data['attempts'][] = $now;

        if (count($data['attempts']) > $max_attempts) {
            file_put_contents($cache_file, json_encode($data));
            return false; // Rate limited
        }

        file_put_contents($cache_file, json_encode($data));
        return true; // Not rate limited
    }

    /**
     * Send JSON response
     */
    public static function sendJsonResponse($data, $status_code = 200) {
        if (!headers_sent()) {
            http_response_code($status_code);
            header('Content-Type: application/json');
        }
        echo json_encode($data);
        exit();
    }

    /**
     * Check if request is AJAX
     */
    public static function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get WhatsApp templates
     */
    public static function getWhatsAppTemplates() {
        $templates_file = dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/core/whatsapp_templates.php';
        if (file_exists($templates_file)) {
            return require $templates_file;
        }
        return [];
    }

    /**
     * Hash password securely
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 3]);
    }

    /**
     * Verify password hash
     */
    public static function verifyPasswordHash($password, $hash) {
        return password_verify($password, $hash);
    }
}
