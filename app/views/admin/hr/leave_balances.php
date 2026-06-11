<?php
$page_title = $page_title ?? 'Leave Balances';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-balance-scale me-2"></i>Leave Balances</h4>
    <a href="<?= BASE_URL ?>/admin/hr/leaves" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body aps-cp-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Year</label>
                <select name="year" class="form-select" onchange="this.form.submit()">
                    <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                        <option value="<?= $y ?>" <?= ($year ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Employee</th><th>Leave Type</th><th>Allocated</th><th>Used</th><th>Remaining</th><th>Carried Forward</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($balances ?? [])): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No balances for <?= $year ?? date('Y') ?></td></tr>
                    <?php else: ?>
                        <?php foreach ($balances as $b): ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars($b['employee_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($b['leave_type_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($b['allocated_days'] ?? '0') ?></td>
                                <td>
                                    <span class="badge bg-<?= ($b['used_days'] ?? 0) > 0 ? 'warning' : 'secondary' ?>"><?= htmlspecialchars($b['used_days'] ?? '0') ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= ($b['remaining_days'] ?? 0) > 0 ? 'success' : 'danger' ?>"><?= htmlspecialchars($b['remaining_days'] ?? '0') ?></span>
                                </td>
                                <td><?= htmlspecialchars($b['carried_forward'] ?? '0') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
