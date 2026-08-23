<?php
$page_title = $page_title ?? 'Shift Types';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-clock me-2"></i>Shift Types</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>Add Shift</button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Name</th><th>Code</th><th>Start</th><th>End</th><th>Duration</th><th>Break</th><th>Color</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($shifts ?? [])): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No shift types</td></tr>
                    <?php else: ?>
                        <?php foreach ($shifts as $s): ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars($s['name'] ?? '') ?></td>
                                <td><code><?= htmlspecialchars($s['code'] ?? '') ?></code></td>
                                <td><?= htmlspecialchars($s['start_time'] ?? '') ?></td>
                                <td><?= htmlspecialchars($s['end_time'] ?? '') ?></td>
                                <td><?= htmlspecialchars($s['duration_hours'] ?? '') ?>h</td>
                                <td><?= htmlspecialchars($s['break_duration'] ?? '60') ?> min</td>
                                <td><span class="badge style-8989">&nbsp;&nbsp;&nbsp;</span></td>
                                <td>
                                    <span class="badge bg-<?= ($s['is_active'] ?? 0) ? 'success' : 'secondary' ?>">
                                        <?= ($s['is_active'] ?? 0) ? 'Active' : 'Inactive' ?>
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

<!-- Add Shift Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/hr/shifts/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Shift Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
                    <div class="row mb-3">
                        <div class="col-6"><label class="form-label">Start Time <span class="text-danger">*</span></label><input type="time" name="start_time" class="form-control" required></div>
                        <div class="col-6"><label class="form-label">End Time <span class="text-danger">*</span></label><input type="time" name="end_time" class="form-control" required></div>
                    </div>
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
