<?php
/**
 * Custom Features Dashboard
 * @var array $stats
 * @var array $recentActivity
 */
$base = BASE_URL;
?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="fas fa-cubes me-2"></i> Custom Features Dashboard</h4>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card aps-cp-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-map-marked-alt fa-3x text-primary mb-2"></i>
                    <h5 class="fw-bold"><?= $stats['neighborhood_count'] ?? 0 ?></h5>
                    <p class="text-muted mb-0">Neighborhood Analyses</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-calculator fa-3x text-success mb-2"></i>
                    <h5 class="fw-bold"><?= $stats['investment_calculations'] ?? 0 ?></h5>
                    <p class="text-muted mb-0">Investment Calculations</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-3x text-warning mb-2"></i>
                    <h5 class="fw-bold"><?= $stats['total_views'] ?? 0 ?></h5>
                    <p class="text-muted mb-0">Total Feature Views</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-info mb-2"></i>
                    <h5 class="fw-bold"><?= $stats['active_users'] ?? 0 ?></h5>
                    <p class="text-muted mb-0">Active Users</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="<?= $base ?>/admin/custom-features/neighborhood" class="btn btn-primary">
                            <i class="fas fa-map-marked-alt me-2"></i> Neighborhood Analytics
                        </a>
                        <a href="<?= $base ?>/admin/custom-features/investment-calculator" class="btn btn-success">
                            <i class="fas fa-calculator me-2"></i> Investment Calculator
                        </a>
                        <a href="<?= $base ?>/admin/custom-features/stats" class="btn btn-info">
                            <i class="fas fa-chart-bar me-2"></i> View Statistics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentActivity)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No recent activity</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Property</th>
                                        <th>User</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentActivity as $activity): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-<?= $activity['type'] === 'neighborhood' ? 'primary' : 'success' ?>">
                                                <?= ucfirst($activity['type']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($activity['property_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($activity['user_name'] ?? 'Anonymous') ?></td>
                                        <td><?= date('M j, Y H:i', strtotime($activity['created_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>