#!/usr/bin/env php
<?php
$urls = ['/contact', '/about', '/property-workflow', '/employee/login'];
foreach ($urls as $url) {
    $ch = curl_init('http://localhost/apsdreamhome' . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "=== $url => HTTP $code ===" . PHP_EOL;
    // Show first error line
    if (preg_match('/<b>(Fatal|Parse|Warning|Notice)[^<]*<\/b>/', $body, $m)) {
        echo "ERROR: " . strip_tags($m[0]) . PHP_EOL;
    }
    if (preg_match('/SQLSTATE[\[\(][^\)]+[\]\)]/', $body, $m)) {
        echo "SQL: " . $m[0] . PHP_EOL;
    }
    echo PHP_EOL;
}
