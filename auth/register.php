<?php
// Enhanced Security Registration System - APS Dream Home
// Comprehensive security implementation for user registration

define('BASE_PATH', dirname(dirname(__FILE__)));
define('APP_ROOT', BASE_PATH);

// Include required files
require_once BASE_PATH . '/includes/db_config.php';
require_once BASE_PATH . '/includes/security/security_functions.php';

// Initialize security logging
$security_log_file = BASE_PATH . '/logs/security.log';
ensureLogDirectory($security_log_file);

// Validate HTTPS connection (allow HTTP on localhost for development)
if ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') && 
    !in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'])) {
    logSecurityEvent('HTTP Registration Attempt', [
        'ip' => getClientIP(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// Advanced rate limiting
$rate_limit_file = BASE_PATH . '/logs/registration_rate_limit.json';
$max_registrations = 3; // Max registrations per hour
$rate_limit_data = checkRateLimit(getClientIP(), $rate_limit_file, $max_registrations);
if (!$rate_limit_data['allowed']) {
    logSecurityEvent('Registration Rate Limit Exceeded', [
        'ip' => getClientIP(),
        'attempts' => $rate_limit_data['operations'],
        'reset_time' => $rate_limit_data['reset_time'],
        'timestamp' => date('Y-m-d H:i:s')
    ], $security_log_file);
    http_response_code(429);
    die('Too many registration attempts. Please try again later.');
}

// Set comprehensive security headers
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

// CORS configuration
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
ini_set('error_log', BASE_PATH . '/logs/registration_error.log');
error_reporting(E_ALL);

// Initialize variables
$first_name = '';
$last_name = '';
$email = '';
$phone = '';
$password = '';
$confirm_password = '';
$user_role = 'customer';
$errors = [];
$success_message = '';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
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
    error_log("Registration Database Error: " . $e->getMessage());
    die('System temporarily unavailable. Please try again later.');
}

// Generate CAPTCHA
if (!isset($_SESSION['captcha_num1_reg'])) {
    $_SESSION['captcha_num1_reg'] = rand(10, 99);
    $_SESSION['captcha_num2_reg'] = rand(10, 99);
}
$captcha_question_reg = $_SESSION['captcha_num1_reg'] . ' + ' . $_SESSION['captcha_num2_reg'];

// Generate CSRF token
$csrf_token = bin2hex(random_bytes(32));

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        logSecurityEvent('CSRF Token Validation Failed', [
            'ip' => getClientIP(),
            'timestamp' => date('Y-m-d H:i:s')
        ], $security_log_file);
        $errors['csrf'] = 'Security error: Invalid CSRF token.';
    } else if (!isset($_POST['captcha_answer']) || intval($_POST['captcha_answer']) !== ($_SESSION['captcha_num1_reg'] + $_SESSION['captcha_num2_reg'])) {
        logSecurityEvent('CAPTCHA Validation Failed', [
            'ip' => getClientIP(),
            'provided_answer' => $_POST['captcha_answer'] ?? 'empty',
            'correct_answer' => $_SESSION['captcha_num1_reg'] + $_SESSION['captcha_num2_reg'],
            'timestamp' => date('Y-m-d H:i:s')
        ], $security_log_file);
        $errors['captcha'] = 'Security error: Invalid CAPTCHA answer.';
        // Reset CAPTCHA for next attempt
        $_SESSION['captcha_num1_reg'] = rand(10, 99);
        $_SESSION['captcha_num2_reg'] = rand(10, 99);
        $captcha_question_reg = $_SESSION['captcha_num1_reg'] . ' + ' . $_SESSION['captcha_num2_reg'];
    } else {
        // Reset CAPTCHA for next registration
        $_SESSION['captcha_num1_reg'] = rand(10, 99);
        $_SESSION['captcha_num2_reg'] = rand(10, 99);
        $captcha_question_reg = $_SESSION['captcha_num1_reg'] . ' + ' . $_SESSION['captcha_num2_reg'];

        // Input sanitization and validation
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $phone = trim($_POST['phone']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $user_role = $_POST['user_role'] ?? 'customer';

        // Comprehensive input validation
        if (empty($first_name)) {
            $errors['first_name'] = 'First name is required';
        } elseif (strlen($first_name) < 2) {
            $errors['first_name'] = 'First name must be at least 2 characters';
        } elseif (strlen($first_name) > 50) {
            $errors['first_name'] = 'First name is too long';
        }

        if (empty($last_name)) {
            $errors['last_name'] = 'Last name is required';
        } elseif (strlen($last_name) < 2) {
            $errors['last_name'] = 'Last name must be at least 2 characters';
        } elseif (strlen($last_name) > 50) {
            $errors['last_name'] = 'Last name is too long';
        }

        if (empty($email)) {
            $errors['email'] = 'Email address is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        } elseif (strlen($email) > 255) {
            $errors['email'] = 'Email address is too long';
        }

        if (empty($phone)) {
            $errors['phone'] = 'Phone number is required';
        } elseif (!preg_match('/^[0-9+\-\s()]{10,15}$/', $phone)) {
            $errors['phone'] = 'Please enter a valid phone number';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long';
        } elseif (strlen($password) > 72) {
            $errors['password'] = 'Password is too long';
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $password)) {
            $errors['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character';
        }

        if (empty($confirm_password)) {
            $errors['confirm_password'] = 'Please confirm your password';
        } elseif ($password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        if (!in_array($user_role, ['customer', 'associate'])) {
            $errors['user_role'] = 'Invalid user role selected';
        }

        // Additional security checks
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

            // Check if email already exists
            $check_query = "SELECT id FROM users WHERE email = ? AND status = 'active'";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param('s', $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $errors['email'] = 'An account with this email already exists';
                logSecurityEvent('Registration Attempt with Existing Email', [
                    'ip' => getClientIP(),
                    'email' => $email,
                    'timestamp' => date('Y-m-d H:i:s')
                ], $security_log_file);
            }

            $check_stmt->close();
        }

        // If no validation errors, proceed with registration
        if (empty($errors)) {
            try {
                $conn->begin_transaction();

                // Hash password with Argon2ID
                $hashed_password = password_hash($password, PASSWORD_ARGON2ID);

                // Generate verification token
                $verification_token = bin2hex(random_bytes(32));
                $token_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

                // Insert user data - Updated to match actual database schema
                $insert_query = "INSERT INTO users (name, email, password, phone, utype, status, created_at, updated_at)
                                VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())";

                // Combine first and last name into single name field
                $full_name = trim($first_name . ' ' . $last_name);
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->bind_param('sssss', $full_name, $email, $hashed_password, $phone, $user_role);

                if ($insert_stmt->execute()) {
                    $user_id = $conn->insert_id;

                    // Log successful registration
                    logSecurityEvent('Successful Registration', [
                        'ip' => getClientIP(),
                        'user_id' => $user_id,
                        'email' => $email,
                        'role' => $user_role,
                        'timestamp' => date('Y-m-d H:i:s')
                    ], $security_log_file);

                    $conn->commit();

                    // Send verification email (placeholder)
                    $success_message = "Registration successful! Please check your email for verification instructions.";

                    // Clear form data
                    $first_name = $last_name = $email = $phone = $password = $confirm_password = '';
                } else {
                    throw new Exception("Registration failed: " . $insert_stmt->error);
                }

                $insert_stmt->close();

            } catch (Exception $e) {
                $conn->rollback();
                logSecurityEvent('Registration Database Error', [
                    'ip' => getClientIP(),
                    'error' => $e->getMessage(),
                    'timestamp' => date('Y-m-d H:i:s')
                ], $security_log_file);
                $errors['registration'] = 'Registration failed. Please try again later.';
            }
        }
    }
}

// Generate new CSRF token
$csrf_token = bin2hex(random_bytes(32));

// Set page title
$page_title = 'Register - APS Dream Homes';

// Start output buffering
ob_start();
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
            max-width: 500px;
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

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }

        .auth-form {
            padding: 30px;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
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

        .form-select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            background: white;
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

        .password-strength {
            margin-top: 5px;
        }

        .password-strength-bar {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            margin: 5px 0;
        }

        .password-strength-fill {
            height: 100%;
            border-radius: 2px;
            transition: all 0.3s;
        }

        .strength-weak { background: #dc3545; width: 33%; }
        .strength-medium { background: #ffc107; width: 66%; }
        .strength-strong { background: #28a745; width: 100%; }

        @media (max-width: 600px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h2>🔐 Secure Registration</h2>
            <p class="text-muted">Create your APS Dream Homes account</p>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors['registration'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($errors['registration']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors['csrf'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($errors['csrf']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                    <?php if (isset($errors['first_name'])): ?>
                        <span class="error"><?php echo htmlspecialchars($errors['first_name']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
                    <?php if (isset($errors['last_name'])): ?>
                        <span class="error"><?php echo htmlspecialchars($errors['last_name']); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <?php if (isset($errors['email'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['email']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                <?php if (isset($errors['phone'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['phone']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="user_role">Account Type</label>
                <select id="user_role" name="user_role" class="form-select">
                    <option value="customer" <?php echo $user_role === 'customer' ? 'selected' : ''; ?>>Customer</option>
                    <option value="associate" <?php echo $user_role === 'associate' ? 'selected' : ''; ?>>Associate</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <div class="password-strength">
                    <small class="text-muted">Password strength: <span id="strength-text">None</span></small>
                    <div class="password-strength-bar">
                        <div class="password-strength-fill" id="strength-bar"></div>
                    </div>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['password']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['confirm_password']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="captcha_answer">Security Question: What is <?php echo htmlspecialchars($captcha_question_reg); ?>?</label>
                <input type="number" id="captcha_answer" name="captcha_answer" required>
                <?php if (isset($errors['captcha'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['captcha']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </div>

            <div class="auth-links">
                <a href="/auth/login">Already have an account? Login</a>
            </div>
        </form>

        <div class="security-tips">
            <small class="text-muted">
                <i class="fas fa-shield-alt"></i> Protected by advanced security measures<br>
                <i class="fas fa-envelope"></i> Email verification required<br>
                <i class="fas fa-user-secret"></i> All connections are encrypted
            </small>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthText = document.getElementById('strength-text');
        const strengthBar = document.getElementById('strength-bar');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^A-Za-z0-9]/)) strength++;

            if (strength <= 2) {
                strengthText.textContent = 'Weak';
                strengthBar.className = 'password-strength-fill strength-weak';
            } else if (strength <= 3) {
                strengthText.textContent = 'Medium';
                strengthBar.className = 'password-strength-fill strength-medium';
            } else {
                strengthText.textContent = 'Strong';
                strengthBar.className = 'password-strength-fill strength-strong';
            }
        });

        // Form validation
        const form = document.querySelector('.auth-form');
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                return false;
            }
        });
    });
    </script>
</body>
</html>

<?php
// End output buffering and flush content
ob_end_flush();
?>
