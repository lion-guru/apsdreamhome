<?php $pageTitle = $pageTitle ?? ($page_title ?? 'User Reports'); ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-file-alt me-2"></i><?= ($pageTitle ?? ($page_title ?? 'User Reports')) ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>admin/reports">Reports</a></li>
                    <li class="breadcrumb-item active">Users</li>
                </ul>
            </div>
        </div>
    </div>
    <?php $userStats = $users ?? $user_stats ?? []; ?>
    <?php if (!empty($userStats)): ?>
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h5><?= number_format($userStats['total_users'] ?? 0) ?></h5>
                    <small class="text-muted">Total Users</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                    <h5><?= number_format($userStats['active_users'] ?? 0) ?></h5>
                    <small class="text-muted">Active Users</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-user-plus fa-2x text-info mb-2"></i>
                    <h5><?= number_format($userStats['new_users'] ?? 0) ?></h5>
                    <small class="text-muted">New Users</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-retweet fa-2x text-warning mb-2"></i>
                    <h5><?= ($userStats['user_retention'] ?? 0) ?>%</h5>
                    <small class="text-muted">Retention</small>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-arrow-trend-up me-2"></i>User Growth</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Month</th><th>Registrations</th><th>Active</th></tr></thead>
                            <tbody>
                                <?php foreach (($user_growth ?? $users['user_growth'] ?? []) as $ug): ?>
                                <tr>
                                    <td><?= ($ug['month'] ?? '') ?></td>
                                    <td><?= ($ug['registrations'] ?? 0) ?></td>
                                    <td><?= ($ug['active'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($user_growth ?? $users['user_growth'] ?? [])): ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">No growth data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-activity me-2"></i>User Activity</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Activity</th><th>Count</th></tr></thead>
                            <tbody>
                                <?php foreach (($user_activity ?? $users['user_activity'] ?? []) as $ua): ?>
                                <tr>
                                    <td><?= ($ua['activity'] ?? '') ?></td>
                                    <td><?= number_format($ua['count'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($user_activity ?? $users['user_activity'] ?? [])): ?>
                                <tr><td colspan="2" class="text-center text-muted py-3">No activity data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-users fa-4x text-muted mb-3"></i>
            <h5>No User Report Data</h5>
            <p class="text-muted mb-0">User reports will appear once data is available.</p>
        </div>
    </div>
    <?php endif; ?>
</div>
