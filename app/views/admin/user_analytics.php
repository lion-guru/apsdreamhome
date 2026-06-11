<?php $pageTitle = $pageTitle ?? ($page_title ?? 'User Analytics'); ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-users me-2"></i><?= ($pageTitle ?? ($page_title ?? 'User Analytics')) ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">User Analytics</li>
                </ul>
            </div>
        </div>
    </div>
    <?php $userData = $user_stats ?? $user_data ?? []; ?>
    <?php if (!empty($userData)): ?>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-arrow-trend-up me-2"></i>Registration Trends</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Date</th><th>Registrations</th></tr></thead>
                            <tbody>
                                <?php foreach (($userData['registration_trends'] ?? []) as $row): ?>
                                <tr>
                                    <td><?= ($row['date'] ?? '') ?></td>
                                    <td><?= ($row['count'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($userData['registration_trends'] ?? [])): ?>
                                <tr><td colspan="2" class="text-center text-muted py-3">No registration data</td></tr>
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
                    <h6 class="mb-0"><i class="fas fa-activity me-2"></i>Activity Analysis</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php $activity = $userData['activity_analysis'] ?? []; ?>
                    <div class="d-flex justify-content-between mb-2"><span>Daily Active</span><strong><?= $activity['daily_active_users'] ?? 'N/A' ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Weekly Active</span><strong><?= $activity['weekly_active_users'] ?? 'N/A' ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Monthly Active</span><strong><?= $activity['monthly_active_users'] ?? 'N/A' ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>7-Day Retention</span><strong><?= $activity['user_retention_7day'] ?? 'N/A' ?></strong></div>
                    <div class="d-flex justify-content-between"><span>30-Day Retention</span><strong><?= $activity['user_retention_30day'] ?? 'N/A' ?></strong></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-globe me-2"></i>Geographic Distribution</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>State</th><th>Users</th></tr></thead>
                            <tbody>
                                <?php foreach (($userData['geographic_distribution'] ?? []) as $geo): ?>
                                <tr>
                                    <td><?= ($geo['state'] ?? '') ?></td>
                                    <td><?= ($geo['user_count'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($userData['geographic_distribution'] ?? [])): ?>
                                <tr><td colspan="2" class="text-center text-muted py-3">No geographic data</td></tr>
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
                    <h6 class="mb-0"><i class="fas fa-user-clock me-2"></i>Retention Metrics</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php $retention = $userData['retention_metrics'] ?? []; ?>
                    <div class="d-flex justify-content-between mb-2"><span>Day 1 Retention</span><strong><?= $retention['day_1_retention'] ?? 'N/A' ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Day 7 Retention</span><strong><?= $retention['day_7_retention'] ?? 'N/A' ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Day 30 Retention</span><strong><?= $retention['day_30_retention'] ?? 'N/A' ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Avg Lifetime Value</span><strong><?= $retention['avg_lifetime_value'] ?? 'N/A' ?></strong></div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-users fa-4x text-muted mb-3"></i>
            <h5>No User Data</h5>
            <p class="text-muted mb-0">User analytics will appear once data is available.</p>
        </div>
    </div>
    <?php endif; ?>
</div>
