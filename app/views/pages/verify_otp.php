<?php
/**
 * Verify OTP Page - APS Dream Homes
 * Migrated from resources/views/Views/verify_otp.php
 */

require_once __DIR__ . '/init.php';

$error = "";
$msg = "";
$page_title = 'Verify OTP | APS Dream Homes';
$layout = 'modern';

// Check if the OTP is set in the session
if (!isset($_SESSION['otp'])) {
    header("Location: login.php"); // Redirect if OTP session is not set
    exit();
}

if (isset($_POST['verify_otp'])) {
    $entered_otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';

    if ($_SESSION['otp'] == $entered_otp) {
        // Success
        unset($_SESSION['otp']);
        $_SESSION['otp_verified'] = true;
        
        $msg = "OTP verified successfully! Redirecting to reset password...";
        header("refresh:2;url=update_password.php");
    } else {
        $error = "Invalid OTP! Please check and try again.";
    }
}

ob_start();
?>

<div class="row justify-content-center align-items-center min-vh-100 py-5">
    <div class="col-md-5">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden animate-fade-up">
            <div class="card-header bg-primary text-white text-center py-4 border-0">
                <h3 class="fw-bold mb-0">Verify OTP</h3>
                <p class="small mb-0 opacity-75">Enter the 6-digit code sent to your email</p>
            </div>
            <div class="card-body p-5">
                <?php if ($msg): ?>
                    <div class="alert alert-success border-0 rounded-3 mb-4">
                        <i class="fas fa-check-circle me-2"></i><?= $msg ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-3 mb-4">
                        <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="post" class="needs-validation" novalidate>
                    <div class="mb-4">
                        <label for="otp" class="form-label fw-bold">One-Time Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="fas fa-key text-muted"></i></span>
                            <input type="text" name="otp" id="otp" class="form-control form-control-lg bg-light border-0" 
                                   placeholder="123456" maxlength="6" pattern="\d{6}" required autofocus>
                        </div>
                        <div class="form-text mt-2">
                            Didn't receive the code? <a href="#" class="text-primary text-decoration-none fw-bold">Resend</a>
                        </div>
                    </div>
                    
                    <button type="submit" name="verify_otp" class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm">
                        Verify OTP <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </form>
            </div>
            <div class="card-footer bg-light text-center py-3 border-0">
                <a href="/login" class="text-muted text-decoration-none small">
                    <i class="fas fa-arrow-left me-1"></i> Back to Login
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Include the layout
require_once __DIR__ . '/../layouts/' . $layout . '.php';
