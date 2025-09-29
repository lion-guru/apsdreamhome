<?php
/**
 * Enhanced Security Booking Management Interface
 * Provides secure booking management with comprehensive security measures
 * Security Enhanced Version
 */

// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/bookings_security.log');
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

// Rate limiting for booking operations
$max_booking_operations = 45; // operations per hour
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
    logSecurityEvent('Bookings Session Timeout', [
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
$rate_limit_key = 'bookings_operations_' . md5($ip_address);
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
    if ($rate_limit_data['operations'] > $max_booking_operations) {
        logSecurityEvent('Bookings Operations Rate Limit Exceeded', [
            'ip_address' => $ip_address,
            'operations' => $rate_limit_data['operations'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'Too many booking operations. Please slow down.',
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
        $logFile = $logDir . '/bookings_security.log';
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
        logSecurityEvent('Suspicious User Agent in Bookings', [
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
$conn = getDbConnection();
if (!$conn) {
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
    logSecurityEvent('Unauthorized Access Attempt to Bookings', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    header('Location: /admin/index.php');
    exit();
}

// Validate request headers
if (!validateRequestHeaders()) {
    logSecurityEvent('Invalid Request Headers in Bookings', [
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
logSecurityEvent('Bookings Page Accessed', [
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
        case 'decimal':
            $input = filter_var($input, FILTER_VALIDATE_FLOAT);
            if ($input === false) {
                return false;
            }
            break;
        case 'date':
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $input) && !preg_match('/^\d{2} [A-Za-z]{3} \d{4}$/', $input)) {
                return false;
            }
            break;
        case 'booking_search':
            // Allow letters, numbers, spaces, hyphens, underscores, commas, and @ for email
            if (!preg_match('/^[A-Za-z0-9\s\-_,@.\(\)]+$/', $input)) {
                return false;
            }
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
        case 'customer_name':
            // Allow letters, spaces, hyphens, apostrophes, and periods
            if (!preg_match('/^[A-Za-z\s\-\'.]+$/', $input)) {
                return false;
            }
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
        case 'property_name':
            // Allow letters, numbers, spaces, hyphens, underscores, and parentheses
            if (!preg_match('/^[A-Za-z0-9\s\-_().,&]+$/', $input)) {
                return false;
            }
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
        case 'booking_status':
            // Allow specific booking status values
            $allowed_statuses = ['Pending', 'Confirmed', 'Cancelled', 'Completed', 'Refunded', 'No Show'];
            if (!in_array($input, $allowed_statuses)) {
                return false;
            }
            break;
        case 'booking_id':
            // Allow alphanumeric characters, hyphens, and underscores
            if (!preg_match('/^[A-Za-z0-9\-_]+$/', $input)) {
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
$bookings = [];
$booking_stats = [];

// Handle search functionality
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = validateInput($_GET['search'], 'booking_search', 100);

    if ($search_term !== false) {
        try {
            // Search bookings with prepared statement
            $search_stmt = $conn->prepare("SELECT b.*, c.first_name, c.last_name, p.property_name
                                        FROM bookings b
                                        LEFT JOIN customers c ON b.customer_id = c.id
                                        LEFT JOIN properties p ON b.property_id = p.id
                                        WHERE b.booking_id LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR p.property_name LIKE ?
                                        ORDER BY b.booking_date DESC");
            if ($search_stmt) {
                $search_param = '%' . $search_term . '%';
                $search_stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);

                if ($search_stmt->execute()) {
                    $search_result = $search_stmt->get_result();
                    while ($row = $search_result->fetch_assoc()) {
                        $bookings[] = $row;
                    }
                }
                $search_stmt->close();
            }

            logSecurityEvent('Booking Search Performed', [
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'admin_user' => $_SESSION['auser'],
                'search_term' => $search_term,
                'results_count' => count($bookings)
            ]);
        } catch (Exception $e) {
            logSecurityEvent('Booking Search Error', [
                'error_message' => $e->getMessage(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'admin_user' => $_SESSION['auser'],
                'search_term' => $search_term
            ]);
        }
    }
}

// Handle booking deletion
if (isset($_POST['delete_booking']) && isset($_POST['booking_id'])) {
    try {
        // CSRF token validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            logSecurityEvent('CSRF Token Mismatch in Booking Deletion', [
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'provided_token' => substr($_POST['csrf_token'] ?? '', 0, 8) . '...',
                'expected_token' => substr($_SESSION['csrf_token'], 0, 8) . '...'
            ]);
            throw new Exception('Security validation failed.');
        }

        $booking_id = validateInput($_POST['booking_id'], 'booking_id', 50);

        if ($booking_id === false) {
            throw new Exception('Invalid booking ID.');
        }

        // Check if booking exists and get details for logging
        $check_stmt = $conn->prepare("SELECT b.booking_id, b.amount, c.first_name, c.last_name, p.property_name
                                    FROM bookings b
                                    LEFT JOIN customers c ON b.customer_id = c.id
                                    LEFT JOIN properties p ON b.property_id = p.id
                                    WHERE b.booking_id = ?");
        if (!$check_stmt) {
            throw new Exception('Failed to prepare booking check statement: ' . $conn->error);
        }

        $check_stmt->bind_param("s", $booking_id);
        if (!$check_stmt->execute()) {
            throw new Exception('Failed to execute booking check: ' . $check_stmt->error);
        }

        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows === 0) {
            throw new Exception('Booking not found.');
        }

        $booking_data = $check_result->fetch_assoc();
        $check_stmt->close();

        // Delete booking with prepared statement
        $delete_stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ?");
        if (!$delete_stmt) {
            throw new Exception('Failed to prepare booking deletion statement: ' . $conn->error);
        }

        $delete_stmt->bind_param("s", $booking_id);

        if (!$delete_stmt->execute()) {
            throw new Exception('Failed to delete booking: ' . $delete_stmt->error);
        }

        $delete_stmt->close();

        // Log successful booking deletion
        logSecurityEvent('Booking Deleted Successfully', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'admin_user' => $_SESSION['auser'],
            'booking_id' => $booking_id,
            'customer_name' => ($booking_data['first_name'] ?? '') . ' ' . ($booking_data['last_name'] ?? ''),
            'property_name' => $booking_data['property_name'] ?? 'Unknown',
            'booking_amount' => $booking_data['amount'] ?? 0
        ]);

        $msg = 'Booking deleted successfully!';
        header("Location: /admin/bookings.php?msg=" . urlencode('Booking deleted successfully.'));
        exit();

    } catch (Exception $e) {
        logSecurityEvent('Booking Deletion Error', [
            'error_message' => $e->getMessage(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
            'admin_user' => $_SESSION['auser'],
            'booking_id' => $_POST['booking_id'] ?? 'UNKNOWN'
        ]);
        $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}

// Fetch all bookings with enhanced security and error handling
try {
    $stmt = $conn->prepare("SELECT b.*, c.first_name, c.last_name, p.property_name
                          FROM bookings b
                          LEFT JOIN customers c ON b.customer_id = c.id
                          LEFT JOIN properties p ON b.property_id = p.id
                          ORDER BY b.booking_date DESC");
    if (!$stmt) {
        throw new Exception('Failed to prepare bookings query: ' . $conn->error);
    }

    if (!$stmt->execute()) {
        throw new Exception('Failed to execute bookings query: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    logSecurityEvent('Error Fetching Bookings', [
        'error_message' => $e->getMessage(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'admin_user' => $_SESSION['auser']
    ]);
    $bookings = [];
}

// Fetch booking statistics
try {
    $stats_stmt = $conn->prepare("SELECT
                                COUNT(*) as total_bookings,
                                SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_bookings,
                                SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_bookings,
                                SUM(amount) as total_revenue
                                FROM bookings");
    if ($stats_stmt && $stats_stmt->execute()) {
        $stats_result = $stats_stmt->get_result();
        $booking_stats = $stats_result->fetch_assoc();
        $stats_stmt->close();
    } else {
        $booking_stats = ['total_bookings' => 0, 'confirmed_bookings' => 0, 'pending_bookings' => 0, 'cancelled_bookings' => 0, 'completed_bookings' => 0, 'total_revenue' => 0];
    }
} catch (Exception $e) {
    logSecurityEvent('Error Fetching Booking Statistics', [
        'error_message' => $e->getMessage(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
    ]);
    $booking_stats = ['total_bookings' => 0, 'confirmed_bookings' => 0, 'pending_bookings' => 0, 'cancelled_bookings' => 0, 'completed_bookings' => 0, 'total_revenue' => 0];
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="robots" content="noindex, nofollow">
    <title>Booking Management - Secure</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <style>
        body { background: #f8f9fa; }
        .main-content { margin-left: 220px; padding: 2rem 1rem; }
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
        .customer-avatar { width: 40px; height: 40px; object-fit: cover; border-radius: 50%; }
        .revenue-highlight { font-size: 1.2em; font-weight: bold; color: #28a745; }
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
            <h3 class="page-title">Booking Management</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Bookings</li>
            </ul>
        </div>
        <div class="col-auto">
            <a href="add_booking.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Booking
            </a>
        </div>
    </div>
</div>
<!-- /Page Header -->

<div class="row">
    <!-- Booking Statistics -->
    <div class="col-md-12 mb-4">
        <div class="row">
            <div class="col-md-2">
                <div class="card stats-card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-check fa-2x text-primary mb-2"></i>
                        <h4 class="text-primary"><?php echo number_format($booking_stats['total_bookings'] ?? 0); ?></h4>
                        <p class="text-muted mb-0">Total Bookings</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h4 class="text-success"><?php echo number_format($booking_stats['confirmed_bookings'] ?? 0); ?></h4>
                        <p class="text-muted mb-0">Confirmed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card border-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                        <h4 class="text-warning"><?php echo number_format($booking_stats['pending_bookings'] ?? 0); ?></h4>
                        <p class="text-muted mb-0">Pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-day fa-2x text-info mb-2"></i>
                        <h4 class="text-info"><?php echo number_format($booking_stats['completed_bookings'] ?? 0); ?></h4>
                        <p class="text-muted mb-0">Completed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card border-danger">
                    <div class="card-body text-center">
                        <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                        <h4 class="text-danger"><?php echo number_format($booking_stats['cancelled_bookings'] ?? 0); ?></h4>
                        <p class="text-muted mb-0">Cancelled</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                        <h4 class="revenue-highlight">$<?php echo number_format($booking_stats['total_revenue'] ?? 0); ?></h4>
                        <p class="text-muted mb-0">Total Revenue</p>
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
                    Rate limit: <?php echo $max_booking_operations - $rate_limit_data['operations']; ?>/<?php echo $max_booking_operations; ?> remaining
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
                                   placeholder="Search bookings by ID, customer name, or property..."
                                   value="<?php echo htmlspecialchars($search_term, ENT_QUOTES, 'UTF-8'); ?>">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <a href="/admin/bookings.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-eraser me-2"></i>Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-center mb-0" id="bookingsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Property</th>
                                <th>Booking Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No Bookings Found</h5>
                                        <p class="text-muted">
                                            <?php if ($search_term): ?>
                                                No bookings match your search criteria.
                                            <?php else: ?>
                                                No bookings have been created yet.
                                            <?php endif; ?>
                                        </p>
                                        <a href="add_booking.php" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>Create First Booking
                                        </a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $index => $booking): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <strong class="text-primary">
                                                <?php echo htmlspecialchars($booking['booking_id'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <h2 class="table-avatar">
                                                <a href="customer-profile.php?id=<?php echo htmlspecialchars($booking['customer_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="avatar avatar-sm me-2">
                                                    <img class="avatar-img rounded-circle" src="../assets/img/profiles/<?php echo htmlspecialchars($booking['profile_image'] ?? 'default-avatar.jpg', ENT_QUOTES, 'UTF-8'); ?>" alt="Customer Image" onerror="this.src='../assets/img/profiles/default-avatar.jpg'">
                                                </a>
                                                <a href="customer-profile.php?id=<?php echo htmlspecialchars($booking['customer_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php echo htmlspecialchars(($booking['first_name'] ?? '') . ' ' . ($booking['last_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            </h2>
                                        </td>
                                        <td>
                                            <a href="property-details.php?id=<?php echo htmlspecialchars($booking['property_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($booking['property_name'] ?? 'Unknown Property', ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                            $booking_date = $booking['booking_date'] ?? '';
                                            if (!empty($booking_date) && $booking_date !== '0000-00-00 00:00:00') {
                                                echo htmlspecialchars(date('d M Y', strtotime($booking_date)), ENT_QUOTES, 'UTF-8');
                                            } else {
                                                echo '<span class="text-muted">Not set</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="text-success fw-bold">
                                                $<?php echo number_format($booking['amount'] ?? 0); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $status = $booking['status'] ?? 'Pending';
                                            $status_class = 'bg-secondary';
                                            $status_icon = 'fas fa-question';

                                            switch ($status) {
                                                case 'Confirmed':
                                                    $status_class = 'bg-success';
                                                    $status_icon = 'fas fa-check-circle';
                                                    break;
                                                case 'Pending':
                                                    $status_class = 'bg-warning';
                                                    $status_icon = 'fas fa-clock';
                                                    break;
                                                case 'Cancelled':
                                                    $status_class = 'bg-danger';
                                                    $status_icon = 'fas fa-times-circle';
                                                    break;
                                                case 'Completed':
                                                    $status_class = 'bg-info';
                                                    $status_icon = 'fas fa-check-double';
                                                    break;
                                                case 'Refunded':
                                                    $status_class = 'bg-secondary';
                                                    $status_icon = 'fas fa-undo';
                                                    break;
                                                case 'No Show':
                                                    $status_class = 'bg-dark';
                                                    $status_icon = 'fas fa-user-slash';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>">
                                                <i class="<?php echo $status_icon; ?> me-1"></i>
                                                <?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="actions">
                                                <a class="btn btn-sm bg-primary-light me-2" href="view_booking.php?id=<?php echo htmlspecialchars($booking['booking_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" title="View Booking">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a class="btn btn-sm bg-success-light me-2" href="edit_booking.php?id=<?php echo htmlspecialchars($booking['booking_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" title="Edit Booking">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                <button class="btn btn-sm bg-danger-light" onclick="deleteBooking('<?php echo htmlspecialchars($booking['booking_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars(addslashes(($booking['first_name'] ?? '') . ' ' . ($booking['last_name'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>')" title="Delete Booking">
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
                <h5 class="modal-title">Delete Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="deleteMessage">Are you sure you want to delete this booking?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="delete_booking" value="1">
                    <input type="hidden" name="booking_id" id="deleteBookingId" value="">
                    <button type="submit" class="btn btn-danger">Delete Booking</button>
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
    $('#bookingsTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 25,
        "columnDefs": [{
            "targets": 'no-sort',
            "orderable": false,
        }],
        "language": {
            "search": "Search bookings:",
            "lengthMenu": "Show _MENU_ bookings per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ bookings",
            "infoEmpty": "No bookings found",
            "zeroRecords": "No matching bookings found"
        }
    });
});

// Delete booking function
function deleteBooking(bookingId, customerName) {
    document.getElementById('deleteBookingId').value = bookingId;
    document.getElementById('deleteMessage').innerHTML = `Are you sure you want to delete the booking for "<strong>${customerName}</strong>"? This action cannot be undone and will permanently remove the booking record.`;
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
logSecurityEvent('Bookings Page Loaded', {
    total_bookings: <?php echo $booking_stats['total_bookings'] ?? 0; ?>,
    confirmed_bookings: <?php echo $booking_stats['confirmed_bookings'] ?? 0; ?>,
    pending_bookings: <?php echo $booking_stats['pending_bookings'] ?? 0; ?>,
    cancelled_bookings: <?php echo $booking_stats['cancelled_bookings'] ?? 0; ?>,
    completed_bookings: <?php echo $booking_stats['completed_bookings'] ?? 0; ?>,
    total_revenue: <?php echo $booking_stats['total_revenue'] ?? 0; ?>,
    search_term: '<?php echo htmlspecialchars($search_term); ?>'
});

// AJAX error handler with security logging
$(document).ajaxError(function(event, xhr, settings, thrownError) {
    logSecurityEvent('AJAX Error in Bookings', {
        url: settings.url,
        method: settings.type,
        error: thrownError,
        status: xhr.status
    });
});
</script>
</body>
</html>
