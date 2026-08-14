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

// Check employee dashboard
$ch2 = curl_init('http://localhost/apsdreamhome/admin/dashboard');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_COOKIE, "PHPSESSID=$cookie");
$html = curl_exec($ch2);
curl_close($ch2);

echo "=== Employee Dashboard ===\n";
echo "Size: " . strlen($html) . " bytes\n";

// Check for any menu items
preg_match_all('/href="[^"]*\/admin\/([^"]+)"/', $html, $urlMatches);
$urls = array_unique($urlMatches[1]);
echo "Admin URLs in HTML: " . count($urls) . " unique\n";
echo "URLs: " . implode(', ', array_slice($urls, 0, 20)) . "\n\n";

// Check for sidebar or nav patterns
echo "Has sidebar: " . (strpos($html, 'sidebar') !== false ? 'YES' : 'NO') . "\n";
echo "Has nav: " . (strpos($html, '<nav') !== false ? 'YES' : 'NO') . "\n";
echo "Has menu: " . (strpos($html, 'menu') !== false ? 'YES' : 'NO') . "\n";

// Now check associate
$ch3 = curl_init('http://localhost/apsdreamhome/admin/login?test_login=5');
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch3, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch3, CURLOPT_HEADER, true);
$response3 = curl_exec($ch3);
curl_close($ch3);
preg_match('/Set-Cookie:\s*PHPSESSID=([^;]+)/i', $response3, $m3);
$cookie3 = $m3[1] ?? null;

$ch4 = curl_init('http://localhost/apsdreamhome/admin/dashboard');
curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch4, CURLOPT_COOKIE, "PHPSESSID=$cookie3");
$html4 = curl_exec($ch4);
curl_close($ch4);

echo "\n=== Associate Dashboard ===\n";
echo "Size: " . strlen($html4) . " bytes\n";
if (preg_match('/<title>(.*?)<\/title>/', $html4, $t)) {
    echo "Title: {$t[1]}\n";
}
preg_match_all('/sidebar-link/', $html4, $m4);
echo "sidebar-link count: " . count($m4[0]) . "\n";?>