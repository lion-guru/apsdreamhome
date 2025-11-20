<?php
require_once __DIR__ . '/includes/config/config.php';
global $con;
/**
 * Enhanced Security Property Management Interface
 * Provides secure property management with comprehensive security measures
 * Security Enhanced Version
 */

// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/properties_security.log');
error_reporting(E_ALL);

// Set comprehensive security headers for admin panel
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.datatables.net \'unsafe-inline\'; style-src \'self\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.datatables.net \'unsafe-inline\'; img-src \'self\' data: https:;');
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

// Rate limiting for property operations
$max_property_operations = 30; // operations per hour
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
    logSecurityEvent('Properties Session Timeout', [
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
$rate_limit_key = 'properties_operations_' . md5($ip_address);
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
    if ($rate_limit_data['operations'] > $max_property_operations) {
        logSecurityEvent('Properties Operations Rate Limit Exceeded', [
            'ip_address' => $ip_address,
            'operations' => $rate_limit_data['operations'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'Too many property operations. Please slow down.',
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
        $logFile = $logDir . '/properties_security.log';
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
        logSecurityEvent('Suspicious User Agent in Properties', [
            'user_agent' => $user_agent,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        return false;
    }

    return true;
}

// Validate database connection file
$db_connection_file = __DIR__ . '/includes/db_config.php';
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
// require_once $db_connection_file;
// Get database connection with validation
$con = $con;
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
    logSecurityEvent('Unauthorized Access Attempt to Properties', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    header('Location: /admin/index.php');
    exit();
}

// Validate request headers
if (!validateRequestHeaders()) {
    logSecurityEvent('Invalid Request Headers in Properties', [
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
logSecurityEvent('Properties Page Accessed', [
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
        case 'property_search':
            // Allow letters, numbers, spaces, hyphens, underscores, and commas
            if (!preg_match('/^[A-Za-z0-9\s\-_,()]+$/', $input)) {
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
$search_term = '';
$properties = [];
$property_types = [];
$stats = [];

// Handle search functionality
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = validateInput($_GET['search'], 'property_search', 100);

    if ($search_term !== false) {
        try {
            // Search properties with prepared statement
            $search_stmt = $con->prepare("SELECT p.*, pt.type_name FROM properties p
                                        LEFT JOIN property_types pt ON p.property_type_id = pt.id
                                        WHERE p.property_name LIKE ? OR p.location LIKE ? OR pt.type_name LIKE ?
                                        ORDER BY p.created_at DESC");
            if ($search_stmt) {
                $search_param = '%' . $search_term . '%';
                $search_stmt->bind_param("sss", $search_param, $search_param, $search_param);

                if ($search_stmt->execute()) {
                    $search_result = $search_stmt->get_result();
                    while ($row = $search_result->fetch_assoc()) {
                        $properties[] = $row;
                    }
                }
                $search_stmt->close();
            }

            logSecurityEvent('Property Search Performed', [
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'admin_user' => $_SESSION['auser'],
                'search_term' => $search_term,
                'results_count' => count($properties)
            ]);
        } catch (Exception $e) {
            logSecurityEvent('Property Search Error', [
                'error_message' => $e->getMessage(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'admin_user' => $_SESSION['auser'],
                'search_term' => $search_term
            ]);
        }
    }
}

// Handle property deletion
if (isset($_POST['delete_property']) && isset($_POST['property_id'])) {
    try {
        // CSRF token validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            logSecurityEvent('CSRF Token Mismatch in Property Deletion', [
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'provided_token' => substr($_POST['csrf_token'] ?? '', 0, 8) . '...',
                'expected_token' => substr($_SESSION['csrf_token'], 0, 8) . '...'
            ]);
            throw new Exception('Security validation failed.');
        }

        $property_id = validateInput($_POST['property_id'], 'integer');

        if ($property_id === false || $property_id <= 0) {
            throw new Exception('Invalid property ID.');
        }

        // Check if property exists and get details for logging
        $check_stmt = $con->prepare("SELECT property_name FROM properties WHERE id = ?");
        if (!$check_stmt) {
            throw new Exception('Failed to prepare property check statement: ' . $con->error);
        }

        $check_stmt->bind_param("i", $property_id);
        if (!$check_stmt->execute()) {
            throw new Exception('Failed to execute property check: ' . $check_stmt->error);
        }

        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows === 0) {
            throw new Exception('Property not found.');
        }

        $property_data = $check_result->fetch_assoc();
        $check_stmt->close();

        // Delete property with prepared statement
        $delete_stmt = $con->prepare("DELETE FROM properties WHERE id = ?");
        if (!$delete_stmt) {
            throw new Exception('Failed to prepare property deletion statement: ' . $con->error);
        }

        $delete_stmt->bind_param("i", $property_id);

        if (!$delete_stmt->execute()) {
            throw new Exception('Failed to delete property: ' . $delete_stmt->error);
        }

        $delete_stmt->close();

        // Log successful property deletion
        logSecurityEvent('Property Deleted Successfully', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'admin_user' => $_SESSION['auser'],
            'property_id' => $property_id,
            'property_name' => $property_data['property_name']
        ]);

        $msg = 'Property deleted successfully!';
        header("Location: /admin/properties.php?msg=" . urlencode('Property deleted successfully.'));
        exit();

    } catch (Exception $e) {
        logSecurityEvent('Property Deletion Error', [
            'error_message' => $e->getMessage(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
            'admin_user' => $_SESSION['auser'],
            'property_id' => $_POST['property_id'] ?? 'UNKNOWN'
        ]);
        $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}

// Fetch all properties with prepared statement and error handling
try {
    $stmt = $con->prepare("SELECT p.*, pt.type_name FROM properties p
                          LEFT JOIN property_types pt ON p.property_type_id = pt.id
                          ORDER BY p.created_at DESC");
    if (!$stmt) {
        throw new Exception('Failed to prepare properties query: ' . $con->error);
    }

    if (!$stmt->execute()) {
        throw new Exception('Failed to execute properties query: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    logSecurityEvent('Error Fetching Properties', [
        'error_message' => $e->getMessage(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'admin_user' => $_SESSION['auser']
    ]);
    $properties = [];
}

// Fetch property types for filter dropdown
try {
    $type_stmt = $con->prepare("SELECT id, type_name FROM property_types ORDER BY type_name");
    if ($type_stmt && $type_stmt->execute()) {
        $type_result = $type_stmt->get_result();
        while ($row = $type_result->fetch_assoc()) {
            $property_types[] = $row;
        }
        $type_stmt->close();
    }
} catch (Exception $e) {
    logSecurityEvent('Error Fetching Property Types', [
        'error_message' => $e->getMessage(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
    ]);
}

// Fetch property statistics
try {
    $stats_stmt = $con->prepare("SELECT
                                COUNT(*) as total_properties,
                                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_properties,
                                SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold_properties,
                                SUM(CASE WHEN status = 'rented' THEN 1 ELSE 0 END) as rented_properties
                                FROM properties");
    if ($stats_stmt && $stats_stmt->execute()) {
        $stats_result = $stats_stmt->get_result();
        $stats = $stats_result->fetch_assoc();
        $stats_stmt->close();
    }
} catch (Exception $e) {
    logSecurityEvent('Error Fetching Property Statistics', [
        'error_message' => $e->getMessage(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
    ]);
    $stats = ['total_properties' => 0, 'available_properties' => 0, 'sold_properties' => 0, 'rented_properties' => 0];
}

// Close database connection
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Properties Management - Secure</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
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
        .stats-card { transition: transform 0.2s; }
        .stats-card:hover { transform: translateY(-2px); }
        .property-image { width: 40px; height: 40px; object-fit: cover; border-radius: 4px; }
    </style>
</head>
<body>
<?php
// Validate and include header
$header_file = __DIR__ . '/includes/new_header.php';
if (file_exists($header_file) && is_readable($header_file)) {
    include $header_file;
} else {
    logSecurityEvent('Header File Missing', ['file_path' => $header_file]);
    echo '<!-- Header not available -->';
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">Properties Management</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Properties</li>
            </ul>
        </div>
        <div class="col-auto">
            <a href="add_property.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Property
            </a>
        </div>
    </div>
</div>
<!-- /Page Header -->

<div class="row">
    <!-- Property Statistics -->
    <div class="col-md-12 mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card stats-card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-building fa-2x text-primary mb-2"></i>
                        <h4 class="text-primary"><?php echo number_format($stats['total_properties']); ?></h4>
                        <p class="text-muted mb-0">Total Properties</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h4 class="text-success"><?php echo number_format($stats['available_properties']); ?></h4>
                        <p class="text-muted mb-0">Available</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card border-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-sold fa-2x text-warning mb-2"></i>
                        <h4 class="text-warning"><?php echo number_format($stats['sold_properties']); ?></h4>
                        <p class="text-muted mb-0">Sold</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-home fa-2x text-info mb-2"></i>
                        <h4 class="text-info"><?php echo number_format($stats['rented_properties']); ?></h4>
                        <p class="text-muted mb-0">Rented</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Status Bar -->
    <div class="col-md-12 mb-4">
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-shield-alt me-2"></i>
                <strong>Security Status:</strong> Protected
                <span class="badge bg-success ms-2">Active</span>
            </div>
            <div class="text-end">
                <small class="text-muted">
                    Session expires: <?php echo date('H:i:s', time() + 1800); ?><br>
                    Rate limit: <?php echo $max_property_operations - $rate_limit_data['operations']; ?>/<?php echo $max_property_operations; ?> remaining
                </small>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($msg): ?>
        <div class="col-md-12 mb-4">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Search and Filter -->
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search"
                                   placeholder="Search properties by name, location, or type..."
                                   value="<?php echo htmlspecialchars($search_term, ENT_QUOTES, 'UTF-8'); ?>">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <a href="/admin/properties.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-eraser me-2"></i>Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Properties Table -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-center mb-0" id="propertiesTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Property</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($properties)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No Properties Found</h5>
                                        <p class="text-muted">
                                            <?php if ($search_term): ?>
                                                No properties match your search criteria.
                                            <?php else: ?>
                                                No properties have been added yet.
                                            <?php endif; ?>
                                        </p>
                                        <a href="add_property.php" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>Add First Property
                                        </a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($properties as $index => $property): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <h2 class="table-avatar">
                                                <a href="property-details.php?id=<?php echo htmlspecialchars($property['id'], ENT_QUOTES, 'UTF-8'); ?>" class="avatar avatar-sm me-2">
                                                    <img class="avatar-img rounded" src="../assets/img/property/<?php echo htmlspecialchars($property['image'] ?? 'default.jpg', ENT_QUOTES, 'UTF-8'); ?>" alt="Property Image" onerror="this.src='../assets/img/property/default.jpg'">
                                                </a>
                                                <a href="property-details.php?id=<?php echo htmlspecialchars($property['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php echo htmlspecialchars($property['property_name'] ?? 'Unnamed Property', ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            </h2>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo htmlspecialchars($property['type_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($property['location'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php if (isset($property['price'])): ?>
                                                <span class="text-primary fw-bold">
                                                    $<?php echo number_format($property['price']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Price not set</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status = $property['status'] ?? 'unknown';
                                            $status_class = 'bg-secondary';
                                            $status_icon = 'fas fa-question';

                                            switch ($status) {
                                                case 'available':
                                                    $status_class = 'bg-success';
                                                    $status_icon = 'fas fa-check-circle';
                                                    break;
                                                case 'sold':
                                                    $status_class = 'bg-danger';
                                                    $status_icon = 'fas fa-times-circle';
                                                    break;
                                                case 'rented':
                                                    $status_class = 'bg-warning';
                                                    $status_icon = 'fas fa-home';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>">
                                                <i class="<?php echo $status_icon; ?> me-1"></i>
                                                <?php echo ucfirst(htmlspecialchars($status, ENT_QUOTES, 'UTF-8')); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="actions">
                                                <a class="btn btn-sm bg-success-light me-2" href="edit_property.php?id=<?php echo htmlspecialchars($property['id'], ENT_QUOTES, 'UTF-8'); ?>" title="Edit Property">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                <a class="btn btn-sm bg-info-light me-2" href="property-details.php?id=<?php echo htmlspecialchars($property['id'], ENT_QUOTES, 'UTF-8'); ?>" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-sm bg-danger-light" onclick="deleteProperty(<?php echo htmlspecialchars($property['id'], ENT_QUOTES, 'UTF-8'); ?>, '<?php echo htmlspecialchars(addslashes($property['property_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>')" title="Delete Property">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Property</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="deleteMessage">Are you sure you want to delete this property?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="delete_property" value="1">
                    <input type="hidden" name="property_id" id="deletePropertyId" value="">
                    <button type="submit" class="btn btn-danger">Delete Property</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /Delete Modal -->

<?php
// Validate and include footer
$footer_file = __DIR__ . '/includes/new_footer.php';
if (file_exists($footer_file) && is_readable($footer_file)) {
    include $footer_file;
} else {
    logSecurityEvent('Footer File Missing', ['file_path' => $footer_file]);
    echo '<!-- Footer not available -->';
}
?>

<!-- DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

<script>
$(document).ready(function() {
    $('#propertiesTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 25,
        "columnDefs": [{
            "targets": 'no-sort',
            "orderable": false,
        }],
        "language": {
            "search": "Search properties:",
            "lengthMenu": "Show _MENU_ properties per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ properties",
            "infoEmpty": "No properties found",
            "zeroRecords": "No matching properties found"
        }
    });
});

// Delete property function
function deleteProperty(propertyId, propertyName) {
    document.getElementById('deletePropertyId').value = propertyId;
    document.getElementById('deleteMessage').innerHTML = `Are you sure you want to delete the property "<strong>${propertyName}</strong>"? This action cannot be undone.`;
    $('#deleteModal').modal('show');
}

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
            csrf_token: '<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>'
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
logSecurityEvent('Properties Page Loaded', {
    total_properties: <?php echo count($properties); ?>,
    available_properties: <?php echo $stats['available_properties']; ?>,
    sold_properties: <?php echo $stats['sold_properties']; ?>,
    rented_properties: <?php echo $stats['rented_properties']; ?>,
    search_term: '<?php echo htmlspecialchars($search_term); ?>'
});

// AJAX error handler with security logging
$(document).ajaxError(function(event, xhr, settings, thrownError) {
    logSecurityEvent('AJAX Error in Properties', {
        url: settings.url,
        method: settings.type,
        error: thrownError,
        status: xhr.status
    });
});
</script>
</body>
</html>