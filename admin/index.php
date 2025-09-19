<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Headers (added from login.php)
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');

// Include necessary files
require_once __DIR__ . '/../includes/config/db_config.php';
require_once __DIR__ . '/includes/csrf_protection.php';

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
    <link href="login.css" rel="stylesheet">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 400px; 
            margin: 50px auto; 
            padding: 20px; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
        }
        input { 
            width: 100%; 
            padding: 10px; 
            margin: 10px 0; 
            box-sizing: border-box; 
        }
        .error { 
            color: red; 
            margin-bottom: 10px; 
        }
    </style>
</head>
<body>
    <div class="login-container">
        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <div class="login-header" style="display:flex;flex-direction:column;align-items:center;gap:0.4rem;margin-bottom:1.2rem;">
        <div class="panel-title" style="font-size:1.45rem;font-weight:700;color:#0d6efd;letter-spacing:1px;">APS Dream Homes</div>
        <div style="font-size:1.05rem;color:#444;font-weight:500;">Admin Panel Login</div>
        <div class="panel-desc" style="font-size:0.98rem;color:#666;">Welcome! Only authorized personnel may proceed.</div>
    </div>
    <?php if (!empty($login_error)): ?>
    <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
    <?php endif; ?>
    <form action="process_login.php" method="post" autocomplete="off" novalidate>
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required autofocus autocomplete="username">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
        </div>
        <div class="mb-3">
            <label for="captcha_answer" class="form-label">Security Question: <?php echo $captcha_question; ?></label>
            <input type="number" class="form-control" id="captcha_answer" name="captcha_answer" required>
        </div>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
                <div class="text-center">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
                </div>
            </form>
    <div class="mt-3 text-center">
        <a href="../index.php">Back to Home</a>
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