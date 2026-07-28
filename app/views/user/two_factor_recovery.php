<?php
$page_title = $page_title ?? 'Use Backup Code';
$has_pending = $has_pending ?? false;
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card shadow">
        <div class="card-header bg-warning text-dark">
          <h4 class="mb-0"><i class="fas fa-key me-2"></i>Backup Code Recovery</h4>
        </div>
        <div class="card-body">
          <p class="text-muted">Enter one of your backup codes to access your account.</p>
          <form method="post" action="<?= BASE_URL ?>/user/two-factor/recovery/verify">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div class="mb-3">
              <label for="code" class="form-label">Backup Code</label>
              <input type="text" id="code" name="code" class="form-control form-control-lg text-center" placeholder="XXXX-XXXX-XXXX" required autofocus>
            </div>
            <button type="submit" class="btn btn-warning w-100 btn-lg">
              <i class="fas fa-unlock me-2"></i>Verify Backup Code
            </button>
          </form>
          <div class="mt-3 text-center">
            <a href="<?= BASE_URL ?>/user/two-factor/verify" class="text-decoration-none">Use authenticator app instead</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
