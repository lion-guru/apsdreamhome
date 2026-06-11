<?php
$page_title = $page_title ?? 'API Keys';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><i class="fas fa-key me-2"></i>API Keys</h1>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($_SESSION['flash_error']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <?php if ($new_key): ?>
    <div class="alert alert-success">
      <h5><i class="fas fa-check-circle"></i> API Key Created</h5>
      <p class="mb-2"><strong>Save the API secret now - it won't be shown again!</strong></p>
      <div class="bg-light p-2 rounded mb-2">
        <div><strong>Name:</strong> <?= htmlspecialchars($new_key['name']) ?></div>
        <div><strong>API Key:</strong> <code class="user-select-all"><?= htmlspecialchars($new_key['api_key']) ?></code></div>
        <div><strong>API Secret:</strong> <code class="user-select-all"><?= htmlspecialchars($new_key['api_secret']) ?></code></div>
        <div><strong>Scopes:</strong> <?= htmlspecialchars(implode(', ', $new_key['scopes'])) ?></div>
        <div><strong>Rate Limit:</strong> <?= $new_key['rate_limit'] ?> req/min</div>
      </div>
      <small class="text-muted">Use these credentials in the Authorization header: <code>Authorization: Bearer &lt;api_key&gt;:&lt;api_secret&gt;</code></small>
    </div>
  <?php endif; ?>

  <div class="row mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-primary text-white">
      <div class="card-body aps-cp-card-body"><small class="opacity-75">Total Keys</small><h3 class="mb-0"><?= $stats['total'] ?? 0 ?></h3></div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-success text-white">
      <div class="card-body aps-cp-card-body"><small class="opacity-75">Active</small><h3 class="mb-0"><?= $stats['active'] ?? 0 ?></h3></div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-warning text-white">
      <div class="card-body aps-cp-card-body"><small class="opacity-75">Revoked</small><h3 class="mb-0"><?= $stats['revoked'] ?? 0 ?></h3></div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-info text-white">
      <div class="card-body aps-cp-card-body"><small class="opacity-75">Used Today</small><h3 class="mb-0"><?= $stats['used_today'] ?? 0 ?></h3></div>
    </div></div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h5 class="mb-0">Create New API Key</h5></div>
    <div class="card-body aps-cp-card-body">
      <form method="post" action="<?= BASE_URL ?>/admin/api-keys/create">
                          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="row g-2">
          <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Key name (e.g. Mobile App)" required></div>
          <div class="col-md-3">
            <select name="scopes[]" class="form-select" multiple size="3">
              <option value="read:leads" selected>read:leads</option>
              <option value="read:properties" selected>read:properties</option>
              <option value="read:bookings" selected>read:bookings</option>
              <option value="write:leads">write:leads</option>
              <option value="write:properties">write:properties</option>
              <option value="admin:*">admin:*</option>
            </select>
            <small class="text-muted">Hold Ctrl to select multiple</small>
          </div>
          <div class="col-md-2"><input type="number" name="rate_limit" class="form-control" value="60" min="1" max="10000" placeholder="Rate limit"></div>
          <div class="col-md-2"><input type="datetime-local" name="expires_at" class="form-control" placeholder="Expires (optional)"></div>
          <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus"></i> Create</button></div>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-white"><h5 class="mb-0">All API Keys (<?= count($keys) ?>)</h5></div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Name</th>
              <th>API Key</th>
              <th>Scopes</th>
              <th>Rate</th>
              <th>Status</th>
              <th>Last Used</th>
              <th>Expires</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($keys)): ?>
              <tr><td colspan="9" class="text-center py-4 text-muted">No API keys yet</td></tr>
            <?php else: foreach ($keys as $k): ?>
              <tr>
                <td><strong><?= htmlspecialchars($k['name']) ?></strong></td>
                <td><code class="small user-select-all"><?= htmlspecialchars($k['api_key']) ?></code></td>
                <td>
                  <?php foreach (array_slice(explode(',', $k['scopes'] ?? ''), 0, 2) as $sc): ?>
                    <span class="badge bg-secondary me-1 small"><?= htmlspecialchars($sc) ?></span>
                  <?php endforeach; ?>
                </td>
                <td><span class="badge bg-info"><?= $k['rate_limit_per_minute'] ?>/min</span></td>
                <td>
                  <span class="badge bg-<?= $k['is_active'] ? 'success' : 'secondary' ?>">
                    <?= $k['is_active'] ? 'Active' : 'Revoked' ?>
                  </span>
                </td>
                <td><small><?= $k['last_used_at'] ? htmlspecialchars($k['last_used_at']) : '<span class="text-muted">Never</span>' ?></small></td>
                <td><small><?= $k['expires_at'] ? htmlspecialchars($k['expires_at']) : '<span class="text-muted">Never</span>' ?></small></td>
                <td><small><?= htmlspecialchars($k['created_at']) ?></small></td>
                <td>
                  <?php if ($k['is_active']): ?>
                    <form method="post" action="<?= BASE_URL ?>/admin/api-keys/revoke/<?= $k['id'] ?>" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                      <button class="btn btn-sm btn-outline-warning" title="Revoke"><i class="fas fa-ban"></i></button>
                    </form>
                  <?php else: ?>
                    <form method="post" action="<?= BASE_URL ?>/admin/api-keys/activate/<?= $k['id'] ?>" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                      <button class="btn btn-sm btn-outline-success" title="Activate"><i class="fas fa-check"></i></button>
                    </form>
                  <?php endif; ?>
                  <form method="post" action="<?= BASE_URL ?>/admin/api-keys/delete/<?= $k['id'] ?>" class="d-inline" onsubmit="return confirm('Permanently delete this API key?')">
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
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/admin/layouts/unified.php';
