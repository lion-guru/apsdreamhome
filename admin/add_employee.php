<?php
/**
 * Enhanced Security Employee Management
 * Provides secure employee creation with comprehensive security measures
 * Security Enhanced Version
 */

// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/add_employee_security.log');
error_reporting(E_ALL);

// Set comprehensive security headers for admin panel
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com \'unsafe-inline\'; style-src \'self\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com \'unsafe-inline\'; img-src \'self\' data:;');
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

// Rate limiting for employee operations
$max_employee_operations = 15; // operations per hour
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
    logSecurityEvent('Add Employee Session Timeout', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    header('Location: /admin/index.php?timeout=1');
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Rate limiting check
$rate_limit_key = 'employee_operations_' . md5($ip_address);
if (!isset($_SESSION[$rate_limit_key])) {
    $_SESSION[$rate_limit_key] = [
        'operations' => 0,
        'first_operation' => $current_time,
        'last_operation' => $current_time
    ];
}

$rate_limit_data = &$_SESSION[$rate_limit_key];

// Check if rate limit exceeded
if ($current_time - $rate_limit_data['first_operation'] < 3600) {
    $rate_limit_data['operations']++;
    if ($rate_limit_data['operations'] > $max_employee_operations) {
        logSecurityEvent('Employee Operations Rate Limit Exceeded', [
            'ip_address' => $ip_address,
            'operations' => $rate_limit_data['operations'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'Too many employee operations. Please slow down.',
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
        $logFile = $logDir . '/add_employee_security.log';
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
    return htmlspecialchars($data ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Validate request headers
function validateRequestHeaders() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Check User-Agent (basic bot detection)
    if (empty($user_agent) || strlen($user_agent) < 10) {
        logSecurityEvent('Suspicious User Agent in Add Employee', [
            'user_agent' => $user_agent,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        return false;
    }

    return true;
}

// Validate database connection file
$db_connection_file = __DIR__ . '/config.php';
if (!file_exists($db_connection_file) || !is_readable($db_connection_file)) {
    logSecurityEvent('Database Connection File Missing', [
        'file_path' => $db_connection_file,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
    ]);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'System configuration error.',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

// Include database connection securely
require_once $db_connection_file;

// Check if user has admin privileges
if (!isset($_SESSION['auser'])) {
    logSecurityEvent('Unauthorized Access Attempt to Add Employee', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    header('Location: /admin/index.php');
    exit();
}

// Validate request headers
if (!validateRequestHeaders()) {
    logSecurityEvent('Invalid Request Headers in Add Employee', [
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

// Log page access
logSecurityEvent('Add Employee Page Accessed', [
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
    'session_id' => session_id(),
    'admin_user' => $_SESSION['auser']
]);

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
        case 'url':
            $input = filter_var($input, FILTER_SANITIZE_URL);
            if (!filter_var($input, FILTER_VALIDATE_URL)) {
                return false;
            }
            break;
        case 'boolean':
            if (!is_bool($input) && !in_array($input, [0, 1, '0', '1', true, false], true)) {
                return false;
            }
            return (bool)$input;
        case 'integer':
            $input = filter_var($input, FILTER_VALIDATE_INT);
            if ($input === false) {
                return false;
            }
            break;
        case 'employee_name':
            // Allow letters, spaces, hyphens, apostrophes, and periods
            if (!preg_match('/^[A-Za-z\s\-\'.]+$/', $input)) {
                return false;
            }
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
        case 'role_name':
            // Allow letters, numbers, spaces, hyphens, and underscores
            if (!preg_match('/^[A-Za-z0-9\s\-_()]+$/', $input)) {
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

// Initialize variables
$msg = '';
$max_name_length = 100;
$max_email_length = 255;
$max_role_name_length = 50;
$max_description_length = 255;

// Get all roles using prepared statement with error handling
try {
    $stmt = $conn->prepare("SELECT id, name FROM roles ORDER BY name");
    if (!$stmt) {
        throw new Exception('Failed to prepare roles query: ' . $conn->error);
    }

    if (!$stmt->execute()) {
        throw new Exception('Failed to execute roles query: ' . $stmt->error);
    }

    $roles = $stmt->get_result();
    $stmt->close();
} catch (Exception $e) {
    logSecurityEvent('Error Fetching Roles in Add Employee', [
        'error_message' => $e->getMessage(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'admin_user' => $_SESSION['auser']
    ]);
    $roles = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // CSRF token validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            logSecurityEvent('CSRF Token Mismatch in Add Employee', [
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'provided_token' => substr($_POST['csrf_token'] ?? '', 0, 8) . '...',
                'expected_token' => substr($_SESSION['csrf_token'], 0, 8) . '...'
            ]);
            throw new Exception('Security validation failed.');
        }

        // Handle new role creation with enhanced validation
        if (isset($_POST['new_role']) && trim($_POST['new_role']) !== '') {
            $new_role = validateInput($_POST['new_role'] ?? '', 'role_name', $max_role_name_length);
            $new_desc = validateInput($_POST['new_desc'] ?? '', 'string', $max_description_length, false);

            if ($new_role === false) {
                throw new Exception('Role name is required and must contain only letters, numbers, spaces, hyphens, underscores, and parentheses.');
            }

            // Check if role already exists
            $check_stmt = $conn->prepare("SELECT id FROM roles WHERE name = ?");
            if (!$check_stmt) {
                throw new Exception('Failed to prepare role check statement: ' . $conn->error);
            }

            $check_stmt->bind_param("s", $new_role);
            if (!$check_stmt->execute()) {
                throw new Exception('Failed to execute role check: ' . $check_stmt->error);
            }

            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                throw new Exception('Role already exists.');
            }

            $check_stmt->close();

            // Insert new role with prepared statement
            $role_stmt = $conn->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
            if (!$role_stmt) {
                throw new Exception('Failed to prepare role insert statement: ' . $conn->error);
            }

            $role_stmt->bind_param("ss", $new_role, $new_desc);

            if (!$role_stmt->execute()) {
                throw new Exception('Failed to insert role: ' . $role_stmt->error);
            }

            $new_role_id = $conn->insert_id;
            $role_stmt->close();

            // Log role creation
            logSecurityEvent('New Role Created Successfully', [
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'admin_user' => $_SESSION['auser'],
                'role_name' => $new_role,
                'role_description' => $new_desc,
                'role_id' => $new_role_id
            ]);

            // Reload roles after adding
            $stmt = $conn->prepare("SELECT id, name FROM roles ORDER BY name");
            if ($stmt && $stmt->execute()) {
                $roles = $stmt->get_result();
            }
            $stmt->close();

            $msg = 'Role added successfully. Please continue with employee creation.';
        } else {
            // Validate employee data
            $name = validateInput($_POST['name'] ?? '', 'employee_name', $max_name_length);
            $email = validateInput($_POST['email'] ?? '', 'email', $max_email_length);
            $role_id = isset($_POST['role_id']) ? validateInput($_POST['role_id'], 'integer') : null;

            if ($name === false) {
                throw new Exception('Employee name is required and must contain only letters, spaces, hyphens, apostrophes, and periods.');
            }

            if ($email === false) {
                throw new Exception('Valid email address is required.');
            }

            if ($role_id === false || $role_id <= 0) {
                throw new Exception('Please select a valid role.');
            }

            // Check if employee email already exists
            $email_check_stmt = $conn->prepare("SELECT id FROM employees WHERE email = ?");
            if (!$email_check_stmt) {
                throw new Exception('Failed to prepare email check statement: ' . $conn->error);
            }

            $email_check_stmt->bind_param("s", $email);
            if (!$email_check_stmt->execute()) {
                throw new Exception('Failed to execute email check: ' . $email_check_stmt->error);
            }

            $email_check_result = $email_check_stmt->get_result();

            if ($email_check_result->num_rows > 0) {
                throw new Exception('Employee with this email already exists.');
            }

            $email_check_stmt->close();

            // Insert employee with prepared statement
            $stmt = $conn->prepare("INSERT INTO employees (name, email, status) VALUES (?, ?, 'active')");
            if (!$stmt) {
                throw new Exception('Failed to prepare employee insert statement: ' . $conn->error);
            }

            $stmt->bind_param("ss", $name, $email);

            if (!$stmt->execute()) {
                throw new Exception('Failed to insert employee: ' . $stmt->error);
            }

            $emp_id = $conn->insert_id;
            $stmt->close();

            // Assign role if provided
            if ($role_id) {
                $stmt_role = $conn->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                if (!$stmt_role) {
                    throw new Exception('Failed to prepare role assignment statement: ' . $conn->error);
                }

                $stmt_role->bind_param("ii", $emp_id, $role_id);

                if (!$stmt_role->execute()) {
                    throw new Exception('Failed to assign role: ' . $stmt_role->error);
                }

                $stmt_role->close();
            }

            // Log employee onboarding
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $details = 'Employee onboarding: ' . $name . ' (ID: ' . $emp_id . ', Email: ' . $email . ')';

            $audit_stmt = $conn->prepare("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (?, 'Employee Onboarding', ?, ?)");
            if ($audit_stmt) {
                $audit_stmt->bind_param('iss', $_SESSION['auser'], $details, $ip);
                $audit_stmt->execute();
                $audit_stmt->close();
            }

            // Log successful employee creation
            logSecurityEvent('Employee Created Successfully', [
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'admin_user' => $_SESSION['auser'],
                'employee_name' => $name,
                'employee_email' => $email,
                'role_id' => $role_id,
                'employee_id' => $emp_id
            ]);

            // Send notification if function exists
            if (function_exists('addNotification')) {
                require_once __DIR__ . '/../includes/functions/notification_util.php';
                addNotification($conn, 'Employee', 'Welcome to the system! Your access has been set up.', $emp_id);
            }

            $msg = 'Employee onboarded successfully.';
            header("Location: /admin/employees.php?msg=" . urlencode('Employee onboarded successfully.'));
            exit();
        }

    } catch (Exception $e) {
        logSecurityEvent('Employee Creation Error', [
            'error_message' => $e->getMessage(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
            'admin_user' => $_SESSION['auser'],
            'employee_name' => $_POST['name'] ?? 'UNKNOWN',
            'employee_email' => $_POST['email'] ?? 'UNKNOWN'
        ]);
        $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Employee - Secure</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        body { background: #f8f9fa; }
        .form-label { font-weight: 500; }
        .form-floating > .fa { position: absolute; left: 20px; top: 22px; color: #aaa; pointer-events: none; }
        .form-floating input { padding-left: 2.5rem; }
        .alert { margin-bottom: 1rem; }
        .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25); }
        .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .btn-primary { background-color: #0d6efd; border-color: #0d6efd; }
        .btn-primary:hover { background-color: #0b5ed7; border-color: #0a58ca; }
    </style>
</head>
<body>
<?php
// Validate and include header
$header_file = __DIR__ . '/../includes/templates/dynamic_header.php';
if (file_exists($header_file) && is_readable($header_file)) {
    include $header_file;
} else {
    logSecurityEvent('Header File Missing', ['file_path' => $header_file]);
    echo '<!-- Header not available -->';
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">Add New Employee</h2>
                        <a href="/admin/employees.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Employees
                        </a>
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
                                Rate limit: <?php echo $max_employee_operations - $rate_limit_data['operations']; ?>/<?php echo $max_employee_operations; ?> remaining
                            </small>
                        </div>
                    </div>

                    <?php if ($msg): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php echo $msg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="form-floating mb-3 position-relative">
                            <input type="text" class="form-control" id="name" name="name"
                                   placeholder="Employee Name" required maxlength="<?php echo $max_name_length; ?>"
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                   pattern="[A-Za-z\s\-\'.]+" title="Only letters, spaces, hyphens, apostrophes, and periods allowed">
                            <label for="name"><i class="fa fa-user"></i> Employee Name *</label>
                            <div class="invalid-feedback">Please enter a valid employee name (letters, spaces, hyphens, apostrophes, periods only).</div>
                            <div class="form-text">Enter the full name of the employee</div>
                        </div>

                        <div class="form-floating mb-3 position-relative">
                            <input type="email" class="form-control" id="email" name="email"
                                   placeholder="Email Address" required maxlength="<?php echo $max_email_length; ?>"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <label for="email"><i class="fa fa-envelope"></i> Email Address *</label>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                            <div class="form-text">This will be used for login credentials</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><i class="fa fa-users me-2"></i>Default Role</label>
                            <div class="input-group">
                                <select name="role_id" class="form-select" id="role_id" required>
                                    <option value="">-- Select a Role --</option>
                                    <?php if ($roles): ?>
                                        <?php while ($role = $roles->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($role['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    <?php echo (isset($_POST['role_id']) && $_POST['role_id'] == $role['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#addRoleBox">
                                    <i class="fas fa-plus"></i> New Role
                                </button>
                            </div>
                            <div class="form-text">Select the default role for this employee</div>
                            <div class="invalid-feedback">Please select a role for the employee.</div>

                            <div class="collapse mt-3" id="addRoleBox">
                                <div class="card card-body bg-light">
                                    <h6 class="card-title mb-3">Create New Role</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="text" name="new_role" id="new_role" class="form-control mb-2"
                                                   placeholder="Role Name" maxlength="<?php echo $max_role_name_length; ?>"
                                                   pattern="[A-Za-z0-9\s\-_()]+" title="Only letters, numbers, spaces, hyphens, underscores, and parentheses allowed">
                                            <div class="form-text">Enter a unique role name</div>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="new_desc" id="new_desc" class="form-control mb-2"
                                                   placeholder="Description (optional)" maxlength="<?php echo $max_description_length; ?>">
                                            <div class="form-text">Brief description of the role</div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-2"></i>Create Role
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa fa-user-plus me-2"></i>Add Employee
                            </button>
                            <a href="/admin/employees.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Information Card -->
            <div class="card mt-3 border-info">
                <div class="card-body">
                    <h6 class="card-title text-info">
                        <i class="fas fa-shield-alt me-2"></i>Security Features
                    </h6>
                    <div class="small">
                        <div class="mb-1">
                            <i class="fas fa-check text-success me-2"></i>Enhanced input validation and sanitization
                        </div>
                        <div class="mb-1">
                            <i class="fas fa-check text-success me-2"></i>CSRF protection with token validation
                        </div>
                        <div class="mb-1">
                            <i class="fas fa-check text-success me-2"></i>SQL injection prevention with prepared statements
                        </div>
                        <div class="mb-1">
                            <i class="fas fa-check text-success me-2"></i>Secure session management with timeout
                        </div>
                        <div class="mb-1">
                            <i class="fas fa-check text-success me-2"></i>Rate limiting (<?php echo $max_employee_operations; ?> operations/hour)
                        </div>
                        <div class="mb-1">
                            <i class="fas fa-check text-success me-2"></i>Comprehensive audit logging
                        </div>
                        <div class="mb-1">
                            <i class="fas fa-check text-success me-2"></i>Request header validation
                        </div>
                        <div class="mb-1">
                            <i class="fas fa-check text-success me-2"></i>Email uniqueness validation
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Validate and include footer
$footer_file = __DIR__ . '/../includes/templates/new_footer.php';
if (file_exists($footer_file) && is_readable($footer_file)) {
    include $footer_file;
} else {
    logSecurityEvent('Footer File Missing', ['file_path' => $footer_file]);
    echo '<!-- Footer not available -->';
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<script>
(() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();

// Enhanced client-side validation with security features
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const roleSelect = document.getElementById('role_id');
    const newRoleInput = document.getElementById('new_role');
    const newDescInput = document.getElementById('new_desc');
    const csrfToken = '<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>';

    // Security event logging
    function logSecurityEvent(event, context = {}) {
        fetch('/admin/log_security_event.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                event: event,
                context: context,
                csrf_token: csrfToken
            })
        }).catch(error => console.error('Security logging failed:', error));
    }

    // Real-time validation for employee name
    if (nameInput) {
        nameInput.addEventListener('input', function() {
            const value = this.value;
            const isValid = /^[A-Za-z\s\-\'.]+$/.test(value);

            if (value.length > 0 && !isValid) {
                this.setCustomValidity('Only letters, spaces, hyphens, apostrophes, and periods are allowed');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Real-time validation for email
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            const value = this.value;
            const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);

            if (value.length > 0 && !isValid) {
                this.setCustomValidity('Please enter a valid email address');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Real-time validation for new role name
    if (newRoleInput) {
        newRoleInput.addEventListener('input', function() {
            const value = this.value;
            const isValid = /^[A-Za-z0-9\s\-_()]+$/.test(value);

            if (value.length > 0 && !isValid) {
                this.setCustomValidity('Only letters, numbers, spaces, hyphens, underscores, and parentheses are allowed');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Form submission enhancement with security
    const form = document.querySelector('.needs-validation');
    if (form) {
        form.addEventListener('submit', function(event) {
            // Additional client-side checks
            const name = nameInput ? nameInput.value.trim() : '';
            const email = emailInput ? emailInput.value.trim() : '';
            const roleId = roleSelect ? roleSelect.value : '';

            if (!name) {
                event.preventDefault();
                alert('Please enter an employee name');
                return false;
            }

            if (!email) {
                event.preventDefault();
                alert('Please enter an email address');
                return false;
            }

            if (!roleId && !document.getElementById('new_role').value.trim()) {
                event.preventDefault();
                alert('Please select a role or create a new one');
                return false;
            }

            // Log form submission attempt
            logSecurityEvent('Employee Form Submission Attempt', {
                employee_name: name,
                employee_email: email,
                role_id: roleId,
                has_new_role: !!document.getElementById('new_role').value.trim()
            });

            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';
            }
        });
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

    // AJAX error handler with security logging
    $(document).ajaxError(function(event, xhr, settings, thrownError) {
        logSecurityEvent('AJAX Error in Add Employee', {
            url: settings.url,
            method: settings.type,
            error: thrownError,
            status: xhr.status
        });
    });

    // Initialize security on page load
    logSecurityEvent('Add Employee Page Loaded', {
        max_operations: <?php echo $max_employee_operations; ?>,
        max_name_length: <?php echo $max_name_length; ?>,
        max_email_length: <?php echo $max_email_length; ?>,
        max_role_name_length: <?php echo $max_role_name_length; ?>
    });
});
</script>
</body>
</html>

