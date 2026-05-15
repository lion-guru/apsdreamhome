<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Audit Trail') ?></h1>
        <div>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Export</button>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <select class="form-select form-select-sm" id="actionFilter" onchange="applyFilters()">
                        <option value="">All Actions</option>
                        <option value="create">Create</option>
                        <option value="update">Update</option>
                        <option value="delete">Delete</option>
                        <option value="login">Login</option>
                        <option value="logout">Logout</option>
                    </select>
                </div>
                <div class="col-auto">
                    <input type="text" class="form-control form-control-sm" id="userFilter" placeholder="User..." onkeyup="applyFilters()">
                </div>
                <div class="col text-end">
                    <span class="text-muted small"><?= count($auditLogs ?? []) ?> entries</span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Timestamp</th><th>User</th><th>Action</th><th>Entity</th><th>Entity ID</th><th>Description</th><th>IP</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($auditLogs)): ?>
                            <?php foreach ($auditLogs as $log): ?>
                                <tr class="audit-row" data-action="<?= htmlspecialchars($log['action'] ?? '') ?>" data-user="<?= htmlspecialchars($log['user'] ?? '') ?>">
                                    <td class="text-nowrap"><?= htmlspecialchars($log['created_at'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($log['user'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($log['action'] ?? '') === 'create' ? 'success' : (($log['action'] ?? '') === 'update' ? 'info' : (($log['action'] ?? '') === 'delete' ? 'danger' : 'secondary')) ?>">
                                            <?= htmlspecialchars($log['action'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($log['entity'] ?? '-') ?></td>
                                    <td><?= (int)($log['entity_id'] ?? 0) ?></td>
                                    <td><?= htmlspecialchars($log['description'] ?? '') ?></td>
                                    <td><code><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-3">No audit trail entries found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
function applyFilters() {
    const action = document.getElementById('actionFilter').value.toLowerCase();
    const user = document.getElementById('userFilter').value.toLowerCase();
    document.querySelectorAll('.audit-row').forEach(row => {
        const matchesAction = !action || row.dataset.action.toLowerCase() === action;
        const matchesUser = !user || row.dataset.user.toLowerCase().includes(user);
        row.style.display = matchesAction && matchesUser ? '' : 'none';
    });
}
</script>
