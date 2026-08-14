<?php
$cookieFile = tempnam(sys_get_temp_dir(), 'aps_smoke_');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/apsdreamhome/admin/login?test_login=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_VERBOSE, true);
$loginResp = curl_exec($ch);
$loginErr = curl_error($ch);
$loginCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "Login: code=$loginCode, size=" . strlen($loginResp) . ", err=$loginErr\n";

$routes = [
    '/admin/backoffice',
    '/admin/backoffice/attendance',
    '/admin/backoffice/attendance/monthly',
    '/admin/backoffice/leaves',
    '/admin/backoffice/leaves/history',
    '/admin/backoffice/payslips',
    '/admin/backoffice/leads',
    '/admin/backoffice/leads/create',
    '/admin/backoffice/operations',
    '/admin/backoffice/operations/create',
    '/admin/backoffice/reports',
    '/admin/backoffice/reports/1/history',
];

$pass = 0;
$fail = 0;
foreach ($routes as $route) {
    $url = 'http://localhost/apsdreamhome' . $route;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code >= 200 && $code < 400) {
        $pass++;
        echo "[OK  $code] $route\n";
    } else {
        $fail++;
        echo "[FAIL $code] $route\n";
    }
}
echo "\nSmoke: $pass PASS, $fail FAIL out of " . count($routes) . " routes\n";

@unlink($cookieFile);?>