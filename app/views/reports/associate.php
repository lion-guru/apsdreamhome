<?php $pageTitle = 'Associate Report'; ?>
<?php $users = $users ?? []; $summary = $summary ?? ['total' => 0, 'active' => 0, 'total_commission' => 0, 'total_sales' => 0]; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>reports/dashboard">Reports</a></li><li class="breadcrumb-item active">Associate Performance</li></ol></nav>
    <div class="d-flex justify-content-between align-items-center mb-4"><h4 class="mb-0"><i class="fas fa-handshake me-2"></i>Associate Performance Report</h4><a href="<?= BASE_URL ?>reports/generate?type=associate" class="btn btn-primary btn-sm"><i class="fas fa-download me-1"></i>Export</a></div>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Total users</small><h4 class="mb-0"><?= number_format($summary['total'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Active</small><h4 class="text-success mb-0"><?= number_format($summary['active'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Total Commission</small><h4 class="text-warning mb-0">₹<?= number_format($summary['total_commission'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Total Sales</small><h4 class="text-primary mb-0">₹<?= number_format($summary['total_sales'] ?? 0) ?></h4></div></div></div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-ranking-star me-2"></i>Associate Rankings</h6></div>
        <div class="card-body p-0">
            <?php if (empty($users)): ?>
            <div class="text-center py-4"><p class="text-muted mb-0">No associate data available</p></div>
            <?php else: ?>
            <div class="table-responsive"><div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive"><thead class="table-light"><tr><th>#</th><th>Associate</th><th>Properties Sold</th><th>Total Sales</th><th>Commission</th><th>Rating</th><th>Status</th></tr></thead>
                <tbody><?php $r = 1; foreach ($users as $a): ?><tr>
                    <td><?= $r++ ?></td><td><?= htmlspecialchars($a['name'] ?? '-') ?></td>
                    <td><?= number_format($a['properties_sold'] ?? 0) ?></td>
                    <td>₹<?= number_format($a['total_sales'] ?? 0) ?></td>
                    <td>₹<?= number_format($a['commission'] ?? 0) ?></td>
                    <td><?= str_repeat('<i class="fas fa-star text-warning"></i>', min(5, $a['rating'] ?? 0)) ?></td>
                    <td><span class="badge bg-<?= ($a['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($a['status'] ?? '-') ?></span></td>
                </tr><?php endforeach; ?></tbody></table></div></div>
            <?php endif; ?>
        </div>
    </div>
</div>
