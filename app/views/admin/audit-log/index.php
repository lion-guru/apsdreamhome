<?php
$page_title = $page_title ?? 'Audit Logs';
$filters = $filters ?? [];
$logs = $logs ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$total_pages = $total_pages ?? 1;
$limit = $limit ?? 50;
$stats = $stats ?? [];
$roles = $roles ?? [];
$actions = $actions ?? [];
$entity_types = $entity_types ?? [];
ob_start();
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-shield-alt me-2 text-primary"></i>Audit Logs</h1>
    <a href="<?= BASE_URL ?>/admin/audit-logs/stats" class="btn btn-outline-primary">
      <i class="fas fa-chart-bar me-1"></i>Statistics
    </a>
  </div>

  <!-- Stats Cards -->
  <div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
      <div class="card border-0 shadow-sm h-100 bg-primary text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <small class="opacity-75">Total Events (30d)</small>
              <h3 class="mb-0"><?= number_format($stats['total'] ?? 0) ?></h3>
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
              <small class="opacity-75">Success Rate</small>
              <?php 
                $successCount = 0;
                if (!empty($stats['by_status'])) {
                  foreach ($stats['by_status'] as $s) {
                    if (($s['status'] ?? '') === 'success') $successCount = $s['cnt'];
                  }
                }
              ?>
              <h3 class="mb-0"><?= $stats['total'] > 0 ? round($successCount / $stats['total'] * 100, 1) : 0 ?>%</h3>
            </div>
            <i class="fas fa-check-circle fa-2x opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
      <div class="card border-0 shadow-sm h-100 bg-warning text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <small class="opacity-75">Failed Events</small>
              <?php 
                $failedCount = 0;
                if (!empty($stats['by_status'])) {
                  foreach ($stats['by_status'] as $s) {
                    if (($s['status'] ?? '') === 'failed') $failedCount = $s['cnt'];
                  }
                }
              ?>
              <h3 class="mb-0"><?= number_format($failedCount) ?></h3>
            </div>
            <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
      <div class="card border-0 shadow-sm h-100 bg-info text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <small class="opacity-75">Unique Actions</small>
              <h3 class="mb-0"><?= count($stats['by_action'] ?? []) ?></h3>
            </div>
            <i class="fas fa-bolt fa-2x opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
      <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
    </div>
    <div class="card-body">
      <form method="get" class="row g-3">
    <?php echo CSRFProtection::csrfField(); ?>
        <div class="col-md-3">
          <label class="form-label small">User Role</label>
          <select name="user_role" class="form-select form-select-sm">
            <option value="">All Roles</option>
            <?php foreach ($roles as $r): ?>
              <option value="<?= htmlspecialchars($r) ?>" <?= ($filters['user_role'] ?? '') === $r ? 'selected' : '' ?>>
                <?= htmlspecialchars($r) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small">Action</label>
          <select name="action" class="form-select form-select-sm">
            <option value="">All Actions</option>
            <?php foreach ($actions as $a): ?>
              <option value="<?= htmlspecialchars($a) ?>" <?= ($filters['action'] ?? '') === $a ? 'selected' : '' ?>>
                <?= htmlspecialchars($a) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small">Entity Type</label>
          <select name="entity_type" class="form-select form-select-sm">
            <option value="">All Types</option>
            <?php foreach ($entity_types as $e): ?>
              <option value="<?= htmlspecialchars($e) ?>" <?= ($filters['entity_type'] ?? '') === $e ? 'selected' : '' ?>>
                <?= htmlspecialchars($e) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small">Status</label>
          <select name="status" class="form-select form-select-sm">
            <option value="">All Status</option>
            <option value="success" <?= ($filters['status'] ?? '') === 'success' ? 'selected' : '' ?>>Success</option>
            <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
            <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
          </select>
        </div>
        <div class="col-md-1">
          <label class="form-label small">Date From</label>
          <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
        </div>
        <div class="col-md-1">
          <label class="form-label small">Date To</label>
          <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
        </div>
        <div class="col-12 d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-search me-1"></i>Apply
          </button>
          <a href="<?= BASE_URL ?>/admin/audit-logs" class="btn btn-secondary btn-sm">
            <i class="fas fa-times me-1"></i>Clear
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Top Actions Chart -->
  <?php if (!empty($stats['by_action'])): ?>
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
      <h5 class="mb-0">Top Actions (30d)</h5>
    </div>
    <div class="card-body">
        <?php foreach (array_slice($stats['by_action'], 0, 10, true) as $item): ?>
          <div class="mb-2">
            <div class="d-flex justify-content-between">
              <span><code><?= htmlspecialchars($item['action']) ?></code></span>
              <strong><?= number_format($item['cnt']) ?></strong>
            </div>
            <div class="progress" style="height: 6px;">
              <div class="progress-bar bg-primary" style="width: <?= min(100, ($item['cnt'] / max(1, $stats['total'] ?? 1)) * 100) ?>%"></div>
            </div>
          </div>
        <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Logs Table -->
  <div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Audit Events</h5>
      <span class="badge bg-secondary"><?= number_format($total) ?> total events</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 160px;">Time</th>
              <th style="width: 200px;">User</th>
              <th style="width: 150px;">Action</th>
              <th style="width: 100px;">Type</th>
              <th>Entity</th>
              <th style="width: 250px;">Description</th>
              <th style="width: 100px;">IP</th>
              <th style="width: 90px;">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($logs)): ?>
              <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                  <i class="fas fa-shield-alt fa-2x mb-2 opacity-25"></i>
                  <p class="mb-0">No audit events found</p>
                </td>
              </tr>
            <?php else: foreach ($logs as $l): ?>
              <tr>
                <td><small><?= htmlspecialchars($l['created_at'] ?? '') ?></small></td>
                <td>
                  <?php if (!empty($l['user_name'])): ?>
                    <strong><?= htmlspecialchars($l['user_name']) ?></strong>
                    <br>
                    <span class="badge bg-<?= in_array($l['user_role'] ?? '', ['admin', 'super_admin']) ? 'danger' : 'secondary' ?> small">
                      <?= htmlspecialchars($l['user_role'] ?? '') ?>
                    </span>
                  <?php else: ?>
                    <span class="text-muted">System</span>
                  <?php endif; ?>
                </td>
                <td><code><?= htmlspecialchars($l['action'] ?? '') ?></code></td>
                <td>
                  <span class="badge bg-<?= match($l['action_type'] ?? 'update') {
                    'create' => 'success',
                    'read' => 'info',
                    'update' => 'warning',
                    'delete' => 'danger',
                    'login' => 'primary',
                    'logout' => 'secondary',
                    'approve' => 'success',
                    'reject' => 'danger',
                    'payment' => 'info',
                    'commission' => 'warning',
                    default => 'secondary'
                  } ?>">
                    <?= htmlspecialchars(ucfirst($l['action_type'] ?? '')) ?>
                  </span>
                </td>
                <td>
                  <?php if (!empty($l['entity_type'])): ?>
                    <span class="badge bg-info">
                      <?= htmlspecialchars($l['entity_type']) ?>
                      <?php if (!empty($l['entity_id'])): ?>
                        #<?= htmlspecialchars($l['entity_id']) ?>
                      <?php endif; ?>
                    </span>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td><small><?= htmlspecialchars($l['description'] ?? '') ?></small></td>
                <td><small class="text-muted"><?= htmlspecialchars($l['ip_address'] ?? '') ?></small></td>
                <td>
                  <span class="badge bg-<?= ($l['status'] ?? 'success') === 'success' ? 'success' : (($l['status'] ?? '') === 'failed' ? 'warning' : 'danger') ?>">
                    <?= htmlspecialchars(ucfirst($l['status'] ?? 'success')) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="card-footer bg-white">
      <nav aria-label="Audit logs pagination">
        <ul class="pagination pagination-sm mb-0 justify-content-center">
          <?php if ($page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="<?= BASE_URL ?>/admin/audit-logs?page=<?= $page - 1 ?>" aria-label="Previous">
                <i class="fas fa-chevron-left"></i>
              </a>
            </li>
          <?php endif; ?>
          <?php
            $start = max(1, $page - 2);
            $end = min($total_pages, $page + 2);
            if ($start > 1) {
              echo '<li class="page-item"><a class="page-link" href="' . BASE_URL . '/admin/audit-logs?page=1">1</a></li>';
              if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            for ($i = $start; $i <= $end; $i++): ?>
              <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= BASE_URL ?>/admin/audit-logs?page=<?= $i ?>"><?= $i ?></a>
              </li>
          <?php endfor; ?>
          <?php if ($end < $total_pages): ?>
            <?php if ($end < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; ?>
            <li class="page-item">
              <a class="page-link" href="<?= BASE_URL ?>/admin/audit-logs?page=<?= $total_pages ?>"><?= $total_pages ?></a>
            </li>
          <?php endif; ?>
          <?php if ($page < $total_pages): ?>
            <li class="page-item">
              <a class="page-link" href="<?= BASE_URL ?>/admin/audit-logs?page=<?= $page + 1 ?>" aria-label="Next">
                <i class="fas fa-chevron-right"></i>
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';