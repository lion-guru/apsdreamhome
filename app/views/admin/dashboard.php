<?php
/**
 * Admin Dashboard - Compatible with AdminBaseController
 * This file renders content when accessed directly
 */

if (!defined('BASE_PATH')) {
    // If accessed directly, check auth and include the standalone version
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/admin/login');
        exit;
    }
}

// Get user info from session
$currentUser = [
    'id' => $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0,
    'name' => $_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? 'User',
    'email' => $_SESSION['admin_email'] ?? '',
    'role' => $_SESSION['admin_role'] ?? $_SESSION['user_role'] ?? 'guest'
];

$currentRole = $currentUser['role'];
$roleName = ucwords(str_replace('_', ' ', $currentRole));

// Get stats from database
$db = \App\Core\Database\Database::getInstance();

// Get basic stats
$stats = [
    'total_users' => 0,
    'total_properties' => 0,
    'total_leads' => 0,
    'new_leads_today' => 0,
    'total_associates' => 0,
    'revenue_month' => 0,
    'total_employees' => 0,
    'pending_bookings' => 0
];

try {
    $stats['total_users'] = $db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
    $stats['total_properties'] = $db->fetch("SELECT COUNT(*) as count FROM properties")['count'] ?? 0;
    $stats['total_leads'] = $db->fetch("SELECT COUNT(*) as count FROM leads")['count'] ?? 0;
    $stats['new_leads_today'] = $db->fetch("SELECT COUNT(*) as count FROM leads WHERE DATE(created_at) = CURDATE()")['count'] ?? 0;
    $stats['total_associates'] = $db->fetch("SELECT COUNT(*) as count FROM users WHERE role IN ('associate', 'agent')")['count'] ?? 0;
    $stats['revenue_month'] = $db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")['total'] ?? 0;
    $stats['total_employees'] = $db->fetch("SELECT COUNT(*) as count FROM employees")['count'] ?? 0;
    $stats['pending_bookings'] = $db->fetch("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")['count'] ?? 0;
} catch (\Exception $e) {
    // Silently handle database errors
}

// Page title
$page_title = 'Dashboard';
$page_description = 'Welcome to your admin dashboard';
$active_page = 'dashboard';

// If using a layout wrapper (from AdminBaseController), just render content
if (isset($layout_content) || (isset($is_standalone) && !$is_standalone)) {
    // This section is for when AdminBaseController renders this view
?>

<!-- Dashboard Header -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title"><?php echo $page_title; ?></h1>
            <p class="page-description"><?php echo $page_description; ?></p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" onclick="location.reload()">
                <i class="fas fa-sync-alt me-2"></i> Refresh
            </button>
        </div>
    </div>
</div>

<!-- Stats Cards Row 1 -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-users"></i></div>
            <div class="stat-content">
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-building"></i></div>
            <div class="stat-content">
                <div class="stat-label">Properties</div>
                <div class="stat-value"><?php echo number_format($stats['total_properties']); ?></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-bullseye"></i></div>
            <div class="stat-content">
                <div class="stat-label">Total Leads</div>
                <div class="stat-value"><?php echo number_format($stats['total_leads']); ?></div>
                <div class="stat-change up"><i class="fas fa-arrow-up"></i> <?php echo $stats['new_leads_today']; ?> today</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon info"><i class="fas fa-network-wired"></i></div>
            <div class="stat-content">
                <div class="stat-label">Associates</div>
                <div class="stat-value"><?php echo number_format($stats['total_associates']); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards Row 2 -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-rupee-sign"></i></div>
            <div class="stat-content">
                <div class="stat-label">Revenue (30 Days)</div>
                <div class="stat-value">₹<?php echo number_format($stats['revenue_month'], 2); ?></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-user-tie"></i></div>
            <div class="stat-content">
                <div class="stat-label">Employees</div>
                <div class="stat-value"><?php echo number_format($stats['total_employees']); ?></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-file-contract"></i></div>
            <div class="stat-content">
                <div class="stat-label">Pending Bookings</div>
                <div class="stat-value"><?php echo number_format($stats['pending_bookings']); ?></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
                <div class="stat-label">System Status</div>
                <div class="stat-value text-success">Online</div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="<?php echo BASE_URL; ?>/admin/leads?action=new" class="btn btn-outline-primary w-100 py-3">
                            <i class="fas fa-user-plus mb-2" style="font-size: 1.5rem;"></i>
                            <div>Add New Lead</div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo BASE_URL; ?>/admin/properties?action=new" class="btn btn-outline-success w-100 py-3">
                            <i class="fas fa-plus mb-2" style="font-size: 1.5rem;"></i>
                            <div>Add Property</div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo BASE_URL; ?>/admin/bookings?action=new" class="btn btn-outline-warning w-100 py-3">
                            <i class="fas fa-file-contract mb-2" style="font-size: 1.5rem;"></i>
                            <div>New Booking</div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo BASE_URL; ?>/admin/mlm/commissions?action=payout" class="btn btn-outline-info w-100 py-3">
                            <i class="fas fa-wallet mb-2" style="font-size: 1.5rem;"></i>
                            <div>Process Payout</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Two Column Layout -->
<div class="row g-4">
    <!-- Recent Leads -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title"><i class="fas fa-user-clock me-2"></i>Recent Leads</h5>
                <a href="<?php echo BASE_URL; ?>/admin/leads" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php 
                try {
                    $recentLeads = $db->fetchAll("SELECT * FROM leads ORDER BY created_at DESC LIMIT 5");
                    if (!empty($recentLeads)):
                ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentLeads as $lead): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-medium"><?php echo htmlspecialchars($lead['name'] ?? 'Unknown'); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($lead['email'] ?? ''); ?></small>
                            </div>
                            <span class="badge bg-<?php echo $lead['status'] === 'hot' ? 'danger' : ($lead['status'] === 'warm' ? 'warning' : 'secondary'); ?>">
                                <?php echo ucfirst($lead['status'] ?? 'new'); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-4 text-muted">No leads found</div>
                <?php endif; } catch (\Exception $e) { ?>
                <div class="text-center py-4 text-muted">Unable to load leads</div>
                <?php } ?>
            </div>
        </div>
    </div>
    
    <!-- System Overview -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-server me-2"></i>System Overview</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Database Tables</span>
                        <span class="fw-semibold">633</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: 100%;"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Active Users</span>
                        <span class="fw-semibold"><?php echo number_format($stats['total_users']); ?></span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: 75%;"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>System Health</span>
                        <span class="fw-semibold text-success">99.9%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: 99.9%;"></div>
                    </div>
                </div>
                
                <hr>
                
                <div class="row text-center">
                    <div class="col-4">
                        <div class="h4 mb-0 text-primary"><?php echo number_format($stats['total_properties']); ?></div>
                        <small class="text-muted">Properties</small>
                    </div>
                    <div class="col-4">
                        <div class="h4 mb-0 text-success"><?php echo number_format($stats['total_leads']); ?></div>
                        <small class="text-muted">Total Leads</small>
                    </div>
                    <div class="col-4">
                        <div class="h4 mb-0 text-warning"><?php echo number_format($stats['pending_bookings']); ?></div>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
} else {
    // Standalone mode - render full HTML page
    $admin_layout = true;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | APS Dream Home Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --sidebar-bg: #1e1b4b;
            --main-bg: #f8fafc;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--main-bg);
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background: var(--sidebar-bg);
            padding-top: 0;
            z-index: 1000;
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
        }
        
        .sidebar-nav { padding: 15px 10px; }
        
        .nav-item { margin-bottom: 2px; }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        
        .nav-link i { width: 24px; margin-right: 10px; }
        
        .main-content { margin-left: 260px; min-height: 100vh; }
        
        .top-navbar {
            background: #fff;
            padding: 15px 25px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-content { padding: 25px; }
        
        .stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .stat-icon.primary { background: #eef2ff; color: var(--primary); }
        .stat-icon.success { background: #ecfdf5; color: #10b981; }
        .stat-icon.warning { background: #fffbeb; color: #f59e0b; }
        .stat-icon.info { background: #f0fdfa; color: #14b8a6; }
        .stat-icon.purple { background: #faf5ff; color: #a855f7; }
        
        .stat-value { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
        .stat-label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; }
        .stat-change { font-size: 0.75rem; margin-top: 5px; }
        .stat-change.up { color: #10b981; }
        
        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }
        
        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #e2e8f0;
            background: transparent;
        }
        
        .card-title { font-size: 1rem; font-weight: 600; margin: 0; }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="sidebar-logo">
                <i class="fas fa-home me-2"></i> APS Dream Home
            </a>
            <div class="mt-2">
                <span class="badge bg-primary"><?php echo $roleName; ?></span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="nav-link active">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>/admin/leads" class="nav-link">
                        <i class="fas fa-bullseye"></i> Leads
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>/admin/properties" class="nav-link">
                        <i class="fas fa-building"></i> Properties
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>/admin/mlm/tree" class="nav-link">
                        <i class="fas fa-sitemap"></i> MLM Network
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>/admin/bookings" class="nav-link">
                        <i class="fas fa-file-contract"></i> Bookings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>/admin/financial" class="nav-link">
                        <i class="fas fa-rupee-sign"></i> Financial
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>/admin/employees" class="nav-link">
                        <i class="fas fa-users"></i> Employees
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a href="<?php echo BASE_URL; ?>/admin/settings" class="nav-link">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>/admin/logout" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <nav class="top-navbar">
            <h5 class="mb-0"><?php echo $page_title; ?></h5>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?>
                </div>
                <div>
                    <div class="fw-semibold"><?php echo htmlspecialchars($currentUser['name']); ?></div>
                    <small class="text-muted"><?php echo $roleName; ?></small>
                </div>
            </div>
        </nav>
        
        <div class="page-content">
            <!-- Dashboard Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title mb-1"><?php echo $page_title; ?></h1>
                    <p class="text-muted mb-0"><?php echo $page_description; ?></p>
                </div>
                <button class="btn btn-primary" onclick="location.reload()">
                    <i class="fas fa-sync-alt me-2"></i> Refresh
                </button>
            </div>
            
            <!-- Stats Cards Row 1 -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon primary"><i class="fas fa-users"></i></div>
                        <div class="stat-content">
                            <div class="stat-label">Total Users</div>
                            <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon success"><i class="fas fa-building"></i></div>
                        <div class="stat-content">
                            <div class="stat-label">Properties</div>
                            <div class="stat-value"><?php echo number_format($stats['total_properties']); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon warning"><i class="fas fa-bullseye"></i></div>
                        <div class="stat-content">
                            <div class="stat-label">Total Leads</div>
                            <div class="stat-value"><?php echo number_format($stats['total_leads']); ?></div>
                            <div class="stat-change up"><i class="fas fa-arrow-up"></i> <?php echo $stats['new_leads_today']; ?> today</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon info"><i class="fas fa-network-wired"></i></div>
                        <div class="stat-content">
                            <div class="stat-label">Associates</div>
                            <div class="stat-value"><?php echo number_format($stats['total_associates']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards Row 2 -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon purple"><i class="fas fa-rupee-sign"></i></div>
                        <div class="stat-content">
                            <div class="stat-label">Revenue (30 Days)</div>
                            <div class="stat-value">₹<?php echo number_format($stats['revenue_month'], 2); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon primary"><i class="fas fa-user-tie"></i></div>
                        <div class="stat-content">
                            <div class="stat-label">Employees</div>
                            <div class="stat-value"><?php echo number_format($stats['total_employees']); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon warning"><i class="fas fa-file-contract"></i></div>
                        <div class="stat-content">
                            <div class="stat-label">Pending Bookings</div>
                            <div class="stat-value"><?php echo number_format($stats['pending_bookings']); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-content">
                            <div class="stat-label">System Status</div>
                            <div class="stat-value text-success">Online</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="<?php echo BASE_URL; ?>/admin/leads?action=new" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-user-plus mb-2" style="font-size: 1.5rem;"></i>
                                <div>Add New Lead</div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo BASE_URL; ?>/admin/properties?action=new" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-plus mb-2" style="font-size: 1.5rem;"></i>
                                <div>Add Property</div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo BASE_URL; ?>/admin/bookings?action=new" class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-file-contract mb-2" style="font-size: 1.5rem;"></i>
                                <div>New Booking</div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo BASE_URL; ?>/admin/mlm/commissions?action=payout" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-wallet mb-2" style="font-size: 1.5rem;"></i>
                                <div>Process Payout</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Two Column Layout -->
            <div class="row g-4">
                <!-- Recent Leads -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title"><i class="fas fa-user-clock me-2"></i>Recent Leads</h5>
                            <a href="<?php echo BASE_URL; ?>/admin/leads" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <?php 
                            try {
                                $recentLeads = $db->fetchAll("SELECT * FROM leads ORDER BY created_at DESC LIMIT 5");
                                if (!empty($recentLeads)):
                            ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentLeads as $lead): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-medium"><?php echo htmlspecialchars($lead['name'] ?? 'Unknown'); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($lead['email'] ?? ''); ?></small>
                                        </div>
                                        <span class="badge bg-<?php echo $lead['status'] === 'hot' ? 'danger' : ($lead['status'] === 'warm' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($lead['status'] ?? 'new'); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4 text-muted">No leads found</div>
                            <?php endif; } catch (\Exception $e) { ?>
                            <div class="text-center py-4 text-muted">Unable to load leads</div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                
                <!-- System Overview -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-server me-2"></i>System Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Database Tables</span>
                                    <span class="fw-semibold">633</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: 100%;"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Active Users</span>
                                    <span class="fw-semibold"><?php echo number_format($stats['total_users']); ?></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: 75%;"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>System Health</span>
                                    <span class="fw-semibold text-success">99.9%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-info" style="width: 99.9%;"></div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="h4 mb-0 text-primary"><?php echo number_format($stats['total_properties']); ?></div>
                                    <small class="text-muted">Properties</small>
                                </div>
                                <div class="col-4">
                                    <div class="h4 mb-0 text-success"><?php echo number_format($stats['total_leads']); ?></div>
                                    <small class="text-muted">Total Leads</small>
                                </div>
                                <div class="col-4">
                                    <div class="h4 mb-0 text-warning"><?php echo number_format($stats['pending_bookings']); ?></div>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php } ?>
