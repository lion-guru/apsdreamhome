<?php
$page_title = $page_title ?? 'KPI Definitions';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i>KPI Definitions</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>Add KPI</button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Name</th><th>Category</th><th>Unit</th><th>Default Target</th><th>Weightage</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($kpis ?? [])): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No KPI definitions</td></tr>
                    <?php else: ?>
                        <?php foreach ($kpis as $k): ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars($k['name'] ?? '') ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($k['category'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($k['unit'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($k['default_target'] ?? '') ?></td>
                                <td><?= htmlspecialchars($k['weightage'] ?? '1.00') ?></td>
                                <td>
                                    <span class="badge bg-<?= ($k['is_active'] ?? 0) ? 'success' : 'secondary' ?>">
                                        <?= ($k['is_active'] ?? 0) ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add KPI Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/hr/kpis/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add KPI</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="productivity">Productivity</option>
                            <option value="sales">Sales</option>
                            <option value="customer_satisfaction">Customer Satisfaction</option>
                            <option value="quality">Quality</option>
                            <option value="financial">Financial</option>
                            <option value="operational">Operational</option>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Unit</label><input type="text" name="unit" class="form-control" placeholder="e.g., %, count, ₹"></div>
                    <div class="mb-3"><label class="form-label">Default Target</label><input type="number" name="default_target" class="form-control" step="0.01"></div>
                    <div class="mb-3"><label class="form-label">Weightage</label><input type="number" name="weightage" class="form-control" step="0.01" value="1.00"></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
