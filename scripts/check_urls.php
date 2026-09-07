#!/usr/bin/env php
<?php
$urls = [
    '/contact',
    '/employee/login',
    '/property-workflow',
    '/properties',
    '/colonies',
    '/about',
    '/blog',
    '/team',
    '/careers',
    '/tools-hub',
    '/faq',
    '/buy',
    '/sell',
    '/rent',
    '/invest',
    '/gallery',
];

foreach ($urls as $url) {
    $ch = curl_init('http://localhost/apsdreamhome' . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $loc = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    $status = $code == 200 ? 'OK' : ($code == 302 ? 'REDIRECT' : 'FAIL');
    echo "[$status] $url => $code" . ($loc ? " -> $loc" : "") . PHP_EOL;
}
