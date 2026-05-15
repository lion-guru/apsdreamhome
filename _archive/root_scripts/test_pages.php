<?php
$pages = ['/', '/about', '/contact', '/login', '/register', '/properties', '/careers', '/admin/login'];

foreach($pages as $page) {
    $url = 'http://localhost/apsdreamhome' . $page;
    $start = microtime(true);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $time = round((microtime(true) - $start) * 1000);
    curl_close($ch);
    
    $status = ($httpCode == 200) ? 'OK' : 'ERROR';
    echo "$page : $httpCode ($status) - {$time}ms\n";
}
