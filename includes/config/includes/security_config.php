<?php
/**
 * Security Configuration
 * Contains security-related settings and functions
 */

// Security Headers
$security_headers = [
    'X-Frame-Options' => 'DENY',
    'X-Content-Type-Options' => 'nosniff',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Content-Security-Policy' => "default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net;"
];

// Apply security headers
function applySecurityHeaders($headers) {
    foreach ($headers as $header => $value) {
        header("$header: $value");
    }
}

// Initialize security settings
function initSecurity() {
    // Disable error display in production
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../../logs/security.log');
    error_reporting(E_ALL);
    
    // Start secure session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        $session_name = 'APS_DREAM_HOME_SESSID';
        $lifetime = 86400; // 24 hours
        $path = '/';
        $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $secure = isset($_SERVER['HTTPS']);
        $httponly = true;
        
        session_name($session_name);
        session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
        session_start();
        
        // Regenerate session ID periodically to prevent session fixation
        if (!isset($_SESSION['created'])) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        } elseif (time() - $_SESSION['created'] > 1800) { // 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}

// CSRF Protection
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Input Sanitization
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Initialize security when this file is included
initSecurity();

// Apply security headers
applySecurityHeaders($security_headers);
?>
