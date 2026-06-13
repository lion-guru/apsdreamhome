<?php
$page_title = $page_title ?? 'Two-Factor Authentication';
$content = $content ?? '';
?>
<div class="container py-5">
  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <strong><?= htmlspecialchars($_SESSION['flash_success'] ?? '') ?></strong>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($_SESSION['flash_error'] ?? '') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Two-Factor Authentication (2FA)</h4>
        </div>
        <div class="card-body aps-cp-card-body">
          <?php if ($is_enabled): ?>
            <div class="alert alert-success">
              <h5><i class="fas fa-check-circle"></i> 2FA is ACTIVE</h5>
              <p class="mb-0">Your account is protected with TOTP-based two-factor authentication.</p>
            </div>
            <form method="post" action="<?= BASE_URL ?>/user/two-factor/disable" onsubmit="return confirm('Disable 2FA? Your account will be less secure.')">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
              <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Disable 2FA</button>
            </form>
          <?php else: ?>
            <p>Two-factor authentication adds an extra layer of security. You'll need an authenticator app like Google Authenticator, Authy, or Microsoft Authenticator.</p>

            <div class="row">
              <div class="col-md-6 text-center">
                <h6 class="mb-3">Step 1: Scan QR Code</h6>
                <img src="<?= htmlspecialchars($qr_url ?? $qrCodeUrl ?? '') ?>" alt="2FA QR Code" class="img-fluid border" style="max-width: 200px;">
                <p class="small text-muted mt-2">Scan with your authenticator app</p>
              </div>
              <div class="col-md-6">
                <h6 class="mb-3">Step 2: Or Enter Manually</h6>
                <p class="small text-muted">If you can't scan, enter this key manually:</p>
                <div class="bg-light p-2 rounded mb-3">
                  <code class="user-select-all"><?= htmlspecialchars($manual_key) ?></code>
                </div>

                <h6 class="mb-3">Step 3: Enter Code</h6>
                <form method="post" action="<?= BASE_URL ?>/user/two-factor/enable">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                  <div class="mb-2">
                    <input type="text" name="code" class="form-control form-control-lg text-center" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autofocus>
                    <small class="text-muted">Current code: <strong><?= $current_otp ?></strong> (changes every 30s)</small>
                  </div>
                  <button type="submit" class="btn btn-primary w-100"><i class="fas fa-check"></i> Enable 2FA</button>
                </form>
              </div>
            </div>

            <hr class="my-4">
            <h6>What happens when I enable 2FA?</h6>
            <ul class="small text-muted">
              <li>After login, you'll be asked for a 6-digit code from your authenticator</li>
              <li>Codes change every 30 seconds</li>
              <li>You'll get 8 backup codes for recovery if you lose your device</li>
              <li>Backup codes can be used once each if you can't access your authenticator</li>
            </ul>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>