<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-chart-bar me-2"></i>Salary Report</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/salary/report?month=<?= $filter_month ?? '' ?>&year=<?= $filter_year ?? '' ?>&employee_id=<?= $filter_employee ?? 0 ?>&export=csv" class="btn btn-outline-success"><i class="fas fa-file-csv me-1"></i>Export CSV</a>
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="get" class="row g-2 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-auto">
                    <select name="month" class="form-select">
                        <option value="">All Months</option>
                        <?php for ($m=1;$m<=12;$m++): ?>
                        <option value="<?= $m ?>" <?= ($filter_month ?? 0) == $m ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="year" class="form-select">
                        <option value="">All Years</option>
                        <?php for ($y=date('Y')-2;$y<=date('Y')+1;$y++): ?>
                        <option value="<?= $y ?>" <?= ($filter_year ?? 0) == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
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
                <div class="col-auto"><button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Generate Report</button></div>
            </form>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-success shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Gross</h6>
                    <h3 class="text-success">₹<?= number_format($total_gross ?? 0, 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-danger shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Deductions</h6>
                    <h3 class="text-danger">₹<?= number_format($total_ded ?? 0, 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-primary shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Net Pay</h6>
                    <h3 class="text-primary">₹<?= number_format($total_net ?? 0, 2) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark"><tr>
                        <th>#</th><th>Employee</th><th>Email</th><th>Gross</th><th>Deductions</th><th>Net</th><th>Date</th><th>Method</th><th>Status</th>
                    </tr></thead>
                    <tbody>
                        <?php if (empty($payments ?? [])): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No payments match the selected filters</td></tr>
                        <?php else: ?>
                            <?php foreach ($payments as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><strong><?= htmlspecialchars($p['employee_name'] ?? '') ?></strong></td>
                                <td><?= htmlspecialchars($p['employee_email'] ?? '') ?></td>
                                <td>₹<?= number_format($p['gross_salary'] ?? 0, 2) ?></td>
                                <td class="text-danger">₹<?= number_format($p['total_deductions'] ?? 0, 2) ?></td>
                                <td><strong>₹<?= number_format($p['net_salary'] ?? 0, 2) ?></strong></td>
                                <td><?= htmlspecialchars($p['payment_date'] ?? '') ?></td>
                                <td><?= ucfirst(str_replace('_',' ', $p['payment_method'] ?? 'bank_transfer')) ?></td>
                                <td><span class="badge bg-<?= match($p['status']??'pending') { 'paid'=>'success', 'pending'=>'warning', 'cancelled'=>'danger', default=>'secondary' } ?>"><?= ucfirst($p['status'] ?? 'pending') ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
