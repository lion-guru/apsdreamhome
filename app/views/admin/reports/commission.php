<?php $pageTitle = 'Commission Report'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-coins me-2"></i>Commission Report</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/reports">Reports</a></li>
                    <li class="breadcrumb-item active">Commission</li>
                </ul>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm"><i class="fas fa-download me-1"></i>Export</button>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total Commission</h6><h3 class="mb-0">₹<?= number_format($totalCommission ?? 0, 2) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Paid</h6><h3 class="mb-0">₹<?= number_format($paidCommission ?? 0, 2) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Pending</h6><h3 class="mb-0">₹<?= number_format($pendingCommission ?? 0, 2) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Recipients</h6><h3 class="mb-0"><?= number_format($recipientCount ?? 0) ?></h3></div></div></div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Commission Breakdown</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">Associate</th><th>Sales Count</th><th>Total Sales</th><th>Commission</th><th>Paid</th><th>Pending</th><th class="text-end pe-4">Status</th></tr></thead>
                    <tbody>
                        <?php if (empty($commissionData)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-coins fa-3x d-block mb-3"></i>No commission data available</td></tr>
                        <?php else: ?>
                            <?php foreach ($commissionData as $row): ?>
                            <tr><td class="ps-4"><strong><?= $row['name'] ?></strong></td><td><?= $row['sales_count'] ?? 0 ?></td><td>₹<?= number_format($row['total_sales'] ?? 0, 2) ?></td><td class="fw-bold">₹<?= number_format($row['commission'] ?? 0, 2) ?></td><td>₹<?= number_format($row['paid'] ?? 0, 2) ?></td><td>₹<?= number_format(($row['commission'] ?? 0) - ($row['paid'] ?? 0), 2) ?></td><td class="text-end pe-4"><span class="badge bg-<?= (($row['commission'] ?? 0) - ($row['paid'] ?? 0)) <= 0 ? 'success' : 'warning' ?>-subtle text-<?= (($row['commission'] ?? 0) - ($row['paid'] ?? 0)) <= 0 ? 'success' : 'warning' ?> rounded-pill px-3"><?= (($row['commission'] ?? 0) - ($row['paid'] ?? 0)) <= 0 ? 'Settled' : 'Due' ?></span></td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
