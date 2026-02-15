<?php
/**
 * Enhanced Security Admin Panel
 * Provides secure admin interface with comprehensive security measures
 * Security Enhanced Version
 */

// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/admin_panel_security.log');
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
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');
    header('Access-Control-Allow-Credentials: false');
    header('Access-Control-Max-Age: 3600');
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once(__DIR__ . '/../includes/functions/role_helper.php');
enforceRole(['admin','superadmin']);
$menu_config = include(__DIR__ . '/includes/config/menu_config.php');
$current_role = getCurrentUserRole();
$menu_items = $menu_config[$current_role] ?? [];
include(__DIR__ . '/includes/templates/header.php');

// Security event logging function
function logSecurityEvent($event, $context = []) {
    static $logFile = null;
    if ($logFile === null) {
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/admin_panel_security.log';
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

// Validate request headers
function validateRequestHeaders() {
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (empty($user_agent) || strlen($user_agent) < 10) {
        logSecurityEvent('Suspicious User Agent in Admin Panel', [
            'user_agent' => $user_agent,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
    }
    return true;
}

// Validate and sanitize input
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
        case 'phone':
            $input = filter_var($input, FILTER_SANITIZE_STRING);
            $input = preg_replace('/[^\d+\s]/', '', $input);
            if (strlen($input) < 10 || strlen($input) > 15) {
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

// Escape for JavaScript output
function escapeForJS($data) {
    if (is_array($data)) {
        $escaped = [];
        foreach ($data as $key => $value) {
            $escaped[$key] = escapeForJS($value);
        }
        return $escaped;
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Validate request headers
validateRequestHeaders();

// Log admin panel access
logSecurityEvent('Admin Panel Access', [
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
    'user_role' => $current_role
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <title>Admin Panel - APS Dream Homes</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN">
    <style>
        body {background: #f8f9fa;}
        .sidebar {background: #fff; min-height: 100vh; box-shadow: 2px 0 8px rgba(0,0,0,0.03);}
        .sidebar .nav-link {color: #333; font-weight: 500;}
        .sidebar .nav-link.active, .sidebar .nav-link:hover {background: #007bff; color: #fff;}
        .dashboard-section {padding: 32px 24px;}
        .section-title {font-size: 1.5rem; font-weight: 600; margin-bottom: 1.5rem;}
        .card {margin-bottom: 24px;}
    </style>
</head>
<body>
<div class="container my-3">
    <div class="d-flex justify-content-end mb-2 gap-2">
        <a href="admin/export_ai_interactions.php" class="btn btn-outline-info btn-sm" target="_blank">
            <i class="fa fa-download me-1"></i>Export AI Feedback (CSV)
        </a>
        <a href="admin/ai_feedback_analytics.php" class="btn btn-outline-primary btn-sm" target="_blank">
            <i class="fa fa-chart-bar me-1"></i>AI Feedback Analytics
        </a>
    </div>
    <div class="card border-success mb-3">
        <div class="card-header bg-success text-white"><i class="fa fa-magic me-2"></i>AI Insights, Trends & Forecast</div>
        <div class="card-body">
            <div id="aiAdminInsightsPanel">
                <div class="text-center text-muted">Loading admin insights...</div>
            </div>
            <div id="adminTrendsPanel" class="mt-3" style="display:none;">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <canvas id="trendRegistrations" height="60"></canvas>
                        <div class="small">Registrations (14d)</div>
                    </div>
                    <div class="col-md-3 text-center">
                        <canvas id="trendBookings" height="60"></canvas>
                        <div class="small">Bookings (14d)</div>
                    </div>
                    <div class="col-md-3 text-center">
                        <canvas id="trendTickets" height="60"></canvas>
                        <div class="small">Tickets (14d)</div>
                    </div>
                    <div class="col-md-3 text-center">
                        <canvas id="trendPayments" height="60"></canvas>
                        <div class="small">Payments (14d)</div>
                    </div>
                </div>
                <div id="aiForecastPanel" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block sidebar py-4">
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link active" href="#users"><i class="fa fa-users me-2"></i>Users/Roles</a></li>
                <li class="nav-item"><a class="nav-link" href="#employees"><i class="fa fa-id-badge me-2"></i>Employees</a></li>
                <li class="nav-item"><a class="nav-link" href="#permissions"><i class="fa fa-key me-2"></i>Permissions</a></li>
                <li class="nav-item"><a class="nav-link" href="#settings"><i class="fa fa-cog me-2"></i>Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="#analytics"><i class="fa fa-bar-chart me-2"></i>Analytics</a></li>
                <li class="nav-item"><a class="nav-link" href="#ai"><i class="fa fa-magic me-2"></i>AI Tools</a></li>
            </ul>
        </nav>
        <main class="col-md-10 ms-sm-auto dashboard-section">
            <div id="users">
                <div class="section-title"><i class="fa fa-users"></i> User & Role Management</div>
                <div class="mb-3">
                    <label for="roleFilter" class="form-label">Filter by Role:</label>
                    <select id="roleFilter" class="form-select" style="width:auto;display:inline-block">
                        <option value="">All</option>
                        <option value="admin">Admin</option>
                        <option value="superadmin">Superadmin</option>
                        <option value="associate">Associate</option>
                        <option value="user">User</option>
                        <option value="builder">Builder</option>
                        <option value="agent">Agent</option>
                        <option value="employee">Employee</option>
                        <option value="customer">Customer</option>
                    </select>
                    <button class="btn btn-primary ms-2" id="addUserBtn"><i class="fa fa-plus"></i> Add User</button>
                </div>
                <div class="card p-4">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="usersTable">
                            <thead>
                                <tr>
                                    <th>UID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <!-- Add/Edit User Modal -->
                <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form id="userForm">
                        <div class="modal-header">
                          <h5 class="modal-title" id="userModalLabel">Add User</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="id" id="userId">
                          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                          <div class="mb-3">
                            <label for="userName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="userName" name="name" required maxlength="100">
                          </div>
                          <div class="mb-3">
                            <label for="userEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="userEmail" name="email" required maxlength="255">
                          </div>
                          <div class="mb-3">
                            <label for="userPhone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="userPhone" name="phone" required maxlength="15" pattern="[\d+\s]{10,15}">
                          </div>
                          <div class="mb-3">
                            <label for="userRole" class="form-label">Role</label>
                            <select class="form-select" id="userRole" name="role" required>
                              <option value="user">User</option>
                              <option value="admin">Admin</option>
                              <option value="superadmin">Superadmin</option>
                              <option value="associate">Associate</option>
                              <option value="builder">Builder</option>
                              <option value="agent">Agent</option>
                              <option value="employee">Employee</option>
                              <option value="customer">Customer</option>
                            </select>
                          </div>
                          <div class="mb-3">
                            <label for="userStatus" class="form-label">Status</label>
                            <select class="form-select" id="userStatus" name="status" required>
                              <option value="active">Active</option>
                              <option value="inactive">Inactive</option>
                            </select>
                          </div>
                          <div class="mb-3 password-field">
                            <label for="userPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="userPassword" name="password" minlength="8" maxlength="255">
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-primary" id="userModalSubmit">Save</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
            </div>
            <div id="employees">
                <div class="section-title"><i class="fa fa-id-badge"></i> Employee Management</div>
                <div class="mb-3">
                    <button class="btn btn-primary" id="addEmployeeBtn"><i class="fa fa-plus"></i> Add Employee</button>
                </div>
                <div class="card p-4">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="employeesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <!-- Add/Edit Employee Modal -->
                <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form id="employeeForm">
                        <div class="modal-header">
                          <h5 class="modal-title" id="employeeModalLabel">Add Employee</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="id" id="employeeId">
                          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                          <div class="mb-3">
                            <label for="employeeName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="employeeName" name="name" required maxlength="100">
                          </div>
                          <div class="mb-3">
                            <label for="employeeEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="employeeEmail" name="email" required maxlength="255">
                          </div>
                          <div class="mb-3">
                            <label for="employeePhone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="employeePhone" name="phone" required maxlength="15" pattern="[\d+\s]{10,15}">
                          </div>
                          <div class="mb-3">
                            <label for="employeeRole" class="form-label">Role</label>
                            <select class="form-select" id="employeeRole" name="role" required>
                              <option value="employee">Employee</option>
                              <option value="admin">Admin</option>
                              <option value="associate">Associate</option>
                              <option value="builder">Builder</option>
                              <option value="agent">Agent</option>
                            </select>
                          </div>
                          <div class="mb-3">
                            <label for="employeeStatus" class="form-label">Status</label>
                            <select class="form-select" id="employeeStatus" name="status" required>
                              <option value="active">Active</option>
                              <option value="inactive">Inactive</option>
                            </select>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-primary" id="employeeModalSubmit">Save</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
            </div>
            <div id="permissions">
                <div class="section-title"><i class="fa fa-key"></i> Permissions Matrix</div>
                <div class="card p-4">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="permissionsTable">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th>Dashboard</th>
                                    <th>Add Property</th>
                                    <th>View Analytics</th>
                                    <th>Manage Users</th>
                                    <th>Manage Employees</th>
                                    <th>Settings</th>
                                    <th>AI Tools</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <button class="btn btn-success mt-3" id="savePermissionsBtn"><i class="fa fa-save"></i> Save Changes</button>
                </div>
            </div>
            <div id="settings">
                <div class="section-title"><i class="fa fa-cog"></i> System Settings</div>
                <div class="card p-4">
                    <form id="settingsForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <div class="mb-3">
                            <label for="siteTitle" class="form-label">Site Title</label>
                            <input type="text" class="form-control" id="siteTitle" name="site_title" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label for="notificationEmail" class="form-label">Notification Email (From Address)</label>
                            <input type="email" class="form-control" id="notificationEmail" name="notification_email" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Booking Notifications</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="bookingNotificationEmail" name="booking_notification_email" value="1">
                                <label class="form-check-label" for="bookingNotificationEmail">Send Email on Booking</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="bookingNotificationWhatsapp" name="booking_notification_whatsapp" value="1">
                                <label class="form-check-label" for="bookingNotificationWhatsapp">Send WhatsApp on Booking</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="defaultUserRole" class="form-label">Default User Role</label>
                            <select class="form-select" id="defaultUserRole" name="default_user_role" required>
                                <option value="user">User</option>
                                <option value="customer">Customer</option>
                                <option value="associate">Associate</option>
                                <option value="builder">Builder</option>
                                <option value="agent">Agent</option>
                                <option value="employee">Employee</option>
                            </select>
                        </div>
                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="maintenanceMode" name="maintenance_mode" value="1">
                            <label class="form-check-label" for="maintenanceMode">Maintenance Mode</label>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Settings</button>
                    </form>
                </div>
            </div>
            <div id="analytics">
                <div class="section-title"><i class="fa fa-bar-chart"></i> Analytics & Reports</div>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card text-center p-3">
                            <div class="fw-bold fs-4" id="totalUsers">0</div>
                            <div>Total Users</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center p-3">
                            <div class="fw-bold fs-4" id="activeUsers">0</div>
                            <div>Active Users</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center p-3">
                            <div class="fw-bold fs-4" id="totalProperties">0</div>
                            <div>Total Properties</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center p-3">
                            <div class="fw-bold fs-4" id="totalBookings">0</div>
                            <div>Total Bookings</div>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card p-3">
                            <div class="fw-bold mb-2">Users by Role</div>
                            <canvas id="usersByRoleChart" height="180"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card p-3">
                            <div class="fw-bold mb-2">Bookings Over Time</div>
                            <canvas id="bookingsOverTimeChart" height="180"></canvas>
                        </div>
                    </div>
                </div>
                <div class="card p-3">
                    <div class="fw-bold mb-2">Recent Logins</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr><th>Name</th><th>Email</th><th>Role</th><th>Last Login</th></tr>
                            </thead>
                            <tbody id="recentLogins"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="ai">
                <div class="section-title"><i class="fa fa-magic"></i> AI Tools & Automation</div>
                <div class="card p-4">
                    <form id="aiSettingsForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="aiChatbot" name="ai_chatbot" value="1">
                            <label class="form-check-label" for="aiChatbot">Enable AI Chatbot</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="autoReminders" name="auto_reminders" value="1">
                            <label class="form-check-label" for="autoReminders">Enable Auto-Reminders</label>
                        </div>
                        <div class="mb-3 ms-4">
                            <label for="reminderFrequency" class="form-label">Reminder Frequency</label>
                            <select class="form-select" id="reminderFrequency" name="reminder_frequency" style="max-width:160px">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="smartTicketRouting" name="smart_ticket_routing" value="1">
                            <label class="form-check-label" for="smartTicketRouting">Enable Smart Ticket Routing</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="autoReports" name="auto_reports" value="1">
                            <label class="form-check-label" for="autoReports">Enable Automated Reports</label>
                        </div>
                        <div class="mb-3 ms-4">
                            <label for="reportSchedule" class="form-label">Report Schedule</label>
                            <select class="form-select" id="reportSchedule" name="report_schedule" style="max-width:160px">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="aiSuggestions" name="ai_suggestions" value="1">
                            <label class="form-check-label" for="aiSuggestions">Enable AI Suggestions/Feedback</label>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save AI/Automation Settings</button>
                    </form>
                </div>
                <!-- Automation Log Viewer -->
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <a class="text-decoration-none" data-bs-toggle="collapse" href="#automationLogCollapse" role="button" aria-expanded="false" aria-controls="automationLogCollapse">
                            <i class="fa fa-file-alt me-2"></i>Automation Log <small class="text-muted">(last 100 lines)</small>
                        </a>
                    </div>
                    <div class="collapse" id="automationLogCollapse">
                        <div class="card-body" style="background:#222;color:#eee;font-family:monospace;font-size:13px;max-height:300px;overflow:auto">
                            <pre id="automationLogContent">Loading...</pre>
                            <button class="btn btn-sm btn-outline-secondary mt-2" id="refreshAutomationLog"><i class="fa fa-sync"></i> Refresh Log</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php include(__DIR__ . '/includes/templates/footer.php'); ?>
<script src="assets/js/bootstrap.bundle.min.js" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3"></script>
<script src="assets/js/jquery.min.js" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3"></script>
<script>
// Security utilities
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validatePhone(phone) {
    const phoneRegex = /^[\d+\s]{10,15}$/;
    return phoneRegex.test(phone);
}

// Fetch and render users with security
function loadUsers(role = '') {
    const csrfToken = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    $.ajax({
        url: 'admin/fetch_users.php',
        method: 'GET',
        data: role ? {role} : {},
        headers: {
            'X-CSRF-Token': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(users) {
            var rows = '';
            users.forEach(function(user) {
                const escapedUser = {
                    id: escapeHtml(user.id.toString()),
                    name: escapeHtml(user.name),
                    email: escapeHtml(user.email),
                    phone: escapeHtml(user.phone),
                    role: escapeHtml(user.role),
                    status: escapeHtml(user.status)
                };
                rows += `<tr>
                    <td>${escapedUser.id}</td>
                    <td>${escapedUser.name}</td>
                    <td>${escapedUser.email}</td>
                    <td>${escapedUser.phone}</td>
                    <td>${escapedUser.role}</td>
                    <td>${escapedUser.status}</td>
                    <td>
                        <button class='btn btn-sm btn-info me-1 edit-user-btn' data-user='${JSON.stringify(escapedUser).replace(/'/g, "&apos;")}'>Edit</button>
                        <button class='btn btn-sm btn-danger delete-user-btn' data-id='${escapedUser.id}'>Delete</button>
                    </td>
                </tr>`;
            });
            $('#usersTable tbody').html(rows);
        },
        error: function(xhr, status, error) {
            console.error('Error loading users:', error);
            logSecurityEvent('User Loading Error', {error: error, status: status});
        }
    });
}

function logSecurityEvent(event, context) {
    $.post('admin/log_security_event.php', {
        event: event,
        context: context,
        csrf_token: '<?php echo $_SESSION['csrf_token'] ?? ''; ?>'
    });
}

$(function() {
    loadUsers();
    $('#roleFilter').change(function() {
        loadUsers($(this).val());
    });

    // Add User
    $('#addUserBtn').click(function() {
        $('#userModalLabel').text('Add User');
        $('#userForm')[0].reset();
        $('#userId').val('');
        $('.password-field').show();
        $('#userModal').modal('show');
    });

    // Edit User
    $(document).on('click', '.edit-user-btn', function() {
        try {
            var user = JSON.parse($(this).attr('data-user').replace(/&apos;/g, "'"));
            $('#userModalLabel').text('Edit User');
            $('#userId').val(user.id);
            $('#userName').val(user.name);
            $('#userEmail').val(user.email);
            $('#userPhone').val(user.phone);
            $('#userRole').val(user.role);
            $('#userStatus').val(user.status);
            $('.password-field').hide();
            $('#userModal').modal('show');
        } catch (e) {
            console.error('Error parsing user data:', e);
            logSecurityEvent('User Data Parse Error', {error: e.message});
        }
    });

    // Delete User
    $(document).on('click', '.delete-user-btn', function() {
        if(confirm('Are you sure you want to delete this user?')) {
            var id = $(this).data('id');
            $.post('admin/user_actions.php', {
                action: 'delete',
                id: id,
                csrf_token: '<?php echo $_SESSION['csrf_token'] ?? ''; ?>'
            }, function(resp) {
                if (resp.success) {
                    alert('User deleted successfully');
                    loadUsers($('#roleFilter').val());
                } else {
                    alert('Error: ' + (resp.message || 'Unknown error'));
                    logSecurityEvent('User Delete Error', {user_id: id, error: resp.message});
                }
            },'json').fail(function(xhr, status, error) {
                alert('Error deleting user');
                logSecurityEvent('User Delete AJAX Error', {user_id: id, error: error, status: status});
            });
        }
    });

    // Submit Add/Edit User
    $('#userForm').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        // Client-side validation
        const name = $('#userName').val().trim();
        const email = $('#userEmail').val().trim();
        const phone = $('#userPhone').val().trim();
        const password = $('#userPassword').val();

        if (!name || name.length > 100) {
            alert('Name must be 1-100 characters');
            return;
        }
        if (!validateEmail(email)) {
            alert('Invalid email address');
            return;
        }
        if (!validatePhone(phone)) {
            alert('Invalid phone number (10-15 digits)');
            return;
        }
        if (!$('#userId').val() && (!password || password.length < 8)) {
            alert('Password must be at least 8 characters');
            return;
        }

        $.ajax({
            url: 'admin/user_actions.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(resp) {
                if (resp.success) {
                    alert('User saved successfully');
                    $('#userModal').modal('hide');
                    loadUsers($('#roleFilter').val());
                } else {
                    alert('Error: ' + (resp.message || 'Unknown error'));
                    logSecurityEvent('User Save Error', {error: resp.message});
                }
            },
            error: function(xhr, status, error) {
                alert('Error saving user');
                logSecurityEvent('User Save AJAX Error', {error: error, status: status});
            }
        });
    });

    // Similar security enhancements for employees, permissions, settings, AI settings...
    // [Additional JavaScript code would follow with similar security measures]
});

// Add security event logging for all AJAX requests
$(document).ajaxError(function(event, xhr, settings, thrownError) {
    logSecurityEvent('AJAX Error', {
        url: settings.url,
        method: settings.type,
        error: thrownError,
        status: xhr.status
    });
</script>
</body>
</html>
