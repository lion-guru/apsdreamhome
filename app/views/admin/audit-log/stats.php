<?php
$page_title = $page_title ?? 'Audit Log Statistics';
$stats = $stats ?? [];
$days = $days ?? 30;
$total = $stats['total'] ?? 0;
ob_start();
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Audit Log Statistics</h1>
    <div class="btn-group">
      <a href="<?= BASE_URL ?>/admin/audit-logs/stats?days=7" class="btn btn-outline-<?= $days === 7 ? 'primary' : 'secondary' ?>">7 Days</a>
      <a href="<?= BASE_URL ?>/admin/audit-logs/stats?days=30" class="btn btn-outline-<?= $days === 30 ? 'primary' : 'secondary' ?>">30 Days</a>
      <a href="<?= BASE_URL ?>/admin/audit-logs/stats?days=90" class="btn btn-outline-<?= $days === 90 ? 'primary' : 'secondary' ?>">90 Days</a>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
      <div class="card border-0 shadow-sm h-100 bg-primary text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <small class="opacity-75">Total Events (<?= $days ?>d)</small>
              <h3 class="mb-0"><?= number_format($total) ?></h3>
            </div>
            <i class="fas fa-list-alt fa-2x opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
      <div class="card border-0 shadow-sm h-100 bg-success text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <small class="opacity-75">Top Action</small>
              <?php if (!empty($stats['by_action'])): ?>
                <?php $topAction = $stats['by_action'][0]; ?>
                <h6 class="mb-0 small"><?= htmlspecialchars($topAction['action'] ?? '') ?></h6>
                <small><?= number_format($topAction['cnt'] ?? 0) ?> events</small>
              <?php else: ?>
                <h6 class="mb-0 small">—</h6>
              <?php endif; ?>
            </div>
            <i class="fas fa-bolt fa-2x opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
      <div class="card border-0 shadow-sm h-100 bg-info text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <small class="opacity-75">Top Role</small>
              <?php if (!empty($stats['by_role'])): ?>
                <?php $topRole = $stats['by_role'][0]; ?>
                <h6 class="mb-0 small"><?= htmlspecialchars($topRole['user_role'] ?? '') ?></h6>
                <small><?= number_format($topRole['cnt'] ?? 0) ?> events</small>
              <?php else: ?>
                <h6 class="mb-0 small">—</h6>
              <?php endif; ?>
            </div>
            <i class="fas fa-user-tie fa-2x opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
      <div class="card border-0 shadow-sm h-100 bg-warning text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <small class="opacity-75">Success Rate</small>
              <?php 
                $success = 0; $failed = 0;
                foreach ($stats['by_status'] ?? [] as $s) {
                  if (($s['status'] ?? '') === 'success') $success = $s['cnt'] ?? 0;
                  if (($s['status'] ?? '') === 'failed') $failed = $s['cnt'] ?? 0;
                }
                $rate = ($success + $failed) > 0 ? round($success / ($success + $failed) * 100) : 0;
              ?>
              <h3 class="mb-0"><?= $rate ?>%</h3>
            </div>
            <i class="fas fa-check-circle fa-2x opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white">
          <h5 class="mb-0">Events by Action</h5>
        </div>
        <div class="card-body">
          <?php if (!empty($stats['by_action'])): ?>
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr><th>Action</th><th class="text-end">Count</th><th class="text-end">%</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($stats['by_action'] as $item): ?>
                    <tr>
                      <td><code><?= htmlspecialchars($item['action'] ?? '') ?></code></td>
                      <td class="text-end"><?= number_format($item['cnt'] ?? 0) ?></td>
                      <td class="text-end">
                        <div class="progress" class="style-17822">
                          <div class="progress-bar bg-primary" class="style-88739"></div>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-muted text-center py-4 mb-0">No data available</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white">
          <h5 class="mb-0">Events by Role</h5>
        </div>
        <div class="card-body">
          <?php if (!empty($stats['by_role'])): ?>
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr><th>Role</th><th class="text-end">Count</th><th class="text-end">%</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($stats['by_role'] as $item): ?>
                    <tr>
                      <td>
                        <span class="badge bg-<?= in_array($item['user_role'] ?? '', ['admin', 'super_admin']) ? 'danger' : 'secondary' ?> me-1">
                          <?= htmlspecialchars($item['user_role'] ?? '') ?>
                        </span>
                      </td>
                      <td class="text-end"><?= number_format($item['cnt'] ?? 0) ?></td>
                      <td class="text-end">
                        <div class="progress" class="style-17822">
                          <div class="progress-bar bg-<?= in_array($item['user_role'] ?? '', ['admin', 'super_admin']) ? 'danger' : 'secondary' ?>" class="style-88739"></div>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-muted text-center py-4 mb-0">No data available</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white">
          <h5 class="mb-0">Events by Status</h5>
        </div>
        <div class="card-body">
          <?php if (!empty($stats['by_status'])): ?>
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr><th>Status</th><th class="text-end">Count</th><th class="text-end">%</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($stats['by_status'] as $s): ?>
                    <tr>
                      <td>
                        <span class="badge bg-<?= $s['status'] === 'success' ? 'success' : ($s['status'] === 'failed' ? 'danger' : 'warning') ?> me-1">
                          <?= htmlspecialchars(ucfirst($s['status'])) ?>
                        </span>
                      </td>
                      <td class="text-end"><?= number_format($s['cnt'] ?? 0) ?></td>
                      <td class="text-end">
                        <div class="progress" class="style-17822">
                          <div class="progress-bar bg-<?= $s['status'] === 'success' ? 'success' : ($s['status'] === 'failed' ? 'danger' : 'warning') ?>" class="style-8668"></div>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-muted text-center py-4 mb-0">No data available</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white">
          <h5 class="mb-0">Top Users by Activity</h5>
        </div>
        <div class="card-body">
          <?php if (!empty($stats['by_user'])): ?>
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr><th>User</th><th>Role</th><th class="text-end">Events</th></tr>
                </thead>
                <tbody>
                  <?php foreach (array_slice($stats['by_user'], 0, 10) as $u): ?>
                    <tr>
                      <td>
                        <strong><?= htmlspecialchars($u['name'] ?? 'Unknown') ?></strong>
                        <?php if (!empty($u['email'])): ?><br><small class="text-muted"><?= htmlspecialchars($u['email']) ?></small><?php endif; ?>
                      </td>
                      <td>
                        <span class="badge bg-<?= in_array($u['user_role'] ?? '', ['admin', 'super_admin']) ? 'danger' : 'secondary' ?>">
                          <?= htmlspecialchars($u['user_role'] ?? '') ?>
                        </span>
                      </td>
                      <td class="text-end"><?= number_format($u['cnt'] ?? 0) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-muted text-center py-4 mb-0">No data available</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';