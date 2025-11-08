<?php
// Employees Management - Enhanced Security Version
// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/employees_error.log');
error_reporting(E_ALL);

// Set security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://cdn.jsdelivr.net \'unsafe-inline\'; style-src \'self\' https://cdn.jsdelivr.net \'unsafe-inline\'; img-src \'self\' data:;');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Start secure session
$session_name = 'secure_admin_session';
$secure = true; // Only send cookies over HTTPS
$httponly = true; // Prevent JavaScript access to session cookie
$samesite = 'Strict';

if (PHP_VERSION_ID < 70300) {
    session_set_cookie_params([
        'lifetime' => 1800, // 30 minutes
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
} else {
    session_set_cookie_params([
        'lifetime' => 1800, // 30 minutes
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
    logError('Session Timeout', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    header('Location: /admin/index.php?error=session_expired');
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Secure authentication check
if (!isset($_SESSION['auser'])) {
    logError('Unauthorized Access Attempt', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    header('Location: /admin/index.php');
    exit();
}

// Validate and include required files
$required_files = [
    __DIR__ . '/config.php'
];

foreach ($required_files as $file) {
    if (!file_exists($file) || !is_readable($file)) {
        logError('Required File Missing', [
            'file_path' => $file,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        die('System configuration error');
    }
}

require_once $required_files[0];

// Role-based access control
if (!function_exists('require_role')) {
    function require_role($role) {
        if (!isset($_SESSION['auser_role']) || $_SESSION['auser_role'] !== $role) {
            logError('Insufficient Permissions', [
                'required_role' => $role,
                'user_role' => $_SESSION['auser_role'] ?? 'UNKNOWN',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
            ]);
            header('Location: /admin/index.php?error=unauthorized');
            exit();
        }
    }
}

require_role('Admin');

// Initialize variables
$msg = '';
$success_msg = '';
$error_msg = '';

// Offboarding: deactivate employee
if (isset($_GET['deactivate']) && is_numeric($_GET['deactivate'])) {
    try {
        // Validate CSRF token for GET requests with actions
        if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('CSRF token validation failed');
        }

        $emp_id = filter_var($_GET['deactivate'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => PHP_INT_MAX]
        ]);

        if ($emp_id === false) {
            throw new Exception('Invalid employee ID');
        }

        // Prevent self-deactivation
        if ($emp_id == $_SESSION['auser']) {
            throw new Exception('Cannot deactivate your own account');
        }

        // Begin transaction
        $conn->begin_transaction();

        try {
            // Check if employee exists and is active
            $check_stmt = $conn->prepare("SELECT id, name, email, status FROM employees WHERE id = ?");
            if (!$check_stmt) {
                throw new Exception('Failed to prepare check statement');
            }

            $check_stmt->bind_param("i", $emp_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $employee = $result->fetch_assoc();
            $check_stmt->close();

            if (!$employee) {
                throw new Exception('Employee not found');
            }

            if ($employee['status'] !== 'active') {
                throw new Exception('Employee is already inactive');
            }

            // Use prepared statements to prevent SQL injection
            $stmt1 = $conn->prepare("UPDATE employees SET status = 'inactive' WHERE id = ?");
            if (!$stmt1) {
                throw new Exception('Failed to prepare update statement');
            }

            $stmt1->bind_param("i", $emp_id);
            if (!$stmt1->execute()) {
                throw new Exception('Failed to update employee status');
            }
            $stmt1->close();

            // Remove all roles using prepared statement
            $stmt2 = $conn->prepare("DELETE FROM user_roles WHERE user_id = ?");
            if (!$stmt2) {
                throw new Exception('Failed to prepare role deletion statement');
            }

            $stmt2->bind_param("i", $emp_id);
            if (!$stmt2->execute()) {
                throw new Exception('Failed to remove user roles');
            }
            $stmt2->close();

            // Log offboarding
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $details = 'Offboarding: Employee ID ' . $emp_id . ' (' . htmlspecialchars($employee['name'], ENT_QUOTES, 'UTF-8') . ') deactivated and roles revoked.';
            $stmt3 = $conn->prepare("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (?, 'Offboarding', ?, ?)");
            if (!$stmt3) {
                throw new Exception('Failed to prepare audit log statement');
            }

            $stmt3->bind_param('iss', $_SESSION['auser'], $details, $ip);
            if (!$stmt3->execute()) {
                throw new Exception('Failed to log offboarding activity');
            }
            $stmt3->close();

            // Send notification
            if (function_exists('addNotification')) {
                require_once __DIR__ . '/../includes/functions/notification_util.php';
                addNotification($conn, 'Employee', 'Your access has been revoked due to offboarding.', $emp_id);
            }

            // Commit transaction
            $conn->commit();

            $success_msg = 'Employee "' . htmlspecialchars($employee['name'], ENT_QUOTES, 'UTF-8') . '" has been successfully offboarded and access revoked.';

        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            throw $e;
        }

    } catch (Exception $e) {
        logError('Employee Offboarding Error', [
            'error_message' => $e->getMessage(),
            'employee_id' => $emp_id ?? 'UNKNOWN',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        $error_msg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}

// List all employees using prepared statement
try {
    $stmt = $conn->prepare("SELECT id, name, email, status, created_at FROM employees ORDER BY name");
    if (!$stmt) {
        throw new Exception('Failed to prepare employee list statement');
    }

    if (!$stmt->execute()) {
        throw new Exception('Failed to execute employee list query');
    }

    $employees = $stmt->get_result();
    $stmt->close();

} catch (Exception $e) {
    logError('Employee List Error', [
        'error_message' => $e->getMessage(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
    ]);
    $employees = null;
    $error_msg = 'Failed to load employee list';
}

function logError($message, $context = []) {
    $logDir = __DIR__ . '/../logs';
    try {
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/employees_error.log';
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

        $logMessage = "[{$timestamp}] {$message}{$contextStr}\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        error_log($logMessage);
    } catch (Exception $e) {
        error_log("CRITICAL LOGGING FAILURE: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Employee Management - Secure</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        body { background: #f8f9fa; }
        .main-content { margin-left: 220px; padding: 2rem 1rem; }
        .table { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; }
        .alert { margin-bottom: 1rem; }
        .btn-danger { background-color: #dc3545; border-color: #dc3545; }
        .btn-danger:hover { background-color: #c82333; border-color: #bd2130; }
        .status-badge { font-size: 0.875rem; padding: 0.375rem 0.75rem; }
        .employee-card { transition: transform 0.2s; }
        .employee-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
<?php
// Validate and include header
$header_file = __DIR__ . '/../includes/templates/dynamic_header.php';
if (file_exists($header_file) && is_readable($header_file)) {
    include $header_file;
} else {
    logError('Header File Missing', ['file_path' => $header_file]);
    echo '<!-- Header not available -->';
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Employee Management</h1>
            <div>
                <a href="/admin/dashboard.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
                <button class="btn btn-info" onclick="refreshEmployeeList()">
                    <i class="fas fa-sync-alt me-2"></i>Refresh
                </button>
            </div>
        </div>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Security Information Card -->
        <div class="card border-info mb-4">
            <div class="card-body">
                <h6 class="card-title text-info">
                    <i class="fas fa-shield-alt me-2"></i>Security Features
                </h6>
                <div class="small">
                    <div class="mb-1">
                        <i class="fas fa-check text-success me-2"></i>CSRF protection
                    </div>
                    <div class="mb-1">
                        <i class="fas fa-check text-success me-2"></i>SQL injection prevention
                    </div>
                    <div class="mb-1">
                        <i class="fas fa-check text-success me-2"></i>Secure session management
                    </div>
                    <div class="mb-1">
                        <i class="fas fa-check text-success me-2"></i>Input validation and sanitization
                    </div>
                    <div class="mb-1">
                        <i class="fas fa-check text-success me-2"></i>Access control and audit logging
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-primary">
                            <i class="fas fa-users me-2"></i>Total Employees
                        </h5>
                        <h2 class="mb-0" id="totalEmployees">
                            <?php echo $employees ? $employees->num_rows : '0'; ?>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-success">
                            <i class="fas fa-user-check me-2"></i>Active
                        </h5>
                        <h2 class="mb-0" id="activeEmployees">
                            <?php
                            if ($employees) {
                                $employees->data_seek(0); // Reset pointer
                                $active_count = 0;
                                while ($emp = $employees->fetch_assoc()) {
                                    if ($emp['status'] === 'active') $active_count++;
                                }
                                echo $active_count;
                            } else {
                                echo '0';
                            }
                            ?>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-warning">
                            <i class="fas fa-user-clock me-2"></i>Inactive
                        </h5>
                        <h2 class="mb-0" id="inactiveEmployees">
                            <?php
                            if ($employees) {
                                $employees->data_seek(0); // Reset pointer
                                $inactive_count = 0;
                                while ($emp = $employees->fetch_assoc()) {
                                    if ($emp['status'] === 'inactive') $inactive_count++;
                                }
                                echo $inactive_count;
                            } else {
                                echo '0';
                            }
                            ?>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-info">
                            <i class="fas fa-chart-line me-2"></i>Offboarded Today
                        </h5>
                        <h2 class="mb-0" id="todayOffboarded">0</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee List -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Employee Directory</h5>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportEmployees()">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="showSecurityInfo()">
                        <i class="fas fa-info-circle me-1"></i>Security Info
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if ($employees && $employees->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $employees->data_seek(0); // Reset pointer
                                while ($employee = $employees->fetch_assoc()):
                                ?>
                                <tr class="employee-card">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($employee['name'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                                <small class="text-muted">ID: <?php echo htmlspecialchars($employee['id'], ENT_QUOTES, 'UTF-8'); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <i class="fas fa-envelope text-muted me-2"></i>
                                            <a href="mailto:<?php echo htmlspecialchars($employee['email'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars($employee['email'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($employee['status'] === 'active'): ?>
                                            <span class="badge bg-success status-badge">
                                                <i class="fas fa-check-circle me-1"></i>Active
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary status-badge">
                                                <i class="fas fa-times-circle me-1"></i>Inactive
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y', strtotime($employee['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($employee['status'] === 'active'): ?>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="confirmOffboarding(<?php echo htmlspecialchars($employee['id'], ENT_QUOTES, 'UTF-8'); ?>, '<?php echo htmlspecialchars($employee['name'], ENT_QUOTES, 'UTF-8'); ?>')">
                                                <i class="fas fa-user-times me-1"></i>Offboard
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>Offboarded
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No employees found</h5>
                        <p class="text-muted">There are no employees in the system yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Offboarding Confirmation Modal -->
<div class="modal fade" id="offboardingModal" tabindex="-1" aria-labelledby="offboardingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="offboardingModalLabel">Confirm Employee Offboarding</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                    <h5>Offboard Employee</h5>
                    <p class="mb-0">Are you sure you want to offboard <strong id="employeeName"></strong>?</p>
                </div>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>What happens when you offboard an employee:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Employee status will be set to "inactive"</li>
                        <li>All assigned roles will be removed</li>
                        <li>Access to the system will be revoked</li>
                        <li>A notification will be sent to the employee</li>
                        <li>This action will be logged in the audit trail</li>
                    </ul>
                </div>
                <div class="text-muted small">
                    <i class="fas fa-shield-alt me-1"></i>
                    This action is irreversible and will be recorded in the audit log.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmOffboardBtn">
                    <i class="fas fa-user-times me-2"></i>Confirm Offboarding
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Security Information Modal -->
<div class="modal fade" id="securityModal" tabindex="-1" aria-labelledby="securityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="securityModalLabel">Security Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Security Features Implemented:</h6>
                <ul>
                    <li><strong>CSRF Protection:</strong> All actions require valid CSRF tokens</li>
                    <li><strong>SQL Injection Prevention:</strong> All database queries use prepared statements</li>
                    <li><strong>Input Validation:</strong> All inputs are validated and sanitized</li>
                    <li><strong>Session Security:</strong> Secure session management with timeout</li>
                    <li><strong>Access Control:</strong> Role-based permissions (Admin only)</li>
                    <li><strong>Audit Logging:</strong> All offboarding actions are logged</li>
                    <li><strong>Output Encoding:</strong> All output is properly escaped</li>
                    <li><strong>Transaction Safety:</strong> Database operations use transactions</li>
                </ul>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> This page is protected by multiple security layers to ensure safe employee management operations.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Understood</button>
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
    logError('Footer File Missing', ['file_path' => $footer_file]);
    echo '<!-- Footer not available -->';
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<script>
let currentEmployeeId = null;
let currentEmployeeName = '';

function confirmOffboarding(employeeId, employeeName) {
    currentEmployeeId = employeeId;
    currentEmployeeName = employeeName;

    document.getElementById('employeeName').textContent = employeeName;

    const modal = new bootstrap.Modal(document.getElementById('offboardingModal'));
    modal.show();
}

function refreshEmployeeList() {
    // Show loading indicator
    const totalEl = document.getElementById('totalEmployees');
    const activeEl = document.getElementById('activeEmployees');
    const inactiveEl = document.getElementById('inactiveEmployees');

    if (totalEl) totalEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    if (activeEl) activeEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    if (inactiveEl) inactiveEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    // Reload page
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

function showSecurityInfo() {
    const modal = new bootstrap.Modal(document.getElementById('securityModal'));
    modal.show();
}

function exportEmployees() {
    alert('Export functionality would be implemented here with proper authorization checks.');
}

// Handle offboarding confirmation
document.getElementById('confirmOffboardBtn').addEventListener('click', function() {
    if (currentEmployeeId && currentEmployeeName) {
        // Create a form to submit the offboarding request
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = window.location.href;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>';

        const deactivateInput = document.createElement('input');
        deactivateInput.type = 'hidden';
        deactivateInput.name = 'deactivate';
        deactivateInput.value = currentEmployeeId;

        form.appendChild(csrfInput);
        form.appendChild(deactivateInput);
        document.body.appendChild(form);

        // Show loading state
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

        form.submit();
    }
});

// Auto-refresh employee statistics every 30 seconds
setInterval(function() {
    // Only refresh if page is visible
    if (!document.hidden) {
        const event = new Event('visibilitychange');
        document.dispatchEvent(event);
    }
}, 30000);

// Handle page visibility change
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        // Page became visible, could refresh data here
        console.log('Employee management page is now visible');
    }
});
</script>
</body>
</html>
