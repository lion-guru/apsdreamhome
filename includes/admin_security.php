<?php
/**
 * Admin Security Functions
 * 
 * This file contains security functions specifically for the admin section
 * of the APS Dream Home website.
 */

require_once __DIR__ . '/../../core/functions.php';

// Security constants (for login rate limiting)
if (!isset($GLOBALS['max_login_attempts'])) {
    $GLOBALS['max_login_attempts'] = 5; // Maximum failed login attempts before lockout
}

if (!isset($GLOBALS['lockout_duration'])) {
    $GLOBALS['lockout_duration'] = 900; // 15 minutes lockout duration
}

/**
 * Log admin actions
 */
if (!function_exists('logAdminAction')) {
    function logAdminAction($data) {
        $log_file = __DIR__ . '/../admin/logs/admin_actions.log';
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $data
        ];
        
        // Ensure the logs directory exists
        if (!is_dir(dirname($log_file))) {
            mkdir(dirname($log_file), 0755, true);
        }
        
        if (is_writable(dirname($log_file))) {
            file_put_contents($log_file, json_encode($log_entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }
}

/**
 * Enhanced input validation and sanitization
 */
if (!function_exists('validateInput')) {
    function validateInput($input, $type = 'string', $max_length = null, $required = true) {
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
                return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
                
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
                return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
    }
}

/**
 * Validate request headers for security
 */
if (!function_exists('validateRequestHeaders')) {
    function validateRequestHeaders() {
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
}

/**
 * Send security response
 */
if (!function_exists('sendSecurityResponse')) {
    function sendSecurityResponse($status_code, $message, $data = null) {
        http_response_code($status_code);
        header('Content-Type: application/json');
        
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
}

/**
 * Initialize admin session with proper security settings
 */
if (!function_exists('initAdminSession')) {
    function initAdminSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        ini_set('session.gc_maxlifetime', 1800); // 30 minutes
        
        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
}

/**
 * Get asset URL helper
 */
if (!function_exists('get_asset_url')) {
    function get_asset_url($filename, $folder = 'assets') {
        $base_url = defined('BASE_URL') ? BASE_URL : 'http://localhost/apsdreamhome/';
        return $base_url . $folder . '/' . $filename;
    }
}

if (!function_exists('getCurrentUrl')) {
    function getCurrentUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
}

/**
 * Check if file exists and is readable
 */
if (!function_exists('safe_file_exists')) {
    function safe_file_exists($filepath) {
        return file_exists($filepath) && is_readable($filepath);
    }
}

/**
 * Safe redirect function
 */
if (!function_exists('safe_redirect')) {
    function safe_redirect($url, $permanent = false) {
        // Validate URL to prevent open redirect vulnerabilities
        $allowed_hosts = [
            $_SERVER['HTTP_HOST'],
            'localhost'
        ];
        
        $url_parts = parse_url($url);
        $host = $url_parts['host'] ?? '';
        
        // If no host in URL, it's a relative URL which is safe
        // Otherwise, check if the host is in the allowed list
        if (!empty($host) && !in_array($host, $allowed_hosts)) {
            // Redirect to homepage instead
            $url = defined('BASE_URL') ? BASE_URL : 'http://localhost/apsdreamhomefinal/';
        }
        
        if (!headers_sent()) {
            header('Location: ' . $url, true, $permanent ? 301 : 302);
            exit();
        } else {
            echo '<script>window.location.href="' . $url . '";</script>';
            echo '<noscript><meta http-equiv="refresh" content="0;url=' . $url . '"></noscript>';
            echo 'If you are not redirected automatically, please <a href="' . $url . '">click here</a>.';
            exit();
        }
    }
}