<?php $pageTitle = 'Financial Report'; ?>
<?php $transactions = $transactions ?? []; $summary = $summary ?? ['total_income' => 0, 'total_expenses' => 0, 'net_profit' => 0, 'pending_payments' => 0]; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>reports/dashboard">Reports</a></li><li class="breadcrumb-item active">Financial Report</li></ol></nav>
    <div class="d-flex justify-content-between align-items-center mb-4"><h4 class="mb-0"><i class="fas fa-rupee-sign me-2"></i>Financial Report</h4><a href="<?= BASE_URL ?>reports/generate?type=financial" class="btn btn-primary btn-sm"><i class="fas fa-download me-1"></i>Export</a></div>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Total Income</small><h4 class="text-success mb-0">â‚¹<?= number_format($summary['total_income'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Total Expenses</small><h4 class="text-danger mb-0">â‚¹<?= number_format($summary['total_expenses'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Net Profit</small><h4 class="text-<?= ($summary['net_profit'] ?? 0) >= 0 ? 'success' : 'danger' ?> mb-0">â‚¹<?= number_format($summary['net_profit'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Pending</small><h4 class="text-warning mb-0">â‚¹<?= number_format($summary['pending_payments'] ?? 0) ?></h4></div></div></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-arrow-up text-success me-2"></i>Income Breakdown</h6></div><div class="card-body aps-cp-card-body"><div class="bg-light rounded d-flex align-items-center justify-content-center" class="style-48982"><p class="text-muted mb-0"><i class="fas fa-chart-bar me-2"></i>Income chart placeholder</p></div></div></div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-arrow-down text-danger me-2"></i>Expense Breakdown</h6></div><div class="card-body aps-cp-card-body"><div class="bg-light rounded d-flex align-items-center justify-content-center" class="style-48982"><p class="text-muted mb-0"><i class="fas fa-chart-bar me-2"></i>Expense chart placeholder</p></div></div></div>
        </div>
    </div>
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-list me-2"></i>Transactions</h6></div>
        <div class="card-body p-0">
            <?php if (empty($transactions)): ?>
            <div class="text-center py-4"><p class="text-muted mb-0">No financial transactions found</p></div>
            <?php else: ?>
            <div class="table-responsive"><div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive"><thead class="table-light"><tr><th>Date</th><th>Description</th><th>Category</th><th>Type</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody><?php foreach ($transactions as $t): ?><tr>
                    <td><?= htmlspecialchars($t['date'] ?? '-') ?></td><td><?= htmlspecialchars($t['description'] ?? '-') ?></td><td><?= htmlspecialchars(ucfirst($t['category'] ?? '-')) ?></td>
                    <td><span class="badge bg-<?= ($t['type'] ?? '') === 'income' ? 'success' : 'danger' ?>"><?= ucfirst($t['type'] ?? '-') ?></span></td>
                    <td class="<?= ($t['type'] ?? '') === 'income' ? 'text-success' : 'text-danger' ?>">â‚¹<?= number_format($t['amount'] ?? 0) ?></td>
                    <td><span class="badge bg-<?= ($t['status'] ?? '') === 'completed' ? 'success' : 'warning' ?>"><?= ucfirst($t['status'] ?? '-') ?></span></td>
                </tr><?php endforeach; ?></tbody></table></div></div>
            <?php endif; ?>
        </div>
    </div>
</div>
