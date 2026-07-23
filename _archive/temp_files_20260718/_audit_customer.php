<?php
$customerPages = [
    "/login",
    "/register",
    "/user/dashboard",
    "/user/profile",
    "/user/notifications",
    "/user/bookings",
    "/user/payments",
    "/user/documents",
    "/user/refer",
    "/user/wallet",
    "/user/commissions",
    "/user/team",
    "/user/settings",
    "/properties",
    "/blog",
    "/about",
    "/contact",
    "/careers",
    "/services",
    "/tools-hub",
    "/projects",
    "/faqs",
    "/gallery",
    "/news",
    "/privacy",
    "/partner-tools"
];

$issues = [];
foreach ($customerPages as $url) {
    $fullUrl = "http://localhost/apsdreamhome$url";
    $ch = curl_init($fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $html = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($status >= 400) {
        $issues[] = "HTTP $status: $url";
        echo "HTTP $status: $url\n";
    } elseif (preg_match('/Warning:|Fatal error:|Notice:|Undefined variable/', $html)) {
        $issues[] = "PHP ERROR: $url";
        echo "PHP ERROR: $url\n";
    } else {
        preg_match('/<title>(.*?)<\/title>/s', $html, $m);
        $title = $m[1] ?? 'N/A';
        echo "OK: $url [$title]\n";
    }
}

echo "\n=== CUSTOMER PORTAL ISSUES ===\n";
foreach ($issues as $i) echo "$i\n";
