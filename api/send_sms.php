<?php
/**
 * Enhanced Security SMS Sending API Endpoint
 * Handles SMS notifications with authentication, authorization, and validation
 * Security Enhanced Version
 */

// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/sms_api_error.log');
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

// Rate limiting for SMS API
$max_sms_requests = 50; // SMS per hour
$time_window = 3600; // 1 hour in seconds
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$current_time = time();

// Start secure session for API
$session_name = 'secure_sms_session';
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
    logSecurityEvent('SMS API Session Timeout', [
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
        if (strpos($key, 'sms_attempts_') === 0 && is_array($value)) {
            if (time() - $value['last_request'] > 3600) {
                unset($_SESSION[$key]);
            }
        }
    }
    $_SESSION['rate_limit_cleanup'] = time();
}

// Check rate limiting
$rate_limit_key = 'sms_attempts_' . md5($ip_address);
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
    if ($rate_limit_data['requests'] > $max_sms_requests) {
        logSecurityEvent('SMS API Rate Limit Exceeded', [
            'ip_address' => $ip_address,
            'requests' => $rate_limit_data['requests'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(429, 'Too many SMS requests. Please try again later.');
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
        $logFile = $logDir . '/sms_api_security.log';
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
        'request_id' => uniqid('sms_')
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    // Add security headers to response
    header('X-Response-Time: ' . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']));
    header('X-Security-Status: protected');
    header('X-Rate-Limit-Remaining: ' . ($GLOBALS['max_sms_requests'] - $GLOBALS['rate_limit_data']['requests']));
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
        case 'phone':
            $input = filter_var($input, FILTER_SANITIZE_STRING);
            // Remove all non-digit characters except + and spaces
            $input = preg_replace('/[^\d+\s]/', '', $input);
            if (strlen($input) < 10 || strlen($input) > 15) {
                return false;
            }
            break;
        case 'message':
            // Allow text content for SMS (160 characters max for standard SMS)
            if (strlen($input) > 160) {
                return false;
            }
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
        logSecurityEvent('Suspicious User Agent in SMS API', [
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
        logSecurityEvent('Unauthenticated SMS API Access Attempt', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        return false;
    }

    $user_id = $_SESSION['auser'];

    // Load required files with validation
    $required_files = [
        __DIR__ . '/../config.php'
    ];

    foreach ($required_files as $file) {
        if (!file_exists($file) || !is_readable($file)) {
            logSecurityEvent('Required File Missing in SMS API', [
                'file_path' => $file,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
            ]);
            return false;
        }
    }

    require_once $required_files[0];

    // Check if user is admin using prepared statement
    global $conn;
    if (!$conn) {
        logSecurityEvent('Database Connection Failed in SMS API', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        return false;
    }

    $admin_check_stmt = $conn->prepare("SELECT ur.user_id FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ? AND r.name = 'Admin'");
    $admin_check_stmt->bind_param('i', $user_id);
    $admin_check_stmt->execute();
    $result = $admin_check_stmt->get_result();
    $admin_check_stmt->close();

    if ($result->num_rows === 0) {
        logSecurityEvent('Unauthorized SMS API Access Attempt', [
            'user_id' => $user_id,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        return false;
    }

    return ['user_id' => $user_id];
}

// SMS Service class for handling SMS operations
class SMSService {
    private $api_key;
    private $api_secret;
    private $sender_id;
    private $base_url;

    public function __construct() {
        $this->api_key = getenv('SMS_API_KEY') ?: 'your_sms_api_key';
        $this->api_secret = getenv('SMS_API_SECRET') ?: 'your_sms_api_secret';
        $this->sender_id = getenv('SMS_SENDER_ID') ?: 'APSDREAM';
        $this->base_url = getenv('SMS_BASE_URL') ?: 'https://api.smsgateway.com/v1';
    }

    public function send($to, $message) {
        try {
            // Validate phone number format
            if (!preg_match('/^[\d+\s]{10,15}$/', $to)) {
                throw new Exception('Invalid phone number format');
            }

            // Check if SMS gateway is configured
            if ($this->api_key === 'your_sms_api_key' || $this->api_secret === 'your_sms_api_secret') {
                // Demo mode - log SMS but don't actually send
                logSecurityEvent('SMS Demo Mode', [
                    'to' => $to,
                    'message_length' => strlen($message),
                    'message_preview' => substr($message, 0, 50) . (strlen($message) > 50 ? '...' : '')
                ]);
                return true; // Simulate successful sending
            }

            // Prepare SMS data
            $sms_data = [
                'api_key' => $this->api_key,
                'api_secret' => $this->api_secret,
                'to' => $to,
                'message' => $message,
                'sender' => $this->sender_id
            ];

            // Initialize cURL
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->base_url . '/send',
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($sms_data),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'User-Agent: APS-Dream-Home-SMS/1.0'
                ]
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($curl_error) {
                throw new Exception('SMS API Error: ' . $curl_error);
            }

            if ($http_code !== 200) {
                throw new Exception('SMS API returned HTTP ' . $http_code . ': ' . $response);
            }

            $response_data = json_decode($response, true);
            if (!$response_data || !isset($response_data['success'])) {
                throw new Exception('Invalid SMS API response: ' . $response);
            }

            return $response_data['success'];

        } catch (Exception $e) {
            logSecurityEvent('SMS Service Exception', [
                'error_message' => $e->getMessage(),
                'to' => $to,
                'message_length' => strlen($message)
            ]);
            throw $e;
        }
    }
}

// Main API logic
try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        logSecurityEvent('Invalid Request Method in SMS API', [
            'method' => $_SERVER['REQUEST_METHOD'],
            'ip_address' => $ip_address,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(405, 'Method not allowed. Only POST requests are accepted.');
    }

    // Validate request headers
    if (!validateRequestHeaders()) {
        logSecurityEvent('Invalid Request Headers in SMS API', [
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
        logSecurityEvent('Empty Request Body in SMS API', [
            'ip_address' => $ip_address,
            'user_id' => $user['user_id'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(400, 'Request body is required.');
    }

    // Validate JSON format
    $data = json_decode($raw_input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        logSecurityEvent('Invalid JSON Format in SMS API', [
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
            logSecurityEvent('CSRF Token Mismatch in SMS API', [
                'provided_token' => substr($_SERVER['HTTP_X_CSRF_TOKEN'], 0, 8) . '...',
                'expected_token' => substr($_SESSION['csrf_token'], 0, 8) . '...',
                'ip_address' => $ip_address,
                'user_id' => $user['user_id']
            ]);
            sendSecurityResponse(403, 'CSRF token validation failed.');
        }
    }

    // Validate required fields
    if (!isset($data['to']) || !isset($data['message'])) {
        logSecurityEvent('Missing Required Fields in SMS API', [
            'provided_fields' => implode(', ', array_keys($data)),
            'ip_address' => $ip_address,
            'user_id' => $user['user_id']
        ]);
        sendSecurityResponse(400, 'Phone number and message are required fields.');
    }

    // Validate and sanitize inputs
    $to = validateInput($data['to'], 'phone');
    $message = validateInput($data['message'], 'message');

    if ($to === false) {
        logSecurityEvent('Invalid Phone Number in SMS API', [
            'phone' => $data['to'],
            'ip_address' => $ip_address,
            'user_id' => $user['user_id']
        ]);
        sendSecurityResponse(400, 'Invalid phone number format. Must be 10-15 digits.');
    }

    if ($message === false) {
        logSecurityEvent('Invalid Message in SMS API', [
            'message_length' => strlen($data['message']),
            'ip_address' => $ip_address,
            'user_id' => $user['user_id']
        ]);
        sendSecurityResponse(400, 'Invalid message format or too long (max 160 characters for SMS).');
    }

    // Check for suspicious patterns in input
    $suspicious_patterns = ['<script', 'javascript:', 'onload=', 'onerror=', 'eval(', 'alert(', 'document.cookie', 'http://', 'https://'];
    foreach ($suspicious_patterns as $pattern) {
        if (stripos($raw_input, $pattern) !== false) {
            logSecurityEvent('Suspicious Input Pattern Detected in SMS API', [
                'pattern' => $pattern,
                'ip_address' => $ip_address,
                'user_id' => $user['user_id']
            ]);
            sendSecurityResponse(400, 'Suspicious content detected in request.');
        }
    }

    // Log SMS sending attempt
    logSecurityEvent('SMS Sending Attempt', [
        'to' => $to,
        'message_length' => strlen($message),
        'message_preview' => substr($message, 0, 50) . (strlen($message) > 50 ? '...' : ''),
        'ip_address' => $ip_address,
        'user_id' => $user['user_id']
    ]);

    // Initialize SMS Service and send SMS
    $smsService = new SMSService();

    // Send SMS with error handling
    try {
        $sent = $smsService->send($to, $message);

        if ($sent) {
            // Log successful SMS sending
            logSecurityEvent('SMS Sent Successfully', [
                'to' => $to,
                'message_length' => strlen($message),
                'ip_address' => $ip_address,
                'user_id' => $user['user_id']
            ]);

            // Prepare success response
            $response_data = [
                'sent' => true,
                'to' => $to,
                'message_length' => strlen($message),
                'timestamp' => date('Y-m-d H:i:s'),
                'delivery_status' => 'sent'
            ];

            sendSecurityResponse(200, 'SMS sent successfully', $response_data);
        } else {
            logSecurityEvent('SMS Sending Failed', [
                'to' => $to,
                'message_length' => strlen($message),
                'ip_address' => $ip_address,
                'user_id' => $user['user_id']
            ]);
            sendSecurityResponse(500, 'Failed to send SMS. Please try again.');
        }

    } catch (Exception $e) {
        logSecurityEvent('SMS Service Exception', [
            'error_message' => $e->getMessage(),
            'to' => $to,
            'message_length' => strlen($message),
            'ip_address' => $ip_address,
            'user_id' => $user['user_id']
        ]);
        sendSecurityResponse(500, 'SMS service error occurred.');
    }

} catch (Exception $e) {
    // Enhanced error handling without information disclosure
    logSecurityEvent('SMS API Exception', [
        'error_message' => $e->getMessage(),
        'error_file' => $e->getFile(),
        'error_line' => $e->getLine(),
        'ip_address' => $ip_address,
        'trace' => $e->getTraceAsString()
    ]);

    // Send generic error response without exposing internal details
    sendSecurityResponse(500, 'An internal error occurred while processing your SMS request.');
}
?>
