<?php $pageTitle = 'Accounting Dashboard'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-book me-2"></i>Accounting Dashboard</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Accounting</li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/admin/accounting/add_income" class="btn btn-success btn-sm me-2"><i class="fas fa-plus me-1"></i>Add Income</a>
                <a href="<?= BASE_URL ?>/admin/accounting/add_expenses" class="btn btn-danger btn-sm"><i class="fas fa-minus me-1"></i>Add Expense</a>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h6 class="card-title mb-1">Total Income</h6><h3 class="mb-0">₹<?= number_format($totalIncome ?? 0, 2) ?></h3></div>
                        <i class="fas fa-arrow-up fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h6 class="card-title mb-1">Total Expenses</h6><h3 class="mb-0">₹<?= number_format($totalExpenses ?? 0, 2) ?></h3></div>
                        <i class="fas fa-arrow-down fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h6 class="card-title mb-1">Net Balance</h6><h3 class="mb-0">₹<?= number_format(($totalIncome ?? 0) - ($totalExpenses ?? 0), 2) ?></h3></div>
                        <i class="fas fa-wallet fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h6 class="card-title mb-1">Transactions</h6><h3 class="mb-0"><?= number_format($transactionCount ?? 0) ?></h3></div>
                        <i class="fas fa-exchange-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Transactions</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">Date</th><th>Description</th><th>Type</th><th class="text-end pe-4">Amount</th></tr></thead>
                    <tbody>
                        <?php if (empty($recentTransactions)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No transactions yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentTransactions as $t): ?>
                            <tr><td class="ps-4"><?= date('d M Y', strtotime($t['date'])) ?></td><td><?= $t['description'] ?></td><td><span class="badge bg-<?= $t['type'] === 'Income' ? 'success' : 'danger' ?>-subtle text-<?= $t['type'] === 'Income' ? 'success' : 'danger' ?> rounded-pill px-3"><?= $t['type'] ?></span></td><td class="text-end pe-4 fw-bold text-<?= $t['type'] === 'Income' ? 'success' : 'danger' ?>">₹<?= number_format($t['amount'] ?? 0, 2) ?></td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
