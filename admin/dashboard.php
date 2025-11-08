<?php
/**
 * Enhanced Security Admin Dashboard
 * Provides secure dashboard functionality with comprehensive security measures
 * Security Enhanced Version
 */

// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/dashboard_security.log');
error_reporting(E_ALL);

// Set comprehensive security headers for admin panel
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://cdn.jsdelivr.net \'unsafe-inline\'; style-src \'self\' https://cdn.jsdelivr.net \'unsafe-inline\'; img-src \'self\' data:;');
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

// Rate limiting for dashboard access
$max_dashboard_loads = 30; // dashboard loads per hour
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
if (isset($_SESSION['admin_last_activity']) &&
    (time() - $_SESSION['admin_last_activity']) > 1800) { // 30 minutes timeout
    session_unset();
    session_destroy();
    logSecurityEvent('Dashboard Session Timeout', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    header('Location: index.php?timeout=1');
    exit();
}

// Update last activity timestamp
$_SESSION['admin_last_activity'] = time();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Rate limiting check
$rate_limit_key = 'dashboard_access_' . md5($ip_address);
if (!isset($_SESSION[$rate_limit_key])) {
    $_SESSION[$rate_limit_key] = [
        'loads' => 0,
        'first_load' => $current_time,
        'last_load' => $current_time
    ];
}

$rate_limit_data = &$_SESSION[$rate_limit_key];

// Check if rate limit exceeded
if ($current_time - $rate_limit_data['first_load'] < 3600) {
    $rate_limit_data['loads']++;
    if ($rate_limit_data['loads'] > $max_dashboard_loads) {
        logSecurityEvent('Dashboard Rate Limit Exceeded', [
            'ip_address' => $ip_address,
            'loads' => $rate_limit_data['loads'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'Too many dashboard loads. Please slow down.',
            'timestamp' => date('Y-m-d H:i:s'),
            'request_id' => uniqid('rate_limit_')
        ]);
        exit();
    }
} else {
    $rate_limit_data['loads'] = 1;
    $rate_limit_data['first_load'] = $current_time;
}

$rate_limit_data['last_load'] = $current_time;

// Security event logging function
function logSecurityEvent($event, $context = []) {
    static $logFile = null;

    if ($logFile === null) {
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/dashboard_security.log';
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
        logSecurityEvent('Suspicious User Agent in Dashboard', [
            'user_agent' => $user_agent,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        return false;
    }

    return true;
}

// Validate database connection file
$db_connection_file = __DIR__ . '/../includes/db_connection.php';
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
try {
    $conn = getDbConnection();
    if (!$conn) {
        logSecurityEvent('Database Connection Failed in Dashboard', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        throw new Exception('Database connection failed');
    }
} catch (Exception $e) {
    logSecurityEvent('Database Error in Dashboard', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'error' => $e->getMessage()
    ]);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error.',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    logSecurityEvent('Unauthorized Dashboard Access', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    header('Location: index.php');
    exit();
}

// Validate user session data
$user_id = $_SESSION['admin_id'] ?? 0;
if (!filter_var($user_id, FILTER_VALIDATE_INT) || $user_id <= 0) {
    logSecurityEvent('Invalid User ID in Session', [
        'user_id' => $user_id,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
    ]);
    session_destroy();
    header('Location: index.php');
    exit();
}

// Validate admin role
$role = $_SESSION['admin_role'] ?? '';
if (empty($role) || !in_array($role, ['admin', 'superadmin', 'manager', 'director', 'office_admin', 'sales', 'employee', 'legal', 'marketing', 'finance', 'hr', 'it', 'operations', 'support'])) {
    logSecurityEvent('Unauthorized Dashboard Access Attempt', [
        'attempted_role' => $role,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    header('Location: index.php?error=unauthorized');
    exit();
}

// Role-based dashboard redirection with validation
$role_dashboard_map = [
    'superadmin' => 'superadmin_dashboard.php',
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
    'support' => 'support_dashboard.php',
];

if (isset($role_dashboard_map[$role])) {
    $dashboard_file = __DIR__ . '/' . $role_dashboard_map[$role];
    if (file_exists($dashboard_file) && is_readable($dashboard_file)) {
        logSecurityEvent('Dashboard Redirect', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'role' => $role,
            'redirect_to' => $role_dashboard_map[$role]
        ]);
        header('Location: ' . $role_dashboard_map[$role]);
        exit();
    }
} elseif (file_exists(__DIR__ . '/' . $role . '_dashboard.php')) {
    $dashboard_file = __DIR__ . '/' . $role . '_dashboard.php';
    if (is_readable($dashboard_file)) {
        logSecurityEvent('Dashboard Redirect', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'role' => $role,
            'redirect_to' => $role . '_dashboard.php'
        ]);
        header('Location: ' . $role . '_dashboard.php');
        exit();
    }
}

// Log dashboard access
logSecurityEvent('Dashboard Accessed', [
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
    'session_id' => session_id(),
    'user_role' => $role,
    'user_id' => $user_id
]);

// Get dashboard statistics with enhanced error handling and prepared statements
try {
    // Property statistics with prepared statements
    $property_stats = [
        'total' => 0,
        'sold' => 0,
        'available' => 0,
        'under_contract' => 0,
    ];

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM properties WHERE 1");
    if ($stmt && $stmt->execute()) {
        $result = $stmt->get_result();
        $property_stats['total'] = (int)($result->fetch_assoc()['count'] ?? 0);
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM properties WHERE status = ?");
    if ($stmt) {
        $stmt->bind_param("s", $status);

        $status = 'sold';
        $stmt->execute();
        $result = $stmt->get_result();
        $property_stats['sold'] = (int)($result->fetch_assoc()['count'] ?? 0);

        $status = 'available';
        $stmt->execute();
        $result = $stmt->get_result();
        $property_stats['available'] = (int)($result->fetch_assoc()['count'] ?? 0);

        $status = 'under_contract';
        $stmt->execute();
        $result = $stmt->get_result();
        $property_stats['under_contract'] = (int)($result->fetch_assoc()['count'] ?? 0);

        $stmt->close();
    }

    // Booking statistics with prepared statements
    $booking_stats = [
        'total' => 0,
        'pending' => 0,
        'confirmed' => 0,
        'completed' => 0,
    ];

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE 1");
    if ($stmt && $stmt->execute()) {
        $result = $stmt->get_result();
        $booking_stats['total'] = (int)($result->fetch_assoc()['count'] ?? 0);
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE status = ?");
    if ($stmt) {
        $stmt->bind_param("s", $status);

        $status = 'pending';
        $stmt->execute();
        $result = $stmt->get_result();
        $booking_stats['pending'] = (int)($result->fetch_assoc()['count'] ?? 0);

        $status = 'confirmed';
        $stmt->execute();
        $result = $stmt->get_result();
        $booking_stats['confirmed'] = (int)($result->fetch_assoc()['count'] ?? 0);

        $status = 'completed';
        $stmt->execute();
        $result = $stmt->get_result();
        $booking_stats['completed'] = (int)($result->fetch_assoc()['count'] ?? 0);

        $stmt->close();
    }

    // Customer statistics with prepared statements
    $customer_stats = [
        'total' => 0,
        'new_this_month' => 0,
    ];

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE 1");
    if ($stmt && $stmt->execute()) {
        $result = $stmt->get_result();
        $customer_stats['total'] = (int)($result->fetch_assoc()['count'] ?? 0);
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE DATE(created_at) >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')");
    if ($stmt && $stmt->execute()) {
        $result = $stmt->get_result();
        $customer_stats['new_this_month'] = (int)($result->fetch_assoc()['count'] ?? 0);
    }
    $stmt->close();

    // Lead statistics with prepared statements
    $lead_stats = [
        'total' => 0,
        'new' => 0,
        'contacted' => 0,
        'qualified' => 0,
    ];

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM leads WHERE 1");
    if ($stmt && $stmt->execute()) {
        $result = $stmt->get_result();
        $lead_stats['total'] = (int)($result->fetch_assoc()['count'] ?? 0);
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM leads WHERE status = ?");
    if ($stmt) {
        $stmt->bind_param("s", $status);

        $status = 'new';
        $stmt->execute();
        $result = $stmt->get_result();
        $lead_stats['new'] = (int)($result->fetch_assoc()['count'] ?? 0);

        $status = 'contacted';
        $stmt->execute();
        $result = $stmt->get_result();
        $lead_stats['contacted'] = (int)($result->fetch_assoc()['count'] ?? 0);

        $status = 'qualified';
        $stmt->execute();
        $result = $stmt->get_result();
        $lead_stats['qualified'] = (int)($result->fetch_assoc()['count'] ?? 0);

        $stmt->close();
    }

    // Recent activities with prepared statements
    $recent_activities = [];

    // Get recent bookings
    $stmt = $conn->prepare("SELECT 'booking' as type, id, customer_name, created_at, 'New booking received' as description
                           FROM bookings
                           ORDER BY created_at DESC LIMIT 5");
    if ($stmt && $stmt->execute()) {
        $bookings_activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $recent_activities = array_merge($recent_activities, $bookings_activities);
    }
    $stmt->close();

    // Get recent leads
    $stmt = $conn->prepare("SELECT 'lead' as type, id, name as customer_name, created_at, 'New lead added' as description
                           FROM leads
                           ORDER BY created_at DESC LIMIT 5");
    if ($stmt && $stmt->execute()) {
        $leads_activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $recent_activities = array_merge($recent_activities, $leads_activities);
    }
    $stmt->close();

    // Merge and sort activities
    usort($recent_activities, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    $recent_activities = array_slice($recent_activities, 0, 5);

    // Recent bookings with prepared statements
    $stmt = $conn->prepare("SELECT b.*, c.name as customer_name, p.title as property_title
                           FROM bookings b
                           LEFT JOIN customers c ON b.customer_id = c.id
                           LEFT JOIN properties p ON b.property_id = p.id
                           ORDER BY b.created_at DESC LIMIT 5");
    $recent_bookings = [];
    if ($stmt && $stmt->execute()) {
        $recent_bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();

    // Revenue data for chart with prepared statements
    $revenue_data = [];
    $stmt = $conn->prepare("SELECT
                               DATE_FORMAT(created_at, '%b %Y') as month,
                               SUM(amount) as total
                           FROM transactions
                           WHERE status = 'completed'
                           GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                           ORDER BY created_at DESC
                           LIMIT 6");
    if ($stmt && $stmt->execute()) {
        $revenue_result = $stmt->get_result();
        if ($revenue_result) {
            $revenue_data = array_reverse($revenue_result->fetch_all(MYSQLI_ASSOC));
        }
    }
    $stmt->close();

} catch (Exception $e) {
    logSecurityEvent("Dashboard Data Error: " . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
    ]);
    $property_stats = $booking_stats = $customer_stats = $lead_stats = [];
    $recent_activities = $recent_bookings = $revenue_data = [];
}

// Validate and include header file
$header_file = __DIR__ . '/includes/new_header.php';
if (file_exists($header_file) && is_readable($header_file)) {
    include $header_file;
} else {
    logSecurityEvent('Header File Missing', ['file_path' => $header_file]);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Header file not found.',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}
?>

<!-- Page Title -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 fade-in">Dashboard</h1>
    <div>
        <button class="btn-modern btn-modern-primary" data-bs-toggle="modal" data-bs-target="#quickActionModal">
            <i class="fas fa-plus me-2"></i>Quick Action
        </button>
    </div>
</div>

<!-- Security Status Bar -->
<div class="alert-modern alert-modern-info d-flex justify-content-between align-items-center mb-4">
    <div>
        <i class="fas fa-shield-alt me-2"></i>
        <strong>Security Status:</strong> Protected
        <span class="badge-modern badge-modern-success ms-2">Active</span>
    </div>
    <div class="text-end">
        <small class="text-muted">
            Session expires: <?php echo date('H:i:s', time() + 1800); ?><br>
            Rate limit: <?php echo $max_dashboard_loads - $rate_limit_data['loads']; ?>/<?php echo $max_dashboard_loads; ?> remaining
        </small>
    </div>
</div>

<!-- Recent Activities Section -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card-modern fade-in">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activities</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Details</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Display recent bookings with proper validation
                            $stmt = $conn->prepare("SELECT b.*, p.address as property_title FROM bookings b LEFT JOIN properties p ON b.property_id = p.id ORDER BY b.booking_date DESC LIMIT 5");
                            if ($stmt && $stmt->execute()) {
                                $recent_bookings_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                $stmt->close();

                                foreach ($recent_bookings_data as $booking): ?>
                                <tr>
                                    <td><span class="badge-modern badge-modern-primary">Booking</span></td>
                                    <td>New booking for <?php echo htmlspecialchars($booking['property_title'] ?? 'Property #' . $booking['property_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($booking['booking_date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="badge-modern badge-modern-<?php echo $booking['status'] === 'confirmed' ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars(ucfirst($booking['status']), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                </tr>
                                <?php endforeach;
                            }

                            // Display recent leads with proper validation
                            $stmt = $conn->prepare("SELECT * FROM leads ORDER BY created_at DESC LIMIT 5");
                            if ($stmt && $stmt->execute()) {
                                $recent_leads_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                $stmt->close();

                                foreach ($recent_leads_data as $lead): ?>
                                <tr>
                                    <td><span class="badge-modern badge-modern-primary">Lead</span></td>
                                    <td>New lead from <?php echo htmlspecialchars($lead['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($lead['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="badge-modern badge-modern-<?php echo $lead['status'] === 'contacted' ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars(ucfirst($lead['status']), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                </tr>
                                <?php endforeach;
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats and Charts -->
<div class="dashboard-grid mb-4">
    <!-- Properties Card -->
    <div class="stat-card fade-in">
        <div class="stat-icon">
            <i class="fas fa-home text-primary"></i>
        </div>
        <div class="stat-number"><?php echo htmlspecialchars(number_format($property_stats['total']), ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="stat-label">Total Properties</div>
        <div class="mt-3">
            <span class="badge-modern badge-modern-success me-2">
                <i class="fas fa-check-circle me-1"></i> <?php echo htmlspecialchars($property_stats['available'], ENT_QUOTES, 'UTF-8'); ?> Available
            </span>
            <span class="badge-modern badge-modern-warning">
                <i class="fas fa-file-contract me-1"></i> <?php echo htmlspecialchars($property_stats['under_contract'], ENT_QUOTES, 'UTF-8'); ?> Under Contract
            </span>
        </div>
        <div class="mt-3">
            <a href="properties.php" class="btn-modern btn-modern-outline">
                View all properties <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>

    <!-- Bookings Card -->
    <div class="stat-card fade-in" style="animation-delay: 0.1s;">
        <div class="stat-icon">
            <i class="fas fa-calendar-check text-info"></i>
        </div>
        <div class="stat-number"><?php echo htmlspecialchars(number_format($booking_stats['total']), ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="stat-label">Total Bookings</div>
        <div class="mt-3">
            <span class="badge-modern badge-modern-warning me-2">
                <i class="fas fa-clock me-1"></i> <?php echo htmlspecialchars($booking_stats['pending'], ENT_QUOTES, 'UTF-8'); ?> Pending
            </span>
            <span class="badge-modern badge-modern-success">
                <i class="fas fa-check me-1"></i> <?php echo htmlspecialchars($booking_stats['confirmed'], ENT_QUOTES, 'UTF-8'); ?> Confirmed
            </span>
        </div>
        <div class="mt-3">
            <a href="bookings.php" class="btn-modern btn-modern-outline">
                View all bookings <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>

    <!-- Customers Card -->
    <div class="stat-card fade-in" style="animation-delay: 0.2s;">
        <div class="stat-icon">
            <i class="fas fa-users text-success"></i>
        </div>
        <div class="stat-number"><?php echo htmlspecialchars(number_format($customer_stats['total']), ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="stat-label">Total Customers</div>
        <div class="mt-3">
            <span class="badge-modern badge-modern-primary">
                <i class="fas fa-user-plus me-1"></i> <?php echo htmlspecialchars($customer_stats['new_this_month'], ENT_QUOTES, 'UTF-8'); ?> New this month
            </span>
        </div>
        <div class="mt-3">
            <a href="customers.php" class="btn-modern btn-modern-outline">
                View all customers <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>

    <!-- Leads Card -->
    <div class="stat-card fade-in" style="animation-delay: 0.3s;">
        <div class="stat-icon">
            <i class="fas fa-user-tie text-warning"></i>
        </div>
        <div class="stat-number"><?php echo htmlspecialchars(number_format($lead_stats['total']), ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="stat-label">Total Leads</div>
        <div class="mt-3">
            <span class="badge-modern badge-modern-error me-2">
                <i class="fas fa-exclamation-circle me-1"></i> <?php echo htmlspecialchars($lead_stats['new'], ENT_QUOTES, 'UTF-8'); ?> New
            </span>
            <span class="badge-modern badge-modern-primary">
                <i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($lead_stats['contacted'], ENT_QUOTES, 'UTF-8'); ?> Contacted
            </span>
        </div>
        <div class="mt-3">
            <a href="leads.php" class="btn-modern btn-modern-outline">
                View all leads <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>

<!-- Advanced Admin Dashboard Widgets -->
<div class="dashboard-grid">
    <!-- Audit Access Log Widget -->
    <div class="card-modern fade-in" style="animation-delay: 0.4s;">
        <div class="card-header">
            <i class="fas fa-history me-2"></i>Audit Access Log
        </div>
        <div class="card-body">
            <?php
            try {
                $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(action='export') as exports, SUM(action='drilldown') as drilldowns FROM audit_access_log");
                if ($stmt && $stmt->execute()) {
                    $audit = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    echo "<div class='stat-number text-primary'>" . htmlspecialchars($audit['total'] ?? 0, ENT_QUOTES, 'UTF-8') . "</div>";
                    echo "<div class='stat-label'>Total Logs</div>";
                    echo "<div class='mt-3'>";
                    echo "<span class='badge-modern badge-modern-success me-2'>" . htmlspecialchars($audit['exports'] ?? 0, ENT_QUOTES, 'UTF-8') . " Exports</span>";
                    echo "<span class='badge-modern badge-modern-primary'>" . htmlspecialchars($audit['drilldowns'] ?? 0, ENT_QUOTES, 'UTF-8') . " Drilldowns</span>";
                    echo "</div>";
                }
            } catch(Exception $e) {
                echo "<div class='text-muted'>No data found.</div>";
            }
            ?>
            <div class="mt-3">
                <a href="audit_access_log_view.php" class="btn-modern btn-modern-outline">
                    View Details <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    <!-- Compliance Status Widget -->
    <div class="card-modern fade-in" style="animation-delay: 0.5s;">
        <div class="card-header">
            <i class="fas fa-balance-scale me-2"></i>Compliance Status
        </div>
        <div class="card-body">
            <?php
            try {
                $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(status='passed') as passed, SUM(status='failed') as failed FROM compliance_audit_bot");
                if ($stmt && $stmt->execute()) {
                    $compliance = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    echo "<div class='stat-number text-warning'>" . htmlspecialchars($compliance['total'] ?? 0, ENT_QUOTES, 'UTF-8') . "</div>";
                    echo "<div class='stat-label'>Total Audits</div>";
                    echo "<div class='mt-3'>";
                    echo "<span class='badge-modern badge-modern-success me-2'>" . htmlspecialchars($compliance['passed'] ?? 0, ENT_QUOTES, 'UTF-8') . " Passed</span>";
                    echo "<span class='badge-modern badge-modern-error'>" . htmlspecialchars($compliance['failed'] ?? 0, ENT_QUOTES, 'UTF-8') . " Failed</span>";
                    echo "</div>";
                }
            } catch(Exception $e) {
                echo "<div class='text-muted'>No data found.</div>";
            }
            ?>
            <div class="mt-3">
                <a href="compliance_dashboard.php" class="btn-modern btn-modern-outline">
                    Compliance Dashboard <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    <!-- Payouts/Commission Widget -->
    <div class="card-modern fade-in" style="animation-delay: 0.6s;">
        <div class="card-header">
            <i class="fas fa-coins me-2"></i>Payouts & Commission
        </div>
        <div class="card-body">
            <?php
            try {
                $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(status='pending') as pending, SUM(status='paid') as paid FROM payouts");
                if ($stmt && $stmt->execute()) {
                    $payouts = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    echo "<div class='stat-number text-success'>" . htmlspecialchars($payouts['total'] ?? 0, ENT_QUOTES, 'UTF-8') . "</div>";
                    echo "<div class='stat-label'>Total Payouts</div>";
                    echo "<div class='mt-3'>";
                    echo "<span class='badge-modern badge-modern-warning me-2'>" . htmlspecialchars($payouts['pending'] ?? 0, ENT_QUOTES, 'UTF-8') . " Pending</span>";
                    echo "<span class='badge-modern badge-modern-success'>" . htmlspecialchars($payouts['paid'] ?? 0, ENT_QUOTES, 'UTF-8') . " Paid</span>";
                    echo "</div>";
                }
            } catch(Exception $e) {
                echo "<div class='text-muted'>No data found.</div>";
            }
            ?>
            <div class="mt-3">
                <a href="payouts_report.php" class="btn-modern btn-modern-outline">
                    Payouts Report <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>
<div class="dashboard-grid">
    <!-- Scheduled Reports Widget -->
    <div class="card-modern fade-in" style="animation-delay: 0.7s;">
        <div class="card-header">
            <i class="fas fa-calendar-alt me-2"></i>Scheduled Reports
        </div>
        <div class="card-body">
            <?php
            try {
                $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(status='scheduled') as scheduled, SUM(status='sent') as sent FROM scheduled_report");
                if ($stmt && $stmt->execute()) {
                    $reports = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    echo "<div class='stat-number text-primary'>" . htmlspecialchars($reports['total'] ?? 0, ENT_QUOTES, 'UTF-8') . "</div>";
                    echo "<div class='stat-label'>Total Reports</div>";
                    echo "<div class='mt-3'>";
                    echo "<span class='badge-modern badge-modern-warning me-2'>" . htmlspecialchars($reports['scheduled'] ?? 0, ENT_QUOTES, 'UTF-8') . " Scheduled</span>";
                    echo "<span class='badge-modern badge-modern-success'>" . htmlspecialchars($reports['sent'] ?? 0, ENT_QUOTES, 'UTF-8') . " Sent</span>";
                    echo "</div>";
                }
            } catch(Exception $e) {
                echo "<div class='text-muted'>No data found.</div>";
            }
            ?>
            <div class="mt-3">
                <a href="scheduled_report.php" class="btn-modern btn-modern-outline">
                    View Reports <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    <!-- Quick Actions Widget -->
    <div class="card-modern fade-in" style="animation-delay: 0.8s; grid-column: 1 / -1;">
        <div class="card-header">
            <i class="fas fa-bolt me-2"></i>Quick Actions
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="add_employee.php" class="btn-modern btn-modern-primary">
                    <i class="fas fa-user-plus me-2"></i>Add Employee
                </a>
                <a href="add_role.php" class="btn-modern btn-modern-outline">
                    <i class="fas fa-user-tag me-2"></i>Add Role
                </a>
                <a href="properties.php" class="btn-modern btn-modern-success">
                    <i class="fas fa-home me-2"></i>Add Property
                </a>
                <a href="leads.php" class="btn-modern btn-modern-warning">
                    <i class="fas fa-user-tie me-2"></i>Add Lead
                </a>
                <a href="notification_management.php" class="btn-modern btn-modern-error">
                    <i class="fas fa-bell me-2"></i>Send Notification
                </a>
                <a href="documents_dashboard.php" class="btn-modern btn-modern-primary">
                    <i class="fas fa-file-upload me-2"></i>Upload Document
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Charts -->
<div class="dashboard-grid">
    <div class="card-modern fade-in" style="animation-delay: 0.9s;">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Leads by Status</h5>
        </div>
        <div class="card-body">
            <canvas id="leadsStatusChart"></canvas>
        </div>
    </div>
    
    <div class="card-modern fade-in" style="animation-delay: 1s;">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Revenue Trend (Last 6 Months)</h5>
        </div>
        <div class="card-body">
            <canvas id="revenueTrendChart" style="min-height: 350px; height: 350px; max-width: 100%;"></canvas>
        </div>
    </div>
</div>

<!-- Quick Action Modal -->
<div class="modal fade" id="quickActionModal" tabindex="-1" aria-labelledby="quickActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: var(--radius-lg); box-shadow: var(--shadow-xl);">
            <div class="modal-header" style="background: var(--bg-gradient); color: var(--text-white); border: none; border-radius: var(--radius-lg) var(--radius-lg) 0 0;">
                <h5 class="modal-title" id="quickActionModalLabel">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: var(--space-xl);">
                <div class="d-grid gap-3">
                    <a href="add_employee.php" class="btn-modern btn-modern-primary">
                        <i class="fas fa-user-plus me-2"></i>Add New Employee
                    </a>
                    <a href="add_role.php" class="btn-modern btn-modern-outline">
                        <i class="fas fa-user-tag me-2"></i>Add New Role
                    </a>
                    <a href="properties.php" class="btn-modern btn-modern-success">
                        <i class="fas fa-home me-2"></i>Add New Property
                    </a>
                    <a href="leads.php" class="btn-modern btn-modern-warning">
                        <i class="fas fa-user-tie me-2"></i>Add New Lead
                    </a>
                    <a href="notification_management.php" class="btn-modern btn-modern-error">
                        <i class="fas fa-bell me-2"></i>Send Notification
                    </a>
                    <a href="documents_dashboard.php" class="btn-modern btn-modern-primary">
                        <i class="fas fa-file-upload me-2"></i>Upload Document
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js" integrity="sha384-..." crossorigin="anonymous"></script>
<script>
<?php
// Leads by Status Chart Data with prepared statements
$lead_status_data = [];
$lead_status_labels = [];
$stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM leads GROUP BY status");
if ($stmt && $stmt->execute()) {
    $lead_status_result = $stmt->get_result();
    while($row = $lead_status_result->fetch_assoc()) {
        $lead_status_labels[] = htmlspecialchars($row['status'] ?: 'Unknown', ENT_QUOTES, 'UTF-8');
        $lead_status_data[] = (int)($row['count'] ?? 0);
    }
}
$stmt->close();

// Revenue Trend Data (last 6 months) with prepared statements
$revenue_labels = [];
$revenue_data_trend = [];
$stmt = $conn->prepare("SELECT DATE_FORMAT(date, '%b %Y') as month, SUM(amount) as total FROM transactions WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY month ORDER BY MIN(date)");
if ($stmt && $stmt->execute()) {
    $revenue_trend_result = $stmt->get_result();
    while($row2 = $revenue_trend_result->fetch_assoc()) {
        $revenue_labels[] = htmlspecialchars($row2['month'], ENT_QUOTES, 'UTF-8');
        $revenue_data_trend[] = (float)($row2['total'] ?: 0);
    }
}
$stmt->close();
?>
const leadsStatusCtx = document.getElementById('leadsStatusChart').getContext('2d');
const leadsStatusChart = new Chart(leadsStatusCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($lead_status_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($lead_status_data); ?>,
            backgroundColor: [
                '#0d6efd','#198754','#ffc107','#dc3545','#6c757d','#6610f2','#fd7e14'
            ],
        }]
    },
    options: {responsive: true}
});
const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
const revenueTrendChart = new Chart(revenueTrendCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($revenue_labels); ?>,
        datasets: [{
            label: 'Revenue',
            data: <?php echo json_encode($revenue_data_trend); ?>,
            backgroundColor: '#0d6efd',
        }]
    },
    options: {responsive: true, plugins: {legend: {display: false}}}
});

// Security monitoring
let csrfToken = '<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>';

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
    logSecurityEvent('AJAX Error in Dashboard', {
        url: settings.url,
        method: settings.type,
        error: thrownError,
        status: xhr.status
    });
});

// Initialize security on page load
document.addEventListener('DOMContentLoaded', function() {
    logSecurityEvent('Dashboard Page Loaded', {
        user_role: '<?php echo htmlspecialchars($role); ?>',
        user_id: <?php echo (int)$user_id; ?>
    });
});
</script>

<?php
// Close database connection
if (isset($conn) && $conn) {
    $conn->close();
}

include __DIR__ . '/includes/new_footer.php';
?>
