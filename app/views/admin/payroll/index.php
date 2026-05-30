<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-money-check me-2"></i>Payroll Management</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/payroll/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New Payroll</a>
            <a href="<?= BASE_URL ?>/admin/payroll/advances" class="btn btn-outline-warning"><i class="fas fa-hand-holding-usd me-1"></i>Advances</a>
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
