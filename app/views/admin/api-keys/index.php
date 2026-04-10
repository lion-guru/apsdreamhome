<?php
$extraHead = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';
$extraHead .= '<style>
.api-key-card { transition: all 0.3s; }
.api-key-card:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
.key-active { border-left: 4px solid #28a745; }
.key-inactive { border-left: 4px solid #dc3545; opacity: 0.7; }
.key-value { font-family: monospace; background: #f8f9fa; padding: 5px 10px; border-radius: 4px; }
.test-btn { cursor: pointer; }
.status-badge { font-size: 0.75rem; padding: 4px 8px; }
</style>';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-key me-2"></i>API Keys Management</h2>
        <a href="<?= BASE_URL ?>/admin/api-keys/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Key
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-key fa-2x opacity-50"></i></div>
                        <div class="text-end">
                            <h3 class="mb-0"><?= count($api_keys) ?></h3>
                            <small>Total Keys</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-check-circle fa-2x opacity-50"></i></div>
                        <div class="text-end">
                            <h3 class="mb-0"><?= $active_count ?></h3>
                            <small>Active</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-times-circle fa-2x opacity-50"></i></div>
                        <div class="text-end">
                            <h3 class="mb-0"><?= $inactive_count ?></h3>
                            <small>Inactive</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-robot fa-2x opacity-50"></i></div>
                        <div class="text-end">
                            <h3 class="mb-0"><?= $active_count ?></h3>
                            <small>AI Keys</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>All API Keys</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Service</th>
                            <th>Key Name</th>
                            <th>Key Value (Masked)</th>
                            <th>Status</th>
                            <th>Usage</th>
                            <th>Last Used</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($api_keys as $key): ?>
                            <tr class="<?= $key['is_active'] ? 'key-active' : 'key-inactive' ?>">
                                <td>
                                    <strong><?= htmlspecialchars(key['service_name'] ?? '') ?></strong>
                                </td>
                                <td>
                                    <code><?= htmlspecialchars(key['key_name'] ?? '') ?></code>
                                </td>
                                <td>
                                    <span class="key-value">
                                        <?= substr(htmlspecialchars(key['key_value'] ?? ''), 0, 20) . '...' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($key['is_active']): ?>
                                        <span class="badge bg-success status-badge">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary status-badge">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $key['usage_count'] ?? 0 ?> times</td>
                                <td>
                                    <?= $key['last_used_at'] ? date('d M Y H:i', strtotime($key['last_used_at'])) : '<span class="text-muted">Never</span>' ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/admin/api-keys/edit/<?= $key['id'] ?>" class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/api-keys/toggle/<?= $key['id'] ?>" class="btn btn-outline-<?= $key['is_active'] ? 'warning' : 'success' ?>" title="<?= $key['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                            <i class="fas fa-<?= $key['is_active'] ? 'ban' : 'check' ?>"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-info test-btn" onclick="testKey(<?= $key['id'] ?>)" title="Test">
                                            <i class="fas fa-plug"></i>
                                        </button>
                                        <a href="<?= BASE_URL ?>/admin/api-keys/delete/<?= $key['id'] ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="testModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Testing API Key</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="testResult">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2">Testing...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function testKey(id) {
    $('#testModal').modal('show');
    $('#testResult').html('<div class="spinner-border text-primary" role="status"></div><p class="mt-2">Testing...</p>');
    
    fetch('<?= BASE_URL ?>/admin/api-keys/test/' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                $('#testResult').html('<i class="fas fa-check-circle text-success fa-3x"></i><h4 class="mt-3 text-success">Valid!</h4><p>' + data.message + '</p>');
            } else {
                $('#testResult').html('<i class="fas fa-times-circle text-danger fa-3x"></i><h4 class="mt-3 text-danger">Invalid!</h4><p>' + data.message + '</p>');
            }
        })
        .catch(err => {
            $('#testResult').html('<i class="fas fa-exclamation-triangle text-warning fa-3x"></i><h4 class="mt-3 text-warning">Error</h4><p>' + err + '</p>');
        });
}
</script>
