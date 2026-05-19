<?php
/**
 * Clean Broken Menu Items from Database
 * Removes menu items that don't have working routes/controllers
 */
require 'config/bootstrap.php';
$db = App\Core\Database\Database::getInstance();

// List of broken URLs (no controller exists)
$brokenUrls = [
    '/admin/chatbot',
    '/admin/ai-chatbot', 
    '/admin/ai-analytics',
    '/admin/ai-calling/dashboard',
    '/admin/ai-calling/schedule',
    '/admin/ai-calling/sessions',
    '/admin/ai-calling/extracted-leads',
    '/admin/ai-calling/training',
    '/admin/crm',
    '/admin/crm/customers',
    '/admin/crm/customers/create',
    '/admin/crm/groups',
    '/admin/crm/followups',
    '/admin/crm/feedback',
    '/admin/crm/support',
    '/admin/customers',
    '/admin/hrm',
    '/admin/hrm/employees/create',
    '/admin/hrm/attendance',
    '/admin/hrm/leave',
    '/admin/hrm/payroll',
    '/admin/hrm/salary-slips',
    '/admin/hrm/performance',
    '/admin/hrm/recruitment',
    '/admin/hrm/jobs',
    '/admin/hrm/applicants',
    '/admin/hrm/documents',
    '/admin/hrm/departments',
    '/admin/hrm/designations',
    '/admin/hrm/settings',
    '/admin/email-templates',
    '/admin/sms-campaigns',
    '/admin/whatsapp-broadcast',
    '/admin/referrals',
    '/admin/social-media',
    '/admin/news/categories',
    '/admin/support-tickets',
    '/admin/meetings',
    '/admin/documents',
    '/admin/testimonials/manage',
    '/admin/emi-calculator',
    '/admin/loans',
    '/admin/agents',
    '/admin/builders',
    '/admin/reports/financial',
    '/admin/backup',
    '/admin/services/home-loan',
    '/admin/services/legal',
    '/admin/services/interior',
    '/admin/services/tax',
    '/admin/jobs',
    '/admin/applicants',
];

$placeholders = implode(',', array_fill(0, count($brokenUrls), '?'));
$sql = "DELETE FROM admin_menu_items WHERE url IN ($placeholders)";
$stmt = $db->prepare($sql);
$stmt->execute($brokenUrls);

echo "Deleted " . count($brokenUrls) . " broken menu items\n";

// Show remaining
$rs = $db->query("SELECT COUNT(*) as cnt FROM admin_menu_items");
echo "Remaining menu items: " . $rs->fetch()['cnt'] . "\n";