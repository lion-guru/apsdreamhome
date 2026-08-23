<?php
$page_title = $page_title ?? 'Leave Types';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-tags me-2"></i>Leave Types</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>Add Type</button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Name</th><th>Code</th><th>Days/Year</th><th>Max Consecutive</th><th>Paid</th><th>Color</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($types ?? [])): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No leave types</td></tr>
                    <?php else: ?>
                        <?php foreach ($types as $t): ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars($t['name'] ?? '') ?></td>
                                <td><code><?= htmlspecialchars($t['code'] ?? '') ?></code></td>
                                <td><?= (int)($t['days_per_year'] ?? 0) ?></td>
                                <td><?= $t['max_consecutive_days'] ?? '-' ?></td>
                                <td><?= ($t['is_paid'] ?? 0) ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' ?></td>
                                <td><span class="badge" class="style-99091">&nbsp;&nbsp;&nbsp;</span></td>
                                <td>
                                    <span class="badge bg-<?= ($t['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>">
                                        <?= htmlspecialchars($t['status'] ?? '') ?>
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

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/hr/leave-types/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Leave Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Code <span class="text-danger">*</span></label><input type="text" name="code" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Days Per Year</label><input type="number" name="days_per_year" class="form-control" value="12"></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                    <div class="mb-3"><label class="form-label">Color</label><input type="color" name="color" class="form-control form-control-color" value="#007bff"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
