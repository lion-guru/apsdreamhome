<?php
/**
 * Super Admin Layout - Full Access
 * Complete dashboard with all modules
 */
if (!defined('BASE_PATH')) exit;

$currentUser = $currentUser ?? [];
$currentRole = $currentRole ?? 'guest';
$roleName = $roleName ?? 'Guest';
$roleLevel = $roleLevel ?? 0;
$roleCategory = $roleCategory ?? 'default';
$permissions = $permissions ?? [];
$menus = $menus ?? [];

// Get flash messages
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Super Admin Dashboard'; ?> | APS Dream Home</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --sidebar-bg: #1e1b4b;
            --sidebar-hover: #312e81;
            --sidebar-active: #4f46e5;
            --sidebar-text: #e0e7ff;
            --sidebar-icon: #a5b4fc;
            --main-bg: #f8fafc;
            --card-border: #e2e8f0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--main-bg);
            overflow-x: hidden;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 3px;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-logo {
            color: #fff;
            font-size: 1.25rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-logo i {
            font-size: 1.5rem;
            color: var(--sidebar-icon);
        }
        
        .sidebar-subtitle {
            color: rgba(255,255,255,0.6);
            font-size: 0.75rem;
            margin-top: 5px;
        }
        
        .sidebar-section {
            padding: 15px 15px 5px;
            font-size: 0.65rem;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
            font-weight: 600;
            letter-spacing: 0.05em;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0 10px;
        }
        
        .sidebar-item {
            margin-bottom: 2px;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .sidebar-link:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }
        
        .sidebar-link.active {
            background: var(--sidebar-active);
            color: #fff;
        }
        
        .sidebar-link i {
            width: 20px;
            margin-right: 10px;
            font-size: 1rem;
            color: var(--sidebar-icon);
        }
        
        .sidebar-link.active i,
        .sidebar-link:hover i {
            color: #fff;
        }
        
        .sidebar-link .badge {
            margin-left: auto;
            font-size: 0.65rem;
            padding: 3px 6px;
            border-radius: 10px;
            background: var(--primary);
        }
        
        .sidebar-arrow {
            margin-left: auto;
            font-size: 0.75rem;
            transition: transform 0.2s;
        }
        
        .sidebar-link[aria-expanded="true"] .sidebar-arrow {
            transform: rotate(180deg);
        }
        
        .sidebar-submenu {
            list-style: none;
            padding-left: 30px;
            margin: 0;
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.3s ease;
        }
        
        .sidebar-submenu.show {
            max-height: 500px;
        }
        
        .sidebar-submenu .sidebar-link {
            padding: 8px 12px;
            font-size: 0.8rem;
        }
        
        .sidebar-submenu .sidebar-link i {
            font-size: 0.85rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }
        
        /* Top Navbar */
        .top-navbar {
            background: #fff;
            height: 64px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--card-border);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .navbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .toggle-sidebar {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #64748b;
            cursor: pointer;
            padding: 5px;
        }
        
        .breadcrumb {
            margin: 0;
            font-size: 0.875rem;
        }
        
        .breadcrumb-item a {
            color: #64748b;
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: #1e293b;
            font-weight: 500;
        }
        
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .navbar-icon {
            position: relative;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #64748b;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .navbar-icon:hover {
            background: #f1f5f9;
            color: var(--primary);
        }
        
        .navbar-icon .badge {
            position: absolute;
            top: 4px;
            right: 4px;
            font-size: 0.6rem;
            padding: 2px 5px;
            border-radius: 10px;
            background: #ef4444;
            color: #fff;
        }
        
        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .user-dropdown:hover {
            background: #f1f5f9;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .user-info {
            text-align: left;
        }
        
        .user-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .user-role {
            font-size: 0.7rem;
            color: #64748b;
        }
        
        /* Page Content */
        .page-content {
            padding: 24px;
        }
        
        .page-header {
            margin-bottom: 24px;
        }
        
        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }
        
        .page-description {
            color: #64748b;
            font-size: 0.875rem;
        }
        
        /* Cards */
        .card {
            background: #fff;
            border: 1px solid var(--card-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--card-border);
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Stats Cards */
        .stat-card {
            background: #fff;
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .stat-icon.primary { background: #eef2ff; color: var(--primary); }
        .stat-icon.success { background: #ecfdf5; color: #10b981; }
        .stat-icon.warning { background: #fffbeb; color: #f59e0b; }
        .stat-icon.danger { background: #fef2f2; color: #ef4444; }
        .stat-icon.info { background: #f0fdfa; color: #14b8a6; }
        .stat-icon.purple { background: #faf5ff; color: #a855f7; }
        
        .stat-content {
            flex: 1;
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }
        
        .stat-change {
            font-size: 0.75rem;
            margin-top: 5px;
        }
        
        .stat-change.up { color: #10b981; }
        .stat-change.down { color: #ef4444; }
        
        /* Tables */
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            color: #64748b;
            border-bottom: 2px solid var(--card-border);
            padding: 12px 16px;
        }
        
        .table td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid var(--card-border);
        }
        
        .table tbody tr:hover {
            background: #f8fafc;
        }
        
        /* Buttons */
        .btn {
            font-size: 0.875rem;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
        }
        
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        /* Alerts */
        .alert {
            border: none;
            border-radius: 8px;
            padding: 12px 16px;
        }
        
        /* Badges */
        .badge {
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
        }
        
        /* Mobile Responsive */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .user-info {
                display: none;
            }
        }
        
        /* Role Badge Colors */
        .role-super_admin { background: #7c3aed; color: #fff; }
        .role-admin { background: #dc2626; color: #fff; }
        .role-manager { background: #ea580c; color: #fff; }
        .role-employee { background: #0891b2; color: #fff; }
        .role-associate { background: #16a34a; color: #fff; }
        .role-customer { background: #4f46e5; color: #fff; }
        .role-agent { background: #ca8a04; color: #fff; }
        
        /* Scrollbar for content area */
        .main-content::-webkit-scrollbar {
            width: 8px;
        }
        
        .main-content::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="sidebar-logo">
                <i class="fas fa-home"></i>
                <span>APS Dream Home</span>
            </a>
            <div class="sidebar-subtitle">
                <span class="badge role-<?php echo $currentRole; ?>"><?php echo ucwords(str_replace('_', ' ', $roleName)); ?></span>
            </div>
        </div>
        
        <div class="sidebar-section">Main</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="sidebar-link <?php echo ($active_page ?? '') === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>
        
        <?php if (!empty($menus)): ?>
            <?php foreach ($menus as $menuKey => $menu): ?>
                <div class="sidebar-section"><?php echo $menu['title']; ?></div>
                <ul class="sidebar-menu">
                    <?php if (!empty($menu['items'])): ?>
                        <?php foreach ($menu['items'] as $itemKey => $item): ?>
                            <li class="sidebar-item">
                                <a href="<?php echo BASE_URL . $item['url']; ?>" class="sidebar-link <?php echo ($active_page ?? '') === $itemKey ? 'active' : ''; ?>">
                                    <i class="<?php echo $item['icon']; ?>"></i>
                                    <span><?php echo $item['title']; ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Default menus when no menus passed -->
            <div class="sidebar-section">CRM & Leads</div>
            <ul class="sidebar-menu">
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/leads" class="sidebar-link"><i class="fas fa-bullseye"></i><span>All Leads</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/customers" class="sidebar-link"><i class="fas fa-user-check"></i><span>Customers</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/campaigns" class="sidebar-link"><i class="fas fa-bullhorn"></i><span>Campaigns</span></a></li>
            </ul>
            
            <div class="sidebar-section">Properties</div>
            <ul class="sidebar-menu">
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/properties" class="sidebar-link"><i class="fas fa-building"></i><span>All Properties</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/projects" class="sidebar-link"><i class="fas fa-city"></i><span>Projects</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/plots" class="sidebar-link"><i class="fas fa-map"></i><span>Plots / Land</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/bookings" class="sidebar-link"><i class="fas fa-file-contract"></i><span>Bookings</span></a></li>
            </ul>
            
            <div class="sidebar-section">MLM Network</div>
            <ul class="sidebar-menu">
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/mlm/tree" class="sidebar-link"><i class="fas fa-sitemap"></i><span>Network Tree</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/mlm/associates" class="sidebar-link"><i class="fas fa-users"></i><span>Associates</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/mlm/commissions" class="sidebar-link"><i class="fas fa-percentage"></i><span>Commissions</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/mlm/payouts" class="sidebar-link"><i class="fas fa-rupee-sign"></i><span>Payouts</span></a></li>
            </ul>
            
            <div class="sidebar-section">Financial</div>
            <ul class="sidebar-menu">
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/financial/transactions" class="sidebar-link"><i class="fas fa-exchange-alt"></i><span>Transactions</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/financial/invoices" class="sidebar-link"><i class="fas fa-file-invoice-dollar"></i><span>Invoices</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/financial/emi" class="sidebar-link"><i class="fas fa-calendar"></i><span>EMI Management</span></a></li>
            </ul>
            
            <div class="sidebar-section">Team & HR</div>
            <ul class="sidebar-menu">
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/employees" class="sidebar-link"><i class="fas fa-user-friends"></i><span>Staff Members</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/attendance" class="sidebar-link"><i class="fas fa-clock"></i><span>Attendance</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/leaves" class="sidebar-link"><i class="fas fa-calendar-alt"></i><span>Leaves</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/payroll" class="sidebar-link"><i class="fas fa-money-bill"></i><span>Payroll</span></a></li>
            </ul>
            
            <div class="sidebar-section">Marketing</div>
            <ul class="sidebar-menu">
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/marketing/campaigns" class="sidebar-link"><i class="fas fa-bullhorn"></i><span>Campaigns</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/marketing/email" class="sidebar-link"><i class="fas fa-envelope"></i><span>Email Templates</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/marketing/sms" class="sidebar-link"><i class="fas fa-comment-sms"></i><span>SMS Templates</span></a></li>
            </ul>
            
            <div class="sidebar-section">Content</div>
            <ul class="sidebar-menu">
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/media" class="sidebar-link"><i class="fas fa-image"></i><span>Media Gallery</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/pages" class="sidebar-link"><i class="fas fa-file"></i><span>Pages</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/blog" class="sidebar-link"><i class="fas fa-newspaper"></i><span>Blog & News</span></a></li>
            </ul>
            
            <div class="sidebar-section">Reports</div>
            <ul class="sidebar-menu">
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/reports/sales" class="sidebar-link"><i class="fas fa-chart-line"></i><span>Sales Reports</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/reports/mlm" class="sidebar-link"><i class="fas fa-sitemap"></i><span>MLM Reports</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/reports/financial" class="sidebar-link"><i class="fas fa-rupee-sign"></i><span>Financial Reports</span></a></li>
            </ul>
            
            <div class="sidebar-section">System</div>
            <ul class="sidebar-menu">
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/settings" class="sidebar-link"><i class="fas fa-cog"></i><span>Settings</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/users" class="sidebar-link"><i class="fas fa-users-cog"></i><span>User Management</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/ai-settings" class="sidebar-link"><i class="fas fa-robot"></i><span>AI Settings</span></a></li>
                <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/backup" class="sidebar-link"><i class="fas fa-database"></i><span>Backup</span></a></li>
            </ul>
        <?php endif; ?>
        
        <div class="sidebar-section">Account</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/admin/profile" class="sidebar-link <?php echo ($active_page ?? '') === 'profile' ? 'active' : ''; ?>">
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/admin/settings" class="sidebar-link <?php echo ($active_page ?? '') === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/admin/logout" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="navbar-left">
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/admin/dashboard">Home</a></li>
                        <li class="breadcrumb-item active"><?php echo $page_title ?? 'Dashboard'; ?></li>
                    </ol>
                </nav>
            </div>
            <div class="navbar-right">
                <button class="navbar-icon" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </button>
                <button class="navbar-icon" title="Messages">
                    <i class="fas fa-envelope"></i>
                    <span class="badge">5</span>
                </button>
                <div class="user-dropdown dropdown">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($currentUser['name'] ?? $currentUser['username'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo $currentUser['name'] ?? $currentUser['username'] ?? 'User'; ?></div>
                        <div class="user-role"><?php echo ucwords(str_replace('_', ' ', $roleName)); ?></div>
                    </div>
                    <i class="fas fa-chevron-down" style="color: #64748b; font-size: 0.75rem;"></i>
                </div>
            </div>
        </nav>
        
        <!-- Page Content -->
        <div class="page-content">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php echo $content ?? ''; ?>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('toggleSidebar')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('toggleSidebar');
            if (window.innerWidth < 992 && 
                !sidebar.contains(e.target) && 
                !toggleBtn.contains(e.target) &&
                sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });
        
        // Auto-dismiss alerts
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
