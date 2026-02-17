<?php
/**
 * Enhanced Security 404 Not Found Page
 * Provides secure error page with comprehensive security measures
 * Security Enhanced Version
 */

// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/404_security.log');
error_reporting(E_ALL);
require_once __DIR__ . "/../../app/helpers.php";

// Set comprehensive security headers for 404 page
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com \'unsafe-inline\'; style-src \'self\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com \'unsafe-inline\'; img-src \'self\' data: https:; font-src \'self\' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net;');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
header('X-Permitted-Cross-Domain-Policies: none');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Secure CORS configuration - Only allow specific origins
$allowed_origins = [
    'https://localhost',
    'http://localhost',
    'https://127.0.0.1',
    'http://127.0.0.1',
    'https://localhost:3000',
    'http://localhost:3000'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key, X-CSRF-Token');
    header('Access-Control-Allow-Credentials: false');
    header('Access-Control-Max-Age: 3600');
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Rate limiting for 404 page access
$max_404_operations = 30; // operations per hour
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$current_time = time();

// Start secure session for 404 page
$session_name = 'secure_404_session';
$secure = true; // Only send cookies over HTTPS
$httponly = true; // Prevent JavaScript access to session cookie
$samesite = 'Strict';

if (PHP_VERSION_ID < 70300) {
    session_set_cookie_params([
        'lifetime' => 1800, // 30 minutes
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
} else {
    session_set_cookie_params([
        'lifetime' => 1800, // 30 minutes
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
}

session_name($session_name);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID to prevent session fixation
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Session timeout check
if (isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > 1800) { // 30 minutes timeout
    session_unset();
    session_destroy();
    logSecurityEvent('404 Session Timeout', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    header('Location: /index.php?timeout=1');
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Rate limiting check for 404 page
$rate_limit_key = '404_operations_' . md5($ip_address);
if (!isset($_SESSION[$rate_limit_key])) {
    $_SESSION[$rate_limit_key] = [
        'operations' => 0,
        'first_operation' => $current_time,
        'last_operation' => $current_time
    ];
}
$rate_limit_data = &$_SESSION[$rate_limit_key];

$_SESSION[$rate_limit_key]['last_operation'] = $current_time;
// Check if rate limit exceeded
if ($current_time - $rate_limit_data['first_operation'] < 3600) {
    $rate_limit_data['operations']++;
    if ($rate_limit_data['operations'] > $max_404_operations) {
        logSecurityEvent('404 Operations Rate Limit Exceeded', [
            'ip_address' => $ip_address,
            'operations' => $rate_limit_data['operations'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'Too many 404 requests. Please slow down.',
            'timestamp' => date('Y-m-d H:i:s'),
            'request_id' => uniqid('rate_limit_')
        ]);
        exit();
    }
} else {
    $rate_limit_data['operations'] = 1;
    $rate_limit_data['first_operation'] = $current_time;
}

$rate_limit_data['last_operation'] = $current_time;

// Security event logging function
function logSecurityEvent($event, $context = []) {
    static $logFile = null;

    if ($logFile === null) {
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/404_security.log';
    }

    $timestamp = date('Y-m-d H:i:s');
    $contextStr = '';

    if (!empty($context)) {
        foreach ($context as $key => $value) {
            try {
                if (is_null($value)) {
                    $strValue = 'NULL';
                } elseif (is_bool($value)) {
                    $strValue = $value ? 'TRUE' : 'FALSE';
                } elseif (is_scalar($value)) {
                    $strValue = (string)$value;
                } elseif (is_array($value) || is_object($value)) {
                    $strValue = json_encode($value, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE);
                } else {
                    $strValue = 'UNKNOWN_TYPE';
                }

                $strValue = mb_strlen($strValue) > 500 ? mb_substr($strValue, 0, 500) . '...' : $strValue;
                $contextStr .= " | $key: $strValue";
            } catch (Exception $e) {
                $contextStr .= " | $key: SERIALIZATION_ERROR";
            }
        }
    }

    $logMessage = "[{$timestamp}] {$event}{$contextStr}\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    error_log($logMessage);
}

// Enhanced output escaping function
function escapeForHTML($data) {
    if (is_array($data)) {
        return array_map('escapeForHTML', $data);
    }
    return h($data);
}

// Validate request headers
function validateRequestHeaders() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Check User-Agent (basic bot detection)
    if (empty($user_agent) || strlen($user_agent) < 10) {
        logSecurityEvent('Suspicious User Agent in 404 Page', [
            'user_agent' => $user_agent,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        return false;
    }

    return true;
}

// Validate request headers
if (!validateRequestHeaders()) {
    logSecurityEvent('Invalid Request Headers in 404 Page', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
    ]);
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request headers.',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

// Log 404 page access for security monitoring
logSecurityEvent('404 Page Accessed', [
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
    'requested_url' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
    'referrer' => $_SERVER['HTTP_REFERER'] ?? 'DIRECT',
    'session_id' => session_id()
]);

// Validate and include header template
$header_file = __DIR__ . '/includes/templates/dynamic_header.php';
if (file_exists($header_file) && is_readable($header_file)) {
    require_once $header_file;
} else {
    logSecurityEvent('Header Template File Missing', ['file_path' => $header_file]);
    echo '<!-- Header template not available -->';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Page Not Found - APS Dream Home</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .notfound-section { min-height: 70vh; display: flex; align-items: center; justify-content: center; }
        .notfound-section .container { max-width: 800px; }
        .notfound-section img { max-width: 300px; width: 100%; height: auto; }
        .display-3 { font-size: 4rem; font-weight: 700; color: #dc3545; }
        .lead { font-size: 1.2rem; color: #6c757d; }
        .btn-primary { background: #1e3c72; border-color: #1e3c72; border-radius: 25px; padding: 12px 30px; font-size: 1.1rem; }
        .btn-primary:hover { background: #2a5298; border-color: #2a5298; transform: translateY(-2px); }
        .security-info { position: fixed; top: 10px; left: 10px; background: rgba(40, 167, 69, 0.9); color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px; z-index: 1001; }
        .security-info i { margin-right: 5px; }
        .alert { border-radius: 10px; border: none; }
        @media (max-width: 768px) { .display-3 { font-size: 3rem; } .lead { font-size: 1rem; } }
    </style>
</head>
<body>
    <!-- Security Information Badge -->
    <div class="security-info">
        <i class="fas fa-shield-alt"></i>
        <span>Secure 404</span>
    </div>

    <!-- Security Status Bar -->
    <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
        <div>
            <i class="fas fa-shield-alt me-2"></i>
            <strong>Security Status:</strong> Protected
            <span class="badge bg-success ms-2">Active</span>
        </div>
        <div class="text-end">
            <small class="text-muted">
                Session expires: <?php echo date('H:i:s', time() + 1800); ?><br>
                Rate limit: <?php echo $max_404_operations - $rate_limit_data['operations']; ?>/<?php echo $max_404_operations; ?> remaining
            </small>
        </div>
    </div>

    <section class="notfound-section py-5 text-center bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <img src="/assets/images/banner/ban3.jpg" alt="Page Not Found" class="img-fluid mb-4" style="max-width:220px;" loading="lazy" onerror="this.src='/assets/images/404-placeholder.jpg'">
                    <h1 class="display-3 fw-bold text-danger mb-3">404</h1>
                    <h2 class="mb-3">Page Not Found</h2>
                    <p class="lead mb-4">Sorry, the page you are looking for doesn't exist or has been moved.<br>Try using the menu or return to the homepage.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="/" class="btn btn-primary btn-lg rounded-pill px-5">
                            <i class="fas fa-home me-2"></i>Go to Homepage
                        </a>
                        <a href="/admin/index.php" class="btn btn-outline-primary btn-lg rounded-pill px-5" target="_blank">
                            <i class="fas fa-cog me-2"></i>Admin Panel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Security Monitoring Info -->
    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Security Notice:</strong> This 404 error has been logged for security monitoring purposes.
                    <br>
                    <small class="text-muted">Request ID: <?php echo uniqid('404_'); ?> | IP: <?php echo h($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'); ?></small>
                </div>
            </div>
        </div>
    </div>

<?php
// Validate and include footer template
$footer_file = __DIR__ . '/includes/templates/new_footer.php';
if (file_exists($footer_file) && is_readable($footer_file)) {
    require_once $footer_file;
} else {
    logSecurityEvent('Footer Template File Missing', ['file_path' => $footer_file]);
    echo '<!-- Footer template not available -->';
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
// Security event logging
function logSecurityEvent(event, context = {}) {
    fetch('/log_security_event.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            event: event,
            context: context,
            csrf_token: '<?php echo h($_SESSION['csrf_token']); ?>'
        })
    }).catch(error => console.error('Security logging failed:', error));
}

// Session timeout warning
let sessionWarningShown = false;
setInterval(function() {
    const now = Math.floor(Date.now() / 1000);
    const sessionTimeout = <?php echo time() + 1800; ?>;
    const timeUntilExpiry = sessionTimeout - now;

    if (timeUntilExpiry <= 300 && timeUntilExpiry > 0 && !sessionWarningShown) {
        alert('Your session will expire in ' + Math.ceil(timeUntilExpiry / 60) + ' minutes. Please save your work.');
        sessionWarningShown = true;
    }
}, 60000);

// Initialize security on page load
logSecurityEvent('404 Page Loaded', {
    requested_url: '<?php echo h($_SERVER['REQUEST_URI'] ?? 'UNKNOWN'); ?>',
    referrer: '<?php echo h($_SERVER['HTTP_REFERER'] ?? 'DIRECT'); ?>'
});

// AJAX error handler with security logging
$(document).ajaxError(function(event, xhr, settings, thrownError) {
    logSecurityEvent('AJAX Error in 404 Page', {
        url: settings.url,
        method: settings.type,
        error: thrownError,
        status: xhr.status
    });
});
</script>
</body>
</html>
