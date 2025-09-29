<?php
/**
 * Secure Configuration File
 * 
 * This file contains sensitive configuration settings that should be kept secure.
 * It should be excluded from version control and protected from direct access.
 */

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'apsdreamhomefinal');
define('DB_PORT', getenv('DB_PORT') ?: '3306');

// Application Security
define('APP_KEY', getenv('APP_KEY') ?: 'your-secure-app-key-here');
define('ENCRYPTION_KEY', getenv('ENCRYPTION_KEY') ?: 'your-encryption-key-here');

// Session Configuration
define('SESSION_NAME', 'aps_dream_home_secure');
define('SESSION_LIFETIME', 86400); // 24 hours
define('SESSION_SECURE', true);
define('SESSION_HTTP_ONLY', true);
define('SESSION_SAME_SITE', 'Strict');

// CSRF Protection
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour

// Error Reporting
if (getenv('APP_ENV') === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Secure Headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https: \'unsafe-inline\' \'unsafe-eval\'; style-src \'self\' https: \'unsafe-inline\'; img-src \'self\' https: data:; font-src \'self\' https: data:;');

// Disable directory listing
if (!defined('ALLOW_DIRECTORY_LISTING')) {
    if (!headers_sent()) {
        header('X-Powered-By: APS Dream Home');
    }
}
