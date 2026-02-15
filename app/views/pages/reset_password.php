<?php
/**
 * Modernized Reset Password Page
 * Migrated from Views/reset_password.php
 */

require_once __DIR__ . '/init.php';

use \App\Core\Database;

$db = \App\Core\App::database();

// Validate token and email from GET
$validToken = false;
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';
$error = '';
$msg = '';

if ($email && $token) {
    try {
        $reset_data = $db->fetch("SELECT * FROM password_resets WHERE email = ? AND token = ? AND expires_at >= NOW() AND used = 0 LIMIT 1", [$email, $token]);
        if ($reset_data) {
            $validToken = true;
        }
    } catch (Exception $e) {
        error_log("Reset Password Token Validation Error: " . $e->getMessage());
        $error = "An error occurred during validation. Please try again.";
    }
}

if (!$validToken && empty($error)) {
    $error = "Invalid or expired reset link. Please request a new password reset.";
}

// Simple math CAPTCHA
if (!isset($_SESSION['captcha_num1_rp'])) {
    $_SESSION['captcha_num1_rp'] = \App\Helpers\SecurityHelper::secureRandomInt(1, 10);
}
if (!isset($_SESSION['captcha_num2_rp'])) {
    $_SESSION['captcha_num2_rp'] = \App\Helpers\SecurityHelper::secureRandomInt(1, 10);
}
$captcha_question_rp = $_SESSION['captcha_num1_rp'] . ' + ' . $_SESSION['captcha_num2_rp'];

// Rate limiting for reset password
if (!isset($_SESSION['rp_attempts'])) {
    $_SESSION['rp_attempts'] = 0;
    $_SESSION['rp_blocked_until'] = 0;
}

if (time() < ($_SESSION['rp_blocked_until'] ?? 0)) {
    $error = 'Too many attempts. Try again after ' . date('H:i:s', $_SESSION['rp_blocked_until']) . '.';
} else if (isset($_POST['reset']) && $validToken) {
    // CSRF check
    if (!validateCsrfToken()) {
        $error = "Security error: Invalid CSRF token.";
    }
    // CAPTCHA check
    else if (!isset($_POST['captcha_answer']) || intval($_POST['captcha_answer']) !== ($_SESSION['captcha_num1_rp'] + $_SESSION['captcha_num2_rp'])) {
        $error = "Security error: Invalid CAPTCHA answer.";
        $_SESSION['captcha_num1_rp'] = \App\Helpers\SecurityHelper::secureRandomInt(1, 10);
        $_SESSION['captcha_num2_rp'] = \App\Helpers\SecurityHelper::secureRandomInt(1, 10);
        $_SESSION['rp_attempts'] += 1;
        if ($_SESSION['rp_attempts'] >= 5) {
            $_SESSION['rp_blocked_until'] = \time() + 600;
            $error = 'Too many attempts. Try again after ' . \date('H:i:s', $_SESSION['rp_blocked_until']) . '.';
        }
    } else {
        $_SESSION['captcha_num1_rp'] = \App\Helpers\SecurityHelper::secureRandomInt(1, 10);
        $_SESSION['captcha_num2_rp'] = \App\Helpers\SecurityHelper::secureRandomInt(1, 10);

        $newPassword = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($newPassword) || strlen($newPassword) < 6) {
            $error = "Password must be at least 6 characters long.";
        } else if ($newPassword === $confirmPassword) {
            // Use secure password hashing
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Start transaction
            $db->beginTransaction();
            try {
                // Update 'user' table
                $db->update('user', ['upass' => $hashedPassword], 'uemail = ?', [$email]);

                // Mark the token as used
                $db->update('password_resets', ['used' => 1], 'email = ? AND token = ?', [$email, $token]);

                $db->commit();
                $msg = "Password has been reset successfully. You can now log in.";
                $_SESSION['rp_attempts'] = 0;
                $_SESSION['rp_blocked_until'] = 0;

                // Set a flag to redirect after showing message
                $reset_success = true;
            } catch (Exception $e) {
                $db->rollBack();
                error_log("Reset Password Update Error: " . $e->getMessage());
                $error = "Error updating password. Please try again later.";
            }
        } else {
            $error = "Passwords do not match.";
        }
    }
}

// Start buffering for modern layout
ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden animate-fade-up">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0 fw-bold">Reset Password</h3>
                    <p class="mb-0 opacity-75">Secure your account with a new password</p>
                </div>
                <div class="card-body p-4 p-md-5">

                    <?php if ($error): ?>
                        <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-4 animate-shake">
                            <i class="fas fa-exclamation-circle me-2"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($msg): ?>
                        <div class="alert alert-success rounded-3 border-0 shadow-sm mb-4">
                            <i class="fas fa-check-circle me-2"></i> <?= $msg ?>
                        </div>
                        <div class="text-center">
                            <a href="login.php" class="btn btn-primary rounded-pill px-4 py-2 mt-2">
                                <i class="fas fa-sign-in-alt me-2"></i> Go to Login
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($validToken && !$msg): ?>
                        <form method="post" autocomplete="off" class="needs-validation">
                            <input type="hidden" name="csrf_token" value="<?= CSRFProtection::generateToken('reset_password') ?>">

                            <div class="mb-4">
                                <label class="form-label fw-semibold">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" name="password" class="form-control bg-light border-0" placeholder="Min. 6 characters" required minlength="6">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Confirm New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-check-double text-muted"></i></span>
                                    <input type="password" name="confirm_password" class="form-control bg-light border-0" placeholder="Repeat new password" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Security Question</label>
                                <div class="card bg-light border-0 rounded-3 mb-2">
                                    <div class="card-body py-2 px-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span>What is <strong><?= $captcha_question_rp ?></strong>?</span>
                                            <input type="text" name="captcha_answer" class="form-control bg-white border-0 shadow-sm" style="width: 80px;" required autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" name="reset" class="btn btn-primary btn-lg rounded-pill fw-bold py-3 shadow-sm hover-lift">
                                    Reset Password <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </form>
                    <?php elseif (!$validToken && !$msg): ?>
                        <div class="text-center py-4">
                            <div class="mb-4">
                                <i class="fas fa-link-slash fa-4x text-muted opacity-25"></i>
                            </div>
                            <p class="text-muted">This reset link is no longer valid. For security reasons, reset links expire after a certain period.</p>
                            <a href="forgot-password.php" class="btn btn-outline-primary rounded-pill px-4 mt-3">
                                Request New Link
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="index.php" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-left me-1"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}
.animate-shake {
    animation: shake 0.4s ease-in-out 0s 2;
}
</style>

<?php
$content = ob_get_clean();
$page_title = "Reset Password - APS Dream Home";
require_once __DIR__ . '/../layouts/modern.php';
?>
