<?php
/**
 * APS Dream Home - Associate Dashboard
 */

$page_title = $page_title ?? 'Associate Dashboard - APS Dream Home';
$user = $user ?? [];
$recent_activities = $recent_activities ?? [];
$notifications = $notifications ?? [];
?>

<!-- Dashboard Header -->
<div class="dashboard-header bg-gradient-primary text-white py-4 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3 mb-0">Associate Dashboard</h1>
                <p class="mb-0 opacity-75">Welcome back, <?= htmlspecialchars($user['name'] ?? 'Associate') ?>!</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-inline-flex align-items-center">
                    <span class="badge bg-success me-2">Active</span>
                    <span class="me-3">Member since: <?= date('M Y', strtotime($user['join_date'] ?? '2024-01-01')) ?></span>
                    <button class="btn btn-light btn-sm">
                        <i class="fas fa-cog"></i> Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Stats -->
<div class="container mb-4">
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Sales</h6>
                            <h3 class="mb-0"><?= number_format($user['performance']['total_sales'] ?? 0) ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Revenue</h6>
                            <h3 class="mb-0">₹<?= number_format($user['performance']['total_revenue'] ?? 0) ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-rupee-sign fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Commission</h6>
                            <h3 class="mb-0">₹<?= number_format($user['performance']['commission_earned'] ?? 0) ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-coins fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Properties Sold</h6>
                            <h3 class="mb-0"><?= $user['performance']['properties_sold'] ?? 0 ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-home fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container">
    <div class="row">
        <!-- Recent Activities -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Activities</h5>
                    <button class="btn btn-sm btn-outline-primary">View All</button>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_activities)): ?>
                        <div class="activity-timeline">
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item d-flex mb-3">
                                    <div class="activity-icon me-3">
                                        <?php if ($activity['type'] === 'sale'): ?>
                                            <i class="fas fa-shopping-cart text-success"></i>
                                        <?php elseif ($activity['type'] === 'inquiry'): ?>
                                            <i class="fas fa-question-circle text-info"></i>
                                        <?php elseif ($activity['type'] === 'commission'): ?>
                                            <i class="fas fa-coins text-warning"></i>
                                        <?php else: ?>
                                            <i class="fas fa-circle text-secondary"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-content flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <?php if ($activity['type'] === 'sale'): ?>
                                                    <strong>Sale Completed:</strong> <?= htmlspecialchars($activity['property']) ?>
                                                    <div class="text-muted">Amount: ₹<?= number_format($activity['amount']) ?></div>
                                                <?php elseif ($activity['type'] === 'inquiry'): ?>
                                                    <strong>New Inquiry:</strong> <?= htmlspecialchars($activity['property']) ?>
                                                    <div class="text-muted">Client: <?= htmlspecialchars($activity['client']) ?></div>
                                                <?php elseif ($activity['type'] === 'commission'): ?>
                                                    <strong>Commission Earned:</strong> ₹<?= number_format($activity['amount']) ?>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted"><?= date('M d, Y', strtotime($activity['date'])) ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No recent activities to display.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Notifications</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($notifications)): ?>
                        <div class="notification-list">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item d-flex mb-3 p-2 border rounded">
                                    <div class="notification-icon me-2">
                                        <?php if ($notification['type'] === 'success'): ?>
                                            <i class="fas fa-check-circle text-success"></i>
                                        <?php elseif ($notification['type'] === 'info'): ?>
                                            <i class="fas fa-info-circle text-info"></i>
                                        <?php elseif ($notification['type'] === 'warning'): ?>
                                            <i class="fas fa-exclamation-triangle text-warning"></i>
                                        <?php else: ?>
                                            <i class="fas fa-bell text-secondary"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notification-content flex-grow-1">
                                        <div class="small"><?= htmlspecialchars($notification['message']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($notification['time']) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No new notifications.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add New Property
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-users me-2"></i>View Clients
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-chart-bar me-2"></i>View Reports
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-calendar me-2"></i>Schedule Meeting
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.activity-timeline {
    position: relative;
}

.activity-item {
    position: relative;
    padding-left: 20px;
}

.activity-item::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 30px;
    bottom: -20px;
    width: 2px;
    background: #e9ecef;
}

.activity-item:last-child::before {
    display: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.notification-item:hover {
    background: #f8f9fa;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: box-shadow 0.15s ease-in-out;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
</style>
