<?php
$page_title = $page_title ?? 'Security Center';
$page_heading = $page_heading ?? 'Security Center';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><i class="fas fa-shield-alt me-2"></i>Security Center</h1>

  <div class="row">
    <div class="col-md-3"><div class="card border-left-danger shadow-sm"><div class="card-body aps-cp-card-body"><h2  class="fs-6 fw-semibold text-muted">Blocked IPs</h2><h2 class="mb-0"><?= count($blocked ?? []) ?></h2></div></div></div>
    <div class="col-md-3"><div class="card border-left-warning shadow-sm"><div class="card-body aps-cp-card-body"><h2  class="fs-6 fw-semibold text-muted">Failed Logins (24h)</h2><h2 class="mb-0"><?= count($failed ?? []) ?></h2></div></div></div>
    <div class="col-md-3"><div class="card border-left-info shadow-sm"><div class="card-body aps-cp-card-body"><h2  class="fs-6 fw-semibold text-muted">2FA Active</h2><h2 class="mb-0">N/A</h2></div></div></div>
    <div class="col-md-3"><div class="card border-left-success shadow-sm"><div class="card-body aps-cp-card-body"><h2  class="fs-6 fw-semibold text-muted">Password Resets (24h)</h2><h2 class="mb-0"><?= count($failed ?? []) ?></h2></div></div></div>
  </div>

  <div class="row mt-4">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between"><strong>Blocked IPs</strong>
          <form method="POST" action="<?= BASE_URL ?>/api/v2/security/ip/unblock" class="d-flex">
                              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <input name="ip" class="form-control form-control-sm me-1" placeholder="IP" required>
            <button class="btn btn-sm btn-success">Unblock</button>
          </form>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive"><table class="table mb-0">
            <thead class="table-light"><tr><th scope="col">IP</th><th scope="col">Type</th><th scope="col">Reason</th><th scope="col">Expires</th></tr></thead>
            <tbody>
              <?php if (empty($blocked)): ?>
                <tr><td colspan="4" class="text-center py-3 text-muted">No blocked IPs</td></tr>
              <?php else: foreach ($blocked as $b): ?>
                <tr>
                  <td><code><?= htmlspecialchars($b['ip_address'] ?? '') ?></code></td>
                  <td><span class="badge bg-danger"><?= htmlspecialchars($b['block_type'] ?? '') ?></span></td>
                  <td><small><?= htmlspecialchars($b['reason'] ?? '') ?></small></td>
                  <td><small><?= htmlspecialchars($b['expires_at'] ?? 'permanent') ?></small></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table></div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Failed Logins (last 24h)</strong></div>
        <div class="card-body p-0">
          <div class="table-responsive"><table class="table mb-0">
            <thead class="table-light"><tr><th scope="col">Email</th><th scope="col">IP</th><th scope="col">Reason</th><th scope="col">Time</th></tr></thead>
            <tbody>
              <?php if (empty($failed)): ?>
                <tr><td colspan="4" class="text-center py-3 text-muted">No failed logins</td></tr>
              <?php else: foreach ($failed as $f): ?>
                <tr>
                  <td><small><?= htmlspecialchars($f['email'] ?? '') ?></small></td>
                  <td><code><?= htmlspecialchars($f['ip_address'] ?? '') ?></code></td>
                  <td><small><?= htmlspecialchars($f['reason'] ?? '') ?></small></td>
                  <td><small><?= htmlspecialchars($f['attempted_at'] ?? '') ?></small></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
