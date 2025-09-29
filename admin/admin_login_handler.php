<?php
/**
 * Enhanced Security Admin Login Handler
 * Provides secure authentication with comprehensive security measures
 * Security Enhanced Version
 */

// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/admin_login_security.log');
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
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');
    header('Access-Control-Allow-Credentials: false');
    header('Access-Control-Max-Age: 3600'); // 1 hour
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Rate limiting for admin login attempts
$max_login_attempts = 5; // attempts per hour
$lockout_duration = 900; // 15 minutes lockout
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
    logSecurityEvent('Admin Session Timeout', [
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
        if (strpos($key, 'admin_login_attempts_') === 0 && is_array($value)) {
            if (time() - $value['last_attempt'] > 3600) {
                unset($_SESSION[$key]);
            }
        }
    }
    $_SESSION['rate_limit_cleanup'] = time();
}

// Check rate limiting
$rate_limit_key = 'admin_login_attempts_' . md5($ip_address);
if (!isset($_SESSION[$rate_limit_key])) {
    $_SESSION[$rate_limit_key] = [
        'attempts' => 0,
        'first_attempt' => $current_time,
        'last_attempt' => $current_time,
        'locked_until' => 0
    ];
}

$rate_limit_data = &$_SESSION[$rate_limit_key];

// Check if account is currently locked
if ($rate_limit_data['locked_until'] > $current_time) {
    logSecurityEvent('Admin Login Attempt During Lockout', [
        'ip_address' => $ip_address,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
        'locked_until' => date('Y-m-d H:i:s', $rate_limit_data['locked_until'])
    ]);
    sendSecurityResponse(429, 'Too many failed login attempts. Please try again later.');
}

if ($current_time - $rate_limit_data['first_attempt'] < 3600) {
    $rate_limit_data['attempts']++;
    if ($rate_limit_data['attempts'] > $max_login_attempts) {
        $rate_limit_data['locked_until'] = $current_time + $lockout_duration;
        logSecurityEvent('Admin Login Rate Limit Exceeded', [
            'ip_address' => $ip_address,
            'attempts' => $rate_limit_data['attempts'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(429, 'Too many failed login attempts. Account temporarily locked.');
    }
} else {
    $rate_limit_data['attempts'] = 1;
    $rate_limit_data['first_attempt'] = $current_time;
}

$rate_limit_data['last_attempt'] = $current_time;

// Security event logging function
function logSecurityEvent($event, $context = []) {
    static $logFile = null;

    if ($logFile === null) {
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/admin_login_security.log';
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
        'request_id' => uniqid('admin_login_')
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    // Add security headers to response
    header('X-Response-Time: ' . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']));
    header('X-Security-Status: protected');
    header('X-Rate-Limit-Remaining: ' . ($GLOBALS['max_login_attempts'] - $GLOBALS['rate_limit_data']['attempts']));
    header('X-Rate-Limit-Reset: ' . ($GLOBALS['rate_limit_data']['first_attempt'] + 3600));

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
        case 'password':
            // Password should be at least 8 characters
            if (strlen($input) < 8) {
                return false;
            }
            // Don't sanitize password - keep it as-is for verification
            break;
        case 'captcha':
            $input = filter_var($input, FILTER_VALIDATE_INT);
            if ($input === false || $input < 0) {
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
        logSecurityEvent('Suspicious User Agent in Admin Login', [
            'user_agent' => $user_agent,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
    }

    return true;
}

// Load required files with validation
$required_files = [
    __DIR__ . '/../includes/db_connection.php',
    __DIR__ . '/includes/session_manager.php',
    __DIR__ . '/includes/csrf_protection.php',
    __DIR__ . '/../includes/password_utils.php'
];

foreach ($required_files as $file) {
    if (!file_exists($file) || !is_readable($file)) {
        logSecurityEvent('Required File Missing in Admin Login Handler', [
            'file_path' => $file,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(500, 'System configuration error.');
    }
}

require_once $required_files[0];
require_once $required_files[1];
require_once $required_files[2];
require_once $required_files[3];

// Define ValidationException class if not already defined
class ValidationException extends Exception {
    public function __construct($message, $code = 0, ?Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

// Define logAdminAction function if not already defined elsewhere
if (!function_exists('logAdminAction')) {
    function logAdminAction($data) {
        error_log("[Admin Action] " . json_encode($data));

        // If you have a database logging function, you can call it here
        if (function_exists('log_admin_action_db')) {
            log_admin_action_db('admin_action', json_encode($data));
        }
    }
}

// Initialize session with proper security settings
initAdminSession();

class AdminLoginHandler {
    private const SESSION_TIMEOUT = 1800; // 30 minutes in seconds

    public static function login($username, $password) {
        try {
            // Validate login attempt
            if (!self::validateLoginAttempt($username, $password)) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid login attempt'
                ];
            }

            // Fetch user from database with enhanced security
            $user = self::getUserByUsername($username);

            if (!$user) {
                logAdminAction([
                    'action' => 'login_failed',
                    'username' => $username,
                    'ip_address' => $_SERVER['REMOTE_ADDR'],
                    'reason' => 'user_not_found'
                ]);
                return [
                    'status' => 'error',
                    'message' => 'Invalid username or password'
                ];
            }

            // Verify password with enhanced security
            if (!self::verifyPassword($password, $user['apass'])) {
                logAdminAction([
                    'action' => 'login_failed',
                    'username' => $username,
                    'ip_address' => $_SERVER['REMOTE_ADDR'],
                    'reason' => 'password_mismatch'
                ]);
                return [
                    'status' => 'error',
                    'message' => 'Invalid username or password'
                ];
            }

            // Check if password needs rehashing
            if (password_needs_rehash($user['apass'], PASSWORD_ARGON2ID)) {
                self::updatePasswordHash($user['id'], $password);
            }

            // Successful login
            return self::createSession($user);

        } catch (Exception $e) {
            logSecurityEvent('Admin Login Exception', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'username' => $username ?? 'UNKNOWN'
            ]);
            return [
                'status' => 'error',
                'message' => 'An error occurred during login. Please try again.'
            ];
        }
    }

    private static function validateLoginAttempt($username, $password) {
        // Enhanced validation
        return !empty($username) && !empty($password) &&
               strlen($username) >= 3 && strlen($username) <= 50 &&
               strlen($password) >= 8;
    }

    private static function getUserByUsername($username) {
        $conn = getDbConnection();
        if (!$conn) {
            logSecurityEvent('Database Connection Failed in Admin Login', [
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
            ]);
            return null;
        }

        try {
            $stmt = $conn->prepare("SELECT id, auser, apass, role, status FROM admin WHERE auser = ? LIMIT 1");
            if (!$stmt) {
                logSecurityEvent('Database Prepare Failed in Admin Login', [
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                    'error' => 'Prepare statement failed'
                ]);
                return null;
            }

            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $user ?: null;
            
        } catch (PDOException $e) {
            logSecurityEvent('Database Error in Admin Login', [
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private static function verifyPassword($password, $hash) {
        // Enhanced password verification with multiple hash support
        if (strpos($hash, '$argon2id$') === 0) {
            return password_verify($password, $hash);
        } elseif (strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0) {
            return password_verify($password, $hash);
        } elseif (preg_match('/^[a-f0-9]{40}$/i', $hash)) {
            // SHA1 fallback - should be upgraded
            return (sha1($password) === $hash);
        }

        return false;
    }

    private static function updatePasswordHash($user_id, $password) {
        $conn = getDbConnection();
        if (!$conn) {
            return false;
        }

        try {
            $new_hash = password_hash($password, PASSWORD_ARGON2ID);
            $stmt = $conn->prepare('UPDATE admin SET apass = ? WHERE id = ?');
            if (!$stmt) {
                return false;
            }

            $result = $stmt->execute([$new_hash, $user_id]);
            return $result;
            
        } catch (PDOException $e) {
            logSecurityEvent('Password Hash Update Failed', [
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'error' => $e->getMessage(),
                'user_id' => $user_id
            ]);
            return false;
        }
    }

    private static function createSession($user) {
        // Set up secure session data
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['auser'];
        $_SESSION['admin_role'] = $user['role'];
        $_SESSION['admin_last_activity'] = time();
        $_SESSION['admin_session']['is_authenticated'] = true;
        $_SESSION['admin_session']['username'] = $user['auser'];
        $_SESSION['admin_session']['role'] = $user['role'];
        $_SESSION['admin_session']['user_id'] = $user['id'];

        // Regenerate session ID for security
        session_regenerate_id(true);

        // Log successful login
        logAdminAction([
            'action' => 'login_success',
            'username' => $user['auser'],
            'role' => $user['role'],
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);

        return [
            'status' => 'success',
            'message' => 'Logged in successfully',
            'redirect' => self::getDashboardForRole($user['role'])
        ];
    }

    private static function getDashboardForRole($role) {
        // Return appropriate dashboard URL based on role
        $role_dashboard_map = [
            'superadmin' => 'superadmin_dashboard.php',
            'admin' => 'dashboard.php',
            'manager' => 'manager_dashboard.php',
            'director' => 'director_dashboard.php',
            'office_admin' => 'office_admin_dashboard.php',
            'sales' => 'sales_dashboard.php',
            'employee' => 'employee_dashboard.php',
            'legal' => 'legal_dashboard.php',
            'marketing' => 'marketing_dashboard.php',
            'finance' => 'finance_dashboard.php',
            'hr' => 'hr_dashboard.php',
            'it' => 'it_dashboard.php',
            'operations' => 'operations_dashboard.php',
            'support' => 'support_dashboard.php'
        ];

        return $role_dashboard_map[$role] ?? 'dashboard.php';
    }

    public static function checkSession() {
        // Check if user is logged in
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            return [
                'status' => 'error',
                'message' => 'Not authenticated',
                'redirect' => 'index.php'
            ];
        }

        // Check session timeout
        if (time() - $_SESSION['admin_last_activity'] > self::SESSION_TIMEOUT) {
            self::terminateSession();
            return [
                'status' => 'error',
                'message' => 'Session expired',
                'redirect' => 'index.php'
            ];
        }

        // Update last activity time
        $_SESSION['admin_last_activity'] = time();

        return [
            'status' => 'success',
            'message' => 'Session valid'
        ];
    }

    public static function terminateSession() {
        // Clear all session data
        $_SESSION = [];

        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/admin');
        }

        // Destroy the session
        session_destroy();
    }

    public static function handleLogin() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                logSecurityEvent('Invalid Request Method in Admin Login', [
                    'method' => $_SERVER['REQUEST_METHOD'],
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
                ]);
                header('Location: index.php');
                exit();
            }

            // Validate request headers
            if (!validateRequestHeaders()) {
                logSecurityEvent('Invalid Request Headers in Admin Login', [
                    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'MISSING',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
                ]);
                sendSecurityResponse(400, 'Invalid request headers.');
            }

            // CSRF token validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                logSecurityEvent('CSRF Token Mismatch in Admin Login', [
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                    'provided_token' => substr($_POST['csrf_token'] ?? '', 0, 8) . '...',
                    'expected_token' => substr($_SESSION['csrf_token'], 0, 8) . '...'
                ]);
                $_SESSION['login_error'] = 'Security validation failed';
                header('Location: index.php');
                exit();
            }

            // CAPTCHA validation
            $captcha_answer = validateInput($_POST['captcha_answer'] ?? '', 'captcha', 10, false);
            if ($captcha_answer === false ||
                !isset($_SESSION['captcha_num1_admin']) ||
                !isset($_SESSION['captcha_num2_admin']) ||
                $captcha_answer !== ($_SESSION['captcha_num1_admin'] + $_SESSION['captcha_num2_admin'])) {
                logSecurityEvent('Invalid CAPTCHA in Admin Login', [
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                    'captcha_answer' => $captcha_answer,
                    'expected_answer' => $_SESSION['captcha_num1_admin'] + $_SESSION['captcha_num2_admin']
                ]);
                $_SESSION['login_error'] = 'Invalid security answer';
                header('Location: index.php');
                exit();
            }

            $username = validateInput($_POST['username'] ?? '', 'username');
            $password = $_POST['password'] ?? ''; // Don't sanitize password

            if ($username === false || empty($password)) {
                logSecurityEvent('Invalid Input in Admin Login', [
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                    'username' => $_POST['username'] ?? 'NULL',
                    'password_length' => strlen($password)
                ]);
                $_SESSION['login_error'] = 'Please fill in all fields correctly';
                header('Location: index.php');
                exit();
            }

            // Get database connection
            $conn = getDbConnection();
            if (!$conn) {
                logSecurityEvent('Database Connection Failed in Admin Login Handler', [
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
                ]);
                $_SESSION['login_error'] = 'System error. Please try again later.';
                header('Location: index.php');
                exit();
            }

            // Check if account is locked
            if (isset($_SESSION['admin_login_blocked_until']) && $_SESSION['admin_login_blocked_until'] > time()) {
                logSecurityEvent('Admin Login Attempt During Lockout', [
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                    'locked_until' => date('Y-m-d H:i:s', $_SESSION['admin_login_blocked_until'])
                ]);
                $_SESSION['login_error'] = 'Too many failed login attempts. Please try again after ' . date('H:i:s', $_SESSION['admin_login_blocked_until']);
                header('Location: index.php');
                exit();
            }

            $user = null;
            try {
                $stmt = $conn->prepare("SELECT id, auser, apass, role, status FROM admin WHERE auser = ? LIMIT 1");
                if (!$stmt) {
                    logSecurityEvent('Database Prepare Failed in Admin Login', [
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                        'error' => 'Prepare statement failed'
                    ]);
                    $_SESSION['login_error'] = 'System error. Please try again later.';
                    header('Location: index.php');
                    exit();
                }

                $stmt->execute([$username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user) {
                    self::handleUserNotFound($username);
                }
                
            } catch (PDOException $e) {
                logSecurityEvent('Database Error in Admin Login Handler', [
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                    'error' => $e->getMessage()
                ]);
                $_SESSION['login_error'] = 'System error. Please try again later.';
                header('Location: index.php');
                exit();
            }

            // Check user status
            if ($user['status'] !== 'active') {
                logSecurityEvent('Inactive Account Login Attempt', [
                    'username' => $username,
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                    'status' => $user['status']
                ]);
                $_SESSION['login_error'] = 'Account is not active';
                header('Location: index.php');
                exit();
            }

            // Verify password
            if (self::verifyPassword($password, $user['apass'])) {
                // Check if password needs rehashing
                if (password_needs_rehash($user['apass'], PASSWORD_ARGON2ID)) {
                    self::updatePasswordHash($user['id'], $password);
                }

                // Successful login
                session_regenerate_id(true); // Prevent session fixation

                // Set comprehensive session data
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['auser'];
                $_SESSION['admin_role'] = $user['role'];
                $_SESSION['admin_last_activity'] = time();

                // Log successful login
                logAdminAction([
                    'action' => 'login_success',
                    'username' => $user['auser'],
                    'role' => $user['role'],
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
                ]);

                // Redirect based on user role
                $dashboard = self::getDashboardForRole($user['role']);
                if (file_exists(__DIR__ . '/' . $dashboard)) {
                    header('Location: ' . $dashboard);
                } else {
                    header('Location: dashboard.php');
                }
                exit();
            } else {
                // Failed password verification
                self::handleFailedLogin($username);
            }

        } catch (Exception $e) {
            logSecurityEvent('Admin Login Handler Exception', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'trace' => $e->getTraceAsString()
            ]);
            $_SESSION['login_error'] = 'An error occurred. Please try again.';
            header('Location: index.php');
            exit();
        }
    }

    private static function handleUserNotFound($username) {
        logSecurityEvent('User Not Found in Admin Login', [
            'username' => $username,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);

        $_SESSION['login_error'] = 'Invalid username or password';
        $_SESSION['admin_login_attempts'] = ($_SESSION['admin_login_attempts'] ?? 0) + 1;

        if ($_SESSION['admin_login_attempts'] >= $GLOBALS['max_login_attempts']) {
            $_SESSION['admin_login_blocked_until'] = time() + $GLOBALS['lockout_duration'];
            $_SESSION['login_error'] = 'Too many failed login attempts. Please try again after ' . date('H:i:s', $_SESSION['admin_login_blocked_until']);
        }

        logAdminAction([
            'action' => 'login_failed',
            'username' => $username,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'reason' => 'user_not_found'
        ]);

        header('Location: index.php');
        exit();
    }

    private static function handleFailedLogin($username) {
        logSecurityEvent('Failed Password Verification in Admin Login', [
            'username' => $username,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);

        $_SESSION['login_error'] = 'Invalid username or password';
        $_SESSION['admin_login_attempts'] = ($_SESSION['admin_login_attempts'] ?? 0) + 1;

        if ($_SESSION['admin_login_attempts'] >= $GLOBALS['max_login_attempts']) {
            $_SESSION['admin_login_blocked_until'] = time() + $GLOBALS['lockout_duration'];
            $_SESSION['login_error'] = 'Too many failed login attempts. Please try again after ' . date('H:i:s', $_SESSION['admin_login_blocked_until']);
        }

        logAdminAction([
            'action' => 'login_failed',
            'username' => $username,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'reason' => 'password_mismatch'
        ]);

        header('Location: index.php');
        exit();
    }

    public static function updateSessionActivity() {
        $_SESSION['admin_session']['last_activity'] = time();
        $_SESSION['admin_session']['is_authenticated'] = true;
        $_SESSION['admin_logged_in'] = true;

        // Regenerate session ID after successful login
        session_regenerate_id(true);

        return [
            'status' => 'success',
            'message' => 'Session updated successfully.'
        ];
    }
}
?>
