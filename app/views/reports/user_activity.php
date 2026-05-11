<?php $pageTitle = 'User Activity Report'; ?>
<?php $activities = $activities ?? []; $summary = $summary ?? ['total_users' => 0, 'active_today' => 0, 'new_this_month' => 0, 'total_logins' => 0]; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>reports/dashboard">Reports</a></li><li class="breadcrumb-item active">User Activity</li></ol></nav>
    <div class="d-flex justify-content-between align-items-center mb-4"><h4 class="mb-0"><i class="fas fa-users me-2"></i>User Activity Report</h4><a href="<?= BASE_URL ?>reports/generate?type=user_activity" class="btn btn-primary btn-sm"><i class="fas fa-download me-1"></i>Export</a></div>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Total Users</small><h4 class="text-primary mb-0"><?= number_format($summary['total_users'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Active Today</small><h4 class="text-success mb-0"><?= number_format($summary['active_today'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">New This Month</small><h4 class="text-info mb-0"><?= number_format($summary['new_this_month'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Total Logins</small><h4 class="text-warning mb-0"><?= number_format($summary['total_logins'] ?? 0) ?></h4></div></div></div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-list me-2"></i>Recent Activity</h6></div>
        <div class="card-body p-0">
            <?php if (empty($activities)): ?>
            <div class="text-center py-4"><p class="text-muted mb-0">No recent user activity found</p></div>
            <?php else: ?>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>User</th><th>Role</th><th>Action</th><th>IP Address</th><th>Date/Time</th></tr></thead>
                <tbody><?php foreach ($activities as $a): ?><tr><td><?= htmlspecialchars($a['user_name'] ?? $a['user'] ?? '-') ?></td><td><?= htmlspecialchars(ucfirst($a['role'] ?? '-')) ?></td><td><?= htmlspecialchars($a['action'] ?? '-') ?></td><td><code><?= htmlspecialchars($a['ip_address'] ?? '-') ?></code></td><td><?= htmlspecialchars($a['created_at'] ?? '-') ?></td></tr><?php endforeach; ?></tbody></table></div>
            <?php endif; ?>
        </div>
    </div>
</div>
