<?php
/**
 * APS Dream Home - Reset Password
 * Allows users to reset their password using a secure token
 */

require_once 'includes/config/config.php';
require_once 'includes/functions.php';

$message = '';
$message_type = '';
$token_valid = false;
$user_id = null;

// Check if token is provided
if (!isset($_GET['token'])) {
    $message = 'Invalid or missing reset token.';
    $message_type = 'danger';
} else {
    $token = $_GET['token'];

    try {
        // Use the database connection from config
        global $conn;

        // Verify token and check if it's not expired
        $stmt = $conn->prepare("SELECT user_id, expires_at FROM password_reset_tokens WHERE token = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $token_data = $result->fetch_assoc();

        if ($token_data) {
            $token_valid = true;
            $user_id = $token_data['user_id'];
        } else {
            $message = 'Invalid or expired reset token. Please request a new password reset.';
            $message_type = 'danger';
        }
    } catch (Exception $e) {
        $message = 'An error occurred while verifying the reset token.';
        $message_type = 'danger';
    }
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirm_password)) {
        $message = 'Please fill in both password fields.';
        $message_type = 'danger';
    } elseif ($password !== $confirm_password) {
        $message = 'Passwords do not match.';
        $message_type = 'danger';
    } elseif (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters long.';
        $message_type = 'danger';
    } else {
        try {
            // Use the database connection from config
            global $conn;

            // Hash the new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Update user password
            $stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            $stmt->execute();

            // Mark token as used
            $update_stmt = $conn->prepare("UPDATE password_reset_tokens SET used_at = NOW() WHERE token = ?");
            $update_stmt->bind_param("s", $token);
            $update_stmt->execute();

            $message = 'Password has been reset successfully! You can now login with your new password.';
            $message_type = 'success';
            $token_valid = false; // Prevent form from showing again

        } catch (Exception $e) {
            $message = 'Failed to reset password. Please try again.';
            $message_type = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - APS Dream Homes</title>
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

        .reset-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .reset-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .reset-logo {
            margin-bottom: 20px;
        }

        .reset-logo i {
            font-size: 3rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .reset-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .reset-subtitle {
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

        .reset-footer {
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

        .password-strength {
            margin-top: 5px;
            font-size: 0.85rem;
        }

        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="reset-container">
                    <div class="reset-header">
                        <div class="reset-logo">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h1 class="reset-title">Reset Password</h1>
                        <p class="reset-subtitle">Enter your new password below</p>
                    </div>

                    <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?>">
                        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($token_valid): ?>
                    <form method="POST" action="">
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" required placeholder="Enter new password" onkeyup="checkPasswordStrength()">
                            <label for="password">New Password</label>
                        </div>

                        <div class="password-strength" id="passwordStrength"></div>

                        <div class="form-floating">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Confirm new password">
                            <label for="confirm_password">Confirm New Password</label>
                        </div>

                        <button type="submit" class="btn btn-reset w-100">
                            <i class="fas fa-key me-2"></i>Update Password
                        </button>
                    </form>
                    <?php endif; ?>

                    <div class="reset-footer">
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
    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthIndicator = document.getElementById('passwordStrength');

            if (password.length === 0) {
                strengthIndicator.textContent = '';
                return;
            }

            let strength = 0;
            const checks = [
                password.length >= 8,
                /[a-z]/.test(password),
                /[A-Z]/.test(password),
                /[0-9]/.test(password),
                /[^A-Za-z0-9]/.test(password)
            ];

            strength = checks.filter(Boolean).length;

            let message = '';
            let className = '';

            if (strength < 2) {
                message = 'Weak password';
                className = 'strength-weak';
            } else if (strength < 4) {
                message = 'Medium strength';
                className = 'strength-medium';
            } else {
                message = 'Strong password';
                className = 'strength-strong';
            }

            strengthIndicator.textContent = message;
            strengthIndicator.className = 'password-strength ' + className;
        }
    </script>
</body>
</html>
