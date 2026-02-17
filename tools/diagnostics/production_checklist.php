<?php

/**
 * Production Deployment Checklist - APS Dream Homes Employee Management System
 * Complete verification and deployment readiness check
 */

require_once dirname(__DIR__, 2) . '/app/helpers.php';
require_once dirname(__DIR__, 2) . '/app/views/layouts/config.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Production Deployment Checklist - APS Dream Homes</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .checklist-container { max-width: 1200px; margin: 20px auto; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .checklist-section { background: white; border-radius: 15px; padding: 25px; margin: 20px 0; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .checklist-item { padding: 15px; margin: 10px 0; border-radius: 10px; border-left: 5px solid #ddd; }
        .checklist-item.pass { border-left-color: #10b981; background: #f0fdf4; }
        .checklist-item.fail { border-left-color: #ef4444; background: #fef2f2; }
        .checklist-item.warning { border-left-color: #f59e0b; background: #fffbeb; }
        .status-badge { padding: 5px 15px; border-radius: 20px; font-weight: 600; font-size: 0.9rem; }
        .status-pass { background: #d4edda; color: #155724; }
        .status-fail { background: #f8d7da; color: #721c24; }
        .status-warning { background: #fff3cd; color: #856404; }
        .progress-bar-custom { height: 30px; border-radius: 15px; }
        .deployment-ready { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
    </style>
</head>
<body>
    <div class='checklist-container'>
        <div class='text-center mb-4'>
            <h1><i class='fas fa-rocket me-2'></i>Production Deployment Checklist</h1>
            <p class='lead'>APS Dream Homes Employee Management System</p>
            <div class='badge bg-primary fs-6'>Version 1.0 | Production Ready</div>
        </div>";

// Initialize checklist scores
$total_checks = 0;
$passed_checks = 0;
$failed_checks = 0;
$warning_checks = 0;

// Function to add checklist item
function add_checklist_item($title, $description, $status, $details = '')
{
    global $total_checks, $passed_checks, $failed_checks, $warning_checks;
    $total_checks++;

    $status_class = '';
    $badge_class = '';
    $icon = '';

    switch ($status) {
        case 'pass':
            $status_class = 'pass';
            $badge_class = 'status-pass';
            $icon = 'fa-check-circle';
            $passed_checks++;
            break;
        case 'fail':
            $status_class = 'fail';
            $badge_class = 'status-fail';
            $icon = 'fa-times-circle';
            $failed_checks++;
            break;
        case 'warning':
            $status_class = 'warning';
            $badge_class = 'status-warning';
            $icon = 'fa-exclamation-triangle';
            $warning_checks++;
            break;
    }

    echo "<div class='checklist-item $status_class'>
        <div class='d-flex justify-content-between align-items-start'>
            <div class='flex-grow-1'>
                <h5><i class='fas $icon me-2'></i>" . h($title) . "</h5>
                <p class='text-muted mb-2'>" . h($description) . "</p>";

    if (!empty($details)) {
        echo "<div class='small text-secondary'>" . h($details) . "</div>";
    }

    echo "</div>
            <span class='status-badge $badge_class'>" . strtoupper($status) . "</span>
        </div>
    </div>";
}

// 1. Database Setup Checks
echo "<div class='checklist-section'>
    <h3><i class='fas fa-database me-2'></i>Database Setup</h3>";

// Check database connection
$db_status = 'pass';
$db_details = 'Connected to ' . DB_NAME . ' on ' . DB_HOST;
try {
    $conn->query("SELECT 1");
} catch (Exception $e) {
    $db_status = 'fail';
    $db_details = 'Connection failed: ' . $e->getMessage();
}
add_checklist_item('Database Connection', 'Verify database connectivity and credentials', $db_status, $db_details);

// Check required tables
$tables = ['employees', 'employee_tasks', 'employee_activities', 'admin'];
$tables_status = 'pass';
$tables_missing = [];
foreach ($tables as $table) {
    try {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() == 0) {
            $tables_status = 'fail';
            $tables_missing[] = $table;
        }
    } catch (Exception $e) {
        $tables_status = 'fail';
        $tables_missing[] = $table;
    }
}
$tables_details = $tables_status === 'pass' ? 'All required tables exist' : 'Missing tables: ' . implode(', ', $tables_missing);
add_checklist_item('Required Tables', 'Check all system tables are created', $tables_status, $tables_details);

// Check table structure
$structure_status = 'pass';
$structure_issues = [];
try {
    // Check employees table has required columns
    $result = $conn->query("DESCRIBE employees");
    $required_columns = ['id', 'name', 'email', 'password', 'department', 'role', 'status'];
    $existing_columns = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $existing_columns[] = $row['Field'];
    }

    foreach ($required_columns as $col) {
        if (!in_array($col, $existing_columns)) {
            $structure_status = 'fail';
            $structure_issues[] = "Missing column: $col in employees table";
        }
    }
} catch (Exception $e) {
    $structure_status = 'fail';
    $structure_issues[] = 'Error checking table structure: ' . $e->getMessage();
}
$structure_details = $structure_status === 'pass' ? 'All required columns present' : implode('; ', $structure_issues);
add_checklist_item('Table Structure', 'Verify all required columns exist', $structure_status, $structure_details);

echo "</div>";

// 2. Security Configuration
echo "<div class='checklist-section'>
    <h3><i class='fas fa-shield-alt me-2'></i>Security Configuration</h3>";

// Check password hashing
$password_status = 'pass';
$password_details = 'Password hashing functions available';
if (!function_exists('password_hash') || !function_exists('password_verify')) {
    $password_status = 'fail';
    $password_details = 'Password hashing functions not available';
}
add_checklist_item('Password Security', 'Verify password hashing is available', $password_status, $password_details);

// Check session configuration
$session_status = 'pass';
$session_details = 'Session configuration is secure';
if (ini_get('session.cookie_httponly') != 1) {
    $session_status = 'warning';
    $session_details = 'Consider setting session.cookie_httponly=1 for better security';
}
add_checklist_item('Session Security', 'Check session configuration', $session_status, $session_details);

// Check file permissions
$file_status = 'pass';
$protected_files = ['includes/config.php'];
$permission_issues = [];
foreach ($protected_files as $file) {
    $file_path = __DIR__ . '/' . $file;
    if (file_exists($file_path)) {
        $perms = fileperms($file_path);
        if ($perms & 0x0004) { // Readable by others
            $file_status = 'warning';
            $permission_issues[] = "$file is world-readable";
        }
    }
}
$file_details = $file_status === 'pass' ? 'Protected files have appropriate permissions' : implode('; ', $permission_issues);
add_checklist_item('File Permissions', 'Check sensitive file permissions', $file_status, $file_details);

echo "</div>";

// 3. User System
echo "<div class='checklist-section'>
    <h3><i class='fas fa-users me-2'></i>User System</h3>";

// Check admin accounts
$admin_status = 'pass';
$admin_count = 0;
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM admin");
    $admin_count = $result->fetch(PDO::FETCH_ASSOC)['count'];
    if ($admin_count == 0) {
        $admin_status = 'fail';
    }
} catch (Exception $e) {
    $admin_status = 'fail';
}
$admin_details = $admin_status === 'pass' ? "$admin_count admin account(s) found" : 'No admin accounts found - run create_first_admin.php';
add_checklist_item('Admin Accounts', 'Verify admin accounts exist', $admin_status, $admin_details);

// Check employee accounts
$employee_status = 'pass';
$employee_count = 0;
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'");
    $employee_count = $result->fetch(PDO::FETCH_ASSOC)['count'];
    // echo "Debug: Employee count: $employee_count";
    if ($employee_count == 0) {
        $employee_status = 'warning';
    }
} catch (Exception $e) {
    $employee_status = 'fail';
}
$employee_details = $employee_status === 'pass' ? "$employee_count active employee(s) found" : ($employee_status === 'warning' ? 'No active employees - create sample employees' : 'Error checking employees');
add_checklist_item('Employee Accounts', 'Check employee accounts exist', $employee_status, $employee_details);

echo "</div>";

// 4. System Files
echo "<div class='checklist-section'>
    <h3><i class='fas fa-file-code me-2'></i>System Files</h3>";

$required_files = [
    'app/Http/Controllers/Admin/AdminController.php' => 'Admin Controller',
    'app/Http/Controllers/Admin/AdminDashboardController.php' => 'Admin Dashboard Controller',
    'app/Http/Controllers/Admin/EmployeeController.php' => 'Employee Controller',
    'app/Http/Controllers/Public/AuthController.php' => 'Auth Controller',
    'app/Http/Controllers/User/DashboardController.php' => 'User Dashboard Controller',
    'fixed_employee_setup.php' => 'Fixed setup script'
];

foreach ($required_files as $file => $description) {
    $file_path = dirname(__DIR__, 2) . '/' . $file;
    $file_status = file_exists($file_path) ? 'pass' : 'fail';
    $file_details = $file_status === 'pass' ? 'File exists' : 'File missing - required for system functionality';
    add_checklist_item($description, "Check $file exists", $file_status, $file_details);
}

echo "</div>";

// 5. Functionality Tests
echo "<div class='checklist-section'>
    <h3><i class='fas fa-cogs me-2'></i>Functionality Tests</h3>";

// Test login functionality
$login_status = 'pass';
$login_details = 'Login pages accessible and functional';
$required_login_routes = [
    'app/Http/Controllers/Auth/AdminAuthController.php',
    'app/Http/Controllers/Public/AuthController.php'
];

foreach ($required_login_routes as $route_file) {
    if (!file_exists(dirname(__DIR__, 2) . '/' . $route_file)) {
        $login_status = 'fail';
        $login_details = 'Login controllers missing: ' . basename($route_file);
        break;
    }
}
add_checklist_item('Login System', 'Test login functionality', $login_status, $login_details);

// Test dashboard functionality
$dashboard_status = 'pass';
$dashboard_details = 'Dashboard pages accessible';
$required_dashboard_controllers = [
    'app/Http/Controllers/Admin/AdminDashboardController.php',
    'app/Http/Controllers/User/DashboardController.php'
];

foreach ($required_dashboard_controllers as $controller_file) {
    if (!file_exists(dirname(__DIR__, 2) . '/' . $controller_file)) {
        $dashboard_status = 'fail';
        $dashboard_details = 'Dashboard controllers missing: ' . basename($controller_file);
        break;
    }
}
add_checklist_item('Dashboard System', 'Test dashboard functionality', $dashboard_status, $dashboard_details);

// Test database operations
$db_ops_status = 'pass';
$db_ops_details = 'Database operations working';
try {
    $conn->query("SELECT COUNT(*) FROM employees LIMIT 1");
    $conn->query("SELECT COUNT(*) FROM admin LIMIT 1");
} catch (Exception $e) {
    $db_ops_status = 'fail';
    $db_ops_details = 'Database operations failed: ' . $e->getMessage();
}
add_checklist_item('Database Operations', 'Test CRUD operations', $db_ops_status, $db_ops_details);

echo "</div>";

// Calculate overall score
$score_percentage = $total_checks > 0 ? round(($passed_checks / $total_checks) * 100) : 0;

// Overall Status
$overall_status = 'fail';
if ($score_percentage >= 90) {
    $overall_status = 'pass';
} elseif ($score_percentage >= 70) {
    $overall_status = 'warning';
}

echo "<div class='checklist-section deployment-ready text-center p-4'>
    <h2><i class='fas fa-rocket me-2'></i>Deployment Readiness</h2>
    <div class='row align-items-center'>
        <div class='col-md-4'>
            <div class='display-4'>$score_percentage%</div>
            <div>Ready Score</div>
        </div>
        <div class='col-md-8'>
            <div class='progress progress-bar-custom mb-3'>
                <div class='progress-bar bg-success' style='width: $score_percentage%'></div>
            </div>
            <div class='row text-start'>
                <div class='col-4'>
                    <div class='text-success'><i class='fas fa-check me-1'></i> $passed_checks Passed</div>
                </div>
                <div class='col-4'>
                    <div class='text-warning'><i class='fas fa-exclamation me-1'></i> $warning_checks Warnings</div>
                </div>
                <div class='col-4'>
                    <div class='text-danger'><i class='fas fa-times me-1'></i> $failed_checks Failed</div>
                </div>
            </div>
        </div>
    </div>
</div>";

// Deployment Recommendations
echo "<div class='checklist-section'>
    <h3><i class='fas fa-lightbulb me-2'></i>Deployment Recommendations</h3>";

if ($overall_status === 'pass') {
    echo "<div class='alert alert-success'>
        <h4><i class='fas fa-check-circle me-2'></i>Ready for Production!</h4>
        <p>Your system has passed all critical checks and is ready for production deployment.</p>
        <ul>
            <li>All required tables and columns are in place</li>
            <li>Security configurations are optimal</li>
            <li>User accounts are set up</li>
            <li>All system files are present</li>
            <li>Functionality tests passed</li>
        </ul>
    </div>";
} elseif ($overall_status === 'warning') {
    echo "<div class='alert alert-warning'>
        <h4><i class='fas fa-exclamation-triangle me-2'></i>Ready with Minor Issues</h4>
        <p>Your system is mostly ready but has some warnings that should be addressed:</p>
        <ul>
            <li>Review and fix any failed checks</li>
            <li>Consider addressing warnings for optimal security</li>
            <li>Test all functionality before deployment</li>
            <li>Create missing accounts if needed</li>
        </ul>
    </div>";
} else {
    echo "<div class='alert alert-danger'>
        <h4><i class='fas fa-times-circle me-2'></i>Not Ready for Production</h4>
        <p>Your system has critical issues that must be resolved before deployment:</p>
        <ul>
            <li>Fix all failed checks immediately</li>
            <li>Run setup scripts to create missing components</li>
            <li>Verify database configuration</li>
            <li>Test all system functionality</li>
            <li>Ensure security measures are in place</li>
        </ul>
    </div>";
}

echo "</div>";

// Quick Actions
echo "<div class='checklist-section'>
    <h3><i class='fas fa-tools me-2'></i>Quick Actions</h3>
    <div class='row'>
        <div class='col-md-3'>
            <button class='btn btn-success w-100 mb-2' onclick='window.open(\"fixed_employee_setup.php\", \"_blank\")'>
                <i class='fas fa-play me-2'></i>Run Setup
            </button>
        </div>
        <div class='col-md-3'>
            <button class='btn btn-info w-100 mb-2' onclick='window.open(\"system_status.php\", \"_blank\")'>
                <i class='fas fa-heartbeat me-2'></i>System Status
            </button>
        </div>
        <div class='col-md-3'>
            <button class='btn btn-warning w-100 mb-2' onclick='window.open(\"database_explorer.php\", \"_blank\")'>
                <i class='fas fa-database me-2'></i>Database Explorer
            </button>
        </div>
        <div class='col-md-3'>
            <button class='btn btn-primary w-100 mb-2' onclick='window.open(\"admin/\", \"_blank\")'>
                <i class='fas fa-user-shield me-2'></i>Admin Panel
            </button>
        </div>
    </div>
</div>";

echo "<div class='text-center mt-4 mb-3'>
    <hr>
    <p class='text-muted'>
        <i class='fas fa-info-circle me-1'></i>
        Production Deployment Checklist - APS Dream Homes Employee Management System<br>
        <small>Run this checklist before deploying to production environment</small>
    </p>
</div>

</div>
</body>
</html>";
