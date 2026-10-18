<?php
$urls = [
    'http://localhost/apsdreamhome/api/health',
    'http://localhost/apsdreamhome/api/v2/mobile/colonies',
    'http://localhost/apsdreamhome/api/v2/mobile/plots/all',
];

foreach ($urls as $url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 15,
    ]);
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($body, 0, $headerSize);
    $bodyContent = substr($body, $headerSize);
    curl_close($ch);

    echo "URL: $url\n";
    echo "HTTP Code: $httpCode\n";
    echo "Content-Type: $contentType\n";
    echo "Header Size: $headerSize\n";
    echo "First 200 chars of body: " . substr($bodyContent, 0, 200) . "\n";
    echo "JSON valid: " . (json_decode($bodyContent) !== null ? "YES" : "NO") . "\n";
    echo "------------------------\n";
}