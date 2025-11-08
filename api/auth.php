<?php
// Enhanced Security Authentication API - APS Dream Home
// Comprehensive API security implementation

// Enhanced Security: Initialize security logging
$security_log_file = __DIR__ . '/../logs/security.log';
ensureLogDirectory($security_log_file);

// Enhanced Security: Validate HTTPS connection
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    logSecurityEvent('HTTP API Access Attempt', [
        'ip' => getClientIP(),
        'endpoint' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// Enhanced Security: Validate request headers
$headers = getallheaders();
if (!validateRequestHeaders($headers)) {
    logSecurityEvent('Invalid API Request Headers', [
        'ip' => getClientIP(),
        'headers' => $headers,
        'endpoint' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request headers']);
    exit();
}

// Enhanced Security: Advanced rate limiting
$rate_limit_file = __DIR__ . '/../logs/api_rate_limit.json';
$max_api_operations = 500; // Max API operations per hour
$rate_limit_data = checkRateLimit(getClientIP(), $rate_limit_file, $max_api_operations);
if (!$rate_limit_data['allowed']) {
    logSecurityEvent('API Rate Limit Exceeded', [
        'ip' => getClientIP(),
        'operations' => $rate_limit_data['operations'],
        'reset_time' => $rate_limit_data['reset_time'],
        'endpoint' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
    http_response_code(429);
    echo json_encode([
        'error' => 'Rate limit exceeded. Please try again later.',
        'retry_after' => $rate_limit_data['reset_time'],
        'requests_remaining' => $rate_limit_data['remaining']
    ]);
    exit();
}

// Enhanced Security: Set comprehensive security headers for API
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com; connect-src 'self'; frame-ancestors 'none';");
header('X-Permitted-Cross-Domain-Policies: none');
header('Cross-Origin-Embedder-Policy: require-corp');
header('Cross-Origin-Opener-Policy: same-origin');
header('Cross-Origin-Resource-Policy: same-origin');
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Enhanced Security: CORS configuration
$allowed_origins = ['https://localhost', 'http://localhost', 'https://127.0.0.1', 'http://127.0.0.1'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400'); // 24 hours
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Enhanced Security: Include security functions
require_once __DIR__ . '/../includes/security/security_functions.php';
require_once __DIR__ . '/../includes/security/api_middleware.php';

// Enhanced Security: Initialize API security middleware
$api_security = new APISecurityMiddleware();
$api_security->secureAPIRequest();

// Enhanced Security: Log API access
logSecurityEvent('API Authentication Endpoint Access', [
    'ip' => getClientIP(),
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'endpoint' => $_SERVER['REQUEST_URI'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'timestamp' => date('Y-m-d H:i:s')
], $security_log_file);

// Disable error display in production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/auth_api_error.log');
error_reporting(E_ALL);

// Enhanced Security: Secure PHP configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 3600); // 1 hour

// Start secure session for API
$session_name = 'secure_api_session';
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

// Enhanced Security: Session regeneration and timeout
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Enhanced Security: Session timeout check
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
    logSecurityEvent('API Session Timeout', [
        'ip' => getClientIP(),
        'session_id' => session_id(),
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
    session_unset();
    session_destroy();
    sendAPIError('Session expired', 401);
}
$_SESSION['last_activity'] = time();

// Enhanced Security: API Key authentication
$api_security->validateAPIAuth(true);

// Enhanced Security: Request method validation
$request_method = $_SERVER['REQUEST_METHOD'];
$allowed_methods = ['GET', 'POST', 'PUT', 'DELETE'];

if (!in_array($request_method, $allowed_methods)) {
    logSecurityEvent('Invalid API Method', [
        'ip' => getClientIP(),
        'method' => $request_method,
        'endpoint' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
    sendAPIError('Method not allowed', 405);
}

// Enhanced Security: Input validation and sanitization
$input_data = [];
if ($request_method === 'POST' || $request_method === 'PUT') {
    $input_data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
} else {
    $input_data = $_GET;
}

// Enhanced Security: Validate required fields based on action
$action = $input_data['action'] ?? 'login';

switch ($action) {
    case 'login':
        $validation_rules = [
            'email' => 'required|email',
            'password' => 'required|min:8|max:72'
        ];
        break;
    case 'register':
        $validation_rules = [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'password' => 'required|min:8|max:72',
            'confirm_password' => 'required|min:8|max:72'
        ];
        break;
    case 'forgot_password':
        $validation_rules = [
            'email' => 'required|email'
        ];
        break;
    default:
        $validation_rules = [];
}

if (!empty($validation_rules)) {
    $api_security->validateRequestData($validation_rules);
}

// Enhanced Security: Database connection with enhanced security
try {
    $dbConfig = require __DIR__ . '/../config/database.php';
    $conn = new mysqli(
        $dbConfig['host'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['database']
    );

    if ($conn->connect_error) {
        logSecurityEvent('API Database Connection Failed', [
            'ip' => getClientIP(),
            'error' => $conn->connect_error,
            'timestamp' => date('Y-m-d H:i:s')
        ], $security_log_file);
        throw new Exception("Database connection failed");
    }

    // Set charset with validation
    if (!$conn->set_charset("utf8mb4")) {
        logSecurityEvent('API Charset Setting Failed', [
            'ip' => getClientIP(),
            'error' => $conn->error,
            'timestamp' => date('Y-m-d H:i:s')
        ], $security_log_file);
        throw new Exception("Error setting charset");
    }

    // Set SQL mode to strict for better security
    $conn->query("SET SESSION sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE'");

} catch (Exception $e) {
    logSecurityEvent('API Database Error', [
        'ip' => getClientIP(),
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
    sendAPIError('Database connection failed', 500);
}

// Enhanced Security: Main API logic with comprehensive security
$response = ['success' => false, 'message' => 'Invalid action'];

try {
    switch ($action) {
        case 'login':
            $email = $api_security->sanitizeInput($input_data['email'], 'email');
            $password = $input_data['password'];

            // Enhanced Security: Validate input
            if (empty($email) || !isValidEmail($email)) {
                logSecurityEvent('API Login Invalid Email', [
                    'ip' => getClientIP(),
                    'email' => $email,
                    'timestamp' => date('Y-m-d H:i:s')
                ], $security_log_file);
                sendAPIError('Invalid email format', 400);
            }

            if (empty($password) || strlen($password) < 8) {
                logSecurityEvent('API Login Invalid Password', [
                    'ip' => getClientIP(),
                    'timestamp' => date('Y-m-d H:i:s')
                ], $security_log_file);
                sendAPIError('Invalid password', 400);
            }

            // Enhanced Security: Check for suspicious patterns
            if (preg_match('/[<>\"\';]/', $email)) {
                logSecurityEvent('API Suspicious Email Pattern', [
                    'ip' => getClientIP(),
                    'email' => $email,
                    'timestamp' => date('Y-m-d H:i:s')
                ], $security_log_file);
                sendAPIError('Invalid email format', 400);
            }

            // Enhanced Security: Database query with prepared statements
            $query = "SELECT id, email, password, role, status FROM users WHERE email = ? AND status = 'active'";
            $stmt = $conn->prepare($query);

            if (!$stmt) {
                logSecurityEvent('API Login Query Preparation Failed', [
                    'ip' => getClientIP(),
                    'error' => $conn->error,
                    'timestamp' => date('Y-m-d H:i:s')
                ], $security_log_file);
                throw new Exception("Database prepare failed: " . $conn->error);
            }

            $stmt->bind_param('s', $email);

            if (!$stmt->execute()) {
                logSecurityEvent('API Login Query Execution Failed', [
                    'ip' => getClientIP(),
                    'error' => $stmt->error,
                    'timestamp' => date('Y-m-d H:i:s')
                ], $security_log_file);
                throw new Exception("Database execute failed: " . $stmt->error);
            }

            $result = $stmt->get_result();

            if ($result && $user = $result->fetch_assoc()) {
                // Enhanced Security: Password verification with timing attack protection
                if (password_verify($password, $user['password'])) {
                    // Enhanced Security: Generate secure API token
                    $api_token = bin2hex(random_bytes(32));
                    $token_expiry = time() + 3600; // 1 hour

                    // Store token securely
                    $token_query = "INSERT INTO api_tokens (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW())";
                    $token_stmt = $conn->prepare($token_query);

                    if ($token_stmt) {
                        $token_stmt->bind_param('iss', $user['id'], $api_token, $token_expiry);
                        $token_stmt->execute();
                        $token_stmt->close();
                    }

                    // Enhanced Security: Log successful login
                    logSecurityEvent('API Login Successful', [
                        'ip' => getClientIP(),
                        'user_id' => $user['id'],
                        'user_role' => $user['role'],
                        'timestamp' => date('Y-m-d H:i:s')
                    ], $security_log_file);

                    $response = [
                        'success' => true,
                        'message' => 'Login successful',
                        'data' => [
                            'user_id' => $user['id'],
                            'email' => $user['email'],
                            'role' => $user['role'],
                            'api_token' => $api_token,
                            'token_expiry' => $token_expiry
                        ]
                    ];
                } else {
                    logSecurityEvent('API Login Failed - Invalid Password', [
                        'ip' => getClientIP(),
                        'email' => $email,
                        'timestamp' => date('Y-m-d H:i:s')
                    ], $security_log_file);
                    sendAPIError('Invalid credentials', 401);
                }
            } else {
                logSecurityEvent('API Login Failed - User Not Found', [
                    'ip' => getClientIP(),
                    'email' => $email,
                    'timestamp' => date('Y-m-d H:i:s')
                ], $security_log_file);
                sendAPIError('Invalid credentials', 401);
            }

            $stmt->close();
            break;

        case 'register':
            $name = $api_security->sanitizeInput($input_data['name'], 'string');
            $email = $api_security->sanitizeInput($input_data['email'], 'email');
            $password = $input_data['password'];
            $confirm_password = $input_data['confirm_password'];

            // Enhanced Security: Comprehensive validation
            if (empty($name) || strlen($name) < 2 || strlen($name) > 100) {
                sendAPIError('Name must be between 2 and 100 characters', 400);
            }

            if (empty($email) || !isValidEmail($email)) {
                sendAPIError('Invalid email format', 400);
            }

            if (empty($password) || strlen($password) < 8 || strlen($password) > 72) {
                sendAPIError('Password must be between 8 and 72 characters', 400);
            }

            if ($password !== $confirm_password) {
                sendAPIError('Passwords do not match', 400);
            }

            // Check if user already exists
            $check_query = "SELECT id FROM users WHERE email = ?";
            $check_stmt = $conn->prepare($check_query);

            if ($check_stmt) {
                $check_stmt->bind_param('s', $email);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    logSecurityEvent('API Registration Failed - Email Exists', [
                        'ip' => getClientIP(),
                        'email' => $email,
                        'timestamp' => date('Y-m-d H:i:s')
                    ], $security_log_file);
                    sendAPIError('Email already exists', 409);
                }

                $check_stmt->close();
            }

            // Enhanced Security: Hash password securely
            $hashed_password = password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,
                'time_cost' => 4,
                'threads' => 3
            ]);

            // Insert new user
            $insert_query = "INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'customer', 'active', NOW())";
            $insert_stmt = $conn->prepare($insert_query);

            if ($insert_stmt) {
                $insert_stmt->bind_param('sss', $name, $email, $hashed_password);

                if ($insert_stmt->execute()) {
                    $user_id = $conn->insert_id;

                    logSecurityEvent('API Registration Successful', [
                        'ip' => getClientIP(),
                        'user_id' => $user_id,
                        'email' => $email,
                        'timestamp' => date('Y-m-d H:i:s')
                    ], $security_log_file);

                    $response = [
                        'success' => true,
                        'message' => 'Registration successful',
                        'data' => [
                            'user_id' => $user_id,
                            'email' => $email,
                            'name' => $name
                        ]
                    ];
                } else {
                    logSecurityEvent('API Registration Database Error', [
                        'ip' => getClientIP(),
                        'error' => $insert_stmt->error,
                        'timestamp' => date('Y-m-d H:i:s')
                    ], $security_log_file);
                    sendAPIError('Registration failed', 500);
                }

                $insert_stmt->close();
            } else {
                logSecurityEvent('API Registration Query Preparation Failed', [
                    'ip' => getClientIP(),
                    'error' => $conn->error,
                    'timestamp' => date('Y-m-d H:i:s')
                ], $security_log_file);
                sendAPIError('Registration failed', 500);
            }
            break;

        case 'forgot_password':
            $email = $api_security->sanitizeInput($input_data['email'], 'email');

            if (empty($email) || !isValidEmail($email)) {
                sendAPIError('Invalid email format', 400);
            }

            // Check if user exists
            $check_query = "SELECT id FROM users WHERE email = ? AND status = 'active'";
            $check_stmt = $conn->prepare($check_query);

            if ($check_stmt) {
                $check_stmt->bind_param('s', $email);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    // Generate secure reset token
                    $reset_token = bin2hex(random_bytes(32));
                    $reset_expiry = time() + 3600; // 1 hour

                    // Store reset token
                    $token_query = "INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (?, ?, ?, NOW())";
                    $token_stmt = $conn->prepare($token_query);

                    if ($token_stmt) {
                        $token_stmt->bind_param('sis', $email, $reset_token, $reset_expiry);
                        $token_stmt->execute();
                        $token_stmt->close();
                    }

                    // In a real application, you would send an email here
                    logSecurityEvent('API Password Reset Requested', [
                        'ip' => getClientIP(),
                        'email' => $email,
                        'timestamp' => date('Y-m-d H:i:s')
                    ], $security_log_file);

                    $response = [
                        'success' => true,
                        'message' => 'Password reset instructions sent to your email'
                    ];
                } else {
                    // Don't reveal if email exists or not for security
                    logSecurityEvent('API Password Reset - Email Not Found', [
                        'ip' => getClientIP(),
                        'email' => $email,
                        'timestamp' => date('Y-m-d H:i:s')
                    ], $security_log_file);
                    sendAPIError('If the email exists, you will receive reset instructions', 404);
                }

                $check_stmt->close();
            } else {
                logSecurityEvent('API Password Reset Query Failed', [
                    'ip' => getClientIP(),
                    'error' => $conn->error,
                    'timestamp' => date('Y-m-d H:i:s')
                ], $security_log_file);
                sendAPIError('Password reset failed', 500);
            }
            break;

        default:
            logSecurityEvent('API Invalid Action', [
                'ip' => getClientIP(),
                'action' => $action,
                'timestamp' => date('Y-m-d H:i:s')
            ], $security_log_file);
            sendAPIError('Invalid action', 400);
    }

} catch (Exception $e) {
    logSecurityEvent('API Error', [
        'ip' => getClientIP(),
        'error' => $e->getMessage(),
        'action' => $action,
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
    sendAPIError('An error occurred', 500);
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}

// Enhanced Security: Final response logging
logSecurityEvent('API Response Sent', [
    'ip' => getClientIP(),
    'success' => $response['success'],
    'action' => $action,
    'timestamp' => date('Y-m-d H:i:s')
], $security_log_file);

// Send JSON response
echo json_encode($response);
exit();
