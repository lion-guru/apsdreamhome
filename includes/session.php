<?php
// Session security and timeout management

// Check if session is not already started
if (session_status() == PHP_SESSION_NONE) {
    // Set secure session parameters before starting session
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.gc_maxlifetime', 1800);
    ini_set('session.cookie_samesite', 'Strict');
    
    // Start the session
    session_start();
}

// Additional security headers
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Session timeout duration (30 minutes) and regeneration interval (15 minutes)
define('SESSION_TIMEOUT', 1800);
define('SESSION_REGENERATE_INTERVAL', 900);

// Check if session is active and not expired
function checkSession() {
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        $_SESSION['created_time'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        require_once __DIR__ . '/csrf_protection.php';
        generateCSRFToken();
        return true;
    }

    // Check for session expiration
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        terminateSession();
        return false;
    }
    
    // Regenerate session ID periodically to prevent session fixation
    if (!isset($_SESSION['created_time']) || (time() - $_SESSION['created_time'] > SESSION_REGENERATE_INTERVAL)) {
        session_regenerate_id(true);
        $_SESSION['created_time'] = time();
    }
    
    // Validate IP address and user agent haven't changed (prevent session hijacking)
    if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR'] ||
        $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        terminateSession();
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    
    // Ensure CSRF token exists
    if (!isset($_SESSION['csrf_token'])) {
        require_once __DIR__ . '/csrf_protection.php';
        generateCSRFToken();
    }
    
    return true;
}

// Terminate session and clean up
function terminateSession() {
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
}

// Check if user is logged in by verifying session variables
function isAuthenticated() {
    return (isset($_SESSION['uid']) || isset($_SESSION['email'])) && checkSession();
}

// Initialize session with user data
function initializeSession($userData) {
    $_SESSION['uid'] = $userData['uid'] ?? null;
    $_SESSION['email'] = $userData['email'] ?? null;
    $_SESSION['user'] = $userData['name'] ?? null;
    $_SESSION['profile_image'] = $userData['profile_image'] ?? null;
    $_SESSION['utype'] = $userData['utype'] ?? 'user';
    $_SESSION['last_activity'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
}

// Get current user type
function getUserType() {
    return isset($_SESSION['utype']) ? $_SESSION['utype'] : null;
}

// Enforce HTTPS
function enforceHTTPS() {
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit();
    }
}