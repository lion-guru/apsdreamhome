<?php
// Password Recovery System
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/core/init.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!validateCsrfToken()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $error = 'Please enter your email address';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } else {
            try {
                // Check if email exists in database
                $db = \App\Core\App::database();

                $admin = $db->fetchOne("SELECT * FROM admin WHERE email = ?", [$email]);

                 if ($admin) {
                    // Generate reset token
                    $token = \bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes(32));
                    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    // Save token to database
                    $db->execute("UPDATE admin SET reset_token = ?, reset_expiry = ? WHERE id = ?", [$token, $expiry, $admin['id']]);

                    // In production, send email here
                    // For demo, just show success message
                    $message = "Password reset link has been sent to your email. (Demo: Token: $token)";

                    // Log the reset request
                    error_log("Password reset requested for: $email");
                } else {
                    // Don't reveal if email exists or not
                    $message = "If your email is registered, you will receive a password reset link.";
                }
            } catch (Exception $e) {
                error_log("Password reset error: " . $e->getMessage());
                $error = 'Unable to process request. Please try again later.';
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
    <title>Password Recovery - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .recovery-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 50px 40px;
            max-width: 450px;
            width: 100%;
        }
        .brand-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .brand-logo i {
            font-size: 3rem;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="recovery-card">
        <div class="brand-logo">
            <i class="fas fa-key"></i>
            <h3>Password Recovery</h3>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?= h($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= h($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?php echo getCsrfField(); ?>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                </button>
            </div>
        </form>

        <div class="text-center mt-4">
            <a href="index.php" class="text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Back to Login
            </a>
        </div>
    </div>
</body>
</html>
