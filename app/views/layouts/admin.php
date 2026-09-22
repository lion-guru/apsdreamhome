<?php
$GLOBALS['_html_doc_started'] = true;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php
        $tenantTitleName = class_exists('\App\Core\Middleware\TenantContext') ? \App\Core\Middleware\TenantContext::getName() : 'APS Dream Home';
        echo $page_title ?? ($tenantTitleName . ' - Admin');
    ?></title>
    <link rel="icon" type="image/jpeg" href="<?= BASE_URL ?>/assets/images/logo/apslogonew.jpg">
    <meta name="description" content="<?php echo $page_description ?? 'Admin Panel'; ?>">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">window.BASE_URL = '<?= defined('BASE_URL') ? BASE_URL : '' ?>';</script>
    <?php if (isset($_SESSION['admin_id']) || isset($_SESSION['user_id'])): ?>
    <meta name="user-id" content="<?= (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0) ?>">
    <meta name="user-role" content="<?= htmlspecialchars($_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'admin') ?>">
    <?php
        // Generate JWT token for WebSocket authentication
        $jwtSecret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?? 'apsdreamhome-dev-key-2025-super-secret';
        $payload = [
            'user_id' => (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0),
            'role' => $_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'admin',
            'iat' => time(),
            'exp' => time() + 3600 * 24 // 24 hours
        ];
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $encodedHeader = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $encodedPayload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', "$encodedHeader.$encodedPayload", $jwtSecret, true);
        $encodedSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        $jwtToken = "$encodedHeader.$encodedPayload.$encodedSignature";
    ?>
    <meta name="ws-token" content="<?= htmlspecialchars($jwtToken) ?>">
    <?php endif; ?>
    <!-- WebSocket URL for real-time notifications -->
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">window.WS_URL = '<?= defined('WS_URL') ? WS_URL : 'ws://localhost:8080' ?>';</script>

    <!-- Preconnect for CDN performance -->
    
    
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Admin Theme (consolidated: admin.css + responsive-fixes + uiux-fixes) -->
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/admin/css/admin-theme.css?v=1" rel="stylesheet">
    <!-- Shared aps-cp-* component system (systematic UI: cards, stats, empty, pills, wizard) -->
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/css/consolidated/aps-components.css?v=2" rel="stylesheet">
    <!-- Utility classes (display, min-width, visibility) -->
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/css/utils.css?v=1" rel="stylesheet">
    <!-- Notification system CSS (dropdowns, toasts, popups) -->
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/css/notification-system.css" rel="stylesheet">
    <!-- Mobile Responsive (all pages) -->
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/css/mobile-responsive.css?v=3" rel="stylesheet">
    <?php if (isset($extra_css) && $extra_css): ?><!-- Extra page-specific CSS --><?php echo e($extra_css); ?><?php endif; ?>
    <?php if (class_exists('\App\Core\Middleware\TenantContext')): ?>
    <?php $tcColors = \App\Core\Middleware\TenantContext::getColors(); $tcLogo = \App\Core\Middleware\TenantContext::getLogo(); ?>
    <style>
        :root {
            --tenant-primary: <?= htmlspecialchars($tcColors['primary'] ?? '') ?>;
            --tenant-secondary: <?= htmlspecialchars($tcColors['secondary'] ?? '') ?>;
        }
        .navbar-brand img[src*="favicon"], .navbar-brand img:not([src]) { max-height: 36px; }
        .sidebar-heading { background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-secondary)); }
        .sidebar .nav-link.active, .sidebar-link.active { background: linear-gradient(135deg, rgba(102,126,234,0.15), rgba(118,75,162,0.1)); border-left: 3px solid var(--tenant-primary); }
        .sidebar .nav-link:hover:not(.active), .sidebar-link:hover:not(.active) { background: rgba(102,126,234,0.08); }
        .btn-tenant-primary { background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-secondary)); border: none; color: #fff; }
        .btn-tenant-primary:hover { opacity: 0.9; color: #fff; }
        .navbar.bg-primary, .top-header.bg-primary { background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-secondary)) !important; }
        .badge-tenant { background: var(--tenant-primary); color: #fff; }
        .tenant-brand-text { color: var(--tenant-primary); }
        a.tenant-link { color: var(--tenant-primary); }
        a.tenant-link:hover { color: var(--tenant-secondary); }
        .card-header.bg-primary, .card-header.bg-tenant { background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-secondary)) !important; color: #fff; }
        .table thead.tenant-header th { background: var(--tenant-primary); color: #fff; border-color: var(--tenant-primary); }
        .page-item.active .page-link { background: var(--tenant-primary); border-color: var(--tenant-primary); }
        .page-item.active .page-link:hover { background: var(--tenant-secondary); border-color: var(--tenant-secondary); }
        .progress-bar.tenant-progress { background: linear-gradient(90deg, var(--tenant-primary), var(--tenant-secondary)); }
        .btn-primary.tenant-btn { background: var(--tenant-primary); border-color: var(--tenant-primary); }
        .btn-primary.tenant-btn:hover { background: var(--tenant-secondary); border-color: var(--tenant-secondary); }
        .btn-outline-primary.tenant-btn { color: var(--tenant-primary); border-color: var(--tenant-primary); }
        .btn-outline-primary.tenant-btn:hover { background: var(--tenant-primary); border-color: var(--tenant-primary); color: #fff; }
        .dropdown-item:active, .dropdown-item.active { background: var(--tenant-primary); }
        .alert-tenant { background: rgba(102,126,234,0.12); border-left: 4px solid var(--tenant-primary); color: inherit; }
        .list-group-item.active { background: var(--tenant-primary); border-color: var(--tenant-primary); }
        .modal-header.bg-tenant { background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-secondary)); color: #fff; }
        ::selection { background: rgba(102,126,234,0.3); }
        .form-control:focus, .form-select:focus { border-color: var(--tenant-primary); box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25); }
        .nav-tabs .nav-link.active { color: var(--tenant-primary); border-bottom: 2px solid var(--tenant-primary); }
        .nav-pills .nav-link.active { background: var(--tenant-primary); }
    </style>
    <?php endif; ?>
</head>

<body class="aps-admin-body">
    <!-- Skip to content link (a11y) -->
    <a href="#aps-main-content" class="aps-skip-link">Skip to main content</a>

    <!-- Sidebar Toggle Button (Mobile) -->
    <button class="sidebar-toggle" onclick="APS.toggleSidebar()" aria-label="Toggle sidebar menu">
        <i class="fas fa-bars" aria-hidden="true"></i>
    </button>

        <!-- CRITICAL: Sidebar functions in HEAD - load before body -->
        <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
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
                var anyHidden = Array.from(menus).some(function(el) {
                    return el.style.display === 'none';
                });
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
                } catch (e) {
                    /* ignore */ }
            };

            APS._bindSwipe = function() {
                if (!APS._sidebar) return;
                APS._sidebar.addEventListener('touchstart', function(e) {
                    APS._touchStartX = e.touches[0].clientX;
                }, {
                    passive: true
                });
                APS._sidebar.addEventListener('touchend', function(e) {
                    var dx = e.changedTouches[0].clientX - APS._touchStartX;
                    if (dx < -60) APS.closeSidebar();
                }, {
                    passive: true
                });
            };

            // Legacy globals for onclick handlers in rbac_sidebar.php
            window.toggleSidebarSection = APS.toggleSection;
            window.toggleAllSidebarSections = APS.toggleAllSections;

            document.addEventListener('DOMContentLoaded', APS._init);
        </script>
</head>

<body>
    <?php
    $currentUrl = esc_url($_SERVER['REQUEST_URI'] ?? '');
    $adminName = $_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? 'Admin';
    $adminRole = $_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'admin';
    $base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
    $isActive = function ($path) use ($currentUrl, $base) {
        $full = $base . $path;
        return $currentUrl === $full || strpos($currentUrl, $full . '/') === 0 || strpos($currentUrl, $full . '?') === 0;
    };
    ?>

    <!-- Sidebar (DB-driven via rbac_sidebar.php) -->
    <?php include_once __DIR__ . '/../admin/layouts/rbac_sidebar.php'; ?>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="APS.closeSidebar()"></div>

    <?php
    // Live notification/message counts from DB (cached 120s to avoid N+1 on every page load)
    $newLeadsCount = 0;
    $pendingTicketsCount = 0;
    $newInquiriesCount = 0;
    try {
        $newLeadsCount = \App\Core\Cache::remember('admin_notify_leads_today', function () {
            $db = \App\Core\Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT COUNT(*) as cnt FROM leads WHERE DATE(created_at) = CURDATE()");
            return (int)($stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);
        }, 120);
        $pendingTicketsCount = \App\Core\Cache::remember('admin_notify_tickets_open', function () {
            $db = \App\Core\Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT COUNT(*) as cnt FROM support_tickets WHERE status = 'open'");
            return (int)($stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);
        }, 120);
        $newInquiriesCount = \App\Core\Cache::remember('admin_notify_inquiries_today', function () {
            $db = \App\Core\Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT COUNT(*) as cnt FROM inquiries WHERE DATE(created_at) = CURDATE()");
            return (int)($stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);
        }, 120);
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
                <!-- Notifications (Leads) â€” replaced by notification-system.js -->
                <button class="nav-icon" id="notification-bell-placeholder" onclick="toggleNotifications()" title="New Leads Today">
                    <i class="fas fa-bell"></i>
                    <span class="badge"><?php echo e($newLeadsCount); ?></span>
                </button>

                <!-- Messages / Inquiries -->
                <button class="nav-icon" onclick="toggleMessages()" title="New Inquiries Today">
                    <i class="fas fa-envelope"></i>
                    <span class="badge"><?php echo e($newInquiriesCount); ?></span>
                </button>

                <!-- Omni-Search Trigger (Ctrl+K) -->
                <button class="nav-icon" id="omniSearchTrigger" onclick="openOmniSearch()" title="Search everywhere (Ctrl+K)">
                    <i class="fas fa-search"></i>
                    <span class="badge" id="omniSearchBadge" style="display:none;">5</span>
                </button>

                <!-- My Workspace (role hub) -->
                <a class="nav-icon" href="<?php echo e($base); ?>/admin/workspace-hubs" title="My Workspace">
                    <i class="fas fa-th-large"></i>
                </a>

                <!-- Profile Dropdown (Bootstrap native) -->
                <div class="dropdown">
                    <div class="user-box dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" >
                        <div class="user-avatar"><?php echo strtoupper(substr($adminName, 0, 1)); ?></div>
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($adminName ?? ''); ?></div>
                            <div class="user-role"><?php echo ucfirst(str_replace('_', ' ', $adminRole)); ?></div>
                        </div>
                        <i class="fas fa-chevron-down"></i>
                    </div>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a href="<?php echo e($base); ?>/admin/profile" class="dropdown-item"><i class="fas fa-user"></i> My Profile</a></li>
                        <li><a href="<?php echo e($base); ?>/admin/profile/security" class="dropdown-item"><i class="fas fa-shield-alt"></i> Security</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a href="<?php echo e($base); ?>/admin/settings" class="dropdown-item"><i class="fas fa-cog"></i> Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a href="<?php echo e($base); ?>/admin/logout" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="page-content" id="page-content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8');
                    unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8');
                    unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php
            // Tenant status banner â€” read-only mode for suspended/cancelled tenants
            $tenantBanner = null;
            if (class_exists('\App\Core\Middleware\TenantContext') && class_exists('\App\Services\TenantService')) {
                try {
                    $tid = \App\Core\Middleware\TenantContext::getId();
                    if ($tid > 1) {
                        $tenant = \App\Services\TenantService::getInstance()->getById($tid);
                        if ($tenant && in_array($tenant['status'] ?? '', ['suspended', 'cancelled', 'trial_expired'])) {
                            $tenantBanner = $tenant['status'];
                        }
                    }
                } catch (\Throwable $e) { error_log('admin tenant banner: ' . $e->getMessage()); }
            }
            ?>
            <?php if ($tenantBanner === 'suspended'): ?>
                <div >
                    <i class="fas fa-ban"></i>
                    <div>
                        <strong>Account Suspended</strong>
                        <div >Your account has been suspended. All create/edit/delete operations are disabled. Contact support to restore access.</div>
                    </div>
                </div>
            <?php elseif ($tenantBanner === 'cancelled'): ?>
                <div >
                    <i class="fas fa-times-circle"></i>
                    <div>
                        <strong>Account Cancelled</strong>
                        <div >Your subscription has been cancelled. Your data is in read-only mode. Contact support to reactivate.</div>
                    </div>
                </div>
            <?php elseif ($tenantBanner === 'trial_expired'): ?>
                <div >
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>Trial Expired</strong>
                        <div >Your free trial has expired. Upgrade your plan to continue creating and editing data. <a href="<?= $base ?>/admin/billing" >View Plans</a></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php echo $content ?? ''; ?>
        </div>
    </main>

    <!-- jQuery (for DataTables, modals, and admin plugins) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    <!-- Admin JS -->
    <script src="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/admin/js/admin.js"></script>
    <!-- Admin Form Enhancer (IFSC, pincode auto-fill, form validation, CSRF) -->
    <script src="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/admin/js/admin-form-enhancer.js"></script>
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    function toggleNotifications() {
        var dropdown = document.getElementById('notificationDropdown');
        if (dropdown) { dropdown.classList.toggle('show'); return; }
        var panel = document.getElementById('notification-panel');
        if (panel) { panel.classList.toggle('show'); return; }
        window.location.href = '<?php echo $base ?? BASE_URL; ?>/admin/notifications';
    }
    function toggleMessages() {
        window.location.href = '<?php echo $base ?? BASE_URL; ?>/admin/inquiries';
    }
    </script>
    <?php if (isset($extra_js) && $extra_js): ?><!-- Extra page-specific JS --><?php echo e($extra_js); ?><?php endif; ?>
        <!-- Frontend enhancements: a11y, forms, toasts, loading -->
        <script src="<?= BASE_URL ?>/assets/js/frontend-enhancements.js"></script>

        <!-- Real-time Notifications (SSE stream + polling fallback) -->
        <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
            window.NOTIFY_USER = {
                id: <?php echo isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null'); ?>,
                role: '<?php echo isset($_SESSION['admin_id']) ? 'admin' : (isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role'] ?? '', ENT_QUOTES) : (isset($_SESSION['user_id']) ? 'customer' : 'guest')); ?>'
            };
        </script>
        <script defer src="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/js/notification-system.js"></script>
        <!-- WebSocket Notification Widget (real-time push) -->
        <link href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/css/notification-widget.css" rel="stylesheet">
        <script defer src="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/js/notification-widget.js"></script>

        <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
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

                    // Intercept sidebar links (DISABLED - fallback to normal navigation)
                    /*
                    document.querySelectorAll('.sidebar-link').forEach(function(link) {
                        link.addEventListener('click', function(e) {
                            var href = this.getAttribute('href');
                            if (href && href.startsWith('/') && !href.includes('/logout')) {
                                e.preventDefault();
                                loadContent(href);
                            }
                        });
                    });
                    */
                })();
            }
        </script>
        
        <!-- Keyboard Shortcuts -->
        <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        // Global loading spinner utility
        function showLoader() {
            if (!document.getElementById('global-loader')) {
                const loader = document.createElement('div');
                loader.id = 'global-loader';
                loader.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
                document.body.appendChild(loader);
                document.body.classList.add('loading-shim');
            }
        }
        function hideLoader() {
            const loader = document.getElementById('global-loader');
            if (loader) { loader.remove(); document.body.classList.remove('loading-shim'); }
        }
        
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K = Search (Omni-Search)
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const modalEl = document.getElementById('omniSearchModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                    setTimeout(() => {
                        const input = document.getElementById('omniSearchInput');
                        if (input) { input.focus(); input.select(); }
                    }, 150);
                }
            }
            // Ctrl/Cmd + N = New booking (on bookings page)
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                const url = window.location.pathname;
                if (url.includes('/admin/bookings')) { window.location.href = '<?= BASE_URL ?>/admin/bookings/create'; }
            }
            // Ctrl/Cmd + S = Save form
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const saveBtn = document.querySelector('button[type="submit"], button.save-btn, .btn-save');
                if (saveBtn) { saveBtn.click(); }
            }
            // Ctrl/Cmd + E = Export (on pages with export)
            if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
                e.preventDefault();
                const exportBtn = document.querySelector('a[href*="export"]');
                if (exportBtn) { exportBtn.click(); }
            }
        });
        </script>

        <!-- Omni-Search JavaScript -->
        <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        (function() {
            const modalEl = document.getElementById('omniSearchModal');
            const inputEl = document.getElementById('omniSearchInput');
            const resultsEl = document.getElementById('omniSearchResults');
            const emptyEl = document.getElementById('omniSearchEmpty');
            const triggerBtn = document.getElementById('omniSearchTrigger');
            if (!modalEl || !inputEl || !resultsEl) return;
            let debounceTimer = null;
            let modalInstance = null;
            let selectedIndex = -1;
            let currentResults = [];

            function openOmniSearch() {
                if (!modalInstance) {
                    modalInstance = new bootstrap.Modal(modalEl);
                }
                modalInstance.show();
                // Focus input after modal animation
                setTimeout(() => {
                    inputEl.focus();
                    inputEl.select();
                }, 150);
            }

            function closeOmniSearch() {
                if (modalInstance) {
                    modalInstance.hide();
                }
                inputEl.value = '';
                renderResults([]);
            }

            function renderResults(results) {
                currentResults = results;
                selectedIndex = -1;
                
                if (results.length === 0) {
                    if (inputEl.value.trim().length >= 2) {
                        resultsEl.innerHTML = `
                            <div class="text-center text-muted py-4" id="omniSearchEmpty">
                                <i class="fas fa-search fa-2x mb-2" style="color: #334155;"></i>
                                <p class="fw-medium" style="color: #475569;">No results found</p>
                                <p class="small" style="color: #64748b;">Try a different search term</p>
                            </div>
                        `;
                    } else {
                        resultsEl.innerHTML = `
                            <div class="text-center text-muted py-5" id="omniSearchEmpty">
                                <i class="fas fa-search fa-3x mb-3" style="color: #334155;"></i>
                                <p class="fw-medium" style="color: #475569;">Start typing to search plots, customers, bookings...</p>
                                <p class="small" style="color: #64748b;">Search by plot number, customer name, booking number, email, phone, or referral code</p>
                            </div>
                        `;
                    }
                    return;
                }

                // Group by category
                const byCategory = {};
                results.forEach(r => {
                    if (!byCategory[r.category]) byCategory[r.category] = [];
                    byCategory[r.category].push(r);
                });

                let html = '';
                const categoryOrder = ['Plots', 'Bookings', 'Customers', 'Associates'];
                
                categoryOrder.forEach(cat => {
                    if (!byCategory[cat]) return;
                    const icon = byCategory[cat][0]?.icon || 'fas fa-circle';
                    html += `
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="text-muted small text-uppercase fw-bold me-2" style="letter-spacing: 0.05em;">${cat}</span>
                                <div class="flex-grow-1 divider"></div>
                            </div>
                            <div class="list-group list-group-flush">
                    `;
                    byCategory[cat].forEach((item, idx) => {
                        html += `
                            <a href="${item.url}" class="list-group-item list-group-item-action px-3 py-2 omni-result-item" data-index="${results.indexOf(item)}">
                                <div class="d-flex align-items-center">
                                    <div class="omni-icon me-3" style="width: 36px; height: 36px; border-radius: 8px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #1e293b; flex-shrink: 0;">
                                        <i class="${item.icon}"></i>
                                    </div>
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="fw-medium text-truncate" style="color: #1e293b;">${item.title}</div>
                                        <div class="text-truncate small" style="color: #64748b;">${item.subtitle}</div>
                                    </div>
                                    <kbd class="text-muted small ms-2" style="background: #f1f5f9; color: #64748b; border-radius: 4px; padding: 0.1rem 0.35rem; font-size: 0.7rem;">Enter</kbd>
                                </div>
                            </a>
                        `;
                    });
                    html += `
                            </div>
                        </div>
                    `;
                });
                resultsEl.innerHTML = html;
            }

            // Debounced search
            inputEl.addEventListener('input', function() {
                const q = this.value.trim();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    if (q.length < 2) {
                        renderResults([]);
                        return;
                    }
                    fetch(BASE_URL + '/admin/api/omni-search?q=' + encodeURIComponent(q))
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                renderResults(data.results || []);
                            }
                        })
                        .catch(err => console.error('Omni-search error:', err));
                }, 150);
            });

            // Keyboard navigation
            inputEl.addEventListener('keydown', function(e) {
                const items = resultsEl.querySelectorAll('.omni-result-item');
                if (items.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                    updateSelection(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, 0);
                    updateSelection(items);
                } else if (e.key === 'Enter' && selectedIndex >= 0) {
                    e.preventDefault();
                    items[selectedIndex].click();
                } else if (e.key === 'Escape') {
                    closeOmniSearch();
                }
            });

            function updateSelection(items) {
                items.forEach((item, idx) => {
                    if (idx === selectedIndex) {
                        item.classList.add('active');
                        item.style.background = '#f0f4f8';
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.classList.remove('active');
                        item.style.background = '';
                    }
                });
            }

            // Trigger button
            if (triggerBtn) {
                triggerBtn.addEventListener('click', openOmniSearch);
            }

            // Ctrl/Cmd + K handler
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    openOmniSearch();
                }
            });

            // Clean up on modal hide
            modalEl.addEventListener('hidden.bs.modal', function() {
                inputEl.value = '';
                renderResults([]);
            });

            // Focus input when modal shows
            modalEl.addEventListener('shown.bs.modal', function() {
                inputEl.focus();
                inputEl.select();
            });
        })();
        </script>

        <!-- APS Confirm Modal -->
        <div class="modal fade" id="apsConfirmModal" tabindex="-1" aria-labelledby="apsConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content" style="border: none; border-radius: 12px; overflow: hidden;">
                    <div class="modal-header border-0 pb-0" id="apsConfirmHeader" style="background: linear-gradient(135deg, #1e293b, #334155); color: #fff; border-radius: 12px 12px 0 0;">
                        <h6 class="modal-title fw-semibold" id="apsConfirmModalLabel">Confirm Action</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div id="apsConfirmIcon" class="mb-3" style="font-size: 2.5rem; color: #f59e0b;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <p id="apsConfirmMessage" class="mb-0 fw-medium" style="color: #1e293b; font-size: 0.95rem;"></p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center gap-2 pt-0 pb-3">
                        <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                        <button type="button" class="btn px-3 fw-semibold" id="apsConfirmBtn" style="border-radius: 8px;">Confirm</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Omni-Search Modal (Ctrl+K) -->
        <div class="modal fade" id="omniSearchModal" tabindex="-1" aria-labelledby="omniSearchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
                    <div class="modal-header border-0 pb-0" id="omniSearchHeader" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; border-radius: 16px 16px 0 0; padding: 1.5rem;">
                        <div class="d-flex align-items-center w-100">
                            <div class="position-relative flex-grow-1">
                                <i class="fas fa-search position-absolute" style="left: 1rem; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 1.1rem;"></i>
                                <input type="text" id="omniSearchInput" class="form-control form-control-lg ps-5 pe-5 bg-slate-800 border-0 text-white" placeholder="Search plots, customers, bookings, associates..." autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" style="background: rgba(30,41,59,0.8); color: #fff; font-size: 1.1rem; height: 3.5rem;">
                                <span id="omniSearchHint" class="position-absolute text-muted small" style="right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none;">
                                    <kbd style="background: #334155; color: #94a3b8; border-radius: 4px; padding: 0.15rem 0.4rem; font-size: 0.75rem;">⌘K</kbd> to open • <kbd style="background: #334155; color: #94a3b8; border-radius: 4px; padding: 0.15rem 0.4rem; font-size: 0.75rem;">Esc</kbd> to close
                                </span>
                            </div>
                            <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.7;"></button>
                        </div>
                    </div>
                    <div class="modal-body p-0" style="max-height: 65vh; overflow-y: auto;">
                        <div id="omniSearchResults" class="p-3">
                            <!-- Results rendered here -->
                            <div class="text-center text-muted py-5" id="omniSearchEmpty">
                                <i class="fas fa-search fa-3x mb-3" style="color: #334155;"></i>
                                <p class="fw-medium" style="color: #475569;">Start typing to search plots, customers, bookings...</p>
                                <p class="small" style="color: #64748b;">Search by plot number, customer name, booking number, email, phone, or referral code</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        /* APS Confirm Modal — replaces native confirm() across admin panel
         * Usage: apsConfirm('Delete this item?').then(ok => { if (ok) ... })
         * Options: { title, confirmText, confirmClass, icon, iconColor }
         */
        (function() {
            let _resolve = null;
            const modal = new bootstrap.Modal(document.getElementById('apsConfirmModal'));
            const btnEl = document.getElementById('apsConfirmBtn');
            const msgEl = document.getElementById('apsConfirmMessage');
            const iconEl = document.getElementById('apsConfirmIcon');
            const headerEl = document.getElementById('apsConfirmHeader');
            const titleEl = document.getElementById('apsConfirmModalLabel');

            btnEl.addEventListener('click', function() {
                modal.hide();
                if (_resolve) _resolve(true);
            });

            document.getElementById('apsConfirmModal').addEventListener('hidden.bs.modal', function() {
                if (_resolve) { _resolve(false); _resolve = null; }
            });

            window.apsConfirm = function(message, opts) {
                opts = opts || {};
                msgEl.textContent = message;
                titleEl.textContent = opts.title || 'Confirm Action';

                const icon = opts.icon || 'exclamation-triangle';
                const iconColor = opts.iconColor || '#f59e0b';
                iconEl.innerHTML = '<i class="fas fa-' + icon + '"></i>';
                iconEl.style.color = iconColor;

                btnEl.textContent = opts.confirmText || 'Confirm';
                btnEl.className = 'btn px-3 fw-semibold ' + (opts.confirmClass || 'btn-warning');
                btnEl.style.borderRadius = '8px';

                const headerColor = opts.headerGradient || 'linear-gradient(135deg, #1e293b, #334155)';
                headerEl.style.background = headerColor;

                modal.show();
                return new Promise(function(resolve) { _resolve = resolve; });
            };
        })();
        </script>
        <!-- Customer & Portal Component Library JS -->
        <script defer src="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/js/customer-pages.js"></script>
</body>

</html>