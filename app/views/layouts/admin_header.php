<?php
/**
 * Admin Layout - Full Dashboard with Sidebar Navigation
 */

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . '/admin/login');
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$current_uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'Dashboard'); ?> | APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5; --primary-dark: #4338ca;
            --sidebar-bg: #1e1b4b; --sidebar-hover: #312e81;
            --sidebar-active: #4f46e5; --sidebar-text: #c7d2fe; --sidebar-icon: #a5b4fc;
            --main-bg: #f1f5f9; --card-bg: #ffffff; --card-border: #e2e8f0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--main-bg); overflow-x: hidden; }
        .sidebar {
            position: fixed; top: 0; left: 0; width: 260px; height: 100vh;
            background: var(--sidebar-bg); z-index: 1000; overflow-y: auto; transition: transform 0.3s;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 2px; }
        .sidebar-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-logo { color: #fff; font-size: 1.1rem; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .sidebar-logo i { font-size: 1.3rem; color: var(--sidebar-icon); }
        .sidebar-subtitle { color: rgba(255,255,255,0.5); font-size: 0.7rem; margin-top: 4px; }
        .sidebar-section { padding: 15px 15px 5px; font-size: 0.65rem; text-transform: uppercase; color: rgba(255,255,255,0.35); font-weight: 600; letter-spacing: 0.05em; }
        .sidebar-menu { list-style: none; padding: 0 10px; margin: 0; }
        .sidebar-item { margin-bottom: 2px; }
        .sidebar-link { display: flex; align-items: center; padding: 9px 12px; color: var(--sidebar-text); text-decoration: none; border-radius: 8px; font-size: 0.85rem; font-weight: 500; transition: all 0.2s; }
        .sidebar-link:hover { background: var(--sidebar-hover); color: #fff; }
        .sidebar-link.active { background: var(--sidebar-active); color: #fff; }
        .sidebar-link i { width: 20px; margin-right: 10px; font-size: 0.95rem; color: var(--sidebar-icon); text-align: center; }
        .sidebar-link.active i, .sidebar-link:hover i { color: #fff; }
        .main-content { margin-left: 260px; min-height: 100vh; }
        .top-navbar {
            background: #fff; height: 60px; padding: 0 24px;
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid var(--card-border); position: sticky; top: 0; z-index: 100;
        }
        .navbar-left { display: flex; align-items: center; gap: 15px; }
        .toggle-sidebar { background: none; border: none; font-size: 1.2rem; color: #64748b; cursor: pointer; padding: 5px; display: none; }
        .page-breadcrumb { font-size: 0.85rem; color: #64748b; }
        .page-breadcrumb strong { color: #1e293b; }
        .navbar-right { display: flex; align-items: center; gap: 15px; }
        .navbar-icon { position: relative; background: none; border: none; font-size: 1.1rem; color: #64748b; cursor: pointer; padding: 8px; border-radius: 8px; transition: all 0.2s; }
        .navbar-icon:hover { background: #f1f5f9; color: var(--primary); }
        .navbar-icon .badge { position: absolute; top: 2px; right: 2px; font-size: 0.55rem; padding: 2px 5px; border-radius: 10px; background: #ef4444; color: #fff; }
        .user-dropdown { display: flex; align-items: center; gap: 10px; padding: 5px 10px; border-radius: 8px; cursor: pointer; }
        .user-dropdown:hover { background: #f1f5f9; }
        .user-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.85rem; }
        .user-info { text-align: left; }
        .user-name { font-size: 0.85rem; font-weight: 600; color: #1e293b; }
        .user-role { font-size: 0.7rem; color: #64748b; }
        .page-content { padding: 24px; }
        .card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .stat-card { background: #fff; border: 1px solid var(--card-border); border-radius: 12px; padding: 20px; display: flex; align-items: flex-start; gap: 15px; }
        .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .stat-icon.primary { background: #eef2ff; color: #4f46e5; }
        .stat-icon.success { background: #ecfdf5; color: #10b981; }
        .stat-icon.warning { background: #fffbeb; color: #f59e0b; }
        .stat-icon.danger { background: #fef2f2; color: #ef4444; }
        .stat-icon.info { background: #f0fdfa; color: #14b8a6; }
        .stat-icon.purple { background: #faf5ff; color: #a855f7; }
        .stat-content { flex: 1; }
        .stat-label { font-size: 0.72rem; color: #64748b; text-transform: uppercase; font-weight: 500; margin-bottom: 4px; }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
        .stat-change { font-size: 0.72rem; margin-top: 4px; }
        .stat-change.up { color: #10b981; }
        .table th { font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: #64748b; border-bottom: 2px solid var(--card-border); padding: 12px 16px; }
        .table td { padding: 12px 16px; vertical-align: middle; border-bottom: 1px solid var(--card-border); }
        .table tbody tr:hover { background: #f8fafc; }
        .btn { font-size: 0.85rem; font-weight: 500; padding: 8px 16px; border-radius: 8px; }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
        .badge { font-weight: 500; padding: 5px 10px; border-radius: 6px; font-size: 0.72rem; }
        .progress { height: 6px; border-radius: 3px; background: #e2e8f0; }
        .progress-bar { border-radius: 3px; }
        .alert { border: none; border-radius: 8px; }
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .toggle-sidebar { display: block; }
            .user-info { display: none; }
        }
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="sidebar-logo">
                <i class="fas fa-home"></i><span>APS Dream Home</span>
            </a>
            <div class="sidebar-subtitle"><?php echo ucfirst(str_replace('_', ' ', $admin_role)); ?> Panel</div>
        </div>
        <div class="sidebar-section">Main</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/dashboard" class="sidebar-link <?php echo strpos($current_uri, '/admin/dashboard') !== false ? 'active' : ''; ?>"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
        </ul>
        <div class="sidebar-section">CRM & Leads</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/leads" class="sidebar-link <?php echo strpos($current_uri, '/admin/leads') !== false ? 'active' : ''; ?>"><i class="fas fa-bullseye"></i> Leads</a></li>
            <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/campaigns" class="sidebar-link <?php echo strpos($current_uri, '/admin/campaigns') !== false ? 'active' : ''; ?>"><i class="fas fa-bullhorn"></i> Campaigns</a></li>
        </ul>
        <div class="sidebar-section">Properties</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/properties" class="sidebar-link <?php echo strpos($current_uri, '/admin/properties') !== false ? 'active' : ''; ?>"><i class="fas fa-building"></i> All Properties</a></li>
            <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/plots" class="sidebar-link <?php echo strpos($current_uri, '/admin/plots') !== false ? 'active' : ''; ?>"><i class="fas fa-map"></i> Plots / Land</a></li>
            <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/sites" class="sidebar-link <?php echo strpos($current_uri, '/admin/sites') !== false ? 'active' : ''; ?>"><i class="fas fa-map-marker-alt"></i> Sites</a></li>
            <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/bookings" class="sidebar-link <?php echo strpos($current_uri, '/admin/bookings') !== false ? 'active' : ''; ?>"><i class="fas fa-file-contract"></i> Bookings</a></li>
        </ul>
        <div class="sidebar-section">MLM Network</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/team/genealogy" class="sidebar-link"><i class="fas fa-sitemap"></i> Network Tree</a></li>
            <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/associate/dashboard" class="sidebar-link"><i class="fas fa-handshake"></i> Associates</a></li>
        </ul>
        <div class="sidebar-section">Content</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/gallery" class="sidebar-link <?php echo strpos($current_uri, '/admin/gallery') !== false ? 'active' : ''; ?>"><i class="fas fa-images"></i> Gallery</a></li>
            <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/legal-pages" class="sidebar-link <?php echo strpos($current_uri, '/admin/legal-pages') !== false ? 'active' : ''; ?>"><i class="fas fa-file-alt"></i> Legal Pages</a></li>
        </ul>
        <div class="sidebar-section">Team</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/users" class="sidebar-link <?php echo strpos($current_uri, '/admin/users') !== false ? 'active' : ''; ?>"><i class="fas fa-users"></i> Users</a></li>
            <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/employee/dashboard" class="sidebar-link"><i class="fas fa-user-tie"></i> Employees</a></li>
        </ul>
        <div class="sidebar-section">System</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/settings" class="sidebar-link <?php echo strpos($current_uri, '/admin/settings') !== false ? 'active' : ''; ?>"><i class="fas fa-cog"></i> Settings</a></li>
            <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/ai-settings" class="sidebar-link <?php echo strpos($current_uri, '/admin/ai-settings') !== false ? 'active' : ''; ?>"><i class="fas fa-robot"></i> AI Settings</a></li>
        </ul>
        <div class="sidebar-section">Account</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/" target="_blank" class="sidebar-link"><i class="fas fa-external-link-alt"></i> View Website</a></li>
            <li class="sidebar-item"><a href="<?php echo BASE_URL; ?>/admin/logout" class="sidebar-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <nav class="top-navbar">
            <div class="navbar-left">
                <button class="toggle-sidebar" id="toggleSidebar"><i class="fas fa-bars"></i></button>
                <div class="page-breadcrumb">Dashboard / <strong><?php echo htmlspecialchars($page_title ?? 'Dashboard'); ?></strong></div>
            </div>
            <div class="navbar-right">
                <button class="navbar-icon" title="Notifications"><i class="fas fa-bell"></i><span class="badge">3</span></button>
                <button class="navbar-icon" title="Messages"><i class="fas fa-envelope"></i><span class="badge">5</span></button>
                <div class="user-dropdown dropdown">
                    <div class="user-avatar"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($admin_name); ?></div>
                        <div class="user-role"><?php echo ucfirst(str_replace('_', ' ', $admin_role)); ?></div>
                    </div>
                    <i class="fas fa-chevron-down" style="color:#64748b;font-size:0.7rem;"></i>
                </div>
            </div>
        </nav>
        <div class="page-content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php echo $content ?? ''; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('toggleSidebar')?.addEventListener('click', function() { document.getElementById('sidebar').classList.toggle('show'); });
        document.addEventListener('click', function(e) {
            const s = document.getElementById('sidebar'), b = document.getElementById('toggleSidebar');
            if (window.innerWidth < 992 && s.classList.contains('show') && !s.contains(e.target) && !b.contains(e.target)) s.classList.remove('show');
        });
        setTimeout(() => { document.querySelectorAll('.alert').forEach(a => { try { new bootstrap.Alert(a).close(); } catch(e) {} }); }, 5000);
    </script>
</body>
</html>
