<?php
$urls = [
    '/admin/dashboard','/admin/colonies','/admin/mlm','/admin/commission',
    '/admin/payouts','/admin/settings','/admin/reports','/admin/analytics',
    '/admin/leads','/admin/properties','/admin/projects','/admin/plots',
    '/admin/users','/admin/invoices','/admin/bookings',
    '/admin/campaigns','/admin/tasks','/admin/locations/states',
    '/admin/locations/districts','/admin/locations/colonies'
];
$allOk = true;
foreach ($urls as $url) {
    $ch = curl_init('http://localhost/apsdreamhome' . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $status = ($code >= 200 && $code < 400) ? "OK" : "FAIL";
    if ($status === "FAIL") { echo "  FAIL $url -> $code\n"; $allOk = false; }
    else { echo "  OK $url -> $code\n"; }
}
echo $allOk ? "\nALL ADMIN PAGES OK!\n" : "\nSOME FAILED\n";
