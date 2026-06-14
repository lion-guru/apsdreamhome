<?php
$page_title = $page_title ?? 'Audit Log';
$page_heading = $page_heading ?? 'Audit Log';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><i class="fas fa-shield-alt me-2"></i>Audit Log</h1>

  <div class="row mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-primary text-white">
      <div class="card-body aps-cp-card-body"><small class="opacity-75">Total Events (7d)</small><h3 class="mb-0"><?= $stats['total'] ?? 0 ?></h3></div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-success text-white">
      <div class="card-body aps-cp-card-body"><small class="opacity-75">Successes</small><h3 class="mb-0"><?= ($stats['total'] ?? 0) - ($stats['failures'] ?? 0) ?></h3></div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-danger text-white">
      <div class="card-body aps-cp-card-body"><small class="opacity-75">Failures (7d)</small><h3 class="mb-0"><?= $stats['failures'] ?? 0 ?></h3></div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-info text-white">
      <div class="card-body aps-cp-card-body"><small class="opacity-75">Unique Actions</small><h3 class="mb-0"><?= count($stats['by_action'] ?? []) ?></h3></div>
    </div></div>
  </div>

  <div class="card shadow-sm mb-3">
    <div class="card-body aps-cp-card-body">
      <form method="get" class="row g-2">
        <div class="col-md-4">
          <input type="text" name="action" class="form-control" placeholder="Action (e.g. login, create_lead)" value="<?= htmlspecialchars($filter_action ?? '') ?>">
        </div>
        <div class="col-md-4">
          <input type="text" name="entity" class="form-control" placeholder="Entity type (e.g. user, booking)" value="<?= htmlspecialchars($filter_entity ?? '') ?>">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
        </div>
        <div class="col-md-2">
          <a href="<?= BASE_URL ?>/admin/audit-log" class="btn btn-secondary w-100"><i class="fas fa-times"></i> Clear</a>
        </div>
      </form>
    </div>
  </div>

  <?php if (!empty($stats['by_action'])): ?>
  <div class="row mb-3">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0">Top Actions (7d)</h5></div>
        <div class="card-body aps-cp-card-body">
          <?php foreach ($stats['by_action'] as $action => $count): ?>
            <div class="mb-2">
              <div class="d-flex justify-content-between">
                <span><code><?= htmlspecialchars($action) ?></code></span>
                <strong><?= $count ?></strong>
              </div>
              <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-primary" style="width: <?= min(100, ($count / max(1, $stats['total'])) * 100) ?>%"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Recent Events</h5>
      <span class="badge bg-secondary"><?= count($logs) ?> records</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Time</th>
              <th>User</th>
              <th>Action</th>
              <th>Entity</th>
              <th>Description</th>
              <th>IP</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($logs)): ?>
              <tr><td colspan="7" class="text-center py-4 text-muted">No audit events found</td></tr>
            <?php else: foreach ($logs as $l): ?>
              <tr>
                <td><small><?= htmlspecialchars($l['created_at'] ?? '') ?></small></td>
                <td>
                  <?php if ($l['user_name']): ?>
                    <strong><?= htmlspecialchars($l['user_name']) ?></strong>
                    <br><span class="badge bg-<?= ($l['user_role'] ?? '') === 'admin' ? 'danger' : 'secondary' ?> small"><?= htmlspecialchars($l['user_role'] ?? '') ?></span>
                  <?php else: ?>
                    <span class="text-muted">System</span>
                  <?php endif; ?>
                </td>
                <td><code><?= htmlspecialchars($l['action'] ?? '') ?></code></td>
                <td>
                  <?php if ($l['entity_type']): ?>
                    <span class="badge bg-info"><?= htmlspecialchars($l['entity_type']) ?> #<?= $l['entity_id'] ?? '' ?></span>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td><small><?= htmlspecialchars($l['description'] ?? '') ?></small></td>
                <td><small class="text-muted"><?= htmlspecialchars($l['ip_address'] ?? '') ?></small></td>
                <td>
                  <span class="badge bg-<?= ($l['status'] ?? 'success') === 'success' ? 'success' : (($l['status'] ?? '') === 'failure' ? 'warning' : 'danger') ?>">
                    <?= htmlspecialchars($l['status'] ?? 'success') ?>
                  </span>
                </td>
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
require_once APP_PATH . '/views/admin/layouts/admin.php';
