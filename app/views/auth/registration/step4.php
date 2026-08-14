<?php
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $basePath = preg_replace('#/public$#', '', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    define('BASE_URL', $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $basePath);
}
require_once __DIR__ . '/../../../Helpers/TranslationHelper.php';
$captcha = substr(strtoupper(md5(uniqid('', true))), 0, 5);
$_SESSION['wizard_captcha'] = $captcha;
$state = $state ?? [];
$formData = $state['form_data'] ?? [];
$emailVerified = !empty($formData['email_otp_verified']);
$phoneVerified = !empty($formData['phone_otp_verified']);
$resent = $_GET['resent'] ?? '';
$verified = $_GET['verified'] ?? '';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(__current_lang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step 4: Verification | Register | APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        body { background: linear-gradient(135deg, #0d9488, #0f766e); min-height: 100vh; font-family: 'Inter', sans-serif; padding: 20px 0; }
        .wizard-card { max-width: 640px; margin: auto; border: none; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
        .wizard-header { background: linear-gradient(135deg, #0d9488, #0f766e); color: #fff; border-radius: 16px 16px 0 0; padding: 1.5rem; }
        .verify-card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 1rem; margin-bottom: 1rem; }
        .verify-card.verified { border-color: #10b981; background: #f0fdf4; }
    </style>
</head>
<body>
<div class="container">
    <div class="wizard-card">
        <div class="wizard-header text-center">
            <h3 class="fw-bold mb-0">Verify Your Account</h3>
            <small class="opacity-75">Step 4 of 4 &middot; Almost done!</small>
        </div>
        <div class="card-body p-4">
            <?php include __DIR__ . '/progress.php'; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <?php if ($resent): ?>
                <div class="alert alert-info"><i class="fas fa-paper-plane me-1"></i> OTP resent to your <?= htmlspecialchars($resent) ?>.</div>
            <?php endif; ?>
            <?php if ($verified): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle me-1"></i> <?= ucfirst(htmlspecialchars($verified)) ?> verified successfully!</div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/register/step4" id="final-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

                <div class="verify-card <?= $emailVerified ? 'verified' : '' ?>" id="email-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <i class="fas fa-envelope <?= $emailVerified ? 'text-success' : 'text-primary' ?> me-1"></i>
                            <strong>Email:</strong> <?= htmlspecialchars($state['email'] ?? '') ?>
                            <?php if ($emailVerified): ?><span class="badge bg-success ms-1">Verified</span><?php endif; ?>
                        </div>
                        <?php if (!$emailVerified): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="resendOtp('email')">
                                <i class="fas fa-paper-plane me-1"></i> Send OTP
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php if (!$emailVerified): ?>
                        <div class="input-group">
                            <input type="text" name="email_otp" class="form-control" placeholder="6-digit OTP" maxlength="6" pattern="[0-9]{6}">
                            <button type="button" class="btn btn-success" onclick="verifyOtp('email')">
                                <i class="fas fa-check me-1"></i> Verify
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="verify-card <?= $phoneVerified ? 'verified' : '' ?>" id="phone-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <i class="fas fa-mobile-alt <?= $phoneVerified ? 'text-success' : 'text-primary' ?> me-1"></i>
                            <strong>Phone:</strong> <?= htmlspecialchars($state['phone'] ?? '') ?>
                            <?php if ($phoneVerified): ?><span class="badge bg-success ms-1">Verified</span><?php endif; ?>
                        </div>
                        <?php if (!$phoneVerified): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="resendOtp('phone')">
                                <i class="fas fa-paper-plane me-1"></i> Send OTP
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php if (!$phoneVerified): ?>
                        <div class="input-group">
                            <input type="text" name="phone_otp" class="form-control" placeholder="6-digit OTP" maxlength="6" pattern="[0-9]{6}">
                            <button type="button" class="btn btn-success" onclick="verifyOtp('phone')">
                                <i class="fas fa-check me-1"></i> Verify
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Captcha * <small class="text-muted">(type: <strong><?= $captcha ?></strong>)</small></label>
                    <input type="text" name="captcha" class="form-control" required placeholder="Enter the code above" class="style-73536">
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="<?= BASE_URL ?>/register/step3" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-check-circle me-1"></i> Complete Registration
                    </button>
                </div>
            </form>

            <form id="otp-form" method="POST" action="<?= BASE_URL ?>/register/verify-otp" class="d-none">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="type" id="otp-type">
                <input type="hidden" name="code" id="otp-code">
            </form>
            <form id="resend-form" method="POST" action="<?= BASE_URL ?>/register/resend-otp" class="d-none">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="type" id="resend-type">
            </form>
        </div>
    </div>
</div>
<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function resendOtp(type) {
    document.getElementById('resend-type').value = type;
    document.getElementById('resend-form').submit();
}
function verifyOtp(type) {
    var card = document.getElementById(type + '-card');
    var input = card.querySelector('input[name$="_otp"]');
    var code = (input && input.value || '').trim();
    if (!/^[0-9]{6}$/.test(code)) { alert('Please enter a valid 6-digit OTP'); return; }
    document.getElementById('otp-type').value = type;
    document.getElementById('otp-code').value = code;
    document.getElementById('otp-form').submit();
}
</script>
</body>
</html>
