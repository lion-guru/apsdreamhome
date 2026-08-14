<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Database;

$db = Database::getInstance();

// Get existing URLs to avoid duplicates
$existing = $db->fetchAll("SELECT url FROM admin_menu_items WHERE is_active = 1");
$existingUrls = array_column($existing, 'url');

echo "Existing URLs: " . count($existingUrls) . PHP_EOL;

// All menu items from the hardcoded fallback + new ones
$menuItems = [
    // Dashboards
    ['Main Dashboard', 'fas fa-tachometer-alt', '/admin/dashboard', 'dashboards', 1],
    ['Analytics', 'fas fa-chart-line', '/admin/analytics', 'dashboards', 2],
    ['Reports', 'fas fa-file-alt', '/admin/reports', 'dashboards', 3],
    ['ERP Overview', 'fas fa-chart-pie', '/admin/erp', 'dashboards', 4],
    ['CEO Dashboard', 'fas fa-user-tie', '/admin/dashboard/ceo', 'dashboards', 5],
    ['CFO Dashboard', 'fas fa-calculator', '/admin/dashboard/cfo', 'dashboards', 6],
    ['Finance Dashboard', 'fas fa-money-bill-wave', '/admin/dashboard/finance', 'dashboards', 7],
    ['Sales Dashboard', 'fas fa-chart-bar', '/admin/dashboard/sales', 'dashboards', 8],

    // CRM & Sales
    ['Leads', 'fas fa-bullseye', '/admin/leads', 'crm', 1],
    ['Lead Kanban', 'fas fa-columns', '/admin/lead-kanban', 'crm', 2],
    ['Lead Scoring', 'fas fa-star', '/admin/leads/scoring', 'crm', 3],
    ['Deals', 'fas fa-handshake', '/admin/deals', 'crm', 4],
    ['Site Visits', 'fas fa-map-marker-alt', '/admin/visits', 'crm', 5],
    ['Enquiries', 'fas fa-question-circle', '/admin/inquiries', 'crm', 6],
    ['Campaigns', 'fas fa-bullhorn', '/admin/campaigns', 'crm', 7],
    ['Support Tickets', 'fas fa-ticket-alt', '/admin/support-tickets', 'crm', 8],
    ['NPS Surveys', 'fas fa-smile', '/admin/nps', 'crm', 9],
    ['Customer Referrals', 'fas fa-share-alt', '/admin/referrals', 'crm', 10],
    ['KYC Verification', 'fas fa-id-card', '/admin/kyc', 'crm', 11],
    ['Messages', 'fas fa-comments', '/admin/messages', 'crm', 12],

    // Properties
    ['All Properties', 'fas fa-list', '/admin/properties', 'properties', 1],
    ['Plots Inventory', 'fas fa-map', '/admin/plots', 'properties', 2],
    ['Plot Categories', 'fas fa-tags', '/admin/plots/categories', 'properties', 3],
    ['Land Acquisitions', 'fas fa-landmark', '/admin/land-inventory/acquisitions', 'properties', 4],
    ['Land Leads', 'fas fa-map-marked-alt', '/admin/land-inventory/leads', 'properties', 5],
    ['Land Brokers', 'fas fa-user-tie', '/admin/land-inventory/brokers', 'properties', 6],
    ['Land Records', 'fas fa-file-alt', '/admin/land/records', 'properties', 7],
    ['Sites Management', 'fas fa-building', '/admin/sites', 'properties', 8],
    ['Colony Management', 'fas fa-city', '/admin/colonies', 'properties', 9],
    ['Resell Properties', 'fas fa-exchange-alt', '/admin/resell-properties', 'properties', 10],
    ['User Properties', 'fas fa-user-plus', '/admin/user-properties', 'properties', 11],
    ['Project Progress', 'fas fa-tasks', '/admin/projects/progress', 'properties', 12],
    ['NOC & Registry', 'fas fa-file-signature', '/admin/noc-registry', 'properties', 13],
    ['Bulk Property Import', 'fas fa-file-import', '/admin/bulk/property-import', 'properties', 14],
    ['Property Alerts', 'fas fa-bell', '/admin/property-alerts', 'properties', 15],
    ['Projects List', 'fas fa-project-diagram', '/admin/projects', 'properties', 16],
    ['Services Directory', 'fas fa-concierge-bell', '/admin/directory', 'properties', 17],
    ['Directory Categories', 'fas fa-list-alt', '/admin/directory/categories', 'properties', 18],
    ['Manage Listings', 'fas fa-list', '/admin/directory/listings', 'properties', 19],
    ['Review Moderation', 'fas fa-star', '/admin/directory/reviews', 'properties', 20],
    ['Jobs', 'fas fa-briefcase', '/admin/directory/jobs', 'properties', 21],
    ['Material Prices', 'fas fa-cubes', '/admin/directory/materials', 'properties', 22],
    ['Colony Pipeline', 'fas fa-sitemap', '/admin/colony-pipeline', 'properties', 23],
    ['Colony Feasibility', 'fas fa-calculator', '/admin/colony-feasibility', 'properties', 24],

    // Sales
    ['Bookings', 'fas fa-calendar-check', '/admin/bookings', 'sales', 1],
    ['Booking Approvals', 'fas fa-check-circle', '/admin/sales/approvals', 'sales', 2],
    ['Agreements', 'fas fa-file-contract', '/admin/agreements', 'sales', 3],
    ['Registry', 'fas fa-file-signature', '/admin/registry', 'sales', 4],
    ['Possession', 'fas fa-key', '/admin/possession', 'sales', 5],
    ['RERA Compliance', 'fas fa-gavel', '/admin/sales/rera', 'sales', 6],

    // Finance
    ['Payments Ledger', 'fas fa-credit-card', '/admin/payments', 'finance', 1],
    ['Invoices', 'fas fa-file-invoice', '/admin/invoices', 'finance', 2],
    ['Expenses', 'fas fa-receipt', '/admin/expense', 'finance', 3],
    ['Cash Book', 'fas fa-book', '/admin/finance/cash-book', 'finance', 4],
    ['Bank Reconciliation', 'fas fa-university', '/admin/finance/reconciliation', 'finance', 5],
    ['TDS Register', 'fas fa-percent', '/admin/finance/tds', 'finance', 6],
    ['GST Invoices', 'fas fa-file-invoice-dollar', '/admin/gst', 'finance', 7],
    ['Vendor Payments', 'fas fa-truck', '/admin/finance/vendors', 'finance', 8],
    ['EMI Penalties', 'fas fa-exclamation-triangle', '/admin/finance/penalties', 'finance', 9],
    ['EMI Auto-Pay', 'fas fa-magic', '/admin/finance/emi-auto-pay', 'finance', 10],
    ['E-Filing', 'fas fa-file-export', '/admin/efiling', 'finance', 11],
    ['TDS Filing', 'fas fa-file-alt', '/admin/efiling/tds', 'finance', 12],
    ['GST Filing', 'fas fa-file-alt', '/admin/efiling/gst', 'finance', 13],
    ['Filing Calendar', 'fas fa-calendar', '/admin/efiling/calendar', 'finance', 14],
    ['Cash Flow Forecast', 'fas fa-chart-area', '/admin/finance/cash-flow', 'finance', 15],
    ['Plot Costs', 'fas fa-calculator', '/admin/plot-costs', 'finance', 16],
    ['Banking', 'fas fa-university', '/admin/banking', 'finance', 17],
    ['Bank Import', 'fas fa-file-import', '/admin/bank-import', 'finance', 18],
    ['Cash Collections', 'fas fa-hand-holding-usd', '/admin/cash-collections', 'finance', 19],
    ['Company Loans', 'fas fa-hand-holding-usd', '/admin/company-loans', 'finance', 20],

    // Commission
    ['Commissions', 'fas fa-coins', '/admin/commission', 'commission', 1],
    ['Recalculations', 'fas fa-sync-alt', '/admin/commission/recalculations', 'commission', 2],
    ['Payout Batches', 'fas fa-box', '/admin/payout-batches', 'commission', 3],

    // MLM
    ['MLM Dashboard', 'fas fa-project-diagram', '/admin/mlm', 'mlm', 1],
    ['Genealogy Tree', 'fas fa-sitemap', '/admin/network/tree', 'mlm', 2],
    ['All Associates', 'fas fa-users', '/admin/mlm/associates', 'mlm', 3],
    ['Commissions Ledger', 'fas fa-coins', '/admin/commission', 'mlm', 4],
    ['Payouts Manager', 'fas fa-money-bill-wave', '/admin/payouts', 'mlm', 5],
    ['Clawbacks Log', 'fas fa-history', '/admin/mlm/clawbacks', 'mlm', 6],
    ['Rank Promotion', 'fas fa-arrow-up', '/admin/mlm/associate-ranks', 'mlm', 7],
    ['Rank Benefits', 'fas fa-medal', '/admin/mlm/rank-benefits', 'mlm', 8],
    ['Withdrawals', 'fas fa-money-bill', '/admin/mlm/withdrawals', 'mlm', 9],
    ['Reward History', 'fas fa-trophy', '/admin/mlm/rewards', 'mlm', 10],
    ['Commission Plans', 'fas fa-cogs', '/admin/commission-plans', 'mlm', 11],
    ['Commission Rules', 'fas fa-sliders-h', '/admin/mlm-settings/rules', 'mlm', 12],
    ['Associate Extensions', 'fas fa-user-plus', '/admin/associate-extensions', 'mlm', 13],
    ['Rank Evaluation', 'fas fa-tasks', '/admin/mlm-settings/evaluate', 'mlm', 14],

    // HRM
    ['Employees Manager', 'fas fa-users-cog', '/admin/employees', 'hrm', 1],
    ['Departments', 'fas fa-building', '/admin/departments', 'hrm', 2],
    ['Payroll', 'fas fa-money-check', '/admin/payroll', 'hrm', 3],
    ['Designations', 'fas fa-user-tag', '/admin/designations', 'hrm', 4],
    ['Attendance', 'fas fa-clock', '/admin/backoffice/attendance', 'hrm', 5],
    ['Telecaller Overrides', 'fas fa-phone-volume', '/admin/telecaller', 'hrm', 6],
    ['Training Courses', 'fas fa-graduation-cap', '/admin/training/courses', 'hrm', 7],
    ['Career Management', 'fas fa-briefcase', '/admin/careers', 'hrm', 8],
    ['Course Enrollments', 'fas fa-user-graduate', '/admin/training/enrollments', 'hrm', 9],
    ['Job Applications', 'fas fa-file-alt', '/admin/careers/manage', 'hrm', 10],
    ['Certificates', 'fas fa-certificate', '/admin/training/certificates', 'hrm', 11],
    ['Training Modules', 'fas fa-book-open', '/admin/training/modules', 'hrm', 12],

    // Legal
    ['Disputes Board', 'fas fa-gavel', '/admin/legal/disputes', 'legal', 1],
    ['Legal Dashboard', 'fas fa-balance-scale', '/admin/legal/dashboard', 'legal', 2],
    ['Legal Deadlines', 'fas fa-calendar-times', '/admin/legal/deadlines', 'legal', 3],
    ['Document Templates', 'fas fa-file-contract', '/admin/legal/templates', 'legal', 4],
    ['RERA Compliance', 'fas fa-gavel', '/admin/sales/rera', 'legal', 5],
    ['Clause Library', 'fas fa-list-alt', '/admin/legal/clauses', 'legal', 6],
    ['Loan Offers', 'fas fa-hand-holding-usd', '/admin/company-loans/offers', 'legal', 7],
    ['AI Document Composer', 'fas fa-robot', '/admin/legal/ai-composer', 'legal', 8],
    ['AI Prompt Templates', 'fas fa-brain', '/admin/legal/ai-prompts', 'legal', 9],
    ['Document Categories', 'fas fa-folder', '/admin/legal/categories', 'legal', 10],

    // Locations
    ['States', 'fas fa-map', '/admin/locations/states', 'locations', 1],
    ['Districts', 'fas fa-map-marked', '/admin/locations/districts', 'locations', 2],
    ['Colonies', 'fas fa-city', '/admin/locations/colonies', 'locations', 3],

    // Marketing
    ['Marketing Strategies', 'fas fa-chess', '/admin/marketing/strategies', 'marketing', 1],
    ['Marketplace', 'fas fa-store', '/admin/marketing/marketplace', 'marketing', 2],
    ['Campaigns Hub', 'fas fa-bullhorn', '/admin/campaigns', 'marketing', 3],
    ['Visits Log', 'fas fa-eye', '/admin/visits', 'marketing', 4],
    ['Voice Scheduler', 'fas fa-microphone', '/admin/voice-scheduler', 'marketing', 5],
    ['Marketing Campaigns', 'fas fa-mail-bulk', '/admin/marketing-campaigns', 'marketing', 6],
    ['Property Comparison', 'fas fa-exchange-alt', '/property-comparison', 'marketing', 7],
    ['Drip Campaigns', 'fas fa-tint', '/admin/drip-campaigns', 'marketing', 8],
    ['Ad Manager', 'fas fa-ad', '/admin/ads', 'marketing', 9],
    ['AdSense Settings', 'fab fa-google', '/admin/ads/settings', 'marketing', 10],
    ['Email/SMS Templates', 'fas fa-file-alt', '/admin/crm/templates', 'marketing', 11],
    ['Bulk Outreach', 'fas fa-paper-plane', '/admin/crm/bulk-send', 'marketing', 12],
    ['Lead Segments', 'fas fa-layer-group', '/admin/crm/segments', 'marketing', 13],
    ['CRM Analytics', 'fas fa-chart-line', '/admin/crm/analytics', 'marketing', 14],
    ['Lead Forms', 'fas fa-wpforms', '/admin/crm/forms', 'marketing', 15],
    ['Referral Leaderboard', 'fas fa-trophy', '/admin/referrals/leaderboard', 'marketing', 16],
    ['Share Analytics', 'fas fa-share-alt', '/admin/referrals/share-analytics', 'marketing', 17],
    ['Referral Tiers', 'fas fa-layer-group', '/admin/referrals/tiers', 'marketing', 18],
    ['Agentic CRM AI', 'fas fa-robot', '/admin/crm/agentic', 'marketing', 19],
    ['CRM Role Dashboard', 'fas fa-users-cog', '/admin/crm/role-dashboard', 'marketing', 20],
    ['Notification Dashboard', 'fas fa-bell', '/admin/notification-dashboard', 'marketing', 21],
    ['Lead Deduplication', 'fas fa-copy', '/admin/crm/dedup', 'marketing', 22],
    ['Custom Fields', 'fas fa-sliders-h', '/admin/crm/custom-fields', 'marketing', 23],
    ['Drip Campaigns (CRM)', 'fas fa-robot', '/admin/crm/drip', 'marketing', 24],
    ['Email Tracking', 'fas fa-envelope-open', '/admin/crm/email-tracking/stats', 'marketing', 25],
    ['SLA Dashboard', 'fas fa-clock', '/admin/crm/sla', 'marketing', 26],
    ['Meetings', 'fas fa-calendar-alt', '/admin/meetings', 'marketing', 27],
    ['Voice CRM', 'fas fa-microphone', '/admin/crm/voice', 'marketing', 28],

    // Bookings
    ['Bookings List', 'fas fa-list', '/admin/bookings', 'bookings', 1],
    ['Agreements', 'fas fa-file-contract', '/admin/agreements', 'bookings', 2],
    ['Registry', 'fas fa-file-signature', '/admin/registry', 'bookings', 3],
    ['Possession', 'fas fa-key', '/admin/possession', 'bookings', 4],
    ['Booking Approvals', 'fas fa-check-circle', '/admin/sales/approvals', 'bookings', 5],

    // CMS
    ['Pages', 'fas fa-file', '/admin/pages', 'cms', 1],
    ['Blogs', 'fas fa-newspaper', '/admin/blog', 'cms', 2],
    ['Gallery', 'fas fa-images', '/admin/gallery', 'cms', 3],
    ['Testimonials', 'fas fa-quote-left', '/admin/testimonials', 'cms', 4],
    ['FAQs', 'fas fa-question-circle', '/admin/faqs', 'cms', 5],
    ['Legal Pages', 'fas fa-gavel', '/admin/legal-pages', 'cms', 6],
    ['News', 'fas fa-rss', '/admin/news', 'cms', 7],
    ['Site Settings', 'fas fa-cogs', '/admin/site-settings', 'cms', 8],
    ['Site Content', 'fas fa-edit', '/admin/site-content', 'cms', 9],

    // Reports
    ['Reports Engine', 'fas fa-chart-bar', '/admin/reports', 'reports', 1],
    ['Analytics', 'fas fa-chart-line', '/admin/analytics', 'reports', 2],
    ['PDF Generator', 'fas fa-file-pdf', '/admin/pdfs', 'reports', 3],
    ['Saved Searches', 'fas fa-search', '/admin/saved-searches', 'reports', 4],

    // Operations
    ['Backoffice Dashboard', 'fas fa-tachometer-alt', '/admin/backoffice', 'operations', 1],

    // Services
    ['Service Enquiries', 'fas fa-list-alt', '/admin/services', 'services', 1],
    ['Service Configuration', 'fas fa-cogs', '/admin/service-configs', 'services', 2],

    // Settings
    ['General Settings', 'fas fa-sliders-h', '/admin/settings', 'settings', 1],
    ['God Mode', 'fas fa-skull-crossbones', '/admin/godmode', 'settings', 2],
    ['Activity Log', 'fas fa-history', '/admin/activity-log', 'settings', 3],
    ['Email SMTP', 'fas fa-envelope', '/admin/settings/email', 'settings', 4],
    ['SMS Gateway', 'fas fa-sms', '/admin/settings/sms', 'settings', 5],
    ['Payment Gateway', 'fas fa-credit-card', '/admin/settings/payment', 'settings', 6],
    ['Bulk Import/Export', 'fas fa-file-import', '/admin/bulk-operations', 'settings', 7],
    ['Webhooks', 'fas fa-plug', '/admin/webhooks', 'settings', 8],
    ['Company Profile', 'fas fa-building', '/admin/company/settings', 'settings', 9],
    ['API Integrations', 'fas fa-plug', '/admin/api/integrations', 'settings', 10],
    ['API Sandbox', 'fas fa-flask', '/admin/api/developers', 'settings', 11],
    ['API Docs', 'fas fa-book', '/admin/api-docs', 'settings', 12],
    ['AI Config', 'fas fa-robot', '/admin/ai_settings', 'settings', 13],
    ['Localization', 'fas fa-language', '/admin/localization', 'settings', 14],
    ['Comm Queue', 'fas fa-stream', '/admin/communication/queue', 'settings', 15],
    ['WhatsApp Config', 'fab fa-whatsapp', '/admin/whatsapp/settings', 'settings', 16],
    ['Bank Gateway', 'fas fa-university', '/admin/gateways', 'settings', 17],
    ['Company Credentials', 'fas fa-key', '/admin/company-credentials', 'settings', 18],
    ['Production Checklist', 'fas fa-clipboard-check', '/admin/production-checklist', 'settings', 19],
    ['Menu Permissions', 'fas fa-key', '/admin/menu-permissions', 'settings', 20],

    // System
    ['Security Center', 'fas fa-shield-alt', '/admin/features/security', 'system', 1],
    ['Audit Logs', 'fas fa-list-alt', '/admin/audit-log', 'system', 2],
    ['System Health', 'fas fa-heartbeat', '/admin/system-health', 'system', 3],
    ['DB Backup', 'fas fa-database', '/admin/backup', 'system', 4],
    ['Clear Cache', 'fas fa-broom', '/admin/cache', 'system', 5],

    // Technology
    ['Custom Features', 'fas fa-cubes', '/admin/custom-features', 'technology', 1],
    ['Neighborhood Analytics', 'fas fa-map-marked-alt', '/admin/custom-features/neighborhood', 'technology', 2],
    ['Investment Calculator', 'fas fa-calculator', '/admin/custom-features/investment-calculator', 'technology', 3],
    ['AI System Dashboard', 'fas fa-brain', '/admin/ai-system', 'technology', 4],
    ['Lead Qualifier', 'fas fa-magnet', '/admin/ai-system/qualifier', 'technology', 5],
    ['Market Intelligence', 'fas fa-chart-line', '/admin/ai-system/market-report', 'technology', 6],
    ['Security Test Suite', 'fas fa-shield-alt', '/admin/security-test', 'technology', 7],
    ['Smart Registration', 'fas fa-user-plus', '/admin/smart-registration', 'technology', 8],
    ['AI Calling Dashboard', 'fas fa-phone-alt', '/admin/ai-calling/dashboard', 'technology', 9],
    ['SIM Calling', 'fas fa-sim-card', '/admin/sim-calling', 'technology', 10],
    ['Voice Agents', 'fas fa-robot', '/admin/voice-agents', 'technology', 11],
    ['Auto Dialer', 'fas fa-clock', '/admin/ai-calling/schedule', 'technology', 12],
    ['Call Sessions', 'fas fa-list-alt', '/admin/ai-calling/sessions', 'technology', 13],
    ['Extracted Leads', 'fas fa-headset', '/admin/ai-calling/extracted-leads', 'technology', 14],
    ['Telephony Health', 'fas fa-heartbeat', '/admin/ai-calling/health', 'technology', 15],

    // Users
    ['All Users', 'fas fa-users', '/admin/users', 'users', 1],
    ['Roles', 'fas fa-user-tag', '/admin/roles', 'users', 2],
    ['Menu Permissions', 'fas fa-key', '/admin/menu-permissions', 'users', 3],
    ['Progressive Registrations', 'fas fa-user-plus', '/admin/features/registrations', 'users', 4],

    // Security
    ['Compliance Scorecard', 'fas fa-shield-alt', '/admin/compliance-scorecard', 'security', 1],

    // Employee portal (sub-menu items - these are for employee layout)
];

$inserted = 0;
$skipped = 0;

foreach ($menuItems as $item) {
    [$name, $icon, $url, $section, $order] = $item;
    
    if (in_array($url, $existingUrls)) {
        $skipped++;
        continue;
    }

    try {
        $db->query(
            "INSERT INTO admin_menu_items (name, icon, url, section, order_index, is_active, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())",
            [$name, $icon, $url, $section, $order]
        );
        $existingUrls[] = $url;
        $inserted++;
        echo "Added: $name â†’ $url ($section) PHP_EOL";
    } catch (\Throwable $e) {
        echo "ERROR adding $name: " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "Done! Inserted: $inserted, Skipped (already exist): $skipped" . PHP_EOL;?>