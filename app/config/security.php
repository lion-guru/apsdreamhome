<?php

// TODO: Add proper error handling with try-catch blocks

/**
 * Security Configuration
 */

return [
    'csrf_token_name' => '_token',
    'csrf_token_length' => 32,
    'session_lifetime' => 120, // minutes
    'session_encryption' => true,
    'password_min_length' => 8,
    'rate_limiting' => [
        'max_attempts' => 5,
        'decay_minutes' => 1,
    ],
    'xss_protection' => true,
    'sql_injection_protection' => true,
    'input_validation' => true,
    'file_uploads' => [
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'max_size' => 5242880, // 5MB
    ],
    'encryption' => [
        'cipher' => 'AES-256-CBC',
        'mode' => 'CBC',
    ],
    'jwt' => [
        'algorithm' => 'HS256',
        'expire' => 60 * 60 * 24, // 24 hours
    ],
    'cors' => [
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'max_age' => 86400,
    ],
];

// Security helper functions
if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '" />';
    }
}

if (!function_exists('validate_csrf_token')) {
    function validate_csrf_token($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('sanitize_input')) {
    function sanitize_input($data) {
        if (is_array($data)) {
            return array_map('sanitize_input', $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}