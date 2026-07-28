<?php
$page_title = $page_title ?? '2FA Disabled';
$disabled_at = $disabled_at ?? date('Y-m-d H:i:s');
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card shadow">
        <div class="card-header bg-danger text-white">
          <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Two-Factor Authentication Disabled</h4>
        </div>
        <div class="card-body text-center">
          <div class="mb-4">
            <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
          </div>
          <h5>2FA Has Been Disabled</h5>
          <p class="text-muted">Two-factor authentication was disabled on <strong><?= date('d M Y h:i A', strtotime($disabled_at)) ?></strong>.</p>
          <div class="alert alert-warning">
            <i class="fas fa-info-circle me-2"></i>Your account is now less secure. We recommend re-enabling 2FA.
          </div>
          <a href="<?= BASE_URL ?>/user/two-factor" class="btn btn-primary btn-lg">
            <i class="fas fa-shield-alt me-2"></i>Re-enable 2FA
          </a>
          <div class="mt-3">
            <a href="<?= BASE_URL ?>/user/dashboard" class="text-decoration-none">Back to Dashboard</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
