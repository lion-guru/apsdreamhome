<?php
ob_start();

// Security configuration file
require_once __DIR__ . '/csrf_protection.php';
require_once __DIR__ . '/input_validation.php';

// Session configuration
function configureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters before starting the session
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 0); // Disable secure cookie for local development
        ini_set('session.gc_maxlifetime', 3600); // Increase session lifetime to 1 hour
        ini_set('session.cookie_path', '/');
        ini_set('session.save_handler', 'files');
        ini_set('session.save_path', sys_get_temp_dir());
        
        // Additional session security settings
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_trans_sid', 0);
        ini_set('session.cache_limiter', 'nocache');
        
        // Start session with secure parameters
        session_name('SECURE_ADMIN_SESSION');
        session_start([
            'cookie_httponly' => 1,
            'cookie_secure' => 0,
            'use_only_cookies' => 1,
            'cookie_samesite' => 'Strict',
            'use_strict_mode' => 1
        ]);
        
        // Initialize session if not already set
        if (!isset($_SESSION['initialized'])) {
            $_SESSION['initialized'] = true;
            $_SESSION['created_time'] = time();
            $_SESSION['last_activity'] = time();
        }
    }
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 900) { // 15 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Security headers configuration
function setSecurityHeaders() {
    // Set security headers
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('X-Content-Type-Options: nosniff');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https:; style-src \'self\' \'unsafe-inline\' https:; img-src \'self\' data: https:; font-src \'self\' data: https:; connect-src \'self\' https:;');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// Error reporting configuration
function configureErrorReporting() {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
    // Force errors to display in terminal
    ini_set('html_errors', 0);
}

// Initialize all security configurations
function initializeSecurity() {
    configureSession();
    setSecurityHeaders();
    configureErrorReporting();
    
    // Initialize CSRF protection for POST requests
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Use procedural CSRF protection functions instead of class
        require_once __DIR__ . '/csrf_protection.php';
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
    }
    
    // Initialize input validator
    global $con;
    if (isset($con)) {
        $GLOBALS['inputValidator'] = new InputValidator($con);
    }
}
?>