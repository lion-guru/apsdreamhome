<?php
/**
 * Basic Security Configuration
 * Provides default security settings for the application
 */

// Security Constants
define('SECURITY_SALT', bin2hex(random_bytes(16)));
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_DURATION', 600); // 10 minutes
define('SESSION_TIMEOUT', 1800); // 30 minutes

// IP Whitelisting (optional, can be expanded)
$ALLOWED_IPS = [
    '127.0.0.1',  // localhost
    '::1',        // IPv6 localhost
];

// CSRF Protection
$CSRF_PROTECTION = true;

// Logging Configuration
$SECURITY_LOGGING = [
    'login_attempts' => true,
    'security_events' => true,
    'log_path' => __DIR__ . '/../logs/security.log'
];

// Optional: Rate Limiting Configuration
$RATE_LIMIT_CONFIG = [
    'max_requests' => 100,
    'time_window' => 3600 // 1 hour
];

// Function to check IP whitelist
function isIpAllowed($ip) {
    global $ALLOWED_IPS;
    return in_array($ip, $ALLOWED_IPS);
}

// Minimal security headers
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// Call security headers function
setSecurityHeaders();
