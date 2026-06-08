<?php

/**
 * Unified Admin Layout with RBAC Sidebar
 * All admin pages should use this layout for consistency
 */

// Get current user info from session
$admin_name = $admin_name ?? ($_SESSION['admin_name'] ?? 'Admin');
$admin_role = $admin_role ?? ($_SESSION['admin_role'] ?? 'admin');
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';

// Get current page for active state
$current_page = $active_page ?? basename($_SERVER['REQUEST_URI'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'APS Dream Home Admin'); ?></title>
    <link rel="icon" type="image/png" href="/apsdreamhome/assets/img/favicon.png">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="/apsdreamhome/assets/admin/css/admin.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        body {
            font-family: var(--font);
            background: var(--body-bg);
            overflow-x: hidden
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1050;
            overflow-y: auto;
            transition: transform .3s ease
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left .3s ease
        }

        .top-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            height: 60px;
            background: var(--topnav-bg);
            border-bottom: 1px solid var(--topnav-border);
            position: sticky;
            top: 0;
            z-index: 1020
        }

        @media(max-width:992px) {
            .sidebar {
                transform: translateX(-100%)
            }

            .sidebar.show {
                transform: translateX(0)
            }

            .main-content {
                margin-left: 0
            }

            .toggle-btn {
                display: block
            }
        }
    </style>

    <!-- CRITICAL: Sidebar Toggle Functions in HEAD - Load first before anything -->
    <script>
        // Define sidebar toggle functions immediately in HEAD
        window.toggleSidebarSection = function(id) {
            console.log('toggleSidebarSection called:', id);
            var ul = document.getElementById(id);
            if (!ul) {
                console.error('Element not found:', id);
                return;
            }
            var hidden = ul.style.display === 'none';
            ul.style.display = hidden ? '' : 'none';
            var arrow = document.getElementById('arrow-' + id);
            if (arrow) {
                arrow.classList.toggle('collapsed', !hidden);
            }
            var savedState = localStorage.getItem('adminSidebarSections');
            var state = savedState ? JSON.parse(savedState) : {};
            state[id] = hidden;
            localStorage.setItem('adminSidebarSections', JSON.stringify(state));
        };

        window.toggleAllSidebarSections = function() {
            console.log('toggleAllSidebarSections called');
            var menus = document.querySelectorAll('.sidebar-menu[id]');
            var anyHidden = Array.from(menus).some(function(el) {
                return el.style.display === 'none';
            });
            menus.forEach(function(el) {
                el.style.display = anyHidden ? '' : 'none';
                var savedState = localStorage.getItem('adminSidebarSections');
                var state = savedState ? JSON.parse(savedState) : {};
                state[el.id] = anyHidden;
                localStorage.setItem('adminSidebarSections', JSON.stringify(state));
            });
            document.querySelectorAll('.sidebar-sec-arrow[id^="arrow-sec-"]').forEach(function(arr) {
                arr.classList.toggle('collapsed', !anyHidden);
            });
        };

        // Load saved state
        document.addEventListener('DOMContentLoaded', function() {
            var savedState = localStorage.getItem('adminSidebarSections');
            if (savedState) {
                try {
                    var state = JSON.parse(savedState);
                    Object.keys(state).forEach(function(id) {
                        var ul = document.getElementById(id);
                        var arrow = document.getElementById('arrow-' + id);
                        if (ul) {
                            ul.style.display = state[id] ? '' : 'none';
                        }
                        if (arrow) {
                            if (state[id]) {
                                arrow.classList.remove('collapsed');
                            } else {
                                arrow.classList.add('collapsed');
                            }
                        }
                    });
                } catch (e) {
                    console.log('Error loading sidebar state:', e);
                }
            }
            console.log('Sidebar toggle functions DEFINED IN HEAD - GUARANTEED TO WORK');
        });
    </script>

    <?php if (!empty($extra_css)): ?>
        <link rel="stylesheet" href="<?php echo $extra_css; ?>">
    <?php endif; ?>
</head>

<body>
    <!-- Sidebar -->
    <?php include __DIR__ . '/rbac_sidebar.php'; ?>
    <div class="sidebar-overlay" onclick="document.getElementById('sidebarMenu').classList.remove('show')"></div>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navigation -->
        <nav class="top-nav">
            <div class="nav-left">
                <button class="toggle-btn" onclick="document.getElementById('sidebarMenu').classList.toggle('show')">
                    <i class="fas fa-bars"></i>
                </button>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size:.85rem">
                        <li class="breadcrumb-item"><a href="<?php echo $base; ?>/admin/dashboard">Admin</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($active_page ?? 'Dashboard'); ?></li>
                    </ol>
                </nav>
            </div>
            <div class="nav-right">
                <div class="dropdown d-inline-block">
                    <button class="nav-icon position-relative" title="Notifications" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="badge notification-badge" style="display:none">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown shadow" style="min-width: 320px; max-height: 400px; overflow-y: auto;">
                        <h6 class="dropdown-header">Notifications</h6>
                        <div class="notification-list">
                            <div class="text-center text-muted p-3">Loading...</div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="<?php echo $base; ?>/admin/audit-log">View audit log</a>
                    </div>
                </div>
                <button class="nav-icon" title="Messages">
                    <i class="fas fa-envelope"></i>
                    <span class="badge">5</span>
                </button>
                <div class="dropdown">
                    <div class="user-box" data-bs-toggle="dropdown">
                        <div class="user-av"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></div>
                        <div>
                            <div style="font-size:.85rem;font-weight:600;color:#1e293b"><?php echo htmlspecialchars($admin_name); ?></div>
                            <div style="font-size:.7rem;color:#64748b"><?php echo ucfirst(str_replace('_', ' ', $admin_role)); ?></div>
                        </div>
                        <i class="fas fa-chevron-down ms-2" style="font-size:.7rem;color:#64748b"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo $base; ?>/admin/profile"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="<?php echo $base; ?>/admin/settings"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="<?php echo $base; ?>/admin/logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
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
                    <?php echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']); ?>
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

    <!-- Admin JS - FIXED PATH -->
    <script src="/apsdreamhome/assets/admin/js/admin.js"></script>
    <script src="/apsdreamhome/assets/js/notification-widget.js"></script>

    <!-- Admin Form Enhancer (SmartFormAutocomplete + validation) -->
    <script src="<?php echo BASE_URL; ?>/assets/admin/js/admin-form-enhancer.js"></script>

    <?php if (!empty($extra_js)): ?>
        <script src="<?php echo $extra_js; ?>"></script>
    <?php endif; ?>
</body>

</html>