<?php
$page_title = $page_title ?? 'Audit Log Detail';
$log = $log ?? [];
$related = $related ?? [];
ob_start();
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Audit Log Detail</h1>
      <p class="text-muted mb-0">Event ID: <code><?= $log['id'] ?? '' ?></code></p>
    </div>
    <a href="<?= BASE_URL ?>/admin/audit-logs" class="btn btn-secondary">
      <i class="fas fa-arrow-left me-1"></i>Back to List
    </a>
  </div>

  <div class="row">
    <!-- Main Info -->
    <div class="col-xl-8 mb-4">
      <div class="card shadow-sm mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Event Details</h5>
          <div>
            <span class="badge bg-<?= ($log['status'] ?? 'success') === 'success' ? 'success' : (($log['status'] ?? '') === 'failed' ? 'warning' : 'danger') ?> fs-6">
              <?= htmlspecialchars(ucfirst($log['status'] ?? 'success')) ?>
            </span>
            <span class="badge bg-<?= match($log['action_type'] ?? 'update') {
              'create' => 'success', 'read' => 'info', 'update' => 'warning', 'delete' => 'danger',
              'login' => 'primary', 'logout' => 'secondary', 'approve' => 'success', 'reject' => 'danger',
              'payment' => 'info', 'commission' => 'warning', default => 'secondary'
            } ?> ms-2 fs-6">
              <?= htmlspecialchars(ucfirst($log['action_type'] ?? 'update')) ?>
            </span>
          </div>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label small text-muted">Action</label>
              <code class="d-block fs-6"><?= htmlspecialchars($log['action'] ?? '') ?></code>
            </div>
            <div class="col-md-6">
              <label class="form-label small text-muted">Timestamp</label>
              <div class="fs-6"><?= htmlspecialchars($log['created_at'] ?? '') ?></div>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label small text-muted">User</label>
              <?php if (!empty($log['user_name'])): ?>
                <div class="fs-6">
                  <strong><?= htmlspecialchars($log['user_name'] ?? '') ?></strong>
                  <span class="badge bg-<?= in_array($log['user_role'] ?? '', ['admin', 'super_admin']) ? 'danger' : 'secondary' ?> ms-2">
                    <?= htmlspecialchars($log['user_role'] ?? '') ?>
                  </span>
                </div>
              <?php else: ?>
                <span class="text-muted">System</span>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label small text-muted">User ID</label>
              <code class="d-block fs-6"><?= $log['user_id'] ?? 'N/A' ?></code>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label small text-muted">IP Address</label>
              <code class="d-block fs-6"><?= htmlspecialchars($log['ip_address'] ?? '') ?></code>
            </div>
            <div class="col-md-4">
              <label class="form-label small text-muted">Session ID</label>
              <code class="d-block fs-6 small"><?= htmlspecialchars(substr($log['session_id'] ?? '', 0, 20)) ?>...</code>
            </div>
            <div class="col-md-4">
              <label class="form-label small text-muted">Request Method</label>
              <code class="d-block fs-6"><?= htmlspecialchars($log['request_method'] ?? '') ?></code>
            </div>
          </div>
          <?php if (!empty($log['entity_type'])): ?>
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label small text-muted">Entity</label>
                <div class="fs-6">
                  <span class="badge bg-info"><?= htmlspecialchars($log['entity_type'] ?? '') ?></span>
                  <?php if (!empty($log['entity_id'])): ?>
                    <span class="ms-2">#<?= htmlspecialchars($log['entity_id'] ?? '') ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
          <?php if (!empty($log['description'])): ?>
            <div class="mb-3">
              <label class="form-label small text-muted">Description</label>
              <p class="text-muted"><?= htmlspecialchars($log['description'] ?? '') ?></p>
            </div>
          <?php endif; ?>
          <?php if (!empty($log['request_url'])): ?>
            <div class="mb-3">
              <label class="form-label small text-muted">Request URL</label>
              <a href="<?= htmlspecialchars($log['request_url'] ?? '') ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-external-link-alt me-1"></i>Open in New Tab
              </a>
            </div>
          <?php endif; ?>
          <?php if (!empty($log['user_agent'])): ?>
            <div class="mb-3">
              <label class="form-label small text-muted">User Agent</label>
              <pre class="bg-light p-2 rounded small text-muted"><code><?= htmlspecialchars($log['user_agent'] ?? '') ?></code></pre>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Changes -->
      <?php if (!empty($log['old_values']) || !empty($log['new_values'])): ?>
        <div class="card shadow-sm mb-3">
          <div class="card-header bg-white">
            <h5 class="mb-0">Changes</h5>
          </div>
          <div class="card-body">
            <div class="row">
              <?php if (!empty($log['old_values'])): ?>
                <div class="col-12 col-md-6">
                  <label class="form-label small text-danger">Old Values</label>
                  <pre class="bg-danger bg-opacity-10 p-3 rounded small text-danger"><code><?= json_encode(json_decode($log['old_values'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></code></pre>
                </div>
              <?php endif; ?>
              <?php if (!empty($log['new_values'])): ?>
                <div class="col-12 col-md-6">
                  <label class="form-label small text-success">New Values</label>
                  <pre class="bg-success bg-opacity-10 p-3 rounded small text-success"><code><?= json_encode(json_decode($log['new_values'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></code></pre>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Metadata -->
      <?php if (!empty($log['metadata'])): ?>
        <div class="card shadow-sm mb-3">
          <div class="card-header bg-white">
            <h5 class="mb-0">Metadata</h5>
          </div>
          <div class="card-body">
            <pre class="bg-light p-3 rounded small"><code><?= json_encode(json_decode($log['metadata'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></code></pre>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-xl-4">
      <!-- Related Events -->
      <div class="card shadow-sm mb-3 sticky-top style-76854">
        <div class="card-header bg-white">
          <h5 class="mb-0">Related Events</h5>
        </div>
        <div class="card-body p-0">
          <?php if (empty($related)): ?>
            <div class="p-3 text-center text-muted">
              <i class="fas fa-link fa-2x mb-2 opacity-25"></i>
              <p class="mb-0 small">No related events found</p>
            </div>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach ($related as $r): ?>
                <a href="<?= BASE_URL ?>/admin/audit-logs/detail/<?= $r['id'] ?>" class="list-group-item list-group-item-action">
                  <div class="d-flex justify-content-between">
                    <code class="small"><?= htmlspecialchars($r['action'] ?? '') ?></code>
                    <span class="badge bg-<?= ($r['status'] ?? 'success') === 'success' ? 'success' : 'warning' ?> small">
                      <?= htmlspecialchars(ucfirst($r['status'] ?? 'success')) ?>
                    </span>
                  </div>
                  <div class="small text-muted mt-1">
                    <?= htmlspecialchars($r['created_at'] ?? '') ?>
                    <?php if (!empty($r['entity_type'])): ?>
                      Â· <span class="badge bg-info"><?= htmlspecialchars($r['entity_type'] ?? '') ?></span>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($r['description'])): ?>
                    <p class="small text-muted mb-0 mt-1"><?= htmlspecialchars(substr($r['description'], 0, 100)) ?>...</p>
                  <?php endif; ?>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card shadow-sm sticky-top style-76854">
        <div class="card-header bg-white">
          <h5 class="mb-0">Quick Actions</h5>
        </div>
        <div class="card-body">
          <div class="d-grid gap-2">
            <?php if (!empty($log['entity_type']) && !empty($log['entity_id'])): ?>
              <a href="<?= BASE_URL ?>/admin/audit-logs/entity?entity_type=<?= urlencode($log['entity_type']) ?>&entity_id=<?= $log['entity_id'] ?>" class="btn btn-outline-primary">
                <i class="fas fa-timeline me-1"></i>View Entity Timeline
              </a>
              <a href="<?= BASE_URL ?>/admin/audit-logs/user/<?= $log['user_id'] ?>" class="btn btn-outline-secondary">
                <i class="fas fa-user me-1"></i>View User Timeline
              </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/audit-logs" class="btn btn-outline-secondary">
              <i class="fas fa-list me-1"></i>Back to All Logs
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';