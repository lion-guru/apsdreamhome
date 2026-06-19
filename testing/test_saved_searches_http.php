<?php
/**
 * End-to-end HTTP test using PHP's built-in HTTP client (curl ext).
 * Simulates a logged-in customer and POSTs a save.
 */

// Load .env
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (!preg_match('/^([A-Z_][A-Z0-9_]*)\s*=\s*(.*)$/i', $line, $m)) continue;
        $key = $m[1]; $val = trim($m[2], " \t\"'");
        $_ENV[$key] = $val;
        putenv("$key=$val");
    }
}

$cookieJar = tempnam(sys_get_temp_dir(), 'cookies');
$base = 'http://localhost/apsdreamhome';

function http($method, $url, $cookieJar, $postData = null) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_COOKIEJAR => $cookieJar,
        CURLOPT_COOKIEFILE => $cookieJar,
        CURLOPT_FOLLOWLOCATION => true,        // <-- follow the 302
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'X-Requested-With: XMLHttpRequest'],
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData ?: []));
    }
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $url_eff = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $headerLen = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'effective_url' => $url_eff, 'body' => substr($resp, $headerLen), 'headers' => substr($resp, 0, $headerLen)];
}

echo "=== 1. Login as customer1@apsdreamhome.com ===\n";
$login = http('POST', "$base/login", $cookieJar, [
    'identity' => 'customer1@apsdreamhome.com',
    'password' => 'Aps@2026',
]);
echo "  Status: {$login['code']}\n";
echo "  Body: " . substr($login['body'], 0, 300) . "\n";

echo "\n=== 2. GET /user/saved-searches (full body for CSRF) ===\n";
$list = http('GET', "$base/user/saved-searches", $cookieJar);
echo "  Status: {$list['code']}\n";
echo "  Body length: " . strlen($list['body']) . " bytes\n";

echo "\n=== 3. POST /user/saved-searches (save new) ===\n";
// First grab the CSRF token from the saved-searches page
$csrf = null;
if (preg_match('/<meta\s+name=["\']csrf-token["\']\s+content=["\']([^"\']+)/i', $list['body'], $m)) {
    $csrf = $m[1];
} elseif (preg_match('/name=["\']csrf_token["\']\s+value=["\']([^"\']+)/i', $list['body'], $m)) {
    $csrf = $m[1];
} elseif (preg_match('/data-csrf=["\']([^"\']+)/i', $list['body'], $m)) {
    $csrf = $m[1];
} elseif (preg_match('/csrf[_-]?token["\']?\s*[:=]\s*["\']([^"\']+)/i', $list['body'], $m)) {
    $csrf = $m[1];
}
echo "  CSRF token: " . ($csrf ? substr($csrf, 0, 16) . '...' : '(not found)') . "\n";

$filters = ['q' => 'Gorakhpur', 'type' => 'villa', 'min_price' => 5000000, 'bedrooms' => 3];
$postData = [
    'name' => '3BHK Villa in Gorakhpur [TEST]',
    'filters' => json_encode($filters),
    'email_alerts' => 1,
];
if ($csrf) $postData['csrf_token'] = $csrf;
$save = http('POST', "$base/user/saved-searches", $cookieJar, $postData);
echo "  Status: {$save['code']}\n";
echo "  Body: " . substr($save['body'], 0, 500) . "\n";

echo "\n=== 4. GET /api/saved-searches/autocomplete?q=Gorakhpur ===\n";
$ac = http('GET', "$base/api/saved-searches/autocomplete?q=Gorakhpur", $cookieJar);
echo "  Status: {$ac['code']}\n";
echo "  Body: " . $ac['body'] . "\n";

echo "\n=== 5. POST /user/saved-searches/cron-alerts (CRON endpoint) ===\n";
$cronSecret = $_ENV['CRON_SECRET'] ?? getenv('CRON_SECRET') ?: 'dev-cron-key';
$cron = http('GET', "$base/user/saved-searches/cron-alerts?key=" . urlencode($cronSecret), $cookieJar);
echo "  Status: {$cron['code']}\n";
echo "  Body: " . substr($cron['body'], 0, 500) . "\n";

echo "\n=== 6. GET /properties?q=Gorakhpur (filtered search) ===\n";
$prop = http('GET', "$base/properties?q=Gorakhpur&type=villa&bedrooms=3", $cookieJar);
echo "  Status: {$prop['code']}\n";
echo "  Body length: " . strlen($prop['body']) . " bytes\n";

echo "\n=== 7. GET /user/saved-searches/manage-alerts ===\n";
$alerts = http('GET', "$base/user/saved-searches/manage-alerts", $cookieJar);
echo "  Status: {$alerts['code']}\n";

unlink($cookieJar);
echo "\n=== Done ===\n";
