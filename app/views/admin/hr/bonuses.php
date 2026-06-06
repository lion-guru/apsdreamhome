<?php
$page_title = $page_title ?? 'Employee Bonuses';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-gift me-2"></i>Employee Bonuses</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>Add Bonus</button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Employee</th><th>Bonus #</th><th>Type</th><th>Amount</th><th>Month/Year</th><th>Reason</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($bonuses ?? [])): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No bonuses recorded</td></tr>
                    <?php else: ?>
                        <?php foreach ($bonuses as $b): ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars($b['employee_name'] ?? '') ?></td>
                                <td><code><?= htmlspecialchars($b['bonus_number'] ?? '') ?></code></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($b['bonus_type'] ?? '') ?></span></td>
                                <td class="fw-bold text-success">₹<?= number_format($b['bonus_amount'] ?? 0, 2) ?></td>
                                <td><?= htmlspecialchars($b['bonus_month'] ?? '') ?>/<?= htmlspecialchars($b['bonus_year'] ?? '') ?></td>
                                <td><span title="<?= htmlspecialchars($b['reason'] ?? '') ?>"><?= mb_strimwidth(htmlspecialchars($b['reason'] ?? ''), 0, 25, '...') ?></span></td>
                                <td>
                                    <span class="badge bg-<?= ($b['payment_status'] ?? '') === 'paid' ? 'success' : (($b['payment_status'] ?? '') === 'cancelled' ? 'danger' : 'warning') ?>">
                                        <?= htmlspecialchars($b['payment_status'] ?? '') ?>
                                    </span>
                                </td>
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

<!-- Add Bonus Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/hr/bonuses/store">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Bonus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select</option>
                            <?php foreach ($users ?? [] as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bonus Type</label>
                        <select name="bonus_type" class="form-select">
                            <option value="performance">Performance</option>
                            <option value="attendance">Attendance</option>
                            <option value="target_achievement">Target Achievement</option>
                            <option value="festival">Festival</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Amount (₹) <span class="text-danger">*</span></label><input type="number" name="bonus_amount" class="form-control" step="0.01" required></div>
                    <div class="row mb-3">
                        <div class="col-6"><label class="form-label">Month</label>
                            <select name="bonus_month" class="form-select">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= $m == date('m') ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-6"><label class="form-label">Year</label><input type="number" name="bonus_year" class="form-control" value="<?= date('Y') ?>"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Reason</label><textarea name="reason" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
