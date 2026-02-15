<?php
/**
 * Enhanced Security Email Sending API Endpoint
 * Handles email notifications with authentication, authorization, and validation
 * Security Enhanced Version
 */

// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/email_api_error.log');
error_reporting(E_ALL);

// Set comprehensive security headers for API
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\';');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
header('X-Permitted-Cross-Domain-Policies: none');
header('Content-Type: application/json; charset=UTF-8');
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
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');
    header('Access-Control-Allow-Credentials: false');
    header('Access-Control-Max-Age: 3600'); // 1 hour
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Rate limiting for email API
$max_email_requests = 20; // emails per hour
$time_window = 3600; // 1 hour in seconds
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$current_time = time();

// Start secure session for API
$session_name = 'secure_email_session';
$secure = true; // Only send cookies over HTTPS
$httponly = true; // Prevent JavaScript access to session cookie
$samesite = 'Strict';

if (PHP_VERSION_ID < 70300) {
    session_set_cookie_params([
        'lifetime' => 3600, // 1 hour
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
} else {
    session_set_cookie_params([
        'lifetime' => 3600, // 1 hour
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
} elseif (time() - $_SESSION['last_regeneration'] > 3600) { // 1 hour
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Session timeout check
if (isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > 3600) { // 1 hour timeout
    session_unset();
    session_destroy();
    logSecurityEvent('Email API Session Timeout', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    sendSecurityResponse(401, 'Session expired. Please login again.');
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Clean up old rate limit data
if (!isset($_SESSION['rate_limit_cleanup'])) {
    $_SESSION['rate_limit_cleanup'] = time();
} elseif (time() - $_SESSION['rate_limit_cleanup'] > 3600) { // Clean up hourly
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'email_attempts_') === 0 && is_array($value)) {
            if (time() - $value['last_request'] > 3600) {
                unset($_SESSION[$key]);
            }
        }
    }
    $_SESSION['rate_limit_cleanup'] = time();
}

// Check rate limiting
$rate_limit_key = 'email_attempts_' . md5($ip_address);
if (!isset($_SESSION[$rate_limit_key])) {
    $_SESSION[$rate_limit_key] = [
        'requests' => 0,
        'first_request' => $current_time,
        'last_request' => $current_time
    ];
}

$rate_limit_data = &$_SESSION[$rate_limit_key];

if ($current_time - $rate_limit_data['first_request'] < $time_window) {
    $rate_limit_data['requests']++;
    if ($rate_limit_data['requests'] > $max_email_requests) {
        logSecurityEvent('Email API Rate Limit Exceeded', [
            'ip_address' => $ip_address,
            'requests' => $rate_limit_data['requests'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(429, 'Too many email requests. Please try again later.');
    }
} else {
    $rate_limit_data['requests'] = 1;
    $rate_limit_data['first_request'] = $current_time;
}

$rate_limit_data['last_request'] = $current_time;

// Security event logging function
function logSecurityEvent($event, $context = []) {
    static $logFile = null;

    if ($logFile === null) {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/email_api_security.log';
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

// Secure response function
function sendSecurityResponse($status_code, $message, $data = null) {
    http_response_code($status_code);

    $response = [
        'success' => $status_code >= 200 && $status_code < 300,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s'),
        'request_id' => uniqid('email_')
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    // Add security headers to response
    header('X-Response-Time: ' . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']));
    header('X-Security-Status: protected');
    header('X-Rate-Limit-Remaining: ' . ($GLOBALS['max_email_requests'] - $GLOBALS['rate_limit_data']['requests']));
    header('X-Rate-Limit-Reset: ' . ($GLOBALS['rate_limit_data']['first_request'] + $GLOBALS['time_window']));

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

// Enhanced input validation and sanitization
function validateInput($input, $type = 'string', $max_length = null, $required = true) {
    if ($input === null) {
        if ($required) {
            return false;
        }
        return '';
    }

    $input = trim($input);

    if ($required && empty($input)) {
        return false;
    }

    switch ($type) {
        case 'email':
            $input = filter_var($input, FILTER_SANITIZE_EMAIL);
            if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            break;
        case 'subject':
            // Allow alphanumeric, spaces, and common subject characters
            if (!preg_match('/^[a-zA-Z0-9\s\-_.,!?()]+$/', $input)) {
                return false;
            }
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
        case 'message':
            // Allow text content with basic HTML
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
        case 'string':
        default:
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
    }

    if ($max_length && strlen($input) > $max_length) {
        return false;
    }

    return $input;
}

// Validate request headers
function validateRequestHeaders() {
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Check Content-Type for POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($content_type, 'application/json') === false && strpos($content_type, 'application/x-www-form-urlencoded') === false) {
        return false;
    }

    // Check User-Agent (basic bot detection)
    if (empty($user_agent) || strlen($user_agent) < 10) {
        logSecurityEvent('Suspicious User Agent in Email API', [
            'user_agent' => $user_agent,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
    }

    return true;
}

// Validate authentication and authorization
function validateAdminAccess() {
    // Check session authentication
    if (!isset($_SESSION['auser'])) {
        logSecurityEvent('Unauthenticated Email API Access Attempt', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        return false;
    }

    $user_id = $_SESSION['auser'];

    // Load required files with validation
    $required_files = [
        __DIR__ . '/../config.php',
        __DIR__ . '/../src/Services/EmailService.php'
    ];

    foreach ($required_files as $file) {
        if (!file_exists($file) || !is_readable($file)) {
            logSecurityEvent('Required File Missing in Email API', [
                'file_path' => $file,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
            ]);
            return false;
        }
    }

    require_once $required_files[0];

    // Check if user is admin using prepared statement
    try {
        $db = \App\Core\App::database();
        $admin_check = $db->fetch("SELECT ur.user_id FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = :user_id AND r.name = 'Admin'", [
            'user_id' => $user_id
        ], false);

        if (!$admin_check) {
            logSecurityEvent('Unauthorized Email API Access Attempt', [
                'user_id' => $user_id,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
            ]);
            return false;
        }
    } catch (Exception $e) {
        logSecurityEvent('Database Error in Email API Auth', [
            'error' => $e->getMessage(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        return false;
    }

    return ['user_id' => $user_id];
}

// Main API logic
try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        logSecurityEvent('Invalid Request Method in Email API', [
            'method' => $_SERVER['REQUEST_METHOD'],
            'ip_address' => $ip_address,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(405, 'Method not allowed. Only POST requests are accepted.');
    }

    // Validate request headers
    if (!validateRequestHeaders()) {
        logSecurityEvent('Invalid Request Headers in Email API', [
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'MISSING',
            'ip_address' => $ip_address
        ]);
        sendSecurityResponse(400, 'Invalid request headers.');
    }

    // Validate authentication and authorization
    $user = validateAdminAccess();
    if (!$user) {
        sendSecurityResponse(401, 'Authentication required. Please login as an administrator.');
    }

    // Get and validate JSON input
    $raw_input = file_get_contents('php://input');

    if (empty($raw_input)) {
        logSecurityEvent('Empty Request Body in Email API', [
            'ip_address' => $ip_address,
            'user_id' => $user['user_id'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(400, 'Request body is required.');
    }

    // Validate JSON format
    $data = json_decode($raw_input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        logSecurityEvent('Invalid JSON Format in Email API', [
            'json_error' => json_last_error_msg(),
            'ip_address' => $ip_address,
            'user_id' => $user['user_id'],
            'raw_input_length' => strlen($raw_input)
        ]);
        sendSecurityResponse(400, 'Invalid JSON format in request body.');
    }

    // Validate CSRF token for additional security
    if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        if ($_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
            logSecurityEvent('CSRF Token Mismatch in Email API', [
                'provided_token' => substr($_SERVER['HTTP_X_CSRF_TOKEN'], 0, 8) . '...',
                'expected_token' => substr($_SESSION['csrf_token'], 0, 8) . '...',
                'ip_address' => $ip_address,
                'user_id' => $user['user_id']
            ]);
            sendSecurityResponse(403, 'CSRF token validation failed.');
        }
    }

    // Validate required fields
    if (!isset($data['to']) || !isset($data['subject']) || !isset($data['message'])) {
        logSecurityEvent('Missing Required Fields in Email API', [
            'provided_fields' => implode(', ', array_keys($data)),
            'ip_address' => $ip_address,
            'user_id' => $user['user_id']
        ]);
        sendSecurityResponse(400, 'Email address, subject, and message are required fields.');
    }

    // Validate and sanitize inputs
    $to = validateInput($data['to'], 'email');
    $subject = validateInput($data['subject'], 'subject', 200);
    $message = validateInput($data['message'], 'message', 10000); // Max 10KB message

    if ($to === false) {
        logSecurityEvent('Invalid Email Address in Email API', [
            'email' => $data['to'],
            'ip_address' => $ip_address,
            'user_id' => $user['user_id']
        ]);
        sendSecurityResponse(400, 'Invalid email address format.');
    }

    if ($subject === false) {
        logSecurityEvent('Invalid Subject in Email API', [
            'subject' => $data['subject'],
            'ip_address' => $ip_address,
            'user_id' => $user['user_id']
        ]);
        sendSecurityResponse(400, 'Invalid subject format or too long (max 200 characters).');
    }

    if ($message === false) {
        logSecurityEvent('Invalid Message in Email API', [
            'message_length' => strlen($data['message']),
            'ip_address' => $ip_address,
            'user_id' => $user['user_id']
        ]);
        sendSecurityResponse(400, 'Invalid message format or too long (max 10,000 characters).');
    }

    // Check for suspicious patterns in input
    $suspicious_patterns = ['<script', 'javascript:', 'onload=', 'onerror=', 'eval(', 'alert(', 'document.cookie', 'iframe', 'embed'];
    foreach ($suspicious_patterns as $pattern) {
        if (stripos($raw_input, $pattern) !== false) {
            logSecurityEvent('Suspicious Input Pattern Detected in Email API', [
                'pattern' => $pattern,
                'ip_address' => $ip_address,
                'user_id' => $user['user_id']
            ]);
            sendSecurityResponse(400, 'Suspicious content detected in request.');
        }
    }

    // Log email sending attempt
    logSecurityEvent('Email Sending Attempt', [
        'to' => $to,
        'subject' => $subject,
        'message_length' => strlen($message),
        'ip_address' => $ip_address,
        'user_id' => $user['user_id']
    ]);

    // Load EmailService and send email
    require_once __DIR__ . '/../src/Services/EmailService.php';

    $emailService = new EmailService();

    // Validate EmailService initialization
    if (!$emailService) {
        logSecurityEvent('EmailService Initialization Failed', [
            'ip_address' => $ip_address,
            'user_id' => $user['user_id']
        ]);
        sendSecurityResponse(500, 'Email service initialization failed.');
    }

    // Send email with error handling
    try {
        // Use the generic send method for admin notifications
        $sent = $emailService->send($to, $subject, $message);

        if ($sent) {
            // Log successful email sending
            logSecurityEvent('Email Sent Successfully', [
                'to' => $to,
                'subject' => $subject,
                'message_length' => strlen($message),
                'ip_address' => $ip_address,
                'user_id' => $user['user_id']
            ]);

            // Prepare success response
            $response_data = [
                'sent' => true,
                'to' => $to,
                'subject' => $subject,
                'message_length' => strlen($message),
                'timestamp' => date('Y-m-d H:i:s')
            ];

            sendSecurityResponse(200, 'Email sent successfully', $response_data);
        } else {
            logSecurityEvent('Email Sending Failed', [
                'to' => $to,
                'subject' => $subject,
                'ip_address' => $ip_address,
                'user_id' => $user['user_id']
            ]);
            sendSecurityResponse(500, 'Failed to send email. Please try again.');
        }

    } catch (Exception $e) {
        logSecurityEvent('EmailService Exception', [
            'error_message' => $e->getMessage(),
            'to' => $to,
            'subject' => $subject,
            'ip_address' => $ip_address,
            'user_id' => $user['user_id']
        ]);
        sendSecurityResponse(500, 'Email service error occurred.');
    }

} catch (Exception $e) {
    // Enhanced error handling without information disclosure
    logSecurityEvent('Email API Exception', [
        'error_message' => $e->getMessage(),
        'error_file' => $e->getFile(),
        'error_line' => $e->getLine(),
        'ip_address' => $ip_address,
        'trace' => $e->getTraceAsString()
    ]);

    // Send generic error response without exposing internal details
    sendSecurityResponse(500, 'An internal error occurred while processing your email request.');
}
?>
