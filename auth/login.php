<?php
// Enhanced Security Login System - APS Dream Home
// Comprehensive security implementation for authentication

// Define base path
define('BASE_PATH', dirname(dirname(__FILE__)));
define('APP_ROOT', BASE_PATH);
define('ABSPATH', BASE_PATH . '/'); // Define ABSPATH for security checks

// Include required files
require_once BASE_PATH . '/includes/db_config.php';
require_once BASE_PATH . '/includes/security/security_functions.php';

// Include CSRF protection (check both possible locations)
$csrf_paths = [
    BASE_PATH . '/admin/includes/csrf_protection.php',
    BASE_PATH . '/includes/security/csrf_protection.php'
];

foreach ($csrf_paths as $csrf_path) {
    if (file_exists($csrf_path)) {
        require_once $csrf_path;
        break;
    }
}

// Fallback CSRF validation if CSRFProtection class is not available
if (!class_exists('CSRFProtection')) {
    function validateCSRFTokenFallback($token, $expected_type) {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Enhanced Security: Initialize security logging
$security_log_file = BASE_PATH . '/logs/security.log';
ensureLogDirectory($security_log_file);

// Enhanced Security: Validate HTTPS connection (allow HTTP on localhost for development)
if ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') && 
    !in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'])) {
    logSecurityEvent('HTTP Login Attempt', [
        'ip' => getClientIP(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// Enhanced Security: Validate request headers
$headers = getallheaders();
if (!validateRequestHeaders($headers)) {
    logSecurityEvent('Invalid Login Request Headers', [
        'ip' => getClientIP(),
        'headers' => $headers,
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request headers']);
    exit();
}

// Enhanced Security: Advanced rate limiting
$rate_limit_file = BASE_PATH . '/logs/login_rate_limit.json';
$max_login_attempts = 5; // Max login attempts per hour
$rate_limit_data = checkRateLimit(getClientIP(), $rate_limit_file, $max_login_attempts);
if (!$rate_limit_data['allowed']) {
    logSecurityEvent('Login Rate Limit Exceeded', [
        'ip' => getClientIP(),
        'attempts' => $rate_limit_data['operations'],
        'reset_time' => $rate_limit_data['reset_time'],
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
    http_response_code(429);
    die('Too many login attempts. Please try again later.');
}

// Enhanced Security: Set comprehensive security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\';');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
header('X-Permitted-Cross-Domain-Policies: none');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Enhanced Security: CORS configuration
$allowed_origins = [
    'https://localhost',
    'http://localhost',
    'https://127.0.0.1',
    'http://127.0.0.1'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 3600');
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Disable error display in production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/logs/login_error.log');
error_reporting(E_ALL);

// Initialize variables with enhanced security
$email = '';
$password = '';
$errors = [];

// Enhanced Security: Secure session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enhanced Security: Session timeout check
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) { // 1 hour timeout
    logSecurityEvent('Login Session Timeout', [
        'ip' => getClientIP(),
        'session_id' => session_id(),
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
    session_unset();
    session_destroy();
    header("Location: login.php?error=session_timeout");
    exit();
}
$_SESSION['last_activity'] = time();

// Database connection with enhanced error handling
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        logSecurityEvent('Database Connection Failed', [
            'ip' => getClientIP(),
            'error' => $conn->connect_error,
            'timestamp' => date('Y-m-d H:i:s')
        ], $security_log_file);
        throw new Exception("Database connection failed");
    }
} catch (Exception $e) {
    logSecurityEvent('Database Error', [
        'ip' => getClientIP(),
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
    error_log("Login Database Error: " . $e->getMessage());
    die('System temporarily unavailable. Please try again later.');
}

// Enhanced Security: CAPTCHA with better randomization
if (!isset($_SESSION['captcha_num1_login'])) {
    $_SESSION['captcha_num1_login'] = rand(10, 99);
    $_SESSION['captcha_num2_login'] = rand(10, 99);
}
$captcha_question_login = $_SESSION['captcha_num1_login'] . ' + ' . $_SESSION['captcha_num2_login'];

// Enhanced Security: Advanced rate limiting with progressive delays
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_blocked_until'] = 0;
    $_SESSION['progressive_delay'] = 0;
}

// Enhanced Security: Check if account is blocked
if (time() < ($_SESSION['login_blocked_until'] ?? 0)) {
    $remaining_time = $_SESSION['login_blocked_until'] - time();
    $errors['login'] = 'Account temporarily locked due to too many failed attempts. Please try again after ' . gmdate('i:s', $remaining_time) . '.';
    logSecurityEvent('Blocked Login Attempt', [
        'ip' => getClientIP(),
        'remaining_time' => $remaining_time,
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Enhanced Security: CSRF token validation
    $csrf_valid = false;
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (class_exists('CSRFProtection')) {
        $csrf_valid = CSRFProtection::validateToken($csrf_token);
    } else {
        // Fallback CSRF validation
        $csrf_valid = validateCSRFTokenFallback($csrf_token);
    }
    
    if (!isset($_POST['csrf_token']) || !$csrf_valid) {
        logSecurityEvent('CSRF Token Validation Failed', [
            'ip' => getClientIP(),
            'timestamp' => date('Y-m-d H:i:s')
        ], $security_log_file);
        $errors['login'] = 'Security error: Invalid or missing CSRF token.';
    } else if (!isset($_POST['captcha_answer']) || intval($_POST['captcha_answer']) !== ($_SESSION['captcha_num1_login'] + $_SESSION['captcha_num2_login'])) {
        logSecurityEvent('CAPTCHA Validation Failed', [
            'ip' => getClientIP(),
            'provided_answer' => $_POST['captcha_answer'] ?? 'empty',
            'correct_answer' => $_SESSION['captcha_num1_login'] + $_SESSION['captcha_num2_login'],
            'timestamp' => date('Y-m-d H:i:s')
        ], $security_log_file);
        $errors['login'] = 'Security error: Invalid CAPTCHA answer.';
        // Reset CAPTCHA for next attempt
        $_SESSION['captcha_num1_login'] = rand(10, 99);
        $_SESSION['captcha_num2_login'] = rand(10, 99);
        $captcha_question_login = $_SESSION['captcha_num1_login'] . ' + ' . $_SESSION['captcha_num2_login'];
        $_SESSION['login_attempts']++;
    } else {
        // Reset CAPTCHA for next login
        $_SESSION['captcha_num1_login'] = rand(10, 99);
        $_SESSION['captcha_num2_login'] = rand(10, 99);
        $captcha_question_login = $_SESSION['captcha_num1_login'] . ' + ' . $_SESSION['captcha_num2_login'];

        // Enhanced Security: Input sanitization and validation
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        // Enhanced Security: Comprehensive input validation
        if (empty($email)) {
            $errors['email'] = 'Email address is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        } elseif (strlen($email) > 255) {
            $errors['email'] = 'Email address is too long';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long';
        } elseif (strlen($password) > 72) {
            $errors['password'] = 'Password is too long';
        }

        // Enhanced Security: Additional security checks
        if (empty($errors)) {
            // Check for suspicious patterns in email
            if (preg_match('/[<>\"\';]/', $email)) {
                logSecurityEvent('Suspicious Email Pattern', [
                    'ip' => getClientIP(),
                    'email' => $email,
                    'timestamp' => date('Y-m-d H:i:s')
                ], $security_log_file);
                $errors['email'] = 'Invalid email format';
            }

            // Check if login is from main index and user is admin
            $referrer = $_SERVER['HTTP_REFERER'] ?? '';
            if (strpos($referrer, '/apsdreamhome/') !== false ||
                strpos($referrer, '/index.php') !== false ||
                !isset($_SERVER['HTTP_REFERER'])) {

                // This is likely a login from the main index page
                // Check if user is admin and block the login
                $admin_check_query = "SELECT role FROM users WHERE email = ? AND status = 'active'";
                $admin_stmt = $conn->prepare($admin_check_query);

                if ($admin_stmt) {
                    $admin_stmt->bind_param('s', $email);
                    $admin_stmt->execute();
                    $admin_result = $admin_stmt->get_result();

                    if ($admin_result && $admin_row = $admin_result->fetch_assoc()) {
                        if ($admin_row['role'] === 'admin') {
                            logSecurityEvent('Admin Login Blocked from Main Index', [
                                'ip' => getClientIP(),
                                'email' => $email,
                                'referrer' => $referrer,
                                'timestamp' => date('Y-m-d H:i:s')
                            ], $security_log_file);

                            $errors['login'] = 'Admin users must login through the admin panel. Please use the admin login page.';
                        }
                    }
                    $admin_stmt->close();
                }
            }
        }

        // If no validation errors, attempt login
        if (empty($errors)) {
            try {
                $query = "SELECT id, email, password, role, status FROM users WHERE email = ? AND status = 'active'";
                $stmt = $conn->prepare($query);

                if (!$stmt) {
                    throw new Exception("Database prepare failed: " . $conn->error);
                }

                $stmt->bind_param('s', $email);

                if (!$stmt->execute()) {
                    throw new Exception("Database execute failed: " . $stmt->error);
                }

                $result = $stmt->get_result();

                if ($result && $user = $result->fetch_assoc()) {
                    // Enhanced Security: Verify password with timing attack protection
                    if (password_verify($password, $user['password'])) {
                        // Enhanced Security: Successful login - regenerate session
                        session_regenerate_id(true);

                        // Store user data with security measures
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['role'];
                        $_SESSION['login_time'] = time();
                        $_SESSION['user_ip'] = getClientIP();

                        // Reset failed attempts
                        $_SESSION['login_attempts'] = 0;
                        $_SESSION['login_blocked_until'] = 0;
                        $_SESSION['progressive_delay'] = 0;

                        // Enhanced Security: Log successful login
                        logSecurityEvent('Successful Login', [
                            'ip' => getClientIP(),
                            'user_id' => $user['id'],
                            'user_role' => $user['role'],
                            'timestamp' => date('Y-m-d H:i:s')
                        ], $security_log_file);

                        // Enhanced Security: Role-based redirect with validation
                        $redirect_url = '/associate_dashboard.php'; // Default
                        if ($user['role'] === 'admin') {
                            $redirect_url = '/admin/admin_panel.php';
                        } elseif ($user['role'] === 'associate') {
                            $redirect_url = '/associate_dashboard.php';
                        } elseif ($user['role'] === 'customer') {
                            $redirect_url = '/customer_dashboard.php';
                        }

                        // Enhanced Security: Validate redirect URL
                        if (!preg_match('/^\/[a-zA-Z0-9_\/-]+\.php$/', $redirect_url)) {
                            logSecurityEvent('Invalid Redirect URL', [
                                'ip' => getClientIP(),
                                'attempted_url' => $redirect_url,
                                'timestamp' => date('Y-m-d H:i:s')
                            ], $security_log_file);
                            $redirect_url = '/associate_dashboard.php';
                        }

                        header('Location: ' . $redirect_url);
                        exit;
                    } else {
                        // Enhanced Security: Failed login attempt
                        $errors['login'] = 'Invalid email or password';
                        $_SESSION['login_attempts']++;

                        logSecurityEvent('Failed Login Attempt', [
                            'ip' => getClientIP(),
                            'email' => $email,
                            'attempt_number' => $_SESSION['login_attempts'],
                            'timestamp' => date('Y-m-d H:i:s')
                        ], $security_log_file);
                    }
                } else {
                    // Enhanced Security: User not found
                    $errors['login'] = 'Invalid email or password';
                    $_SESSION['login_attempts']++;

                    logSecurityEvent('Login Attempt for Non-existent User', [
                        'ip' => getClientIP(),
                        'email' => $email,
                        'attempt_number' => $_SESSION['login_attempts'],
                        'timestamp' => date('Y-m-d H:i:s')
                    ], $security_log_file);
                }

                $stmt->close();

                // Enhanced Security: Progressive lockout system
                if ($_SESSION['login_attempts'] >= 3) {
                    $lockout_duration = min(300 * pow(2, $_SESSION['login_attempts'] - 3), 3600); // Progressive: 5min, 10min, 20min, 40min, max 1hr
                    $_SESSION['login_blocked_until'] = time() + $lockout_duration;

                    logSecurityEvent('Account Locked Due to Failed Attempts', [
                        'ip' => getClientIP(),
                        'attempts' => $_SESSION['login_attempts'],
                        'lockout_duration' => $lockout_duration,
                        'timestamp' => date('Y-m-d H:i:s')
                    ], $security_log_file);

                    $errors['login'] = 'Account temporarily locked due to too many failed attempts. Please try again after ' . gmdate('i:s', $lockout_duration) . '.';
                }
            } catch (Exception $e) {
                logSecurityEvent('Login Database Error', [
                    'ip' => getClientIP(),
                    'error' => $e->getMessage(),
                    'timestamp' => date('Y-m-d H:i:s')
                ], $security_log_file);
                $errors['login'] = 'System error. Please try again later.';
            }
        }
    }
}

// Enhanced Security: Generate simple CSRF token
$csrf_token = bin2hex(random_bytes(32));

// Set page title
$page_title = 'Login - APS Dream Homes';

// Enhanced Security: Security status display
$security_status = [
    'https_active' => true,
    'rate_limit_remaining' => $rate_limit_data['remaining'],
    'session_secure' => true
];

// Start output buffering
ob_start();
?>

<!-- Enhanced Security: Security status display -->
<div id="security-info" style="display: none;">
    <span class="badge badge-success">✓ Secure Connection (HTTPS)</span>
    <span class="badge badge-info">Rate Limit: <?php echo $rate_limit_data['remaining']; ?>/<?php echo $max_login_attempts; ?></span>
    <span class="badge badge-secondary">IP: <?php echo htmlspecialchars(substr(getClientIP(), 0, 12)); ?>...</span>
</div>

<div class="auth-container">
    <div class="auth-box">
        <div class="auth-header">
            <h2>🔐 Secure Login</h2>
            <p class="text-muted">APS Dream Homes - Advanced Security System</p>
        </div>

        <!-- Enhanced Security: Security status badges -->
        <div class="security-status mb-3">
            <small class="text-muted">
                <span class="badge badge-success">✓ HTTPS Active</span>
                <span class="badge badge-info">Attempts Left: <?php echo $rate_limit_data['remaining']; ?></span>
                <span class="badge badge-secondary">👥 Associates, Agents & Customers</span>
                <?php if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 0): ?>
                    <span class="badge badge-warning">Failed Attempts: <?php echo $_SESSION['login_attempts']; ?></span>
                <?php endif; ?>
            </small>
        </div>

        <?php if (isset($errors['login'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($errors['login']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-info">
                <?php
                switch ($_GET['error']) {
                    case 'session_timeout':
                        echo 'Your session has expired. Please login again.';
                        break;
                    case 'access_denied':
                        echo 'Access denied. Please login with appropriate credentials.';
                        break;
                    default:
                        echo 'Please login to continue.';
                }
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required autocomplete="email">
                <?php if (isset($errors['email'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['email']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
                <?php if (isset($errors['password'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['password']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="captcha_answer">Security Question: What is <?php echo htmlspecialchars($captcha_question_login); ?>?</label>
                <input type="number" class="form-control" name="captcha_answer" id="captcha_answer" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Secure Login
                </button>
            </div>

            <div class="auth-links">
                <a href="/auth/forgot-password">Forgot Password?</a>
                <span>|</span>
                <a href="/auth/register">Create Account</a>
                <span>|</span>
                <a href="/admin/index.php" class="text-warning">
                    <i class="fas fa-shield-alt"></i> Admin Login
                </a>
            </div>
        </form>

        <!-- Enhanced Security: Security tips -->
        <div class="security-tips mt-3">
            <small class="text-muted">
                <i class="fas fa-shield-alt"></i> Protected by advanced security measures<br>
                <i class="fas fa-clock"></i> Account locks after multiple failed attempts<br>
                <i class="fas fa-user-secret"></i> All connections are encrypted
            </small>
        </div>
    </div>
</div>

<!-- Enhanced Security: Client-side security monitoring -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Log security events
    function logSecurityEvent(event, data) {
        fetch('/includes/security/security_logger.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                event: 'Client_' + event,
                data: data,
                timestamp: new Date().toISOString(),
                page: 'login'
            })
        }).catch(error => console.error('Security logging failed:', error));
    }

    // Monitor for suspicious activities
    let keystrokeCount = 0;
    document.addEventListener('keydown', function() {
        keystrokeCount++;
        if (keystrokeCount > 50) { // Suspicious rapid typing
            logSecurityEvent('RapidKeystrokes', { count: keystrokeCount });
        }
    });

    // Monitor form submission
    document.querySelector('.auth-form').addEventListener('submit', function() {
        logSecurityEvent('LoginAttempt', { timestamp: new Date().toISOString() });
    });

    // Monitor for copy/paste in password field
    document.getElementById('password').addEventListener('paste', function() {
        logSecurityEvent('PasswordPaste', { timestamp: new Date().toISOString() });
    });
});
</script>

<?php
$content = ob_get_clean();

// Simple HTML template for login page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .auth-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
        }

        .auth-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .auth-header h2 {
            margin-bottom: 10px;
        }

        .security-status {
            margin: 15px 0;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            margin: 2px;
        }

        .badge-success { background: #d4edda; color: #155724; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-warning { background: #fff3cd; color: #856404; }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }

        .auth-form {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        .auth-links {
            text-align: center;
            margin-top: 20px;
        }

        .auth-links a {
            color: #667eea;
            text-decoration: none;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }

        .security-tips {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
    </style>
</head>
<body>
    <?php echo $content; ?>
</body>
</html>