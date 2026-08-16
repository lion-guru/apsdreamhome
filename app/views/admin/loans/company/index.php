<?php
$stats = $stats ?? [];
$loans = $loans ?? [];
$offers = $offers ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-hand-holding-usd me-2 text-primary"></i>Company Loan Management</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/company-loans/calculator" class="btn btn-outline-primary btn-sm me-1"><i class="fas fa-calculator me-1"></i>Calculator</a>
            <a href="<?= BASE_URL ?>/admin/company-loans/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Loan</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fas fa-file-invoice"></i></span></div>
                    <div><div class="aps-cp-stat-label">Total Loans</div><div class="aps-cp-stat-value"><?= (int)($stats['total_loans'] ?? 0) ?></div><div class="aps-cp-stat-meta">Pending: <?= (int)($stats['pending_loans'] ?? 0) ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-check-circle"></i></span></div>
                    <div><div class="aps-cp-stat-label">Active</div><div class="aps-cp-stat-value"><?= (int)($stats['active_loans'] ?? 0) ?></div><div class="aps-cp-stat-meta">Completed: <?= (int)($stats['completed_loans'] ?? 0) ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-danger rounded-pill p-2"><i class="fas fa-exclamation-triangle"></i></span></div>
                    <div><div class="aps-cp-stat-label">Defaulted</div><div class="aps-cp-stat-value text-danger"><?= (int)($stats['defaulted_loans'] ?? 0) ?></div><div class="aps-cp-stat-meta">Overdue: <?= (int)($stats['overdue_count'] ?? 0) ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-warning rounded-pill p-2"><i class="fas fa-rupee-sign"></i></span></div>
                    <div><div class="aps-cp-stat-label">Outstanding</div><div class="aps-cp-stat-value">₹<?= number_format(($stats['total_outstanding'] ?? 0) / 100000, 1) ?>L</div><div class="aps-cp-stat-meta">Collected: ₹<?= number_format(($stats['total_collected'] ?? 0) / 100000, 1) ?>L</div></div>
                </div>
            </div></div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-bold me-2"><i class="fas fa-bolt me-1"></i>Quick Actions:</span>
                    <a href="<?= BASE_URL ?>/admin/company-loans/offers" class="btn btn-outline-info btn-sm"><i class="fas fa-tags me-1"></i>Manage Offers (<?= count($offers) ?>)</a>
                    <a href="<?= BASE_URL ?>/admin/company-loans/early-incentives" class="btn btn-outline-success btn-sm"><i class="fas fa-gift me-1"></i>Early Incentives</a>
                    <form method="POST" action="<?= BASE_URL ?>/admin/company-loans/run-penalties" class="style-71727" onsubmit="return confirm('Apply daily penalties to all overdue installments?')">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <button type="submit" class="btn btn-outline-warning btn-sm"><i class="fas fa-gavel me-1"></i>Run Penalties</button>
                    </form>
                </div>
            </div></div>
        </div>
    </div>

    <!-- Loans Table -->
    <div class="aps-cp-card">
        <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i>All Company Loans</span>
            <div class="d-flex gap-1">
                <a href="?status=active" class="btn btn-sm btn-outline-success">Active</a>
                <a href="?status=pending" class="btn btn-sm btn-outline-warning">Pending</a>
                <a href="?status=defaulted" class="btn btn-sm btn-outline-danger">Defaulted</a>
                <a href="?status=completed" class="btn btn-sm btn-outline-info">Completed</a>
                <a href="<?= BASE_URL ?>/admin/company-loans" class="btn btn-sm btn-outline-secondary">All</a>
            </div>
        </div>
        <div class="aps-cp-card-body p-0">
            <?php if (empty($loans)): ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-hand-holding-usd fa-3x mb-3"></i>
                    <p>No company loans found. <a href="<?= BASE_URL ?>/admin/company-loans/create">Create the first loan</a>.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr>
                            <th>Loan #</th><th>Customer</th><th>Amount</th><th>EMI</th><th>Tenure</th><th>Paid</th><th>Balance</th><th>Status</th><th>Actions</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($loans as $l): ?>
                            <tr>
                                <td><a href="<?= BASE_URL ?>/admin/company-loans/<?= $l['id'] ?>" class="fw-bold"><?= htmlspecialchars($l['loan_number'] ?? '') ?></a></td>
                                <td><?= htmlspecialchars($l['customer_name'] ?? 'N/A') ?><br><small class="text-muted"><?= htmlspecialchars($l['customer_phone'] ?? '') ?></small></td>
                                <td>₹<?= number_format($l['loan_amount'] / 100000, 1) ?>L</td>
                                <td>₹<?= number_format($l['emi_amount']) ?></td>
                                <td><?= $l['tenure_months'] ?>m</td>
                                <td>₹<?= number_format($l['amount_paid'] / 100000, 1) ?>L</td>
                                <td><strong>₹<?= number_format($l['balance_amount'] / 100000, 1) ?>L</strong></td>
                                <td>
                                    <span class="aps-cp-badge badge bg-<?= match($l['status']) {
                                        'active' => 'success', 'pending' => 'warning', 'completed' => 'info',
                                        'defaulted' => 'danger', 'foreclosed' => 'secondary', 'cancelled' => 'dark'
                                    } ?>"><?= ucfirst($l['status']) ?></span>
                                    <?php if (!empty($l['interest_free_active'])): ?>
                                        <br><small class="text-success"><i class="fas fa-star"></i> Interest-Free</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/admin/company-loans/<?= $l['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
