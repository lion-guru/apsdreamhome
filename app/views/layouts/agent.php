<?php
use App\Services\PortalMenuService;
// session is already started by the framework
$GLOBALS['_html_doc_started'] = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Agent Portal - APS Dream Home'; ?></title>
    <meta name="description" content="<?php echo $page_description ?? 'Agent Portal'; ?>">
    <?php if (isset($_SESSION['user_id'])): ?>
    <meta name="user-id" content="<?= (int)$_SESSION['user_id'] ?>">
    <?php endif; ?>

    <a href="#aps-main-content" class="aps-skip-link">Skip to main content</a>

    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <!-- Consolidated APS CSS Bundles -->
    <link href="<?php echo BASE_URL; ?>/assets/css/consolidated/aps-core.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/consolidated/aps-components.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/consolidated/aps-layout.css" rel="stylesheet">
    <!-- Dark Mode CSS -->
    <link href="<?php echo BASE_URL; ?>/assets/css/dark-mode.css" rel="stylesheet">
    <!-- Universal mobile-first responsive overrides -->
    <link href="<?php echo BASE_URL; ?>/assets/css/mobile-responsive.css" rel="stylesheet">

    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f8fafc; }

        .sidebar {
            position: fixed; top: 0; left: 0; width: 260px; height: 100vh;
            background: linear-gradient(180deg, #064e3b 0%, #065f46 100%);
            z-index: 1000; overflow-y: auto; transition: transform 0.3s ease;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 2px; }
        .sidebar-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-logo { color: #fff; font-size: 1.1rem; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .sidebar-logo i { font-size: 1.3rem; color: #6ee7b7; }
        .sidebar-sub { color: rgba(255,255,255,0.5); font-size: 0.7rem; margin-top: 4px; }
        .user-card { padding: 15px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); color: #fff; }
        .user-avatar { width: 45px; height: 45px; background: linear-gradient(135deg, #34d399 0%, #10b981 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 10px; }
        .user-name { font-weight: 600; font-size: 0.95rem; margin-bottom: 3px; }
        .user-role { font-size: 0.75rem; color: rgba(255,255,255,0.6); }
        .sidebar-section { padding: 15px 20px 5px; font-size: 0.7rem; text-transform: uppercase; color: rgba(255,255,255,0.5); font-weight: 600; letter-spacing: 0.05em; }
        .sidebar-menu { list-style: none; padding: 0 10px; margin: 0; }
        .sidebar-item { margin-bottom: 2px; }
        .sidebar-link { display: flex; align-items: center; padding: 10px 15px; color: #cbd5e1; text-decoration: none; border-radius: 8px; transition: all 0.2s ease; font-size: 0.9rem; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(255,255,255,0.1); color: #fff; }
        .sidebar-link i { width: 22px; margin-right: 10px; font-size: 1rem; }
        .sidebar-badge { margin-left: auto; background: rgba(255,255,255,0.2); color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; }

        .main-content { margin-left: 260px; min-height: 100vh; transition: margin-left 0.3s ease; }
        .top-header { background: #fff; padding: 15px 25px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
        .page-title { font-size: 1.4rem; font-weight: 700; color: #1e293b; margin: 0; }
        .breadcrumb { margin: 0; font-size: 0.85rem; }
        .header-actions { display: flex; gap: 10px; align-items: center; }
        .btn-icon { width: 40px; height: 40px; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff; color: #64748b; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; }
        .btn-icon:hover { background: #f1f5f9; color: #1e293b; }
        .content-wrapper { padding: 25px; }
        .sidebar-toggle { display: none; position: fixed; top: 20px; left: 20px; z-index: 1001; width: 45px; height: 45px; background: linear-gradient(135deg, #10b981 0%, #047857 100%); border: none; border-radius: 10px; color: #fff; font-size: 1.2rem; cursor: pointer; }
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .sidebar-toggle { display: flex; align-items: center; justify-content: center; }
            .top-header { padding-left: 70px; }
        }
        .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 999; }
        .sidebar-overlay.show { display: block; }
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=2">
</head>
<body>
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo BASE_URL; ?>" class="sidebar-logo">
                <i class="fas fa-handshake"></i>
                <span>APS Dream Home</span>
            </a>
            <div class="sidebar-sub"><?= htmlspecialchars(PortalMenuService::roleLabel()) ?></div>
        </div>

        <div class="user-card">
            <div class="user-avatar">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['agent_name'] ?? 'Agent'); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($_SESSION['user_email'] ?? $_SESSION['agent_email'] ?? 'agent@example.com'); ?></div>
        </div>

        <?php
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
                <a href="<?= BASE_URL . htmlspecialchars($menuItem['url'] ?? '') ?>" class="sidebar-link <?= $isActive ? 'active' : '' ?> <?= $isLogout ? 'text-danger' : '' ?>" data-menu-key="<?= htmlspecialchars($menuItem['key'] ?? '') ?>">
                    <i class="<?= htmlspecialchars($menuItem['icon'] ?? '') ?>"></i>
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

    <main class="main-content">
        <header class="top-header">
            <div>
                <h1 class="page-title"><?php echo preg_replace('/\s*-\s*APS Dream Home\s*$/', '', $page_title ?? 'Agent Portal'); ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/agent/dashboard">Home</a></li>
                        <li class="breadcrumb-item active"><?php echo $page_title ?? 'Dashboard'; ?></li>
                    </ol>
                </nav>
            </div>
            <div class="header-actions">
                <button class="btn btn-sm btn-outline-secondary me-2" id="darkModeToggle" onclick="toggleDarkMode()" title="Toggle Dark Mode">
                    <i class="fas fa-moon" id="darkModeIcon"></i>
                </button>
                <a href="<?= BASE_URL ?>/user/notifications" class="btn-icon" title="Notifications"><i class="fas fa-bell"></i></a>
            </div>
        </header>
        <div class="content-wrapper" id="aps-main-content">
            <?php echo $content ?? ''; ?>
        </div>
    </main>

    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script defer src="<?php echo BASE_URL; ?>/assets/js/frontend-enhancements.js"></script>
    <script defer src="<?php echo BASE_URL; ?>/assets/js/customer-pages.js"></script>
    <!-- Notification System + WebSocket Widget -->
    <script>window.NOTIFY_USER = { id: <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null'; ?>, role: 'agent' };</script>
    <link href="<?php echo BASE_URL; ?>/assets/css/notification-system.css" rel="stylesheet">
    <script defer src="<?php echo BASE_URL; ?>/assets/js/notification-system.js"></script>
    <link href="<?php echo BASE_URL; ?>/assets/css/notification-widget.css" rel="stylesheet">
    <script defer src="<?php echo BASE_URL; ?>/assets/js/notification-widget.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
            document.querySelector('.sidebar-overlay').classList.toggle('show');
        }
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1024) {
                document.getElementById('sidebar').classList.remove('show');
                document.querySelector('.sidebar-overlay').classList.remove('show');
            }
        });

        // Dark Mode Toggle
        function toggleDarkMode() {
            var isDark = document.body.classList.toggle('dark-mode');
            localStorage.setItem('aps-dark-mode', isDark ? '1' : '0');
            var icon = document.getElementById('darkModeIcon');
            if (icon) {
                icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
            }
        }

        // Load saved preference on page load
        document.addEventListener('DOMContentLoaded', function() {
            var saved = localStorage.getItem('aps-dark-mode');
            if (saved === '1') {
                document.body.classList.add('dark-mode');
                var icon = document.getElementById('darkModeIcon');
                if (icon) icon.className = 'fas fa-sun';
            }
        });
    </script>
</body>
</html>
