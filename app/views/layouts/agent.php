<?php
use App\Services\PortalMenuService;
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
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">window.BASE_URL = '<?= defined('BASE_URL') ? BASE_URL : '' ?>';</script>

    <a href="#aps-main-content" class="aps-skip-link">Skip to main content</a>

    <!-- Fonts & Bootstrap -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <!-- Design Tokens & Components -->
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css?v=7" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/consolidated/aps-components.css?v=2" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/notification-system.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/mobile-responsive.css?v=3" rel="stylesheet">

    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; background: #f8fafc; color: #1e293b; }

        /* Unified Dark Slate Sidebar with Emerald Accents */
        .sidebar {
            position: fixed; top: 0; left: 0; width: 260px; height: 100vh;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            z-index: 1000; overflow-y: auto; transition: transform 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.15);
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 2px; }
        .sidebar-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .sidebar-logo { color: #fff; font-size: 1.1rem; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .sidebar-logo i { font-size: 1.3rem; color: #10b981; }
        .sidebar-sub { color: rgba(255,255,255,0.6); font-size: 0.72rem; margin-top: 4px; }
        
        .user-card { padding: 15px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); color: #fff; }
        .user-avatar { width: 44px; height: 44px; background: linear-gradient(135deg, #059669 0%, #047857 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; margin-bottom: 8px; color: #fff; }
        .user-name { font-weight: 600; font-size: 0.92rem; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 0.75rem; color: rgba(255,255,255,0.6); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        
        .sidebar-section { padding: 14px 20px 4px; font-size: 0.68rem; text-transform: uppercase; color: rgba(255,255,255,0.45); font-weight: 700; letter-spacing: 0.05em; }
        .sidebar-menu { list-style: none; padding: 0 10px; margin: 0 0 8px; }
        .sidebar-item { margin-bottom: 2px; }
        .sidebar-link { display: flex; align-items: center; padding: 9px 14px; color: #cbd5e1; text-decoration: none; border-radius: 8px; transition: all 0.2s ease; font-size: 0.88rem; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(255,255,255,0.1); color: #fff; }
        .sidebar-link.active { background: rgba(16, 185, 129, 0.15); color: #34d399; font-weight: 600; }
        .sidebar-link i { width: 22px; margin-right: 10px; font-size: 0.95rem; text-align: center; }
        .sidebar-badge { margin-left: auto; background: rgba(16, 185, 129, 0.25); color: #34d399; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; font-weight: 600; }

        /* Main Content Layout */
        .main-content { margin-left: 260px; min-height: 100vh; transition: margin-left 0.3s ease; }
        .top-header { background: #fff; padding: 14px 28px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
        .page-title { font-size: 1.3rem; font-weight: 700; color: #0f172a; margin: 0; }
        .breadcrumb { margin: 0; font-size: 0.82rem; }
        .header-actions { display: flex; gap: 12px; align-items: center; }
        .btn-icon { width: 38px; height: 38px; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff; color: #64748b; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; text-decoration: none; position: relative; }
        .btn-icon:hover { background: #f1f5f9; color: #0f172a; }
        .content-wrapper { padding: 28px; }
        
        .sidebar-toggle { display: none; position: fixed; top: 16px; left: 16px; z-index: 1001; width: 40px; height: 40px; background: #0f172a; border: none; border-radius: 8px; color: #fff; font-size: 1.1rem; cursor: pointer; align-items: center; justify-content: center; }
        .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 999; }
        .sidebar-overlay.show { display: block; }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .sidebar-toggle { display: flex; }
            .top-header { padding-left: 68px; }
            .content-wrapper { padding: 16px; }
        }
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=3">
</head>
<body>
    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar menu">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Agent Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo BASE_URL; ?>" class="sidebar-logo">
                <i class="fas fa-handshake"></i>
                <span>APS Dream Home</span>
            </a>
            <div class="sidebar-sub">Agent Partner Portal</div>
        </div>

        <!-- User Info Card -->
        <div class="user-card">
            <div class="user-avatar">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['agent_name'] ?? 'Agent Partner'); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($_SESSION['user_email'] ?? $_SESSION['agent_email'] ?? 'agent@example.com'); ?></div>
        </div>

        <!-- RBAC Menu -->
        <?php
        try {
            $portalMenu = PortalMenuService::forSession();
        } catch (\Throwable $e) {
            $portalMenu = [];
        }
        $activeKey = $current_page ?? 'dashboard';
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

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div>
                <h1 class="page-title"><?php echo preg_replace('/\s*-\s*APS Dream Home\s*$/', '', $page_title ?? 'Agent Dashboard'); ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/agent/dashboard" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active"><?php echo preg_replace('/\s*-\s*APS Dream Home\s*$/', '', $page_title ?? 'Dashboard'); ?></li>
                    </ol>
                </nav>
            </div>
            <div class="header-actions">
                <a href="<?= BASE_URL ?>/user/notifications" class="btn-icon" title="Notifications">
                    <i class="fas fa-bell"></i>
                </a>
                <a href="<?= BASE_URL ?>/user/messages" class="btn-icon" title="Messages">
                    <i class="fas fa-envelope"></i>
                </a>
                <div class="dropdown">
                    <button class="btn-icon" data-bs-toggle="dropdown" aria-expanded="false" title="Account Menu">
                        <i class="fas fa-user-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius: 12px; min-width: 180px;">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/agent/profile"><i class="fas fa-user me-2 text-muted"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/agent/wallet"><i class="fas fa-wallet me-2 text-muted"></i>Wallet</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/auth/logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-wrapper" id="aps-main-content">
            <?php echo $content ?? ''; ?>
        </div>
    </main>

    <!-- Scripts -->
    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script defer src="<?php echo BASE_URL; ?>/assets/js/frontend-enhancements.js"></script>
    <script defer src="<?php echo BASE_URL; ?>/assets/js/customer-pages.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1024) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.querySelector('.sidebar-overlay');
                if (sidebar) sidebar.classList.remove('show');
                if (overlay) overlay.classList.remove('show');
            }
        });
    </script>
</body>
</html>
