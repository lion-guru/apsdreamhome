<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
$GLOBALS['_html_doc_started'] = true;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Associate Dashboard - APS Dream Home'; ?></title>
    <meta name="description" content="<?php echo $page_description ?? 'Associate Portal'; ?>">

    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <!-- Universal mobile-first responsive overrides -->
    <link href="<?php echo BASE_URL; ?>/assets/css/mobile-responsive.css" rel="stylesheet">

    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
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
            background: linear-gradient(180deg, #0f172a 0%, #134e4a 100%);
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
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
            color: #5eead4;
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
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
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
            padding: 0;
            margin: 0;
        }

        .menu-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 11px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 0.88rem;
        }

        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .sidebar-link.active {
            background: rgba(13, 148, 136, 0.2);
            color: #5eead4;
            border-left: 3px solid #5eead4;
        }

        .sidebar-link i {
            width: 20px;
            margin-right: 12px;
            text-align: center;
        }

        .submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            background: rgba(0, 0, 0, 0.2);
            display: none;
        }

        .sidebar-item:hover .submenu {
            display: block;
        }

        .submenu-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .submenu-link {
            display: flex;
            align-items: center;
            padding: 10px 20px 10px 52px;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }

        .submenu-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.8);
        }

        .submenu-link.active {
            background: rgba(13, 148, 136, 0.1);
            color: #5eead4;
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
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
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
            color: #14b8a6;
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

        /* aps-cp-card styles (used by views but defined nowhere in associate layout) */
        .aps-cp-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        .aps-cp-card-header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 20px;
        }
        .aps-cp-card-body {
            padding: 20px;
        }

        /* Custom Bootstrap color extensions (not in standard BS5) */
        .bg-purple { background-color: #6f42c1 !important; }
        .text-purple { color: #6f42c1 !important; }
        .bg-gold { background-color: #d4a843 !important; }
        .text-gold { color: #d4a843 !important; }
        .bg-teal { background-color: #20c997 !important; }
        .text-teal { color: #20c997 !important; }
        .bg-indigo { background-color: #0d9488 !important; }
        .text-indigo { color: #0d9488 !important; }

        /* Fix text visibility on colored backgrounds */
        .card.bg-success, .card.bg-warning, .card.bg-info, .card.bg-primary {
            color: #fff;
        }
        .card.bg-success .card-title,
        .card.bg-warning .card-title,
        .card.bg-info .card-title,
        .card.bg-primary .card-title {
            color: #fff;
        }
        .card.bg-success h6, .card.bg-warning h6, .card.bg-info h6, .card.bg-primary h6 {
            color: #fff;
        }
        .card.bg-success h3, .card.bg-warning h3, .card.bg-info h3, .card.bg-primary h3 {
            color: #fff;
        }

        /* Fix table responsiveness double-wrap */
        .table-responsive .table-responsive {
            overflow: visible;
        }

        /* Sidebar badge */
        .sidebar-badge {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: auto;
            font-weight: 600;
        }

        /* Collapsible section toggle */
        .sidebar-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            user-select: none;
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            margin-top: 4px;
        }
        .sidebar-section:first-of-type {
            border-top: none;
            margin-top: 0;
            padding-top: 12px;
        }
        .sidebar-section .section-toggle {
            font-size: 0.65rem;
            transition: transform 0.2s ease;
        }
        .sidebar-section.collapsed .section-toggle {
            transform: rotate(-90deg);
        }
        .sidebar-section.collapsed + .sidebar-menu {
            display: none;
        }

        /* ===== ASSOCIATE PORTAL MOBILE RESPONSIVENESS ===== */
        @media (max-width: 768px) {
            .top-header {
                padding: 10px 12px 10px 65px !important;
                flex-wrap: wrap;
            }
            .page-title { font-size: 1rem !important; }
            .breadcrumb { font-size: 0.72rem !important; display: none !important; }
            .header-actions { gap: 6px; }
            .content-wrapper { padding: 12px !important; }
            .stat-card { padding: 14px !important; }
            .stat-value { font-size: 1.3rem !important; }
            .stat-icon { width: 40px !important; height: 40px !important; font-size: 1.1rem !important; }
            .aps-cp-card { border-radius: 10px !important; }
            .aps-cp-card-header { padding: 12px 14px !important; font-size: 0.85rem !important; flex-wrap: wrap; }
            .aps-cp-card-body { padding: 14px !important; }
            .table-responsive { font-size: 0.8rem; }
            .table th, .table td { padding: 6px 8px !important; white-space: normal; overflow-wrap: break-word; word-break: break-word; }
            .btn { padding: 6px 12px !important; font-size: 0.8rem !important; }
            .row.g-3 > [class*="col-"] { padding: 6px; }
            .row.g-4 > [class*="col-"] { padding: 8px; }
            .pipeline-mini { gap: 4px !important; }
            .pipeline-mini .pm-item { min-width: 60px !important; padding: 6px 8px !important; font-size: 0.7rem !important; }
            .pipeline-mini .pm-item .pm-count { font-size: 1rem !important; }
        }
        @media (max-width: 480px) {
            .user-card { padding: 10px 14px !important; }
            .stat-value { font-size: 1.1rem !important; }
            .pipeline-mini .pm-item { min-width: 50px !important; padding: 4px 6px !important; font-size: 0.65rem !important; }
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

        <!-- RBAC-Driven Portal Menu (associate role) -->
        <?php
        use App\Services\PortalMenuService;
        try {
            $portalMenu = PortalMenuService::forSession();
        } catch (\Throwable $e) {
            $portalMenu = [];
        }
        $activeKey = $current_page ?? '';
        // Auto-expand section containing active item
        $activeSection = '';
        foreach ($portalMenu as $section) {
            foreach ($section['items'] as $item) {
                if ($item['key'] === $activeKey) {
                    $activeSection = $section['name'];
                    break 2;
                }
            }
        }
        foreach ($portalMenu as $section):
            if (empty($section['items'])) continue;
            $isCollapsed = ($activeSection !== '' && $activeSection !== $section['name']);
        ?>
        <div class="sidebar-section <?= $isCollapsed ? 'collapsed' : '' ?>" onclick="toggleSection(this)">
            <span><?= htmlspecialchars(__($section['name'], null, $section['name'])) ?></span>
            <i class="fas fa-chevron-down section-toggle"></i>
        </div>
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
                <h1 class="page-title"><?php echo $page_title ?? 'Dashboard'; ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/associate/dashboard">Home</a></li>
                        <li class="breadcrumb-item active"><?php echo $page_title ?? 'Dashboard'; ?></li>
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
                <a href="<?= BASE_URL ?>/associate/profile" class="btn-icon" title="My Profile">
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
    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        function toggleSection(el) {
            el.classList.toggle('collapsed');
            localStorage.setItem('sidebar_section_' + el.querySelector('span').textContent, el.classList.contains('collapsed') ? '1' : '0');
        }

        // Restore section states from localStorage
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.sidebar-section').forEach(function(sec) {
                var key = 'sidebar_section_' + sec.querySelector('span').textContent;
                var val = localStorage.getItem(key);
                if (val === '1') sec.classList.add('collapsed');
            });
        });

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

    <!-- Floating AI Chat Widget -->
    <div id="aiChatWidget" class="style-96938">
        <button id="chatToggle" onclick="toggleAssociateChat()" class="style-14728">
            <i class="fas fa-robot"></i>
        </button>
        <div id="chatPanel" class="style-19303">
            <div class="style-25493">
                <div class="style-44602">
                    <i class="fas fa-robot" class="style-30322"></i>
                    <div>
                        <div class="style-35140">APS Assistant</div>
                        <div class="style-1333">Property &amp; MLM Help</div>
                    </div>
                </div>
                <button onclick="toggleAssociateChat()" class="style-71807"><i class="fas fa-times"></i></button>
            </div>
            <div id="assocChatMessages" class="style-47443">
                <div class="style-88703">
                    Namaste! I'm your APS assistant. Ask about properties, commissions, bookings, or anything else.
                </div>
            </div>
            <div class="style-22035">
                <input type="text" id="assocChatInput" placeholder="Type a message..." class="style-69765" onkeypress="if(event.key==='Enter')sendAssociateChat()">
                <button onclick="sendAssociateChat()" class="style-58231"><i class="fas fa-paper-plane" class="style-64777"></i></button>
            </div>
        </div>
    </div>
    <script>
    function toggleAssociateChat() {
        var p = document.getElementById('chatPanel');
        p.style.display = p.style.display === 'none' ? 'block' : 'none';
        if (p.style.display === 'block') document.getElementById('assocChatInput').focus();
    }
    async function sendAssociateChat() {
        var inp = document.getElementById('assocChatInput');
        var msg = inp.value.trim();
        if (!msg) return;
        inp.value = '';
        addAssocMsg(msg, true);
        try {
            var r = await fetch('<?= BASE_URL ?>api/ai/chat', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({message:msg}) });
            var d = await r.json();
            addAssocMsg(d.reply || d.response || 'Sorry, I could not process that.', false);
        } catch(e) {
            addAssocMsg('Connection error. Please try again.', false);
        }
    }
    function addAssocMsg(text, isUser) {
        var c = document.getElementById('assocChatMessages');
        var d = document.createElement('div');
        d.style.cssText = 'padding:10px 14px;border-radius:12px;font-size:0.85rem;max-width:88%;margin-bottom:8px;box-shadow:0 1px 3px rgba(0,0,0,0.06);' + (isUser ? 'background:#0d9488;color:#fff;margin-left:auto;border-radius:12px 12px 4px 12px;' : 'background:#fff;color:#334155;border-left:3px solid #0d9488;');
        d.innerHTML = text.replace(/\n/g,'<br>');
        c.appendChild(d);
        c.scrollTop = c.scrollHeight;
    }
    </script>
</body>

</html>