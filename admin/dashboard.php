<?php
// Admin Dashboard: Modern UI, CRM/ERP Integrated, Analytics (Leads by Status, Revenue Trend)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Role-based dashboard redirection
$role = $_SESSION['admin_role'] ?? '';
if ($role !== 'admin') {
    // Use same logic as in admin_login_handler.php
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
        // Add more roles as needed
    ];
    if (isset($role_dashboard_map[$role]) && file_exists(__DIR__ . '/' . $role_dashboard_map[$role])) {
        header('Location: ' . $role_dashboard_map[$role]);
        exit();
    } else if (file_exists(__DIR__ . '/' . $role . '_dashboard.php')) {
        header('Location: ' . $role . '_dashboard.php');
        exit();
    } else {
        header('Location: login.php?error=unauthorized');
        exit();
    }
}

// Include database connection
require_once __DIR__ . '/../includes/db_connection.php';
$conn = getDbConnection();

// Dashboard Modern Stats (property, customer, booking, contact/inquiry)
$property_count = $conn->query("SELECT COUNT(*) as count FROM properties")->fetch_assoc()['count'] ?? 0;
$customer_count = $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'] ?? 0;
$booking_count = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'] ?? 0;
$contact_count = $conn->query("SELECT COUNT(*) as count FROM leads")->fetch_assoc()['count'] ?? 0;

// Recent Bookings
// Try to use p.title as property_title, fallback to p.name or p.address if error
try {
    $recent_bookings = $conn->query("SELECT b.*, u.name as customer_name, p.title as property_title FROM bookings b LEFT JOIN users u ON b.customer_id = u.id LEFT JOIN properties p ON b.property_id = p.id ORDER BY b.booking_date DESC LIMIT 5");
} catch (mysqli_sql_exception $e) {
    // If 'title' column missing, try 'name'
    try {
        $recent_bookings = $conn->query("SELECT b.*, u.name as customer_name, p.name as property_title FROM bookings b LEFT JOIN users u ON b.customer_id = u.id LEFT JOIN properties p ON b.property_id = p.id ORDER BY b.booking_date DESC LIMIT 5");
    } catch (mysqli_sql_exception $e2) {
        // If 'name' also missing, try 'address'
        $recent_bookings = $conn->query("SELECT b.*, u.name as customer_name, p.address as property_title FROM bookings b LEFT JOIN users u ON b.customer_id = u.id LEFT JOIN properties p ON b.property_id = p.id ORDER BY b.booking_date DESC LIMIT 5");
    }
}


// Recent Transactions
$recent_transactions = $conn->query("
    SELECT t.*, 
        COALESCE(u.name, l.name) AS customer_name
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN leads l ON t.user_id = l.id
    ORDER BY t.date DESC LIMIT 5
");

// Recent Inquiries
$recent_inquiries = $conn->query("SELECT * FROM leads ORDER BY id DESC LIMIT 5");

// Include header
include 'admin_header.php';
?>
<!-- Quick Actions -->
<div class="mb-4 d-flex gap-2">
    <a href="properties.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Property</a>
    <a href="admin_panel.php" class="btn btn-success"><i class="fas fa-user-plus"></i> Add User</a>
    <a href="leads.php" class="btn btn-warning"><i class="fas fa-user-tie"></i> Add Lead</a>
</div>
<!-- Dashboard Stats -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card">
            <div class="stats-card primary">
                <div class="stats-icon">
                    <i class="fas fa-home fa-2x text-primary"></i>
                </div>
                <div class="stats-info ms-3">
                    <h5 class="stats-title">Properties</h5>
                    <h2 class="stats-number"><?php echo $property_count; ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card">
            <div class="stats-card success">
                <div class="stats-icon">
                    <i class="fas fa-users fa-2x text-success"></i>
                </div>
                <div class="stats-info ms-3">
                    <h5 class="stats-title">Customers</h5>
                    <h2 class="stats-number"><?php echo $customer_count; ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card">
            <div class="stats-card warning">
                <div class="stats-icon">
                    <i class="fas fa-calendar-check fa-2x text-warning"></i>
                </div>
                <div class="stats-info ms-3">
                    <h5 class="stats-title">Bookings</h5>
                    <h2 class="stats-number"><?php echo $booking_count; ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card">
            <div class="stats-card info">
                <div class="stats-icon">
                    <i class="fas fa-envelope fa-2x text-info"></i>
                </div>
                <div class="stats-info ms-3">
                    <h5 class="stats-title">Inquiries</h5>
                    <h2 class="stats-number"><?php echo $contact_count; ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Bookings -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Bookings</h5>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Property</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_bookings && $recent_bookings instanceof mysqli_result && $recent_bookings->num_rows > 0):
                            while($b = $recent_bookings->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $b['id']; ?></td>
                            <td><?php echo htmlspecialchars($b['customer_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($b['property_title'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($b['booking_date'] ?? ''); ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4">No data found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Recent Transactions -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Transactions</h5>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_transactions && $recent_transactions instanceof mysqli_result && $recent_transactions->num_rows > 0):
                            while($t = $recent_transactions->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $t['id']; ?></td>
                            <td><?php echo htmlspecialchars($t['customer_name'] ?? ''); ?></td>
                            <td>₹<?php echo number_format($t['amount'] ?? 0,2); ?></td>
                            <td><?php echo htmlspecialchars($t['transaction_date'] ?? ''); ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4">No data found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Recent Inquiries/Leads Table -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Inquiries / Leads</h5>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Lead ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Source</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_inquiries && $recent_inquiries instanceof mysqli_result && $recent_inquiries->num_rows > 0):
                            while($lead = $recent_inquiries->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $lead['id']; ?></td>
                            <td><?php echo htmlspecialchars($lead['name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($lead['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($lead['phone'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($lead['status'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($lead['source'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($lead['created_at'] ?? ''); ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="7">No data found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Analytics Charts -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Leads by Status</h5></div>
            <div class="card-body"><canvas id="leadsStatusChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Revenue Trend (Last 6 Months)</h5></div>
            <div class="card-body"><canvas id="revenueTrendChart" style="min-height: 350px; height: 350px; max-width: 100%;"></canvas></div>
        </div>
    </div>
</div>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Leads by Status Chart Data
<?php
$lead_status_data = [];
$lead_status_labels = [];
$res = $conn->query("SELECT status, COUNT(*) as count FROM leads GROUP BY status");
while($row = $res->fetch_assoc()) {
    $lead_status_labels[] = $row['status'] ?: 'Unknown';
    $lead_status_data[] = $row['count'];
}
// Revenue Trend Data (last 6 months)
$revenue_labels = [];
$revenue_data = [];
$res2 = $conn->query("SELECT DATE_FORMAT(date, '%b %Y') as month, SUM(amount) as total FROM transactions WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY month ORDER BY MIN(date)");
while($row2 = $res2->fetch_assoc()) {
    $revenue_labels[] = $row2['month'];
    $revenue_data[] = $row2['total'] ?: 0;
}
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
            data: <?php echo json_encode($revenue_data); ?>,
            backgroundColor: '#0d6efd',
        }]
    },
    options: {responsive: true, plugins: {legend: {display: false}}}
});
</script>
<!-- Leads Converted to Customers Widget -->
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card text-bg-success">
            <div class="card-body">
                <h5 class="card-title">Leads Converted (This Month)</h5>
                <h2 class="card-text">
                    <?php
                    $converted_this_month = $conn->query("SELECT COUNT(*) as cnt FROM leads WHERE status='converted' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetch_assoc()['cnt'] ?? 0;
                    echo $converted_this_month;
                    ?>
                </h2>
                <p class="mb-0 small">Total Converted: <b><?php echo $conn->query("SELECT COUNT(*) as cnt FROM leads WHERE status='converted'")->fetch_assoc()['cnt'] ?? 0; ?></b></p>
            </div>
        </div>
    </div>
</div>
<!-- Upcoming Visit Reminders Table -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Upcoming Visit Reminders</h5></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Property</th>
                            <th>Customer/Lead</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $visits = $conn->query("SELECT v.*, p.address as property_address, c.name as customer_name, l.name as lead_name FROM property_visits v LEFT JOIN properties p ON v.property_id = p.id LEFT JOIN customers c ON v.customer_id = c.id LEFT JOIN leads l ON v.lead_id = l.id WHERE v.visit_date >= CURDATE() ORDER BY v.visit_date ASC, v.visit_time ASC LIMIT 5");
                        } catch (mysqli_sql_exception $e) {
                            $visits = false;
                        }
                        if ($visits && $visits instanceof mysqli_result && $visits->num_rows > 0):
                            while($v = $visits->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($v['visit_date'] . ' ' . $v['visit_time']); ?></td>
                            <td><?php echo htmlspecialchars($v['property_address'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($v['customer_name'] ?: $v['lead_name']); ?></td>
                            <td><?php echo htmlspecialchars($v['status'] ?? ''); ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4">No data found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Recent Notifications Widget -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Recent Notifications</h5></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $admin_id = $_SESSION['admin_id'] ?? 1;
                        $notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $admin_id ORDER BY id DESC LIMIT 5");
                        if ($notifications && $notifications instanceof mysqli_result && $notifications->num_rows > 0):
                            while($n = $notifications->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($n['title'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($n['message'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($n['created_at'] ?? ''); ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="3">No data found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php

function logError($message, $context = []) {
    // Validate and create logs directory
    $logDir = __DIR__ . '/../logs';
    try {
        if (!is_dir($logDir)) {
            // Attempt to create directory with full permissions
            if (!mkdir($logDir, 0777, true)) {
                // Fallback to system temp directory if creation fails
                $logDir = sys_get_temp_dir() . '/apsdreamhome_logs';
                mkdir($logDir, 0777, true);
            }
        }

        $logFile = $logDir . '/dashboard_error.log';
        
        // Prepare timestamp and context
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = '';
        
        if (!empty($context)) {
            foreach ($context as $key => $value) {
                // Advanced context value conversion
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
                    
                    // Truncate extremely long values
                    $strValue = mb_strlen($strValue) > 500 ? mb_substr($strValue, 0, 500) . '...' : $strValue;
                    
                    $contextStr .= " | $key: $strValue";
                } catch (Exception $e) {
                    $contextStr .= " | $key: SERIALIZATION_ERROR";
                }
            }
        }
        
        // Construct log message
        $logMessage = "[{$timestamp}] {$message}{$contextStr}\n";
        
        // Write to log file with error handling
        if (file_put_contents($logFile, $logMessage, FILE_APPEND) === false) {
            // Fallback error logging
            error_log("CRITICAL: Unable to write to log file. Message: {$logMessage}");
        }
        
        // Additional system error logging
        error_log($logMessage);
    } catch (Exception $e) {
        // Last resort error logging
        error_log("CRITICAL LOGGING FAILURE: " . $e->getMessage());
    }
}

// Validate critical paths and dependencies
function validateSystemPaths() {
    $criticalPaths = [
        'includes_dir' => __DIR__ . '/../includes',
        'config_dir' => __DIR__ . '/../config',
        'db_connection_file' => __DIR__ . '/../includes/db_connection.php',
        'session_manager_file' => __DIR__ . '/../app/Services/SessionManager.php'
    ];

    $missingPaths = [];
    foreach ($criticalPaths as $name => $path) {
        if (!file_exists($path)) {
            $missingPaths[] = $name . ': ' . $path;
        }
    }

    if (!empty($missingPaths)) {
        logError('Critical Path Validation Failed', [
            'missing_paths' => $missingPaths
        ]);
        throw new Exception('System configuration paths are missing: ' . implode(', ', $missingPaths));
    }
}

// Show all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Global error and exception handling
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    logError('PHP Error', [
        'errno' => $errno,
        'errstr' => $errstr,
        'errfile' => $errfile,
        'errline' => $errline
    ]);
    return false; // Let PHP handle the error
});

set_exception_handler(function($exception) {
    logError('Uncaught Exception', [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    die('A critical error occurred. Our team has been notified.');
});

// Explicit error tracking for file inclusion
function safeRequire($file) {
    if (!file_exists($file)) {
        logError('File Not Found', ['file' => $file]);
        throw new Exception("Required file not found: $file");
    }
    
    try {
        require_once $file;
    } catch (Exception $e) {
        logError('File Include Error', [
            'file' => $file,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}

// Show all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Global error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    logError("PHP Error", [
        'errno' => $errno,
        'errstr' => $errstr,
        'errfile' => $errfile,
        'errline' => $errline
    ]);
    return false; // Let PHP handle the error
});

// Global exception handler
set_exception_handler(function($exception) {
    logError("Uncaught Exception", [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    die('A critical error occurred. Please check the logs.');
});

// Show all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Start session and include database connection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DEBUG: Output session contents for troubleshooting
if (isset($_GET['debug_session'])) {
    echo '<pre>SESSION DEBUG:\n';
    print_r($_SESSION);
    echo '</pre>';
}

// Validate system paths before file inclusion
try {
    validateSystemPaths();
} catch (Exception $e) {
    logError('System Path Validation Failed', [
        'message' => $e->getMessage()
    ]);
    die('System configuration error. Please contact support.');
}

// Require necessary files with error handling
try {
    // Validate and include database connection
    $dbConnectionPath = __DIR__ . '/../includes/db_connection.php';
    if (!file_exists($dbConnectionPath)) {
        throw new Exception('Database connection file not found: ' . $dbConnectionPath);
    }
    require_once $dbConnectionPath;

    // Validate and include session manager
    $sessionManagerPath = __DIR__ . '/../includes/classes/SessionManager.php';
    if (!file_exists($sessionManagerPath)) {
        throw new Exception('Session manager file not found: ' . $sessionManagerPath);
    }
    require_once $sessionManagerPath;
} catch (Exception $e) {
    logError('Critical File Inclusion Error', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    die('Unable to load critical system files. Our team has been notified.');
}

// Establish database connection with comprehensive error handling
try {
    // Detailed system and extension checks
    $systemChecks = [
        'php_version' => PHP_VERSION,
        'os' => PHP_OS,
        'server_api' => PHP_SAPI,
        'extensions' => [
            'mysqli' => extension_loaded('mysqli'),
            'pdo' => extension_loaded('pdo'),
            'json' => extension_loaded('json')
        ]
    ];

    // Validate critical system prerequisites
    $missingExtensions = array_filter($systemChecks['extensions'], function($loaded) {
        return !$loaded;
    });

    if (!empty($missingExtensions)) {
        throw new Exception('Missing critical PHP extensions: ' . implode(', ', array_keys($missingExtensions)));
    }

    // Validate database connection function existence
    if (!function_exists('getDbConnection')) {
        // Log detailed context about function availability
        $availableFunctions = get_defined_functions(true);
        $userFunctions = $availableFunctions['user'] ?? [];
        
        logError('Database Connection Function Missing', [
            'available_user_functions' => array_slice($userFunctions, 0, 20), // Limit to first 20
            'include_path' => get_include_path(),
            'included_files' => get_included_files()
        ]);

        throw new Exception('Database connection function is not defined');
    }

    // Validate database configuration file
    $dbConfigPath = __DIR__ . '/../config/database.php';
    if (!file_exists($dbConfigPath)) {
        throw new Exception('Database configuration file is missing: ' . $dbConfigPath);
    }

    // Attempt to establish connection with timeout
    $connectionStartTime = microtime(true);
    $con = getDbConnection();
    $connectionEndTime = microtime(true);
    $connectionTime = round(($connectionEndTime - $connectionStartTime) * 1000, 2);

    // Validate connection object
    if (!$con || !($con instanceof mysqli)) {
        $errorDetails = [
            'connection_type' => gettype($con),
            'mysqli_connect_error' => mysqli_connect_error(),
            'mysqli_connect_errno' => mysqli_connect_errno(),
            'connection_attempt_time_ms' => $connectionTime
        ];
        throw new Exception('Invalid database connection', 500);
    }

    // Perform basic connection health check
    $healthCheckQuery = $con->prepare('SELECT 1');
    if (!$healthCheckQuery) {
        throw new Exception('Failed to prepare health check query: ' . $con->error);
    }

    $healthCheckResult = $healthCheckQuery->execute();
    $healthCheckQuery->close();
    if (!$healthCheckResult) {
        throw new Exception('Database health check failed: ' . $healthCheckQuery->error);
    }
    // Log successful connection
    logError('Database Connection Established', [
        'connection_time_ms' => $connectionTime,
        'server_info' => $con->server_info,
        'host' => $con->host_info
    ]);
}

catch (Exception $e) {
   // Comprehensive error logging with extensive system context
    $errorContext = [
        'error_code' => $e->getCode(),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'system_info' => [
            'php_version' => PHP_VERSION,
            'os' => PHP_OS,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'server_api' => PHP_SAPI,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'current_user' => get_current_user()
        ],
        'include_paths' => [
            'include_path' => get_include_path(),
            'included_files' => array_slice(get_included_files(), 0, 10) // Limit to first 10 files
        ],
        'extensions' => [
            'loaded' => array_keys(get_loaded_extensions()),
            'mysqli_info' => extension_loaded('mysqli') ? mysqli_get_client_info() : 'Not Loaded'
        ]
    ];

    // Log detailed error information
    logError('Database Connection Critical Failure', $errorContext);
    // TEMP DEBUG: Show error context for troubleshooting
    echo '<pre>' . print_r($errorContext, true) . '</pre>';
    
    // User-friendly error response with minimal information
    http_response_code(500);
    die('A system error occurred. Our support team has been notified. Error Code: ' . uniqid());
}

// Create property_types table with comprehensive error handling
try {
    // Check if table exists, if not create it
    $tableCheckQuery = "SHOW TABLES LIKE 'property_types'";
    $tableCheckResult = $con->query($tableCheckQuery);
    
    if ($tableCheckResult->num_rows == 0) {
        // Table does not exist, create it
        $createTableSql = "CREATE TABLE property_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (!$con->query($createTableSql)) {
            throw new Exception('Failed to create property_types table: ' . $con->error);
        }
        
        logError('Property Types Table Created', [
            'table' => 'property_types',
            'status' => 'success'
        ]);
    }

    // Check if table is empty
    $countQuery = "SELECT COUNT(*) as count FROM property_types";
    $countResult = $con->query($countQuery);
    $count = $countResult ? $countResult->fetch_assoc()['count'] : 0;

    // Insert default property types if table is empty
    if ($count == 0) {
        $default_types = [
            ['type' => 'Residential', 'description' => 'Homes and apartments for living'],
            ['type' => 'Commercial', 'description' => 'Office spaces and business properties'],
            ['type' => 'Industrial', 'description' => 'Warehouses and manufacturing facilities'],
            ['type' => 'Land', 'description' => 'Undeveloped or agricultural land']
        ];

        // Prepare statement with error handling
        $stmt = $con->prepare("INSERT INTO property_types (type, description) VALUES (?, ?)");
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $con->error);
        }

        // Transaction for data insertion
        $con->begin_transaction();
        try {
            foreach ($default_types as $type) {
                $stmt->bind_param("ss", $type['type'], $type['description']);
                if (!$stmt->execute()) {
                    throw new Exception('Failed to insert property type: ' . $stmt->error);
                }
            }
            $con->commit();
            
            logError('Default Property Types Inserted', [
                'count' => count($default_types),
                'status' => 'success'
            ]);
        } catch (Exception $e) {
            $con->rollback();
            throw $e;
        }
    }
} catch (Exception $e) {
    // Log detailed error information
    logError('Property Types Table Error', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Display user-friendly error message
    die('Unable to set up property types. Our team has been notified.');
}
require_once __DIR__ . '/includes/csrf_protection.php';
require_once __DIR__ . '/includes/dashboard_utils.php';
// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db_connection.php';
// [Cascade fix] Wrapped session_start() to prevent duplicate session warnings.
$conn = getDbConnection();

// Include necessary files with error handling
$requiredFiles = [
    'config.php' => __DIR__ . '/../config/config.php',
    'db_connection.php' => __DIR__ . '/../includes/db_connection.php',
    'session_manager.php' => __DIR__ . '/../app/Services/SessionManager.php'
];

foreach ($requiredFiles as $fileName => $filePath) {
    if (!file_exists($filePath)) {
        logError('Missing Critical File', [
            'file' => $fileName,
            'path' => $filePath
        ]);
        die("Critical system file missing: $fileName");
    }
    require_once $filePath;
}

// Debug: Check if critical functions exist
$criticalFunctions = [
    'getDbConnection' => function_exists('getDbConnection'),
    'logError' => function_exists('logError')
];

$missingFunctions = array_filter($criticalFunctions, function($exists) { return !$exists; });
if (!empty($missingFunctions)) {
    logError('Missing Critical Functions', [
        'missing_functions' => array_keys($missingFunctions)
    ]);
    die('ERROR: Critical system functions are unavailable.');
}

// Create secure DB connection
try {
    $conn = getDbConnection();
    
    // Additional connection validation
    if (!$conn || !($conn instanceof mysqli)) {
        throw new Exception('Invalid database connection object');
    }
    
    // Set connection attributes for security and performance
    $conn->set_charset('utf8mb4');
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
} catch (Exception $e) {
    logError('Database Connection Failure', [
        'error_message' => $e->getMessage(),
        'error_code' => $e->getCode(),
        'trace' => $e->getTraceAsString()
    ]);
    die('Database connection failed. Please contact support.');
}

// Secure admin session management
try {
    // Session Configuration and Security Checks
    $sessionConfig = [
        'timeout_duration' => 1800, // 30 minutes
        'regenerate_interval' => 600 // Regenerate session ID every 10 minutes
    ];

    // Start or resume session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Session timeout check
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity']) > $sessionConfig['timeout_duration']) {
        // Session expired
        session_unset();
        session_destroy();
        logError('Session Timeout', [
            'username' => $_SESSION['admin_username'] ?? 'UNKNOWN',
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);
        header('Location: login.php?error=session_expired');
        exit();
    }

    // Periodic session ID regeneration for security
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > $sessionConfig['regenerate_interval']) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }

    // Update last activity timestamp
    $_SESSION['last_activity'] = time();

    // Comprehensive session validation
    $sessionValidation = [
        'is_logged_in' => isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true,
        'username' => $_SESSION['admin_username'] ?? 'NOT_SET',
        'role' => $_SESSION['admin_role'] ?? 'NOT_SET',
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ];

    // Log session details
    logError('Dashboard Session Validation', $sessionValidation);

    // Strict authentication check
    if (!$sessionValidation['is_logged_in'] || 
        $sessionValidation['role'] !== 'admin') {
        logError('Unauthorized Dashboard Access', $sessionValidation);
        header('Location: login.php?error=unauthorized');
        exit();
    }

    // Additional role-based access control
    $isAdminLoggedIn = true; // Confirmed by previous checks
    
    if (!$isAdminLoggedIn) {
        // Log unauthorized access attempt
        logError('Unauthorized Dashboard Access', [
            'session_data' => array_keys($_SESSION),
            'remote_ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ]);
        
        // Destroy potentially compromised session
        session_unset();
        session_destroy();
        
        // Redirect to login with clear message
        header('Location: login.php?error=unauthorized');
        exit();
    }
    
    // Optional: Additional session security checks
    $sessionLifetime = 1800; // 30 minutes
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionLifetime)) {
        // Session expired
        session_unset();
        session_destroy();
        header('Location: login.php?error=session_expired');
        exit();
    }
    
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
    
} catch (Exception $e) {
    logError('Session Management Error', [
        'error_message' => $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
    die('Session management failed. Please contact support.');
}

// Final database connection check
if (!$conn) {
    logError('Final Database Connection Check Failed', [
        'connection_status' => is_null($conn) ? 'Null Connection' : 'Invalid Connection'
    ]);
    die('Database connection could not be established.');
}

// Create required tables if they don't exist
$sql = "CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Error handling function for table creation
function createTableWithErrorHandling($conn, $tableName, $sqlQuery) {
    // Validate connection
    if (!$conn || $conn->connect_error) {
        error_log("Database connection error: " . ($conn ? $conn->connect_error : 'Unknown connection error'));
        return false;
    }

    // Trim and sanitize SQL query
    $sqlQuery = trim($sqlQuery);

    try {
        // Disable error reporting temporarily
        $previousErrorReporting = error_reporting(0);
        
        // Execute the query
        $result = $conn->query($sqlQuery);
        
        // Restore previous error reporting
        error_reporting($previousErrorReporting);

        // Check query execution
        if ($result === false) {
            throw new Exception("Failed to create table {$tableName}: " . $conn->error);
        }

        // Log successful table creation
        error_log("Successfully created/verified table: {$tableName}");
        return true;
    } catch (Exception $e) {
        // Log detailed error information
        error_log("Table Creation Error for {$tableName}: " . $e->getMessage());
        error_log("SQL Query: {$sqlQuery}");
        error_log('Full Exception Details: ' . print_r($e, true));

        // Optional: Additional error handling or notification mechanism
        // You might want to send an alert or take specific action here
        return false;
    }
}

// Property Types Table
$propertyTypesSql = "CREATE TABLE IF NOT EXISTS property_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(100) NOT NULL,
    description_text TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
createTableWithErrorHandling($conn, 'property_types', $propertyTypesSql);

// Bookings Table
$bookingsSql = "CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    user_id INT,
    status VARCHAR(50),
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES property_types(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
createTableWithErrorHandling($conn, 'bookings', $bookingsSql);

// Transactions Table
$transactionsSql = "CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
)";
createTableWithErrorHandling($conn, 'transactions', $transactionsSql);

$leadsSql = "CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact VARCHAR(100) NOT NULL,
    email VARCHAR(255),
    status ENUM('new', 'contacted', 'converted', 'lost') DEFAULT 'new',
    source VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_contact (contact),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
)";
createTableWithErrorHandling($conn, 'leads', $leadsSql);

// Create audit_access_log table if not exists
$auditLogSql = "CREATE TABLE IF NOT EXISTS audit_access_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    accessed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    action VARCHAR(50),
    user_id INT,
    ip_address VARCHAR(45),
    details TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";
createTableWithErrorHandling($conn, 'audit_access_log', $auditLogSql);


// Enhanced analytics functions
function getAnalytics($con) {
    $analytics = array();
    
    // Basic counts
    $analytics['total_projects'] = getTotalProjects($con);
    $analytics['total_properties'] = getTotalProperties($con);
    $analytics['total_bookings'] = getTotalBookings($con);
    $analytics['total_customers'] = getTotalCustomers($con);
    
    // Revenue metrics
    $analytics['total_revenue'] = getTotalRevenue($con);
    $analytics['monthly_revenue'] = getMonthlyRevenue($con);
    
    // Property metrics
    $analytics['property_types'] = getPropertyTypeDistribution($con);
    $analytics['booking_status'] = getBookingStatusDistribution($con);
    
    // Lead metrics
    $analytics['lead_conversion'] = getLeadConversionRate($con);
    $analytics['active_leads'] = getActiveLeadsCount($con);
    
    return $analytics;
}

function getTotalProjects($con) {
    $stmt = $con->prepare("SELECT COUNT(*) FROM projects");
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

function getTotalProperties($con) {
    $stmt = $con->prepare("SELECT COUNT(*) FROM property_types");
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

function getTotalBookings($con) {
    $stmt = $con->prepare("SELECT COUNT(*) FROM bookings");
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

function getTotalCustomers($con) {
    $stmt = $con->prepare("SELECT COUNT(*) FROM users WHERE type = 'customer'");
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

function getTotalRevenue($con) {
    // No 'status' column in transactions; sum all amounts
    $stmt = $con->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions");
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

function getMonthlyRevenue($con) {
    // No 'status' or 'date' column in transactions, use created_at
    $stmt = $con->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COALESCE(SUM(amount), 0) as revenue 
                         FROM transactions 
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) 
                         GROUP BY month 
                         ORDER BY month DESC");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getPropertyTypeDistribution($con) {
    $stmt = $con->prepare("SELECT type, COUNT(*) as count 
                         FROM property_types 
                         GROUP BY type");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getBookingStatusDistribution($con) {
    $stmt = $con->prepare("SELECT status, COUNT(*) as count 
                         FROM bookings 
                         GROUP BY status");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getLeadConversionRate($con) {
    $stmt = $con->prepare("SELECT 
                         (SELECT COUNT(*) FROM leads WHERE status = 'converted') as converted,
                         COUNT(*) as total 
                         FROM leads");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return ($result['total'] > 0) ? ($result['converted'] / $result['total'] * 100) : 0;
}

function getActiveLeadsCount($con) {
    $stmt = $con->prepare("SELECT COUNT(*) FROM leads WHERE status = 'active'");
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

// Upload/Notification Audit Analytics
$audit_summary = $conn->query("SELECT COUNT(*) as total, SUM(slack_status='sent') as slack_ok, SUM(slack_status!='sent') as slack_fail, SUM(telegram_status='sent') as telegram_ok, SUM(telegram_status!='sent') as telegram_fail FROM upload_audit_log WHERE created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')")->fetch_assoc();
$most_active = $conn->query("SELECT uploader, COUNT(*) as c FROM upload_audit_log WHERE created_at >= DATE_FORMAT(NOW(),'%Y-%m-01') GROUP BY uploader ORDER BY c DESC LIMIT 1")->fetch_assoc();

// Audit Access Analytics (this month)
$access = $conn->query("SELECT COUNT(*) as views, SUM(action='export') as exports, SUM(action='drilldown') as drilldowns FROM audit_access_log WHERE accessed_at >= DATE_FORMAT(NOW(),'%Y-%m-01')")->fetch_assoc();
$is_superadmin = isset($_SESSION['auser']) && $_SESSION['auser'] === 'superadmin';

// Use standardized admin header
include __DIR__ . '/../includes/templates/dynamic_header.php';
// MLM Commission Widget
include __DIR__ . '/dashboard_commission_widget.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>APS DREAM HOMES - Dashboard</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('aps.png', 'images'); ?>">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">

    <!-- Feathericon CSS -->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/feathericon.min.css', 'css'); ?>">

    <link rel="stylesheet" href="assets/plugins/morris/morris.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <!-- Main Wrapper -->
    <!-- /Header -->

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
					<br>
                        <h3 class="page-title">Welcome Admin!</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->

            <?php $analytics = getAnalytics($conn); ?>
            <div class="row">
                <!-- Monthly Revenue Chart -->
                <div class="col-xl-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <h5 class="card-title">मासिक राजस्व विश्लेषण</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Property Distribution Chart -->
                <div class="col-xl-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <h5 class="card-title">प्रॉपर्टी प्रकार वितरण</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="propertyChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Initialization -->
            <script>
            // Monthly Revenue Chart
            const revenueData = <?php echo json_encode($analytics['monthly_revenue']); ?>;
            const months = revenueData.map(item => item.month);
            const revenues = revenueData.map(item => item.revenue);

            new Chart(document.getElementById('revenueChart'), {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'मासिक राजस्व',
                        data: revenues,
                        borderColor: '#2196f3',
                        tension: 0.1,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'मासिक राजस्व ट्रेंड'
                        }
                    }
                }
            });

            // Property Distribution Chart
            const propertyData = <?php echo json_encode($analytics['property_types']); ?>;
            const propertyTypes = propertyData.map(item => item.type);
            const propertyCounts = propertyData.map(item => item.count);

            new Chart(document.getElementById('propertyChart'), {
                type: 'doughnut',
                data: {
                    labels: propertyTypes,
                    datasets: [{
                        data: propertyCounts,
                        backgroundColor: [
                            '#4CAF50',
                            '#2196F3', 
                            '#FFC107',
                            '#E91E63',
                            '#9C27B0'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        title: {
                            display: true,
                            text: 'प्रॉपर्टी प्रकार वितरण'
                        }
                    }
                }
            });
            </script>

            <div class="row">
                <!-- Revenue Overview -->
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-success">
                                    <i class="fe fe-money"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3>₹<?php echo number_format($analytics['total_revenue']); ?></h3>
                                <h6 class="text-muted">कुल राजस्व</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-success w-75"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Projects -->
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-info">
                                    <i class="fe fe-home"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo $analytics['total_projects']; ?></h3>
                                <h6 class="text-muted">कुल प्रोजेक्ट्स</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-info w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Leads -->
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-warning">
                                    <i class="fe fe-user-plus"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo $analytics['active_leads']; ?></h3>
                                <h6 class="text-muted">सक्रिय लीड्स</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-warning w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lead Conversion Rate -->
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-primary">
                                    <i class="fe fe-chart-bar"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo number_format($analytics['lead_conversion'], 1); ?>%</h3>
                                <h6 class="text-muted">लीड कन्वर्जन दर</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-primary" style="width: <?php echo $analytics['lead_conversion']; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-success">
                                    <i class="fe fe-users"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo countUsersByType($conn, 'agent'); ?></h3>
                                <h6 class="text-muted">Agents</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-success w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-danger">
                                    <i class="fe fe-user"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo countUsersByType($conn, 'builder'); ?></h3>
                                <h6 class="text-muted">Builders</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-danger w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-info">
                                    <i class="fe fe-home"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo countPropertiesByType($conn, ''); // Count all properties ?></h3>
                                <h6 class="text-muted">Properties</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-info w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Audit Overview -->
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon bg-info">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </span>
                            <div class="dash-count">
                                <h3><?= $audit_summary['total'] ?? 0 ?></h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Uploads This Month</h6>
                            <span class="text-success">Slack OK: <?= $audit_summary['slack_ok'] ?? 0 ?></span> |
                            <span class="text-danger">Slack Fail: <?= $audit_summary['slack_fail'] ?? 0 ?></span><br>
                            <span class="text-success">Telegram OK: <?= $audit_summary['telegram_ok'] ?? 0 ?></span> |
                            <span class="text-danger">Telegram Fail: <?= $audit_summary['telegram_fail'] ?? 0 ?></span>
                            <br>
                            <span class="text-muted">Top Uploader: <?= htmlspecialchars($most_active['uploader'] ?? '-') ?></span>
                            <br>
                            <a href="/admin/upload_audit_log_view.php" class="btn btn-link p-0">Full Audit Log &raquo;</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Access Overview (superadmin only) -->
            <?php if ($is_superadmin): ?>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon bg-warning">
                                <i class="fas fa-user-shield"></i>
                            </span>
                            <div class="dash-count">
                                <h3><?= $access['views'] ?? 0 ?></h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Audit Log Views (Month)</h6>
                            <span class="text-success">Exports: <?= $access['exports'] ?? 0 ?></span> |
                            <span class="text-info">Drilldowns: <?= $access['drilldowns'] ?? 0 ?></span>
                            <br>
                            <a href="/admin/audit_access_log_view.php" class="btn btn-link p-0">Access Log &raquo;</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="row">
                <!-- Additional Rows for Property Types -->
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-warning">
                                    <i class="fe fe-table"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo countPropertiesByType($conn, 'apartment'); ?></h3>
                                <h6 class="text-muted">No. of Apartments</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-warning w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-info">
                                    <i class="fe fe-home"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo countPropertiesByType($conn, 'house'); ?></h3>
                                <h6 class="text-muted">No. of Houses</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-info w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-secondary">
                                    <i class="fe fe-building"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo countPropertiesByType($conn, 'building'); ?></h3>
                                <h6 class="text-muted">No. of Buildings</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-secondary w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-primary">
                                    <i class="fe fe-tablet"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo countPropertiesByType($conn, 'flat'); ?></h3>
                                <h6 class="text-muted">No. of Flats</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-primary w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			
			

            <!-- Sales Overview and Order Status -->
            <div class="row">
                <div class="col-md-12 col-lg-6">
                    <div class="card card-chart">
                        <div class="card-header">
                            <h4 class="card-title">Sales Overview</h4>
                        </div>
                        <div class="card-body">
                            <div id="morrisArea"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-lg-6">
                    <div class="card card-chart">
                        <div class="card-header">
                            <h4 class="card-title">Order Status</h4>
                        </div>
                        <div class="card-body">
                            <div id="morrisLine"></div>
                        </div>
                    </div>
                </div>	
            </div>
        </div>			
    </div>
	
    <!-- /Page Wrapper -->

    <!-- jQuery -->
    <script src="<?php echo get_asset_url('js/jquery-3.2.1.min.js', 'js'); ?>"></script>

    <!-- Bootstrap Core JS -->
    <script src="<?php echo get_asset_url('js/popper.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script>

    <!-- Slimscroll JS -->
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <script src="assets/plugins/raphael/raphael.min.js"></script>    
    <script src="assets/plugins/morris/morris.min.js"></script>  
    <script src="<?php echo get_asset_url('js/chart.morris.js', 'js'); ?>"></script>

    <!-- Custom JS -->
    <script src="<?php echo get_asset_url('js/script.js', 'js'); ?>"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</html>

<!-- Duplicate/legacy dashboard code block removed for security and maintainability. All logic is now handled in the main dashboard logic above. -->
<!-- Duplicate/legacy dashboard code block removed for security and maintainability. All logic is now handled in the main dashboard logic above. -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">डैशबोर्ड</h1>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase">कुल प्रॉपर्टीज</h6>
                                        <h1 class="display-4"><?php echo $properties_count; ?></h1>
                                    </div>
                                    <i class="fas fa-home fa-3x"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a href="properties.php" class="text-white">विस्तृत देखें</a>
                                <i class="fas fa-angle-right"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase">कुल उपयोगकर्ता</h6>
                                        <h1 class="display-4"><?php echo $users_count; ?></h1>
                                    </div>
                                    <i class="fas fa-users fa-3x"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a href="users.php" class="text-white">विस्तृत देखें</a>
                                <i class="fas fa-angle-right"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card bg-warning text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase">कुल इंक्वायरीज</h6>
                                        <h1 class="display-4"><?php echo $inquiries_count; ?></h1>
                                    </div>
                                    <i class="fas fa-question-circle fa-3x"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a href="inquiries.php" class="text-white">विस्तृत देखें</a>
                                <i class="fas fa-angle-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                हाल ही की प्रॉपर्टीज
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>आईडी</th>
                                            <th>शीर्षक</th>
                                            <th>मूल्य</th>
                                            <th>स्थान</th>
                                            <th>कार्रवाई</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($recent_properties && $recent_properties instanceof mysqli_result && $recent_properties->num_rows > 0):
                                            while($property = $recent_properties->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($property['id']); ?></td>
                                            <td><?php echo htmlspecialchars($property['title']); ?></td>
                                            <td>₹<?php echo htmlspecialchars(number_format($property['price'])); ?></td>
                                            <td><?php echo htmlspecialchars($property['location']); ?></td>
                                            <td>
                                                <a href="edit_property.php?id=<?php echo urlencode($property['id']); ?>" class="btn btn-sm btn-primary">संपादित करें</a>
                                            </td>
                                        </tr>
                                        <?php endwhile; else: ?>
                                        <tr><td colspan="5">No data found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-user me-1"></i>
                                हाल ही के उपयोगकर्ता
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>आईडी</th>
                                            <th>उपयोगकर्ता नाम</th>
                                            <th>ईमेल</th>
                                            <th>भूमिका</th>
                                            <th>कार्रवाई</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($recent_users && $recent_users instanceof mysqli_result && $recent_users->num_rows > 0):
                                            while($user = $recent_users->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                                            <td>
                                                <a href="edit_user.php?id=<?php echo urlencode($user['id']); ?>" class="btn btn-sm btn-primary">संपादित करें</a>
                                            </td>
                                        </tr>
                                        <?php endwhile; else: ?>
                                        <tr><td colspan="5">No data found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/admin.js"></script>
</body>
</html>
