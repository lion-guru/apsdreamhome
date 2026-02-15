<?php
/**
 * Enhanced Security User Actions Handler
 * Provides secure user management with comprehensive security measures
 * Security Enhanced Version
 */

// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/user_actions_security.log');
error_reporting(E_ALL);

// Set comprehensive security headers for admin panel
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

// Rate limiting for user actions
$max_user_actions = 10; // actions per minute
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$current_time = time();

// Start secure session for admin
$session_name = 'secure_admin_session';
$secure = true; // Only send cookies over HTTPS
$httponly = true; // Prevent JavaScript access to session cookie
$samesite = 'Strict';

if (PHP_VERSION_ID < 70300) {
    session_set_cookie_params([
        'lifetime' => 1800, // 30 minutes
        'path' => '/admin',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
} else {
    session_set_cookie_params([
        'lifetime' => 1800, // 30 minutes
        'path' => '/admin',
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
    logSecurityEvent('User Actions Session Timeout', [
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

// Rate limiting check
$rate_limit_key = 'user_actions_' . md5($ip_address);
if (!isset($_SESSION[$rate_limit_key])) {
    $_SESSION[$rate_limit_key] = [
        'actions' => 0,
        'first_action' => $current_time,
        'last_action' => $current_time
    ];
}

$rate_limit_data = &$_SESSION[$rate_limit_key];

// Check if rate limit exceeded
if ($current_time - $rate_limit_data['first_action'] < 60) {
    $rate_limit_data['actions']++;
    if ($rate_limit_data['actions'] > $max_user_actions) {
        logSecurityEvent('User Actions Rate Limit Exceeded', [
            'ip_address' => $ip_address,
            'actions' => $rate_limit_data['actions'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(429, 'Too many user actions. Please slow down.');
    }
} else {
    $rate_limit_data['actions'] = 1;
    $rate_limit_data['first_action'] = $current_time;
}

$rate_limit_data['last_action'] = $current_time;

// Security event logging function
function logSecurityEvent($event, $context = []) {
    static $logFile = null;

    if ($logFile === null) {
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/user_actions_security.log';
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
        'request_id' => uniqid('user_action_')
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    // Add security headers to response
    header('X-Response-Time: ' . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']));
    header('X-Security-Status: protected');
    header('X-Rate-Limit-Remaining: ' . ($GLOBALS['max_user_actions'] - $GLOBALS['rate_limit_data']['actions']));
    header('X-Rate-Limit-Reset: ' . ($GLOBALS['rate_limit_data']['first_action'] + 60));

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
        case 'username':
            // Allow alphanumeric, underscore, and dot characters only
            if (!preg_match('/^[a-zA-Z0-9_.]{3,50}$/', $input)) {
                return false;
            }
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
        case 'email':
            $input = filter_var($input, FILTER_SANITIZE_EMAIL);
            if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            break;
        case 'phone':
            $input = filter_var($input, FILTER_SANITIZE_STRING);
            $input = preg_replace('/[^\d+\s]/', '', $input);
            if (strlen($input) < 10 || strlen($input) > 15) {
                return false;
            }
            break;
        case 'password':
            // Password should be at least 8 characters
            if (strlen($input) < 8) {
                return false;
            }
            // Don't sanitize password - keep it as-is for hashing
            break;
        case 'role':
            $allowed_roles = ['user', 'admin', 'superadmin', 'associate', 'builder', 'agent', 'employee', 'customer'];
            if (!in_array($input, $allowed_roles)) {
                return false;
            }
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
        case 'status':
            $allowed_statuses = ['active', 'inactive'];
            if (!in_array($input, $allowed_statuses)) {
                return false;
            }
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
        case 'id':
            $input = filter_var($input, FILTER_VALIDATE_INT);
            if ($input === false || $input < 1) {
                return false;
            }
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($content_type, 'application/x-www-form-urlencoded') === false) {
        return false;
    }

    // Check User-Agent (basic bot detection)
    if (empty($user_agent) || strlen($user_agent) < 10) {
        logSecurityEvent('Suspicious User Agent in User Actions', [
            'user_agent' => $user_agent,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
    }

    return true;
}

// Load required files with validation
$required_files = [
    __DIR__ . '/../includes/functions/role_helper.php',
    __DIR__ . '/../src/Database/Database.php'
];

foreach ($required_files as $file) {
    if (!file_exists($file) || !is_readable($file)) {
        logSecurityEvent('Required File Missing in User Actions', [
            'file_path' => $file,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(500, 'System configuration error.');
    }
}

require_once $required_files[0];
require_once $required_files[1];

// Check if user has required role
enforceRole(['admin','superadmin']);

$db = new Database();
$con = $db->getConnection();

if (!$con) {
    logSecurityEvent('Database Connection Failed in User Actions', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
    ]);
    sendSecurityResponse(500, 'Database connection error.');
}

// Validate request headers
if (!validateRequestHeaders()) {
    logSecurityEvent('Invalid Request Headers in User Actions', [
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'MISSING',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
    ]);
    sendSecurityResponse(400, 'Invalid request headers.');
}

// CSRF token validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    logSecurityEvent('CSRF Token Mismatch in User Actions', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'provided_token' => substr($_POST['csrf_token'] ?? '', 0, 8) . '...',
        'expected_token' => substr($_SESSION['csrf_token'], 0, 8) . '...'
    ]);
    sendSecurityResponse(403, 'Security validation failed.');
}

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid request'];

if ($action === 'add') {
    $name = validateInput($_POST['name'] ?? '', 'string', 100);
    $email = validateInput($_POST['email'] ?? '', 'email');
    $phone = validateInput($_POST['phone'] ?? '', 'phone');
    $role = validateInput($_POST['role'] ?? 'user', 'role');
    $password = $_POST['password'] ?? '';
    $status = validateInput($_POST['status'] ?? 'active', 'status');

    if ($name === false || $email === false || $phone === false || $role === false || $status === false) {
        logSecurityEvent('Invalid Input in User Add Action', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'name' => $_POST['name'] ?? 'NULL',
            'email' => $_POST['email'] ?? 'NULL',
            'phone' => $_POST['phone'] ?? 'NULL',
            'role' => $_POST['role'] ?? 'NULL'
        ]);
        sendSecurityResponse(400, 'Invalid input data provided.');
    }

    if (empty($password) || strlen($password) < 8) {
        logSecurityEvent('Invalid Password in User Add Action', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'password_length' => strlen($password)
        ]);
        sendSecurityResponse(400, 'Password must be at least 8 characters long.');
    }

    // Check if email already exists
    $stmt = $con->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    if (!$stmt) {
        logSecurityEvent('Database Prepare Failed in User Add', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $con->error
        ]);
        sendSecurityResponse(500, 'Database error occurred.');
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        logSecurityEvent('Database Execute Failed in User Add', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $stmt->error
        ]);
        $stmt->close();
        sendSecurityResponse(500, 'Database error occurred.');
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        logSecurityEvent('Duplicate Email in User Add Action', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'email' => $email
        ]);
        sendSecurityResponse(400, 'Email address already exists.');
    }
    $stmt->close();

    // Hash password with Argon2ID for better security
    $hashed_password = password_hash($password, PASSWORD_ARGON2ID);

    $stmt = $con->prepare("INSERT INTO users (name, email, phone, utype, password, status) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        logSecurityEvent('Database Prepare Failed in User Add', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $con->error
        ]);
        sendSecurityResponse(500, 'Database error occurred.');
    }

    $stmt->bind_param("ssssss", $name, $email, $phone, $role, $hashed_password, $status);
    if ($stmt->execute()) {
        $user_id = $con->insert_id;
        logSecurityEvent('User Added Successfully', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_id' => $user_id,
            'email' => $email,
            'role' => $role
        ]);
        sendSecurityResponse(200, 'User added successfully', ['user_id' => $user_id]);
    } else {
        logSecurityEvent('Database Execute Failed in User Add', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $stmt->error
        ]);
        $stmt->close();
        sendSecurityResponse(500, 'Failed to add user.');
    }

} elseif ($action === 'edit') {
    $id = validateInput($_POST['id'] ?? '', 'id');
    $name = validateInput($_POST['name'] ?? '', 'string', 100);
    $email = validateInput($_POST['email'] ?? '', 'email');
    $phone = validateInput($_POST['phone'] ?? '', 'phone');
    $role = validateInput($_POST['role'] ?? 'user', 'role');
    $status = validateInput($_POST['status'] ?? 'active', 'status');

    if ($id === false || $name === false || $email === false || $phone === false || $role === false || $status === false) {
        logSecurityEvent('Invalid Input in User Edit Action', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_id' => $_POST['id'] ?? 'NULL',
            'name' => $_POST['name'] ?? 'NULL',
            'email' => $_POST['email'] ?? 'NULL'
        ]);
        sendSecurityResponse(400, 'Invalid input data provided.');
    }

    // Check if email already exists for another user
    $stmt = $con->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
    if (!$stmt) {
        logSecurityEvent('Database Prepare Failed in User Edit', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $con->error
        ]);
        sendSecurityResponse(500, 'Database error occurred.');
    }

    $stmt->bind_param("si", $email, $id);
    if (!$stmt->execute()) {
        logSecurityEvent('Database Execute Failed in User Edit', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $stmt->error
        ]);
        $stmt->close();
        sendSecurityResponse(500, 'Database error occurred.');
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        logSecurityEvent('Duplicate Email in User Edit Action', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'email' => $email,
            'user_id' => $id
        ]);
        sendSecurityResponse(400, 'Email address already exists for another user.');
    }
    $stmt->close();

    $stmt = $con->prepare("UPDATE users SET name=?, email=?, phone=?, utype=?, status=? WHERE id=?");
    if (!$stmt) {
        logSecurityEvent('Database Prepare Failed in User Edit', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $con->error
        ]);
        sendSecurityResponse(500, 'Database error occurred.');
    }

    $stmt->bind_param("sssssi", $name, $email, $phone, $role, $status, $id);
    if ($stmt->execute()) {
        logSecurityEvent('User Updated Successfully', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_id' => $id,
            'email' => $email,
            'role' => $role
        ]);
        sendSecurityResponse(200, 'User updated successfully');
    } else {
        logSecurityEvent('Database Execute Failed in User Edit', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $stmt->error
        ]);
        $stmt->close();
        sendSecurityResponse(500, 'Failed to update user.');
    }

} elseif ($action === 'delete') {
    $id = validateInput($_POST['id'] ?? '', 'id');

    if ($id === false) {
        logSecurityEvent('Invalid User ID in Delete Action', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_id' => $_POST['id'] ?? 'NULL'
        ]);
        sendSecurityResponse(400, 'Invalid user ID provided.');
    }

    // Check if user exists and is not an admin trying to delete themselves
    $stmt = $con->prepare("SELECT id, utype FROM users WHERE id = ? LIMIT 1");
    if (!$stmt) {
        logSecurityEvent('Database Prepare Failed in User Delete', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $con->error
        ]);
        sendSecurityResponse(500, 'Database error occurred.');
    }

    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        logSecurityEvent('Database Execute Failed in User Delete', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $stmt->error
        ]);
        $stmt->close();
        sendSecurityResponse(500, 'Database error occurred.');
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        logSecurityEvent('User Not Found in Delete Action', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_id' => $id
        ]);
        sendSecurityResponse(404, 'User not found.');
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Prevent deletion of superadmin users
    if ($user['utype'] === 'superadmin') {
        logSecurityEvent('Attempted Superadmin Deletion', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_id' => $id
        ]);
        sendSecurityResponse(403, 'Cannot delete superadmin users.');
    }

    // Pre-delete RESTRICT checks for dependent records (saved_searches, sessions, associates, customers)
    // saved_searches -> users (user_id) has ON DELETE RESTRICT
    $savedCount = 0;
    $stmt = $con->prepare("SELECT COUNT(*) AS cnt FROM saved_searches WHERE user_id = ?");
    if (!$stmt) {
        logSecurityEvent('Database Prepare Failed in Pre-Delete (saved_searches)', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $con->error
        ]);
        sendSecurityResponse(500, 'Database error occurred.');
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        logSecurityEvent('Database Execute Failed in Pre-Delete (saved_searches)', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $stmt->error
        ]);
        $stmt->close();
        sendSecurityResponse(500, 'Database error occurred.');
    }
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $savedCount = (int)($row['cnt'] ?? 0);
    }
    $stmt->close();

    // sessions -> users (user_id) has ON DELETE RESTRICT
    $sessionCount = 0;
    $stmt = $con->prepare("SELECT COUNT(*) AS cnt FROM sessions WHERE user_id = ?");
    if (!$stmt) {
        logSecurityEvent('Database Prepare Failed in Pre-Delete (sessions)', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $con->error
        ]);
        sendSecurityResponse(500, 'Database error occurred.');
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        logSecurityEvent('Database Execute Failed in Pre-Delete (sessions)', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $stmt->error
        ]);
        $stmt->close();
        sendSecurityResponse(500, 'Database error occurred.');
    }
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $sessionCount = (int)($row['cnt'] ?? 0);
    }
    $stmt->close();

    // associates -> users (user_id) has ON DELETE RESTRICT
    $associateCount = 0;
    $stmt = $con->prepare("SELECT COUNT(*) AS cnt FROM associates WHERE user_id = ?");
    if (!$stmt) {
        logSecurityEvent('Database Prepare Failed in Pre-Delete (associates)', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $con->error
        ]);
        sendSecurityResponse(500, 'Database error occurred.');
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        logSecurityEvent('Database Execute Failed in Pre-Delete (associates)', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $stmt->error
        ]);
        $stmt->close();
        sendSecurityResponse(500, 'Database error occurred.');
    }
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $associateCount = (int)($row['cnt'] ?? 0);
    }
    $stmt->close();

    // customers -> users (user_id) has ON DELETE RESTRICT
    $customerLinkCount = 0;
    $stmt = $con->prepare("SELECT COUNT(*) AS cnt FROM customers WHERE user_id = ?");
    if (!$stmt) {
        logSecurityEvent('Database Prepare Failed in Pre-Delete (customers)', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $con->error
        ]);
        sendSecurityResponse(500, 'Database error occurred.');
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        logSecurityEvent('Database Execute Failed in Pre-Delete (customers)', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $stmt->error
        ]);
        $stmt->close();
        sendSecurityResponse(500, 'Database error occurred.');
    }
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $customerLinkCount = (int)($row['cnt'] ?? 0);
    }
    $stmt->close();

    if ($savedCount > 0 || $sessionCount > 0 || $associateCount > 0 || $customerLinkCount > 0) {
        $details = [];
        if ($savedCount > 0) { $details[] = $savedCount . ' saved searches'; }
        if ($sessionCount > 0) { $details[] = $sessionCount . ' sessions'; }
        if ($associateCount > 0) { $details[] = $associateCount . ' associate profiles'; }
        if ($customerLinkCount > 0) { $details[] = $customerLinkCount . ' linked customer records'; }
        logSecurityEvent('User Delete Restricted by Dependencies', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_id' => $id,
            'saved_searches_count' => $savedCount,
            'sessions_count' => $sessionCount,
            'associates_count' => $associateCount,
            'customers_link_count' => $customerLinkCount
        ]);
        sendSecurityResponse(409, 'Cannot delete user. Remove dependent records first: ' . implode(', ', $details) . '.');
    }

    $stmt = $con->prepare("DELETE FROM users WHERE id=?");
    if (!$stmt) {
        logSecurityEvent('Database Prepare Failed in User Delete', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $con->error
        ]);
        sendSecurityResponse(500, 'Database error occurred.');
    }

    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        logSecurityEvent('User Deleted Successfully', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_id' => $id,
            'user_role' => $user['utype']
        ]);
        sendSecurityResponse(200, 'User deleted successfully');
    } else {
        logSecurityEvent('Database Execute Failed in User Delete', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'error' => $stmt->error
        ]);
        $stmt->close();
        sendSecurityResponse(500, 'Failed to delete user.');
    }
} else {
    logSecurityEvent('Invalid Action in User Actions', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'action' => $action
    ]);
    sendSecurityResponse(400, 'Invalid action specified.');
}

$con->close();
?>
