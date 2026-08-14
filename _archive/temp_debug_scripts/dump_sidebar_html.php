<?php
$ch = curl_init('http://localhost/apsdreamhome/admin/login?test_login=2');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
curl_close($ch);

preg_match('/Set-Cookie:\s*PHPSESSID=([^;]+)/i', $response, $m);
$cookie = $m[1] ?? null;
if (!$cookie) { echo "No cookie\n"; exit(1); }

$ch2 = curl_init('http://localhost/apsdreamhome/admin/dashboard');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_COOKIE, "PHPSESSID=$cookie");
$html = curl_exec($ch2);
curl_close($ch2);

echo "Total HTML size: " . strlen($html) . " bytes\n\n";

preg_match_all('/sidebar-link/', $html, $linkMatches);
echo "sidebar-link occurrences in full HTML: " . count($linkMatches[0]) . "\n";

if (preg_match('/<aside[\s\S]*?<\/aside>/', $html, $aside)) {
    echo "Aside tag found: " . strlen($aside[0]) . " bytes\n";
} else {
    echo "No aside tag found\n";
}

// Show a snippet around sidebar
$pos = strpos($html, 'sidebar-link');
if ($pos !== false) {
    echo "\nSnippet at sidebar-link (pos $pos):\n";
    echo substr($html, $pos - 50, 300) . "\n";
} else {
    echo "\nNo sidebar-link found. Checking body start:\n";
    echo substr($html, 0, 500) . "\n";
}?>