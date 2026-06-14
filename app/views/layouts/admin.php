<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'APS Dream Home - Admin'; ?></title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/favicon.png">
    <meta name="description" content="<?php echo $page_description ?? 'Admin Panel'; ?>">

    <!-- Skip to content link (a11y) -->
    <a href="#aps-main-content" class="aps-skip-link">Skip to main content</a>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Admin CSS (page-specific overrides) -->
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/admin/css/admin.css" rel="stylesheet">
    <?php if (isset($extra_css) && $extra_css): ?><!-- Extra page-specific CSS --><?php echo $extra_css; ?><?php endif; ?>
    <style>
        /* Only overrides that admin.css does not cover */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--font); background: var(--body-bg); overflow-x: hidden; }
    </style>

    <!-- CRITICAL: Sidebar functions in HEAD - load before body -->
    <script>
        var APS = APS || {};
        APS._sidebar = null;
        APS._overlay = null;
        APS._touchStartX = 0;

        APS._init = function() {
            APS._sidebar = document.getElementById('sidebarMenu');
            APS._overlay = document.getElementById('sidebarOverlay');
            APS._restoreSections();
            APS._bindSwipe();
            // Auto-close sidebar on mobile when a link is clicked
            if (APS._sidebar) {
                APS._sidebar.querySelectorAll('a[href]').forEach(function(a) {
                    a.addEventListener('click', function() {
                        if (window.innerWidth <= 992) APS.closeSidebar();
                    });
                });
            }
        };

        APS.toggleSidebar = function() {
            if (!APS._sidebar) return;
            APS._sidebar.classList.toggle('show');
            if (APS._overlay) APS._overlay.classList.toggle('active', APS._sidebar.classList.contains('show'));
            document.body.style.overflow = APS._sidebar.classList.contains('show') ? 'hidden' : '';
        };

        APS.closeSidebar = function() {
            if (!APS._sidebar) return;
            APS._sidebar.classList.remove('show');
            if (APS._overlay) APS._overlay.classList.remove('active');
            document.body.style.overflow = '';
        };

        APS.toggleSection = function(id) {
            var ul = document.getElementById(id);
            if (!ul) return;
            var hidden = ul.style.display === 'none';
            ul.style.display = hidden ? '' : 'none';
            var arrow = document.getElementById('arrow-' + id);
            if (arrow) arrow.classList.toggle('collapsed', !hidden);
            var saved = localStorage.getItem('adminSidebarSections');
            var state = saved ? JSON.parse(saved) : {};
            state[id] = hidden;
            localStorage.setItem('adminSidebarSections', JSON.stringify(state));
        };

        APS.toggleAllSections = function() {
            var menus = document.querySelectorAll('.sidebar-menu[id]');
            var anyHidden = Array.from(menus).some(function(el) { return el.style.display === 'none'; });
            menus.forEach(function(el) {
                el.style.display = anyHidden ? '' : 'none';
                var saved = localStorage.getItem('adminSidebarSections');
                var state = saved ? JSON.parse(saved) : {};
                state[el.id] = anyHidden;
                localStorage.setItem('adminSidebarSections', JSON.stringify(state));
            });
            document.querySelectorAll('.sidebar-sec-arrow[id^="arrow-sec-"]').forEach(function(arr) {
                arr.classList.toggle('collapsed', !anyHidden);
            });
        };

        APS._restoreSections = function() {
            var saved = localStorage.getItem('adminSidebarSections');
            if (!saved) return;
            try {
                var state = JSON.parse(saved);
                Object.keys(state).forEach(function(id) {
                    var ul = document.getElementById(id);
                    var arrow = document.getElementById('arrow-' + id);
                    if (ul) ul.style.display = state[id] ? '' : 'none';
                    if (arrow) arrow.classList.toggle('collapsed', !state[id]);
                });
            } catch (e) { /* ignore */ }
        };

        APS._bindSwipe = function() {
            if (!APS._sidebar) return;
            APS._sidebar.addEventListener('touchstart', function(e) {
                APS._touchStartX = e.touches[0].clientX;
            }, { passive: true });
            APS._sidebar.addEventListener('touchend', function(e) {
                var dx = e.changedTouches[0].clientX - APS._touchStartX;
                if (dx < -60) APS.closeSidebar();
            }, { passive: true });
        };

        // Legacy globals for onclick handlers in rbac_sidebar.php
        window.toggleSidebarSection = APS.toggleSection;
        window.toggleAllSidebarSections = APS.toggleAllSections;

        document.addEventListener('DOMContentLoaded', APS._init);
    </script>
</head>

<body>
    <?php
    $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
    $adminName = $_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? 'Admin';
    $adminRole = $_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'admin';
    $base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
    $isActive = function ($path) use ($currentUrl, $base) {
        $full = $base . $path;
        return $currentUrl === $full || strpos($currentUrl, $full . '/') === 0 || strpos($currentUrl, $full . '?') === 0;
    };
    ?>

    <!-- Sidebar (DB-driven via rbac_sidebar.php) -->
    <?php include_once __DIR__ . '/../admin/layouts/rbac_sidebar.php'; ?>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="APS.closeSidebar()"></div>

    <?php
    // Live notification/message counts from DB
    $newLeadsCount = 0;
    $pendingTicketsCount = 0;
    $newInquiriesCount = 0;
    try {
        $db = \App\Core\Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM leads WHERE DATE(created_at) = CURDATE()");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $newLeadsCount = $row['cnt'] ?? 0;
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM support_tickets WHERE status = 'open'");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $pendingTicketsCount = $row['cnt'] ?? 0;
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM inquiries WHERE DATE(created_at) = CURDATE()");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $newInquiriesCount = $row['cnt'] ?? 0;
    } catch (\Exception $e) { /* silent */
    }
    ?>

    <!-- Main Content -->
    <main class="main-content" id="aps-main-content">
        <!-- Top Navigation -->
        <nav class="top-nav">
            <div class="nav-left">
                <button class="toggle-btn" onclick="APS.toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">Admin</li>
                        <li class="breadcrumb-item active" id="breadcrumb-title"><?php echo $page_title ?? 'Dashboard'; ?></li>
                    </ol>
                </nav>
            </div>

            <div class="nav-right">
                <!-- Notifications (Leads) -->
                <button class="nav-icon" id="notification-bell-placeholder" onclick="toggleNotifications()" title="New Leads Today">
                    <i class="fas fa-bell"></i>
                    <span class="badge"><?php echo $newLeadsCount; ?></span>
                </button>

                <!-- Messages / Inquiries -->
                <button class="nav-icon" onclick="toggleMessages()" title="New Inquiries Today">
                    <i class="fas fa-envelope"></i>
                    <span class="badge"><?php echo $newInquiriesCount; ?></span>
                </button>

                <!-- Profile Dropdown (Bootstrap native) -->
                <div class="dropdown" style="position: relative;">
                    <div class="user-box dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="cursor:pointer;">
                        <div class="user-avatar"><?php echo strtoupper(substr($adminName, 0, 1)); ?></div>
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($adminName); ?></div>
                            <div class="user-role"><?php echo ucfirst(str_replace('_', ' ', $adminRole)); ?></div>
                        </div>
                        <i class="fas fa-chevron-down" style="font-size: 0.7rem; color: #64748b;"></i>
                    </div>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a href="<?php echo $base; ?>/admin/profile" class="dropdown-item"><i class="fas fa-user"></i> My Profile</a></li>
                        <li><a href="<?php echo $base; ?>/admin/profile/security" class="dropdown-item"><i class="fas fa-shield-alt"></i> Security</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a href="<?php echo $base; ?>/admin/settings" class="dropdown-item"><i class="fas fa-cog"></i> Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a href="<?php echo $base; ?>/admin/logout" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="page-content" id="page-content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php echo $content ?? ''; ?>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Admin JS -->
    <script src="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/admin/js/admin.js"></script>
    <?php if (isset($extra_js) && $extra_js): ?><!-- Extra page-specific JS --><?php echo $extra_js; ?><?php endif; ?>
    <!-- Frontend enhancements: a11y, forms, toasts, loading -->
    <script defer src="<?php echo BASE_URL; ?>/assets/js/frontend-enhancements.js"></script>

    <!-- Real-time WebSocket Notifications -->
    <script>
        window.NOTIFY_USER = {
            id: <?php echo isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null'); ?>,
            role: '<?php echo isset($_SESSION['admin_id']) ? 'admin' : (isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role'] ?? '', ENT_QUOTES) : (isset($_SESSION['user_id']) ? 'customer' : 'guest')); ?>'
        };
    </script>
    <script defer src="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/js/notification-system.js"></script>

    <script>
        // AJAX Navigation for Sidebar - sidebar remains fixed, only content updates
        // Guard: only initialize once per page (prevents double-registration after reRunScripts)
        if (!window._adminAjaxNavInitialized) {
            window._adminAjaxNavInitialized = true;
            (function() {
                var baseUrl = '<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>';

                function updateActiveSidebar(url) {
                    document.querySelectorAll('.sidebar-link').forEach(function(link) {
                        var href = link.getAttribute('href');
                        if (href && (url === href || url.startsWith(href + '/'))) {
                            link.classList.add('active');
                        } else {
                            link.classList.remove('active');
                        }
                    });
                }

                function loadContent(url, pushState) {
                    if (pushState !== false) {
                        history.pushState({
                            url: url
                        }, '', url);
                    }
                    updateActiveSidebar(url);

                    fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(function(r) {
                            return r.text();
                        })
                        .then(function(html) {
                            var parser = new DOMParser();
                            var doc = parser.parseFromString(html, 'text/html');
                            var newContent = doc.getElementById('page-content');
                            var newTitle = doc.getElementById('breadcrumb-title');
                            if (newContent) {
                                document.getElementById('page-content').innerHTML = newContent.innerHTML;
                                // Re-initialize any scripts in the new content
                                if (typeof reRunScripts === 'function') reRunScripts();
                            }
                            if (newTitle) {
                                document.getElementById('breadcrumb-title').textContent = newTitle.textContent;
                                document.title = newTitle.textContent + ' - APS Dream Home Admin';
                            }
                        })
                        .catch(function(err) {
                            console.error('AJAX nav error:', err);
                        });
                }

                // Handle browser back/forward
                window.addEventListener('popstate', function(e) {
                    if (e.state && e.state.url) {
                        loadContent(e.state.url, false);
                    }
                });

                // Intercept sidebar links
                document.querySelectorAll('.sidebar-link').forEach(function(link) {
                    link.addEventListener('click', function(e) {
                        var href = this.getAttribute('href');
                        if (href && href.startsWith('/') && !href.includes('/logout')) {
                            e.preventDefault();
                            loadContent(href);
                        }
                    });
                });
            })();
        }
    </script>
</body>

</html>

