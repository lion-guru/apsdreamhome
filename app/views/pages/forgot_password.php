<?php
/**
 * APS Dream Home - Forgot Password
 * Allows users to request password reset
 */

require_once __DIR__ . '/init.php';

use \App\Core\Database;

$message = '';
$message_type = '';
$email_sent = false;

// Handle forgot password form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Validation
    if (!validateCsrfToken()) {
        $message = "Invalid CSRF token. Action blocked.";
        $message_type = "danger";
    } else {
        $email = $_POST['email'] ?? '';

        if (empty($email)) {
            $message = 'Please enter your email address';
            $message_type = 'danger';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address';
            $message_type = 'danger';
        } else {
            try {
                // Get database instance
                $db = \App\Core\App::database();

                // Check if user exists
                $user = $db->fetch("SELECT uid, uname, uemail FROM user WHERE uemail = :email AND status = 'active' LIMIT 1", ['email' => $email]);

                if ($user) {
                    // Generate secure reset token
                    $token = bin2hex(\random_bytes(32));
                    $created_at = date('Y-m-d H:i:s');
                    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    // Delete any existing tokens for this email
                    $db->delete('password_resets', 'email = ?', [$email]);

                    // Insert new reset token
                    $db->insert('password_resets', [
                        'email' => $email,
                        'token' => $token,
                        'created_at' => $created_at,
                        'expires_at' => $expires_at,
                        'used' => 0
                    ]);

                    // Send reset email (using the existing email system)
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/apsdreamhome/reset_password.php?token=" . $token . "&email=" . urlencode($email);

                    $email_service = new \EmailService();
                    $result = $email_service->sendPasswordResetEmail(
                        $email,
                        $user['uname'],
                        $reset_link
                    );

                    if ($result['success']) {
                        $email_sent = true;
                        $message = 'Password reset instructions have been sent to your email address.';
                        $message_type = 'success';
                    } else {
                        $message = 'Failed to send email. Please try again later.';
                        $message_type = 'danger';
                    }
                } else {
                    // Don't reveal if email doesn't exist for security reasons
                    $message = 'If the email address is registered, password reset instructions have been sent.';
                    $message_type = 'info';
                }
            } catch (Exception $e) {
                error_log("Forgot password error: " . $e->getMessage());
                $message = 'An error occurred. Please try again later.';
                $message_type = 'danger';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .forgot-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .forgot-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .forgot-logo {
            margin-bottom: 20px;
        }

        .forgot-logo i {
            font-size: 3rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .forgot-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .forgot-subtitle {
            color: #666;
            font-size: 1rem;
            margin-bottom: 20px;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-control {
            height: 60px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px 15px 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-label {
            padding: 0 10px;
            color: #666;
            font-weight: 500;
        }

        .btn-reset {
            height: 55px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .forgot-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .back-login {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            backdrop-filter: blur(10px);
        }

        .back-login:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="forgot-container">
                    <div class="forgot-header">
                        <div class="forgot-logo">
                            <i class="fas fa-key"></i>
                        </div>
                        <h1 class="forgot-title">Forgot Password</h1>
                        <p class="forgot-subtitle">Enter your email to receive password reset instructions</p>
                    </div>

                    <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : ($message_type === 'info' ? 'info' : 'danger'); ?>">
                        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'info' ? 'info-circle' : 'exclamation-triangle'); ?> me-2"></i>
                        <?php echo h($message); ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!$email_sent): ?>
                    <form method="POST" action="">
                        <?php echo getCsrfField(); ?>
                        <div class="form-floating">
                            <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email address">
                            <label for="email">Email Address</label>
                        </div>

                        <button type="submit" class="btn btn-reset w-100">
                            <i class="fas fa-paper-plane me-2"></i>Send Reset Instructions
                        </button>
                    </form>
                    <?php endif; ?>

                    <div class="forgot-footer">
                        <p class="mb-2">Remember your password?</p>
                        <a href="login.php" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Back to Login
                        </a>

                        <div class="mt-3">
                            <a href="index.php" class="btn back-login">
                                <i class="fas fa-home me-2"></i>Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
