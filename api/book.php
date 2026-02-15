<?php

/**
 * Enhanced Security Property Booking API Endpoint
 * Handles property booking requests with authentication and validation
 * Security Enhanced Version
 */

// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/book_api_error.log');
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

// Rate limiting for booking API
$max_booking_requests = 10; // requests per hour
$time_window = 3600; // 1 hour in seconds
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$current_time = time();

// Start secure session for API
$session_name = 'secure_booking_session';
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
if (
    isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > 3600
) { // 1 hour timeout
    session_unset();
    session_destroy();
    logSecurityEvent('Booking API Session Timeout', [
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
        if (strpos($key, 'booking_attempts_') === 0 && is_array($value)) {
            if (time() - $value['last_request'] > 3600) {
                unset($_SESSION[$key]);
            }
        }
    }
    $_SESSION['rate_limit_cleanup'] = time();
}

// Check rate limiting
$rate_limit_key = 'booking_attempts_' . md5($ip_address);
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
    if ($rate_limit_data['requests'] > $max_booking_requests) {
        logSecurityEvent('Booking API Rate Limit Exceeded', [
            'ip_address' => $ip_address,
            'requests' => $rate_limit_data['requests'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(429, 'Too many booking requests. Please try again later.');
    }
} else {
    $rate_limit_data['requests'] = 1;
    $rate_limit_data['first_request'] = $current_time;
}

$rate_limit_data['last_request'] = $current_time;

// Security event logging function
function logSecurityEvent($event, $context = [])
{
    static $logFile = null;

    if ($logFile === null) {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/booking_api_security.log';
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
function sendSecurityResponse($status_code, $message, $data = null)
{
    http_response_code($status_code);

    $response = [
        'success' => $status_code >= 200 && $status_code < 300,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s'),
        'request_id' => uniqid('booking_')
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    // Add security headers to response
    header('X-Response-Time: ' . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']));
    header('X-Security-Status: protected');
    header('X-Rate-Limit-Remaining: ' . ($GLOBALS['max_booking_requests'] - $GLOBALS['rate_limit_data']['requests']));
    header('X-Rate-Limit-Reset: ' . ($GLOBALS['rate_limit_data']['first_request'] + $GLOBALS['time_window']));

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

// Enhanced input validation and sanitization
function validateInput($input, $type = 'string', $max_length = null, $required = true)
{
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
        case 'integer':
            $input = filter_var($input, FILTER_VALIDATE_INT);
            if ($input === false || $input < 1) {
                return false;
            }
            break;
        case 'date':
            // Validate date format YYYY-MM-DD
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $input)) {
                return false;
            }
            // Validate if it's a valid date
            $date = DateTime::createFromFormat('Y-m-d', $input);
            if (!$date || $date->format('Y-m-d') !== $input) {
                return false;
            }
            // Check if date is not in the past
            $today = new DateTime();
            $today->setTime(0, 0, 0);
            $input_date = new DateTime($input);
            if ($input_date < $today) {
                return false;
            }
            break;
        case 'email':
            $input = filter_var($input, FILTER_SANITIZE_EMAIL);
            if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            break;
        case 'phone':
            // Remove all non-digit characters except + and spaces
            $input = preg_replace('/[^\d+\s]/', '', $input);
            if (strlen($input) < 10 || strlen($input) > 15) {
                return false;
            }
            break;
        case 'string':
        default:
            $input = htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
            break;
    }

    if ($max_length && strlen($input) > $max_length) {
        return false;
    }

    return $input;
}

// Validate request headers
function validateRequestHeaders()
{
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Check Content-Type for POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($content_type, 'application/json') === false && strpos($content_type, 'application/x-www-form-urlencoded') === false) {
        return false;
    }

    // Check User-Agent (basic bot detection)
    if (empty($user_agent) || strlen($user_agent) < 10) {
        logSecurityEvent('Suspicious User Agent in Booking API', [
            'user_agent' => $user_agent,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
    }

    return true;
}

// Validate authentication token
function validateAuthToken()
{
    $headers = getallheaders();
    $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (empty($auth_header) || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
        return false;
    }

    $token = $matches[1];

    // Load required files with validation
    $required_files = [
        __DIR__ . '/../includes/config.php',
        __DIR__ . '/../includes/Auth.php'
    ];

    foreach ($required_files as $file) {
        if (!file_exists($file) || !is_readable($file)) {
            logSecurityEvent('Required File Missing in Booking API', [
                'file_path' => $file,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
            ]);
            return false;
        }
    }

    require_once $required_files[0];
    require_once $required_files[1];

    $auth = Auth::getInstance();

    try {
        $user = $auth->verifyToken($token);
        if (!$user) {
            return false;
        }

        return $user;
    } catch (Exception $e) {
        logSecurityEvent('Token Verification Failed', [
            'error' => $e->getMessage(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        return false;
    }
}

// Main API logic
try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        logSecurityEvent('Invalid Request Method in Booking API', [
            'method' => $_SERVER['REQUEST_METHOD'],
            'ip_address' => $ip_address,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(405, 'Method not allowed. Only POST requests are accepted.');
    }

    // Validate request headers
    if (!validateRequestHeaders()) {
        logSecurityEvent('Invalid Request Headers in Booking API', [
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'MISSING',
            'ip_address' => $ip_address
        ]);
        sendSecurityResponse(400, 'Invalid request headers.');
    }

    // Get and validate JSON input
    $raw_input = file_get_contents('php://input');

    if (empty($raw_input)) {
        logSecurityEvent('Empty Request Body in Booking API', [
            'ip_address' => $ip_address,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(400, 'Request body is required.');
    }

    // Validate JSON format
    $data = json_decode($raw_input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        logSecurityEvent('Invalid JSON Format in Booking API', [
            'json_error' => json_last_error_msg(),
            'ip_address' => $ip_address,
            'raw_input_length' => strlen($raw_input)
        ]);
        sendSecurityResponse(400, 'Invalid JSON format in request body.');
    }

    // Validate CSRF token for additional security
    if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        if ($_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
            logSecurityEvent('CSRF Token Mismatch in Booking API', [
                'provided_token' => substr($_SERVER['HTTP_X_CSRF_TOKEN'], 0, 8) . '...',
                'expected_token' => substr($_SESSION['csrf_token'], 0, 8) . '...',
                'ip_address' => $ip_address
            ]);
            sendSecurityResponse(403, 'CSRF token validation failed.');
        }
    }

    // Validate authentication
    $user = validateAuthToken();
    if (!$user) {
        logSecurityEvent('Authentication Failed in Booking API', [
            'ip_address' => $ip_address,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(401, 'Authentication required. Please provide a valid token.');
    }

    // Validate required fields
    if (!isset($data['property_id']) || !isset($data['visit_date'])) {
        logSecurityEvent('Missing Required Fields in Booking API', [
            'provided_fields' => implode(', ', array_keys($data)),
            'ip_address' => $ip_address,
            'user_id' => $user['id'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(400, 'Property ID and visit date are required fields.');
    }

    // Validate and sanitize inputs
    $property_id = validateInput($data['property_id'], 'integer');
    $visit_date = validateInput($data['visit_date'], 'date');

    if ($property_id === false) {
        logSecurityEvent('Invalid Property ID in Booking API', [
            'property_id' => $data['property_id'],
            'ip_address' => $ip_address,
            'user_id' => $user['id'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(400, 'Invalid property ID. Must be a positive integer.');
    }

    if ($visit_date === false) {
        logSecurityEvent('Invalid Visit Date in Booking API', [
            'visit_date' => $data['visit_date'],
            'ip_address' => $ip_address,
            'user_id' => $user['id'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(400, 'Invalid visit date. Must be in YYYY-MM-DD format and not in the past.');
    }

    // Check for suspicious patterns in input
    $suspicious_patterns = ['<script', 'javascript:', 'onload=', 'onerror=', 'eval(', 'alert(', 'document.cookie'];
    foreach ($suspicious_patterns as $pattern) {
        if (stripos($raw_input, $pattern) !== false) {
            logSecurityEvent('Suspicious Input Pattern Detected in Booking API', [
                'pattern' => $pattern,
                'ip_address' => $ip_address,
                'user_id' => $user['id'] ?? 'UNKNOWN'
            ]);
            sendSecurityResponse(400, 'Suspicious content detected in request.');
        }
    }

    // Load required files and get database connection
    require_once __DIR__ . '/../app/bootstrap.php';
    $db = \App\Core\App::database();

    // Verify property exists and is available
    $property = $db->fetch(
        "SELECT id, status, title FROM properties WHERE id = :id AND status = 'available' AND is_active = 1",
        ['id' => $property_id],
        false
    );

    if (!$property) {
        logSecurityEvent('Property Not Available for Booking', [
            'property_id' => $property_id,
            'ip_address' => $ip_address,
            'user_id' => $user['id'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(400, 'Property is not available for booking.');
    }

    // Check if user already has a booking for this property on the same date
    $existing_booking = $db->fetch(
        "SELECT id FROM bookings WHERE property_id = :property_id AND visit_date = :visit_date AND user_id = :user_id AND status != 'cancelled'",
        [
            'property_id' => $property_id,
            'visit_date' => $visit_date,
            'user_id' => $user['id']
        ],
        false
    );

    if ($existing_booking) {
        logSecurityEvent('Duplicate Booking Attempt', [
            'property_id' => $property_id,
            'visit_date' => $visit_date,
            'ip_address' => $ip_address,
            'user_id' => $user['id'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(400, 'You already have a booking for this property on this date.');
    }

    // Log booking attempt
    logSecurityEvent('Property Booking Attempt', [
        'property_id' => $property_id,
        'visit_date' => $visit_date,
        'ip_address' => $ip_address,
        'user_id' => $user['id'] ?? 'UNKNOWN',
        'property_title' => $property['title']
    ]);

    // Insert booking record
    $inserted = $db->execute(
        "INSERT INTO bookings (property_id, user_id, visit_date, status, created_at) VALUES (:property_id, :user_id, :visit_date, 'pending', NOW())",
        [
            'property_id' => $property_id,
            'user_id' => $user['id'],
            'visit_date' => $visit_date
        ]
    );

    if (!$inserted) {
        logSecurityEvent('Booking Insertion Failed', [
            'property_id' => $property_id,
            'visit_date' => $visit_date,
            'ip_address' => $ip_address,
            'user_id' => $user['id'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(500, 'Failed to create booking. Please try again.');
    }

    $booking_id = $db->lastInsertId();

    // Log successful booking
    logSecurityEvent('Property Booking Successful', [
        'booking_id' => $booking_id,
        'property_id' => $property_id,
        'visit_date' => $visit_date,
        'ip_address' => $ip_address,
        'user_id' => $user['id'] ?? 'UNKNOWN',
        'property_title' => $property['title']
    ]);

    // Prepare success response
    $response_data = [
        'booking_id' => $booking_id,
        'property_id' => $property_id,
        'visit_date' => $visit_date,
        'status' => 'pending',
        'message' => 'Your booking request has been submitted successfully and is pending approval.'
    ];

    sendSecurityResponse(201, 'Booking created successfully', $response_data);
} catch (Exception $e) {
    // Enhanced error handling without information disclosure
    logSecurityEvent('Booking API Exception', [
        'error_message' => $e->getMessage(),
        'error_file' => $e->getFile(),
        'error_line' => $e->getLine(),
        'ip_address' => $ip_address,
        'trace' => $e->getTraceAsString()
    ]);

    // Send generic error response without exposing internal details
    sendSecurityResponse(500, 'An internal error occurred while processing your booking request.');
}
