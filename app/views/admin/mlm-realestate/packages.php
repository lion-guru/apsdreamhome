<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-box me-2"></i>Networker Packages</h1>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#packageModal"><i class="fas fa-plus me-1"></i>Add Package</button>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Price</th><th>Direct Reward</th><th>Level Reward</th><th>Daily Cap</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if (empty($packages ?? [])): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-box fa-3x text-muted mb-3 style-82835"></i>
                            <h5 class="text-muted">No packages found</h5>
                            <p class="text-muted mb-3">Create networker packages to define rewards and commission structures.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($packages as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><strong><?= htmlspecialchars($p['name'] ?? '') ?></strong></td>
                        <td>₹<?= number_format((float)$p['price'], 2) ?></td>
                        <td>₹<?= number_format((float)$p['direct_reward'], 2) ?></td>
                        <td>₹<?= number_format((float)$p['level_reward'], 2) ?></td>
                        <td>₹<?= number_format((float)$p['daily_capping'], 2) ?></td>
                        <td><span class="badge bg-<?= $p['is_active'] ? 'success' : 'secondary' ?>"><?= $p['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                        <td><button class="btn btn-sm btn-outline-primary" onclick="editPackage(<?= htmlspecialchars(json_encode($p)) ?>)"><i class="fas fa-edit"></i></button></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<div class="modal fade" id="packageModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="<?= BASE_URL ?>/admin/mlm-realestate/packages/save">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="modal-header"><h5 class="modal-title" id="modalTitle">Add Package</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="id" id="pkgId" value="0">
            <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" id="pkgName" class="form-control" required></div>
            <div class="row"><div class="col-md-6 mb-3"><label class="form-label">Price (₹)</label><input type="number" step="0.01" name="price" id="pkgPrice" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Direct Reward (₹)</label><input type="number" step="0.01" name="direct_reward" id="pkgDirect" class="form-control"></div></div>
            <div class="row"><div class="col-md-6 mb-3"><label class="form-label">Level Reward (₹)</label><input type="number" step="0.01" name="level_reward" id="pkgLevel" class="form-control"></div>
            <div class="col-md-6 mb-3"><label class="form-label">Daily Capping (₹)</label><input type="number" step="0.01" name="daily_capping" id="pkgCap" class="form-control"></div></div>
            <div class="mb-3"><label class="form-label">Description</label><textarea name="description" id="pkgDesc" class="form-control" rows="2"></textarea></div>
            <div class="form-check"><input type="checkbox" name="is_active" class="form-check-input" id="pkgActive" value="1" checked><label class="form-check-label">Active</label></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

<script>
function editPackage(p) {
    document.getElementById('modalTitle').textContent = 'Edit Package';
    document.getElementById('pkgId').value = p.id;
    document.getElementById('pkgName').value = p.name;
    document.getElementById('pkgPrice').value = p.price;
    document.getElementById('pkgDirect').value = p.direct_reward;
    document.getElementById('pkgLevel').value = p.level_reward;
    document.getElementById('pkgCap').value = p.daily_capping;
    document.getElementById('pkgDesc').value = p.description || '';
    document.getElementById('pkgActive').checked = p.is_active == 1;
    new bootstrap.Modal(document.getElementById('packageModal')).show();
}
</script>