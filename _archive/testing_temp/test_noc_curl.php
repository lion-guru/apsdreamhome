<?php
header('Content-Type: text/plain; charset=UTF-8');
$cookieFile = __DIR__ . '/cookies.txt';

// Log in
$loginUrl = 'http://localhost/apsdreamhome/admin/login?test_login=2';
$ch = curl_init($loginUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR      => $cookieFile,
    CURLOPT_COOKIEFILE     => $cookieFile,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 10,
]);
curl_exec($ch);
curl_close($ch);

// Request page
$url = 'http://localhost/apsdreamhome/admin/noc-registry';
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEFILE     => $cookieFile,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_HTTPHEADER     => ['X-Testing: true']
]);
$output = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n\n";
echo "Response Body Preview:\n";
echo substr(strip_tags($output), 0, 1000);
