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

// ═══════════════════════════════════════════════════════════════
// 5 ENTERPRISE HUBS — Consolidated from 23 legacy sections
// ═══════════════════════════════════════════════════════════════
// Hub 1: Inventory & Projects
// Hub 2: Sales & CRM Pipeline
// Hub 3: MLM Network & Team
// Hub 4: Finance & Collections
// Hub 5: Enterprise Control
// ═══════════════════════════════════════════════════════════════

// Hub definitions — map legacy sections to 5 hubs
$hubDefinitions = [
    'inventory' => [
        'label' => '🏢 1. Inventory & Projects',
        'icon' => 'fas fa-building',
        'sections' => ['properties', 'locations', 'land', 'colonies', 'plots', 'sites', 'cms'],
    ],
    'sales' => [
        'label' => '🎯 2. Sales & CRM Pipeline',
        'icon' => 'fas fa-funnel-dollar',
        'sections' => ['crm', 'sales', 'legal', 'leads', 'inquiries', 'site_visits', 'bookings', 'registry', 'possession'],
    ],
    'mlm' => [
        'label' => '🌲 3. MLM Network & Team',
        'icon' => 'fas fa-sitemap',
        'sections' => ['mlm', 'commission', 'associates', 'payouts', 'rewards', 'mlm_settings', 'referrals'],
    ],
    'finance' => [
        'label' => '💳 4. Finance & Collections',
        'icon' => 'fas fa-coins',
        'sections' => ['finance', 'commission', 'cashbook', 'expenses', 'banking', 'gst', 'tds', 'efiling', 'collections', 'penalties', 'emi_auto_pay', 'plot_costs', 'payroll', 'billing'],
    ],
    'control' => [
        'label' => '⚙️ 5. Enterprise Control',
        'icon' => 'fas fa-cogs',
        'sections' => ['users', 'roles', 'settings', 'system', 'hrm', 'security', 'ai_tech', 'communication', 'saas', 'operations', 'reports', 'analytics', 'audit_log', 'backup', 'cache', 'webhooks', 'api', 'company', 'menu_permissions', 'services', 'marketing', 'employee', 'hr', 'training', 'quality', 'health'],
    ],
];

// Build section-to-hub mapping for quick lookup
$sectionToHub = [];
foreach ($hubDefinitions as $hubKey => $def) {
    foreach ($def['sections'] as $section) {
        $sectionToHub[$section] = $hubKey;
    }
}

// Default hub for unknown sections
$defaultHub = 'control';

// Section display names with icons (for individual section headers within hubs)
$sectionNames = [
    'dashboards'  => '📊 Dashboards',
    'crm'         => '👥 CRM & Leads',
    'properties'  => '🏠 Properties & Land',
    'mlm'         => '📜 MLM Network',
    'finance'     => '💰 Finance & Accounting',
    'commission'  => '💸 Commission Engine',
    'cms'         => '📖 Content (CMS)',
    'marketing'   => '📢 Marketing & Referrals',
    'reports'     => '📈 Reports & Analytics',
    'operations'  => '⚙️ Operations',
    'users'       => '👤 Users & Roles',
    'locations'   => '📍 Locations',
    'settings'    => '⚙️ Settings',
    'hrm'         => '👨‍💼 HR & Payroll',
    'legal'       => '⚖️ Legal & Compliance',
    'sales'       => '🏷️ Sales & Bookings',
    'services'    => '🛠️ Services',
    'system'      => '🔧 System Admin',
    'ai_tech'     => '🤖 AI & Technology',
    'security'    => '🔒 Security',
    'employee'    => '👨‍💼💼 Employee Portal',
    'saas'        => '☁️ SaaS / Multi-Tenant',
    'communication' => '📞 Communication',
    'leads'       => '🎯 Leads',
    'inquiries'   => '📧 Inquiries',
    'site_visits' => '🏗️ Site Visits',
    'bookings'    => '📋 Bookings',
    'registry'    => '📝 Registry',
    'possession'  => '🔑 Possession',
    'cashbook'    => '💵 Cash Book',
    'expenses'    => '💸 Expenses',
    'banking'     => '🏦 Banking',
    'gst'         => '🧾 GST',
    'tds'         => '📝 TDS',
    'efiling'     => '📤 E-Filing',
    'collections' => '💰 Collections',
    'penalties'   => '⚠️ Penalties',
    'emi_auto_pay'=> '🔁 EMI Auto-Pay',
    'plot_costs'  => '📊 Plot Costs',
    'payroll'     => '💼 Payroll',
    'billing'     => '🧾 Billing',
    'mlm_settings'=> '⚙️ MLM Settings',
    'associates'  => '👥 Associates',
    'payouts'     => '💸 Payouts',
    'rewards'     => '🏆 Rewards',
    'referrals'   => '🔗 Referrals',
    'audit_log'   => '📋 Audit Log',
    'backup'      => '💾 Backup',
    'cache'       => '⚡ Cache',
    'webhooks'    => '🔗 Webhooks',
    'api'         => '🔌 API',
    'company'     => '🏢 Company',
    'menu_permissions' => '🔐 Menu Permissions',
    'training'    => '🎓 Training',
    'quality'     => '✅ Quality',
    'health'      => '🏥 Health',
    'services'    => '🛠️ Services',
    'hr'          => '👨‍💼 HR',
];

// Hub sort order
$hubOrder = ['inventory', 'sales', 'mlm', 'finance', 'control'];

// Group menu items by hub (using 5 Enterprise Hubs)
$hubbedItems = [];
foreach ($menuItems as $item) {
    $section = strtolower($item['section'] ?? 'main');
    $hub = $sectionToHub[$section] ?? $defaultHub;
    $hubbedItems[$hub][$section][] = $item;
}

// Sort hubs by defined order
$sortedHubbed = [];
foreach ($hubOrder as $hub) {
    if (isset($hubbedItems[$hub])) {
        // Sort sections within each hub alphabetically (or keep original order)
        ksort($hubbedItems[$hub]);
        $sortedHubbed[$hub] = $hubbedItems[$hub];
    }
}
// Append any hubs not in the order list
foreach ($hubbedItems as $hub => $sections) {
    if (!isset($sortedHubbed[$hub])) {
        ksort($sections);
        $sortedHubbed[$hub] = $sections;
    }
}
$hubbedItems = $sortedHubbed;
?>

<aside class="sidebar" id="sidebarMenu">
    <div class="sidebar-header">
        <a href="<?php echo e($base); ?>/admin/dashboard" class="sidebar-logo">
            <?php
            $tenantLogo = TenantContext::getLogo();
            $tenantName = TenantContext::getName();
            $tenantColors = TenantContext::getColors();
            ?>
            <?php if ($tenantLogo): ?>
                <img src="<?php echo htmlspecialchars($tenantLogo ?? '');?>" alt="Logo" >
            <?php else: ?>
                <i class="fas fa-home"></i>
            <?php endif; ?>
            <span><?php echo htmlspecialchars($tenantName ?? ''); ?></span>
        </a>
        <div class="sidebar-sub">Admin Panel v2.0</div>
    </div>

    <!-- Sidebar Search / Quick Filter -->
    <div class="sidebar-search p-2">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-transparent border-end-0">
                <i class="fas fa-search text-muted"></i>
            </span>
            <input type="search" class="form-control border-start-0" id="sidebarSearch" 
                   placeholder="Quick find feature..." aria-label="Search admin menu"
                   autocomplete="off">
            <span class="input-group-text bg-transparent border-start-0" id="clearSearchBtn" style="display:none; cursor:pointer;">
                <i class="fas fa-times text-muted"></i>
            </span>
        </div>
    </div>

    <?php
    // Tenant Switch Banner — show when SuperAdmin is impersonating a tenant
    $switchActive = !empty($_SESSION['tenant_switch_active']);
    $switchName = $_SESSION['tenant_switch_name'] ?? '';
    ?>
    <?php if ($switchActive): ?>
    <div >
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="fas fa-exchange-alt"></i>
            <strong>Viewing: <?= htmlspecialchars($switchName ?? '') ?></strong>
        </div>
        <form method="POST" action="<?= $base ?>/admin/tenants/stop-switch" >
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <button type="submit" class="btn btn-sm btn-light w-100">
                <i class="fas fa-undo me-1"></i>Back to My Tenant
            </button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Tenant CSS Variables for white-labeling -->
    <style>
        :root {
            --tenant-primary: <?php echo htmlspecialchars($tenantColors['primary'] ?? ''); ?>;
            --tenant-secondary: <?php echo htmlspecialchars($tenantColors['secondary'] ?? ''); ?>;
        }
    </style>

    <?php
    $reqUri = $_SERVER['REQUEST_URI'] ?? '';
    $reqPath = rtrim(parse_url($reqUri, PHP_URL_PATH), '/');

    // Determine which hub/section has the active item (to auto-expand it)
    $hubHasActive = [];
    $sectionHasActive = [];
    foreach ($hubbedItems as $hub => $sections) {
        foreach ($sections as $section => $items) {
            foreach ($items as $item) {
                $itemFullUrl = rtrim($base . $item['url'], '/');
                if ($reqPath === $itemFullUrl || ($itemFullUrl !== $base && strpos($reqPath, $itemFullUrl . '/') === 0)) {
                    $hubHasActive[$hub] = true;
                    $sectionHasActive[$hub][$section] = true;
                    break 3;
                }
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
        <div >
            <i class="fas fa-exclamation-triangle"></i> Sidebar error:<br>
            <code><?php echo htmlspecialchars($menuError ?? ''); ?></code>
        </div>
    <?php endif; ?>

    <?php if (!empty($hubbedItems)): ?>
        <?php foreach ($hubbedItems as $hub => $sections): ?>
            <?php if (!empty($sections)): ?>
                <?php
                $hubId = 'hub-' . preg_replace('/[^a-z0-9]/', '', $hub);
                $hubDef = $hubDefinitions[$hub] ?? ['label' => ucfirst($hub), 'icon' => 'fas fa-folder'];
                $hubHasActive = !empty($hubHasActive[$hub]);
                ?>
                <!-- Hub: <?php echo $hubDef['label']; ?> -->
                <div class="sidebar-hub mb-3">
                    <div class="sidebar-hub-header" onclick="toggleSidebarHub('<?php echo e($hubId); ?>')">
                        <span class="d-flex align-items-center gap-2">
                            <i class="<?php echo $hubDef['icon']; ?> text-primary"></i>
                            <strong><?php echo $hubDef['label']; ?></strong>
                        </span>
                        <i class="fas fa-chevron-down sidebar-sec-arrow <?php echo $hubHasActive ? '' : 'collapsed'; ?>" id="arrow-<?php echo e($hubId); ?>"></i>
                    </div>
                    <div class="sidebar-hub-content" id="<?php echo e($hubId); ?>" >
                        <?php foreach ($sections as $section => $items): ?>
                            <?php if (!empty($items)): ?>
                                <?php
                                $secId = 'sec-' . preg_replace('/[^a-z0-9]/', '', $hub . '-' . $section);
                                $sectionHasActiveFlag = !empty($sectionHasActive[$hub][$section]);
                                ?>
                                <div class="sidebar-sec" onclick="toggleSidebarSection('<?php echo e($secId); ?>')">
                                    <span><?php echo $sectionNames[$section] ?? ucfirst($section); ?></span>
                                    <i class="fas fa-chevron-down sidebar-sec-arrow <?php echo $sectionHasActiveFlag ? '' : 'collapsed'; ?>" id="arrow-<?php echo e($secId); ?>"></i>
                                </div>
                                <ul class="sidebar-menu" id="<?php echo e($secId); ?>" >
                                    <?php foreach ($items as $item): ?>
                                        <?php
                                        $itemFullUrl = rtrim($base . $item['url'], '/');
                                        $isActive = ($reqPath === $itemFullUrl) || ($itemFullUrl !== $base && strpos($reqPath, $itemFullUrl . '/') === 0);
                                        ?>
                                        <li class="sidebar-item">
                                            <a href="<?php echo $base . htmlspecialchars($item['url'] ?? ''); ?>"
                                                class="sidebar-link <?php echo $isActive ? 'active' : ''; ?>">
                                                <i class="<?php echo htmlspecialchars($item['icon'] ?? 'fas fa-circle'); ?>"></i>
                                                <?php echo htmlspecialchars($item['name'] ?? ''); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div >
            <i class="fas fa-exclamation-circle"></i>
            <strong>No menu items found</strong><br>
            <span >
                Check that admin_menu_items table has data<br>
                and AdminMenuService is working.
            </span>
        </div>
    <?php endif; ?>

</aside>

<!-- Sidebar Search / Filter JavaScript -->
<script>
(function() {
    const searchInput = document.getElementById('sidebarSearch');
    const clearBtn = document.getElementById('clearSearchBtn');
    const sidebar = document.getElementById('sidebarMenu');
    if (!searchInput || !sidebar) return;

    const hubs = sidebar.querySelectorAll('.sidebar-hub');
    const sections = sidebar.querySelectorAll('.sidebar-sec');
    const items = sidebar.querySelectorAll('.sidebar-item');
    const menus = sidebar.querySelectorAll('.sidebar-menu');

    let debounceTimer = null;

    function filterSidebar(query) {
        const q = query.trim().toLowerCase();
        
        // Show/hide clear button
        if (clearBtn) clearBtn.style.display = q ? 'block' : 'none';

        if (!q) {
            // Show everything, restore collapsed state from localStorage
            hubs.forEach(hub => hub.style.display = '');
            sections.forEach(sec => sec.style.display = '');
            menus.forEach(menu => menu.style.display = '');
            items.forEach(item => item.style.display = '');
            restoreCollapsedState();
            return;
        }

        // Hide all initially
        items.forEach(item => {
            const text = item.textContent.trim().toLowerCase();
            const match = text.includes(q);
            item.style.display = match ? '' : 'none';
        });

        // Show parent hubs/sections that have matching items
        hubs.forEach(hub => {
            const hubItems = hub.querySelectorAll('.sidebar-item');
            const hasMatch = Array.from(hubItems).some(item => item.style.display !== 'none');
            hub.style.display = hasMatch ? '' : 'none';
            if (hasMatch) {
                // Auto-expand matching hub
                const content = hub.querySelector('.sidebar-hub-content');
                if (content) content.style.display = '';
                const arrow = hub.querySelector('.sidebar-sec-arrow');
                if (arrow) arrow.classList.remove('collapsed');
            }
        });

        sections.forEach(sec => {
            const secItems = sec.parentElement.querySelectorAll('.sidebar-item');
            const hasMatch = Array.from(secItems).some(item => item.style.display !== 'none');
            sec.style.display = hasMatch ? '' : 'none';
            if (hasMatch) {
                const menuId = sec.getAttribute('onclick')?.match(/toggleSidebarSection\('([^']+)'\)/);
                if (menuId) {
                    const menu = document.getElementById(menuId[1]);
                    if (menu) menu.style.display = '';
                    const arrow = document.getElementById('arrow-' + menuId[1]);
                    if (arrow) arrow.classList.remove('collapsed');
                }
            }
        });

        // Show menus that have visible items
        menus.forEach(menu => {
            const menuItems = menu.querySelectorAll('.sidebar-item');
            const hasMatch = Array.from(menuItems).some(item => item.style.display !== 'none');
            menu.style.display = hasMatch ? '' : 'none';
        });
    }

    function restoreCollapsedState() {
        try {
            const saved = localStorage.getItem('adminSidebarSections');
            if (!saved) return;
            const state = JSON.parse(saved);
            Object.keys(state).forEach(id => {
                const menu = document.getElementById(id);
                const arrow = document.getElementById('arrow-' + id);
                if (menu) menu.style.display = state[id] ? '' : 'none';
                if (arrow) arrow.classList.toggle('collapsed', !state[id]);
            });
        } catch (e) {}
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => filterSidebar(this.value), 150);
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            filterSidebar('');
            searchInput.focus();
        });
    }

    // Keyboard shortcut: Ctrl/Cmd + K focuses search
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            searchInput.focus();
        }
    });
})();
</script>

<!-- Hub/Section toggle functions (for search auto-expand) -->
<script>
function toggleSidebarHub(hubId) {
    const content = document.getElementById(hubId);
    const arrow = document.getElementById('arrow-' + hubId);
    if (!content) return;
    const hidden = content.style.display === 'none';
    content.style.display = hidden ? '' : 'none';
    if (arrow) arrow.classList.toggle('collapsed', !hidden);
}
</script>