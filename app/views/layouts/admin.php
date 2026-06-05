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

    <!-- Admin CSS -->
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/admin/css/admin.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/frontend-enhancements.css" rel="stylesheet">
    <style>
        /* RBAC Sidebar section toggle styles */
        .sidebar-sec { padding:15px 15px 5px; font-size:.7rem; text-transform:uppercase; color:rgba(255,255,255,.4); font-weight:600; letter-spacing:.05em; cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
        .sidebar-sec-arrow { font-size:.6rem; transition:transform .25s; color:rgba(255,255,255,.3) }
        .sidebar-sec-arrow.collapsed { transform:rotate(-90deg) }
    </style>
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
    <?php include __DIR__ . '/../admin/layouts/rbac_sidebar.php'; ?>

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
    } catch (\Exception $e) { /* silent */ }
    ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navigation -->
        <nav class="top-nav">
            <div class="nav-left">
                <button class="toggle-btn" onclick="document.getElementById('sidebarMenu').classList.toggle('show')">
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
                <button class="nav-icon" onclick="toggleNotifications()" title="New Leads Today">
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
                        <li><hr class="dropdown-divider"></li>
                        <li><a href="<?php echo $base; ?>/admin/settings" class="dropdown-item"><i class="fas fa-cog"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
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
    <script src="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/js/admin.js"></script>
    <!-- Frontend enhancements: a11y, forms, toasts, loading -->
    <script defer src="<?php echo BASE_URL; ?>/assets/js/frontend-enhancements.js"></script>

    <!-- Real-time WebSocket Notifications -->
    <script>
        window.NOTIFY_USER = {
            id: <?php echo isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null'); ?>,
            role: '<?php echo isset($_SESSION['admin_id']) ? 'admin' : (isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role'], ENT_QUOTES) : (isset($_SESSION['user_id']) ? 'customer' : 'guest')); ?>'
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
                link.classList.remove('active');
                if (!href) return;
                // Match exact or sub-path after base
                if (url === href || (href !== baseUrl + '/admin/dashboard' && url.indexOf(href) === 0) || (href.indexOf('?') > -1 && url.indexOf(href.substring(0, href.indexOf('?'))) === 0)) {
                    link.classList.add('active');
                }
            });
        }

        function reRunScripts(container) {
            container.querySelectorAll('script').forEach(function(oldScript) {
                var newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(function(attr) {
                    newScript.setAttribute(attr.name, attr.value);
                });
                newScript.textContent = oldScript.textContent;
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });
        }

        function findContent(doc) {
            // Try id first, then class fallback
            var el = doc.getElementById('page-content');
            if (!el) el = doc.querySelector('.page-content');
            return el;
        }

        function navigateTo(url, pushState) {
            if (pushState !== false) {
                history.pushState({ url: url }, '', url);
            }

            var loadingEl = document.getElementById('page-content') || document.querySelector('.page-content');
            if (loadingEl) loadingEl.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Loading...</p></div>';

            fetch(url)
                .then(function(response) {
                    if (!response.ok) throw new Error('Page load failed: ' + response.status);
                    return response.text();
                })
                .then(function(html) {
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');

                    var newContent = findContent(doc);
                    var currentContent = document.getElementById('page-content') || document.querySelector('.page-content');
                    if (newContent && currentContent) {
                        currentContent.innerHTML = newContent.innerHTML;
                    } else {
                        // Fallback: full page reload
                        window.location.href = url;
                        return;
                    }

                    // Update breadcrumb: try #breadcrumb-title first, then .breadcrumb
                    var newBreadcrumb = doc.getElementById('breadcrumb-title') || doc.querySelector('.breadcrumb');
                    var currentBreadcrumb = document.getElementById('breadcrumb-title') || document.querySelector('.breadcrumb');
                    if (newBreadcrumb && currentBreadcrumb) {
                        currentBreadcrumb.innerHTML = newBreadcrumb.innerHTML;
                    }

                    updateActiveSidebar(url);
                    reRunScripts(currentContent);
                    document.title = doc.title || 'APS Dream Home - Admin';
                })
                .catch(function(err) {
                    console.error('AJAX navigation error:', err);
                    window.location.href = url;
                });
        }

        // Intercept sidebar link clicks
        document.addEventListener('click', function(e) {
            var link = e.target.closest('.sidebar-link');
            if (!link) return;

            // Allow logout, external links, target="_blank" to behave normally
            if (link.getAttribute('target') === '_blank') return;
            if (link.getAttribute('href') && link.getAttribute('href').indexOf('/logout') !== -1) return;
            if (link.getAttribute('href') && link.getAttribute('href').indexOf('://') !== -1 && link.getAttribute('href').indexOf(window.location.hostname) === -1) return;

            e.preventDefault();
            var href = link.getAttribute('href');
            if (href) navigateTo(href);
        });

        // Handle browser back/forward
        window.addEventListener('popstate', function(e) {
            if (e.state && e.state.url) {
                navigateTo(e.state.url, false);
            }
        });

        // Intercept sidebar subsection links (not primary sidebar-link)
        document.addEventListener('click', function(e) {
            var link = e.target.closest('.dropdown-item');
            if (!link) return;
            if (link.getAttribute('target') === '_blank') return;
            if (link.getAttribute('href') && link.getAttribute('href').indexOf('/logout') !== -1) return;
            var href = link.getAttribute('href');
            if (!href || href.indexOf('#') === 0) return;

            // Only intercept if it contains /admin/ path
            if (href.indexOf(baseUrl + '/admin/') === 0 || href.indexOf('/admin/') === 0) {
                e.preventDefault();
                navigateTo(href);
            }
        });
    })();
    }
    </script>
</body>

</html>