<?php
$page_title = $page_title ?? 'User Activity Timeline';
$user = $user ?? [];
$timeline = $timeline ?? [];
ob_start();
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1"><i class="fas fa-user-clock me-2 text-primary"></i>User Activity Timeline</h1>
      <p class="text-muted mb-0">
        Activity for <strong><?= htmlspecialchars($user['name'] ?? '') ?></strong>
        (<code><?= htmlspecialchars($user['email'] ?? '') ?></code>)
        <span class="badge bg-<?= in_array($user['role'] ?? '', ['admin', 'super_admin']) ? 'danger' : 'secondary' ?> ms-2">
          <?= htmlspecialchars(ucfirst($user['role'] ?? '')) ?>
        </span>
      </p>
    </div>
    <a href="<?= BASE_URL ?>/admin/audit-logs" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i>Back to Audit Logs
    </a>
  </div>

  <?php if (empty($timeline)): ?>
    <div class="card shadow-sm">
      <div class="card-body text-center py-5">
        <i class="fas fa-history fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No activity found</h5>
        <p class="text-muted">This user has no recorded activity in the audit logs.</p>
      </div>
    </div>
  <?php else: ?>
    <div class="card shadow-sm">
      <div class="card-header bg-white">
        <h5 class="mb-0">Timeline (<?= count($timeline) ?> events)</h5>
      </div>
      <div class="card-body p-0">
        <div class="timeline">
          <?php foreach ($timeline as $index => $log): ?>
            <div class="timeline-item position-relative">
              <div class="timeline-marker position-absolute" style="left: 20px; top: 8px;">
                <div class="rounded-circle bg-<?= match($log['status'] ?? 'success') {
                  'success' => 'success',
                  'failed' => 'danger',
                  'pending' => 'warning',
                  default => 'secondary'
                } ?>" style="width: 16px; height: 16px; border: 3px solid white; box-shadow: 0 0 0 2px <?= match($log['status'] ?? 'success') {
                  'success' => '#198754',
                  'failed' => '#dc3545',
                  'pending' => '#ffc107',
                  default => '#6c757d'
                } ?>;"></div>
              </div>
              <div class="ps-5 pb-4 border-start" style="border-left: 2px solid #e9ecef; padding-left: 50px;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <span class="badge bg-<?= match($log['action_type'] ?? 'update') {
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
                    } ?> me-1">
                      <?= htmlspecialchars(ucfirst($log['action_type'] ?? '')) ?>
                    </span>
                    <code class="ms-1"><?= htmlspecialchars($log['action'] ?? '') ?></code>
                  </div>
                  <small class="text-muted"><?= htmlspecialchars($log['created_at'] ?? '') ?></small>
                </div>
                <?php if (!empty($log['entity_type'])): ?>
                  <div class="mb-1">
                    <span class="badge bg-info me-1"><?= htmlspecialchars($log['entity_type']) ?></span>
                    <?php if (!empty($log['entity_id'])): ?>
                      <code class="small">#<?= htmlspecialchars($log['entity_id']) ?></code>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
                <?php if (!empty($log['description'])): ?>
                  <p class="text-muted small mb-1"><?= htmlspecialchars($log['description']) ?></p>
                <?php endif; ?>
                <?php if (!empty($log['ip_address'])): ?>
                  <div class="d-flex gap-3 small text-muted">
                    <span><i class="fas fa-network-wired me-1"></i><?= htmlspecialchars($log['ip_address']) ?></span>
                    <?php if (!empty($log['request_url'])): ?>
                      <span><i class="fas fa-link me-1"></i><?= htmlspecialchars($log['request_url']) ?></span>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<style>
  .timeline-item:last-child .ps-5 {
    border-left-color: transparent;
  }
</style>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';