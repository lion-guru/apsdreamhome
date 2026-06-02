<?php
/**
 * Deep scan: test 150+ admin/frontend routes via file_get_contents with session cookie
 * Usage: php _tmp_deep_scan.php
 */
// First get session from admin login
$opts = ['http' => ['method' => 'GET', 'header' => '', 'follow_location' => false]];
$ctx = stream_context_create($opts);

// Get login page with test_login bypass
$loginUrl = 'http://localhost/apsdreamhome/admin/login?test_login=1';
$resp = file_get_contents($loginUrl, false, $ctx);
$cookies = [];
foreach ($http_response_header as $h) {
  if (preg_match('/^Set-Cookie:\s*([^=]+)=([^;]+)/', $h, $m)) {
    $cookies[$m[1]] = $m[2];
  }
}
$sid = $cookies['PHPSESSID'] ?? '';
echo "Session: " . ($sid ? "OK ($sid)" : "FAIL") . "\n";

if (!$sid) { echo "No session — aborting\n"; exit(1); }

$cookieStr = 'PHPSESSID=' . $sid;
$adminOpts = ['http' => [
  'method' => 'GET',
  'header' => "Cookie: $cookieStr\r\n",
  'follow_location' => false,
  'ignore_errors' => true
]];
$adminCtx = stream_context_create($adminOpts);

$routes = [
  // Admin core
  '/admin/dashboard', '/admin/analytics', '/admin/reports', '/admin/bookings',
  '/admin/bookings/1', '/admin/properties', '/admin/projects', '/admin/plots',
  '/admin/sites', '/admin/mlm', '/admin/commission', '/admin/payouts',
  '/admin/payments', '/admin/accounting', '/admin/users',
  '/admin/leads', '/admin/deals', '/admin/sales', '/admin/campaigns',
  '/admin/ceo-dashboard', '/admin/cfo-dashboard', '/admin/builder-dashboard',
  '/admin/agent-dashboard', '/admin/cm-dashboard',
  '/admin/hr/users', '/admin/hr/attendance', '/admin/hr/leaves',
  '/admin/payroll', '/admin/financial-reports',
  '/admin/voice-agents', '/admin/voice-agents/history',
  '/admin/voice-agents/schedule', '/admin/voice-agents/scripts',
  '/admin/voice-agents/extracted-leads', '/admin/voice-agents/settings',
  '/admin/deal-pipeline', '/admin/property-allocations',
  '/admin/localization', '/admin/network/tree',
  '/admin/plot-costs', '/admin/plot-costs/colony/2',
  '/admin/locations/states', '/admin/locations/colonies',
  '/admin/reports-engine', '/admin/reports/funnel',
  '/admin/reports/agent-performance', '/admin/reports/conversion',
  '/admin/land/acquisitions', '/admin/land/records',
  '/admin/loans', '/admin/backups', '/admin/inventory',
  // Public
  '/', '/properties', '/services', '/contact', '/blog', '/news', '/faq',
  '/login', '/register', '/associate/login', '/employee/login',
  '/tools-hub', '/calc', '/colonies', '/plots',
  '/user/dashboard', '/user/favorites', '/user/saved-searches',
];

$passed = 0; $failed = 0; $results = [];
foreach ($routes as $path) {
  $url = "http://localhost/apsdreamhome$path";
  $content = @file_get_contents($url, false, $adminCtx);
  $status = 0;
  if (isset($http_response_header[0]) && preg_match('/\s(\d+)\s/', $http_response_header[0], $m)) {
    $status = (int)$m[1];
  }
  $ok = ($status >= 200 && $status < 400);
  if ($ok) { $passed++; } else { $failed++; $results[] = "$path => $status FAIL"; }
}

echo "\n=== Deep Scan: $passed passed, $failed failed ===\n";
foreach ($results as $r) { echo "  $r\n"; }
if ($failed === 0) { echo "  Zero failures!\n"; }

unlink(__FILE__);
