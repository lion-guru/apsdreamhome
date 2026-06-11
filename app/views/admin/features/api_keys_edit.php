<?php
$page_title = $page_title ?? 'Edit API Key';
$content = $content ?? '';
$key = $key ?? [];
ob_start();
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-key me-2"></i>Edit API Key: <?= htmlspecialchars($key['name'] ?? '') ?></h1>
    <a href="<?= BASE_URL ?>/admin/api-keys" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
  </div>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($_SESSION['flash_error']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <div class="card border-0 shadow-sm">
    <div class="card-body aps-cp-card-body">
      <form method="post" action="<?= BASE_URL ?>/admin/api-keys/update/<?= (int)$key['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Key Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($key['name'] ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Rate Limit (req/min)</label>
            <input type="number" name="rate_limit" class="form-control" value="<?= (int)($key['rate_limit_per_minute'] ?? 60) ?>" min="1" max="1000">
          </div>
          <div class="col-md-12">
            <label class="form-label">Scopes</label>
            <div class="row">
              <?php
              $allScopes = ['read:leads','read:properties','read:bookings','write:leads','write:properties','admin:*'];
              $currentScopes = explode(',', $key['scopes'] ?? '');
              foreach ($allScopes as $scope): ?>
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="scopes[]" value="<?= $scope ?>" <?= in_array($scope, $currentScopes) ? 'checked' : '' ?>>
                    <label class="form-check-label"><code><?= $scope ?></code></label>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="col-md-6 text-muted">
            <small><strong>API Key:</strong> <code><?= htmlspecialchars($key['api_key'] ?? '') ?></code></small><br>
            <small><strong>Status:</strong> <?= !empty($key['is_active']) ? '<span class="text-success">Active</span>' : '<span class="text-danger">Revoked</span>' ?></small><br>
            <small><strong>Expires:</strong> <?= $key['expires_at'] ? date('M j, Y', strtotime($key['expires_at'])) : 'Never' ?></small>
          </div>
        </div>
        <hr>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
        <a href="<?= BASE_URL ?>/admin/api-keys" class="btn btn-outline-secondary ms-2">Cancel</a>
      </form>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/unified.php';
