<?php
$baseUrl = 'http://localhost/apsdreamhome';
$loginUrl = "$baseUrl/admin/login?test_login=3";

$ch = curl_init($loginUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_COOKIEFILE => '',
    CURLOPT_COOKIEJAR => '',
]);
$response = curl_exec($ch);
curl_close($ch);

preg_match('/Set-Cookie:\s*PHPSESSID=([^;]+)/i', $response, $m);
$sessionId = $m[1] ?? '';

$dashUrl = "$baseUrl/admin/dashboard";
$ch = curl_init($dashUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIE => "PHPSESSID=$sessionId",
]);
$html = curl_exec($ch);
curl_close($ch);

echo "=== FULL TELECALLER DASHBOARD HTML ===\n";
echo $html;
