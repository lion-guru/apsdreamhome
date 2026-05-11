<?php $pageTitle = 'Payment Plans'; ?>
<?php $plans = $plans ?? []; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>payments">Payments</a></li>
            <li class="breadcrumb-item active">EMI / Installment Plans</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Installment Plans</h4>
        <a href="<?= BASE_URL ?>payments/initiate" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Payment</a>
    </div>
    <?php if (empty($plans)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5"><i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i><h6 class="text-muted">No installment plans found</h6><p class="text-muted small">Create a payment to start a plan.</p><a href="<?= BASE_URL ?>payments/initiate" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Create Payment</a></div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Plan ID</th><th>Customer</th><th>Total Amount</th><th>Paid</th><th>Balance</th><th>Installments</th><th>Next Due</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody><?php foreach ($plans as $pl): $paid = $pl['paid'] ?? 0; $total = $pl['total_amount'] ?? 0; ?>
                        <tr>
                            <td><code>#<?= str_pad($pl['id'] ?? 0, 4, '0', STR_PAD_LEFT) ?></code></td>
                            <td><?= htmlspecialchars($pl['customer_name'] ?? '-') ?></td>
                            <td>₹<?= number_format($total) ?></td>
                            <td class="text-success">₹<?= number_format($paid) ?></td>
                            <td class="text-warning">₹<?= number_format(max(0, $total - $paid)) ?></td>
                            <td><?= ($pl['completed_installments'] ?? 0) ?> / <?= $pl['total_installments'] ?? 0 ?></td>
                            <td><?= htmlspecialchars($pl['next_due_date'] ?? '-') ?></td>
                            <td><span class="badge bg-<?= ($pl['status'] ?? '') === 'active' ? 'success' : (($pl['status'] ?? '') === 'completed' ? 'secondary' : 'warning') ?>"><?= ucfirst($pl['status'] ?? 'unknown') ?></span></td>
                            <td><a href="<?= BASE_URL ?>payments/initiate?plan_id=<?= $pl['id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-credit-card"></i> Pay</a></td>
                        </tr>
                    <?php endforeach; ?></tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
