<?php
/**
 * Unified Employee Layout with Role-Based Sidebar
 * All employee pages should use this layout for consistency
 */

// Get current user info from session
$employee_name = $employee_name ?? ($_SESSION['user_name'] ?? 'Employee');
$employee_email = $employee_email ?? ($_SESSION['user_email'] ?? '');
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';

// Get current page for active state
$current_page = $active_page ?? basename($_SERVER['REQUEST_URI'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'APS Dream Home Employee'); ?></title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/app/views/admin/assets/img/favicon.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#f8fafc;overflow-x:hidden}
        .sidebar{position:fixed;top:0;left:0;width:260px;height:100vh;background:linear-gradient(180deg, #1e40af 0%, #3b82f6 100%);z-index:1000;overflow-y:auto;transition:transform .3s;box-shadow:4px 0 15px rgba(0,0,0,0.1)}
        .sidebar::-webkit-scrollbar{width:4px}
        .sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.3);border-radius:2px}
        .sidebar-header{padding:24px 20px;border-bottom:1px solid rgba(255,255,255,.1)}
        .sidebar-logo{color:#fff;font-size:1.1rem;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:10px}
        .sidebar-logo i{font-size:1.4rem;color:#dbeafe}
        .sidebar-sub{color:rgba(255,255,255,.7);font-size:.75rem;margin-top:6px}
        .sidebar-menu{list-style:none;padding:15px 10px;margin:0}
        .sidebar-item{margin-bottom:2px}
        .sidebar-link{display:flex;align-items:center;padding:12px 14px;color:#dbeafe;text-decoration:none;border-radius:10px;font-size:.9rem;font-weight:500;transition:all .2s}
        .sidebar-link:hover{background:rgba(255,255,255,.15);color:#fff}
        .sidebar-link.active{background:#fff;color:#1e40af}
        .sidebar-link i{width:24px;margin-right:12px;font-size:1rem;color:#dbeafe;text-align:center}
        .sidebar-link.active i,.sidebar-link:hover i{color:#1e40af}
        .main-content{margin-left:260px;min-height:100vh;transition:margin-left .3s}
        .top-nav{background:#fff;height:64px;padding:0 28px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e2e8f0;position:sticky;top:0;z-index:100;box-shadow:0 1px 3px rgba(0,0,0,.05)}
        .nav-left{display:flex;align-items:center;gap:16px}
        .toggle-btn{background:none;border:none;font-size:1.3rem;color:#64748b;cursor:pointer;display:none;padding:8px;border-radius:8px}
        .toggle-btn:hover{background:#f1f5f9}
        .nav-right{display:flex;align-items:center;gap:16px}
        .nav-icon{position:relative;background:none;border:none;font-size:1.1rem;color:#64748b;cursor:pointer;padding:8px;border-radius:8px}
        .nav-icon:hover{background:#f1f5f9}
        .user-box{display:flex;align-items:center;gap:12px;padding:8px 14px;border-radius:10px;cursor:pointer;transition:background .2s}
        .user-box:hover{background:#f1f5f9}
        .user-av{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg, #1e40af, #3b82f6);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:.9rem}
        .page-content{padding:28px}
        .card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
        .btn-primary{background:#1e40af;border-color:#1e40af}
        .btn-primary:hover{background:#1e3a8a;border-color:#1e3a8a}
        @media(max-width:991px){
            .sidebar{transform:translateX(-100%)}
            .sidebar.show{transform:translateX(0)}
            .main-content{margin-left:0}
            .toggle-btn{display:block}
        }
    </style>
    <?php if (!empty($extra_css)): ?>
    <link rel="stylesheet" href="<?php echo $extra_css; ?>">
    <?php endif; ?>
</head>
<body>
    <!-- Employee Sidebar -->
    <aside class="sidebar" id="sidebarMenu">
        <div class="sidebar-header">
            <a href="<?php echo $base; ?>/employee/dashboard" class="sidebar-logo">
                <i class="fas fa-home"></i>
                <span>APS Dream Home</span>
            </a>
            <div class="sidebar-sub">Employee Portal</div>
        </div>
        
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/employee/dashboard" class="sidebar-link <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/employee/tasks" class="sidebar-link <?php echo $current_page == 'tasks' ? 'active' : ''; ?>">
                    <i class="fas fa-tasks"></i> My Tasks
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/employee/attendance" class="sidebar-link <?php echo $current_page == 'attendance' ? 'active' : ''; ?>">
                    <i class="fas fa-clock"></i> Attendance
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/employee/leaves" class="sidebar-link <?php echo $current_page == 'leaves' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-minus"></i> Leave Requests
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/employee/reports" class="sidebar-link <?php echo $current_page == 'reports' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/employee/leads" class="sidebar-link <?php echo $current_page == 'leads' ? 'active' : ''; ?>">
                    <i class="fas fa-bullseye"></i> Leads
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/employee/profile" class="sidebar-link <?php echo $current_page == 'profile' ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i> Profile
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/employee/settings" class="sidebar-link <?php echo $current_page == 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/employee/logout" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navigation -->
        <nav class="top-nav">
            <div class="nav-left">
                <button class="toggle-btn" onclick="document.getElementById('sidebarMenu').classList.toggle('show')">
                    <i class="fas fa-bars"></i>
                </button>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" class="style-93188">
                        <li class="breadcrumb-item"><a href="<?php echo $base; ?>/employee/dashboard">Employee</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($active_page ?? 'Dashboard'); ?></li>
                    </ol>
                </nav>
            </div>
            <div class="nav-right">
                <button class="nav-icon" title="Notifications">
                    <i class="fas fa-bell"></i>
                </button>
                <button class="nav-icon" title="Messages">
                    <i class="fas fa-envelope"></i>
                </button>
                <div class="dropdown">
                    <div class="user-box" data-bs-toggle="dropdown">
                        <div class="user-av"><?php echo strtoupper(substr($employee_name,0,1)); ?></div>
                        <div>
                            <div class="style-46756"><?php echo htmlspecialchars($employee_name); ?></div>
                            <div class="style-46475"><?php echo htmlspecialchars($employee_email); ?></div>
                        </div>
                        <i class="fas fa-chevron-down ms-2" class="style-46475"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo $base; ?>/employee/profile"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="<?php echo $base; ?>/employee/settings"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo $base; ?>/employee/logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="page-content">
            <!-- Flash Messages -->
            <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($_SESSION['success'] ?? ''); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($_SESSION['error'] ?? ''); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Main Page Content -->
            <?php 
            // Include the actual page content
            if (!empty($content)) {
                echo $content;
            }
            ?>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auto-dismiss alerts -->
    <script>
    setTimeout(function(){
        document.querySelectorAll('.alert').forEach(function(alert){
            alert.classList.remove('show');
            setTimeout(function(){ alert.remove(); }, 150);
        });
    }, 5000);
    </script>
</body>
</html>