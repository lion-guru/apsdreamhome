<?php

/**
 * Admin Sidebar - 100% DB-Driven
 * All menu items come from admin_menu_items table.
 * NO hardcoded fallback — if DB is empty, sidebar shows a helpful error.
 */

use App\Services\AdminMenuService;
use App\Core\Middleware\TenantContext;

// Get current page for active state
$currentPage = $active_page ?? basename($_SERVER['REQUEST_URI'] ?? '');
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');

// Initialize menu service
$menuItems = [];
$menuError = null;
try {
    $menuService = new AdminMenuService();
    $menuItems = $menuService->getMenuItems();
} catch (\Throwable $e) {
    $menuError = $e->getMessage();
    error_log('Sidebar: AdminMenuService failed: ' . $e->getMessage());
}

// Group menu items by section
$groupedItems = [];
foreach ($menuItems as $item) {
    $section = strtolower($item['section'] ?? 'main');
    $groupedItems[$section][] = $item;
}

// Section display names with icons
$sectionNames = [
    'dashboards'  => '📊 Dashboards',
    'crm'         => '👥 CRM & Leads',
    'properties'  => '🏠 Properties & Land',
    'mlm'         => '🔗 MLM Network',
    'finance'     => '💰 Finance & Accounting',
    'commission'  => '💸 Commission Engine',
    'cms'         => '📝 Content (CMS)',
    'marketing'   => '📢 Marketing & Referrals',
    'reports'     => '📈 Reports & Analytics',
    'operations'  => '⚙️ Operations',
    'users'       => '👤 Users & Roles',
    'locations'   => '📍 Locations',
    'settings'    => '🔧 Settings',
    'hrm'         => '👔 HR & Payroll',
    'legal'       => '⚖️ Legal & Compliance',
    'sales'       => '🏷️ Sales & Bookings',
    'services'    => '🛎️ Services',
    'system'      => '🖥️ System Admin',
    'ai_tech'     => '🤖 AI & Technology',
    'security'    => '🔒 Security',
    'employee'    => '👩‍💼 Employee Portal',
    'saas'        => '☁️ SaaS / Multi-Tenant',
    'communication' => '📡 Communication',
];

// Section sort order — matches DB section_order
$sectionOrder = [
    'dashboards', 'crm', 'properties', 'sales', 'finance',
    'commission', 'mlm', 'hrm', 'legal', 'marketing',
    'cms', 'services', 'reports', 'operations', 'locations',
    'ai_tech', 'security', 'communication', 'users', 'saas', 'settings', 'system', 'employee',
];

// Sort grouped items by defined order
$sortedGrouped = [];
foreach ($sectionOrder as $sec) {
    if (isset($groupedItems[$sec])) {
        $sortedGrouped[$sec] = $groupedItems[$sec];
    }
}
// Append any sections not in the order list
foreach ($groupedItems as $sec => $items) {
    if (!isset($sortedGrouped[$sec])) {
        $sortedGrouped[$sec] = $items;
    }
}
$groupedItems = $sortedGrouped;
?>

<aside class="sidebar" id="sidebarMenu">
    <div class="sidebar-header">
        <a href="<?php echo $base; ?>/admin/dashboard" class="sidebar-logo">
            <?php
            $tenantLogo = TenantContext::getLogo();
            $tenantName = TenantContext::getName();
            $tenantColors = TenantContext::getColors();
            ?>
            <?php if ($tenantLogo): ?>
                <img src="<?php echo htmlspecialchars($tenantLogo); ?>" alt="Logo" style="max-height:28px; max-width:120px;">
            <?php else: ?>
                <i class="fas fa-home"></i>
            <?php endif; ?>
            <span><?php echo htmlspecialchars($tenantName); ?></span>
        </a>
        <div class="sidebar-sub">Admin Panel v2.0</div>
    </div>

    <?php
    // Tenant Switch Banner — show when SuperAdmin is impersonating a tenant
    $switchActive = !empty($_SESSION['tenant_switch_active']);
    $switchName = $_SESSION['tenant_switch_name'] ?? '';
    ?>
    <?php if ($switchActive): ?>
    <div style="background:linear-gradient(135deg,#f59e0b,#ef4444);color:#fff;padding:10px 14px;margin:0 8px;border-radius:8px;font-size:0.82rem;">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="fas fa-exchange-alt"></i>
            <strong>Viewing: <?= htmlspecialchars($switchName) ?></strong>
        </div>
        <form method="POST" action="<?= $base ?>/admin/tenants/stop-switch" style="margin:0;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <button type="submit" class="btn btn-sm btn-light w-100" style="font-size:0.78rem;padding:3px 8px;">
                <i class="fas fa-undo me-1"></i>Back to My Tenant
            </button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Tenant CSS Variables for white-labeling -->
    <style>
        :root {
            --tenant-primary: <?php echo htmlspecialchars($tenantColors['primary']); ?>;
            --tenant-secondary: <?php echo htmlspecialchars($tenantColors['secondary']); ?>;
        }
    </style>

    <?php
    $reqUri = $_SERVER['REQUEST_URI'] ?? '';
    $reqPath = rtrim(parse_url($reqUri, PHP_URL_PATH), '/');

    // Determine which section has the active item (to auto-expand it)
    $sectionHasActive = [];
    foreach ($groupedItems as $section => $items) {
        foreach ($items as $item) {
            $itemFullUrl = rtrim($base . $item['url'], '/');
            if ($reqPath === $itemFullUrl || ($itemFullUrl !== $base && strpos($reqPath, $itemFullUrl . '/') === 0)) {
                $sectionHasActive[$section] = true;
                break;
            }
        }
    }
    ?>

    <!-- Expand All / Collapse All toggle -->
    <div class="sidebar-sec" onclick="toggleAllSidebarSections()">
        <span><i class="fas fa-layer-group"></i> All Sections</span>
        <i class="fas fa-chevron-down sidebar-sec-arrow" id="arrow-expand-all"></i>
    </div>

    <?php if (!empty($menuError)): ?>
        <div style="padding:15px; color:#f87171; font-size:0.8rem;">
            <i class="fas fa-exclamation-triangle"></i> Sidebar error:<br>
            <code><?php echo htmlspecialchars($menuError); ?></code>
        </div>
    <?php endif; ?>

    <?php if (!empty($groupedItems)): ?>
        <?php foreach ($groupedItems as $section => $items): ?>
            <?php if (!empty($items)):
                $secId = 'sec-' . preg_replace('/[^a-z0-9]/', '', $section);
                $hasActive = !empty($sectionHasActive[$section]);
            ?>
                <div class="sidebar-sec" onclick="toggleSidebarSection('<?php echo $secId; ?>')">
                    <span><?php echo $sectionNames[$section] ?? ucfirst($section); ?></span>
                    <i class="fas fa-chevron-down sidebar-sec-arrow <?php echo $hasActive ? '' : 'collapsed'; ?>" id="arrow-<?php echo $secId; ?>"></i>
                </div>
                <ul class="sidebar-menu" id="<?php echo $secId; ?>" style="<?php echo $hasActive ? '' : 'display:none'; ?>">
                    <?php foreach ($items as $item): ?>
                        <?php
                        $itemFullUrl = rtrim($base . $item['url'], '/');
                        $isActive = ($reqPath === $itemFullUrl) || ($itemFullUrl !== $base && strpos($reqPath, $itemFullUrl . '/') === 0);
                        ?>
                        <li class="sidebar-item">
                            <a href="<?php echo $base . htmlspecialchars($item['url']); ?>"
                                class="sidebar-link <?php echo $isActive ? 'active' : ''; ?>">
                                <i class="<?php echo htmlspecialchars($item['icon'] ?? 'fas fa-circle'); ?>"></i>
                                <?php echo htmlspecialchars($item['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="padding:20px; color:#94a3b8; text-align:center;">
            <i class="fas fa-exclamation-circle" style="font-size:2rem; display:block; margin-bottom:10px;"></i>
            <strong>No menu items found</strong><br>
            <span style="font-size:0.8rem;">
                Check that admin_menu_items table has data<br>
                and AdminMenuService is working.
            </span>
        </div>
    <?php endif; ?>

</aside>
<!-- Sidebar toggle/restore handled by APS namespace (layout head) + admin.js (just before </body>).
     Both persist collapse state to localStorage. Do NOT redefine toggle functions here. -->
