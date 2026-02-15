<?php
/**
 * APS Dream Home - Forgot Password
 * Allows users to request password reset
 */

require_once 'includes/config/config.php';
require_once 'includes/functions.php';

// Start session and generate CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$message_type = '';
$email_sent = false;

// Handle forgot password form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
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
                // Use the database connection from config
                global $conn;

                // Check if user exists
                $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE email = ? AND status = 'active' LIMIT 1");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if ($user) {
                    // Generate secure reset token
                    $token = bin2hex(random_bytes(32));
                    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    // Delete any existing tokens for this user
                    $delete_stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
                    $delete_stmt->bind_param("i", $user['id']);
                    $delete_stmt->execute();

                    // Insert new reset token
                    $insert_stmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
                    $insert_stmt->bind_param("issss", $user['id'], $token, $expires_at, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
                    $insert_stmt->execute();

                    // Send reset email (using the existing email system)
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/apsdreamhome/reset_password.php?token=" . $token;

                    $email_body = "
                <html>
                <head>
                    <title>Password Reset - APS Dream Home</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                        .button { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; display: inline-block; margin: 20px 0; }
                        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 0.9em; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>🔐 Password Reset Request</h1>
                            <p>APS Dream Home</p>
                        </div>
                        <div class='content'>
                            <h2>Hello {$user['name']},</h2>
                            <p>You have requested to reset your password for your APS Dream Home account.</p>
                            <p>Click the button below to reset your password. This link will expire in 1 hour for security reasons.</p>
                            <div style='text-align: center;'>
                                <a href='{$reset_link}' class='button'>Reset Password</a>
                            </div>
                            <p><strong>Reset Link:</strong> <a href='{$reset_link}'>{$reset_link}</a></p>
                            <p>If you didn't request this password reset, please ignore this email. Your password will remain unchanged.</p>
                            <p>Best regards,<br>APS Dream Home Team</p>
                        </div>
                        <div class='footer'>
                            <p>This is an automated email. Please do not reply to this message.</p>
                        </div>
                    </div>
                </body>
                </html>
                ";

                    // Use PHPMailer if available, otherwise use basic mail()
                    if (file_exists('PHPMailer/PHPMailer.php')) {
                        require_once 'PHPMailer/PHPMailer.php';
                        require_once 'PHPMailer/SMTP.php';

                        $mail = new PHPMailer\PHPMailer\PHPMailer();
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'apsdreamhomes44@gmail.com';
                        $mail->Password = 'your_app_password_here'; // Replace with actual app password
                        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        $mail->setFrom('apsdreamhomes44@gmail.com', 'APS Dream Home');
                        $mail->addAddress($email, $user['name']);
                        $mail->isHTML(true);
                        $mail->Subject = 'Password Reset - APS Dream Home';
                        $mail->Body = $email_body;

                        if ($mail->send()) {
                            $email_sent = true;
                            $message = 'Password reset instructions have been sent to your email address.';
                            $message_type = 'success';
                        } else {
                            $message = 'Failed to send email. Please try again later.';
                            $message_type = 'danger';
                        }
                    } else {
                        // Fallback to basic mail function
                        $headers = "MIME-Version: 1.0\r\n";
                        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                        $headers .= "From: APS Dream Home <apsdreamhomes44@gmail.com>\r\n";

                        if (mail($email, 'Password Reset - APS Dream Home', $email_body, $headers)) {
                            $email_sent = true;
                            $message = 'Password reset instructions have been sent to your email address.';
                            $message_type = 'success';
                        } else {
                            $message = 'Failed to send email. Please contact support.';
                            $message_type = 'danger';
                        }
                    }
                } else {
                    // Don't reveal if email doesn't exist for security reasons
                    $message = 'If the email address is registered, password reset instructions have been sent.';
                    $message_type = 'info';
                }
            } catch (Exception $e) {
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
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!$email_sent): ?>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
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
