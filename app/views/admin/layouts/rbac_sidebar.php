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
    'crm'         => '👥 CRM & Sales',
    'properties'  => '🏠 Properties',
    'mlm'         => '🔗 MLM Network',
    'finance'     => '💰 Finance',
    'bookings'    => '📅 Bookings',
    'cms'         => '📝 Content',
    'marketing'   => '📢 Marketing',
    'reports'     => '📈 Reports',
    'operations'  => '⚙️ Operations',
    'users'       => '👤 Users & Team',
    'locations'   => '📍 Locations',
    'settings'    => '🔧 Settings',
    'hrm'         => '👔 HR & Payroll',
    'legal'       => '⚖️ Legal',
    'sales'       => '🏷️ Sales',
    'services'    => '🛎️ Services',
    'system'      => '🖥️ System',
    'technology'  => '🤖 Technology',
    'commission'  => '💸 Commission',
    'security'    => '🔒 Security',
    'employee'    => '👩‍💼 Employee',
];

// Section sort order — controls which sections appear first
$sectionOrder = [
    'dashboards', 'crm', 'properties', 'sales', 'bookings', 'finance',
    'commission', 'mlm', 'hrm', 'employee', 'legal', 'marketing',
    'services', 'cms', 'operations', 'reports', 'locations', 'technology',
    'security', 'settings', 'system', 'users',
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
