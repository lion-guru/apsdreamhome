<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - APS Dream Home</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Admin CSS -->
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-color);
            overflow-x: hidden;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--dark-color) 0%, #2c3e50 100%);
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .sidebar-logo {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .sidebar-menu {
            padding: 0;
            margin: 0;
            list-style: none;
        }
        
        .sidebar-item {
            position: relative;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--primary-color);
        }
        
        .sidebar-link.active {
            background-color: var(--primary-color);
            color: white;
            border-left-color: var(--primary-color);
        }
        
        .sidebar-icon {
            width: 20px;
            margin-right: 12px;
            text-align: center;
        }
        
        .sidebar-badge {
            margin-left: auto;
            background: var(--danger-color);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }
        
        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        .top-navbar {
            background: white;
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--dark-color);
            text-decoration: none;
        }
        
        .navbar-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .content-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: var(--secondary-color);
            font-size: 0.9rem;
        }
        
        .toggle-sidebar {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: none;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .toggle-sidebar {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">AD</div>
            <small style="color: rgba(255,255,255,0.6);">Admin Panel</small>
        </div>
        
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="#" class="sidebar-link active">
                    <i class="fas fa-tachometer-alt sidebar-icon"></i>
                    Dashboard
                    <span class="sidebar-badge">new</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link">
                    <i class="fas fa-home sidebar-icon"></i>
                    Properties
                    <span class="sidebar-badge">250+</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link">
                    <i class="fas fa-users sidebar-icon"></i>
                    Users
                    <span class="sidebar-badge">150+</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link">
                    <i class="fas fa-envelope sidebar-icon"></i>
                    Contacts
                    <span class="sidebar-badge">89</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link">
                    <i class="fas fa-chart-line sidebar-icon"></i>
                    Analytics
                    <span class="sidebar-badge">reports</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link">
                    <i class="fas fa-cog sidebar-icon"></i>
                    Settings
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navigation -->
        <nav class="top-navbar">
            <div class="d-flex justify-content-between align-items-center">
                <a href="#" class="navbar-brand">
                    <i class="fas fa-home me-2"></i>
                    APS Dream Home Admin
                </a>
                
                <div class="navbar-actions">
                    <button class="btn btn-outline-secondary btn-sm me-2">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger ms-1">3</span>
                    </button>
                    
                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                            Admin User
                            <i class="fas fa-chevron-down ms-1"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Dashboard Content -->
        <div class="row">
            <div class="col-md-3">
                <div class="content-card stat-card">
                    <div class="text-center">
                        <div class="stat-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="stat-value">250+</div>
                        <div class="stat-label">Total Properties</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="content-card">
                    <div class="text-center">
                        <div class="stat-icon" style="background: var(--success-color);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-value">150+</div>
                        <div class="stat-label">Total Users</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="content-card">
                    <div class="text-center">
                        <div class="stat-icon" style="background: var(--info-color);">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-value">89</div>
                        <div class="stat-label">New Contacts</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="content-card">
                    <div class="text-center">
                        <div class="stat-icon" style="background: var(--warning-color);">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="stat-value">1,234</div>
                        <div class="stat-label">Site Visits</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="row">
            <div class="col-md-8">
                <div class="content-card">
                    <h5 class="mb-3">
                        <i class="fas fa-clock me-2"></i>
                        Recent Activity
                    </h5>
                    <div class="list-group">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-plus-circle text-success me-2"></i>
                                New property added: "Luxury Villa in Gorakhpur"
                            </div>
                            <small class="text-muted">2 minutes ago</small>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-user-plus text-info me-2"></i>
                                New user registered: John Doe
                            </div>
                            <small class="text-muted">5 minutes ago</small>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-envelope text-primary me-2"></i>
                                New contact inquiry: Sarah Smith
                            </div>
                            <small class="text-muted">10 minutes ago</small>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-edit text-warning me-2"></i>
                                Property updated: "Modern Apartment"
                            </div>
                            <small class="text-muted">15 minutes ago</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="content-card">
                    <h5 class="mb-3">
                        <i class="fas fa-chart-pie me-2"></i>
                        Quick Actions
                    </h5>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>
                            Add Property
                        </button>
                        <button class="btn btn-success w-100">
                            <i class="fas fa-user-plus me-2"></i>
                            Add User
                        </button>
                        <button class="btn btn-info w-100">
                            <i class="fas fa-chart-bar me-2"></i>
                            View Reports
                        </button>
                        <button class="btn btn-warning w-100">
                            <i class="fas fa-cog me-2"></i>
                            Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toggle Sidebar Button -->
    <button class="toggle-sidebar" id="toggleSidebar" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const toggleBtn = document.getElementById('toggleSidebar');
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }
        
        // Set active menu item based on current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const menuLinks = document.querySelectorAll('.sidebar-link');
            
            menuLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
        });
        
        // Handle responsive sidebar
        if (window.innerWidth < 768) {
            document.getElementById('sidebar').classList.add('collapsed');
            document.getElementById('mainContent').classList.add('expanded');
        }
        
        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
            });
        });
    </script>
</body>
</html>
