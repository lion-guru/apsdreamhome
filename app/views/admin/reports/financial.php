<?php
$page_title = $page_title ?? 'Financial Reports';
$totalReceipts = $totalReceipts ?? 0;
$totalBookings = $totalBookings ?? 0;
$gstOutput = $gstOutput ?? 0;
$gstInput = $gstInput ?? 0;
$gstPayable = $gstPayable ?? 0;
$totalTds = $totalTds ?? 0;
$depositedTds = $depositedTds ?? 0;
$pendingTds = $pendingTds ?? 0;
$bankAccounts = $bankAccounts ?? [];
$totalBankBalance = $totalBankBalance ?? 0;
$escrowBalance = $escrowBalance ?? 0;
$reconciliations = $reconciliations ?? 0;
$pendingRecon = $pendingRecon ?? 0;
$monthlyData = $monthlyData ?? [];
$methodBreakdown = $methodBreakdown ?? [];
$recentPayments = $recentPayments ?? [];
$netIncome = $netIncome ?? $totalReceipts;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Financial Reports</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/finance/cash-book" class="btn btn-outline-primary btn-sm"><i class="fas fa-book me-1"></i>Cash Book</a>
            <a href="<?= BASE_URL ?>/admin/finance/tds" class="btn btn-outline-primary btn-sm"><i class="fas fa-percentage me-1"></i>TDS</a>
            <a href="<?= BASE_URL ?>/admin/finance/gst" class="btn btn-outline-primary btn-sm"><i class="fas fa-receipt me-1"></i>GST</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-arrow-up"></i></span></div>
                    <div><div class="aps-cp-stat-label">Total Income</div><div class="aps-cp-stat-value text-success">₹<?= number_format($totalReceipts/100000,1) ?>L</div><div class="aps-cp-stat-meta">Bookings: ₹<?= number_format($totalBookings/100000,1) ?>L</div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-info rounded-pill p-2"><i class="fas fa-receipt"></i></span></div>
                    <div><div class="aps-cp-stat-label">GST Payable</div><div class="aps-cp-stat-value">₹<?= number_format($gstPayable) ?></div><div class="aps-cp-stat-meta">Output: ₹<?= number_format($gstOutput) ?> | ITC: ₹<?= number_format($gstInput) ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-warning rounded-pill p-2"><i class="fas fa-percentage"></i></span></div>
                    <div><div class="aps-cp-stat-label">TDS Deducted</div><div class="aps-cp-stat-value">₹<?= number_format($totalTds) ?></div><div class="aps-cp-stat-meta">Deposited: ₹<?= number_format($depositedTds) ?> | Pending: ₹<?= number_format($pendingTds) ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fas fa-university"></i></span></div>
                    <div><div class="aps-cp-stat-label">Bank Balance</div><div class="aps-cp-stat-value">₹<?= number_format($totalBankBalance/100000,1) ?>L</div><div class="aps-cp-stat-meta">Escrow: ₹<?= number_format($escrowBalance/100000,1) ?>L</div></div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-university me-2"></i>Bank Accounts</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($bankAccounts)): ?>
                        <div class="text-center text-muted py-3">No bank accounts</div>
                    <?php else: ?>
                        <?php foreach ($bankAccounts as $ba): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                <div><strong class="small"><?= htmlspecialchars($ba['account_name'] ?? '') ?></strong><br><small class="text-muted"><?= htmlspecialchars($ba['bank_name'] ?? '') ?></small></div>
                                <div class="text-end"><strong class="text-success">₹<?= number_format($ba['current_balance']) ?></strong><br><small class="text-muted"><?= $ba['is_escrow'] ? 'Escrow' : ucfirst($ba['account_type']) ?></small></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-credit-card me-2"></i>Payment Methods</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($methodBreakdown)): ?>
                        <div class="text-center text-muted py-3">No payments recorded</div>
                    <?php else: ?>
                        <?php foreach ($methodBreakdown as $m): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-capitalize"><i class="fas fa-credit-card me-1"></i><?= htmlspecialchars($m['payment_method'] ?? '') ?></span>
                                <div class="text-end"><strong>₹<?= number_format($m['total']) ?></strong><br><small class="text-muted"><?= $m['cnt'] ?> txns</small></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-balance-scale me-2"></i>Bank Reconciliation</div>
                <div class="aps-cp-card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between"><small>Completed</small><small class="text-success"><?= $reconciliations ?></small></div>
                        <div class="progress style-32124"><div class="progress-bar bg-success style-78654"></div></div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between"><small>Pending</small><small class="text-warning"><?= $pendingRecon ?></small></div>
                        <div class="progress style-32124"><div class="progress-bar bg-warning style-6772"></div></div>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?= BASE_URL ?>/admin/finance/reconciliation" class="btn btn-outline-primary btn-sm">View Reconciliation</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header"><i class="fas fa-list me-2"></i>Recent Payments</div>
        <div class="aps-cp-card-body">
            <?php if (empty($recentPayments)): ?>
                <div class="text-center text-muted py-4"><i class="fas fa-credit-card fa-2x mb-2"></i><p>No payments recorded</p></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>ID</th><th>User</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentPayments as $p): ?>
                            <tr>
                                <td>#<?= $p['id'] ?></td>
                                <td><?= htmlspecialchars($p['name'] ?? 'N/A') ?></td>
                                <td><strong>₹<?= number_format($p['amount']) ?></strong></td>
                                <td><span class="text-capitalize"><?= htmlspecialchars($p['payment_method'] ?? '') ?></span></td>
                                <td><span class="aps-cp-badge badge bg-<?= $p['payment_status'] === 'completed' ? 'success' : ($p['payment_status'] === 'pending' ? 'warning' : 'danger') ?>"><?= ucfirst(htmlspecialchars($p['payment_status'] ?? '')) ?></span></td>
                                <td class="text-muted small"><?= htmlspecialchars($p['created_at'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
