<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-clipboard-list me-2"></i>Salary Plans</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fas fa-plus me-1"></i>New Plan</button>
    </div>
    <div class="row g-3">
        <?php if (empty($plans ?? [])): ?>
        <div class="col-12"><div class="alert alert-info">No salary plans defined yet.</div></div>
        <?php else: ?>
            <?php foreach ($plans as $p): ?>
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body aps-cp-card-body">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title"><?= htmlspecialchars($p['name'] ?? '') ?></h5>
                            <span class="badge bg-<?= ($p['is_active'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($p['is_active'] ?? 0) ? 'Active' : 'Inactive' ?></span>
                        </div>
                        <p class="text-muted"><?= htmlspecialchars($p['description'] ?? '') ?></p>
                        <table class="table table-sm mb-0">
                            <tr><td>Base Salary</td><td class="text-end"><strong>₹<?= number_format($p['base_salary'] ?? 0, 2) ?></strong></td></tr>
                            <tr><td>Bonus %</td><td class="text-end"><?= (float)($p['bonus_percent'] ?? 0) ?>%</td></tr>
                            <tr><td>Commission %</td><td class="text-end"><?= (float)($p['commission_percent'] ?? 0) ?>%</td></tr>
                        </table>
                        <button class="btn btn-sm btn-outline-warning mt-3" data-bs-toggle="modal" data-bs-target="#editModal<?= $p['id'] ?>"><i class="fas fa-edit me-1"></i>Edit</button>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="editModal<?= $p['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" action="<?= BASE_URL ?>/admin/salary/plans/update/<?= $p['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="modal-header bg-warning"><h5 class="modal-title"><i class="fas fa-edit me-1"></i>Edit Plan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                                <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($p['name'] ?? '') ?>" required></div>
                                <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control"><?= htmlspecialchars($p['description'] ?? '') ?></textarea></div>
                                <div class="row">
                                    <div class="col-md-4 mb-3"><label class="form-label">Base Salary</label><input type="number" step="0.01" name="base_salary" class="form-control" value="<?= $p['base_salary'] ?? 0 ?>"></div>
                                    <div class="col-md-4 mb-3"><label class="form-label">Bonus %</label><input type="number" step="0.01" name="bonus_percent" class="form-control" value="<?= $p['bonus_percent'] ?? 0 ?>"></div>
                                    <div class="col-md-4 mb-3"><label class="form-label">Commission %</label><input type="number" step="0.01" name="commission_percent" class="form-control" value="<?= $p['commission_percent'] ?? 0 ?>"></div>
                                </div>
                                <div class="mb-3"><label class="form-label">Benefits (JSON)</label><textarea name="benefits_json" class="form-control" rows="3"><?= htmlspecialchars($p['benefits_json'] ?? '') ?></textarea></div>
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" value="1" <?= ($p['is_active'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label">Active</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i>Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= BASE_URL ?>/admin/salary/plans/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fas fa-plus me-1"></i>New Salary Plan</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control"></textarea></div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">Base Salary</label><input type="number" step="0.01" name="base_salary" class="form-control" value="0"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Bonus %</label><input type="number" step="0.01" name="bonus_percent" class="form-control" value="0"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Commission %</label><input type="number" step="0.01" name="commission_percent" class="form-control" value="0"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Benefits (JSON)</label><textarea name="benefits_json" class="form-control" rows="3" placeholder='{"health_insurance":true,"meal_coupon":1500}'></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
