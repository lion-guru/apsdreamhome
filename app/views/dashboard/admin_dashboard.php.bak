<?php

/**
 * Admin Dashboard View
 * For Super Admin and Admin roles
 */

$dashboardData = $dashboardData ?? [];
$role = $dashboardData['role'] ?? 'admin';
$title = $dashboardData['title'] ?? 'Admin Dashboard';
$widgets = $dashboardData['widgets'] ?? [];
$recentActivities = $dashboardData['recent_activities'] ?? [];
$analytics = $dashboardData['analytics'] ?? [];
$quickActions = $dashboardData['quick_actions'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            border-left: 3px solid #667eea;
            padding-left: 1rem;
            margin-bottom: 0.5rem;
        }

        .stats-card {
            background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .bg-gradient-ai {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .ai-gradient {
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .ai-enhanced {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border: 1px solid rgba(102, 126, 234, 0.2);
        }

        .hover-module {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .hover-module:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: #667eea !important;
        }

        .ai-indicator {
            margin-top: 0.5rem;
        }
    </style>
</head>

<body>
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="fas fa-tachometer-alt me-3"></i><?php echo htmlspecialchars($title); ?></h1>
                    <p class="mb-0">Welcome back, Admin! Here's your system overview.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex justify-content-md-end gap-2">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-user-shield me-1"></i>
                            <?php echo ucfirst($role); ?>
                        </span>
                        <span class="badge bg-success">
                            <i class="fas fa-circle me-1"></i>
                            System Online
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- AI Mode Controls -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-ai">
                    <div class="card-body">
                        <h5 class="card-title text-white"><i class="fas fa-brain me-2"></i>AI Mode</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-light" onclick="enableAIMode()">
                                <i class="fas fa-robot me-1"></i>
                                Enable AI Mode
                            </button>
                            <button class="btn btn-outline-light" onclick="refreshAIData()">
                                <i class="fas fa-sync me-1"></i>
                                Refresh AI Data
                            </button>
                            <button class="btn btn-outline-light" onclick="showAIInsights()">
                                <i class="fas fa-chart-line me-1"></i>
                                AI Insights
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enterprise Modules -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-th-large me-2"></i>Enterprise Modules</h5>
                        <div class="row">
                            <div class="col-md-2 col-sm-4 col-6 mb-3">
                                <div class="text-center p-3 border rounded hover-module">
                                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                    <h6>User Management</h6>
                                    <small>Users, Roles, Permissions</small>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-6 mb-3">
                                <div class="text-center p-3 border rounded hover-module">
                                    <i class="fas fa-users-cog fa-2x text-success mb-2"></i>
                                    <h6>Team Management</h6>
                                    <small>Staff, Departments, Performance</small>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-6 mb-3">
                                <div class="text-center p-3 border rounded hover-module">
                                    <i class="fas fa-rupee-sign fa-2x text-warning mb-2"></i>
                                    <h6>Financial</h6>
                                    <small>Transactions, Commissions, Tax</small>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-6 mb-3">
                                <div class="text-center p-3 border rounded hover-module">
                                    <i class="fas fa-bullhorn fa-2x text-info mb-2"></i>
                                    <h6>Marketing</h6>
                                    <small>Campaigns, Leads, Social Media</small>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-6 mb-3">
                                <div class="text-center p-3 border rounded hover-module">
                                    <i class="fas fa-cogs fa-2x text-danger mb-2"></i>
                                    <h6>System</h6>
                                    <small>Settings, Backup, Security</small>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-6 mb-3">
                                <div class="text-center p-3 border rounded hover-module">
                                    <i class="fas fa-chart-bar fa-2x text-secondary mb-2"></i>
                                    <h6>Analytics</h6>
                                    <small>Reports, Insights, Metrics</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Stats Grid with AI -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card widget-card ai-enhanced">
                    <div class="card-body text-center">
                        <div class="widget-icon text-primary ai-gradient">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <h6 class="card-title">Network Size</h6>
                        <div class="widget-count"><?php echo number_format($dashboardData['network_size'] ?? 1250); ?></div>
                        <div class="ai-indicator">
                            <small class="text-success"><i class="fas fa-arrow-up"></i> +12.5%</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card widget-card ai-enhanced">
                    <div class="card-body text-center">
                        <div class="widget-icon text-success ai-gradient">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h6 class="card-title">Platform Revenue</h6>
                        <div class="widget-count">₹<?php echo number_format($dashboardData['revenue'] ?? 2500000); ?></div>
                        <div class="ai-indicator">
                            <small class="text-success"><i class="fas fa-arrow-up"></i> +8.3%</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card widget-card ai-enhanced">
                    <div class="card-body text-center">
                        <div class="widget-icon text-warning ai-gradient">
                            <i class="fas fa-users"></i>
                        </div>
                        <h6 class="card-title">Active Users</h6>
                        <div class="widget-count"><?php echo number_format($dashboardData['active_users'] ?? 850); ?></div>
                        <div class="ai-indicator">
                            <small class="text-success"><i class="fas fa-arrow-up"></i> +5.2%</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card widget-card ai-enhanced">
                    <div class="card-body text-center">
                        <div class="widget-icon text-info ai-gradient">
                            <i class="fas fa-home"></i>
                        </div>
                        <h6 class="card-title">Properties</h6>
                        <div class="widget-count"><?php echo number_format($dashboardData['properties'] ?? 320); ?></div>
                        <div class="ai-indicator">
                            <small class="text-success"><i class="fas fa-arrow-up"></i> +3.7%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics and Activities -->
        <div class="row">
            <!-- Analytics Section -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-chart-line me-2"></i>Analytics Overview</h5>
                        <?php if (!empty($analytics)): ?>
                            <div class="row">
                                <?php foreach ($analytics as $key => $data): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="stats-card">
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

        <!-- System Health -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-heartbeat me-2"></i>System Health</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="text-success">
                                        <i class="fas fa-database fa-2x"></i>
                                    </div>
                                    <h6>Database</h6>
                                    <span class="badge bg-success">Healthy</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="text-success">
                                        <i class="fas fa-server fa-2x"></i>
                                    </div>
                                    <h6>Server</h6>
                                    <span class="badge bg-success">Online</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="text-success">
                                        <i class="fas fa-shield-alt fa-2x"></i>
                                    </div>
                                    <h6>Security</h6>
                                    <span class="badge bg-success">Secured</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="text-success">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                    <h6>Users</h6>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh dashboard every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>

</html>