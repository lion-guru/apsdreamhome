<?php
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$rows = $pdo->query("SELECT id, name, url, section FROM admin_menu_items WHERE is_active=1 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Login as admin
$ch = curl_init('http://localhost/apsdreamhome/admin/login?test_login=1');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => true,
    CURLOPT_COOKIEFILE => '',
    CURLOPT_COOKIEJAR => '',
]);
$loginResp = curl_exec($ch);
preg_match_all('/Set-Cookie: ([^\r\n]+)/', $loginResp, $cookies);
$cookieStr = '';
foreach ($cookies[1] as $c) {
    $parts = explode(';', $c)[0];
    $cookieStr .= ($cookieStr ? '; ' : '') . $parts;
}
curl_close($ch);

echo "Total menu items: " . count($rows) . "\n\n";

$issues = [];
$ok = 0;

foreach ($rows as $r) {
    $url = 'http://localhost/apsdreamhome' . $r['url'];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIE => $cookieStr,
        CURLOPT_NOBODY => false,
        CURLOPT_TIMEOUT => 5,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    
    $title = '';
    if (preg_match('/<title>(.*?)<\/title>/i', $body, $m)) {
        $title = trim($m[1]);
    }
    
    // Check for key admin indicators
    $hasAdminSidebar = (strpos($body, 'sidebar') !== false);
    $hasAssociateSidebar = (strpos($body, 'associate') !== false && strpos($body, 'Associate Dashboard') !== false);
    $hasUserRedirect = (strpos($finalUrl, '/associate/') !== false || strpos($finalUrl, '/user/') !== false);
    $has404 = (strpos($body, '404') !== false && strpos($body, 'not found') !== false);
    $has500 = ($code === 500 || strpos($body, '500') !== false && strpos($body, 'Server Error') !== false);
    $hasLoginForm = (strpos($body, 'admin_login') !== false || strpos($body, 'Admin Login') !== false);
    $isStubPage = (strpos($body, 'under construction') !== false || strpos($body, 'Coming Soon') !== false || strpos($body, 'stub_page') !== false);
    
    $status = 'OK';
    if ($hasUserRedirect) {
        $status = 'REDIRECT_TO_USER_PORTAL';
    } elseif ($has500) {
        $status = '500_ERROR';
    } elseif ($has404) {
        $status = '404_NOT_FOUND';
    } elseif ($hasLoginForm) {
        $status = 'SHOWS_LOGIN';
    } elseif ($isStubPage) {
        $status = 'STUB_PAGE';
    } elseif ($code >= 300 && $code < 400) {
        $status = "REDIRECT_$code";
    }
    
    if ($status !== 'OK') {
        $issues[] = "{$status} | [{$r['id']}] {$r['name']} → {$r['url']} | Title: $title | Final: $finalUrl";
    } else {
        $ok++;
    }
}

echo "=== RESULTS: $ok OK, " . count($issues) . " ISSUES ===\n\n";
foreach ($issues as $i) {
    echo "⚠ $i\n";
}
