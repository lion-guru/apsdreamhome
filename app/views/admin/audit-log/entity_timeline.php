<?php
$page_title = $page_title ?? 'Entity Timeline';
$entity_type = $entity_type ?? '';
$entity_id = $entity_id ?? 0;
$timeline = $timeline ?? [];
ob_start();
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1"><i class="fas fa-history me-2 text-primary"></i>Entity Timeline</h1>
      <p class="text-muted mb-0">Complete history for <strong><?= htmlspecialchars($entity_type ?? '') ?></strong> <code>#<?= $entity_id ?></code></p>
    </div>
    <a href="<?= BASE_URL ?>/admin/audit-logs" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i>Back to Audit Logs
    </a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <?php if (empty($timeline)): ?>
        <div class="text-center py-5">
          <i class="fas fa-history fa-3x mb-3 opacity-25"></i>
          <p class="text-muted mb-0">No history recorded for this entity</p>
        </div>
      <?php else: ?>
        <div class="timeline px-4 py-4">
          <?php foreach ($timeline as $index => $t): ?>
            <div class="timeline-item position-relative pb-4 style-31335">
              <div class="position-absolute style-97302">
                <div class="timeline-marker bg-<?= ($t['status'] ?? 'success') === 'success' ? 'success' : 'danger' ?> rounded-circle border border-3 border-white shadow-sm style-41849">
                  <i class="fas fa-<?= $t['action_type'] === 'login' ? 'sign-in-alt' : ($t['action_type'] === 'logout' ? 'sign-out-alt' : ($t['action_type'] === 'create' ? 'plus' : ($t['action_type'] === 'delete' ? 'trash' : 'edit'))) ?> text-white small"></i>
                </div>
                <?php if ($index < count($timeline) - 1): ?>
                  <div class="timeline-line style-63524"></div>
                <?php endif; ?>
              </div>
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <code class="me-2"><?= htmlspecialchars($t['action'] ?? '') ?></code>
                  <span class="badge bg-<?= ($t['status'] ?? 'success') === 'success' ? 'success' : 'danger' ?> small">
                    <?= htmlspecialchars(ucfirst($t['status'] ?? 'success')) ?>
                  </span>
                </div>
                <small class="text-muted"><?= htmlspecialchars($t['created_at'] ?? '') ?></small>
              </div>
              <div class="mb-2">
                <span class="badge bg-<?= in_array($t['user_role'] ?? '', ['admin', 'super_admin']) ? 'danger' : 'secondary' ?> me-1">
                  <?= htmlspecialchars($t['user_role'] ?? '') ?>
                </span>
                <?php if (!empty($t['user_name'])): ?>
                  <strong><?= htmlspecialchars($t['user_name'] ?? '') ?></strong>
                <?php else: ?>
                  <span class="text-muted"><i class="fas fa-robot me-1"></i>System</span>
                <?php endif; ?>
              </div>
              <?php if (!empty($t['description'])): ?>
                <p class="text-muted small mb-1"><?= htmlspecialchars($t['description'] ?? '') ?></p>
              <?php endif; ?>
              <?php if (!empty($t['new_values']) || !empty($t['old_values'])): ?>
                <details class="mb-2">
                  <summary class="text-primary small cursor-pointer">Show data changes</summary>
                  <div class="row mt-2">
                    <?php if (!empty($t['old_values'])): ?>
                      <div class="col-md-6">
                        <small class="text-danger">Old:</small>
                        <pre class="bg-light p-2 rounded small"><code><?= json_encode(json_decode($t['old_values'], true), JSON_PRETTY_PRINT) ?></code></pre>
                      </div>
                    <?php endif; ?>
                    <?php if (!empty($t['new_values'])): ?>
                      <div class="col-md-6">
                        <small class="text-success">New:</small>
                        <pre class="bg-light p-2 rounded small"><code><?= json_encode(json_decode($t['new_values'], true), JSON_PRETTY_PRINT) ?></code></pre>
                      </div>
                    <?php endif; ?>
                  </div>
                </details>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php
require_once APP_PATH . '/views/layouts/admin_footer.php';