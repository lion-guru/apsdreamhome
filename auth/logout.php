<?php
// Enhanced Security Logout System - APS Dream Home
// Comprehensive security implementation for logout process

// Enhanced Security: Initialize security logging
$security_log_file = __DIR__ . '/../logs/security.log';
ensureLogDirectory($security_log_file);

// Enhanced Security: Validate HTTPS connection
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    logSecurityEvent('HTTP Logout Attempt', [
        'ip' => getClientIP(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// Enhanced Security: Validate request headers
$headers = getallheaders();
if (!validateRequestHeaders($headers)) {
    logSecurityEvent('Invalid Logout Request Headers', [
        'ip' => getClientIP(),
        'headers' => $headers,
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request headers']);
    exit();
}

// Enhanced Security: Set comprehensive security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com; connect-src 'self'; frame-ancestors 'none';");

// Enhanced Security: Log logout access
logSecurityEvent('Logout Page Access', [
    'ip' => getClientIP(),
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
    'session_id' => session_id() ?? 'NO_SESSION',
    'timestamp' => date('Y-m-d H:i:s')
], $security_log_file);

// Enhanced Security: Secure session management
require_once __DIR__ . '/../includes/security/security_functions.php';
secureSession();

// Enhanced Security: Get user information before logout
$user_id = $_SESSION['user_id'] ?? null;
$user_email = $_SESSION['user_email'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;
$login_time = $_SESSION['login_time'] ?? null;
$session_duration = $login_time ? time() - $login_time : 0;

// Enhanced Security: Comprehensive session cleanup
$session_data = [
    'user_id' => $user_id,
    'user_email' => $user_email,
    'user_role' => $user_role,
    'login_time' => $login_time,
    'session_duration' => $session_duration,
    'logout_time' => time(),
    'ip_address' => getClientIP(),
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
];

// Enhanced Security: Clear all session data securely
$_SESSION = array(); // Clear all session variables

// Enhanced Security: Delete session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Enhanced Security: Destroy session completely
session_destroy();

// Enhanced Security: Additional security cleanup
if (isset($_COOKIE)) {
    foreach ($_COOKIE as $name => $value) {
        setcookie($name, '', time() - 42000, '/');
    }
}

// Enhanced Security: Clear browser cache headers
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Enhanced Security: Log successful logout
logSecurityEvent('Successful Logout', [
    'user_id' => $user_id,
    'user_email' => $user_email,
    'user_role' => $user_role,
    'session_duration' => $session_duration,
    'ip_address' => getClientIP(),
    'timestamp' => date('Y-m-d H:i:s')
], $security_log_file);

// Enhanced Security: Determine redirect URL based on previous context
$redirect_url = '/march2025apssite/'; // Default redirect

// Enhanced Security: Validate redirect URL to prevent open redirect
if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
    $requested_redirect = $_GET['redirect'];

    // Validate that redirect is internal and safe
    if (filter_var($requested_redirect, FILTER_VALIDATE_URL)) {
        // If it's a full URL, ensure it's our domain
        $parsed_url = parse_url($requested_redirect);
        if ($parsed_url['host'] === $_SERVER['HTTP_HOST']) {
            $redirect_url = $requested_redirect;
        } else {
            // Log suspicious redirect attempt
            logSecurityEvent('Suspicious Logout Redirect', [
                'requested_url' => $requested_redirect,
                'ip' => getClientIP(),
                'timestamp' => date('Y-m-d H:i:s')
            ], $security_log_file);
        }
    } else {
        // Relative URL - ensure it doesn't contain suspicious patterns
        if (!preg_match('/[<>"\';]/', $requested_redirect) &&
            !preg_match('/\.\./', $requested_redirect)) {
            $redirect_url = $requested_redirect;
        } else {
            // Log suspicious redirect attempt
            logSecurityEvent('Invalid Logout Redirect Pattern', [
                'requested_url' => $requested_redirect,
                'ip' => getClientIP(),
                'timestamp' => date('Y-m-d H:i:s')
            ], $security_log_file);
        }
    }
}

// Enhanced Security: Add cache-busting parameter to prevent caching
$redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'logged_out=' . time();

// Enhanced Security: Final redirect with security validation
if (!filter_var($redirect_url, FILTER_VALIDATE_URL) && !preg_match('/^\/march2025apssite\//', $redirect_url)) {
    logSecurityEvent('Invalid Logout Redirect URL', [
        'attempted_url' => $redirect_url,
        'ip' => getClientIP(),
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
    $redirect_url = '/march2025apssite/';
}

header('Location: ' . $redirect_url);
exit;