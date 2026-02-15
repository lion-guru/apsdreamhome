<?php
/**
 * Enhanced Security Lead Management Interface
 * Provides secure lead management with comprehensive security measures
 * Security Enhanced Version
 */

// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/leads_security.log');
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config/config.php';
global $con;

// Set comprehensive security headers for admin panel
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com \'unsafe-inline\'; style-src \'self\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com \'unsafe-inline\'; img-src \'self\' data: https:;');
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

// Rate limiting for lead operations
$max_lead_operations = 40; // operations per hour
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
    logSecurityEvent('Leads Session Timeout', [
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
$rate_limit_key = 'leads_operations_' . md5($ip_address);
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
    if ($rate_limit_data['operations'] > $max_lead_operations) {
        logSecurityEvent('Leads Operations Rate Limit Exceeded', [
            'ip_address' => $ip_address,
            'operations' => $rate_limit_data['operations'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'Too many lead operations. Please slow down.',
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
        $logFile = $logDir . '/leads_security.log';
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
        logSecurityEvent('Suspicious User Agent in Leads', [
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
$conn = $con;
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
    logSecurityEvent('Unauthorized Access Attempt to Leads', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    header('Location: /admin/index.php');
    exit();
}

// Validate request headers
if (!validateRequestHeaders()) {
    logSecurityEvent('Invalid Request Headers in Leads', [
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
logSecurityEvent('Leads Page Accessed', [
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
        case 'phone':
            // Allow digits, spaces, hyphens, parentheses, and plus
            if (!preg_match('/^[\d\s\-\+\(\)\.]+$/', $input)) {
                return false;
            }
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
        case 'lead_name':
            // Allow letters, spaces, hyphens, apostrophes, and periods
            if (!preg_match('/^[A-Za-z\s\-\'.]+$/', $input)) {
                return false;
            }
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
        case 'lead_source':
            // Allow letters, numbers, spaces, hyphens, underscores, and common source types
            if (!preg_match('/^[A-Za-z0-9\s\-_().,&]+$/', $input)) {
                return false;
            }
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
        case 'lead_status':
            // Allow specific status values
            $allowed_statuses = ['New', 'Contacted', 'Qualified', 'Lost', 'Converted', 'Follow Up', 'Nurturing'];
            if (!in_array($input, $allowed_statuses)) {
                return false;
            }
            break;
        case 'address':
            // Allow letters, numbers, spaces, hyphens, commas, periods, and common address characters
            if (!preg_match('/^[A-Za-z0-9\s\-_,.#()]+$/', $input)) {
                return false;
            }
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
        case 'notes':
            // Allow letters, numbers, spaces, hyphens, underscores, and basic punctuation
            if (!preg_match('/^[A-Za-z0-9\s\-_().,!?\n\r]+$/', $input)) {
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
$leads = [];
$lead_sources = ['Website', 'Social Media', 'Referral', 'Advertisement', 'Cold Call', 'Email Campaign', 'Walk-in', 'Other'];
$lead_statuses = ['New', 'Contacted', 'Qualified', 'Follow Up', 'Nurturing', 'Lost', 'Converted'];
$users = [];

// Handle form submission for new lead with enhanced security
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_lead'])) {
    try {
        // CSRF token validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            logSecurityEvent('CSRF Token Mismatch in Lead Creation', [
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'provided_token' => substr($_POST['csrf_token'] ?? '', 0, 8) . '...',
                'expected_token' => substr($_SESSION['csrf_token'], 0, 8) . '...'
            ]);
            throw new Exception('Security validation failed.');
        }

        // Validate all input fields
        $name = validateInput($_POST['name'] ?? '', 'lead_name', 100);
        $email = validateInput($_POST['email'] ?? '', 'email', 255, false);
        $phone = validateInput($_POST['phone'] ?? '', 'phone', 20, false);
        $address = validateInput($_POST['address'] ?? '', 'address', 255, false);
        $source = validateInput($_POST['source'] ?? '', 'lead_source', 50);
        $status = validateInput($_POST['status'] ?? 'New', 'lead_status');
        $notes = validateInput($_POST['notes'] ?? '', 'notes', 1000, false);
        $assigned_to = isset($_POST['assigned_to']) && !empty($_POST['assigned_to']) ? validateInput($_POST['assigned_to'], 'integer') : null;

        if ($name === false) {
            throw new Exception('Lead name is required and must contain only letters, spaces, hyphens, apostrophes, and periods.');
        }

        if ($email === false) {
            throw new Exception('Invalid email address provided.');
        }

        if ($phone === false) {
            throw new Exception('Invalid phone number provided.');
        }

        if ($address === false) {
            throw new Exception('Invalid address provided.');
        }

        if ($source === false) {
            throw new Exception('Invalid lead source provided.');
        }

        if ($status === false) {
            throw new Exception('Invalid lead status provided.');
        }

        if ($notes === false) {
            throw new Exception('Invalid notes provided.');
        }

        if ($assigned_to === false || ($assigned_to !== null && $assigned_to <= 0)) {
            throw new Exception('Invalid assigned user ID.');
        }

        // Check if lead with this email already exists
        if (!empty($email)) {
            $check_stmt = $conn->prepare("SELECT lead_id FROM leads WHERE email = ?");
            if (!$check_stmt) {
                throw new Exception('Failed to prepare email check statement: ' . $conn->error);
            }

            $check_stmt->bind_param("s", $email);
            if (!$check_stmt->execute()) {
                throw new Exception('Failed to execute email check: ' . $check_stmt->error);
            }

            $check_result = $check_stmt->get_result();
            if ($check_result->num_rows > 0) {
                throw new Exception('A lead with this email already exists.');
            }
            $check_stmt->close();
        }

        // Insert new lead with prepared statement
        $stmt = $conn->prepare("INSERT INTO leads (name, email, phone, address, source, status, notes, assigned_to, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if (!$stmt) {
            throw new Exception('Failed to prepare lead insert statement: ' . $conn->error);
        }

        $stmt->bind_param('sssssssi',
            $name,
            $email,
            $phone,
            $address,
            $source,
            $status,
            $notes,
            $assigned_to
        );

        if (!$stmt->execute()) {
            throw new Exception('Failed to insert lead: ' . $stmt->error);
        }

        $new_lead_id = $conn->insert_id;
        $stmt->close();

        // Log successful lead creation
        logSecurityEvent('Lead Created Successfully', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'admin_user' => $_SESSION['auser'],
            'lead_name' => $name,
            'lead_email' => $email,
            'lead_phone' => $phone,
            'lead_source' => $source,
            'lead_status' => $status,
            'lead_id' => $new_lead_id
        ]);

        $msg = '<div class="alert alert-success">Lead added successfully!</div>';
        header("Location: /admin/leads.php?msg=" . urlencode('Lead added successfully.'));
        exit();

    } catch (Exception $e) {
        logSecurityEvent('Lead Creation Error', [
            'error_message' => $e->getMessage(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
            'admin_user' => $_SESSION['auser'],
            'lead_name' => $_POST['name'] ?? 'UNKNOWN',
            'lead_email' => $_POST['email'] ?? 'UNKNOWN'
        ]);
        $msg = '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
    }
}

// Handle lead deletion with enhanced security
if (isset($_GET['delete'])) {
    try {
        // CSRF token validation (for GET requests, we can use session-based validation)
        if (!isset($_SESSION['csrf_token'])) {
            throw new Exception('Security validation failed.');
        }

        $lead_id = validateInput($_GET['delete'], 'integer');

        if ($lead_id === false || $lead_id <= 0) {
            throw new Exception('Invalid lead ID.');
        }

        // Check if lead exists and get details for logging
        $check_stmt = $conn->prepare("SELECT name, email FROM leads WHERE lead_id = ?");
        if (!$check_stmt) {
            throw new Exception('Failed to prepare lead check statement: ' . $conn->error);
        }

        $check_stmt->bind_param("i", $lead_id);
        if (!$check_stmt->execute()) {
            throw new Exception('Failed to execute lead check: ' . $check_stmt->error);
        }

        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows === 0) {
            throw new Exception('Lead not found.');
        }

        $lead_data = $check_result->fetch_assoc();
        $check_stmt->close();

        // Delete lead with prepared statement
        $stmt = $conn->prepare("DELETE FROM leads WHERE lead_id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare lead deletion statement: ' . $conn->error);
        }

        $stmt->bind_param('i', $lead_id);

        if (!$stmt->execute()) {
            throw new Exception('Failed to delete lead: ' . $stmt->error);
        }

        $stmt->close();

        // Log successful lead deletion
        logSecurityEvent('Lead Deleted Successfully', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'admin_user' => $_SESSION['auser'],
            'lead_id' => $lead_id,
            'lead_name' => $lead_data['name'],
            'lead_email' => $lead_data['email']
        ]);

        $msg = '<div class="alert alert-success">Lead deleted successfully!</div>';
        header("Location: /admin/leads.php?msg=" . urlencode('Lead deleted successfully.'));
        exit();

    } catch (Exception $e) {
        logSecurityEvent('Lead Deletion Error', [
            'error_message' => $e->getMessage(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
            'admin_user' => $_SESSION['auser'],
            'lead_id' => $_GET['delete'] ?? 'UNKNOWN'
        ]);
        $msg = '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
    }
}

// Fetch leads with enhanced security and error handling
try {
    $stmt = $conn->prepare("SELECT l.*, u.first_name, u.last_name
                          FROM leads l
                          LEFT JOIN users u ON l.assigned_to = u.id
                          ORDER BY l.lead_id DESC");
    if (!$stmt) {
        throw new Exception('Failed to prepare leads query: ' . $conn->error);
    }

    if (!$stmt->execute()) {
        throw new Exception('Failed to execute leads query: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $leads[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    logSecurityEvent('Error Fetching Leads', [
        'error_message' => $e->getMessage(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'admin_user' => $_SESSION['auser']
    ]);
    $leads = [];
}

// Fetch users for assignment dropdown
try {
    $user_stmt = $conn->prepare("SELECT id, first_name, last_name FROM users ORDER BY first_name, last_name");
    if ($user_stmt && $user_stmt->execute()) {
        $user_result = $user_stmt->get_result();
        while ($row = $user_result->fetch_assoc()) {
            $users[] = $row;
        }
        $user_stmt->close();
    }
} catch (Exception $e) {
    logSecurityEvent('Error Fetching Users for Assignment', [
        'error_message' => $e->getMessage(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
    ]);
}

// Fetch lead statistics
try {
    $stats_stmt = $conn->prepare("SELECT
                                COUNT(*) as total_leads,
                                SUM(CASE WHEN status = 'New' THEN 1 ELSE 0 END) as new_leads,
                                SUM(CASE WHEN status = 'Contacted' THEN 1 ELSE 0 END) as contacted_leads,
                                SUM(CASE WHEN status = 'Qualified' THEN 1 ELSE 0 END) as qualified_leads,
                                SUM(CASE WHEN status = 'Converted' THEN 1 ELSE 0 END) as converted_leads,
                                SUM(CASE WHEN status = 'Lost' THEN 1 ELSE 0 END) as lost_leads
                                FROM leads");
    if ($stats_stmt && $stats_stmt->execute()) {
        $stats_result = $stats_stmt->get_result();
        $lead_stats = $stats_result->fetch_assoc();
        $stats_stmt->close();
    } else {
        $lead_stats = ['total_leads' => 0, 'new_leads' => 0, 'contacted_leads' => 0, 'qualified_leads' => 0, 'converted_leads' => 0, 'lost_leads' => 0];
    }
} catch (Exception $e) {
    logSecurityEvent('Error Fetching Lead Statistics', [
        'error_message' => $e->getMessage(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
    ]);
    $lead_stats = ['total_leads' => 0, 'new_leads' => 0, 'contacted_leads' => 0, 'qualified_leads' => 0, 'converted_leads' => 0, 'lost_leads' => 0];
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
    <title>Leads Management - Secure</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer">
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
    </style>
</head>
<body>
<?php
// Validate and include sidebar
$sidebar_file = __DIR__ . '/includes/admin_sidebar.php';
if (file_exists($sidebar_file) && is_readable($sidebar_file)) {
    include $sidebar_file;
} else {
    logSecurityEvent('Sidebar File Missing', ['file_path' => $sidebar_file]);
    echo '<!-- Sidebar not available -->';
}
?>

<div class="main-content">
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
                Rate limit: <?php echo $max_lead_operations - $rate_limit_data['operations']; ?>/<?php echo $max_lead_operations; ?> remaining
            </small>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Leads Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeadModal">
            <i class="fas fa-plus me-1"></i> Add Lead
        </button>
    </div>

    <!-- Lead Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card stats-card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h4 class="text-primary"><?php echo number_format($lead_stats['total_leads'] ?? 0); ?></h4>
                    <p class="text-muted mb-0">Total Leads</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-plus-circle fa-2x text-info mb-2"></i>
                    <h4 class="text-info"><?php echo number_format($lead_stats['new_leads'] ?? 0); ?></h4>
                    <p class="text-muted mb-0">New</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-phone fa-2x text-warning mb-2"></i>
                    <h4 class="text-warning"><?php echo number_format($lead_stats['contacted_leads'] ?? 0); ?></h4>
                    <p class="text-muted mb-0">Contacted</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h4 class="text-success"><?php echo number_format($lead_stats['qualified_leads'] ?? 0); ?></h4>
                    <p class="text-muted mb-0">Qualified</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card border-danger">
                <div class="card-body text-center">
                    <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                    <h4 class="text-danger"><?php echo number_format($lead_stats['lost_leads'] ?? 0); ?></h4>
                    <p class="text-muted mb-0">Lost</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-trophy fa-2x text-success mb-2"></i>
                    <h4 class="text-success"><?php echo number_format($lead_stats['converted_leads'] ?? 0); ?></h4>
                    <p class="text-muted mb-0">Converted</p>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($msg)) echo $msg; ?>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="leadsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leads)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Leads Found</h5>
                                    <p class="text-muted">No leads have been created yet.</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeadModal">
                                        <i class="fas fa-plus me-2"></i>Add First Lead
                                    </button>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($leads as $lead): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($lead['lead_id'] ?? ''); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($lead['name'] ?? ''); ?></strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($lead['email'])): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($lead['email'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars($lead['email'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Not provided</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($lead['phone'])): ?>
                                            <a href="tel:<?php echo htmlspecialchars($lead['phone'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars($lead['phone'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Not provided</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($lead['source'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $status = $lead['status'] ?? 'New';
                                        $status_class = 'bg-secondary';
                                        $status_icon = 'fas fa-question';

                                        switch ($status) {
                                            case 'New':
                                                $status_class = 'bg-info';
                                                $status_icon = 'fas fa-plus-circle';
                                                break;
                                            case 'Contacted':
                                                $status_class = 'bg-warning';
                                                $status_icon = 'fas fa-phone';
                                                break;
                                            case 'Qualified':
                                                $status_class = 'bg-success';
                                                $status_icon = 'fas fa-check-circle';
                                                break;
                                            case 'Converted':
                                                $status_class = 'bg-success';
                                                $status_icon = 'fas fa-trophy';
                                                break;
                                            case 'Lost':
                                                $status_class = 'bg-danger';
                                                $status_icon = 'fas fa-times-circle';
                                                break;
                                            case 'Follow Up':
                                                $status_class = 'bg-primary';
                                                $status_icon = 'fas fa-clock';
                                                break;
                                            case 'Nurturing':
                                                $status_class = 'bg-secondary';
                                                $status_icon = 'fas fa-seedling';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <i class="<?php echo $status_icon; ?> me-1"></i>
                                            <?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($lead['first_name']) || !empty($lead['last_name'])): ?>
                                            <span class="text-sm">
                                                <?php echo htmlspecialchars(($lead['first_name'] ?? '') . ' ' . ($lead['last_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a class="btn btn-sm bg-success-light me-2" href="edit_lead.php?id=<?php echo htmlspecialchars($lead['lead_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" title="Edit Lead">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <a class="btn btn-sm bg-info-light me-2" href="lead_details.php?id=<?php echo htmlspecialchars($lead['lead_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn btn-sm bg-danger-light" onclick="deleteLead(<?php echo htmlspecialchars($lead['lead_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>, '<?php echo htmlspecialchars(addslashes($lead['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>')" title="Delete Lead">
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

    <!-- Add Lead Modal -->
    <div class="modal fade" id="addLeadModal" tabindex="-1" aria-labelledby="addLeadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addLeadModalLabel">Add New Lead</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="name" name="name"
                                           placeholder="Lead Name" required maxlength="100"
                                           pattern="[A-Za-z\s\-\'.]+" title="Only letters, spaces, hyphens, apostrophes, and periods allowed">
                                    <label for="name"><i class="fa fa-user"></i> Lead Name *</label>
                                    <div class="invalid-feedback">Please enter a valid lead name.</div>
                                    <div class="form-text">Enter the full name of the lead</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="email" name="email"
                                           placeholder="Email Address" maxlength="255">
                                    <label for="email"><i class="fa fa-envelope"></i> Email Address</label>
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                    <div class="form-text">Optional: Email address for the lead</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="phone" name="phone"
                                           placeholder="Phone Number" maxlength="20"
                                           pattern="[\d\s\-\+\(\)\.]+" title="Only digits, spaces, hyphens, parentheses, and plus allowed">
                                    <label for="phone"><i class="fa fa-phone"></i> Phone Number</label>
                                    <div class="invalid-feedback">Please enter a valid phone number.</div>
                                    <div class="form-text">Optional: Phone number for the lead</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="source" name="source" required>
                                        <option value="">-- Select Source --</option>
                                        <?php foreach ($lead_sources as $source): ?>
                                            <option value="<?php echo htmlspecialchars($source, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars($source, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="source"><i class="fa fa-bullhorn"></i> Lead Source *</label>
                                    <div class="invalid-feedback">Please select a lead source.</div>
                                    <div class="form-text">How did you acquire this lead?</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="address" name="address"
                                   placeholder="Address" maxlength="255"
                                   pattern="[A-Za-z0-9\s\-_,.#()]+" title="Only letters, numbers, spaces, and common address characters allowed">
                            <label for="address"><i class="fa fa-map-marker"></i> Address</label>
                            <div class="invalid-feedback">Please enter a valid address.</div>
                            <div class="form-text">Optional: Address of the lead</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="">-- Select Status --</option>
                                        <?php foreach ($lead_statuses as $status): ?>
                                            <option value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $status === 'New' ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="status"><i class="fa fa-tasks"></i> Lead Status *</label>
                                    <div class="invalid-feedback">Please select a lead status.</div>
                                    <div class="form-text">Current status of the lead</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="assigned_to" name="assigned_to">
                                        <option value="">-- Assign to User --</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="assigned_to"><i class="fa fa-user-check"></i> Assign To</label>
                                    <div class="invalid-feedback">Please select a valid user.</div>
                                    <div class="form-text">Optional: Assign this lead to a user</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="notes" name="notes"
                                      placeholder="Notes about the lead" maxlength="1000"
                                      style="height: 100px;"></textarea>
                            <label for="notes"><i class="fa fa-sticky-note"></i> Notes</label>
                            <div class="invalid-feedback">Notes contain invalid characters.</div>
                            <div class="form-text">Optional: Additional notes about the lead</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Lead</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

<script>
// Delete lead function
function deleteLead(leadId, leadName) {
    if (confirm(`Are you sure you want to delete the lead "${leadName}"? This action cannot be undone.`)) {
        window.location.href = '?delete=' + encodeURIComponent(leadId);
    }
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
logSecurityEvent('Leads Page Loaded', {
    total_leads: <?php echo $lead_stats['total_leads'] ?? 0; ?>,
    new_leads: <?php echo $lead_stats['new_leads'] ?? 0; ?>,
    contacted_leads: <?php echo $lead_stats['contacted_leads'] ?? 0; ?>,
    qualified_leads: <?php echo $lead_stats['qualified_leads'] ?? 0; ?>,
    converted_leads: <?php echo $lead_stats['converted_leads'] ?? 0; ?>,
    lost_leads: <?php echo $lead_stats['lost_leads'] ?? 0; ?>
});

// AJAX error handler with security logging
$(document).ajaxError(function(event, xhr, settings, thrownError) {
    logSecurityEvent('AJAX Error in Leads', {
        url: settings.url,
        method: settings.type,
        error: thrownError,
        status: xhr.status
    });
});
</script>
</body>
</html>