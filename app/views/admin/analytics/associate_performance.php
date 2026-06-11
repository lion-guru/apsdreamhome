<?php $pageTitle = 'Associate Performance Analytics'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-users-gear me-2"></i>Associate Performance</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/analytics">Analytics</a></li>
                    <li class="breadcrumb-item active">Associate Performance</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total users</h6><h3 class="mb-0"><?= number_format($totalAssociates ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Active This Month</h6><h3 class="mb-0"><?= number_format($activeAssociates ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total Sales</h6><h3 class="mb-0">₹<?= number_format($totalSales ?? 0, 2) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Avg. Performance</h6><h3 class="mb-0"><?= number_format($avgPerformance ?? 0) ?>%</h3></div></div></div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-ranking-star me-2"></i>Associate Rankings</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">#</th><th>Name</th><th>Code</th><th>Sales This Month</th><th>Leads Generated</th><th>Conversion Rate</th><th class="text-end pe-4">Rating</th></tr></thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-chart-simple fa-3x d-block mb-3"></i>No associate data available</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $i => $a): ?>
                            <tr><td class="ps-4"><?= $i+1 ?></td><td><strong><?= $a['name'] ?></strong></td><td><?= $a['code'] ?? '-' ?></td><td>₹<?= number_format($a['sales'] ?? 0, 2) ?></td><td><?= $a['leads'] ?? 0 ?></td><td><span class="badge bg-<?= ($a['conversion'] ?? 0) > 50 ? 'success' : 'warning' ?>-subtle text-<?= ($a['conversion'] ?? 0) > 50 ? 'success' : 'warning' ?> rounded-pill px-3"><?= number_format($a['conversion'] ?? 0, 1) ?>%</span></td><td class="text-end pe-4"><?php for($s=1;$s<=5;$s++): ?><i class="fas fa-star<?= $s <= round(($a['rating'] ?? 0)/20) ? ' text-warning' : ' text-muted' ?>"></i><?php endfor; ?></td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
