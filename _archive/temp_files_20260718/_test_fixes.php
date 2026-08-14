<?php
error_reporting(0);
$base = 'http://localhost/apsdreamhome';

// Get admin session
$ch = curl_init("$base/admin/login?test_login=1");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/_ck.txt');
curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/_ck.txt');
curl_exec($ch);
curl_close($ch);

$urls = [
    '/admin/compliance-scorecard',
    '/admin/features/registrations',
    '/admin/meetings',
    '/admin/messages',
    '/admin/noc-registry',
    '/admin/saved-searches',
    '/admin/smart-registration',
    '/admin/payments',
];

foreach ($urls as $url) {
    $ch = curl_init($base . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/_ck.txt');
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    
    $icon = $code === 200 ? 'âœ…' : ($code === 302 ? 'â†—ï¸�' : 'â�Œ');
    echo "$icon $code $url" . ($code !== 200 ? " -> $finalUrl" : "") . "\n";
}?>