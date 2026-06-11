<?php $pageTitle = 'Payment Dashboard'; ?>
<?php $payments = $payments ?? []; $totalReceived = $totalReceived ?? 0; $totalPending = $totalPending ?? 0; $totalRefunded = $totalRefunded ?? 0; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item active">Payments</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Dashboard</h4>
        <div>
            <a href="<?= BASE_URL ?>payments/initiate" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Payment</a>
            <a href="<?= BASE_URL ?>payments/history" class="btn btn-outline-secondary btn-sm"><i class="fas fa-history me-1"></i>History</a>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><small class="text-muted text-uppercase fw-bold">Total Received</small><h3 class="mb-0 mt-1 text-success">₹<?= number_format($totalReceived) ?></h3></div>
                        <div class="bg-success-subtle p-3 rounded"><i class="fas fa-wallet fa-2x text-success"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><small class="text-muted text-uppercase fw-bold">Pending</small><h3 class="mb-0 mt-1 text-warning">₹<?= number_format($totalPending) ?></h3></div>
                        <div class="bg-warning-subtle p-3 rounded"><i class="fas fa-clock fa-2x text-warning"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><small class="text-muted text-uppercase fw-bold">Refunded</small><h3 class="mb-0 mt-1 text-info">₹<?= number_format($totalRefunded) ?></h3></div>
                        <div class="bg-info-subtle p-3 rounded"><i class="fas fa-undo fa-2x text-info"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><small class="text-muted text-uppercase fw-bold">Transactions</small><h3 class="mb-0 mt-1 text-primary"><?= count($payments) ?></h3></div>
                        <div class="bg-primary-subtle p-3 rounded"><i class="fas fa-exchange-alt fa-2x text-primary"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center"><h6 class="mb-0"><i class="fas fa-list me-2"></i>Recent Transactions</h6></div>
        <div class="card-body p-0">
            <?php if (empty($payments)): ?>
            <div class="text-center py-5"><i class="fas fa-credit-card fa-3x text-muted mb-3"></i><p class="text-muted">No transactions yet</p><a href="<?= BASE_URL ?>payments/initiate" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Initiate Payment</a></div>
            <?php else: ?>
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                    <thead class="table-light"><tr><th>#</th><th>Transaction ID</th><th>Purpose</th><th>Amount</th><th>Gateway</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody><?php $i = 1; foreach ($payments as $p): ?>
                        <tr><td><?= $i++ ?></td><td><code><?= htmlspecialchars($p['transaction_id'] ?? '-') ?></code></td><td><?= htmlspecialchars($p['purpose'] ?? '-') ?></td><td>₹<?= number_format($p['amount'] ?? 0) ?></td><td><?= htmlspecialchars($p['gateway'] ?? '-') ?></td>
                        <td><span class="badge bg-<?= ($p['status'] ?? '') === 'completed' ? 'success' : (($p['status'] ?? '') === 'pending' ? 'warning' : 'danger') ?>"><?= ucfirst($p['status'] ?? 'unknown') ?></span></td>
                        <td><?= htmlspecialchars($p['created_at'] ?? '-') ?></td>
                        <td><a href="<?= BASE_URL ?>payments/history?txn=<?= urlencode($p['transaction_id'] ?? '') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td></tr>
                    <?php endforeach; ?></tbody>
                </table></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
