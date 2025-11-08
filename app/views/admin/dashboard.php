<?php
/**
 * Admin Dashboard Template
 * Main dashboard for administrators
 */

?>

<!-- Admin Dashboard Header -->
<section class="admin-header py-4 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="mb-0">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Admin Dashboard
                </h1>
                <p class="mb-0 opacity-75">Welcome back! Here's what's happening with your system.</p>
            </div>
            <div class="col-lg-6 text-lg-end">
                <div class="d-flex align-items-center justify-content-lg-end gap-3">
                    <div class="text-center">
                        <div class="fw-bold fs-4"><?php echo date('d'); ?></div>
                        <div class="small opacity-75"><?php echo date('M Y'); ?></div>
                    </div>
                    <div class="vr"></div>
                    <div class="text-center">
                        <div class="fw-bold fs-4" id="currentTime"><?php echo date('H:i'); ?></div>
                        <div class="small opacity-75">Time</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dashboard Stats Cards -->
<section class="dashboard-stats py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Total Properties -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-home fa-2x"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($stats['total_properties'] ?? 0); ?></div>
                        <div class="stat-label">Total Properties</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up text-success me-1"></i>
                            <small class="text-muted">+12% from last month</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Featured Properties -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-star fa-2x"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($stats['featured_properties'] ?? 0); ?></div>
                        <div class="stat-label">Featured Properties</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up text-success me-1"></i>
                            <small class="text-muted">+8% from last month</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Users -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-info">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($stats['total_users'] ?? 0); ?></div>
                        <div class="stat-label">Total Users</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up text-success me-1"></i>
                            <small class="text-muted">+15% from last month</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Agents -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-user-tie fa-2x"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($stats['total_agents'] ?? 0); ?></div>
                        <div class="stat-label">Active Agents</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up text-success me-1"></i>
                            <small class="text-muted">+5% from last month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Dashboard Content -->
<section class="dashboard-content py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Recent Activities -->
            <div class="col-lg-8">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2"></i>
                            Recent Activities
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($recent_activities) && !empty($recent_activities)): ?>
                            <div class="activity-timeline">
                                <?php foreach ($recent_activities as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon <?php echo $activity['type'] === 'property_added' ? 'bg-success' : ($activity['type'] === 'user_registered' ? 'bg-info' : ($activity['type'] === 'property_updated' ? 'bg-warning' : 'bg-primary')); ?>">
                                            <i class="fas fa-<?php
                                                echo $activity['type'] === 'property_added' ? 'home' :
                                                     ($activity['type'] === 'user_registered' ? 'user-plus' :
                                                     ($activity['type'] === 'property_updated' ? 'edit' :
                                                     ($activity['type'] === 'inquiry_received' ? 'envelope' : 'dollar-sign')));
                                            ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-message"><?php echo htmlspecialchars($activity['message']); ?></div>
                                            <div class="activity-time">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo htmlspecialchars($activity['time']); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Recent Activities</h5>
                                <p class="text-muted">Activities will appear here as users interact with the system.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="col-lg-4">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-server me-2"></i>
                            System Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="system-status">
                            <?php if (isset($system_status) && !empty($system_status)): ?>
                                <?php foreach ($system_status as $key => $value): ?>
                                    <div class="status-item">
                                        <div class="status-label">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            <?php echo ucwords(str_replace('_', ' ', $key)); ?>
                                        </div>
                                        <div class="status-value"><?php echo htmlspecialchars($value); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-3">
                                    <i class="fas fa-server fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">System status information not available.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="dashboard-card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="<?php echo BASE_URL; ?>admin/properties" class="btn btn-primary btn-sm w-100 mb-2">
                                <i class="fas fa-home me-2"></i>Manage Properties
                            </a>
                            <a href="<?php echo BASE_URL; ?>admin/users" class="btn btn-info btn-sm w-100 mb-2">
                                <i class="fas fa-users me-2"></i>Manage Users
                            </a>
                            <a href="<?php echo BASE_URL; ?>admin/settings" class="btn btn-warning btn-sm w-100 mb-2">
                                <i class="fas fa-cog me-2"></i>System Settings
                            </a>
                            <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-secondary btn-sm w-100" target="_blank">
                                <i class="fas fa-external-link-alt me-2"></i>View Website
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Admin Navigation -->
<section class="admin-navigation py-4 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="admin-nav">
                    <h6 class="mb-3">Quick Navigation</h6>
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <a href="<?php echo BASE_URL; ?>admin/properties" class="nav-card">
                                <div class="nav-icon bg-primary">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="nav-content">
                                    <h6>Properties</h6>
                                    <p class="small text-muted mb-0">Manage property listings</p>
                                </div>
                            </a>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <a href="<?php echo BASE_URL; ?>admin/users" class="nav-card">
                                <div class="nav-icon bg-info">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="nav-content">
                                    <h6>Users</h6>
                                    <p class="small text-muted mb-0">Manage user accounts</p>
                                </div>
                            </a>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <a href="<?php echo BASE_URL; ?>admin/settings" class="nav-card">
                                <div class="nav-icon bg-warning">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <div class="nav-content">
                                    <h6>Settings</h6>
                                    <p class="small text-muted mb-0">System configuration</p>
                                </div>
                            </a>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <a href="<?php echo BASE_URL; ?>admin/analytics" class="nav-card">
                                <div class="nav-icon bg-success">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <div class="nav-content">
                                    <h6>Analytics</h6>
                                    <p class="small text-muted mb-0">View reports & insights</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Update current time every minute
function updateTime() {
    const now = new Date();
    const timeString = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
    document.getElementById('currentTime').textContent = timeString;
}

setInterval(updateTime, 60000);
updateTime(); // Initial call
</script>
