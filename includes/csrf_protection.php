<?php
// Session is managed by security_config.php
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/security_config.php';
    configureSession();
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('CSRF token validation failed');
    }
    return true;
}

function getCSRFTokenField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

// Generate CSRF token if it doesn't exist
$csrf_token = generateCSRFToken();