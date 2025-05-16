<?php
/**
 * Security Configuration and Access Control
 */

// Prevent direct script access
if (!defined('SECURE_ACCESS')) {
    die('Direct access not allowed');
}

/**
 * Check admin access and role-based permissions
 * @param array $allowedRoles Roles allowed to access the page
 */
function adminAccessControl(array $allowedRoles = []) {
    // Ensure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    if (!isset($_SESSION['admin_session']['is_authenticated']) || 
        $_SESSION['admin_session']['is_authenticated'] !== true) {
        // Redirect to login page
        header('Location: /admin/index.php');
        exit();
    }

    // Check role-based access
    if (!empty($allowedRoles) && 
        !in_array($_SESSION['admin_session']['role'], $allowedRoles)) {
        // Unauthorized access
        header('HTTP/1.1 403 Forbidden');
        die('Access Denied: Insufficient Permissions');
    }

    // Additional security headers
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), camera=(), microphone=()');

    // CSRF Protection
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once __DIR__ . '/../csrf_protection.php';
        if (!CSRFProtection::validateToken()) {
            die('CSRF Token Validation Failed');
        }
    }
}

/**
 * Log security-related events
 * @param string $event Event type
 * @param array $details Event details
 */
function logSecurityEvent(string $event, array $details = []) {
    $logEntry = date('Y-m-d H:i:s') . " | $event | " . json_encode($details) . "\n";
    file_put_contents(__DIR__ . '/../../logs/security.log', $logEntry, FILE_APPEND);
}

// Password Policy Configuration
const PASSWORD_POLICY = [
    'min_length' => 12,
    'require_uppercase' => true,
    'require_lowercase' => true,
    'require_number' => true,
    'require_special_char' => true,
    'max_password_age_days' => 90,
    'prevent_password_reuse' => 5
];

/**
 * Validate password against security policy
 * @param string $password Password to validate
 * @return bool|array True if valid, array of error messages if invalid
 */
function validatePasswordPolicy(string $password) {
    $errors = [];

    if (strlen($password) < PASSWORD_POLICY['min_length']) {
        $errors[] = "Password must be at least " . PASSWORD_POLICY['min_length'] . " characters long";
    }

    if (PASSWORD_POLICY['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }

    if (PASSWORD_POLICY['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }

    if (PASSWORD_POLICY['require_number'] && !preg_match('/\d/', $password)) {
        $errors[] = "Password must contain at least one number";
    }

    if (PASSWORD_POLICY['require_special_char'] && !preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>\/?]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }

    return empty($errors) ? true : $errors;
}
