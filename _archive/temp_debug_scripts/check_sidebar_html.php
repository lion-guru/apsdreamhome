<?php
$baseUrl = 'http://localhost/apsdreamhome';
$loginUrl = "$baseUrl/admin/login?test_login=3";

// Login as telecaller
$ch = curl_init($loginUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_COOKIEFILE => '',
    CURLOPT_COOKIEJAR => '',
]);
$response = curl_exec($ch);
curl_close($ch);

preg_match('/Set-Cookie:\s*PHPSESSID=([^;]+)/i', $response, $m);
$sessionId = $m[1] ?? '';

// Access dashboard
$dashUrl = "$baseUrl/admin/dashboard";
$ch = curl_init($dashUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => false,
    CURLOPT_COOKIE => "PHPSESSID=$sessionId",
]);
$html = curl_exec($ch);
curl_close($ch);

echo "Dashboard size: " . strlen($html) . " bytes\n";

// Check sidebar HTML class patterns
echo "\n=== SIDEBAR CLASS PATTERNS ===\n";
echo "class=\"sidebar-link: " . substr_count($html, 'class="sidebar-link') . "\n";
echo "class='sidebar-link: " . substr_count($html, "class='sidebar-link") . "\n";
echo "sidebar-link: " . substr_count($html, 'sidebar-link') . "\n";
echo "nav-link: " . substr_count($html, 'nav-link') . "\n";
echo "menu-item: " . substr_count($html, 'menu-item') . "\n";
echo "sidebar-menu: " . substr_count($html, 'sidebar-menu') . "\n";

// Extract a sample sidebar section
if (preg_match('/<nav[^>]*sidebar[^>]*>(.*?)<\/nav>/si', $html, $nav)) {
    echo "\n=== SAMPLE SIDEBAR (first 2000 chars) ===\n";
    echo substr($nav[1], 0, 2000) . "\n";
} elseif (preg_match('/id="sidebarMenu"(.*?)<\/div>/si', $html, $sidebar)) {
    echo "\n=== SIDEBAR MENU SECTION (first 2000 chars) ===\n";
    echo substr($sidebar[1], 0, 2000) . "\n";
} else {
    echo "\n=== No sidebar nav/sidebarMenu found ===\n";
    // Find any sidebar reference
    preg_match_all('/class="[^"]*sidebar[^"]*"/', $html, $sideclasses);
    echo "Sidebar-related classes found: " . count($sideclasses[0]) . "\n";
    foreach (array_slice($sideclasses[0], 0, 10) as $c) {
        echo "  $c\n";
    }
}?>