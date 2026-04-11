<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Associate Dashboard - APS Dream Home'; ?></title>
    <meta name="description" content="<?php echo $page_description ?? 'Associate Portal'; ?>">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

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
            background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);
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
            color: #a5b4fc;
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
            color: #c7d2fe;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        /* Quick Stats Cards */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .stat-icon.blue {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .stat-icon.green {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .stat-icon.orange {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .stat-icon.purple {
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #64748b;
        }

        .stat-trend {
            font-size: 0.8rem;
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stat-trend.up {
            color: #10b981;
        }

        .stat-trend.down {
            color: #ef4444;
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
            <div class="sidebar-sub">Associate Portal</div>
        </div>

        <!-- User Info Card -->
        <div class="user-card">
            <div class="user-avatar">
                <i class="fas fa-handshake"></i>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['associate_name'] ?? 'Associate'); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($_SESSION['associate_role'] ?? 'Associate'); ?></div>
        </div>

        <!-- Main Menu -->
        <div class="sidebar-section">Main Menu</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/associate/dashboard" class="sidebar-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/associate/genealogy" class="sidebar-link <?php echo $current_page === 'genealogy' ? 'active' : ''; ?>">
                    <i class="fas fa-sitemap"></i>
                    <span>My Network</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/associate/leads" class="sidebar-link <?php echo $current_page === 'leads' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>My Leads</span>
                    <span class="sidebar-badge">12</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/associate/properties" class="sidebar-link <?php echo $current_page === 'properties' ? 'active' : ''; ?>">
                    <i class="fas fa-building"></i>
                    <span>My Properties</span>
                </a>
            </li>
        </ul>

        <!-- Earnings -->
        <div class="sidebar-section">Earnings</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/associate/commissions" class="sidebar-link <?php echo $current_page === 'commissions' ? 'active' : ''; ?>">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Commissions</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/associate/wallet" class="sidebar-link <?php echo $current_page === 'wallet' ? 'active' : ''; ?>">
                    <i class="fas fa-wallet"></i>
                    <span>Wallet</span>
                </a>
            </li>
        </ul>

        <!-- Account -->
        <div class="sidebar-section">Account</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/associate/profile" class="sidebar-link <?php echo $current_page === 'profile' ? 'active' : ''; ?>">
                    <i class="fas fa-user-cog"></i>
                    <span>My Profile</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/associate/bank-details" class="sidebar-link <?php echo $current_page === 'bank-details' ? 'active' : ''; ?>">
                    <i class="fas fa-university"></i>
                    <span>Bank Details</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/associate/settings" class="sidebar-link <?php echo $current_page === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/associate/logout" class="sidebar-link text-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div>
                <h1 class="page-title"><?php echo $page_title ?? 'Dashboard'; ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/associate/dashboard">Home</a></li>
                        <li class="breadcrumb-item active"><?php echo $page_title ?? 'Dashboard'; ?></li>
                    </ol>
                </nav>
            </div>
            <div class="header-actions">
                <button class="btn-icon" title="Notifications">
                    <i class="fas fa-bell"></i>
                </button>
                <button class="btn-icon" title="Messages">
                    <i class="fas fa-envelope"></i>
                </button>
                <a href="<?php echo BASE_URL; ?>/associate/profile" class="btn-icon" title="Profile">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle Script -->
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
