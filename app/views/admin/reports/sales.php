<?php $pageTitle = 'Sales Report'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-chart-line me-2"></i>Sales Report</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/reports">Reports</a></li>
                    <li class="breadcrumb-item active">Sales</li>
                </ul>
            </div>
            <div class="col-auto">
                <button class="btn btn-success btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
                <button class="btn btn-primary btn-sm"><i class="fas fa-download me-1"></i>Export</button>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h6 class="text-muted">This Month</h6><h4 class="text-primary mb-0">₹<?= number_format($monthRevenue ?? 0, 2) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h6 class="text-muted">This Quarter</h6><h4 class="text-success mb-0">₹<?= number_format($quarterRevenue ?? 0, 2) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h6 class="text-muted">This Year</h6><h4 class="text-info mb-0">₹<?= number_format($yearRevenue ?? 0, 2) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h6 class="text-muted">Growth</h6><h4 class="text-<?= ($growth ?? 0) >= 0 ? 'success' : 'danger' ?> mb-0"><?= ($growth ?? 0) >= 0 ? '+' : '' ?><?= number_format($growth ?? 0, 1) ?>%</h4></div></div></div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-table me-2"></i>Sales Data</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">Period</th><th>Sales Count</th><th>Revenue</th><th>Avg. Deal</th><th class="text-end pe-4">Growth</th></tr></thead>
                    <tbody>
                        <?php if (empty($salesData)): ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted"><i class="fas fa-chart-line fa-3x d-block mb-3"></i>No sales data available</td></tr>
                        <?php else: ?>
                            <?php foreach ($salesData as $row): ?>
                            <tr><td class="ps-4"><strong><?= $row['period'] ?></strong></td><td><?= $row['count'] ?? 0 ?></td><td>₹<?= number_format($row['revenue'] ?? 0, 2) ?></td><td>₹<?= number_format($row['avg_deal'] ?? 0, 2) ?></td><td class="text-end pe-4 text-<?= ($row['growth'] ?? 0) >= 0 ? 'success' : 'danger' ?>"><?= ($row['growth'] ?? 0) >= 0 ? '+' : '' ?><?= number_format($row['growth'] ?? 0, 1) ?>%</td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
