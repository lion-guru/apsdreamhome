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
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
    
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .recent-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-left: 4px solid #667eea;
        }
        
        .admin-sidebar {
            background: #2c3e50;
            min-height: 100vh;
            padding: 1rem;
        }
        
        .admin-sidebar .nav-link {
            color: #ecf0f1;
            padding: 0.75rem 1rem;
            border-radius: 5px;
            margin-bottom: 0.25rem;
            transition: all 0.3s ease;
        }
        
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: #34495e;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 admin-sidebar">
                <h4 class="text-white mb-4">APS Admin</h4>
                <nav class="nav flex-column">
                    <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="nav-link active">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/properties" class="nav-link">
                        <i class="fas fa-home me-2"></i> Properties
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/users" class="nav-link">
                        <i class="fas fa-users me-2"></i> Users
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/analytics" class="nav-link">
                        <i class="fas fa-chart-line me-2"></i> Analytics
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/settings" class="nav-link">
                        <i class="fas fa-cog me-2"></i> Settings
                    </a>
                    <hr class="text-white">
                    <a href="<?php echo BASE_URL; ?>/" class="nav-link">
                        <i class="fas fa-arrow-left me-2"></i> Back to Site
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10">
                <!-- Header -->
                <div class="admin-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h1><i class="fas fa-tachometer-alt me-3"></i>Admin Dashboard</h1>
                            <p class="mb-0">Welcome back! Here's what's happening with APS Dream Home today.</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <span class="badge bg-light text-dark me-2">
                                <i class="fas fa-user me-1"></i> Admin User
                            </span>
                            <span class="badge bg-success">
                                <i class="fas fa-circle me-1"></i> System Online
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="stat-number"><?php echo $stats['total_properties'] ?? 0; ?></h3>
                                    <p class="text-muted mb-0">Total Properties</p>
                                </div>
                                <div class="text-primary">
                                    <i class="fas fa-home fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="stat-number"><?php echo $stats['total_users'] ?? 0; ?></h3>
                                    <p class="text-muted mb-0">Total Users</p>
                                </div>
                                <div class="text-success">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="stat-number"><?php echo $stats['pending_applications'] ?? 0; ?></h3>
                                    <p class="text-muted mb-0">Pending Applications</p>
                                </div>
                                <div class="text-warning">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="stat-number"><?php echo $stats['total_revenue'] ?? '₹0'; ?></h3>
                                    <p class="text-muted mb-0">Total Revenue</p>
                                </div>
                                <div class="text-info">
                                    <i class="fas fa-rupee-sign fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activities -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-building me-2"></i>Recent Projects</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($recent_projects)): ?>
                                    <?php foreach ($recent_projects as $project): ?>
                                        <div class="recent-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($project['name'] ?? 'Project Name'); ?></h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($project['location'] ?? 'Location'); ?></small>
                                                </div>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($project['status'] ?? 'Active'); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">No recent projects found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-user-plus me-2"></i>Recent Applications</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($recent_applications)): ?>
                                    <?php foreach ($recent_applications as $application): ?>
                                        <div class="recent-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($application['name'] ?? 'Applicant Name'); ?></h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($application['property'] ?? 'Property Interest'); ?></small>
                                                </div>
                                                <span class="badge bg-warning"><?php echo htmlspecialchars($application['status'] ?? 'Pending'); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">No recent applications found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pending Tasks -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-tasks me-2"></i>Pending Tasks</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($pending_tasks)): ?>
                                    <div class="row">
                                        <?php foreach ($pending_tasks as $task): ?>
                                            <div class="col-md-4 mb-3">
                                                <div class="alert alert-info">
                                                    <h6 class="alert-heading"><?php echo htmlspecialchars($task['title'] ?? 'Task Title'); ?></h6>
                                                    <p class="mb-1"><?php echo htmlspecialchars($task['description'] ?? 'Task Description'); ?></p>
                                                    <small class="text-muted">Due: <?php echo htmlspecialchars($task['due_date'] ?? 'Not set'); ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No pending tasks. Great job!</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-refresh dashboard every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
        
        // Add interactive features
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stat numbers
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(element => {
                const finalValue = parseInt(element.textContent.replace(/[^0-9]/g, '')) || 0;
                let currentValue = 0;
                const increment = finalValue / 50;
                
                const counter = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        currentValue = finalValue;
                        clearInterval(counter);
                    }
                    
                    if (element.textContent.includes('₹')) {
                        element.textContent = '₹' + Math.floor(currentValue).toLocaleString('en-IN');
                    } else {
                        element.textContent = Math.floor(currentValue).toLocaleString('en-IN');
                    }
                }, 20);
            });
        });
    </script>
</body>
</html>
