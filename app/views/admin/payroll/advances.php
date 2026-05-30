<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-hand-holding-usd me-2"></i>Salary Advances</h1>
        <a href="<?= BASE_URL ?>/admin/payroll" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Payroll</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Amount (₹)</th>
                            <th>Reason</th>
                            <th>Approved By</th>
                            <th>Repay EMI</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($advances ?? [])): ?>
                            <tr><td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-hand-holding-usd fa-3x text-muted mb-3"></i>
                                <h5>No Advances</h5>
                                <p class="mb-3">No salary advance records found.</p>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($advances as $a): ?>
                                <tr>
                                    <td><?= $a['id'] ?? '' ?></td>
                                    <td><strong><?= htmlspecialchars($a['employee_name'] ?? '') ?></strong></td>
                                    <td class="text-warning">₹<?= number_format($a['advance_amount'] ?? 0, 2) ?></td>
                                    <td><?= htmlspecialchars($a['advance_reason'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($a['advance_approved_by'] ?? '') ?></td>
                                    <td>₹<?= number_format($a['advance_repay_emi'] ?? 0, 2) ?></td>
                                    <td><?= htmlspecialchars($a['payment_date'] ?? date('Y-m-d', strtotime($a['created_at'] ?? ''))) ?></td>
                                    <td><span class="badge bg-warning text-dark">Advance</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
