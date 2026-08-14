<?php
$ch = curl_init('http://localhost/apsdreamhome/admin/login?test_login=4');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
curl_close($ch);

preg_match('/Set-Cookie:\s*PHPSESSID=([^;]+)/i', $response, $m);
$cookie = $m[1] ?? null;
if (!$cookie) { echo "No cookie\n"; exit(1); }

// Dashboard
$ch2 = curl_init('http://localhost/apsdreamhome/admin/dashboard');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_COOKIE, "PHPSESSID=$cookie");
$html = curl_exec($ch2);
$info = curl_getinfo($ch2);
curl_close($ch2);

echo "Dashboard: HTTP {$info['http_code']}, " . strlen($html) . " bytes\n";

preg_match_all('/sidebar-link/', $html, $m);
echo "sidebar-link count: " . count($m[0]) . "\n";

// Check if it contains admin layout
echo "Has admin layout: " . (strpos($html, 'sidebar-menu') !== false ? 'YES' : 'NO') . "\n";
echo "Has employee layout: " . (strpos($html, 'employee') !== false ? 'YES' : 'NO') . "\n";

// Show title
if (preg_match('/<title>(.*?)<\/title>/', $html, $t)) {
    echo "Title: {$t[1]}\n";
}

// Show first 300 chars
echo "\nFirst 300 chars:\n" . substr($html, 0, 300) . "\n";?>