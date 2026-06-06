<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'My Account - APS Dream Home'; ?></title>
    <meta name="description" content="<?php echo $page_description ?? 'Customer Portal'; ?>">

    <!-- CSRF Token -->
    <?php
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES); ?>">

    <!-- Skip to content link (a11y) -->
    <a href="#aps-main-content" class="aps-skip-link">Skip to main content</a>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/frontend-enhancements.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #f8fafc;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #334155 100%);
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-logo i {
            font-size: 1.3rem;
            color: #60a5fa;
        }

        .sidebar-sub {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.7rem;
            margin-top: 4px;
        }

        .user-card {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 3px;
        }

        .user-role {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .sidebar-section {
            padding: 15px 20px 5px;
            font-size: 0.7rem;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0 10px;
            margin: 0;
        }

        .sidebar-item {
            margin-bottom: 2px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .sidebar-link i {
            width: 22px;
            margin-right: 10px;
            font-size: 1rem;
        }

        .sidebar-badge {
            margin-left: auto;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* Top Header */
        .top-header {
            background: #fff;
            padding: 15px 25px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .page-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .breadcrumb {
            margin: 0;
            font-size: 0.85rem;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .btn-icon:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .content-wrapper {
            padding: 25px;
        }

        /* Mobile Toggle */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 1.2rem;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .top-header {
                padding-left: 70px;
            }
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle Button (Mobile) -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo BASE_URL; ?>" class="sidebar-logo">
                <i class="fas fa-home"></i>
                <span>APS Dream Home</span>
            </a>
            <div class="sidebar-sub"><?= __('cust_sidebar_sub', null, 'My Account Portal') ?></div>
        </div>

        <!-- User Info Card -->
        <div class="user-card">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Customer'); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'customer@example.com'); ?></div>
        </div>

        <!-- Main Menu -->
        <div class="sidebar-section">Main Menu</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/user/dashboard" class="sidebar-link <?php echo ($current_page ?? '') === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span><?= __('nav_dashboard', null, 'Dashboard') ?></span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/user/properties" class="sidebar-link <?php echo ($current_page ?? '') === 'properties' ? 'active' : ''; ?>">
                    <i class="fas fa-building"></i>
                    <span><?= __('nav_my_properties', null, 'My Properties') ?></span>
                    <span class="sidebar-badge">3</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/user/inquiries" class="sidebar-link <?php echo ($current_page ?? '') === 'inquiries' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i>
                    <span><?= __('nav_my_inquiries', null, 'My Inquiries') ?></span>
                    <span class="sidebar-badge">5</span>
                </a>
            </li>
        </ul>

        <!-- Services -->
        <div class="sidebar-section">Services</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/properties" class="sidebar-link <?php echo ($current_page ?? '') === 'browse' ? 'active' : ''; ?>">
                    <i class="fas fa-search"></i>
                    <span><?= __('nav_browse_properties', null, 'Browse Properties') ?></span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/list-property" class="sidebar-link <?php echo ($current_page ?? '') === 'post' ? 'active' : ''; ?>">
                    <i class="fas fa-plus-circle"></i>
                    <span><?= __('nav_post_property', null, 'Post Property') ?></span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/financial-services" class="sidebar-link <?php echo ($current_page ?? '') === 'loan' ? 'active' : ''; ?>">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span><?= __('nav_home_loan', null, 'Home Loan') ?></span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/interior-design" class="sidebar-link <?php echo ($current_page ?? '') === 'interior' ? 'active' : ''; ?>">
                    <i class="fas fa-couch"></i>
                    <span><?= __('nav_interior_design', null, 'Interior Design') ?></span>
                </a>
            </li>
        </ul>

        <!-- Account -->
        <div class="sidebar-section">Account</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/user/profile" class="sidebar-link <?php echo ($current_page ?? '') === 'profile' ? 'active' : ''; ?>">
                    <i class="fas fa-user-cog"></i>
                    <span><?= __('nav_my_profile', null, 'My Profile') ?></span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/user/bank-details" class="sidebar-link <?php echo ($current_page ?? '') === 'bank' ? 'active' : ''; ?>">
                    <i class="fas fa-university"></i>
                    <span><?= __('cust_bank_details', null, 'Bank Details') ?></span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/user/settings" class="sidebar-link <?php echo ($current_page ?? '') === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span><?= __('nav_settings', null, 'Settings') ?></span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/user/logout" class="sidebar-link text-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    <span><?= __('nav_logout', null, 'Logout') ?></span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div>
                <h1 class="page-title"><?php echo preg_replace('/\s*-\s*APS Dream Home\s*$/', '', $page_title ?? 'Dashboard'); ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/user/dashboard"><?= __('nav_home', null, 'Home') ?></a></li>
                        <li class="breadcrumb-item active"><?php echo $page_title ?? 'Dashboard'; ?></li>
                    </ol>
                </nav>
            </div>
            <div class="header-actions">
                <a href="<?= BASE_URL ?>/user/notifications" class="btn btn-sm btn-outline-primary position-relative me-2">
                    <i class="fas fa-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="font-size:9px;">0</span>
                </a>
                <button class="btn-icon" title="Messages">
                    <i class="fas fa-envelope"></i>
                </button>
                <a href="<?php echo BASE_URL; ?>/user/profile" class="btn-icon" title="Profile">
                    <i class="fas fa-user"></i>
                </a>
            </div>
        </header>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <?php echo $content ?? ''; ?>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="<?php echo BASE_URL; ?>/assets/js/frontend-enhancements.js"></script>

    <!-- Real-time WebSocket Notifications -->
    <script>
        window.NOTIFY_USER = {
            id: <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 'null'); ?>,
            role: '<?php echo isset($_SESSION['admin_id']) ? 'admin' : (isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role'], ENT_QUOTES) : (isset($_SESSION['user_id']) ? 'customer' : 'guest')); ?>'
        };
    </script>
    <script defer src="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/js/notification-system.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
function checkNotifications() {
    fetch('<?= BASE_URL ?>/api/notifications/unread-count')
        .then(r => r.json())
        .then(d => { const b = document.getElementById('notifBadge'); if(b) { b.textContent = d.count || 0; b.style.display = (d.count > 0) ? 'inline' : 'none'; } })
        .catch(() => {});
}
document.addEventListener('DOMContentLoaded', checkNotifications);
setInterval(checkNotifications, 30000);
</script>
<script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        // Close sidebar on window resize if open
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1024) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.querySelector('.sidebar-overlay');
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });
    </script>
</body>
</html>
