<?php
$page_title = $page_title ?? 'Webhooks';
$page_heading = $page_heading ?? 'Webhooks';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-plug me-2"></i>Webhooks</h1>
    <form method="post" action="<?= BASE_URL ?>/admin/webhooks/process" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <button type="submit" class="btn btn-outline-primary btn-sm"><i class="fas fa-paper-plane"></i> Process Pending</button>
    </form>
  </div>

  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_SESSION['flash_success'] ?? '') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_success']); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($_SESSION['flash_error'] ?? '') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <div class="row mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-primary text-white">
      <div class="card-body aps-cp-card-body"><small class="opacity-75">Total (7d)</small><h3 class="mb-0"><?= $stats['total'] ?? 0 ?></h3></div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-success text-white">
      <div class="card-body aps-cp-card-body"><small class="opacity-75">Success</small><h3 class="mb-0"><?= $stats['success'] ?? 0 ?></h3></div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-warning text-white">
      <div class="card-body aps-cp-card-body"><small class="opacity-75">Retrying</small><h3 class="mb-0"><?= $stats['retrying'] ?? 0 ?></h3></div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-danger text-white">
      <div class="card-body aps-cp-card-body"><small class="opacity-75">Failed</small><h3 class="mb-0"><?= $stats['failed'] ?? 0 ?></h3></div>
    </div></div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h5 class="mb-0">Add New Endpoint</h5></div>
    <div class="card-body aps-cp-card-body">
      <form method="post" action="<?= BASE_URL ?>/admin/webhooks/create">
                          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="row g-2">
          <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Name (e.g. Slack Alerts)" required></div>
          <div class="col-md-4"><input type="url" name="url" class="form-control" placeholder="https://example.com/webhook" required></div>
          <div class="col-md-3">
            <input type="text" name="events" class="form-control" placeholder="Events: lead.created,booking.*,* (comma-separated)">
          </div>
          <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus"></i> Add</button></div>
        </div>
        <div class="row mt-2">
          <div class="col-md-6">
            <input type="text" name="secret" class="form-control" placeholder="HMAC secret (auto-generated if empty)">
          </div>
          <div class="col-md-6">
            <small class="text-muted">Events: <code>lead.created</code>, <code>lead.updated</code>, <code>booking.*</code>, <code>*</code> (all). HMAC-SHA256 signature in <code>X-Webhook-Signature</code> header.</small>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h5 class="mb-0">Endpoints (<?= count($endpoints) ?>)</h5></div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Name</th>
              <th>URL</th>
              <th>Events</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($endpoints)): ?>
              <tr><td colspan="6" class="text-center py-4 text-muted">No webhook endpoints registered</td></tr>
            <?php else: foreach ($endpoints as $ep): ?>
              <tr>
                <td><strong><?= htmlspecialchars($ep['name'] ?? '') ?></strong></td>
                <td><code class="small"><?= htmlspecialchars(substr($ep['url'], 0, 60)) ?><?= strlen($ep['url']) > 60 ? '...' : '' ?></code></td>
                <td>
                  <?php foreach (array_slice(explode(',', $ep['events'] ?? ''), 0, 3) as $ev): ?>
                    <span class="badge bg-secondary me-1"><?= htmlspecialchars($ev ?? '') ?></span>
                  <?php endforeach; ?>
                </td>
                <td>
                  <span class="badge bg-<?= ($ep['is_active'] ?? 0) ? 'success' : 'secondary' ?>">
                    <?= ($ep['is_active'] ?? 0) ? 'Active' : 'Inactive' ?>
                  </span>
                </td>
                <td><small><?= htmlspecialchars($ep['created_at'] ?? '') ?></small></td>
                <td>
                  <form method="post" action="<?= BASE_URL ?>/admin/webhooks/toggle/<?= $ep['id'] ?>" class="d-inline">
                                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="active" value="<?= ($ep['is_active'] ?? 0) ? 0 : 1 ?>">
                    <button class="btn btn-sm btn-outline-<?= ($ep['is_active'] ?? 0) ? 'warning' : 'success' ?>" title="Toggle">
                      <i class="fas fa-<?= ($ep['is_active'] ?? 0) ? 'pause' : 'play' ?>"></i>
                    </button>
                  </form>
                  <form method="post" action="<?= BASE_URL ?>/admin/webhooks/delete/<?= $ep['id'] ?>" class="d-inline" onsubmit="return confirm('Delete this endpoint?')">
                                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Recent Deliveries</h5>
      <span class="badge bg-secondary"><?= count($deliveries) ?> records</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Endpoint</th>
              <th>Event</th>
              <th>Attempt</th>
              <th>Status</th>
              <th>Response</th>
              <th>Time</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($deliveries)): ?>
              <tr><td colspan="7" class="text-center py-4 text-muted">No deliveries yet</td></tr>
            <?php else: foreach ($deliveries as $d): ?>
              <tr>
                <td><code>#<?= $d['id'] ?></code></td>
                <td><?= htmlspecialchars($d['endpoint_name'] ?? '') ?></td>
                <td><code><?= htmlspecialchars($d['event_type'] ?? '') ?></code></td>
                <td><?= $d['attempt'] ?? 1 ?></td>
                <td>
                  <span class="badge bg-<?= $d['status'] === 'success' ? 'success' : ($d['status'] === 'pending' ? 'secondary' : ($d['status'] === 'retrying' ? 'warning' : 'danger')) ?>">
                    <?= htmlspecialchars($d['status'] ?? '') ?>
                  </span>
                </td>
                <td>
                  <?php if ($d['response_code']): ?>
                    <code class="small"><?= $d['response_code'] ?></code>
                  <?php endif; ?>
                  <?php if ($d['error_message']): ?>
                    <small class="text-danger d-block"><?= htmlspecialchars(substr($d['error_message'], 0, 60)) ?></small>
                  <?php endif; ?>
                </td>
                <td><small><?= htmlspecialchars($d['created_at'] ?? '') ?></small></td>
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
