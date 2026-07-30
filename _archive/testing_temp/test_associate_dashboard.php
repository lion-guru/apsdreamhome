<?php
header('Content-Type: text/plain; charset=UTF-8');
$cookieFile = __DIR__ . '/cookies.txt';

// Log in as Associate (role 5)
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}
$loginUrl = 'http://localhost/apsdreamhome/admin/login?test_login=5';
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

// Test Associate URLs
$urls = [
    '/associate/dashboard',
    '/associate/add-property',
    '/associate/leads',
    '/associate/commissions',
    '/associate/properties',
    '/associate/sold',
    '/associate/pending',
    '/associate/profile',
    '/associate/genealogy',
    '/associate/wallet',
    '/associate/bank-details',
    '/associate/settings',
    '/associate/mlm-plan',
    '/associate/list-property',
    '/associate/leads/add',
    '/associate/leads/all',
    '/associate/commissions/history',
    '/associate/wallet/withdraw',
    '/associate/network/tree',
    '/associate/team',
    '/associate/team/add',
    '/associate/team/performance',
    '/associate/collections',
    '/associate/collections/create',
];

$passed = 0;
$failed = 0;

foreach ($urls as $url) {
    $fullUrl = 'http://localhost/apsdreamhome' . $url;
    $ch = curl_init($fullUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEFILE     => $cookieFile,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => ['X-Testing: true']
    ]);
    
    $output = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 302) {
        // Follow redirect once
        $ch = curl_init($fullUrl);
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
    }
    
    $status = 'PASS';
    $err = '';
    if ($httpCode !== 200) {
        $status = 'FAIL';
        $err = "HTTP Status: $httpCode | Body: " . json_encode(substr(trim(strip_tags($output)), 0, 100));
        $failed++;
    } else {
        if (preg_match('/(Fatal error|Parse error|Warning|Notice|Exception):/i', $output, $m)) {
            $status = 'FAIL';
            $err = trim(strip_tags($m[0] . ' found in HTML'));
            $failed++;
        } else {
            $passed++;
        }
    }
    
    printf("%s: %-30s | Code: %d | %s\n", $status, $url, $httpCode, $err);
}

echo "\nSummary: $passed passed, $failed failed.\n";
