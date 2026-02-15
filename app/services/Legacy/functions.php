<?php

namespace App\Services\Legacy;

use App\Core\App;
use App\Helpers\AuthHelper;
use App\Helpers\SecurityHelper;

/**
 * Namespaced helper functions for legacy support
 */

/**
 * Database connection helper
 */
function db_connect() {
    return App::database()->getConnection();
}

/**
 * Input sanitization helper
 */
function clean_input($data, $type = 'string', $options = []) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = clean_input($value, $type, $options);
        }
        return $data;
    }

    $data = trim($data);
    $data = stripslashes($data);

    switch ($type) {
        case 'email':
            return filter_var($data, FILTER_SANITIZE_EMAIL);
        case 'int':
        case 'integer':
            return (int)$data;
        case 'float':
            return (float)$data;
        case 'bool':
        case 'boolean':
            return (bool)$data;
        case 'string':
        default:
            return h($data);
    }
}

/**
 * Authentication check helper
 */
function is_logged_in($required_role = null) {
    if ($required_role) {
        return AuthHelper::isLoggedIn($required_role);
    }
    return AuthHelper::isLoggedIn();
}

/**
 * CSRF token generation
 */
function generate_csrf_token() {
    return SecurityHelper::generateCsrfToken();
}

/**
 * CSRF token validation
 */
function validate_csrf_token($token) {
    return SecurityHelper::validateCsrfToken($token);
}

/**
 * CSRF input field generation
 */
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Secure random integer
 */
function secure_random_int($min, $max) {
    return SecurityHelper::secureRandomInt($min, $max);
}

/**
 * Secure random bytes
 */
function secure_random_bytes($length = 32) {
    return SecurityHelper::secureRandomBytes($length);
}

/**
 * Safe random number (backward compatibility)
 */
function safe_rand($min, $max) {
    return secure_random_int($min, $max);
}

/**
 * Redirect helper
 */
function redirect($url) {
    if (!headers_sent()) {
        header("Location: $url");
        exit;
    }
    echo '<script>window.location.href="' . $url . '";</script>';
    exit;
}

/**
 * Security headers helper
 */
function set_security_headers() {
    if (!headers_sent()) {
        header("X-Frame-Options: SAMEORIGIN");
        header("X-XSS-Protection: 1; mode=block");
        header("X-Content-Type-Options: nosniff");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' data: https:;");
    }
}

/**
 * Generate unsubscribe token
 */
function generate_unsubscribe_token($email) {
    return hash_hmac('sha256', $email, App::config('app.key', 'legacy_secret_key'));
}

/**
 * Get recent properties
 */
function get_recent_properties($limit = 6) {
    $propertyManager = new PropertyManager();
    return $propertyManager->getProperties(['status' => 'active'], $limit);
}

/**
 * Get property availability status
 */
function get_property_availability_status($property_id) {
    $propertyManager = new PropertyManager();
    $property = $propertyManager->getPropertyDetails($property_id);
    return $property['status'] ?? 'unknown';
}
