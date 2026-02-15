<?php
// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SECURITY_TIMEOUT,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
    $_SESSION['active'] = true; // Mark session as active
    error_log("New session started: ".session_id());
}
if (session_status() === PHP_SESSION_NONE) {
    error_log("Starting new session");
    session_name(SESSION_NAME);
    session_start([
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'cookie_lifetime' => SECURITY_TIMEOUT
    ]);
} else {
    error_log("Session already started: ".session_id());
}

// Enhanced HSTS
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
// Set security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Set content security policy
// Enhanced Content Security Policy
// Generate CSP nonce
$csp_nonce = base64_encode(random_bytes(16));
$_SESSION['csp_nonce'] = $csp_nonce;

// Update CSP header to be less strict temporarily
header("Content-Security-Policy: "
    . "default-src 'self' 'unsafe-inline'; "
    . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.jsdelivr.net/npm/sweetalert2@11; "
    . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
    . "img-src 'self' data:; "
    . "font-src 'self'; "
    . "frame-src 'none'; "
    . "object-src 'none';");

// Disable caching for sensitive pages
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Additional security headers
header("Feature-Policy: "
    . "geolocation 'none'; "
    . "microphone 'none'; "
    . "camera 'none'; "
    . "fullscreen 'self'");
header("X-Permitted-Cross-Domain-Policies: none");

// Additional security measures
header("Expect-CT: enforce, max-age=30");
header("X-Content-Type-Options: nosniff");
header("X-Download-Options: noopen");
header("Cross-Origin-Embedder-Policy: require-corp");
header("Cross-Origin-Opener-Policy: same-origin");
header("Cross-Origin-Resource-Policy: same-origin");

// Prevent information disclosure
header("Server: Apache"); // Generic server header
header("X-Powered-By: PHP"); // Generic PHP header

// Ensure no output before headers
if (headers_sent()) {
    error_log("Security headers not sent - output already started");
    die("Security violation detected");
}

// Add HTTP Public Key Pinning (HPKP) - Note: Requires careful implementation
header("Public-Key-Pins: "
    . "pin-sha256=\"base64+primary==\"; "
    . "pin-sha256=\"base64+backup==\"; "
    . "max-age=5184000; includeSubDomains");

// Add security.txt reference
header("Link: </.well-known/security.txt>; rel=\"security.txt\"");

// Security logging function
function logSecurityEvent($event) {
    $logMessage = date('Y-m-d H:i:s') . " - " . $_SERVER['REMOTE_ADDR'] . " - " . $event . "\n";
    file_put_contents(__DIR__.'/../logs/security.log', $logMessage, FILE_APPEND);
}

// Log header initialization
logSecurityEvent("Security headers initialized for ".$_SERVER['REQUEST_URI']);

// Clean up sensitive variables
unset($csp_nonce);

// Rate limiting protection
$rateLimitKey = 'rate_limit_'.$_SERVER['REMOTE_ADDR'];
if (!isset($_SESSION[$rateLimitKey])) {
    $_SESSION[$rateLimitKey] = 0;
}
if ($_SESSION[$rateLimitKey] > 100) { // 100 requests per session
    header('HTTP/1.1 429 Too Many Requests');
    die('Rate limit exceeded');
}
$_SESSION[$rateLimitKey]++;

// Validate request origin
if (isset($_SERVER['HTTP_ORIGIN']) && !in_array($_SERVER['HTTP_ORIGIN'], ['https://yourdomain.com'])) {
    die('Invalid request origin');
}

// Validate request method
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    header('HTTP/1.1 405 Method Not Allowed');
    die('Method not allowed');
}

// Validate session integrity
if (!isset($_SESSION['active'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Session fingerprinting for extra security
if (!isset($_SESSION['fingerprint'])) {
    $_SESSION['fingerprint'] = md5(
        $_SERVER['HTTP_USER_AGENT'] . 
        $_SERVER['HTTP_ACCEPT'] . 
        $_SERVER['HTTP_ACCEPT_LANGUAGE']
    );
} elseif ($_SESSION['fingerprint'] !== md5(
    $_SERVER['HTTP_USER_AGENT'] . 
    $_SERVER['HTTP_ACCEPT'] . 
    $_SERVER['HTTP_ACCEPT_LANGUAGE']
)) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

// Block suspicious user agents
if (preg_match('/(curl|wget|python|nikto|sqlmap)/i', $_SERVER['HTTP_USER_AGENT'] ?? '')) {
    header('HTTP/1.1 403 Forbidden');
    die('Access denied');
}

// Validate request headers
if (!isset($_SERVER['HTTP_ACCEPT']) || !isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    header('HTTP/1.1 400 Bad Request');
    die('Invalid request');
}

// Check for suspicious input patterns
array_walk_recursive($_REQUEST, function($value) {
    if (preg_match('/<script|SELECT.*FROM|UNION.*SELECT/i', $value)) {
        logSecurityEvent("Suspicious input detected: " . substr($value, 0, 50));
        header('HTTP/1.1 400 Bad Request');
        die('Invalid input detected');
    }
});

// Set security flag for downstream checks
define('SECURITY_HEADERS_LOADED', true);

// Add additional security headers
header("X-Content-Security-Policy: default-src 'self'");
header("X-WebKit-CSP: default-src 'self'");
header("X-Request-ID: " . bin2hex(random_bytes(16)));

// Session timeout: 30 minutes (1800 seconds)
$timeout = 1800;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// Final security check
register_shutdown_function(function() {
    if (headers_sent() && !defined('HEADERS_SENT')) {
        logSecurityEvent("Headers sent prematurely");
    }
    // Verify security flag was properly set
    if (!defined('SECURITY_HEADERS_LOADED')) {
        logSecurityEvent("Security headers not properly loaded");
    }
    // Validate session timeout
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SECURITY_TIMEOUT)) {
        session_unset();
        session_destroy();
    }
    $_SESSION['LAST_ACTIVITY'] = time();
});

// Security constants
define('SECURITY_TIMEOUT', 1800); // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
define('SESSION_REGENERATE_INTERVAL', 300); // 5 minutes
define('MAX_REQUEST_TIME', 5); // 5 seconds
define('MAX_UPLOAD_SIZE', 1000000); // 1MB
define('SESSION_NAME', 'SECURE_SESSION_ID');

// Add session fixation protection
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Validate Content-Type header for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
    (empty($_SERVER['CONTENT_TYPE']) || 
     !preg_match('/^application\/x-www-form-urlencoded|multipart\/form-data/', $_SERVER['CONTENT_TYPE']))) {
    header('HTTP/1.1 400 Bad Request');
    die('Invalid Content-Type');
}

// Add brute force protection
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if ($_SESSION['login_attempts'] > MAX_LOGIN_ATTEMPTS) {
    header('HTTP/1.1 429 Too Many Requests');
    die('Too many login attempts');
}

// Add session regeneration based on interval
if (!isset($_SESSION['last_regeneration']) || 
    (time() - $_SESSION['last_regeneration'] > SESSION_REGENERATE_INTERVAL)) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Add request size validation
if ($_SERVER['CONTENT_LENGTH'] > 1000000) { // 1MB max
    header('HTTP/1.1 413 Payload Too Large');
    die('Request too large');
}

// Add security token for form submissions
if (!isset($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

// Add HTTP header validation
if (!isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] !== 'yourdomain.com') {
    header('HTTP/1.1 400 Bad Request');
    die('Invalid Host header');
}

// Add request time validation
if (isset($_SERVER['REQUEST_TIME_FLOAT']) && 
    (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) > 5) { // 5 seconds max
    header('HTTP/1.1 408 Request Timeout');
    die('Request timeout');
}

// Add IP-based session validation
if (isset($_SESSION['ip']) && $_SESSION['ip'] !== $_SERVER['REMOTE_ADDR']) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}
$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];

// Add secure cookie settings
if (!isset($_SESSION['cookie_set'])) {
    setcookie(session_name(), session_id(), [
        'expires' => time() + SECURITY_TIMEOUT,
        'path' => '/',
        'domain' => 'yourdomain.com',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    $_SESSION['cookie_set'] = true;
}

// Add security token for AJAX requests
if (!isset($_SESSION['ajax_token'])) {
    $_SESSION['ajax_token'] = bin2hex(random_bytes(16));
    $_SESSION['ajax_token_created'] = time();
}

// Add security token for API requests
if (!isset($_SESSION['api_token'])) {
    $_SESSION['api_token'] = bin2hex(random_bytes(32));
    $_SESSION['api_token_created'] = time();
}

// Add request fingerprinting
if (!isset($_SESSION['fingerprint'])) {
    $_SESSION['fingerprint'] = md5(
        $_SERVER['HTTP_USER_AGENT'] . 
        $_SERVER['HTTP_ACCEPT'] . 
        $_SERVER['HTTP_ACCEPT_LANGUAGE']
    );
} elseif ($_SESSION['fingerprint'] !== md5(
    $_SERVER['HTTP_USER_AGENT'] . 
    $_SERVER['HTTP_ACCEPT'] . 
    $_SERVER['HTTP_ACCEPT_LANGUAGE']
)) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
    if (!isset($_SESSION['active'])) {
        error_log("Session validation failed - redirecting to login");
    }
});
?>

define('AJAX_TOKEN_EXPIRY', 3600); // 1 hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

function validateAjaxToken($token) {
    return isset($_SESSION['ajax_token']) && 
           hash_equals($_SESSION['ajax_token'], $token) &&
           (time() - $_SESSION['ajax_token_created']) < AJAX_TOKEN_EXPIRY;
}

function validateApiToken($token) {
    return isset($_SESSION['api_token']) && 
           hash_equals($_SESSION['api_token'], $token) &&
           (time() - $_SESSION['api_token_created']) < API_TOKEN_EXPIRY;
}

// Debug session status
register_shutdown_function(function() {
