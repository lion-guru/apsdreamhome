<?php
$pages = [
    '/associate/login',
    '/associate/dashboard',
    '/associate/leads',
    '/associate/leads/create',
    '/associate/bookings',
    '/associate/commissions',
    '/associate/team',
    '/associate/profile',
    '/agent/login',
    '/agent/dashboard',
    '/farmer/login',
    '/employee/login',
    '/admin/login'
];
foreach ($pages as $url) {
    $ch = curl_init("http://localhost/apsdreamhome$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo ($status == 200 ? 'OK' : "HTTP $status") . ": $url\n";
}?>