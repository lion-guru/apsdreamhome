<?php $pageTitle = 'Sales Report'; ?>
<?php $salesData = $salesData ?? []; $summary = $summary ?? ['total_revenue' => 0, 'total_transactions' => 0, 'avg_value' => 0, 'conversion_rate' => 0]; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>reports/dashboard">Reports</a></li><li class="breadcrumb-item active">Sales Report</li></ol></nav>
    <div class="d-flex justify-content-between align-items-center mb-4"><h4 class="mb-0"><i class="fas fa-chart-line me-2"></i>Sales Report</h4><a href="<?= BASE_URL ?>reports/generate?type=sales" class="btn btn-primary btn-sm"><i class="fas fa-download me-1"></i>Export</a></div>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Total Revenue</small><h4 class="text-success mb-0">â‚¹<?= number_format($summary['total_revenue'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Transactions</small><h4 class="text-primary mb-0"><?= number_format($summary['total_transactions'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Avg. Value</small><h4 class="text-info mb-0">â‚¹<?= number_format($summary['avg_value'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Conversion</small><h4 class="text-warning mb-0"><?= number_format($summary['conversion_rate'] ?? 0, 1) ?>%</h4></div></div></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm"><div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Revenue Trend</h6></div><div class="card-body aps-cp-card-body"><div class="bg-light rounded d-flex align-items-center justify-content-center" class="style-53696"><p class="text-muted mb-0"><i class="fas fa-chart-simple me-2"></i>Chart will render here</p></div></div></div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm"><div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>By Category</h6></div><div class="card-body aps-cp-card-body"><div class="bg-light rounded d-flex align-items-center justify-content-center" class="style-53696"><p class="text-muted mb-0"><i class="fas fa-chart-pie me-2"></i>Pie chart placeholder</p></div></div></div>
        </div>
    </div>
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-list me-2"></i>Sales Transactions</h6></div>
        <div class="card-body p-0">
            <?php if (empty($salesData)): ?>
            <div class="text-center py-4"><p class="text-muted mb-0">No sales data available for the selected period</p></div>
            <?php else: ?>
            <div class="table-responsive"><div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive"><thead class="table-light"><tr><th>Date</th><th>Invoice</th><th>Customer</th><th>Property</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody><?php foreach ($salesData as $s): ?><tr><td><?= htmlspecialchars($s['date'] ?? '-') ?></td><td><code><?= htmlspecialchars($s['invoice'] ?? '-') ?></code></td><td><?= htmlspecialchars($s['customer'] ?? '-') ?></td><td><?= htmlspecialchars($s['property'] ?? '-') ?></td><td>â‚¹<?= number_format($s['amount'] ?? 0) ?></td><td><span class="badge bg-<?= ($s['status'] ?? '') === 'completed' ? 'success' : 'warning' ?>"><?= ucfirst($s['status'] ?? '-') ?></span></td></tr><?php endforeach; ?></tbody></table></div></div>
            <?php endif; ?>
        </div>
    </div>
</div>
