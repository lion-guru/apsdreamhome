<?php
$base = 'http://localhost/apsdreamhome';

// 125 sidebar menu URLs from DB
$urls = [
'/admin/leads/create','/admin/leads/sources','/admin/leads/status','/admin/leads/followups',
'/admin/leads/scoring','/admin/pages','/admin/leads/import','/admin/leads/analysis',
'/admin/employees','/admin/gallery','/admin/network/tree','/admin/legal-pages',
'/admin/payments','/admin/tasks','/admin/resell-properties','/admin/projects',
'/admin/menu-permissions','/admin/godmode','/admin/careers','/admin/blog',
'/admin/reports/sales','/admin/reports/leads','/admin/reports/commission',
'/admin/network/genealogy','/admin/network/ranks','/admin/activity-log',
'/admin/hrm/employees','/admin/dashboard','/admin/dashboard/agent','/admin/bookings/create',
'/admin/mlm/associates','/admin/telecalling/dashboard','/admin/legal/services',
'/admin/dashboard/builder','/admin/plots/create','/admin/mlm/associates/create',
'/admin/invoices','/admin/locations/districts','/admin/settings/email','/admin/telecalling/assign',
'/admin/legal/disputes','/admin/dashboard/ceo','/admin/properties','/admin/plots/categories',
'/admin/commission','/admin/expenses','/admin/locations/colonies','/admin/settings/sms',
'/admin/telecalling/commissions','/admin/kyc','/admin/legal/deadlines','/admin/dashboard/cfo',
'/admin/users','/admin/roles','/admin/settings/payment','/admin/telecalling/approvals',
'/admin/commission/agent-rates','/admin/dashboard/cm','/admin/leads',
'/admin/commission/associate/structure','/admin/projects/progress',
'/admin/marketing/strategies','/admin/dashboard/coo','/admin/bookings',
'/admin/colonies','/admin/mlm-realestate','/admin/commission/associate/calculations',
'/admin/marketing/marketplace','/admin/land/acquisitions','/admin/dashboard/cto',
'/admin/sites','/admin/commission/bonuses','/admin/banking','/admin/land/records',
'/admin/dashboard/director','/admin/inquiries','/admin/commission/mlm/levels',
'/admin/payroll','/admin/banking/reconciliation','/admin/dashboard/finance',
'/admin/plots','/admin/commission/mlm/records','/admin/gst','/admin/telecaller',
'/admin/dashboard/hr','/admin/locations/states','/admin/mlm-settings/levels',
'/admin/commission/mlm/analytics','/admin/training/courses','/admin/dashboard/it',
'/admin/news','/admin/mlm-settings/rules','/admin/commission/revenue/daily',
'/admin/company/settings','/admin/training/enrollments','/admin/dashboard/marketing',
'/admin/campaigns','/admin/mlm-settings/evaluate','/admin/commission/telecaller/rules',
'/admin/associate-extensions','/admin/api/integrations','/admin/training/certificates',
'/admin/dashboard/operations','/admin/visits','/admin/mlm-settings/associate-progress',
'/admin/commission/telecaller/commissions','/admin/api/developers','/admin/training/modules',
'/admin/dashboard/sales','/admin/deals','/admin/mlm/rank-criteria',
'/admin/dashboard/superadmin','/admin/testimonials','/admin/mlm/upgrades',
'/admin/api-keys','/admin/mlm/withdrawals','/admin/services','/admin/mlm/rewards',
'/admin/user-properties','/admin/plot-costs','/admin/ai_settings','/admin/analytics',
'/admin/mlm','/admin/payouts','/admin/reports',
];

// Get session
$c = curl_init($base . '/admin/login?test_login=1');
curl_setopt_array($c, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_HEADER=>true, CURLOPT_TIMEOUT=>8]);
$r = curl_exec($c);
preg_match_all('/Set-Cookie: ([^;]+)/', $r, $cks);
$cookie = '';
foreach ($cks[1] as $ck) {
    if (strpos($ck, 'PHPSESSID') !== false) $cookie = $ck;
}

echo "MENU_NAME|URL|STATUS|DOCTYPE|TITLE|SIDEBAR|ERRORS|REDIRECT\n";

foreach ($urls as $path) {
    curl_setopt_array($c, [
        CURLOPT_URL => $base . $path,
        CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_FOLLOWLOCATION => false,
    ]);
    $r = curl_exec($c);
    $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
    
    // Extract redirect location
    $redirect = '';
    if ($code >= 301 && $code <= 303) {
        preg_match('/Location: ([^\r\n]+)/', $r, $m);
        $redirect = isset($m[1]) ? basename(parse_url($m[1], PHP_URL_PATH)) : 'yes';
    }
    
    $headerSize = curl_getinfo($c, CURLINFO_HEADER_SIZE);
    $body = substr($r, $headerSize);
    
    $hasDoctype = (stripos($body, '<!DOCTYPE') !== false) ? 'Y' : 'N';
    $hasTitle = (stripos($body, '<title>') !== false) ? 'Y' : 'N';
    $hasSidebar = (stripos($body, 'sidebar-link') !== false) ? 'Y' : 'N';
    $hasErrors = (preg_match('/Fatal error|Warning:|Notice:|Parse error|CRITICAL/i', $body)) ? 'Y' : 'N';
    
    // Get menu name from lookup
    $name = '';
    
    // Output tab-separated for easy parsing
    echo "$path|$code|$hasDoctype|$hasTitle|$hasSidebar|$hasErrors|$redirect\n";
}

curl_close($c);?>