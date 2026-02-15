<?php

/**
 * Admin Dashboard Template
 * Main dashboard for administrators
 */

?>

<!-- Admin Dashboard Header -->
<header class="admin-header py-4 bg-primary text-white">
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
</header>

<!-- Dashboard Stats Cards -->
<section class="dashboard-stats py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Total Bookings -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($stats['total_bookings'] ?? 0); ?></div>
                        <div class="stat-label">Total Bookings</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up text-success me-1"></i>
                            <small class="text-muted">+15 this month</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Sales -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-rupee-sign fa-2x"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">₹<?php echo number_format($stats['total_sales'] ?? 0); ?></div>
                        <div class="stat-label">Total Sales</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up text-success me-1"></i>
                            <small class="text-muted">+22% from last month</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Commission Paid -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-info">
                        <i class="fas fa-hand-holding-usd fa-2x"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">₹<?php echo number_format($stats['total_commission'] ?? 0); ?></div>
                        <div class="stat-label">Commission Paid</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up text-success me-1"></i>
                            <small class="text-muted">+8% this quarter</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Expenses -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-danger">
                        <i class="fas fa-receipt fa-2x"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">₹<?php echo number_format($stats['total_expenses'] ?? 0); ?></div>
                        <div class="stat-label">Total Expenses</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-down text-success me-1"></i>
                            <small class="text-muted">-5% this month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($ai_agents_status) && !empty($ai_agents_status)): ?>
        <!-- AI Agents Status Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0"><i class="fas fa-robot me-2"></i>AI Agents Status</h5>
                        <span class="badge bg-success">All Systems Operational</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Agent Name</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Last Activity</th>
                                        <th>Current Mood</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ai_agents_status as $agent): ?>
                                    <tr>
                                        <td class="fw-bold">
                                            <i class="fas fa-user-shield text-primary me-2"></i>
                                            <?php echo htmlspecialchars($agent['name']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($agent['type']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $agent['status'] === 'Online' ? 'success' : 'warning'; ?>-soft text-<?php echo $agent['status'] === 'Online' ? 'success' : 'warning'; ?>">
                                                <i class="fas fa-circle me-1 small"></i>
                                                <?php echo htmlspecialchars($agent['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, H:i', strtotime($agent['last_activity'])); ?></td>
                                        <td>
                                            <span class="badge bg-info-soft text-info">
                                                <?php echo htmlspecialchars($agent['mood']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="alert('Chat with <?php echo $agent['name']; ?> coming soon!')">
                                                <i class="fas fa-comment"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Main Dashboard Content -->
<main class="dashboard-content py-5">
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
                                                                echo $activity['type'] === 'property_added' ? 'home' : ($activity['type'] === 'user_registered' ? 'user-plus' : ($activity['type'] === 'property_updated' ? 'edit' : ($activity['type'] === 'inquiry_received' ? 'envelope' : 'dollar-sign')));
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
                                <i class="fas fa-building me-2"></i>Manage Properties
                            </a>
                            <a href="<?php echo BASE_URL; ?>admin/emi" class="btn btn-success btn-sm w-100 mb-2">
                                <i class="fas fa-money-bill-wave me-2"></i>EMI Management
                            </a>
                            <a href="<?php echo BASE_URL; ?>admin/visits" class="btn btn-info btn-sm w-100 mb-2">
                                <i class="fas fa-calendar-check me-2"></i>Visit Management
                            </a>
                            <a href="<?php echo BASE_URL; ?>admin/users" class="btn btn-warning btn-sm w-100 mb-2">
                                <i class="fas fa-users me-2"></i>User Management
                            </a>
                            <a href="<?php echo BASE_URL; ?>admin/leads" class="btn btn-secondary btn-sm w-100 mb-2">
                                <i class="fas fa-user-tie me-2"></i>Lead Management
                            </a>
                            <a href="<?php echo BASE_URL; ?>admin/reports" class="btn btn-dark btn-sm w-100 mb-2">
                                <i class="fas fa-chart-bar me-2"></i>View Reports
                            </a>
                            <a href="<?php echo BASE_URL; ?>admin/settings" class="btn btn-outline-dark btn-sm w-100 mb-2">
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

                            <div class="col-lg-3 col-md-6">
                                <a href="<?php echo BASE_URL; ?>admin/emi" class="nav-card">
                                    <div class="nav-icon bg-danger">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="nav-content">
                                        <h6>EMI Plans</h6>
                                        <p class="small text-muted mb-0">Track payments</p>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <a href="<?php echo BASE_URL; ?>admin/visits" class="nav-card">
                                    <div class="nav-icon bg-secondary">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="nav-content">
                                        <h6>Site Visits</h6>
                                        <p class="small text-muted mb-0">Manage schedule</p>
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
