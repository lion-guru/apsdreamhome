<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Manage Features') ?></h1>
        <a href="<?= $base ?? BASE_URL ?>/features/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New Feature</a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Feature Management</h5>
            <div class="d-flex gap-2">
                <select id="bulkAction" class="form-select form-select-sm" class="style-68062">
                    <option value="">Bulk Actions</option>
                    <option value="activate">Activate</option>
                    <option value="deactivate">Deactivate</option>
                    <option value="delete">Delete</option>
                </select>
                <button class="btn btn-sm btn-outline-primary" onclick="doBulkAction()">Apply</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Version</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($features)): ?>
                            <?php foreach ($features as $f): ?>
                                <tr>
                                    <td><input type="checkbox" class="featureCheck" value="<?= (int)($f['id'] ?? 0) ?>"></td>
                                    <td><?= htmlspecialchars($f['name'] ?? '') ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($f['status'] ?? 'inactive') === 'active' ? 'success' : 'secondary' ?>">
                                            <?= htmlspecialchars($f['status'] ?? 'inactive') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($f['version'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($f['created_at'] ?? '-') ?></td>
                                    <td>
                                        <a href="<?= $base ?? BASE_URL ?>/features/edit?id=<?= (int)($f['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteFeature(<?= (int)($f['id'] ?? 0) ?>)"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No features to manage.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.featureCheck').forEach(cb => cb.checked = this.checked);
});
function doBulkAction() {
    const ids = [...document.querySelectorAll('.featureCheck:checked')].map(cb => cb.value);
    const action = document.getElementById('bulkAction').value;
    if (!ids.length || !action) return alert('Select items and an action.');
    if (action === 'delete' && !confirm('Delete selected features?')) return;
    fetch('<?= $base ?? BASE_URL ?>/features/bulk', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ids, action})
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
function deleteFeature(id) {
    if (!confirm('Delete this feature?')) return;
    fetch('<?= $base ?? BASE_URL ?>/features/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id})
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
</script>