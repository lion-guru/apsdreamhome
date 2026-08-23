<?php
$page_title = $page_title ?? 'Performance Reviews';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-trophy me-2"></i>Performance Reviews</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>Add Review</button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Employee</th><th>KPI</th><th>Period</th><th>Target</th><th>Actual</th><th>Achievement</th><th>Score</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($reviews ?? [])): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No reviews yet</td></tr>
                    <?php else: ?>
                        <?php foreach ($reviews as $r): ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars($r['employee_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($r['kpi_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($r['period_start'] ?? '') ?> - <?= htmlspecialchars($r['period_end'] ?? '') ?></td>
                                <td><?= htmlspecialchars($r['target_value'] ?? '') ?></td>
                                <td><?= htmlspecialchars($r['actual_value'] ?? '') ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2 style-22349">
                                            <div class="progress-bar bg-<?= ($r['achievement_percentage'] ?? 0) >= 100 ? 'success' : (($r['achievement_percentage'] ?? 0) >= 80 ? 'warning' : 'danger') ?> style-81199"></div>
                                        </div>
                                        <small><?= htmlspecialchars($r['achievement_percentage'] ?? '0') ?>%</small>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($r['score'] ?? '') ?></td>
                                <td><span class="badge bg-<?= ($r['status'] ?? '') === 'completed' ? 'success' : 'info' ?>"><?= htmlspecialchars($r['status'] ?? '') ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (($total_pages ?? 1) > 1): ?>
        <div class="card-footer bg-white">
            <nav><ul class="pagination pagination-sm mb-0 justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === ($page ?? 1) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                <?php endfor; ?>
            </ul></nav>
        </div>
    <?php endif; ?>
</div>

<!-- Add Review Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/hr/performance/store">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Performance Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select</option>
                            <?php foreach ($users ?? [] as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">KPI <span class="text-danger">*</span></label>
                        <select name="kpi_id" class="form-select" required>
                            <option value="">Select KPI</option>
                            <?php foreach ($kpis_list ?? [] as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><label class="form-label">Period Start</label><input type="date" name="period_start" class="form-control" value="<?= date('Y-m-01') ?>"></div>
                        <div class="col-6"><label class="form-label">Period End</label><input type="date" name="period_end" class="form-control" value="<?= date('Y-m-t') ?>"></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><label class="form-label">Target Value</label><input type="number" name="target_value" class="form-control" step="0.01"></div>
                        <div class="col-6"><label class="form-label">Actual Value</label><input type="number" name="actual_value" class="form-control" step="0.01"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
