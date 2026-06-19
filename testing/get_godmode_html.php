<?php
$cookieFile = __DIR__ . '/cookies.txt';

// Fetch godmode page
$url = 'http://localhost/apsdreamhome/admin/godmode';
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEFILE     => $cookieFile,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_HTTPHEADER     => ['X-Testing: true']
]);
$output = curl_exec($ch);
curl_close($ch);

echo $output;
