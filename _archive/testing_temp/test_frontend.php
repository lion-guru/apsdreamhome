<?php
$urls = [
    'http://localhost/apsdreamhome/',
    'http://localhost/apsdreamhome/properties',
    'http://localhost/apsdreamhome/projects',
    'http://localhost/apsdreamhome/about',
    'http://localhost/apsdreamhome/contact',
    'http://localhost/apsdreamhome/services',
    'http://localhost/apsdreamhome/blog',
    'http://localhost/apsdreamhome/faqs',
];
foreach($urls as $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "$httpCode - $url\n";
}
