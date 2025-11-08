<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/admin_error.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Headers (added from login.php)
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');

// Include necessary files
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/includes/csrf_protection.php';

// Debug: Check if database connection is working
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Generate CSRF token
$csrf_token = CSRFProtection::generateToken();

// Check for login error and success messages
$login_error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

$success_message = '';
if (isset($_GET['password_changed']) && $_GET['password_changed'] == 1) {
    $success_message = 'Your password has been changed successfully. Please log in with your new password.';
}

// Also check for success message in session (in case of redirect)
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Generate simple CAPTCHA
$num1 = rand(1, 10);
$num2 = rand(1, 10);
$_SESSION['captcha_num1_admin'] = $num1;
$_SESSION['captcha_num2_admin'] = $num2;
$_SESSION['captcha_answer'] = $num1 + $num2;
$captcha_question = "$num1 + $num2 = ?";

// Check if already logged in
if (isset($_SESSION['admin_session']['is_authenticated']) && $_SESSION['admin_session']['is_authenticated'] === true) {
    header('Location: dashboard.php');
    exit();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/modern-ui.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --login-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body {
            background: var(--login-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a"><stop offset="0" stop-color="%23ffffff" stop-opacity=".1"/><stop offset="1" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="500" cy="500" r="400" fill="url(%23a)"/></svg>');
            animation: float 20s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--radius-xl);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: var(--space-2xl);
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            animation: slideUp 0.8s ease-out;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            animation: shimmer 2s infinite;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .login-header {
            text-align: center;
            margin-bottom: var(--space-2xl);
        }

        .login-logo {
            margin-bottom: var(--space-lg);
        }

        .login-logo i {
            font-size: 3.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: var(--space-sm);
            letter-spacing: -0.025em;
        }

        .login-subtitle {
            color: var(--text-secondary);
            font-size: var(--font-size-sm);
            font-weight: 500;
            margin-bottom: var(--space-lg);
        }

        .login-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--space-sm);
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: var(--space-xs) var(--space-md);
            border-radius: var(--radius-full);
            font-size: var(--font-size-xs);
            font-weight: 600;
            margin-bottom: var(--space-lg);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .form-floating-modern {
            position: relative;
            margin-bottom: var(--space-lg);
        }

        .form-floating-modern input {
            height: 60px;
            padding: var(--space-lg) var(--space-md);
            border: 2px solid #e1e8ed;
            border-radius: var(--radius-lg);
            font-size: var(--font-size-base);
            transition: all var(--transition-normal);
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        .form-floating-modern input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .form-floating-modern label {
            position: absolute;
            left: var(--space-md);
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            padding: 0 var(--space-sm);
            color: var(--text-secondary);
            transition: all var(--transition-fast);
            pointer-events: none;
            font-weight: 500;
        }

        .form-floating-modern input:focus + label,
        .form-floating-modern input:not(:placeholder-shown) + label {
            top: 0;
            font-size: var(--font-size-xs);
            color: #667eea;
            background: white;
            padding: 0 var(--space-xs);
        }

        .form-floating-modern i {
            position: absolute;
            right: var(--space-md);
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            transition: color var(--transition-fast);
        }

        .form-floating-modern input:focus + i {
            color: #667eea;
        }

        .btn-login {
            height: 55px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: var(--radius-lg);
            font-size: var(--font-size-base);
            font-weight: 600;
            color: white;
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
        }

        .login-footer {
            text-align: center;
            margin-top: var(--space-xl);
            padding-top: var(--space-xl);
            border-top: 1px solid #e9ecef;
        }

        .login-links {
            display: flex;
            justify-content: center;
            gap: var(--space-lg);
            margin-top: var(--space-md);
        }

        .login-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: var(--font-size-sm);
            transition: color var(--transition-fast);
            display: flex;
            align-items: center;
            gap: var(--space-xs);
        }

        .login-links a:hover {
            color: #667eea;
        }

        .alert-modern {
            border: none;
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            margin-bottom: var(--space-lg);
            backdrop-filter: blur(10px);
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background: rgba(244, 67, 54, 0.1);
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }

        .security-info {
            background: rgba(255, 152, 0, 0.1);
            border: 1px solid rgba(255, 152, 0, 0.3);
            border-radius: var(--radius-lg);
            padding: var(--space-md);
            margin-top: var(--space-lg);
            font-size: var(--font-size-sm);
            color: #856404;
        }

        .captcha-section {
            background: #f8f9fa;
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            margin-top: var(--space-lg);
            border: 2px dashed #dee2e6;
        }

        .back-home-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            backdrop-filter: blur(10px);
        }

        .back-home-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
        }

        /* Loading animation for form submission */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-xl);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all var(--transition-normal);
        }

        .loading-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive improvements */
        @media (max-width: 576px) {
            .login-container {
                margin: var(--space-lg);
                padding: var(--space-xl);
            }

            .login-logo i {
                font-size: 2.5rem;
            }

            .login-title {
                font-size: 1.5rem;
            }
        }

        /* Enhanced form validation */
        .form-floating-modern input:invalid {
            border-color: var(--error-color);
        }

        .form-floating-modern input:invalid:focus {
            border-color: var(--error-color);
            box-shadow: 0 0 0 4px rgba(244, 67, 54, 0.1);
        }

        /* Password visibility toggle */
        .password-toggle {
            cursor: pointer;
            user-select: none;
        }

        /* Focus trap for better accessibility */
        .login-container:focus-within {
            outline: 2px solid rgba(102, 126, 234, 0.5);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="login-container" tabindex="0">
        <div class="loading-overlay" id="loadingOverlay">
            <div class="spinner"></div>
        </div>

        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 class="login-title">APS Dream Homes</h1>
            <p class="login-subtitle">Secure Admin Access</p>
            <div class="login-badge">
                <i class="fas fa-lock"></i>
                Admin Panel Login
            </div>
        </div>

        <?php if (!empty($login_error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($login_error); ?>
        </div>
        <?php endif; ?>

        <form action="process_login.php" method="post" autocomplete="off" novalidate id="loginForm">
            <div class="form-floating-modern">
                <input type="text" id="username" name="username" required
                       placeholder="Enter your username" autocomplete="username">
                <label for="username">
                    <i class="fas fa-user me-2"></i>Username
                </label>
                <i class="fas fa-user"></i>
            </div>

            <div class="form-floating-modern">
                <input type="password" id="password" name="password" required
                       placeholder="Enter your password" autocomplete="current-password">
                <label for="password">
                    <i class="fas fa-lock me-2"></i>Password
                </label>
                <i class="fas fa-lock password-toggle" id="passwordToggle" title="Show Password"></i>
            </div>

            <div class="captcha-section">
                <div class="form-floating-modern">
                    <input type="number" id="captcha_answer" name="captcha_answer" required
                           placeholder="Enter the result" min="1" max="99">
                    <label for="captcha_answer">
                        <i class="fas fa-calculator me-2"></i>Security Question: <?php echo $captcha_question; ?>
                    </label>
                    <i class="fas fa-shield-alt"></i>
                </div>
            </div>

            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <button type="submit" class="btn btn-login w-100" id="loginBtn">
                <i class="fas fa-sign-in-alt me-2"></i>
                <span>Login to Dashboard</span>
            </button>

            <div class="security-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Security Notice:</strong> This admin panel is restricted and monitored.
                Only authorized personnel should attempt to login.
            </div>
        </form>

        <div class="login-footer">
            <div class="login-links">
                <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                    <i class="fas fa-key"></i>
                    Forgot Password?
                </a>
                <a href="../index.php">
                    <i class="fas fa-home"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">
                        <i class="fas fa-key me-2"></i>Reset Your Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="forgotPasswordForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   placeholder="Enter your registered email address">
                            <div class="form-text">
                                Enter the email address associated with your admin account.
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Send Reset Link</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Password visibility toggle
        $('#passwordToggle').on('click', function() {
            const passwordInput = $('#password');
            const icon = $(this);

            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                icon.removeClass('fa-lock').addClass('fa-unlock');
                icon.attr('title', 'Hide Password');
            } else {
                passwordInput.attr('type', 'password');
                icon.removeClass('fa-unlock').addClass('fa-lock');
                icon.attr('title', 'Show Password');
            }
        });

        // Form submission with loading state
        $('#loginForm').on('submit', function(e) {
            const loginBtn = $('#loginBtn');
            const loadingOverlay = $('#loadingOverlay');

            // Show loading state
            loginBtn.prop('disabled', true).html(`
                <div class="spinner" style="width: 20px; height: 20px; margin-right: 8px;"></div>
                Signing In...
            `);
            loadingOverlay.addClass('show');

            // Form will submit normally after showing loading state
            // The loading state will remain until page redirects
        });

        // Handle forgot password form submission
        $('#forgotPasswordForm').on('submit', function(e) {
            e.preventDefault();

            const email = $('#email').val().trim();
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();

            if (!email) {
                alert('Please enter your email address');
                return;
            }

            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address');
                return;
            }

            submitBtn.prop('disabled', true).html(`
                <div class="spinner" style="width: 16px; height: 16px; margin-right: 8px;"></div>
                Sending...
            `);

            $.ajax({
                url: 'reset_password.php',
                type: 'POST',
                data: { email: email },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('✅ ' + response.message);
                        $('#forgotPasswordModal').modal('hide');
                    } else {
                        alert('❌ ' + (response.message || 'Error sending reset link'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('❌ An error occurred. Please try again.');
                    console.error('Reset password error:', error);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Enhanced form validation feedback
        $('input[required]').on('blur', function() {
            const input = $(this);
            const formGroup = input.closest('.form-floating-modern');

            if (input.val().trim() === '') {
                formGroup.addClass('error');
            } else {
                formGroup.removeClass('error');
            }
        });

        // Auto-focus on first input
        $('#username').focus();

        // Keyboard navigation improvements
        $(document).on('keydown', function(e) {
            // Escape key closes modals
            if (e.key === 'Escape') {
                $('.modal').modal('hide');
            }

            // Enter key submits form when focused on password field
            if (e.key === 'Enter' && $(e.target).is('#password')) {
                $('#loginForm').submit();
            }
        });
    });
    </script>
</body>
</html>