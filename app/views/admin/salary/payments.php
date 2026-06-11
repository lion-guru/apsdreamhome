<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-money-bill-wave me-2"></i>Salary Payments</h1>
        <div>
            <button class="btn btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#bulkModal"><i class="fas fa-tasks me-1"></i>Bulk Process</button>
            <a href="<?= BASE_URL ?>/admin/salary/payments/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New Payment</a>
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-auto">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="paid" <?= ($filter_status ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="pending" <?= ($filter_status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="cancelled" <?= ($filter_status ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="employee_id" class="form-select">
                        <option value="">All users</option>
                        <?php foreach ($users ?? [] as $e): ?>
                        <option value="<?= $e['id'] ?>" <?= ($filter_employee ?? 0) == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto"><button type="submit" class="btn btn-outline-primary"><i class="fas fa-filter me-1"></i>Filter</button></div>
            </form>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark"><tr>
                        <th>#</th><th>Employee</th><th>Gross</th><th>Deductions</th><th>Net</th><th>Date</th><th>Method</th><th>Transaction</th><th>Status</th><th>Actions</th>
                    </tr></thead>
                    <tbody>
                        <?php if (empty($payments ?? [])): ?>
                            <tr><td colspan="10" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No payments found</td></tr>
                        <?php else: ?>
                            <?php foreach ($payments as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><strong><?= htmlspecialchars($p['employee_name'] ?? '') ?></strong></td>
                                <td>₹<?= number_format($p['gross_salary'] ?? 0, 2) ?></td>
                                <td class="text-danger">₹<?= number_format($p['total_deductions'] ?? 0, 2) ?></td>
                                <td><strong>₹<?= number_format($p['net_salary'] ?? 0, 2) ?></strong></td>
                                <td><?= htmlspecialchars($p['payment_date'] ?? '') ?></td>
                                <td><?= ucfirst(str_replace('_',' ', $p['payment_method'] ?? 'bank_transfer')) ?></td>
                                <td><?= htmlspecialchars($p['transaction_id'] ?? '-') ?></td>
                                <td><span class="badge bg-<?= match($p['status']??'pending') { 'paid'=>'success', 'pending'=>'warning', 'cancelled'=>'danger', default=>'secondary' } ?>"><?= ucfirst($p['status'] ?? 'pending') ?></span></td>
                                <td><a href="<?= BASE_URL ?>/admin/salary/payments/view/<?= $p['id'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="bulkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= BASE_URL ?>/admin/salary/payments/bulk">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header bg-secondary text-white"><h5 class="modal-title"><i class="fas fa-tasks me-1"></i>Bulk Process Payments</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p>Generate pending payments for all users with active salary structures.</p>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Month</label>
                            <select name="month" class="form-select" required>
                                <?php for ($m=1;$m<=12;$m++): ?>
                                <option value="<?= $m ?>" <?= $m==date('n')?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3"><label class="form-label">Year</label>
                            <select name="year" class="form-select" required>
                                <?php for ($y=date('Y')-2;$y<=date('Y')+1;$y++): ?>
                                <option value="<?= $y ?>" <?= $y==date('Y')?'selected':'' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary"><i class="fas fa-play me-1"></i>Process</button>
                </div>
            </form>
        </div>
    </div>
</div>
