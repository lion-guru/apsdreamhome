<?php
$page_title = $page_title ?? 'API Keys';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><i class="fas fa-key me-2"></i>API Keys</h1>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($_SESSION['flash_error'] ?? '') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_SESSION['flash_success'] ?? '') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_success']); ?>
  <?php endif; ?>

  <?php if (!empty($new_key)): ?>
    <div class="alert alert-success">
      <h5><i class="fas fa-check-circle"></i> API Key Created</h5>
      <?php if (!empty($new_key['warning'])): ?>
        <p class="mb-2"><strong><?= htmlspecialchars($new_key['warning'] ?? '') ?></strong></p>
      <?php endif; ?>
      <div class="bg-light p-2 rounded mb-2">
        <div><strong>Name:</strong> <?= htmlspecialchars($new_key['key_name'] ?? '') ?></div>
        <div><strong>Key:</strong> <code class="user-select-all"><?= htmlspecialchars($new_key['key_value'] ?? '') ?></code></div>
        <div><strong>Type:</strong> <?= htmlspecialchars($new_key['key_type'] ?? '') ?></div>
        <?php if (!empty($new_key['service_name'])): ?>
          <div><strong>Service:</strong> <?= htmlspecialchars($new_key['service_name'] ?? '') ?></div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="row mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-primary text-white">
      <div class="card-body"><small class="opacity-75">Total Keys</small><h3 class="mb-0"><?= $stats['total'] ?? 0 ?></h3></div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-success text-white">
      <div class="card-body"><small class="opacity-75">Active</small><h3 class="mb-0"><?= $stats['active'] ?? 0 ?></h3></div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-warning text-white">
      <div class="card-body"><small class="opacity-75">Revoked</small><h3 class="mb-0"><?= $stats['revoked'] ?? 0 ?></h3></div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-info text-white">
      <div class="card-body"><small class="opacity-75">Used Today</small><h3 class="mb-0"><?= $stats['used_today'] ?? 0 ?></h3></div>
    </div></div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h5 class="mb-0">Create New API Key</h5></div>
    <div class="card-body">
      <form method="post" action="<?= $BASE_URL ?>/admin/api-keys/create">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="row g-2">
          <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Key name (e.g. Mobile App)" required></div>
          <div class="col-md-2"><input type="text" name="service_name" class="form-control" placeholder="Service (e.g. Gemini)"></div>
          <div class="col-md-2">
            <select name="key_type" class="form-select">
              <option value="api_key">API Key</option>
              <option value="token">Token</option>
              <option value="password">Password</option>
              <option value="certificate">Certificate</option>
            </select>
          </div>
          <div class="col-md-3"><input type="text" name="description" class="form-control" placeholder="Description (optional)"></div>
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
              <th>Key</th>
              <th>Type</th>
              <th>Service</th>
              <th>Status</th>
              <th>Used</th>
              <th>Last Used</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($keys)): ?>
              <tr><td colspan="9" class="text-center py-4 text-muted">No API keys yet</td></tr>
            <?php else: foreach ($keys as $k): ?>
              <tr>
                <td><strong><?= htmlspecialchars($k['key_name'] ?? '') ?></strong></td>
                <td><code class="small user-select-all"><?= htmlspecialchars($k['key_value_masked'] ?? '****') ?></code></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($k['key_type'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($k['service_name'] ?? '-') ?></td>
                <td>
                  <span class="badge bg-<?= $k['is_active'] ? 'success' : 'secondary' ?>">
                    <?= $k['is_active'] ? 'Active' : 'Revoked' ?>
                  </span>
                </td>
                <td><span class="badge bg-light text-dark"><?= $k['usage_count'] ?? 0 ?></span></td>
                <td><small><?= !empty($k['last_used_at']) ? htmlspecialchars($k['last_used_at'] ?? '') : '<span class="text-muted">Never</span>' ?></small></td>
                <td><small><?= htmlspecialchars($k['created_at'] ?? '') ?></small></td>
                <td>
                  <?php if ($k['is_active']): ?>
                    <form method="post" action="<?= $BASE_URL ?>/admin/api-keys/revoke/<?= $k['id'] ?>" class="d-inline">
                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                      <button class="btn btn-sm btn-outline-warning" title="Revoke key" aria-label="Revoke key"><i class="fas fa-ban"></i></button>
                    </form>
                  <?php else: ?>
                    <form method="post" action="<?= $BASE_URL ?>/admin/api-keys/activate/<?= $k['id'] ?>" class="d-inline">
                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                      <button class="btn btn-sm btn-outline-success" title="Activate" aria-label="Confirm"><i class="fas fa-check"></i></button>
                    </form>
                  <?php endif; ?>
                  <form method="post" action="<?= $BASE_URL ?>/admin/api-keys/delete/<?= $k['id'] ?>" class="d-inline" onsubmit="return confirm('Permanently delete this API key?')">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <button class="btn btn-sm btn-outline-danger" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button>
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
require_once APP_PATH . '/views/layouts/admin.php';
