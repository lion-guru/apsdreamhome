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
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/favicon.png">
    <meta name="description" content="<?php echo $page_description ?? 'Admin Panel'; ?>">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <?php if (isset($_SESSION['admin_id']) || isset($_SESSION['user_id'])): ?>
    <meta name="user-id" content="<?= (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0) ?>">
    <?php endif; ?>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Admin CSS (page-specific overrides) -->
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/admin/css/admin.css" rel="stylesheet">
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/admin/css/responsive-fixes.css" rel="stylesheet">
    <!-- Notification system CSS (dropdowns, toasts, popups) -->
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/css/notification-system.css" rel="stylesheet">
    <!-- Dark mode CSS (toggle via button or system preference) -->
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/css/dark-mode.css" rel="stylesheet">
    <?php if (isset($extra_css) && $extra_css): ?><!-- Extra page-specific CSS --><?php echo $extra_css; ?><?php endif; ?>
</head>

<body>
    <!-- Skip to content link (a11y) -->
    <a href="#aps-main-content" class="aps-skip-link">Skip to main content</a>

    <!-- Sidebar Toggle Button (Mobile) -->
    <button class="sidebar-toggle" onclick="APS.toggleSidebar()">
        <i class="fas fa-bars"></i>
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
    $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
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
                <!-- Dark Mode Toggle -->
                <button class="nav-icon" id="darkModeToggle" onclick="toggleDarkMode()" title="Toggle Dark Mode">
                    <i class="fas fa-moon" id="darkModeIcon"></i>
                </button>

                <!-- Notifications (Leads) — replaced by notification-system.js -->
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

            <?php
            // Tenant status banner — read-only mode for suspended/cancelled tenants
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
                } catch (\Throwable $e) { /* ignore */ }
            }
            ?>
            <?php if ($tenantBanner === 'suspended'): ?>
                <div style="background:linear-gradient(135deg,#dc2626,#ef4444);color:#fff;padding:12px 20px;border-radius:8px;margin-bottom:16px;display:flex;align-items:center;gap:12px;">
                    <i class="fas fa-ban" style="font-size:1.4rem;"></i>
                    <div>
                        <strong>Account Suspended</strong>
                        <div style="font-size:0.85rem;opacity:0.9;">Your account has been suspended. All create/edit/delete operations are disabled. Contact support to restore access.</div>
                    </div>
                </div>
            <?php elseif ($tenantBanner === 'cancelled'): ?>
                <div style="background:linear-gradient(135deg,#6b21a8,#9333ea);color:#fff;padding:12px 20px;border-radius:8px;margin-bottom:16px;display:flex;align-items:center;gap:12px;">
                    <i class="fas fa-times-circle" style="font-size:1.4rem;"></i>
                    <div>
                        <strong>Account Cancelled</strong>
                        <div style="font-size:0.85rem;opacity:0.9;">Your subscription has been cancelled. Your data is in read-only mode. Contact support to reactivate.</div>
                    </div>
                </div>
            <?php elseif ($tenantBanner === 'trial_expired'): ?>
                <div style="background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;padding:12px 20px;border-radius:8px;margin-bottom:16px;display:flex;align-items:center;gap:12px;">
                    <i class="fas fa-clock" style="font-size:1.4rem;"></i>
                    <div>
                        <strong>Trial Expired</strong>
                        <div style="font-size:0.85rem;opacity:0.9;">Your free trial has expired. Upgrade your plan to continue creating and editing data. <a href="<?= $base ?>/admin/billing" style="color:#fff;text-decoration:underline;">View Plans</a></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php echo $content ?? ''; ?>
        </div>
    </main>

    <!-- jQuery (for DataTables, modals, and admin plugins) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
    function toggleDarkMode() {
        var isDark = document.body.classList.toggle('dark-mode');
        localStorage.setItem('aps-dark-mode', isDark ? '1' : '0');
        var icon = document.getElementById('darkModeIcon');
        if (icon) { icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon'; }
    }
    (function() {
        var saved = localStorage.getItem('aps-dark-mode');
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (saved === '1' || (saved === null && prefersDark)) {
            document.body.classList.add('dark-mode');
            var icon = document.getElementById('darkModeIcon');
            if (icon) icon.className = 'fas fa-sun';
        }
    })();
    </script>
    <?php if (isset($extra_js) && $extra_js): ?><!-- Extra page-specific JS --><?php echo $extra_js; ?><?php endif; ?>
        <!-- Frontend enhancements: a11y, forms, toasts, loading -->

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
</body>

</html>