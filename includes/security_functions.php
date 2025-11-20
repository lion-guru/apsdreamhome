<?php
/**
 * Enhanced Security Functions for APS Dream Home
 * Provides improved security functions for the entire application
 */

// Advanced input sanitization with context-aware filtering
function secure_input($data, $context = 'text') {
    if (is_array($data)) {
        return array_map(function($item) use ($context) {
            return secure_input($item, $context);
        }, $data);
    }
    
    // First apply basic sanitization
    $data = trim($data);
    $data = stripslashes($data);
    
    // Apply context-specific sanitization
    switch ($context) {
        case 'email':
            $data = filter_var($data, FILTER_SANITIZE_EMAIL);
            break;
        case 'url':
            $data = filter_var($data, FILTER_SANITIZE_URL);
            break;
        case 'int':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            break;
        case 'float':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            break;
        case 'html':
            // For HTML content, use HTML Purifier or similar library
            // This is a simplified version
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            break;
        case 'text':
        default:
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            break;
    }
    
    return $data;
}

// Enhanced CSRF protection with token rotation
if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token() {
        // Generate a new token on each request for better security
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
        
        return $_SESSION['csrf_token'];
    }
}

// Validate CSRF token with expiration check
if (!function_exists('validate_csrf_token')) {
    function validate_csrf_token($token, $max_age = 3600) {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        // Check if token has expired
        if (time() - $_SESSION['csrf_token_time'] > $max_age) {
            // Token expired, generate a new one
            generate_csrf_token();
            return false;
        }
        
        // Validate token using constant-time comparison
        $valid = hash_equals($_SESSION['csrf_token'], $token);
        
        // Rotate token after validation for better security
        if ($valid) {
            generate_csrf_token();
        }
        
        return $valid;
    }
}

// Secure redirect function to prevent open redirects
function secure_redirect($url) {
    // Validate URL is local or in allowed domains
    $allowed_domains = [
        'localhost',
        '127.0.0.1',
        'apsdreamhome.com',
        'www.apsdreamhome.com'
    ];
    
    $parsed_url = parse_url($url);
    $host = $parsed_url['host'] ?? '';
    
    // Check if URL is relative (no host) or in allowed domains
    if (empty($host) || in_array($host, $allowed_domains)) {
        // URL is safe, perform redirect
        header("Location: $url");
        exit();
    } else {
        // URL is not safe, redirect to homepage
        header("Location: /");
        exit();
    }
}

// Secure file upload validation
function validate_file_upload($file, $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'], $max_size = 5242880) {
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['valid' => false, 'error' => 'No file uploaded'];
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        return ['valid' => false, 'error' => 'File size exceeds limit'];
    }
    
    // Check file type
    $file_info = pathinfo($file['name']);
    $extension = strtolower($file_info['extension'] ?? '');
    
    if (!in_array($extension, $allowed_types)) {
        return ['valid' => false, 'error' => 'File type not allowed'];
    }
    
    // Validate file content (MIME type)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    
    $allowed_mimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'pdf' => 'application/pdf'
    ];
    
    if (!isset($allowed_mimes[$extension]) || $allowed_mimes[$extension] !== $mime_type) {
        return ['valid' => false, 'error' => 'File content does not match extension'];
    }
    
    // File is valid
    return ['valid' => true, 'error' => ''];
}

// Generate secure random token
function generate_secure_token($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Log security events
function log_security_event($event, $severity = 'INFO', $data = []) {
    $log_file = __DIR__ . '/../logs/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_id = $_SESSION['user_id'] ?? 'Not logged in';
    
    $log_entry = sprintf(
        "[%s] [%s] [IP: %s] [User: %s] %s %s\n",
        $timestamp,
        $severity,
        $ip,
        $user_id,
        $event,
        !empty($data) ? json_encode($data) : ''
    );
    
    // Ensure log directory exists
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    // Write to log file
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    
    // Also log to system log for critical events
    if ($severity === 'CRITICAL' || $severity === 'ERROR') {
        error_log($log_entry);
    }
}

// Include this file in your main configuration
?>