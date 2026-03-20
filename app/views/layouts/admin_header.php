<?php

/**
 * Admin Header Layout
 * Dedicated header for admin pages
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . '/admin/login');
    exit;
}

// Get admin info
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$admin_email = $_SESSION['admin_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/admin/css/admin.css?v=<?= time() ?>">

    <style>
        :root {
            --primary-color: #764ba2;
            --secondary-color: #667eea;
            --accent-color: #f093fb;
            --dark-bg: #1a1a2e;
            --light-bg: #f8f9ff;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            --primary-gradient: linear-gradient(135deg, #764ba2 0%, #f093fb 100%);
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .admin-header {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 1rem 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--glass-shadow);
        }

        .admin-logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .admin-nav {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .admin-nav a:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .theme-toggle {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .quick-actions {
            display: flex;
            gap: 0.5rem;
        }

        .quick-action-btn {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(118, 75, 162, 0.4);
        }
    </style>
</head>

<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <a href="<?= BASE_URL ?>admin/dashboard" class="admin-logo">
                        <i class="fas fa-home"></i>
                        APS Dream Admin
                    </a>
                </div>
                <div class="col-md-6">
                    <nav class="admin-nav">
                        <a href="<?= BASE_URL ?>admin/dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="<?= BASE_URL ?>admin/properties">
                            <i class="fas fa-building"></i> Properties
                        </a>
                        <a href="<?= BASE_URL ?>admin/users">
                            <i class="fas fa-users"></i> Users
                        </a>
                        <a href="<?= BASE_URL ?>admin/reports">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                        <a href="<?= BASE_URL ?>admin/settings">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </nav>
                </div>
                <div class="col-md-3 text-end">
                    <div class="admin-user">
                        <div class="quick-actions">
                            <button class="quick-action-btn" onclick="window.open('<?= BASE_URL ?>', '_blank')">
                                <i class="fas fa-external-link-alt"></i> View Site
                            </button>
                        </div>
                        <div class="admin-avatar">
                            <?= strtoupper(substr($admin_name, 0, 2)) ?>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-link text-white dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> <?= htmlspecialchars($admin_name) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/profile">
                                        <i class="fas fa-user"></i> Profile
                                    </a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/settings">
                                        <i class="fas fa-cog"></i> Settings
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/logout">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container-fluid">
        <?= $content ?>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Theme Toggle
        function toggleTheme() {
            document.body.classList.toggle('dark-theme');
            localStorage.setItem('admin-theme',
                document.body.classList.contains('dark-theme') ? 'dark' : 'light'
            );
        }

        // Load saved theme
        if (localStorage.getItem('admin-theme') === 'dark') {
            document.body.classList.add('dark-theme');
        }

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Auto-refresh dashboard data
        setInterval(function() {
            // Refresh stats every 30 seconds
            if (window.location.pathname === '/admin/dashboard') {
                location.reload();
            }
        }, 30000);
    </script>
</body>

</html>