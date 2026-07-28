<?php
$GLOBALS['_html_doc_started'] = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'My Account - APS Dream Home'; ?></title>
    <meta name="description" content="<?php echo $page_description ?? 'Customer Portal'; ?>">
    <?php if (isset($_SESSION['user_id'])): ?>
    <meta name="user-id" content="<?= (int)$_SESSION['user_id'] ?>">
    <?php endif; ?>

    <!-- CSRF Token -->
    <?php
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES); ?>">

    <!-- Skip to content link (a11y) -->
    <a href="#aps-main-content" class="aps-skip-link">Skip to main content</a>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Consolidated APS CSS Bundles -->
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/header.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/notification-system.css" rel="stylesheet">
    <!-- Universal mobile-first responsive overrides -->
    <link href="<?php echo BASE_URL; ?>/assets/css/mobile-responsive.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        /* ===== CUSTOMER PORTAL MOBILE RESPONSIVENESS ===== */
        @media (max-width: 768px) {
            .top-header { padding: 10px 12px 10px 65px !important; flex-wrap: wrap; }
            .page-title { font-size: 1rem !important; }
            .breadcrumb { font-size: 0.72rem !important; }
            .header-actions { gap: 6px; }
            .content-wrapper { padding: 12px !important; }
            .sidebar-toggle { display: flex !important; }
            .aps-cp-card, .card { border-radius: 10px !important; }
            .table-responsive { font-size: 0.8rem; }
            .table th, .table td { padding: 6px 8px !important; }
            .btn { padding: 6px 12px !important; font-size: 0.8rem !important; }
            .row.g-3 > [class*="col-"] { padding: 6px; }
        }
        @media (max-width: 480px) {
            .breadcrumb { display: none !important; }
            .page-title { font-size: 0.9rem !important; }
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

        <!-- RBAC-Driven Portal Menu -->
        <?php
        use App\Services\PortalMenuService;
        try {
            $portalMenu = PortalMenuService::forSession();
        } catch (\Throwable $e) {
            $portalMenu = [];
        }
        $activeKey = $current_page ?? '';
        foreach ($portalMenu as $section):
            if (empty($section['items'])) continue;
        ?>
        <div class="sidebar-section"><?= htmlspecialchars(__($section['name'], null, $section['name'])) ?></div>
        <ul class="sidebar-menu">
            <?php foreach ($section['items'] as $menuItem):
                $isActive = ($activeKey === $menuItem['key']);
                $isLogout = ($menuItem['key'] === 'logout');
                $badge = $menuItem['badge'] ?? null;
            ?>
            <li class="sidebar-item">
                <a href="<?= BASE_URL . htmlspecialchars($menuItem['url']) ?>" class="sidebar-link <?= $isActive ? 'active' : '' ?> <?= $isLogout ? 'text-danger' : '' ?>" data-menu-key="<?= htmlspecialchars($menuItem['key']) ?>">
                    <i class="<?= htmlspecialchars($menuItem['icon']) ?>"></i>
                    <span><?= htmlspecialchars(__('menu_' . $menuItem['key'], null, $menuItem['label'])) ?></span>
                    <?php if ($badge !== null && $badge > 0): ?>
                    <span class="sidebar-badge"><?= (int)$badge ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endforeach; ?>
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
                <a href="<?= BASE_URL ?>/user/messages" class="btn-icon" title="Messages">
                    <i class="fas fa-envelope"></i>
                </a>
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
    <script defer src="<?php echo BASE_URL; ?>/assets/js/customer-pages.js"></script>

    <!-- Real-time WebSocket Notifications -->
    <script>
        window.NOTIFY_USER = {
            id: <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 'null'); ?>,
            role: '<?php echo isset($_SESSION['admin_id']) ? 'admin' : (isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role'] ?? '', ENT_QUOTES) : (isset($_SESSION['user_id']) ? 'customer' : 'guest')); ?>'
        };
    </script>
    <script defer src="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/js/notification-system.js"></script>
    <!-- WebSocket Notification Widget (real-time push) -->
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/css/notification-widget.css" rel="stylesheet">
    <script defer src="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/js/notification-widget.js"></script>

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
<script src="<?= BASE_URL ?>/assets/js/push-notifications.js"></script>
<script>
function showToast(message, type) {
    if (typeof APS !== 'undefined' && APS.toast) {
        APS.toast(message, type || 'info');
    } else {
        var toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;top:20px;right:20px;padding:12px 24px;background:#3b82f6;color:#fff;border-radius:8px;z-index:99999;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,0.15);animation:fadeIn 0.3s';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(function() { toast.remove(); }, 300); }, 3000);
    }
}
</script>
</body>
</html>
