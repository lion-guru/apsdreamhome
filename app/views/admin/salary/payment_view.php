<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-receipt me-2"></i>Payment Details</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/salary/payments" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
            <button class="btn btn-outline-primary" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Payment Information</h5>
                    <span class="badge bg-<?= match($payment['status']??'pending') { 'paid'=>'success', 'pending'=>'warning', 'cancelled'=>'danger', default=>'secondary' } ?> fs-6"><?= ucfirst($payment['status'] ?? 'pending') ?></span>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Employee:</strong> <?= htmlspecialchars($payment['employee_name'] ?? '') ?></div>
                        <div class="col-md-6"><strong>Email:</strong> <?= htmlspecialchars($payment['employee_email'] ?? '') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Phone:</strong> <?= htmlspecialchars($payment['employee_phone'] ?? '-') ?></div>
                        <div class="col-md-6"><strong>Payment Date:</strong> <?= htmlspecialchars($payment['payment_date'] ?? '') ?></div>
                    </div>
                    <table class="table table-bordered mt-3">
                        <tr><th>Description</th><th class="text-end">Amount</th></tr>
                        <tr><td>Gross Salary</td><td class="text-end">₹<?= number_format($payment['gross_salary'] ?? 0, 2) ?></td></tr>
                        <tr><td>Total Deductions</td><td class="text-end text-danger">- ₹<?= number_format($payment['total_deductions'] ?? 0, 2) ?></td></tr>
                        <tr class="table-success"><td><strong>Net Salary</strong></td><td class="text-end"><strong>₹<?= number_format($payment['net_salary'] ?? 0, 2) ?></strong></td></tr>
                    </table>
                    <div class="row mt-3">
                        <div class="col-md-4"><strong>Method:</strong> <?= ucfirst(str_replace('_',' ', $payment['payment_method'] ?? 'bank_transfer')) ?></div>
                        <div class="col-md-4"><strong>Transaction ID:</strong> <?= htmlspecialchars($payment['transaction_id'] ?? '-') ?></div>
                        <div class="col-md-4"><strong>Paid By:</strong> Admin #<?= $payment['paid_by'] ?? 0 ?></div>
                    </div>
                    <?php if ($payment['notes'] ?? ''): ?>
                    <div class="mt-3"><strong>Notes:</strong><br><?= nl2br(htmlspecialchars($payment['notes'])) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-cog me-2"></i>Payment Flow</h5></div>
                <div class="card-body aps-cp-card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-user me-2 text-primary"></i>Employee: <?= htmlspecialchars($payment['employee_name'] ?? '') ?></li>
                        <li class="mb-2"><i class="fas fa-calculator me-2 text-info"></i>Gross: ₹<?= number_format($payment['gross_salary'] ?? 0, 2) ?></li>
                        <li class="mb-2"><i class="fas fa-minus-circle me-2 text-danger"></i>Deductions: ₹<?= number_format($payment['total_deductions'] ?? 0, 2) ?></li>
                        <li class="mb-2"><i class="fas fa-check-circle me-2 text-success"></i>Net: <strong>₹<?= number_format($payment['net_salary'] ?? 0, 2) ?></strong></li>
                        <li class="mb-2"><i class="fas fa-calendar me-2 text-warning"></i>Paid on: <?= htmlspecialchars($payment['payment_date'] ?? '-') ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
