<?php
$page_title = $page_title ?? '2FA Disabled';
$content = $content ?? '';
ob_start();
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card shadow border-secondary text-center">
        <div class="card-body p-5">
          <div class="mb-4">
            <i class="fas fa-shield-alt fa-4x text-secondary mb-3"></i>
            <h3 class="text-secondary">2FA Has Been Disabled</h3>
          </div>

          <div class="alert alert-warning mb-4">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <strong>Your account is less secure now.</strong>
            <p class="mb-0 small mt-1">Anyone with your password can now log in without a second factor.</p>
          </div>

          <?php if (!empty($disabled_at)): ?>
            <p class="text-muted small mb-4">
              <i class="fas fa-clock me-1"></i>
              Disabled at: <strong><?= htmlspecialchars($disabled_at) ?></strong>
            </p>
          <?php endif; ?>

          <div class="d-grid gap-2">
            <a href="<?= BASE_URL ?>/user/two-factor" class="btn btn-primary btn-lg">
              <i class="fas fa-shield-alt me-1"></i> Re-enable 2FA
            </a>
            <a href="<?= BASE_URL ?>/user/dashboard" class="btn btn-outline-secondary">
              <i class="fas fa-tachometer-alt me-1"></i> Go to Dashboard
            </a>
          </div>

          <hr class="my-4">

          <h6 class="text-start"><i class="fas fa-lightbulb me-1"></i> Why enable 2FA?</h6>
          <ul class="text-start text-muted small mb-0">
            <li>Protects your account even if your password is stolen</li>
            <li>Industry standard for securing online accounts</li>
            <li>Takes less than 2 minutes to set up</li>
            <li>Compatible with Google Authenticator, Authy, Microsoft Authenticator</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/base.php';
