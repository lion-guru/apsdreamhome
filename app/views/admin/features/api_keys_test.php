<?php
$page_title = $page_title ?? 'Test API Key';
$content = $content ?? '';
$key = $key ?? [];
$test_result = $test_result ?? [];
ob_start();
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-vial me-2"></i>Test API Key: <?= htmlspecialchars($key['name'] ?? '') ?></h1>
    <a href="<?= BASE_URL ?>/admin/api-keys" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm <?= !empty($test_result['valid']) ? 'bg-success' : 'bg-danger' ?> text-white">
        <div class="card-body text-center">
          <i class="fas fa-<?= !empty($test_result['valid']) ? 'check-circle' : 'times-circle' ?> fa-2x mb-2"></i>
          <h4 class="mb-0"><?= !empty($test_result['valid']) ? 'Valid' : 'Invalid' ?></h4>
          <small><?= !empty($test_result['valid']) ? 'Key is active and not expired' : 'Key is inactive or expired' ?></small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm bg-info text-white">
        <div class="card-body aps-cp-card-body">
          <small class="opacity-75">Status</small>
          <h4 class="mb-0 text-capitalize"><?= $test_result['status'] ?? 'unknown' ?></h4>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm bg-primary text-white">
        <div class="card-body aps-cp-card-body">
          <small class="opacity-75">Rate Limit</small>
          <h4 class="mb-0"><?= $test_result['rate_limit'] ?? 0 ?> <small>req/min</small></h4>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm bg-secondary text-white">
        <div class="card-body aps-cp-card-body">
          <small class="opacity-75">Last Used</small>
          <h5 class="mb-0"><?= $test_result['last_used'] === 'Never' ? 'Never' : date('M j, Y', strtotime($test_result['last_used'])) ?></h5>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-md-6">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-info-circle me-1"></i> Key Details</h6></div>
        <div class="card-body aps-cp-card-body">
          <div class="table-responsive"><table class="table table-sm mb-0">
            <tr><td class="text-muted">Key Preview</td><td><code><?= htmlspecialchars($test_result['key_preview'] ?? '') ?></code></td></tr>
            <tr><td class="text-muted">Scopes</td><td><?php foreach (($test_result['scopes'] ?? []) as $s): ?><span class="badge bg-light text-dark me-1"><?= htmlspecialchars($s ?? '') ?></span> <?php endforeach; ?></td></tr>
            <tr><td class="text-muted">Expires</td><td><?= $test_result['expires_at'] ?? 'Never' ?></td></tr>
            <tr><td class="text-muted">Last Used</td><td><?= $test_result['last_used'] ?? 'Never' ?></td></tr>
          </table></div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-code me-1"></i> Usage Example</h6></div>
        <div class="card-body aps-cp-card-body">
          <pre class="bg-light p-3 rounded mb-0 small"><code>curl -H "Authorization: Bearer <?= htmlspecialchars($test_result['key_preview'] ?? 'apk_...') ?>:YOUR_SECRET" \
     "<?= BASE_URL ?>/api/v2/leads?per_page=10"</code></pre>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/admin.php';
