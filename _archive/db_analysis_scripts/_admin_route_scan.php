<?php
// Quick scan of all major admin routes (correctly without /apsdreamhome/ prefix)
$routes = [
    '/', '/admin/login', '/admin', '/admin/dashboard', '/admin/users', '/admin/properties',
    '/admin/plots', '/admin/colonies', '/admin/projects', '/admin/leads', '/admin/bookings',
    '/admin/payments', '/admin/commissions', '/admin/payouts', '/admin/invoices', '/admin/expenses',
    '/admin/reports', '/admin/settings', '/admin/mlm', '/admin/employees', '/admin/hr',
    '/admin/ai', '/admin/ai-settings', '/admin/voice-agents', '/admin/voice-users',
    '/admin/crm', '/admin/support_tickets', '/admin/inventory', '/admin/loans',
    '/admin/gallery', '/admin/news', '/admin/testimonials', '/admin/faqs',
    '/admin/locations/states', '/admin/locations/districts', '/admin/locations/colonies',
    '/admin/roles', '/admin/permissions', '/admin/audit-log', '/admin/notifications',
    '/admin/plot-costs', '/admin/loyalty', '/admin/associates', '/admin/agents',
    '/admin/referrals', '/admin/team', '/admin/chat', '/admin/chatbot',
    '/admin/translations', '/admin/finance', '/admin/accounting',
    '/admin/marketing', '/admin/campaigns', '/admin/sms', '/admin/email',
    '/admin/analytics', '/admin/financial-reports',
    '/admin/api-keys', '/admin/integrations',
    '/admin/cm-dashboard', '/admin/builder', '/admin/agent', '/admin/cto',
    '/admin/partner', '/admin/coo', '/admin/cheifminister', '/admin/ceo', '/admin/cfo',
    '/admin/hr/leave', '/admin/hr/attendance', '/admin/hr/payroll', '/admin/hr/jobs',
    '/admin/hr/recruitment', '/admin/hr/performance', '/admin/hr/departments',
    '/admin/hr/designations', '/admin/hr/settings', '/admin/hr/users',
    '/admin/network/tree', '/admin/network/ranks', '/admin/network/dashboard',
    '/admin/deals', '/admin/deal-pipeline',
    '/admin/career', '/admin/careers', '/admin/careers/manage',
    '/admin/documents', '/admin/pages', '/admin/blog', '/admin/blogs',
    '/admin/activity-log', '/admin/dev-tools',
    '/admin/property-allocations', '/admin/associate-extensions',
    '/admin/emi', '/admin/sites', '/admin/loans', '/admin/backups',
    '/admin/feature-management', '/admin/payment-gateway', '/admin/payment-analytics',
    '/admin/lead-pipeline', '/admin/lead-sources', '/admin/lead-campaigns',
    '/admin/kyc', '/admin/gst', '/admin/legal', '/admin/insurance',
    '/admin/legal-pages', '/admin/templates', '/admin/widgets',
    '/admin/reports-engine', '/admin/lead-management',
    '/admin/email-templates', '/admin/sms-campaigns', '/admin/whatsapp-broadcast',
    '/admin/visitor-tracking', '/admin/click-tracking', '/admin/call-tracking',
    '/admin/seo', '/admin/redirects', '/admin/sitemap',
    '/admin/property-search', '/admin/lead-search',
    '/admin/team-management', '/admin/team-performance',
    '/admin/api-management', '/admin/api-documentation',
    '/admin/reputation', '/admin/feedback', '/admin/ratings',
    '/admin/payment-history', '/admin/commission-history',
    '/admin/employee-attendance', '/admin/employee-leave', '/admin/employee-payroll',
    '/admin/chat-management', '/admin/chat-history',
    '/admin/customer-preferences', '/admin/customer-views',
    '/admin/social-media', '/admin/social-login', '/admin/oauth',
    '/admin/calendar', '/admin/timeline',
    '/admin/import', '/admin/export', '/admin/backup',
    '/admin/security', '/admin/security-audit', '/admin/ip-blocking',
    '/admin/maintenance', '/admin/system-info', '/admin/phpinfo',
    '/admin/voice-calls', '/admin/call-logs', '/admin/call-scripts',
    '/admin/scripts', '/admin/agents-list', '/admin/agent-performance',
    '/admin/notification-templates', '/admin/notification-center',
    '/admin/email-queue', '/admin/sms-queue', '/admin/whatsapp-queue',
    '/admin/ai-training', '/admin/ai-models', '/admin/ai-dashboard',
    '/home', '/properties', '/plots', '/login', '/register', '/contact',
    '/about', '/services', '/gallery', '/faq', '/blog', '/careers',
    '/support', '/whatsapp-chat', '/map', '/user/network',
    '/user/dashboard', '/user/properties', '/user/inquiries', '/user/favorites',
    '/user/saved-searches', '/user/investments', '/user/bookings',
];

$counts = ['200' => 0, '302' => 0, '401' => 0, '403' => 0, '404' => 0, '500' => 0, 'other' => 0];
$fails = [];
$success = 0;

foreach (array_unique($routes) as $r) {
    $url = "http://localhost" . $r;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_NOBODY => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 10,
    ]);
    curl_exec($ch);
    $code = (string)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (isset($counts[$code])) $counts[$code]++;
    else $counts['other']++;
    if ($code === '500') $fails[] = "500 $r";
    if ($code === '404') $fails[] = "404 $r";
    if (in_array($code, ['200', '302', '401', '403'])) $success++;
}

echo "ROUTE SCAN RESULTS (no /apsdreamhome/ prefix)\n";
echo str_repeat("=", 50) . "\n";
foreach ($counts as $code => $n) echo sprintf("  HTTP %s: %d\n", $code, $n);
echo "\nTotal: " . count($routes) . " | Success (200/302/401/403): $success\n";

if ($fails) {
    echo "\nFAILING ROUTES:\n";
    foreach (array_slice($fails, 0, 50) as $f) echo "  $f\n";
    if (count($fails) > 50) echo "  ... and " . (count($fails) - 50) . " more\n";
} else {
    echo "\nNo 404s or 500s found. PERFECT!\n";
}?>