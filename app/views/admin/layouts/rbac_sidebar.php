<?php

/**
 * RBAC-Based Sidebar Menu - Unified Version (FIXED)
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
    $section = strtolower($item['section'] ?? 'main');
    $groupedItems[$section][] = $item;
}

// Section display names with icons
$sectionNames = [
    'dashboards' => '📊 Dashboards',
    'crm' => '👥 CRM & Sales',
    'properties' => '🏠 Properties',
    'mlm' => '🔗 MLM Network',
    'finance' => '💰 Finance',
    'bookings' => '📅 Bookings',
    'cms' => '📝 Content',
    'marketing' => '📢 Marketing',
    'reports' => '📈 Reports',
    'operations' => '⚙️ Operations',
    'users' => '👤 Users & Team',
    'locations' => '📍 Locations',
    'settings' => '🔧 Settings',
    'hrm' => '👔 HR & Payroll',
    'legal' => '⚖️ Legal',
    'sales' => '🏷️ Sales',
    'services' => '🛎️ Services',
    'system' => '⚙️ System'
];
?>

<aside class="sidebar" id="sidebarMenu">
    <div class="sidebar-header">
        <a href="<?php echo $base; ?>/admin/dashboard" class="sidebar-logo">
            <i class="fas fa-home"></i>
            <span>APS Dream Home</span>
        </a>
        <div class="sidebar-sub">Admin Panel v2.0</div>
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

    <!-- Expand All / Collapse All toggle -->
    <div class="sidebar-sec" onclick="toggleAllSidebarSections()">
        <span><i class="fas fa-layer-group"></i> All Sections</span>
        <i class="fas fa-chevron-down sidebar-sec-arrow" id="arrow-expand-all"></i>
    </div>

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
    <?php endif; ?>

    <?php if (empty($menuItems)): ?>
        <!-- IMPROVED Fallback menu with better organization and icons -->
        <div class="sidebar-sec" onclick="toggleSidebarSection('sec-dashboards')">
            <span><i class="fas fa-chart-pie"></i> Dashboards</span>
            <i class="fas fa-chevron-down sidebar-sec-arrow" id="arrow-sec-dashboards"></i>
        </div>
        <ul class="sidebar-menu" id="sec-dashboards">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/dashboard" class="sidebar-link <?php echo $currentPage == 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Main Dashboard
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/analytics" class="sidebar-link <?php echo $currentPage == 'analytics' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> Analytics
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/reports" class="sidebar-link <?php echo $currentPage == 'reports' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i> Reports
                </a>
            </li>
        </ul>

        <div class="sidebar-sec" onclick="toggleSidebarSection('sec-crm')">
            <span><i class="fas fa-users"></i> CRM & Sales</span>
            <i class="fas fa-chevron-down sidebar-sec-arrow" id="arrow-sec-crm"></i>
        </div>
        <ul class="sidebar-menu" id="sec-crm">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/leads" class="sidebar-link <?php echo $currentPage == 'leads' ? 'active' : ''; ?>">
                    <i class="fas fa-bullseye"></i> Leads
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/leads/scoring" class="sidebar-link <?php echo $currentPage == 'scoring' ? 'active' : ''; ?>">
                    <i class="fas fa-star"></i> Lead Scoring
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/users" class="sidebar-link <?php echo $currentPage == 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-user-friends"></i> users
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
                    <i class="fas fa-calendar-check"></i> Bookings
                </a>
            </li>
        </ul>

        <div class="sidebar-sec" onclick="toggleSidebarSection('sec-properties')">
            <span><i class="fas fa-building"></i> Properties</span>
            <i class="fas fa-chevron-down sidebar-sec-arrow" id="arrow-sec-properties"></i>
        </div>
        <ul class="sidebar-menu" id="sec-properties">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/properties" class="sidebar-link <?php echo $currentPage == 'properties' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i> All Properties
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/properties/user" class="sidebar-link <?php echo strpos($currentPage, 'user-properties') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-user-plus"></i> User Properties
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/properties/plot" class="sidebar-link <?php echo $currentPage == 'plot' ? 'active' : ''; ?>">
                    <i class="fas fa-map"></i> Plot Inventory
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/locations" class="sidebar-link <?php echo $currentPage == 'locations' ? 'active' : ''; ?>">
                    <i class="fas fa-map-marker-alt"></i> Locations
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/property/images" class="sidebar-link <?php echo $currentPage == 'property-images' ? 'active' : ''; ?>">
                    <i class="fas fa-images"></i> Property Images
                </a>
            </li>
        </ul>

        <div class="sidebar-sec" onclick="toggleSidebarSection('sec-inventory')">
            <span><i class="fas fa-boxes"></i> Inventory</span>
            <i class="fas fa-chevron-down sidebar-sec-arrow" id="arrow-sec-inventory"></i>
        </div>
        <ul class="sidebar-menu" id="sec-inventory">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/erp/inventory" class="sidebar-link <?php echo $currentPage == 'erp-inventory' ? 'active' : ''; ?>">
                    <i class="fas fa-warehouse"></i> Plot Inventory
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/erp/plot-profit" class="sidebar-link <?php echo $currentPage == 'plot-profit' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Plot Profit
                </a>
            </li>
        </ul>

        <div class="sidebar-sec" onclick="toggleSidebarSection('sec-content')">
            <span><i class="fas fa-edit"></i> Content</span>
            <i class="fas fa-chevron-down sidebar-sec-arrow" id="arrow-sec-content"></i>
        </div>
        <ul class="sidebar-menu" id="sec-content">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/pages" class="sidebar-link <?php echo $currentPage == 'pages' ? 'active' : ''; ?>">
                    <i class="fas fa-file"></i> Pages
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/blogs" class="sidebar-link <?php echo $currentPage == 'blogs' ? 'active' : ''; ?>">
                    <i class="fas fa-newspaper"></i> Blogs
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/gallery" class="sidebar-link <?php echo $currentPage == 'gallery' ? 'active' : ''; ?>">
                    <i class="fas fa-images"></i> Gallery
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/testimonials" class="sidebar-link <?php echo $currentPage == 'testimonials' ? 'active' : ''; ?>">
                    <i class="fas fa-quote-left"></i> Testimonials
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/faqs" class="sidebar-link <?php echo $currentPage == 'faqs' ? 'active' : ''; ?>">
                    <i class="fas fa-question-circle"></i> FAQs
                </a>
            </li>
        </ul>

        <div class="sidebar-sec" onclick="toggleSidebarSection('sec-services')">
            <span><i class="fas fa-concierge-bell"></i> Services</span>
            <i class="fas fa-chevron-down sidebar-sec-arrow" id="arrow-sec-services"></i>
        </div>
        <ul class="sidebar-menu" id="sec-services">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/services" class="sidebar-link <?php echo $currentPage == 'services' ? 'active' : ''; ?>">
                    <i class="fas fa-list-alt"></i> Service Interest
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/services/enquiry" class="sidebar-link <?php echo $currentPage == 'services-enquiry' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope-open-text"></i> Service Enquiries
                </a>
            </li>
        </ul>

        <div class="sidebar-sec" onclick="toggleSidebarSection('sec-users')">
            <span><i class="fas fa-users-cog"></i> Users & Team</span>
            <i class="fas fa-chevron-down sidebar-sec-arrow" id="arrow-sec-users"></i>
        </div>
        <ul class="sidebar-menu" id="sec-users">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/users" class="sidebar-link <?php echo $currentPage == 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> All Users
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/admin-users" class="sidebar-link <?php echo $currentPage == 'admin-users' ? 'active' : ''; ?>">
                    <i class="fas fa-user-shield"></i> Admin Users
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/roles" class="sidebar-link <?php echo $currentPage == 'roles' ? 'active' : ''; ?>">
                    <i class="fas fa-user-tag"></i> Roles
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/permissions" class="sidebar-link <?php echo $currentPage == 'permissions' ? 'active' : ''; ?>">
                    <i class="fas fa-key"></i> Permissions
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/users" class="sidebar-link <?php echo $currentPage == 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-id-badge"></i> users
                </a>
            </li>
        </ul>

        <div class="sidebar-sec" onclick="toggleSidebarSection('sec-ai')">
            <span><i class="fas fa-robot"></i> AI Features</span>
            <i class="fas fa-chevron-down sidebar-sec-arrow" id="arrow-sec-ai"></i>
        </div>
        <ul class="sidebar-menu" id="sec-ai">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/ai-dashboard" class="sidebar-link <?php echo $currentPage == 'ai-dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-brain"></i> AI Dashboard
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/ai/chatbot" class="sidebar-link <?php echo $currentPage == 'ai-chatbot' ? 'active' : ''; ?>">
                    <i class="fas fa-comment-dots"></i> AI Chatbot
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/ai/valuation" class="sidebar-link <?php echo $currentPage == 'ai-valuation' ? 'active' : ''; ?>">
                    <i class="fas fa-calculator"></i> Property Valuation
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/ai/analytics" class="sidebar-link <?php echo $currentPage == 'ai-analytics' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-area"></i> AI Analytics
                </a>
            </li>
        </ul>

        <div class="sidebar-sec" onclick="toggleSidebarSection('sec-financial')">
            <span><i class="fas fa-wallet"></i> Financial</span>
            <i class="fas fa-chevron-down sidebar-sec-arrow" id="arrow-sec-financial"></i>
        </div>
        <ul class="sidebar-menu" id="sec-financial">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/payments" class="sidebar-link <?php echo $currentPage == 'payments' ? 'active' : ''; ?>">
                    <i class="fas fa-credit-card"></i> Payments
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/invoices" class="sidebar-link <?php echo $currentPage == 'invoices' ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice"></i> Invoices
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/expense" class="sidebar-link <?php echo $currentPage == 'expense' ? 'active' : ''; ?>">
                    <i class="fas fa-receipt"></i> Expenses
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/wallet" class="sidebar-link <?php echo $currentPage == 'wallet' ? 'active' : ''; ?>">
                    <i class="fas fa-wallet"></i> Wallet
                </a>
            </li>
        </ul>

        <div class="sidebar-sec" onclick="toggleSidebarSection('sec-mlm')">
            <span><i class="fas fa-network-wired"></i> MLM Network</span>
            <i class="fas fa-chevron-down sidebar-sec-arrow" id="arrow-sec-mlm"></i>
        </div>
        <ul class="sidebar-menu" id="sec-mlm">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/mlm/dashboard" class="sidebar-link <?php echo $currentPage == 'mlm-dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-project-diagram"></i> MLM Dashboard
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/mlm/tree" class="sidebar-link <?php echo $currentPage == 'mlm-tree' ? 'active' : ''; ?>">
                    <i class="fas fa-sitemap"></i> Genealogy Tree
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/mlm/commission" class="sidebar-link <?php echo $currentPage == 'mlm-commission' ? 'active' : ''; ?>">
                    <i class="fas fa-coins"></i> Commissions
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/mlm/users" class="sidebar-link <?php echo $currentPage == 'mlm-users' ? 'active' : ''; ?>">
                    <i class="fas fa-user-tie"></i> users
                </a>
            </li>
        </ul>

        <div class="sidebar-sec" onclick="toggleSidebarSection('sec-settings')">
            <span><i class="fas fa-cog"></i> Settings</span>
            <i class="fas fa-chevron-down sidebar-sec-arrow" id="arrow-sec-settings"></i>
        </div>
        <ul class="sidebar-menu" id="sec-settings">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/settings" class="sidebar-link <?php echo $currentPage == 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-sliders-h"></i> General Settings
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/settings/company" class="sidebar-link <?php echo $currentPage == 'company-settings' ? 'active' : ''; ?>">
                    <i class="fas fa-building"></i> Company Info
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/settings/email" class="sidebar-link <?php echo $currentPage == 'email-settings' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i> Email Settings
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/settings/sms" class="sidebar-link <?php echo $currentPage == 'sms-settings' ? 'active' : ''; ?>">
                    <i class="fas fa-sms"></i> SMS Settings
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/settings/payment" class="sidebar-link <?php echo $currentPage == 'payment-settings' ? 'active' : ''; ?>">
                    <i class="fas fa-credit-card"></i> Payment Gateway
                </a>
            </li>
        </ul>

        <div class="sidebar-sec" onclick="toggleSidebarSection('sec-system')">
            <span><i class="fas fa-server"></i> System</span>
            <i class="fas fa-chevron-down sidebar-sec-arrow" id="arrow-sec-system"></i>
        </div>
        <ul class="sidebar-menu" id="sec-system">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/logs" class="sidebar-link <?php echo $currentPage == 'logs' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i> Activity Logs
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/backup" class="sidebar-link <?php echo $currentPage == 'backup' ? 'active' : ''; ?>">
                    <i class="fas fa-database"></i> Database Backup
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/cache" class="sidebar-link <?php echo $currentPage == 'cache' ? 'active' : ''; ?>">
                    <i class="fas fa-broom"></i> Clear Cache
                </a>
            </li>
        </ul>

    <?php endif; ?>

</aside>
<!-- Sidebar toggle/restore handled by APS namespace (layout head) + admin.js (just before </body>).
     Both persist collapse state to localStorage. Do NOT redefine toggle functions here. -->