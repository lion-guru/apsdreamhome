<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .admin-sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .admin-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        .admin-card:hover {
            transform: translateY(-5px);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
        }
        .activity-item {
            border-left: 4px solid #667eea;
            padding-left: 20px;
            margin-bottom: 20px;
        }
        .health-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .health-good { background-color: #28a745; }
        .health-warning { background-color: #ffc107; }
        .health-danger { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 admin-sidebar">
                <div class="p-4">
                    <h4 class="text-white mb-4">
                        <i class="fas fa-cog me-2"></i>Admin Panel
                    </h4>
                    <nav class="nav nav-pills flex-column">
                        <a href="/admin" class="nav-link text-white <?php echo basename($_SERVER['REQUEST_URI']) == 'admin' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a href="/admin/users" class="nav-link text-white">
                            <i class="fas fa-users me-2"></i>Users
                        </a>
                        <a href="/admin/properties" class="nav-link text-white">
                            <i class="fas fa-building me-2"></i>Properties
                        </a>
                        <a href="/admin/leads" class="nav-link text-white">
                            <i class="fas fa-user-tie me-2"></i>Leads
                        </a>
                        <a href="/admin/reports" class="nav-link text-white">
                            <i class="fas fa-chart-bar me-2"></i>Reports
                        </a>
                        <a href="/admin/settings" class="nav-link text-white">
                            <i class="fas fa-cogs me-2"></i>Settings
                        </a>
                        <a href="/admin/database" class="nav-link text-white">
                            <i class="fas fa-database me-2"></i>Database
                        </a>
                        <a href="/admin/logs" class="nav-link text-white">
                            <i class="fas fa-file-alt me-2"></i>Logs
                        </a>
                        <hr class="my-3 bg-white">
                        <a href="/" class="nav-link text-white">
                            <i class="fas fa-home me-2"></i>Back to Site
                        </a>
                        <a href="/logout" class="nav-link text-white">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>
                    <div>
                        <button class="btn btn-outline-primary me-2" onclick="clearCache()">
                            <i class="fas fa-broom me-1"></i>Clear Cache
                        </button>
                        <button class="btn btn-success" onclick="createBackup()">
                            <i class="fas fa-download me-1"></i>Create Backup
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stat-card text-center">
                            <i class="fas fa-users fa-2x mb-3"></i>
                            <h3><?php echo number_format($data['stats']['total_users']); ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card text-center">
                            <i class="fas fa-building fa-2x mb-3"></i>
                            <h3><?php echo number_format($data['stats']['total_properties']); ?></h3>
                            <p>Total Properties</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card text-center">
                            <i class="fas fa-user-tie fa-2x mb-3"></i>
                            <h3><?php echo number_format($data['stats']['total_leads']); ?></h3>
                            <p>Total Leads</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card text-center">
                            <i class="fas fa-dollar-sign fa-2x mb-3"></i>
                            <h3>₹<?php echo number_format($data['stats']['monthly_revenue']); ?></h3>
                            <p>Monthly Revenue</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Activities -->
                    <div class="col-md-8 mb-4">
                        <div class="card admin-card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activities</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($data['recentActivities'])): ?>
                                    <?php foreach ($data['recentActivities'] as $activity): ?>
                                        <div class="activity-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($activity['title']); ?></h6>
                                                    <p class="text-muted mb-1"><?php echo htmlspecialchars($activity['description']); ?></p>
                                                    <small class="text-muted">
                                                        <?php if (!empty($activity['lead_name'])): ?>
                                                            Lead: <?php echo htmlspecialchars($activity['lead_name']); ?>
                                                        <?php endif; ?>
                                                        <?php if (!empty($activity['user_name'])): ?>
                                                            by <?php echo htmlspecialchars($activity['user_name']); ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo date('M j, H:i', strtotime($activity['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center py-4">No recent activities found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- System Health -->
                    <div class="col-md-4 mb-4">
                        <div class="card admin-card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-heartbeat me-2"></i>System Health</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Database</span>
                                        <span class="health-indicator health-<?php echo $data['systemHealth']['database'] ? 'good' : 'danger'; ?>"></span>
                                    </div>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-<?php echo $data['systemHealth']['database'] ? 'success' : 'danger'; ?>"
                                             style="width: <?php echo $data['systemHealth']['database'] ? '100%' : '0%'; ?>"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Disk Space</span>
                                        <small class="text-muted"><?php echo round($data['systemHealth']['disk_space']['percentage']); ?>%</small>
                                    </div>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-warning" style="width: <?php echo $data['systemHealth']['disk_space']['percentage']; ?>%"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Memory Usage</span>
                                        <small class="text-muted">
                                            <?php echo isset($data['systemHealth']['memory_usage']['current']) ?
                                                round($data['systemHealth']['memory_usage']['current'] / 1024 / 1024, 1) . 'MB' : 'N/A'; ?>
                                        </small>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Last Backup</span>
                                        <small class="text-muted">
                                            <?php echo $data['systemHealth']['last_backup'] ?
                                                date('M j', strtotime($data['systemHealth']['last_backup'])) : 'Never'; ?>
                                        </small>
                                    </div>
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
        function clearCache() {
            if (confirm('Are you sure you want to clear the cache?')) {
                fetch('/admin/clear-cache', { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Cache cleared successfully!');
                            location.reload();
                        } else {
                            alert('Failed to clear cache: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error clearing cache');
                    });
            }
        }

        function createBackup() {
            if (confirm('Are you sure you want to create a database backup? This may take some time.')) {
                fetch('/admin/backup', { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Backup created successfully!');
                            location.reload();
                        } else {
                            alert('Failed to create backup: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error creating backup');
                    });
            }
        }
    </script>
</body>
</html>
