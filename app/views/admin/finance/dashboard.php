<?php $page_title = $page_title ?? 'Finance Dashboard'; $page_heading = $page_heading ?? 'Finance'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-wallet me-2 text-primary"></i>Money Workflow Dashboard</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/finance/bank-accounts" class="btn btn-outline-primary"><i class="fas fa-university me-1"></i>Bank Accounts</a>
            <a href="<?= BASE_URL ?>/admin/finance/cash-book" class="btn btn-outline-primary"><i class="fas fa-book me-1"></i>Cash Book</a>
            <a href="<?= BASE_URL ?>/admin/finance/expense-form" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New Expense</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Total Bank Balance</div>
                    <div class="aps-cp-stat-value text-success">₹<?= number_format((float)($stats['total_bank_balance'] ?? 0), 0) ?></div>
                    <div class="aps-cp-stat-meta">Escrow: ₹<?= number_format((float)($stats['escrow_balance'] ?? 0), 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Petty Cash</div>
                    <div class="aps-cp-stat-value text-info">₹<?= number_format((float)($stats['petty_cash'] ?? 0), 0) ?></div>
                    <div class="aps-cp-stat-meta">Cash on hand</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">MTD Net Cash</div>
                    <?php $cn = (float)($stats['cash_net_mtd'] ?? 0); ?>
                    <div class="aps-cp-stat-value <?= $cn >= 0 ? 'text-success' : 'text-danger' ?>">₹<?= number_format($cn, 0) ?></div>
                    <div class="aps-cp-stat-meta">R: ₹<?= number_format((float)($stats['cash_receipts_mtd'] ?? 0), 0) ?> | P: ₹<?= number_format((float)($stats['cash_payments_mtd'] ?? 0), 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Pending Expenses</div>
                    <div class="aps-cp-stat-value text-warning"><?= (int)($stats['pending_expenses'] ?? 0) ?></div>
                    <div class="aps-cp-stat-meta">Awaiting approval</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Cheques Issued</div>
                    <div class="aps-cp-stat-value"><?= (int)($stats['cheques_issued'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Cheques Pending</div>
                    <div class="aps-cp-stat-value text-warning"><?= (int)($stats['cheques_pending'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Cheques Bounced</div>
                    <div class="aps-cp-stat-value text-danger"><?= (int)($stats['cheques_bounced'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">GST Net Payable</div>
                    <div class="aps-cp-stat-value text-primary">₹<?= number_format((float)($stats['gst_net_payable'] ?? 0), 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Recent Cash Transactions</h5>
            <a href="<?= BASE_URL ?>/admin/finance/cash-book" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="aps-cp-card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr><th>Date</th><th>Type</th><th>Party</th><th>Mode</th><th class="text-end">Amount</th><th>Voucher</th></tr>
                </thead>
                <tbody>
                <?php if (empty($recent_txns)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No transactions yet</td></tr>
                <?php else: foreach ($recent_txns as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['transaction_date'] ?? '') ?></td>
                        <td><span class="badge bg-<?= ($t['transaction_type'] ?? '') === 'receipt' ? 'success' : 'danger' ?>"><?= htmlspecialchars($t['transaction_type'] ?? '') ?></span></td>
                        <td><?= htmlspecialchars($t['party_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($t['payment_mode'] ?? '-') ?></td>
                        <td class="text-end fw-bold">₹<?= number_format((float)($t['amount'] ?? 0), 2) ?></td>
                        <td><code><?= htmlspecialchars($t['voucher_number'] ?? '-') ?></code></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
