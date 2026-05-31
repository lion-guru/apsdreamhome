<?php $pageTitle = 'Customer Report'; ?>
<?php $users = $users ?? []; $summary = $summary ?? ['total' => 0, 'new_this_month' => 0, 'active' => 0, 'total_purchases' => 0]; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>reports/dashboard">Reports</a></li><li class="breadcrumb-item active">Customer Report</li></ol></nav>
    <div class="d-flex justify-content-between align-items-center mb-4"><h4 class="mb-0"><i class="fas fa-user-tie me-2"></i>Customer Report</h4><a href="<?= BASE_URL ?>reports/generate?type=customer" class="btn btn-primary btn-sm"><i class="fas fa-download me-1"></i>Export</a></div>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Total users</small><h4 class="mb-0"><?= number_format($summary['total'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">New This Month</small><h4 class="text-success mb-0"><?= number_format($summary['new_this_month'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Active users</small><h4 class="text-info mb-0"><?= number_format($summary['active'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Total Purchases</small><h4 class="text-primary mb-0">₹<?= number_format($summary['total_purchases'] ?? 0) ?></h4></div></div></div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-list me-2"></i>Customer List</h6></div>
        <div class="card-body p-0">
            <?php if (empty($users)): ?>
            <div class="text-center py-4"><p class="text-muted mb-0">No customer data available</p></div>
            <?php else: ?>
            <div class="table-responsive"><div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive"><thead class="table-light"><tr><th>Customer</th><th>Email</th><th>Phone</th><th>Properties</th><th>Total Spent</th><th>Joined</th><th>Status</th></tr></thead>
                <tbody><?php foreach ($users as $c): ?><tr>
                    <td><?= htmlspecialchars($c['name'] ?? '-') ?></td><td><?= htmlspecialchars($c['email'] ?? '-') ?></td><td><?= htmlspecialchars($c['phone'] ?? '-') ?></td>
                    <td><?= number_format($c['properties_count'] ?? 0) ?></td><td>₹<?= number_format($c['total_spent'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($c['created_at'] ?? '-') ?></td>
                    <td><span class="badge bg-<?= ($c['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($c['status'] ?? '-') ?></span></td>
                </tr><?php endforeach; ?></tbody></table></div></div>
            <?php endif; ?>
        </div>
    </div>
</div>
