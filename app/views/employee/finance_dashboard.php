<?php $pageTitle = 'Finance Dashboard'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/employee">Employee</a></li>
            <li class="breadcrumb-item active" aria-current="page">Finance Dashboard</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-coins me-2"></i>Finance Dashboard</h4>
        <span class="text-muted small"><i class="far fa-calendar-alt me-1"></i><?= date('l, F j, Y') ?></span>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2"><i class="fas fa-arrow-down"></i></div>
                    <h3 class="fw-bold mb-1">₹<?= number_format($totalIncome ?? 0) ?></h3>
                    <p class="text-muted mb-0">Total Income</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-danger mb-2"><i class="fas fa-arrow-up"></i></div>
                    <h3 class="fw-bold mb-1">₹<?= number_format($totalExpenses ?? 0) ?></h3>
                    <p class="text-muted mb-0">Total Expenses</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2"><i class="fas fa-file-invoice"></i></div>
                    <h3 class="fw-bold mb-1"><?= $pendingInvoices ?? 0 ?></h3>
                    <p class="text-muted mb-0">Pending Invoices</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-info mb-2"><i class="fas fa-calendar-check"></i></div>
                    <h3 class="fw-bold mb-1"><?= $taxDeadlines ?? 0 ?></h3>
                    <p class="text-muted mb-0">Upcoming Tax Deadlines</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Recent Transactions</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($recentTransactions)): ?>
                        <div class="table-responsive"><table class="table table-sm table-hover mb-0">
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
                        </table></div>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-receipt fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No transactions yet</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Budget Variance</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($budgetVariance)): ?>
                        <?php foreach ($budgetVariance as $bv): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?= htmlspecialchars($bv['department'] ?? '') ?></span>
                            <span class="badge bg-<?= ($bv['variance'] ?? 0) >= 0 ? 'success' : 'danger' ?>">₹<?= number_format(abs($bv['variance'] ?? 0)) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-chart-pie fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No budget data available</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
