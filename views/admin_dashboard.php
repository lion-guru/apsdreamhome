<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #0f2b66 0%, #1b5fd0 50%, #0f2b66 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .sidebar h4 {
            color: white;
            margin-bottom: 30px;
        }
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 15px;
            margin-bottom: 5px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            padding: 30px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .stat-card h3 {
            color: #1b5fd0;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .stat-card p {
            color: #666;
            margin-bottom: 0;
        }
        .user-info {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .action-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .action-card h5 {
            color: #0f2b66;
            margin-bottom: 15px;
        }
        .btn-action {
            background: linear-gradient(135deg, #1b5fd0, #0f2b66);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <?php
    // Check if admin is logged in
    session_start();
    if (!isset($_SESSION['admin_logged_in'])) {
        header('Location: ' . BASE_URL . 'admin/login');
        exit;
    }
    
    $adminUsername = $_SESSION['admin_username'] ?? 'Admin';
    ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <h4><i class="fas fa-shield-alt me-2"></i>Admin Panel</h4>
                
                <div class="user-info text-white mb-4">
                    <i class="fas fa-user-circle me-2"></i>
                    <?php echo htmlspecialchars($adminUsername); ?>
                </div>
                
                <nav>
                    <a href="<?php echo BASE_URL; ?>admin/dashboard" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>Dashboard
                    </a>
                    <a href="#" class="nav-link">
                        <i class="fas fa-home"></i>Properties
                    </a>
                    <a href="#" class="nav-link">
                        <i class="fas fa-building"></i>Projects
                    </a>
                    <a href="#" class="nav-link">
                        <i class="fas fa-users"></i>Users
                    </a>
                    <a href="#" class="nav-link">
                        <i class="fas fa-comments"></i>Enquiries
                    </a>
                    <a href="#" class="nav-link">
                        <i class="fas fa-chart-bar"></i>Reports
                    </a>
                    <a href="#" class="nav-link">
                        <i class="fas fa-cog"></i>Settings
                    </a>
                    <a href="<?php echo BASE_URL; ?>admin/logout" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>Logout
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h1>
                    <div>
                        <span class="badge bg-success">Online</span>
                        <span class="ms-2">Last login: <?php echo date('Y-m-d H:i:s'); ?></span>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h3>150</h3>
                            <p>Total Properties</p>
                            <i class="fas fa-home fa-2x text-primary opacity-25"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h3>50</h3>
                            <p>Total Projects</p>
                            <i class="fas fa-building fa-2x text-success opacity-25"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h3>1,200</h3>
                            <p>Total Users</p>
                            <i class="fas fa-users fa-2x text-info opacity-25"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h3>85</h3>
                            <p>Active Enquiries</p>
                            <i class="fas fa-comments fa-2x text-warning opacity-25"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="action-card">
                            <h5><i class="fas fa-plus-circle me-2"></i>Quick Actions</h5>
                            <div>
                                <a href="#" class="btn-action">
                                    <i class="fas fa-plus me-2"></i>Add Property
                                </a>
                                <a href="#" class="btn-action">
                                    <i class="fas fa-plus me-2"></i>Add Project
                                </a>
                                <a href="#" class="btn-action">
                                    <i class="fas fa-user-plus me-2"></i>Add User
                                </a>
                                <a href="#" class="btn-action">
                                    <i class="fas fa-chart-line me-2"></i>View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="action-card">
                            <h5><i class="fas fa-bell me-2"></i>Recent Activity</h5>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item">
                                    <small class="text-muted">2 hours ago</small>
                                    New property added: "Luxury Villa in Lucknow"
                                </div>
                                <div class="list-group-item">
                                    <small class="text-muted">5 hours ago</small>
                                    New user registration: John Doe
                                </div>
                                <div class="list-group-item">
                                    <small class="text-muted">1 day ago</small>
                                    Project completed: APS Dream City Phase 2
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- System Status -->
                <div class="row">
                    <div class="col-12">
                        <div class="action-card">
                            <h5><i class="fas fa-server me-2"></i>System Status</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <p><strong>Database:</strong> <span class="badge bg-success">Connected</span></p>
                                    <p><strong>Cache:</strong> <span class="badge bg-success">Active</span></p>
                                </div>
                                <div class="col-md-3">
                                    <p><strong>Storage:</strong> <span class="badge bg-warning">75% Used</span></p>
                                    <p><strong>Memory:</strong> <span class="badge bg-success">Normal</span></p>
                                </div>
                                <div class="col-md-3">
                                    <p><strong>API:</strong> <span class="badge bg-success">Running</span></p>
                                    <p><strong>Cron:</strong> <span class="badge bg-success">Active</span></p>
                                </div>
                                <div class="col-md-3">
                                    <p><strong>SSL:</strong> <span class="badge bg-success">Valid</span></p>
                                    <p><strong>Backup:</strong> <span class="badge bg-success">Daily</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
