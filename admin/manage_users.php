<?php
/**
 * Enhanced Security User Management Interface
 * Provides secure user and admin management with comprehensive security measures
 * Security Enhanced Version
 */

// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/manage_users_security.log');
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

// Rate limiting for user management page
$max_page_loads = 20; // page loads per hour
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
    logSecurityEvent('Manage Users Session Timeout', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    header('Location: /admin/login.php?timeout=1');
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Rate limiting check
$rate_limit_key = 'manage_users_' . md5($ip_address);
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
    if ($rate_limit_data['loads'] > $max_page_loads) {
        logSecurityEvent('Manage Users Rate Limit Exceeded', [
            'ip_address' => $ip_address,
            'loads' => $rate_limit_data['loads'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'Too many page loads. Please slow down.',
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
        $logFile = $logDir . '/manage_users_security.log';
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
        logSecurityEvent('Suspicious User Agent in Manage Users', [
            'user_agent' => $user_agent,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        return false;
    }

    return true;
}

// Load required files with validation
$required_files = [
    __DIR__ . '/../includes/SessionManager.php',
    __DIR__ . '/../includes/db_config.php',
    __DIR__ . '/../includes/templates/dynamic_header.php',
    __DIR__ . '/../includes/templates/new_footer.php'
];

foreach ($required_files as $file) {
    if (!file_exists($file) || !is_readable($file)) {
        logSecurityEvent('Required File Missing in Manage Users', [
            'file_path' => $file,
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
}

require_once $required_files[0];
require_once $required_files[1];

// Check if user has required role (superadmin only)
try {
    $sessionManager = new SessionManager();
    $sessionManager->requireSuperAdmin();
} catch (Exception $e) {
    logSecurityEvent('Unauthorized Access Attempt to Manage Users', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
        'error' => $e->getMessage()
    ]);
    header('Location: /admin/login.php?unauthorized=1');
    exit();
}

// Validate request headers
if (!validateRequestHeaders()) {
    logSecurityEvent('Invalid Request Headers in Manage Users', [
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

// Get database connection with error handling
try {
    $conn = getDbConnection();
    if (!$conn) {
        logSecurityEvent('Database Connection Failed in Manage Users', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        throw new Exception('Database connection failed');
    }
} catch (Exception $e) {
    logSecurityEvent('Database Error in Manage Users', [
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

$page_title = 'Manage Users & Admins';

// Log page access
logSecurityEvent('Manage Users Page Accessed', [
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
    'session_id' => session_id()
]);

// Fetch admins/superadmins/finance/associates with prepared statements
$admins = [];
try {
    $admin_stmt = $conn->prepare("SELECT aid AS id, auser AS name, email, role, status FROM admin WHERE status = 'active' ORDER BY aid DESC");
    if (!$admin_stmt) {
        throw new Exception('Failed to prepare admin query: ' . $conn->error);
    }

    if (!$admin_stmt->execute()) {
        throw new Exception('Failed to execute admin query: ' . $admin_stmt->error);
    }

    $result = $admin_stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['source'] = 'admin';
            $row = array_map('escapeForHTML', $row);
            $admins[] = $row;
        }
    }
    $admin_stmt->close();

} catch (Exception $e) {
    logSecurityEvent('Error Fetching Admins in Manage Users', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'error' => $e->getMessage()
    ]);
    $admins = []; // Continue with empty array
}

// Fetch users (customers, investors, tenants) with prepared statements
$users = [];
try {
    $user_stmt = $conn->prepare("SELECT id, name, email, type AS role, status FROM users WHERE status = 'active' ORDER BY id DESC LIMIT 100");
    if (!$user_stmt) {
        throw new Exception('Failed to prepare user query: ' . $conn->error);
    }

    if (!$user_stmt->execute()) {
        throw new Exception('Failed to execute user query: ' . $user_stmt->error);
    }

    $result = $user_stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['source'] = 'user';
            $row = array_map('escapeForHTML', $row);
            $users[] = $row;
        }
    }
    $user_stmt->close();

} catch (Exception $e) {
    logSecurityEvent('Error Fetching Users in Manage Users', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'error' => $e->getMessage()
    ]);
    $users = []; // Continue with empty array
}

// Merge for display with security validation
$all_users = array_merge($admins, $users);

// Validate merged data
$validated_users = [];
foreach ($all_users as $user) {
    if (is_array($user) &&
        isset($user['id'], $user['name'], $user['email'], $user['role'], $user['status'], $user['source']) &&
        is_numeric($user['id']) &&
        is_string($user['name']) &&
        is_string($user['email']) &&
        is_string($user['role']) &&
        is_string($user['status']) &&
        is_string($user['source'])) {
        $validated_users[] = $user;
    }
}

$all_users = $validated_users;

// Log successful data retrieval
logSecurityEvent('User Data Retrieved Successfully', [
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
    'admin_count' => count($admins),
    'user_count' => count($users),
    'total_count' => count($all_users)
]);

// Include header with security context
$security_context = [
    'csrf_token' => $_SESSION['csrf_token'],
    'session_timeout' => 1800,
    'rate_limit_remaining' => $max_page_loads - $rate_limit_data['loads'],
    'rate_limit_reset' => $rate_limit_data['first_load'] + 3600
];

include __DIR__ . '/../includes/templates/dynamic_header.php';
?>

<!-- Enhanced Security User Management Interface -->
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-lg-10 mx-auto">
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
                        Rate limit: <?php echo $max_page_loads - $rate_limit_data['loads']; ?>/<?php echo $max_page_loads; ?> remaining
                    </small>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 text-primary">
                    <i class="fas fa-users-cog me-2"></i>Manage Users & Admins
                    <span class="badge bg-info ms-2">Super Admin Only</span>
                </h1>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal" onclick="generateCSRFToken()">
                    <i class="fas fa-user-plus me-1"></i> Add Admin
                </button>
            </div>

            <!-- Enhanced Search with Security -->
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="userSearch" placeholder="Search by name, email, or role..." onkeyup="filterUsers()">
                <button class="btn btn-outline-secondary" type="button" onclick="filterUsers()">
                    <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-outline-info" type="button" onclick="clearSearch()" title="Clear Search">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="table-responsive rounded shadow-sm">
                <table class="table table-hover align-middle mb-0" id="usersTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role/Type</th>
                            <th>Status</th>
                            <th>Source</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($all_users)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    <i class="fas fa-users-slash me-2"></i>No users found.
                                </td>
                            </tr>
                        <?php else: ?>
                        <?php foreach ($all_users as $user): ?>
                        <tr data-user-id="<?php echo (int)$user['id']; ?>" data-user-email="<?php echo htmlspecialchars($user['email']); ?>">
                            <td><?php echo (int)$user['id']; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <span class="avatar-title bg-primary text-white rounded-circle">
                                            <?php echo strtoupper(substr(htmlspecialchars($user['name']), 0, 1)); ?>
                                        </span>
                                    </div>
                                    <span><?php echo htmlspecialchars($user['name']); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge bg-<?php
                                    echo match($user['role']) {
                                        'superadmin', 'super_admin' => 'danger',
                                        'admin' => 'primary',
                                        'associate' => 'info',
                                        'finance' => 'warning',
                                        'manager' => 'success',
                                        default => 'secondary'
                                    };
                                ?> text-uppercase">
                                    <?php echo htmlspecialchars($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst(htmlspecialchars($user['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $user['source'] === 'admin' ? 'primary' : 'secondary'; ?>">
                                    <?php echo ucfirst(htmlspecialchars($user['source'])); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-outline-primary btn-sm" title="Edit User" onclick="editUser(<?php echo (int)$user['id']; ?>, '<?php echo htmlspecialchars($user['source']); ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm" title="Change Role" onclick="changeRole(<?php echo (int)$user['id']; ?>, '<?php echo htmlspecialchars($user['role']); ?>', '<?php echo htmlspecialchars($user['source']); ?>')">
                                        <i class="fas fa-user-tag"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" title="Deactivate User" onclick="deactivateUser(<?php echo (int)$user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>', '<?php echo htmlspecialchars($user['source']); ?>')">
                                        <i class="fas fa-user-slash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- User Statistics -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h5 class="card-title text-primary"><?php echo count($admins); ?></h5>
                            <p class="card-text">Active Admins</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <h5 class="card-title text-info"><?php echo count($users); ?></h5>
                            <p class="card-text">Active Users</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h5 class="card-title text-success"><?php echo count($all_users); ?></h5>
                            <p class="card-text">Total Users</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <h5 class="card-title text-warning">
                                <i class="fas fa-shield-alt"></i>
                            </h5>
                            <p class="card-text">Security Protected</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning mt-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Security Notice:</strong> All user management actions are logged and monitored.
                Unauthorized access attempts will be reported to system administrators.
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal with Enhanced Security -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Add New Admin User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addUserForm" method="POST" action="user_actions.php">
                <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="userName" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="userName" name="name" required
                                   maxlength="100" pattern="[A-Za-z\s]{2,100}"
                                   placeholder="Enter full name">
                            <div class="form-text">2-100 characters, letters and spaces only</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="userEmail" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="userEmail" name="email" required
                                   maxlength="255" placeholder="Enter email address">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="userPhone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="userPhone" name="phone" required
                                   maxlength="15" pattern="[\d\s+\-()]{10,15}"
                                   placeholder="Enter phone number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="userRole" class="form-label">Role *</label>
                            <select class="form-select" id="userRole" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="associate">Associate</option>
                                <option value="finance">Finance</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="userPassword" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="userPassword" name="password" required
                                   minlength="8" maxlength="128"
                                   placeholder="Enter secure password">
                            <div class="form-text">Minimum 8 characters</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="userStatus" class="form-label">Status</label>
                            <select class="form-select" id="userStatus" name="status">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-user-plus me-1"></i>Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Enhanced JavaScript with Security Features -->
<script>
let csrfToken = '<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>';

// Generate new CSRF token for security
function generateCSRFToken() {
    fetch('/admin/generate_csrf.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            csrfToken = data.csrf_token;
            document.getElementById('csrf_token').value = csrfToken;
        }
    })
    .catch(error => {
        logSecurityEvent('CSRF Token Generation Failed', { error: error.message });
    });
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
            csrf_token: csrfToken
        })
    }).catch(error => console.error('Security logging failed:', error));
}

// Enhanced user filtering with security
function filterUsers() {
    const searchTerm = document.getElementById('userSearch').value.toLowerCase();
    const table = document.getElementById('usersTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        if (cells.length >= 4) {
            const name = cells[1].textContent.toLowerCase();
            const email = cells[2].textContent.toLowerCase();
            const role = cells[3].textContent.toLowerCase();

            if (name.includes(searchTerm) || email.includes(searchTerm) || role.includes(searchTerm)) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    }
}

// Clear search function
function clearSearch() {
    document.getElementById('userSearch').value = '';
    filterUsers();
}

// Edit user function with security
function editUser(userId, source) {
    logSecurityEvent('User Edit Attempted', { user_id: userId, source: source });
    // Implementation would go here
    alert('Edit functionality coming soon. User ID: ' + userId);
}

// Change role function with security
function changeRole(userId, currentRole, source) {
    logSecurityEvent('Role Change Attempted', { user_id: userId, current_role: currentRole, source: source });
    // Implementation would go here
    alert('Role change functionality coming soon. User ID: ' + userId);
}

// Deactivate user function with security
function deactivateUser(userId, userName, source) {
    if (confirm('Are you sure you want to deactivate user: ' + userName + '?')) {
        logSecurityEvent('User Deactivation Attempted', { user_id: userId, user_name: userName, source: source });

        fetch('/admin/user_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                'action': 'delete',
                'id': userId,
                'csrf_token': csrfToken
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                logSecurityEvent('User Deactivated Successfully', { user_id: userId, user_name: userName });
                location.reload();
            } else {
                logSecurityEvent('User Deactivation Failed', { user_id: userId, error: data.message });
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            logSecurityEvent('User Deactivation Exception', { user_id: userId, error: error.message });
            alert('An error occurred. Please try again.');
        });
    }
}

// Form submission with security validation
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('csrf_token', csrfToken);

    fetch('/admin/user_actions.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            logSecurityEvent('User Added Successfully', { email: formData.get('email'), role: formData.get('role') });
            location.reload();
        } else {
            logSecurityEvent('User Addition Failed', { error: data.message });
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        logSecurityEvent('User Addition Exception', { error: error.message });
        alert('An error occurred. Please try again.');
    });
});

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
}, 60000); // Check every minute

// AJAX error handler with security logging
$(document).ajaxError(function(event, xhr, settings, thrownError) {
    logSecurityEvent('AJAX Error in Manage Users', {
        url: settings.url,
        method: settings.type,
        error: thrownError,
        status: xhr.status
    });
});

// Initialize security on page load
document.addEventListener('DOMContentLoaded', function() {
    logSecurityEvent('Manage Users Page Loaded', {
        admin_count: <?php echo count($admins); ?>,
        user_count: <?php echo count($users); ?>
    });
});
</script>

<?php
// Close database connection
if (isset($conn) && $conn) {
    $conn->close();
}

include __DIR__ . '/../includes/templates/new_footer.php';
?>
