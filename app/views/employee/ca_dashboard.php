<?php $pageTitle = 'CA Dashboard'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/employee">Employee</a></li>
            <li class="breadcrumb-item active" aria-current="page">CA Dashboard</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-calculator me-2"></i>CA Dashboard</h4>
        <span class="text-muted small"><i class="far fa-calendar-alt me-1"></i><?= date('l, F j, Y') ?></span>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2"><i class="fas fa-rupee-sign"></i></div>
                    <h3 class="fw-bold mb-1">₹<?= number_format($totalRevenue ?? 0) ?></h3>
                    <p class="text-muted mb-0">Total Revenue</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-danger mb-2"><i class="fas fa-wallet"></i></div>
                    <h3 class="fw-bold mb-1">₹<?= number_format($pendingPayments ?? 0) ?></h3>
                    <p class="text-muted mb-0">Pending Payments</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2"><i class="fas fa-file-invoice"></i></div>
                    <h3 class="fw-bold mb-1"><?= $totalTransactions ?? 0 ?></h3>
                    <p class="text-muted mb-0">Transactions</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2"><i class="fas fa-hourglass-half"></i></div>
                    <h3 class="fw-bold mb-1"><?= $pendingApprovals ?? 0 ?></h3>
                    <p class="text-muted mb-0">Pending Approvals</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Recent Transactions</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($recentTransactions)): ?>
                        <div class="table-responsive"><div class="table-responsive"><table class="table table-sm table-hover mb-0 table-responsive">
                            <thead><tr><th>Date</th><th>Description</th><th class="text-end">Amount</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentTransactions as $txn): ?>
                                <tr>
                                    <td class="small"><?= htmlspecialchars($txn['date'] ?? '') ?></td>
                                    <td class="small"><?= htmlspecialchars($txn['description'] ?? '') ?></td>
                                    <td class="text-end small">₹<?= number_format($txn['amount'] ?? 0) ?></td>
                                    <td><span class="badge bg-<?= ($txn['status'] ?? '') === 'completed' ? 'success' : 'warning' ?>"><?= ucfirst($txn['status'] ?? '') ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div></div>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-inbox fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No transactions yet</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Financial Summary</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($financialSummary)): ?>
                        <?php foreach ($financialSummary as $item): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?= htmlspecialchars($item['label'] ?? '') ?></span>
                            <strong>₹<?= number_format($item['amount'] ?? 0) ?></strong>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-chart-line fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No financial data available</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
