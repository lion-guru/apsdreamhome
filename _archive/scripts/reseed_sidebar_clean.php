<?php
/**
 * Reseed Sidebar Menu Script - Safe, Clean, Consolidated & Deduplicated
 * 
 * This script rebuilds the admin_menu_items and grants super_admin/admin full access
 * and manager/employee/associate/agent/customer default view-only access.
 * It is fully idempotent and safe to run.
 * 
 * Run via: php scripts/reseed_sidebar_clean.php
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

try {
    $pdo = \App\Core\Database::getInstance()->getConnection();

    echo "=== SIDEBAR RESEED & CLEANUP ===\n\n";

    // 1. Ensure 'section' column exists in admin_menu_items
    $cols = $pdo->query("SHOW COLUMNS FROM admin_menu_items")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('section', $cols, true)) {
        $pdo->exec("ALTER TABLE admin_menu_items ADD COLUMN section VARCHAR(50) DEFAULT 'main' AFTER url");
        echo "  + Added 'section' column to admin_menu_items\n";
    }

    // 2. Disable foreign key checks for truncation
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    try {
        $pdo->exec("TRUNCATE TABLE admin_role_menu_permissions");
        echo "âœ“ Truncated admin_role_menu_permissions\n";
    } catch (PDOException $e) {
        echo "âš ï¸� Could not truncate admin_role_menu_permissions: " . $e->getMessage() . "\n";
    }
    try {
        $pdo->exec("TRUNCATE TABLE admin_user_menu_permissions");
        echo "âœ“ Truncated admin_user_menu_permissions\n";
    } catch (PDOException $e) {
        echo "âš ï¸� Could not truncate admin_user_menu_permissions: " . $e->getMessage() . "\n";
    }
    try {
        $pdo->exec("TRUNCATE TABLE admin_menu_items");
        echo "âœ“ Truncated admin_menu_items\n";
    } catch (PDOException $e) {
        echo "âš ï¸� Could not truncate admin_menu_items: " . $e->getMessage() . "\n";
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "âœ“ Truncated menu and permission tables\n";

    // 3. Define clean consolidated menu items (No duplicates, organized strictly by section)
    $menuItems = [
        // --- SECTION: dashboards ---
        ['name' => 'ERP Overview', 'url' => '/admin/erp', 'icon' => 'fas fa-th-large', 'section' => 'dashboards', 'order_index' => 1, 'permission_key' => 'dashboard.view'],
        ['name' => 'Main Dashboard', 'url' => '/admin/dashboard', 'icon' => 'fas fa-home', 'section' => 'dashboards', 'order_index' => 2, 'permission_key' => 'dashboard.view'],
        ['name' => 'CEO Dashboard', 'url' => '/admin/dashboard/ceo', 'icon' => 'fas fa-user-tie', 'section' => 'dashboards', 'order_index' => 3, 'permission_key' => 'dashboard.view'],
        ['name' => 'CFO Dashboard', 'url' => '/admin/dashboard/cfo', 'icon' => 'fas fa-wallet', 'section' => 'dashboards', 'order_index' => 4, 'permission_key' => 'dashboard.view'],
        ['name' => 'Finance Dashboard', 'url' => '/admin/dashboard/finance', 'icon' => 'fas fa-piggy-bank', 'section' => 'dashboards', 'order_index' => 5, 'permission_key' => 'dashboard.view'],
        ['name' => 'Sales Dashboard', 'url' => '/admin/dashboard/sales', 'icon' => 'fas fa-chart-line', 'section' => 'dashboards', 'order_index' => 6, 'permission_key' => 'dashboard.view'],
        
        // --- SECTION: crm ---
        ['name' => 'Leads Manager', 'url' => '/admin/leads', 'icon' => 'fas fa-user-plus', 'section' => 'crm', 'order_index' => 1, 'permission_key' => 'leads.view'],
        ['name' => 'Lead Kanban', 'url' => '/admin/lead-kanban', 'icon' => 'fas fa-columns', 'section' => 'crm', 'order_index' => 2, 'permission_key' => 'leads.view'],
        ['name' => 'Lead Scoring', 'url' => '/admin/leads/scoring', 'icon' => 'fas fa-star', 'section' => 'crm', 'order_index' => 3, 'permission_key' => 'leads.view'],
        ['name' => 'Deals Board', 'url' => '/admin/deals', 'icon' => 'fas fa-handshake', 'section' => 'crm', 'order_index' => 4, 'permission_key' => 'deals.view'],
        ['name' => 'Site Visits', 'url' => '/admin/visits', 'icon' => 'fas fa-calendar-alt', 'section' => 'crm', 'order_index' => 5, 'permission_key' => 'visits.view'],
        ['name' => 'Enquiries', 'url' => '/admin/inquiries', 'icon' => 'fas fa-envelope', 'section' => 'crm', 'order_index' => 6, 'permission_key' => 'inquiries.view'],
        ['name' => 'Campaigns', 'url' => '/admin/campaigns', 'icon' => 'fas fa-bullhorn', 'section' => 'crm', 'order_index' => 7, 'permission_key' => 'campaigns.view'],
        ['name' => 'Support Tickets', 'url' => '/admin/support-tickets', 'icon' => 'fas fa-ticket-alt', 'section' => 'crm', 'order_index' => 8, 'permission_key' => 'tickets.view'],
        ['name' => 'NPS Surveys', 'url' => '/admin/nps', 'icon' => 'fas fa-poll', 'section' => 'crm', 'order_index' => 9, 'permission_key' => 'nps.view'],
        ['name' => 'Customer Referrals', 'url' => '/admin/referrals', 'icon' => 'fas fa-share-alt', 'section' => 'crm', 'order_index' => 10, 'permission_key' => 'referrals.view'],

        // --- SECTION: properties ---
        ['name' => 'Colony Pipeline', 'url' => '/admin/colony-pipeline', 'icon' => 'fas fa-road', 'section' => 'properties', 'order_index' => 1, 'permission_key' => 'property.view'],
        ['name' => 'Colony Feasibility', 'url' => '/admin/colony-feasibility', 'icon' => 'fas fa-chart-area', 'section' => 'properties', 'order_index' => 2, 'permission_key' => 'property.view'],
        ['name' => 'All Properties', 'url' => '/admin/properties', 'icon' => 'fas fa-building', 'section' => 'properties', 'order_index' => 3, 'permission_key' => 'property.view'],
        ['name' => 'Plots Inventory', 'url' => '/admin/plots', 'icon' => 'fas fa-th', 'section' => 'properties', 'order_index' => 4, 'permission_key' => 'plots.view'],
        ['name' => 'Plot Categories', 'url' => '/admin/plots/categories', 'icon' => 'fas fa-tags', 'section' => 'properties', 'order_index' => 5, 'permission_key' => 'plots.view'],
        ['name' => 'Land Acquisitions', 'url' => '/admin/land-inventory/acquisitions', 'icon' => 'fas fa-map-marked-alt', 'section' => 'properties', 'order_index' => 6, 'permission_key' => 'land.acquisitions.view'],
        ['name' => 'Land Leads', 'url' => '/admin/land-inventory/leads', 'icon' => 'fas fa-map-pin', 'section' => 'properties', 'order_index' => 7, 'permission_key' => 'land.leads.view'],
        ['name' => 'Land Brokers', 'url' => '/admin/land-inventory/brokers', 'icon' => 'fas fa-user-friends', 'section' => 'properties', 'order_index' => 8, 'permission_key' => 'land.brokers.view'],
        ['name' => 'Land Records', 'url' => '/admin/land/records', 'icon' => 'fas fa-folder', 'section' => 'properties', 'order_index' => 9, 'permission_key' => 'land.records.view'],
        ['name' => 'Sites Management', 'url' => '/admin/sites', 'icon' => 'fas fa-map-marker-alt', 'section' => 'properties', 'order_index' => 10, 'permission_key' => 'sites.view'],
        ['name' => 'Colony Management', 'url' => '/admin/colonies', 'icon' => 'fas fa-globe', 'section' => 'properties', 'order_index' => 11, 'permission_key' => 'colonies.view'],
        ['name' => 'Resell Properties', 'url' => '/admin/resell-properties', 'icon' => 'fas fa-exchange-alt', 'section' => 'properties', 'order_index' => 12, 'permission_key' => 'resell.view'],
        ['name' => 'User Properties', 'url' => '/admin/user-properties', 'icon' => 'fas fa-user-check', 'section' => 'properties', 'order_index' => 13, 'permission_key' => 'user.properties.view'],
        ['name' => 'Project Progress', 'url' => '/admin/projects/progress', 'icon' => 'fas fa-tasks', 'section' => 'properties', 'order_index' => 14, 'permission_key' => 'projects.view'],
        ['name' => 'NOC & Registry', 'url' => '/admin/noc-registry', 'icon' => 'fas fa-file-signature', 'section' => 'properties', 'order_index' => 15, 'permission_key' => 'noc.registry.view'],
        ['name' => 'Bulk Property Import', 'url' => '/admin/bulk/property-import', 'icon' => 'fas fa-upload', 'section' => 'properties', 'order_index' => 16, 'permission_key' => 'property.import'],
        ['name' => 'Property Alerts', 'url' => '/admin/property-alerts', 'icon' => 'fas fa-bell', 'section' => 'properties', 'order_index' => 17, 'permission_key' => 'property.alerts.view'],
        ['name' => 'Projects List', 'url' => '/admin/projects', 'icon' => 'fas fa-folder-open', 'section' => 'properties', 'order_index' => 18, 'permission_key' => 'projects.view'],

        // --- SECTION: bookings ---
        ['name' => 'Bookings List', 'url' => '/admin/bookings', 'icon' => 'fas fa-calendar-check', 'section' => 'bookings', 'order_index' => 1, 'permission_key' => 'bookings.view'],
        ['name' => 'Agreements', 'url' => '/admin/agreements', 'icon' => 'fas fa-file-contract', 'section' => 'bookings', 'order_index' => 2, 'permission_key' => 'agreements.view'],
        ['name' => 'Registry Management', 'url' => '/admin/registry', 'icon' => 'fas fa-signature', 'section' => 'bookings', 'order_index' => 3, 'permission_key' => 'registry.view'],
        ['name' => 'Possession Handover', 'url' => '/admin/possession', 'icon' => 'fas fa-key', 'section' => 'bookings', 'order_index' => 4, 'permission_key' => 'possession.view'],

        // --- SECTION: mlm ---
        ['name' => 'MLM Dashboard', 'url' => '/admin/mlm', 'icon' => 'fas fa-chart-pie', 'section' => 'mlm', 'order_index' => 1, 'permission_key' => 'mlm.view'],
        ['name' => 'Genealogy Tree', 'url' => '/admin/network/tree', 'icon' => 'fas fa-sitemap', 'section' => 'mlm', 'order_index' => 2, 'permission_key' => 'mlm.tree.view'],
        ['name' => 'All Associates', 'url' => '/admin/mlm/associates', 'icon' => 'fas fa-user-tie', 'section' => 'mlm', 'order_index' => 3, 'permission_key' => 'mlm.view'],
        ['name' => 'Commissions Ledger', 'url' => '/admin/commission', 'icon' => 'fas fa-coins', 'section' => 'mlm', 'order_index' => 4, 'permission_key' => 'commission.view'],
        ['name' => 'Payouts Manager', 'url' => '/admin/payouts', 'icon' => 'fas fa-money-bill-wave', 'section' => 'mlm', 'order_index' => 5, 'permission_key' => 'payouts.view'],
        ['name' => 'Clawbacks Log', 'url' => '/admin/mlm/clawbacks', 'icon' => 'fas fa-undo', 'section' => 'mlm', 'order_index' => 6, 'permission_key' => 'commission.view'],
        ['name' => 'Rank Promotion', 'url' => '/admin/mlm/associate-ranks', 'icon' => 'fas fa-arrow-up', 'section' => 'mlm', 'order_index' => 7, 'permission_key' => 'mlm.view'],
        ['name' => 'Rank Benefits', 'url' => '/admin/mlm/rank-benefits', 'icon' => 'fas fa-trophy', 'section' => 'mlm', 'order_index' => 8, 'permission_key' => 'mlm.view'],
        ['name' => 'Withdrawals Request', 'url' => '/admin/mlm/withdrawals', 'icon' => 'fas fa-wallet', 'section' => 'mlm', 'order_index' => 9, 'permission_key' => 'withdrawals.view'],
        ['name' => 'Reward History', 'url' => '/admin/mlm/rewards', 'icon' => 'fas fa-gift', 'section' => 'mlm', 'order_index' => 10, 'permission_key' => 'rewards.view'],
        ['name' => 'Commission Plans', 'url' => '/admin/commission-plans', 'icon' => 'fas fa-sliders-h', 'section' => 'mlm', 'order_index' => 11, 'permission_key' => 'commission.plans.view'],
        ['name' => 'Commission Rules', 'url' => '/admin/mlm-settings/rules', 'icon' => 'fas fa-gavel', 'section' => 'mlm', 'order_index' => 12, 'permission_key' => 'commission.rules.view'],
        ['name' => 'Associate Extensions', 'url' => '/admin/associate-extensions', 'icon' => 'fas fa-plus-circle', 'section' => 'mlm', 'order_index' => 13, 'permission_key' => 'mlm.view'],
        ['name' => 'Rank Management', 'url' => '/admin/mlm-settings/evaluate', 'icon' => 'fas fa-users-cog', 'section' => 'mlm', 'order_index' => 14, 'permission_key' => 'mlm.view'],

        // --- SECTION: finance ---
        ['name' => 'Payments Ledger', 'url' => '/admin/payments', 'icon' => 'fas fa-credit-card', 'section' => 'finance', 'order_index' => 1, 'permission_key' => 'financial.view'],
        ['name' => 'Invoices Billing', 'url' => '/admin/invoices', 'icon' => 'fas fa-file-invoice', 'section' => 'finance', 'order_index' => 2, 'permission_key' => 'invoice.view'],
        ['name' => 'Expenses Tracking', 'url' => '/admin/expense', 'icon' => 'fas fa-receipt', 'section' => 'finance', 'order_index' => 3, 'permission_key' => 'expense.view'],
        ['name' => 'Cash Book', 'url' => '/admin/finance/cash-book', 'icon' => 'fas fa-book', 'section' => 'finance', 'order_index' => 4, 'permission_key' => 'financial.view'],
        ['name' => 'Bank Reconciliation', 'url' => '/admin/finance/reconciliation', 'icon' => 'fas fa-exchange-alt', 'section' => 'finance', 'order_index' => 5, 'permission_key' => 'financial.view'],
        ['name' => 'TDS Register', 'url' => '/admin/finance/tds', 'icon' => 'fas fa-percent', 'section' => 'finance', 'order_index' => 6, 'permission_key' => 'financial.view'],
        ['name' => 'GST Invoices', 'url' => '/admin/gst', 'icon' => 'fas fa-file-invoice-dollar', 'section' => 'finance', 'order_index' => 7, 'permission_key' => 'financial.view'],
        ['name' => 'Vendor Payments', 'url' => '/admin/finance/vendors', 'icon' => 'fas fa-truck-loading', 'section' => 'finance', 'order_index' => 8, 'permission_key' => 'financial.view'],
        ['name' => 'EMI Penalties', 'url' => '/admin/finance/penalties', 'icon' => 'fas fa-exclamation-triangle', 'section' => 'finance', 'order_index' => 9, 'permission_key' => 'emi.manage'],
        ['name' => 'EMI Auto-Pay', 'url' => '/admin/finance/emi-auto-pay', 'icon' => 'fas fa-sync', 'section' => 'finance', 'order_index' => 10, 'permission_key' => 'emi.manage'],
        ['name' => 'E-Filing Dashboard', 'url' => '/admin/efiling', 'icon' => 'fas fa-file-upload', 'section' => 'finance', 'order_index' => 12, 'permission_key' => 'efiling.view'],
        ['name' => 'TDS Filing', 'url' => '/admin/efiling/tds', 'icon' => 'fas fa-percentage', 'section' => 'finance', 'order_index' => 13, 'permission_key' => 'efiling.view'],
        ['name' => 'GST Filing', 'url' => '/admin/efiling/gst', 'icon' => 'fas fa-receipt', 'section' => 'finance', 'order_index' => 14, 'permission_key' => 'efiling.view'],
        ['name' => 'Filing Calendar', 'url' => '/admin/efiling/calendar', 'icon' => 'fas fa-calendar-alt', 'section' => 'finance', 'order_index' => 15, 'permission_key' => 'efiling.view'],
        ['name' => 'Plot Costs', 'url' => '/admin/plot-costs', 'icon' => 'fas fa-calculator', 'section' => 'finance', 'order_index' => 16, 'permission_key' => 'plot.costs.view'],
        ['name' => 'Banking Transactions', 'url' => '/admin/banking', 'icon' => 'fas fa-university', 'section' => 'finance', 'order_index' => 17, 'permission_key' => 'financial.view'],
        ['name' => 'Bank Import', 'url' => '/admin/bank-import', 'icon' => 'fas fa-file-import', 'section' => 'finance', 'order_index' => 18, 'permission_key' => 'financial.view'],
        ['name' => 'Cash Collections', 'url' => '/admin/finance/collections', 'icon' => 'fas fa-piggy-bank', 'section' => 'finance', 'order_index' => 19, 'permission_key' => 'financial.view'],

        // --- SECTION: hrm ---
        ['name' => 'Employees Manager', 'url' => '/admin/employees', 'icon' => 'fas fa-users', 'section' => 'hrm', 'order_index' => 1, 'permission_key' => 'hrm.view'],
        ['name' => 'Payroll Management', 'url' => '/admin/payroll', 'icon' => 'fas fa-calculator', 'section' => 'hrm', 'order_index' => 2, 'permission_key' => 'payroll.view'],
        ['name' => 'Attendance Register', 'url' => '/admin/backoffice/attendance', 'icon' => 'fas fa-user-clock', 'section' => 'hrm', 'order_index' => 3, 'permission_key' => 'hrm.view'],
        ['name' => 'Telecaller Overrides', 'url' => '/admin/telecaller', 'icon' => 'fas fa-phone-volume', 'section' => 'hrm', 'order_index' => 4, 'permission_key' => 'telecallers.view'],
        ['name' => 'Training Courses', 'url' => '/admin/training/courses', 'icon' => 'fas fa-graduation-cap', 'section' => 'hrm', 'order_index' => 5, 'permission_key' => 'training.view'],
        ['name' => 'Course Enrollments', 'url' => '/admin/training/enrollments', 'icon' => 'fas fa-user-graduate', 'section' => 'hrm', 'order_index' => 6, 'permission_key' => 'training.view'],
        ['name' => 'Certificates Issued', 'url' => '/admin/training/certificates', 'icon' => 'fas fa-certificate', 'section' => 'hrm', 'order_index' => 7, 'permission_key' => 'training.view'],
        ['name' => 'Training Modules', 'url' => '/admin/training/modules', 'icon' => 'fas fa-book-open', 'section' => 'hrm', 'order_index' => 8, 'permission_key' => 'training.view'],

        // --- SECTION: legal ---
        ['name' => 'Disputes Board', 'url' => '/admin/legal/disputes', 'icon' => 'fas fa-balance-scale', 'section' => 'legal', 'order_index' => 1, 'permission_key' => 'legal.view'],
        ['name' => 'Legal Deadlines', 'url' => '/admin/legal/deadlines', 'icon' => 'fas fa-clock', 'section' => 'legal', 'order_index' => 2, 'permission_key' => 'legal.view'],
        ['name' => 'RERA Compliance', 'url' => '/admin/sales/rera', 'icon' => 'fas fa-gavel', 'section' => 'legal', 'order_index' => 3, 'permission_key' => 'legal.view'],

        // --- SECTION: locations ---
        ['name' => 'States Management', 'url' => '/admin/locations/states', 'icon' => 'fas fa-map', 'section' => 'locations', 'order_index' => 1, 'permission_key' => 'locations.view'],
        ['name' => 'Districts Board', 'url' => '/admin/locations/districts', 'icon' => 'fas fa-map-signs', 'section' => 'locations', 'order_index' => 2, 'permission_key' => 'locations.view'],
        ['name' => 'Colonies Board', 'url' => '/admin/locations/colonies', 'icon' => 'fas fa-map-marker-alt', 'section' => 'locations', 'order_index' => 3, 'permission_key' => 'locations.view'],

        // --- SECTION: marketing ---
        ['name' => 'Marketing Strategies', 'url' => '/admin/marketing/strategies', 'icon' => 'fas fa-chess', 'section' => 'marketing', 'order_index' => 1, 'permission_key' => 'marketing.view'],
        ['name' => 'Marketplace Listings', 'url' => '/admin/marketing/marketplace', 'icon' => 'fas fa-store', 'section' => 'marketing', 'order_index' => 2, 'permission_key' => 'marketing.view'],
        ['name' => 'Campaigns Hub', 'url' => '/admin/campaigns', 'icon' => 'fas fa-bullhorn', 'section' => 'marketing', 'order_index' => 3, 'permission_key' => 'campaigns.view'],
        ['name' => 'Visits Log', 'url' => '/admin/visits', 'icon' => 'fas fa-eye', 'section' => 'marketing', 'order_index' => 4, 'permission_key' => 'visits.view'],
        ['name' => 'Voice Scheduler', 'url' => '/admin/voice-scheduler', 'icon' => 'fas fa-microphone', 'section' => 'marketing', 'order_index' => 5, 'permission_key' => 'marketing.view'],
        ['name' => 'Marketing Campaigns', 'url' => '/admin/marketing-campaigns', 'icon' => 'fas fa-mail-bulk', 'section' => 'marketing', 'order_index' => 6, 'permission_key' => 'marketing.view'],
        ['name' => 'Property Comparison', 'url' => '/property-comparison', 'icon' => 'fas fa-exchange-alt', 'section' => 'marketing', 'order_index' => 7, 'permission_key' => 'marketing.view'],
        ['name' => 'Drip Campaigns', 'url' => '/admin/drip-campaigns', 'icon' => 'fas fa-tint', 'section' => 'marketing', 'order_index' => 8, 'permission_key' => 'marketing.view'],

        // --- SECTION: cms ---
        ['name' => 'Pages Content', 'url' => '/admin/pages', 'icon' => 'fas fa-file', 'section' => 'cms', 'order_index' => 1, 'permission_key' => 'pages.manage'],
        ['name' => 'Blogs Manager', 'url' => '/admin/blog', 'icon' => 'fas fa-newspaper', 'section' => 'cms', 'order_index' => 2, 'permission_key' => 'blog.manage'],
        ['name' => 'Gallery Images', 'url' => '/admin/gallery', 'icon' => 'fas fa-images', 'section' => 'cms', 'order_index' => 3, 'permission_key' => 'media.manage'],
        ['name' => 'Testimonials Manager', 'url' => '/admin/testimonials', 'icon' => 'fas fa-quote-left', 'section' => 'cms', 'order_index' => 4, 'permission_key' => 'testimonials.view'],
        ['name' => 'FAQs Manager', 'url' => '/admin/faqs', 'icon' => 'fas fa-question-circle', 'section' => 'cms', 'order_index' => 5, 'permission_key' => 'faq.manage'],
        ['name' => 'Legal Pages Content', 'url' => '/admin/legal-pages', 'icon' => 'fas fa-file-alt', 'section' => 'cms', 'order_index' => 6, 'permission_key' => 'pages.manage'],
        ['name' => 'News Feed Manager', 'url' => '/admin/news', 'icon' => 'fas fa-newspaper', 'section' => 'cms', 'order_index' => 7, 'permission_key' => 'news.view'],
        ['name' => 'Site Settings Manager', 'url' => '/admin/site-settings', 'icon' => 'fas fa-window-restore', 'section' => 'cms', 'order_index' => 8, 'permission_key' => 'system.settings'],
        ['name' => 'Site Content Editor', 'url' => '/admin/site-content', 'icon' => 'fas fa-edit', 'section' => 'cms', 'order_index' => 9, 'permission_key' => 'system.settings'],

        // --- SECTION: services ---
        ['name' => 'Service Enquiries', 'url' => '/admin/services', 'icon' => 'fas fa-concierge-bell', 'section' => 'services', 'order_index' => 1, 'permission_key' => 'services.view'],
        ['name' => 'Service Configuration', 'url' => '/admin/service-configs', 'icon' => 'fas fa-cogs', 'section' => 'services', 'order_index' => 2, 'permission_key' => 'system.settings'],

        // --- SECTION: users ---
        ['name' => 'All Users List', 'url' => '/admin/users', 'icon' => 'fas fa-users', 'section' => 'users', 'order_index' => 1, 'permission_key' => 'users.view.all'],
        ['name' => 'Role Settings', 'url' => '/admin/roles', 'icon' => 'fas fa-user-tag', 'section' => 'users', 'order_index' => 2, 'permission_key' => 'roles.view'],
        ['name' => 'Progressive Registrations', 'url' => '/admin/features/registrations', 'icon' => 'fas fa-user-plus', 'section' => 'users', 'order_index' => 4, 'permission_key' => 'users.view.all'],

        // --- SECTION: settings ---
        ['name' => 'General Settings', 'url' => '/admin/settings', 'icon' => 'fas fa-sliders-h', 'section' => 'settings', 'order_index' => 1, 'permission_key' => 'system.settings'],
        ['name' => 'God Mode Console', 'url' => '/admin/godmode', 'icon' => 'fas fa-user-secret', 'section' => 'settings', 'order_index' => 2, 'permission_key' => 'system.settings'],
        ['name' => 'Activity History Log', 'url' => '/admin/activity-log', 'icon' => 'fas fa-history', 'section' => 'settings', 'order_index' => 3, 'permission_key' => 'system.settings'],
        ['name' => 'Email SMTP Settings', 'url' => '/admin/settings/email', 'icon' => 'fas fa-envelope', 'section' => 'settings', 'order_index' => 4, 'permission_key' => 'system.settings'],
        ['name' => 'SMS Gateway Settings', 'url' => '/admin/settings/sms', 'icon' => 'fas fa-sms', 'section' => 'settings', 'order_index' => 5, 'permission_key' => 'system.settings'],
        ['name' => 'Payment Gateway Settings', 'url' => '/admin/settings/payment', 'icon' => 'fas fa-credit-card', 'section' => 'settings', 'order_index' => 6, 'permission_key' => 'system.settings'],
        ['name' => 'Bulk Import & Export', 'url' => '/admin/bulk-operations', 'icon' => 'fas fa-file-export', 'section' => 'settings', 'order_index' => 7, 'permission_key' => 'system.settings'],
        ['name' => 'Webhooks Manager', 'url' => '/admin/webhooks', 'icon' => 'fas fa-link', 'section' => 'settings', 'order_index' => 8, 'permission_key' => 'system.settings'],
        ['name' => 'Company Profile Settings', 'url' => '/admin/company/settings', 'icon' => 'fas fa-building', 'section' => 'settings', 'order_index' => 9, 'permission_key' => 'system.settings'],
        ['name' => 'API Integrations Engine', 'url' => '/admin/api/integrations', 'icon' => 'fas fa-network-wired', 'section' => 'settings', 'order_index' => 10, 'permission_key' => 'system.settings'],
        ['name' => 'API Developer Sandbox', 'url' => '/admin/api/developers', 'icon' => 'fas fa-code', 'section' => 'settings', 'order_index' => 11, 'permission_key' => 'system.settings'],
        ['name' => 'API Developer Docs', 'url' => '/admin/api-docs', 'icon' => 'fas fa-book', 'section' => 'settings', 'order_index' => 12, 'permission_key' => 'system.settings'],
        ['name' => 'AI Neural Configurations', 'url' => '/admin/ai_settings', 'icon' => 'fas fa-robot', 'section' => 'settings', 'order_index' => 13, 'permission_key' => 'ai.settings'],
        ['name' => 'Localization & Language', 'url' => '/admin/localization', 'icon' => 'fas fa-globe-asia', 'section' => 'settings', 'order_index' => 14, 'permission_key' => 'system.settings'],
        ['name' => 'Communication Queue', 'url' => '/admin/communication/queue', 'icon' => 'fas fa-paper-plane', 'section' => 'settings', 'order_index' => 15, 'permission_key' => 'system.settings'],
        ['name' => 'WhatsApp Configuration', 'url' => '/admin/whatsapp/settings', 'icon' => 'fab fa-whatsapp', 'section' => 'settings', 'order_index' => 16, 'permission_key' => 'system.settings'],
        ['name' => 'Bank Gateway Manager', 'url' => '/admin/gateways', 'icon' => 'fas fa-university', 'section' => 'settings', 'order_index' => 17, 'permission_key' => 'system.settings'],
        ['name' => 'Company Credentials', 'url' => '/admin/company-credentials', 'icon' => 'fas fa-id-card', 'section' => 'settings', 'order_index' => 18, 'permission_key' => 'system.settings'],
        ['name' => 'Production Checklist', 'url' => '/admin/production-checklist', 'icon' => 'fas fa-clipboard-check', 'section' => 'settings', 'order_index' => 19, 'permission_key' => 'system.settings'],
        ['name' => 'Menu Permissions RBAC', 'url' => '/admin/menu-permissions', 'icon' => 'fas fa-user-lock', 'section' => 'settings', 'order_index' => 20, 'permission_key' => 'system.settings'],

        // --- SECTION: reports ---
        ['name' => 'Reports Engine', 'url' => '/admin/reports', 'icon' => 'fas fa-file-alt', 'section' => 'reports', 'order_index' => 1, 'permission_key' => 'reports.view'],
        ['name' => 'System Analytics', 'url' => '/admin/analytics', 'icon' => 'fas fa-chart-line', 'section' => 'reports', 'order_index' => 2, 'permission_key' => 'analytics.view'],
        ['name' => 'PDF Generator Hub', 'url' => '/admin/pdfs', 'icon' => 'fas fa-file-pdf', 'section' => 'reports', 'order_index' => 3, 'permission_key' => 'reports.view'],
        ['name' => 'Saved Searches Query', 'url' => '/admin/saved-searches', 'icon' => 'fas fa-search-plus', 'section' => 'reports', 'order_index' => 4, 'permission_key' => 'reports.view'],

        // --- SECTION: system ---
        ['name' => 'Security Center Guard', 'url' => '/admin/features/security', 'icon' => 'fas fa-shield-alt', 'section' => 'system', 'order_index' => 1, 'permission_key' => 'system.settings'],
        ['name' => 'Audit logs Tracker', 'url' => '/admin/audit-log', 'icon' => 'fas fa-clipboard-list', 'section' => 'system', 'order_index' => 2, 'permission_key' => 'system.settings'],
        ['name' => 'System Health Monitor', 'url' => '/admin/system-health', 'icon' => 'fas fa-heartbeat', 'section' => 'system', 'order_index' => 3, 'permission_key' => 'system.settings'],
        ['name' => 'Database Backup Utility', 'url' => '/admin/backup', 'icon' => 'fas fa-database', 'section' => 'system', 'order_index' => 4, 'permission_key' => 'system.settings'],
        ['name' => 'Clear Cache Tool', 'url' => '/admin/cache', 'icon' => 'fas fa-broom', 'section' => 'system', 'order_index' => 5, 'permission_key' => 'system.settings'],
    ];

    // 4. Insert clean items
    $stmt = $pdo->prepare("INSERT INTO admin_menu_items (name, icon, url, section, order_index, permission_key, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $inserted = 0;
    foreach ($menuItems as $item) {
        $stmt->execute([
            $item['name'],
            $item['icon'],
            $item['url'],
            $item['section'],
            $item['order_index'],
            $item['permission_key']
        ]);
        $inserted++;
    }
    echo "âœ“ Successfully seeded {$inserted} unique menu items!\n";

    // 5. Grant permissions to roles
    $menuIds = $pdo->query("SELECT id FROM admin_menu_items")->fetchAll(PDO::FETCH_COLUMN);
    $roleStmt = $pdo->prepare("INSERT INTO admin_role_menu_permissions (role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (?, ?, ?, ?, ?, ?)");
    
    $grantedCount = 0;
    $roles = ['super_admin', 'admin', 'manager', 'employee', 'associate', 'agent', 'customer'];

    foreach ($roles as $role) {
        foreach ($menuIds as $menuId) {
            if ($role === 'super_admin' || $role === 'admin') {
                // Admin and super_admin get full rights
                $roleStmt->execute([$role, $menuId, 1, 1, 1, 1]);
            } else {
                // Others get view access by default (so they don't see a blank sidebar)
                $roleStmt->execute([$role, $menuId, 1, 0, 0, 0]);
            }
            $grantedCount++;
        }
    }
    echo "âœ“ Successfully granted {$grantedCount} role permissions across " . count($roles) . " roles!\n";

    // 6. Invalidate menu cache
    \App\Services\CacheService::invalidateAdminMenu();
    echo "âœ“ Cleared Admin Sidebar Cache\n\n";

    echo "=== SIDEBAR FIXED SUCCESSFULLY! ===\n";

} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}?>