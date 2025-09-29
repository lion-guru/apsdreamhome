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
    <style>
        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-family);
        }
        .login-container {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            padding: var(--space-2xl);
            width: 100%;
            max-width: 450px;
            border: none;
            margin: 0;
        }
        .login-container .form-control {
            border: 2px solid #e9ecef;
            border-radius: var(--radius-md);
            padding: var(--space-lg);
            font-size: var(--font-size-base);
            transition: var(--transition-fast);
        }
        .login-container .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
        }
        .btn-primary {
            background: var(--bg-gradient);
            border: none;
            border-radius: var(--radius-md);
            padding: var(--space-lg);
            font-weight: 600;
            transition: var(--transition-normal);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        .error {
            color: var(--error-color);
            background: rgba(244, 67, 54, 0.1);
            padding: var(--space-md);
            border-radius: var(--radius-md);
            border-left: 4px solid var(--error-color);
            margin-bottom: var(--space-lg);
        }
        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            border: none;
            border-left: 4px solid var(--success-color);
            color: var(--success-color);
        }
        .login-header {
            text-align: center;
            margin-bottom: var(--space-2xl);
        }
        .panel-title {
            font-size: var(--font-size-3xl);
            font-weight: 700;
            background: var(--bg-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: var(--space-sm);
        }
        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--space-sm);
        }
        a {
            color: var(--primary-color);
            text-decoration: none;
            transition: var(--transition-fast);
        }
        a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container fade-in">
        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <div class="login-header">
            <div class="panel-title">APS Dream Homes</div>
            <div style="font-size: var(--font-size-lg); color: var(--text-secondary); font-weight: 500; margin-bottom: var(--space-xs);">Admin Panel Login</div>
            <div class="panel-desc" style="font-size: var(--font-size-base); color: var(--text-muted);">
                🔒 Admin Access Only<br>
                <small class="text-warning">⚠️ Admin login is restricted from the main website</small>
            </div>
            <div class="mt-3">
                <i class="fas fa-shield-alt text-primary" style="font-size: var(--font-size-2xl);"></i>
            </div>
        </div>
        
        <?php if (!empty($login_error)): ?>
        <div class="error">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($login_error); ?>
        </div>
        <?php endif; ?>
        <form action="process_login.php" method="post" autocomplete="off" novalidate class="form-modern">
            <div class="form-group-modern">
                <input type="text" class="form-control-modern" id="username" name="username" required autofocus autocomplete="username" placeholder=" ">
                <label for="username" class="form-label-modern">
                    <i class="fas fa-user me-2"></i>Username
                </label>
            </div>
            
            <div class="form-group-modern">
                <input type="password" class="form-control-modern" id="password" name="password" required autocomplete="current-password" placeholder=" ">
                <label for="password" class="form-label-modern">
                    <i class="fas fa-lock me-2"></i>Password
                </label>
            </div>
            
            <div class="form-group-modern">
                <input type="number" class="form-control-modern" id="captcha_answer" name="captcha_answer" required placeholder=" ">
                <label for="captcha_answer" class="form-label-modern">
                    <i class="fas fa-calculator me-2"></i>Security: <?php echo $captcha_question; ?>
                </label>
            </div>
            
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <button type="submit" class="btn-modern btn-modern-primary w-100 mb-3">
                <i class="fas fa-sign-in-alt me-2"></i>
                Login to Dashboard
            </button>
            
            <div class="text-center">
                <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                    <i class="fas fa-key me-1"></i>Forgot Password?
                </a>
            </div>
        </form>
        <div class="mt-4 text-center">
            <a href="../index.php" class="btn btn-outline-secondary">
                <i class="fas fa-home me-2"></i>Back to Home
            </a>
        </div>
    </div>
    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Reset Your Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="forgotPasswordForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="form-text">Enter the email address associated with your account.</div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <script>
    $(document).ready(function() {
        // Handle forgot password form submission
        $('#forgotPasswordForm').on('submit', function(e) {
            e.preventDefault();
            
            const email = $('#email').val().trim();
            const submitBtn = $(this).find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            
            // Basic email validation
            if (!email) {
                alert('Please enter your email address');
                return;
            }
            
            // Show loading state
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');
            
            // Send AJAX request
            $.ajax({
                url: 'reset_password.php',
                type: 'POST',
                data: { email: email },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#forgotPasswordModal').modal('hide');
                    } else {
                        alert(response.message || 'Error sending reset link');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
        });
    });
    </script>
</body>
</html>