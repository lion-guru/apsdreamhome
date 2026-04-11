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
    'main' => 'Main',
    'crm' => 'CRM & Sales',
    'properties' => 'Properties',
    'mlm' => 'MLM Network',
    'operations' => 'Operations',
    'marketing' => 'Marketing',
    'ai' => 'AI & Technology',
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
    
    <?php foreach ($groupedItems as $section => $items): ?>
        <?php if (!empty($items)): ?>
        <div class="sidebar-sec"><?php echo htmlspecialchars($sectionNames[$section] ?? ucfirst($section)); ?></div>
        <ul class="sidebar-menu">
            <?php foreach ($items as $item): ?>
                <?php
                $hasChildren = !empty($item['children']);
                $isActive = ($currentPage === ltrim($item['url'], '/')) || 
                          (strpos($_SERVER['REQUEST_URI'] ?? '', $item['url']) !== false);
                ?>
                <li class="sidebar-item">
                    <a href="<?php echo $base . htmlspecialchars($item['url']); ?>" 
                       class="sidebar-link <?php echo $isActive ? 'active' : ''; ?>">
                        <i class="fas <?php echo htmlspecialchars($item['icon']); ?>"></i>
                        <?php echo htmlspecialchars($item['name']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    <?php endforeach; ?>
    
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
