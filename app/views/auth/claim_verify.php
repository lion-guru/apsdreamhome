<?php
// Claim Booking — Step 2: OTP + password
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = preg_replace('#/public$#', '', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    define('BASE_URL', $protocol . '://' . $host . $basePath);
}
$csrf_token = $csrf_token ?? $_SESSION['csrf_token'] ?? '';
$error = $error ?? null;
$success = $success ?? null;
$phone = $phone ?? '';
$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify & Claim - APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        body { min-height: 100vh; background: linear-gradient(135deg, #0d9488, #0f766e); display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; padding: 20px 0 }
        .card { max-width: 460px; width: 100%; border: none; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.2) }
        .card-body { padding: 2rem }
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
    <div class="container">
        <div class="card mx-auto">
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="mb-3"><i class="fas fa-shield-halved fa-2x text-success"></i></div>
                    <h3 class="fw-bold">Verify & Claim</h3>
                    <p class="text-muted">OTP sent to <strong><?= htmlspecialchars($phone) ?></strong></p>
                </div>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <form method="POST" action="<?php echo e($base); ?>/auth/claim-booking/verify">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                    <div class="mb-3">
                        <label class="form-label" for="claimOtp">OTP *</label>
                        <input type="text" class="form-control" name="otp" id="claimOtp" placeholder="6-digit code" maxlength="6" pattern="[0-9]{6}" required inputmode="numeric">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="claimName">Your Name</label>
                        <input type="text" class="form-control" name="name" id="claimName" placeholder="Full name (for new accounts)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="claimPassword">Set Password *</label>
                        <input type="password" class="form-control" name="password" id="claimPassword" placeholder="Min 6 characters" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="claimConfirm">Confirm Password *</label>
                        <input type="password" class="form-control" name="confirm_password" id="claimConfirm" placeholder="Re-enter password" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 py-2">
                        <i class="fas fa-check-circle me-2"></i>Verify & Claim Bookings
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
