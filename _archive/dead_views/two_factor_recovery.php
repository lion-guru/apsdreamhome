<?php
$page_title = $page_title ?? 'Use Backup Code';
$content = $content ?? '';
ob_start();
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
    <div class="col-lg-6">
      <div class="card shadow">
        <div class="card-header bg-info text-white">
          <h4 class="mb-0"><i class="fas fa-key me-2"></i>Use Backup Recovery Code</h4>
        </div>
        <div class="card-body aps-cp-card-body">
          <?php if (empty($has_pending)): ?>
            <div class="alert alert-warning">
              <i class="fas fa-exclamation-triangle me-1"></i>
              No pending login session. Please <a href="<?= BASE_URL ?>/login" class="alert-link">log in</a> first.
            </div>
          <?php else: ?>
            <p class="text-muted">Enter one of the 8-character backup codes you saved when you enabled 2FA. Each code works only once.</p>

            <form method="post" action="<?= BASE_URL ?>/user/two-factor/recovery/verify" id="recoveryForm" autocomplete="off">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
              <div class="mb-3">
                <label for="code" class="form-label fw-bold">Backup Code</label>
                <input
                  type="text"
                  name="code"
                  id="code"
                  class="form-control form-control-lg text-center text-uppercase"
                  placeholder="ABCD2345"
                  maxlength="16"
                  minlength="6"
                  pattern="[A-Za-z0-9]{6,16}"
                  required
                  autofocus
                  style="letter-spacing: 4px; font-family: 'Courier New', monospace;"
                >
                <small class="form-text text-muted">Codes are 6-16 characters, letters and numbers (e.g. <code>ABCD2345</code>).</small>
              </div>

              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                  <i class="fas fa-sign-in-alt me-1"></i> Verify Backup Code
                </button>
                <a href="<?= BASE_URL ?>/user/two-factor/verify" class="btn btn-outline-secondary">
                  <i class="fas fa-arrow-left me-1"></i> Use authenticator code instead
                </a>
              </div>
            </form>

            <hr class="my-4">
            <div class="text-muted small">
              <i class="fas fa-shield-alt me-1"></i>
              Lost your backup codes? Contact support to disable 2FA on your account.
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  const codeInput = document.getElementById('code');
  if (codeInput) {
    codeInput.addEventListener('input', (e) => {
      e.target.value = e.target.value.toUpperCase().replace(/\s/g, '');
    });
  }
})();
</script>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/base.php';
