<?php
/**
 * Unified Associate Layout with Role-Based Sidebar
 * All associate pages should use this layout for consistency
 */

// Get current user info from session
$associate_name = $associate_name ?? ($_SESSION['user_name'] ?? 'Associate');
$associate_email = $associate_email ?? ($_SESSION['user_email'] ?? '');
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');

// Get current page for active state
$current_page = $active_page ?? basename(esc_url($_SERVER['REQUEST_URI'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'APS Dream Home Associate'); ?></title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/app/views/admin/assets/img/favicon.png">
    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Consolidated APS component system -->
    <link href="<?php echo BASE_URL; ?>/assets/css/consolidated/aps-components.css?v=2" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#f8fafc;overflow-x:hidden}
        .sidebar{position:fixed;top:0;left:0;width:260px;height:100vh;background:linear-gradient(180deg, #c2410c 0%, #ea580c 100%);z-index:1000;overflow-y:auto;transition:transform .3s;box-shadow:4px 0 15px rgba(0,0,0,0.1)}
        .sidebar::-webkit-scrollbar{width:4px}
        .sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.3);border-radius:2px}
        .sidebar-header{padding:24px 20px;border-bottom:1px solid rgba(255,255,255,.1)}
        .sidebar-logo{color:#fff;font-size:1.1rem;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:10px}
        .sidebar-logo i{font-size:1.4rem;color:#ffedd5}
        .sidebar-sub{color:rgba(255,255,255,.7);font-size:.75rem;margin-top:6px}
        .sidebar-menu{list-style:none;padding:15px 10px;margin:0}
        .sidebar-item{margin-bottom:2px}
        .sidebar-link{display:flex;align-items:center;padding:12px 14px;color:#ffedd5;text-decoration:none;border-radius:10px;font-size:.9rem;font-weight:500;transition:all .2s}
        .sidebar-link:hover{background:rgba(255,255,255,.15);color:#fff}
        .sidebar-link.active{background:#fff;color:#c2410c}
        .sidebar-link i{width:24px;margin-right:12px;font-size:1rem;color:#ffedd5;text-align:center}
        .sidebar-link.active i,.sidebar-link:hover i{color:#c2410c}
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
        .user-av{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg, #c2410c, #ea580c);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:.9rem}
        .page-content{padding:28px}
        .card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
        .btn-primary{background:#c2410c;border-color:#c2410c}
        .btn-primary:hover{background:#9a3412;border-color:#9a3412}
        @media(max-width:991px){
            .sidebar{transform:translateX(-100%)}
            .sidebar.show{transform:translateX(0)}
            .main-content{margin-left:0}
            .toggle-btn{display:block}
        }
    </style>
    <?php if (!empty($extra_css)): ?>
    <link rel="stylesheet" href="<?= e($extra_css) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
    <!-- Associate Sidebar -->
    <aside class="sidebar" id="sidebarMenu">
        <div class="sidebar-header">
            <a href="<?php echo e($base); ?>/associate/dashboard" class="sidebar-logo">
                <i class="fas fa-home"></i>
                <span>APS Dream Home</span>
            </a>
            <div class="sidebar-sub">Associate Portal</div>
        </div>
        
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo e($base); ?>/associate/dashboard" class="sidebar-link <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo e($base); ?>/associate/network/tree" class="sidebar-link <?php echo $current_page == 'network' ? 'active' : ''; ?>">
                    <i class="fas fa-network-wired"></i> My Network
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo e($base); ?>/associate/team" class="sidebar-link <?php echo $current_page == 'team' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> My Team
                </a>
            </li>
<li class="sidebar-item">
                <a href="<?php echo e($base); ?>/associate/commissions" class="sidebar-link <?php echo $current_page == 'commissions' ? 'active' : ''; ?>">
                    <i class="fas fa-rupee-sign"></i> Commissions
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo e($base); ?>/associate/tools" class="sidebar-link <?php echo $current_page == 'tools' ? 'active' : ''; ?>">
                    <i class="fas fa-toolbox"></i> Tools
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo e($base); ?>/associate/profile" class="sidebar-link <?php echo $current_page == 'profile' ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i> Profile
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo e($base); ?>/associate/leads" class="sidebar-link <?php echo $current_page == 'leads' ? 'active' : ''; ?>">
                    <i class="fas fa-bullseye"></i> Leads
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo e($base); ?>/associate/properties" class="sidebar-link <?php echo $current_page == 'properties' ? 'active' : ''; ?>">
                    <i class="fas fa-building"></i> Properties
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo e($base); ?>/associate/referral" class="sidebar-link <?php echo $current_page == 'referrals' ? 'active' : ''; ?>">
                    <i class="fas fa-user-plus"></i> Referrals
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo e($base); ?>/associate/tools" class="sidebar-link <?php echo $current_page == 'tools' ? 'active' : ''; ?>">
                    <i class="fas fa-toolbox"></i> Tools & Calculators
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo e($base); ?>/associate/profile" class="sidebar-link <?php echo $current_page == 'profile' ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i> Profile
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo e($base); ?>/associate/tools" class="sidebar-link <?php echo $current_page == 'tools' ? 'active' : ''; ?>">
                    <i class="fas fa-toolbox"></i> Smart Tools
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo e($base); ?>/associate/settings" class="sidebar-link <?php echo $current_page == 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo e($base); ?>/associate/logout" class="sidebar-link">
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
                    <ol class="breadcrumb mb-0 style-93188">
                        <li class="breadcrumb-item"><a href="<?php echo e($base); ?>/associate/dashboard">Associate</a></li>
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
                        <div class="user-av"><?= e(strtoupper(substr($associate_name,0,1))) ?></div>
                        <div>
                            <div class="style-46756"><?php echo htmlspecialchars($associate_name ?? ''); ?></div>
                            <div class="style-46475"><?php echo htmlspecialchars($associate_email ?? ''); ?></div>
                        </div>
                        <i class="fas fa-chevron-down ms-2 style-46475"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo e($base); ?>/associate/profile"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="<?php echo e($base); ?>/associate/settings"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo e($base); ?>/associate/logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
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
    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    
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