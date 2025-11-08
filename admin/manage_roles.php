<?php
/**
 * Enhanced Security Role Management Interface
 * Provides secure role management with comprehensive security measures
 * Security Enhanced Version
 */

// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/manage_roles_security.log');
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

// Rate limiting for role operations
$max_role_operations = 25; // operations per hour
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
    logSecurityEvent('Manage Roles Session Timeout', [
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
$rate_limit_key = 'manage_roles_operations_' . md5($ip_address);
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
    if ($rate_limit_data['operations'] > $max_role_operations) {
        logSecurityEvent('Manage Roles Operations Rate Limit Exceeded', [
            'ip_address' => $ip_address,
            'operations' => $rate_limit_data['operations'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'Too many role operations. Please slow down.',
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
        $logFile = $logDir . '/manage_roles_security.log';
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
        logSecurityEvent('Suspicious User Agent in Manage Roles', [
            'user_agent' => $user_agent,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        return false;
    }

    return true;
}

// Validate database connection file
$db_connection_file = __DIR__ . '/../includes/db_config.php';
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

// Get database connection with validation
$con = getDbConnection();
if (!$con) {
    logSecurityEvent('Database Connection Failed', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'error' => 'Unable to establish database connection'
    ]);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error.',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

// Check if user has admin privileges
if (!isset($_SESSION['auser'])) {
    logSecurityEvent('Unauthorized Access Attempt to Manage Roles', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    header('Location: /admin/index.php');
    exit();
}

// Validate request headers
if (!validateRequestHeaders()) {
    logSecurityEvent('Invalid Request Headers in Manage Roles', [
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
logSecurityEvent('Manage Roles Page Accessed', [
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
        case 'role_name':
            // Allow letters, numbers, spaces, hyphens, and underscores
            if (!preg_match('/^[A-Za-z0-9\s\-_()]+$/', $input)) {
                return false;
            }
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
        case 'role_description':
            // Allow letters, numbers, spaces, hyphens, underscores, and basic punctuation
            if (!preg_match('/^[A-Za-z0-9\s\-_().,!]+$/', $input)) {
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
$max_role_name_length = 50;
$max_description_length = 255;
$roles = [];

// Handle new role creation with enhanced security
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role_name'])) {
    try {
        // CSRF token validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            logSecurityEvent('CSRF Token Mismatch in Manage Roles', [
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'provided_token' => substr($_POST['csrf_token'] ?? '', 0, 8) . '...',
                'expected_token' => substr($_SESSION['csrf_token'], 0, 8) . '...'
            ]);
            throw new Exception('Security validation failed.');
        }

        // Validate role data
        $role_name = validateInput($_POST['role_name'] ?? '', 'role_name', $max_role_name_length);
        $role_desc = validateInput($_POST['role_desc'] ?? '', 'role_description', $max_description_length, false);

        if ($role_name === false) {
            throw new Exception('Role name is required and must contain only letters, numbers, spaces, hyphens, underscores, and parentheses.');
        }

        if ($role_desc === false) {
            throw new Exception('Role description contains invalid characters.');
        }

        // Check if role already exists
        $check_stmt = $con->prepare("SELECT id FROM roles WHERE name = ?");
        if (!$check_stmt) {
            throw new Exception('Failed to prepare role check statement: ' . $con->error);
        }

        $check_stmt->bind_param("s", $role_name);
        if (!$check_stmt->execute()) {
            throw new Exception('Failed to execute role check: ' . $check_stmt->error);
        }

        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            throw new Exception('Role with this name already exists.');
        }

        $check_stmt->close();

        // Insert role with prepared statement
        $stmt = $con->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
        if (!$stmt) {
            throw new Exception('Failed to prepare role insert statement: ' . $con->error);
        }

        $stmt->bind_param("ss", $role_name, $role_desc);

        if (!$stmt->execute()) {
            throw new Exception('Failed to insert role: ' . $stmt->error);
        }

        $new_role_id = $con->insert_id;
        $stmt->close();

        // Log successful role creation
        logSecurityEvent('Role Created Successfully via Manage Roles', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'admin_user' => $_SESSION['auser'],
            'role_name' => $role_name,
            'role_description' => $role_desc,
            'role_id' => $new_role_id
        ]);

        // Log admin activity if function exists
        if (function_exists('log_admin_activity')) {
            log_admin_activity('manage_roles', 'Created new role: ' . $role_name . ' - ' . $role_desc);
        }

        $msg = 'Role added successfully!';
        header("Location: /admin/manage_roles.php?msg=" . urlencode('Role added successfully.'));
        exit();

    } catch (Exception $e) {
        logSecurityEvent('Role Creation Error in Manage Roles', [
            'error_message' => $e->getMessage(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
            'admin_user' => $_SESSION['auser'],
            'role_name' => $_POST['role_name'] ?? 'UNKNOWN',
            'role_description' => $_POST['role_desc'] ?? 'UNKNOWN'
        ]);
        $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}

// Fetch all roles with prepared statement and error handling
try {
    $stmt = $con->prepare("SELECT id, name, description FROM roles ORDER BY name");
    if (!$stmt) {
        throw new Exception('Failed to prepare roles query: ' . $con->error);
    }

    if (!$stmt->execute()) {
        throw new Exception('Failed to execute roles query: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    logSecurityEvent('Error Fetching Roles in Manage Roles', [
        'error_message' => $e->getMessage(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'admin_user' => $_SESSION['auser']
    ]);
    $roles = [];
}

// Close database connection
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Roles - Secure</title>
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
        .table { background: white; }
        .table th { background-color: #f8f9fa; border-top: none; }
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
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">Role Management</h2>
                        <div>
                            <a href="/admin/add_role.php" class="btn btn-success me-2">
                                <i class="fas fa-plus me-2"></i>Add Single Role
                            </a>
                            <a href="/admin/roles.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Roles
                            </a>
                        </div>
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
                                Rate limit: <?php echo $max_role_operations - $rate_limit_data['operations']; ?>/<?php echo $max_role_operations; ?> remaining
                            </small>
                        </div>
                    </div>

                    <?php if ($msg): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $msg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Add Role Form -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Create New Role</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" class="needs-validation" novalidate>
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="role_name" name="role_name"
                                                   placeholder="Role Name" required maxlength="<?php echo $max_role_name_length; ?>"
                                                   value="<?php echo htmlspecialchars($_POST['role_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                   pattern="[A-Za-z0-9\s\-_()]+" title="Only letters, numbers, spaces, hyphens, underscores, and parentheses allowed">
                                            <label for="role_name"><i class="fa fa-user-tag"></i> Role Name *</label>
                                            <div class="invalid-feedback">Please enter a valid role name (letters, numbers, spaces, hyphens, underscores, parentheses only).</div>
                                            <div class="form-text">Enter a unique name for the role</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="role_desc" name="role_desc"
                                                   placeholder="Description" maxlength="<?php echo $max_description_length; ?>"
                                                   value="<?php echo htmlspecialchars($_POST['role_desc'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                   pattern="[A-Za-z0-9\s\-_().,!]*" title="Letters, numbers, spaces, and basic punctuation only">
                                            <label for="role_desc"><i class="fa fa-align-left"></i> Description</label>
                                            <div class="invalid-feedback">Please enter a valid description.</div>
                                            <div class="form-text">Brief description of the role's responsibilities</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fa fa-plus me-2"></i>Create Role
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Roles Table -->
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Existing Roles</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($roles)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>No roles found. Create your first role above.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th><i class="fas fa-hashtag me-2"></i>ID</th>
                                                <th><i class="fas fa-tag me-2"></i>Role Name</th>
                                                <th><i class="fas fa-align-left me-2"></i>Description</th>
                                                <th><i class="fas fa-calendar me-2"></i>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($roles as $role): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($role['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($role['description'] ?? 'No description', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <small class="text-muted">System Generated</small>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Security Information Card -->
                    <div class="card mt-3 border-info">
                        <div class="card-body">
                            <h6 class="card-title text-info">
                                <i class="fas fa-shield-alt me-2"></i>Security Features
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
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
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small">
                                        <div class="mb-1">
                                            <i class="fas fa-check text-success me-2"></i>Rate limiting (<?php echo $max_role_operations; ?> operations/hour)
                                        </div>
                                        <div class="mb-1">
                                            <i class="fas fa-check text-success me-2"></i>Comprehensive audit logging
                                        </div>
                                        <div class="mb-1">
                                            <i class="fas fa-check text-success me-2"></i>Request header validation
                                        </div>
                                        <div class="mb-1">
                                            <i class="fas fa-check text-success me-2"></i>Role uniqueness validation
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    const roleNameInput = document.getElementById('role_name');
    const roleDescInput = document.getElementById('role_desc');
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

    // Real-time validation for role name
    if (roleNameInput) {
        roleNameInput.addEventListener('input', function() {
            const value = this.value;
            const isValid = /^[A-Za-z0-9\s\-_()]+$/.test(value);

            if (value.length > 0 && !isValid) {
                this.setCustomValidity('Only letters, numbers, spaces, hyphens, underscores, and parentheses are allowed');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Real-time validation for description
    if (roleDescInput) {
        roleDescInput.addEventListener('input', function() {
            const value = this.value;
            const isValid = /^[A-Za-z0-9\s\-_().,!]*$/.test(value);

            if (value.length > 0 && !isValid) {
                this.setCustomValidity('Description contains invalid characters');
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
            const roleName = roleNameInput ? roleNameInput.value.trim() : '';
            const roleDesc = roleDescInput ? roleDescInput.value.trim() : '';

            if (!roleName) {
                event.preventDefault();
                alert('Please enter a role name');
                return false;
            }

            // Log form submission attempt
            logSecurityEvent('Manage Roles Form Submission Attempt', {
                role_name: roleName,
                role_description: roleDesc
            });

            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';
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
        logSecurityEvent('AJAX Error in Manage Roles', {
            url: settings.url,
            method: settings.type,
            error: thrownError,
            status: xhr.status
        });
    });

    // Initialize security on page load
    logSecurityEvent('Manage Roles Page Loaded', {
        max_operations: <?php echo $max_role_operations; ?>,
        max_role_name_length: <?php echo $max_role_name_length; ?>,
        max_description_length: <?php echo $max_description_length; ?>,
        total_roles: <?php echo count($roles); ?>
    });
});
</script>
</body>
</html>
