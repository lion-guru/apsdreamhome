<?php $pageTitle = 'Payment History'; ?>
<?php $transactions = $transactions ?? []; $filters = $filters ?? ['status' => '', 'gateway' => '', 'from' => '', 'to' => '']; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>payments">Payments</a></li>
            <li class="breadcrumb-item active">History</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-history me-2"></i>Payment History</h4>
        <a href="<?= BASE_URL ?>payments/initiate" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Payment</a>
    </div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="get" action="<?= BASE_URL ?>payments/history" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select class="form-select form-select-sm" name="status">
                        <option value="">All Statuses</option>
                        <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                        <option value="refunded" <?= ($filters['status'] ?? '') === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Gateway</label>
                    <select class="form-select form-select-sm" name="gateway">
                        <option value="">All Gateways</option>
                        <option value="razorpay" <?= ($filters['gateway'] ?? '') === 'razorpay' ? 'selected' : '' ?>>Razorpay</option>
                        <option value="paypal" <?= ($filters['gateway'] ?? '') === 'paypal' ? 'selected' : '' ?>>PayPal</option>
                        <option value="stripe" <?= ($filters['gateway'] ?? '') === 'stripe' ? 'selected' : '' ?>>Stripe</option>
                        <option value="paytm" <?= ($filters['gateway'] ?? '') === 'paytm' ? 'selected' : '' ?>>Paytm</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">From</label>
                    <input type="date" class="form-control form-control-sm" name="from" value="<?= htmlspecialchars($filters['from'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">To</label>
                    <input type="date" class="form-control form-control-sm" name="to" value="<?= htmlspecialchars($filters['to'] ?? '') ?>">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="<?= BASE_URL ?>payments/history" class="btn btn-outline-secondary btn-sm w-100"><i class="fas fa-times"></i></a>
                </div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($transactions)): ?>
            <div class="text-center py-5"><i class="fas fa-history fa-3x text-muted mb-3"></i><p class="text-muted">No transactions found</p><a href="<?= BASE_URL ?>payments/initiate" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Initiate Payment</a></div>
            <?php else: ?>
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                    <thead class="table-light"><tr><th>#</th><th>Transaction ID</th><th>Customer</th><th>Purpose</th><th>Amount</th><th>Gateway</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody><?php $i = 1; foreach ($transactions as $t): ?>
                        <tr><td><?= $i++ ?></td><td><code><?= htmlspecialchars($t['transaction_id'] ?? '-') ?></code></td><td><?= htmlspecialchars($t['customer_name'] ?? $t['email'] ?? '-') ?></td><td><?= htmlspecialchars($t['purpose'] ?? '-') ?></td><td>₹<?= number_format($t['amount'] ?? 0) ?></td><td><?= htmlspecialchars($t['gateway'] ?? '-') ?></td>
                        <td><span class="badge bg-<?= ($t['status'] ?? '') === 'completed' ? 'success' : (($t['status'] ?? '') === 'pending' ? 'warning' : (($t['status'] ?? '') === 'refunded' ? 'info' : 'danger')) ?>"><?= ucfirst($t['status'] ?? 'unknown') ?></span></td>
                        <td><?= htmlspecialchars($t['created_at'] ?? '-') ?></td>
                        <td><a href="<?= BASE_URL ?>payments/refund?txn=<?= urlencode($t['transaction_id'] ?? '') ?>" class="btn btn-sm btn-outline-warning" title="Refund"><i class="fas fa-undo"></i></a></td></tr>
                    <?php endforeach; ?></tbody>
                </table></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
