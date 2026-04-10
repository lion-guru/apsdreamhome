<?php

/**
 * Associate Dashboard View
 * For Associate role users
 */

$dashboardData = $dashboardData ?? [];
$role = $dashboardData['role'] ?? 'associate';
$title = $dashboardData['title'] ?? 'Associate Dashboard';
$widgets = $dashboardData['widgets'] ?? [];
$recentActivities = $dashboardData['recent_activities'] ?? [];
$analytics = $dashboardData['analytics'] ?? [];
$quickActions = $dashboardData['quick_actions'] ?? [];
?>

<style>
    .dashboard-header {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
    }

    .widget-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        height: 100%;
    }

    .widget-card:hover {
        transform: translateY(-5px);
    }

    .widget-icon {
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    .widget-count {
        font-size: 2.5rem;
        font-weight: bold;
        color: #333;
    }

    .quick-action-btn {
        border-radius: 25px;
        padding: 0.5rem 1.5rem;
        margin: 0.25rem;
        transition: all 0.3s ease;
    }

    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .activity-item {
        border-left: 3px solid #f5576c;
        padding-left: 1rem;
        margin-bottom: 0.5rem;
    }

    .performance-card {
        background: linear-gradient(45deg, #fa709a 0%, #fee140 100%);
        color: white;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }

    .commission-badge {
        background: linear-gradient(45deg, #11998e 0%, #38ef7d 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: bold;
    }
</style>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1><i class="fas fa-handshake me-3"></i><?php echo htmlspecialchars($title); ?></h1>
                <p class="mb-0">Welcome back, Associate! Track your performance and manage your clients.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex justify-content-md-end gap-2">
                    <span class="badge bg-light text-dark">
                        <i class="fas fa-user-tie me-1"></i>
                        <?php echo ucfirst($role); ?>
                    </span>
                    <span class="badge bg-success">
                        <i class="fas fa-chart-line me-1"></i>
                        Active
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Performance Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-trophy me-2"></i>Performance Overview</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="performance-card text-center">
                                <h6>This Month</h6>
                                <h3>₹<?php echo number_format(50000, 2); ?></h3>
                                <small>Commission Earned</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="performance-card text-center">
                                <h6>Conversion Rate</h6>
                                <h3>85%</h3>
                                <small>Lead to Client</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="performance-card text-center">
                                <h6>Properties Sold</h6>
                                <h3>12</h3>
                                <small>This Month</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="performance-card text-center">
                                <h6>Client Rating</h6>
                                <h3>4.8</h3>
                                <small>Average</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    <div class="d-flex flex-wrap">
                        <?php if (!empty($quickActions)): ?>
                            <?php foreach ($quickActions as $action => $url): ?>
                                <a href="<?php echo BASE_URL . htmlspecialchars($url); ?>" class="btn btn-success quick-action-btn">
                                    <i class="fas fa-plus me-1"></i>
                                    <?php echo ucwords(str_replace('_', ' ', $action)); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Widgets -->
    <div class="row mb-4">
        <?php if (!empty($widgets)): ?>
            <?php foreach ($widgets as $key => $widget): ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card widget-card">
                        <div class="card-body text-center">
                            <div class="widget-icon text-success">
                                <i class="fas fa-<?php echo htmlspecialchars($widget['icon'] ?? 'cube'); ?>"></i>
                            </div>
                            <h6 class="card-title"><?php echo htmlspecialchars($widget['title'] ?? 'Widget'); ?></h6>
                            <div class="widget-count"><?php echo htmlspecialchars($widget['count'] ?? '0'); ?></div>
                            <?php if ($key === 'commissions'): ?>
                                <span class="commission-badge">+15% This Month</span>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL . htmlspecialchars($widget['link'] ?? '#'); ?>" class="btn btn-sm btn-outline-success mt-2">
                                View Details <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Analytics and Activities -->
    <div class="row">
        <!-- Analytics Section -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-chart-pie me-2"></i>Sales Analytics</h5>
                    <?php if (!empty($analytics)): ?>
                        <div class="row">
                            <?php foreach ($analytics as $key => $data): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="performance-card">
                                        <h6><?php echo ucwords(str_replace('_', ' ', $key)); ?></h6>
                                        <p class="mb-0">Analytics data available</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No analytics data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-history me-2"></i>Recent Activities</h5>
                    <?php if (!empty($recentActivities)): ?>
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="activity-item">
                                <small class="text-muted"><?php echo date('M d, H:i', strtotime($activity['created_at'] ?? 'now')); ?></small>
                                <p class="mb-1"><?php echo htmlspecialchars($activity['action'] ?? 'Activity'); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No recent activities</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Management -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users me-2"></i>Lead Management</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="text-primary">
                                    <i class="fas fa-phone fa-2x"></i>
                                </div>
                                <h6>New Leads</h6>
                                <span class="badge bg-primary">5 Today</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="text-warning">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                                <h6>Pending</h6>
                                <span class="badge bg-warning">12</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="text-success">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                                <h6>Converted</h6>
                                <span class="badge bg-success">8 This Week</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="text-info">
                                    <i class="fas fa-calendar-check fa-2x"></i>
                                </div>
                                <h6>Follow-ups</h6>
                                <span class="badge bg-info">3 Today</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>