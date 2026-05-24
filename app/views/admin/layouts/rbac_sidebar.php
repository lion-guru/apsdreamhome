<?php
/**
 * RBAC-Based Sidebar Menu - Unified Version
 * This file dynamically renders the admin sidebar menu based on user role
 * and custom permissions using the AdminMenuService.
 */

use App\Services\AdminMenuService;

// Get current page for active state
$currentPage = $active_page ?? basename($_SERVER['REQUEST_URI'] ?? '');
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';

// Initialize menu service
try {
    $menuService = new AdminMenuService();
    $menuItems = $menuService->getMenuItems();
} catch (Exception $e) {
    // Fallback to default menu if service fails
    $menuItems = [];
}

// Group menu items by section
$groupedItems = [];
foreach ($menuItems as $item) {
    $section = $item['section'] ?? 'main';
    $groupedItems[$section][] = $item;
}

// Section display names
$sectionNames = [
    'dashboards' => 'Dashboards',
    'crm' => 'CRM & Sales',
    'properties' => 'Properties',
    'mlm' => 'MLM Network',
    'financial' => 'Financial',
    'bookings' => 'Bookings',
    'cms' => 'Content',
    'marketing' => 'Marketing',
    'reports' => 'Reports',
    'operations' => 'Operations',
    'users' => 'Users & Team',
    'locations' => 'Locations',
    'settings' => 'Settings'
];
?>

<aside class="sidebar" id="sidebarMenu">
    <div class="sidebar-header">
        <a href="<?php echo $base; ?>/admin/dashboard" class="sidebar-logo">
            <i class="fas fa-home"></i>
            <span>APS Dream Home</span>
        </a>
        <div class="sidebar-sub">Super Admin Panel</div>
    </div>
    
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
    
    $sectionKeys = array_keys($groupedItems);
    ?>
    
    <?php foreach ($groupedItems as $section => $items): ?>
        <?php if (!empty($items)): 
            $secId = 'sec-' . preg_replace('/[^a-z0-9]/', '', $section);
            $isExpanded = !empty($sectionHasActive[$section]) || count($items) <= 2;
        ?>
        <div class="sidebar-sec" onclick="toggleSidebarSection('<?php echo $secId; ?>')" style="cursor:pointer;display:flex;justify-content:space-between;align-items:center;">
            <span><?php echo htmlspecialchars($sectionNames[$section] ?? ucfirst($section)); ?></span>
            <i class="fas fa-chevron-down sidebar-sec-arrow <?php echo $isExpanded ? '' : 'collapsed'; ?>" id="arrow-<?php echo $secId; ?>"></i>
        </div>
        <ul class="sidebar-menu" id="<?php echo $secId; ?>" style="<?php echo $isExpanded ? '' : 'display:none;'; ?>">
            <?php foreach ($items as $item): ?>
                <?php
                $itemFullUrl = rtrim($base . $item['url'], '/');
                // Active state: exact match or child path match
                $isActive = ($reqPath === $itemFullUrl) || ($itemFullUrl !== $base && strpos($reqPath, $itemFullUrl . '/') === 0);
                ?>
                <li class="sidebar-item">
                    <a href="<?php echo $base . htmlspecialchars($item['url']); ?>" 
                       class="sidebar-link <?php echo $isActive ? 'active' : ''; ?>">
                        <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                        <?php echo htmlspecialchars($item['name']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    <?php endforeach; ?>
    
    <script>
    function toggleSidebarSection(id) {
        const ul = document.getElementById(id);
        if (!ul) return;
        const isHidden = ul.style.display === 'none';
        ul.style.display = isHidden ? '' : 'none';
        const arrow = document.getElementById('arrow-' + id);
        if (arrow) arrow.classList.toggle('collapsed', !isHidden);
        try { localStorage.setItem('sidebar_' + id, isHidden ? 'open' : 'closed'); } catch(e) {}
    }
    // Restore saved section states on load
    (function() {
        document.querySelectorAll('.sidebar-menu[id]').forEach(function(el) {
            var saved = null;
            try { saved = localStorage.getItem('sidebar_' + el.id); } catch(e) {}
            if (saved === 'closed') {
                el.style.display = 'none';
                var arrow = document.getElementById('arrow-' + el.id);
                if (arrow) arrow.classList.add('collapsed');
            }
        });
    })();
    </script>
    
    <?php if (empty($menuItems)): ?>
        <!-- Fallback menu if no items from database -->
        <div class="sidebar-sec">Main</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/dashboard" class="sidebar-link <?php echo $currentPage == 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/analytics" class="sidebar-link <?php echo $currentPage == 'analytics' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Analytics
                </a>
            </li>
        </ul>
        
        <div class="sidebar-sec">CRM & Sales</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/leads" class="sidebar-link <?php echo $currentPage == 'leads' ? 'active' : ''; ?>">
                    <i class="fas fa-bullseye"></i> Leads
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/leads/scoring" class="sidebar-link <?php echo $currentPage == 'scoring' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> Lead Scoring
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/customers" class="sidebar-link <?php echo $currentPage == 'customers' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Customers
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/deals" class="sidebar-link <?php echo $currentPage == 'deals' ? 'active' : ''; ?>">
                    <i class="fas fa-handshake"></i> Deals
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/sales" class="sidebar-link <?php echo $currentPage == 'sales' ? 'active' : ''; ?>">
                    <i class="fas fa-rupee-sign"></i> Sales
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/campaigns" class="sidebar-link <?php echo $currentPage == 'campaigns' ? 'active' : ''; ?>">
                    <i class="fas fa-bullhorn"></i> Campaigns
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/bookings" class="sidebar-link <?php echo $currentPage == 'bookings' ? 'active' : ''; ?>">
                    <i class="fas fa-file-contract"></i> Bookings
                </a>
            </li>
        </ul>
        
        <div class="sidebar-sec">Content</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/erp/inventory" class="sidebar-link <?php echo $currentPage == 'erp-inventory' ? 'active' : ''; ?>">
                    <i class="fas fa-cubes"></i> Plot Inventory
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/erp/plot-profit" class="sidebar-link <?php echo $currentPage == 'erp-profit' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-pie"></i> Plot P&L
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/erp/land-mapping" class="sidebar-link <?php echo $currentPage == 'erp-land' ? 'active' : ''; ?>">
                    <i class="fas fa-map-marked-alt"></i> Land Mapping
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/colonies" class="sidebar-link <?php echo $currentPage == 'colonies' ? 'active' : ''; ?>">
                    <i class="fas fa-city"></i> Colonies
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/team" class="sidebar-link <?php echo $currentPage == 'team' ? 'active' : ''; ?>">
                    <i class="fas fa-user-tie"></i> Team
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/pages" class="sidebar-link <?php echo $currentPage == 'pages' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i> CMS Pages
                </a>
            </li>
        </ul>
        
        <div class="sidebar-sec">Properties</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/properties" class="sidebar-link <?php echo $currentPage == 'properties' ? 'active' : ''; ?>">
                    <i class="fas fa-building"></i> All Properties
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/projects" class="sidebar-link <?php echo $currentPage == 'projects' ? 'active' : ''; ?>">
                    <i class="fas fa-project-diagram"></i> Projects
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/plots" class="sidebar-link <?php echo $currentPage == 'plots' ? 'active' : ''; ?>">
                    <i class="fas fa-map"></i> Plots / Land
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/sites" class="sidebar-link <?php echo $currentPage == 'sites' ? 'active' : ''; ?>">
                    <i class="fas fa-map-marker-alt"></i> Sites
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/inventory" class="sidebar-link <?php echo $currentPage == 'inventory' ? 'active' : ''; ?>">
                    <i class="fas fa-warehouse"></i> Plot Inventory
                </a>
            </li>
        </ul>
        
        <div class="sidebar-sec">Operations</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/visits" class="sidebar-link <?php echo $currentPage == 'visits' ? 'active' : ''; ?>">
                    <i class="fas fa-car"></i> Site Visits
                </a>
            </li>
        </ul>
        
        <div class="sidebar-sec">Settings</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/settings" class="sidebar-link <?php echo $currentPage == 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i> Site Settings
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/api-keys" class="sidebar-link <?php echo $currentPage == 'api-keys' ? 'active' : ''; ?>">
                    <i class="fas fa-key"></i> API Keys
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/logout" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    <?php endif; ?>
</aside>
