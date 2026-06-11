<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-link me-2"></i>Payroll Integration</h1>
        <a href="<?= BASE_URL ?>/admin/payroll" class="btn btn-outline-secondary"><i class="fas fa-external-link-alt me-1"></i>Go to Payroll</a>
    </div>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Overview</h5></div>
                <div class="card-body aps-cp-card-body">
                    <p>This system has <strong>two salary modules</strong> working together:</p>
                    <table class="table table-bordered">
                        <thead class="table-light"><tr><th>Module</th><th>Table</th><th>Records</th><th>Total Paid</th></tr></thead>
                        <tbody>
                            <tr>
                                <td><strong>Salary Module</strong> (New)</td>
                                <td><code>salary_payments</code></td>
                                <td><?= (int)($GLOBALS['salary_count'] ?? 0) ?></td>
                                <td>₹0</td>
                            </tr>
                            <tr>
                                <td><strong>Payroll Module</strong> (Legacy)</td>
                                <td><code>employee_payroll</code></td>
                                <td><?= (int)($payroll_count ?? 0) ?></td>
                                <td>₹<?= number_format($payroll_total ?? 0, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Quick Links</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>/admin/salary" class="btn btn-primary"><i class="fas fa-coins me-1"></i>Salary Dashboard</a>
                        <a href="<?= BASE_URL ?>/admin/salary/payments" class="btn btn-outline-success"><i class="fas fa-money-bill-wave me-1"></i>Salary Payments</a>
                        <a href="<?= BASE_URL ?>/admin/payroll" class="btn btn-outline-info"><i class="fas fa-money-check me-1"></i>Legacy Payroll</a>
                        <a href="<?= BASE_URL ?>/admin/salary/report" class="btn btn-outline-warning"><i class="fas fa-chart-pie me-1"></i>Salary Reports</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-table me-2"></i>Available Sections</h5></div>
        <div class="card-body aps-cp-card-body">
            <div class="row g-2">
                <div class="col-md-3"><a href="<?= BASE_URL ?>/admin/salary/structures" class="btn btn-outline-primary w-100 mb-2"><i class="fas fa-layer-group me-1"></i>Structures</a></div>
                <div class="col-md-3"><a href="<?= BASE_URL ?>/admin/salary/payments/create" class="btn btn-outline-primary w-100 mb-2"><i class="fas fa-plus me-1"></i>Create Payment</a></div>
                <div class="col-md-3"><a href="<?= BASE_URL ?>/admin/salary/contracts" class="btn btn-outline-primary w-100 mb-2"><i class="fas fa-file-signature me-1"></i>Contracts</a></div>
                <div class="col-md-3"><a href="<?= BASE_URL ?>/admin/salary/plans" class="btn btn-outline-primary w-100 mb-2"><i class="fas fa-clipboard-list me-1"></i>Plans</a></div>
                <div class="col-md-3"><a href="<?= BASE_URL ?>/admin/salary/records" class="btn btn-outline-primary w-100 mb-2"><i class="fas fa-database me-1"></i>Records</a></div>
                <div class="col-md-3"><a href="<?= BASE_URL ?>/admin/salary/tracker" class="btn btn-outline-primary w-100 mb-2"><i class="fas fa-tachometer-alt me-1"></i>Tracker</a></div>
                <div class="col-md-3"><a href="<?= BASE_URL ?>/admin/salary/history" class="btn btn-outline-primary w-100 mb-2"><i class="fas fa-history me-1"></i>History</a></div>
                <div class="col-md-3"><a href="<?= BASE_URL ?>/admin/salary/payouts" class="btn btn-outline-primary w-100 mb-2"><i class="fas fa-hand-holding-usd me-1"></i>Payouts</a></div>
            </div>
        </div>
    </div>
</div>
