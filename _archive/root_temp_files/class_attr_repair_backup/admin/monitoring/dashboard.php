ï»¿<?php $pageTitle = 'System Monitoring'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-desktop me-2"></i>System Monitoring</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">System Monitoring</li>
                </ul>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm" onclick="location.reload()"><i class="fas fa-sync me-1"></i>Refresh</button>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center"><i class="fas fa-server fa-2x text-primary mb-2"></i><h6>Server Status</h6><span class="badge bg-success">Online</span></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center"><i class="fas fa-database fa-2x text-info mb-2"></i><h6>Database</h6><span class="badge bg-<?= $dbStatus ?? 'success' ?>"><?= $dbStatusLabel ?? 'Connected' ?></span></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center"><i class="fas fa-clock fa-2x text-warning mb-2"></i><h6>Uptime</h6><strong><?= $uptime ?? '12d 4h 32m' ?></strong></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center"><i class="fas fa-users fa-2x text-success mb-2"></i><h6>Active Users</h6><strong><?= number_format($activeUsers ?? 0) ?></strong></div></div></div>
    </div>
    <div class="row g-4">
        <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>System Resources</h5></div><div class="card-body aps-cp-card-body"><div class="mb-3"><label class="form-label d-flex justify-content-between"><span>CPU Usage</span><span><?= $cpuUsage ?? 45 ?>%</span></label><div class="progress style-32124"><div class="progress-bar bg-<?= ($cpuUsage ?? 45) > 80 ? 'danger' : (($cpuUsage ?? 45) > 60 ? 'warning' : 'success') ?>" class="style-49321"></div></div></div><div class="mb-3"><label class="form-label d-flex justify-content-between"><span>Memory Usage</span><span><?= $memUsage ?? 62 ?>%</span></label><div class="progress style-32124"><div class="progress-bar bg-<?= ($memUsage ?? 62) > 80 ? 'danger' : (($memUsage ?? 62) > 60 ? 'warning' : 'success') ?>" class="style-80241"></div></div></div><div class="mb-3"><label class="form-label d-flex justify-content-between"><span>Disk Usage</span><span><?= $diskUsage ?? 55 ?>%</span></label><div class="progress style-32124"><div class="progress-bar bg-<?= ($diskUsage ?? 55) > 80 ? 'danger' : (($diskUsage ?? 55) > 60 ? 'warning' : 'success') ?>" class="style-82128"></div></div></div></div></div></div>
        <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>System Logs</h5></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-sm mb-0"><thead class="bg-light"><tr><th class="ps-4">Level</th><th>Message</th><th class="text-end pe-4">Time</th></tr></thead><tbody><?php if (empty($recentLogs)): ?><tr><td colspan="3" class="text-center py-4 text-muted"><i class="fas fa-check-circle text-success me-2"></i>No recent errors</td></tr><?php else: ?><?php foreach ($recentLogs as $log): ?><tr><td class="ps-4"><span class="badge bg-<?= $log['level'] === 'error' ? 'danger' : ($log['level'] === 'warning' ? 'warning' : 'info') ?>-subtle text-<?= $log['level'] === 'error' ? 'danger' : ($log['level'] === 'warning' ? 'warning' : 'info') ?>"><?= $log['level'] ?></span></td><td><?= $log['message'] ?></td><td class="text-end pe-4 small text-muted"><?= $log['time'] ?? '' ?></td></tr><?php endforeach; ?><?php endif; ?></tbody></table></div></div></div></div>
    </div>
</div>
