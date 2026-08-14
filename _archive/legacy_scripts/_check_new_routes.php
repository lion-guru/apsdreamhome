<?php
$url = 'http://localhost/apsdreamhome';
$routes = [
    '/admin/leads/1',
    '/admin/leads/1/edit',
    '/admin/deals/1/update-stage',
    '/admin/testimonials/manage',
    '/admin/ai-settings/progressive-register',
    '/admin/ai-settings/campaign-webhook',
    '/property/sell',
    '/property/buy/1',
    '/property/schedule-visit/1',
];
foreach ($routes as $r) {
    $ch = curl_init($url . $r);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo str_pad($r, 50) . ' -> ' . $code . PHP_EOL;
    curl_close($ch);
}?>