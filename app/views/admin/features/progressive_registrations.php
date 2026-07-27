<?php
$page_title = $page_title ?? 'Progressive Registrations';
$page_heading = $page_heading ?? 'Progressive Registrations';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><i class="fas fa-user-clock me-2"></i>Progressive Registrations</h1>

  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card border-left-primary shadow-sm">
        <div class="card-body aps-cp-card-body">
          <h6 class="text-muted">Active Sessions</h6>
          <h2 class="mb-0"><?= count($incomplete ?? []) ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-left-warning shadow-sm">
        <div class="card-body aps-cp-card-body">
          <h6 class="text-muted">Avg Completion Step</h6>
          <h2 class="mb-0">
            <?php
              $avg = 0;
              if (!empty($stats)) {
                $sum = 0; $c = 0;
                foreach ($stats as $s) { $sum += (float)$s['avg_step']; $c++; }
                $avg = $c > 0 ? round($sum / $c, 1) : 0;
              }
              echo $avg;
            ?>
          </h2>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-left-info shadow-sm">
        <div class="card-body aps-cp-card-body">
          <h6 class="text-muted">Funnel Stages</h6>
          <h2 class="mb-0"><?= count($stats ?? []) ?></h2>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-white"><strong>Incomplete Registrations (last 100)</strong></div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Token</th><th>Step</th><th>IP</th><th>Updated</th><th>Expires</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($incomplete)): ?>
              <tr><td colspan="5" class="text-center py-4 text-muted">No incomplete registrations</td></tr>
            <?php else: foreach ($incomplete as $r): ?>
              <tr>
                <td><code class="small"><?= htmlspecialchars(substr($r['token'] ?? '', 0, 12)) ?>…</code></td>
                <td><span class="badge bg-primary"><?= (int)$r['step'] ?></span></td>
                <td><small><?= htmlspecialchars($r['ip_address'] ?? '') ?></small></td>
                <td><small><?= htmlspecialchars($r['updated_at'] ?? '') ?></small></td>
                <td><small><?= htmlspecialchars($r['expires_at'] ?? '') ?></small></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
