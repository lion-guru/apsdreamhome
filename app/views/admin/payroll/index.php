<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-money-check me-2"></i>Payroll Management</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/payroll/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New Payroll</a>
            <a href="<?= BASE_URL ?>/admin/payroll/advances" class="btn btn-outline-warning"><i class="fas fa-hand-holding-usd me-1"></i>Advances</a>
        </div>
    </div>
    <?php $batch_month = $batch_month ?? date('Y-m'); $recent_payslips = $recent_payslips ?? []; ?>
    <div class="card shadow-sm mb-4 border-primary">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span><i class="fas fa-bolt me-2"></i>Monthly Payroll Batch (attendance + approved leaves pro-rata, PF/ESI/TDS/PT)</span>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/payroll/generate-batch" class="row g-3 align-items-end" data-aps-confirm="Run monthly payroll batch? Existing payslips for the month will be refreshed, not duplicated.">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <div class="col-md-4">
                    <label class="form-label">Month</label>
                    <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($batch_month) ?>" required>
                </div>
                <div class="col-md-8">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-bolt me-1"></i>Run Monthly Payroll Batch</button>
                    <small class="text-muted ms-2">Counts present days + approved leaves, deducts PF (12%), ESI (0.75%), TDS &amp; PT. Paid payslips are never overwritten.</small>
                </div>
            </form>
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white"><i class="fas fa-file-invoice me-2"></i>Recent Payslips</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr><th>#</th><th>Employee</th><th>Period</th><th>Present/LOP</th><th>Net Pay</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_payslips)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No payslips yet — run the monthly batch above.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recent_payslips as $s): ?>
                                <tr>
                                    <td><?= $s['id'] ?? '' ?></td>
                                    <td><strong><?= htmlspecialchars($s['employee_name'] ?? '') ?></strong></td>
                                    <td><?= str_pad((string)($s['period_month'] ?? ''), 2, '0', STR_PAD_LEFT) ?>/<?= htmlspecialchars($s['period_year'] ?? '') ?></td>
                                    <td><?= (int)($s['days_present'] ?? 0) ?>/<?= (int)($s['lop_days'] ?? 0) ?></td>
                                    <td><strong>₹<?= number_format($s['net_salary'] ?? 0, 2) ?></strong></td>
                                    <td><span class="badge bg-<?= ($s['status'] ?? '') === 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($s['status'] ?? 'draft') ?></span></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/payroll/payslip/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary" title="View/Print Payslip"><i class="fas fa-eye"></i></a>
                                        <a href="<?= BASE_URL ?>/admin/payroll/payslip/<?= $s['id'] ?>/pdf" class="btn btn-sm btn-outline-success" title="Download PDF"><i class="fas fa-file-pdf"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Basic</th>
                            <th>HRA</th>
                            <th>Allowance</th>
                            <th>Deduction</th>
                            <th>Net Salary</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payrolls ?? [])): ?>
                            <tr><td colspan="10" class="text-center text-muted py-5">
                                <i class="fas fa-money-check fa-3x text-muted mb-3"></i>
                                <h5>No Payroll Records</h5>
                                <p class="mb-3">Create your first payroll record.</p>
                                <a href="<?= BASE_URL ?>/admin/payroll/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New Payroll</a>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($payrolls as $p): ?>
                                <tr>
                                    <td><?= $p['id'] ?? '' ?></td>
                                    <td><strong><?= htmlspecialchars($p['employee_name'] ?? '') ?></strong></td>
                                    <td>₹<?= number_format($p['basic_salary'] ?? 0, 2) ?></td>
                                    <td>₹<?= number_format($p['hra'] ?? 0, 2) ?></td>
                                    <td>₹<?= number_format($p['allowance'] ?? 0, 2) ?></td>
                                    <td class="text-danger">₹<?= number_format($p['deduction'] ?? 0, 2) ?></td>
                                    <td><strong>₹<?= number_format($p['net_salary'] ?? 0, 2) ?></strong></td>
                                    <td><?= htmlspecialchars($p['payment_date'] ?? '') ?></td>
                                    <td>
                                        <span class="badge bg-<?= match($p['payment_status'] ?? 'pending') { 'paid' => 'success', 'advance' => 'warning', 'pending' => 'secondary', default => 'secondary' } ?>">
                                            <?= ucfirst($p['payment_status'] ?? 'pending') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/payroll/edit/<?= $p['id'] ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
