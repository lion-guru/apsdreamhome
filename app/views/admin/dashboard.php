<?php

/**
 * APS Dream Home - Admin Dashboard
 * Main admin interface
 */

$page_title = $page_title ?? 'Admin Dashboard - APS Dream Home';
$stats = $stats ?? [];
$recent_projects = $recent_projects ?? [];
$recent_applications = $recent_applications ?? [];
$pending_tasks = $pending_tasks ?? [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>/public/assets/css/style.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/public/assets/css/admin.css" rel="stylesheet">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.3);
            --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        }

        body {
            background: #f0f2f5;
            font-family: 'Inter', sans-serif;
        }

        .admin-sidebar {
            background: #1a1c23;
            min-height: 100vh;
            padding: 1.5rem;
            position: fixed;
            width: inherit;
        }

        .admin-sidebar .nav-link {
            color: rgba(255, 255, 255, 0.7);
            padding: 0.8rem 1rem;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .main-content {
            padding: 2rem;
            margin-left: 16.666667%;
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--glass-shadow);
            padding: 1.5rem;
            transition: transform 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
        }

        .admin-header {
            background: var(--primary-gradient);
            border-radius: 24px;
            padding: 2.5rem;
            color: white;
            margin-bottom: 2rem;
            box-shadow: 0 10px 20px rgba(118, 75, 162, 0.2);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .recent-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: background 0.2s;
        }

        .recent-item:last-child {
            border-bottom: none;
        }

        .recent-item:hover {
            background: rgba(0, 0, 0, 0.02);
        }

        .activity-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 1rem;
        }

        .bg-gradient-blue {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        }

        .bg-gradient-green {
            background: linear-gradient(135deg, #064e3b 0%, #10b981 100%);
        }

        .bg-gradient-orange {
            background: linear-gradient(135deg, #7c2d12 0%, #f97316 100%);
        }

        .bg-gradient-purple {
            background: linear-gradient(135deg, #4c1d95 0%, #8b5cf6 100%);
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 position-fixed">
                <div class="admin-sidebar">
                    <div class="text-center mb-5">
                        <h4 class="text-white fw-bold">APS <span class="text-primary text-opacity-75">Dream</span></h4>
                        <small class="text-muted">ADMIN PANEL</small>
                    </div>
                    <nav class="nav flex-column">
                        <a href="/admin/dashboard" class="nav-link active"><i class="fas fa-grid-2 me-2"></i> Dashboard</a>
                        <a href="/admin/properties" class="nav-link"><i class="fas fa-home me-2"></i> Properties</a>
                        <a href="/admin/users" class="nav-link"><i class="fas fa-users me-2"></i> User Network</a>
                        <a href="/admin/reports" class="nav-link"><i class="fas fa-chart-pie me-2"></i> Analytics</a>
                        <a href="/admin/settings" class="nav-link"><i class="fas fa-sliders me-2"></i> Settings</a>
                        <hr class="border-secondary opacity-25">
                        <a href="/" class="nav-link"><i class="fas fa-external-link-alt me-2"></i> Public View</a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="admin-header animate-fade-in">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="fw-bold mb-1">Administrative Intelligence</h1>
                            <p class="mb-0 opacity-75">Visualizing property metrics and user growth in real-time.</p>
                        </div>
                        <div class="text-end">
                            <div class="d-flex align-items-center bg-white bg-opacity-10 rounded-pill px-3 py-2 border border-white border-opacity-10">
                                <img src="https://ui-avatars.com/api/?name=Admin&background=random" class="rounded-circle me-2" width="32">
                                <span class="fw-medium">Super Admin</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="glass-card">
                            <div class="stat-icon bg-gradient-blue"><i class="fas fa-users"></i></div>
                            <h2 class="fw-bold mb-0"><?php echo number_format($stats['total_users'] ?? 150); ?></h2>
                            <p class="text-muted small mb-0">Total Network Size</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="glass-card">
                            <div class="stat-icon bg-gradient-green"><i class="fas fa-building"></i></div>
                            <h2 class="fw-bold mb-0"><?php echo number_format($stats['total_properties'] ?? 85); ?></h2>
                            <p class="text-muted small mb-0">Managed Assets</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="glass-card">
                            <div class="stat-icon bg-gradient-orange"><i class="fas fa-file-invoice-dollar"></i></div>
                            <h2 class="fw-bold mb-0"><?php echo number_format($stats['pending_approvals'] ?? 5); ?></h2>
                            <p class="text-muted small mb-0">Pending Validations</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="glass-card">
                            <div class="stat-icon bg-gradient-purple"><i class="fas fa-wallet"></i></div>
                            <h2 class="fw-bold mb-0">₹<?php echo number_format($stats['total_revenue'] ?? 245000); ?></h2>
                            <p class="text-muted small mb-0">Platform Revenue</p>
                        </div>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="row g-4">
                    <div class="col-md-8">
                        <div class="glass-card h-100">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold mb-0">Growth Performance</h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light rounded-pill px-3" type="button">This Month</button>
                                </div>
                            </div>
                            <div style="height: 300px; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.02); border-radius: 15px;">
                                <p class="text-muted">Interactive growth charts will render here.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="glass-card h-100">
                            <h5 class="fw-bold mb-4">Live Activities</h5>
                            <div class="activity-feed">
                                <?php if (!empty($recent_activities)): ?>
                                    <?php foreach ($recent_activities as $activity): ?>
                                        <div class="recent-item">
                                            <div class="activity-dot bg-primary"></div>
                                            <div>
                                                <p class="mb-0 fw-medium small"><?php echo htmlspecialchars($activity['name']); ?></p>
                                                <small class="text-muted">Performed <?php echo htmlspecialchars($activity['action']); ?> action</small>
                                            </div>
                                            <small class="ms-auto text-muted" style="font-size: 0.7rem;">Today</small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-stream text-muted fa-2x mb-3"></i>
                                        <p class="text-muted small">No recent activity detected.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-primary w-100 mt-4 rounded-pill">View All Logs</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>