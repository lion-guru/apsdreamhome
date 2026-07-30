<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
}
echo "HELLO WORLD DEBUG VERIFY\n\n";
header('Content-Type: text/plain; charset=UTF-8');
set_time_limit(120);

require_once __DIR__ . '/../config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
$items = $db->fetchAll("SELECT * FROM admin_menu_items WHERE is_active = 1 ORDER BY id");

$totalItems = count($items);
$chunk = isset($_GET['chunk']) ? (int)$_GET['chunk'] : 1;
$chunkSize = 30;

$start = ($chunk - 1) * $chunkSize;
$slicedItems = array_slice($items, $start, $chunkSize);

echo "=== Testing Menu Items: Chunk $chunk (Indices $start to " . ($start + count($slicedItems) - 1) . ") out of $totalItems ===\n\n";

$cookieFile = __DIR__ . '/cookies.txt';

// Log in as Super Admin (if cookie doesn't exist or we are on chunk 1)
if ($chunk === 1 || !file_exists($cookieFile)) {
    if (file_exists($cookieFile)) {
        unlink($cookieFile);
    }
    $loginUrl = 'http://localhost/apsdreamhome/admin/login?test_login=2';
    $ch = curl_init($loginUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_COOKIEFILE     => $cookieFile,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

$failed = 0;
$passed = 0;

foreach ($slicedItems as $it) {
    $url = 'http://localhost/apsdreamhome' . $it['url'];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEFILE     => $cookieFile,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => ['X-Testing: true']
    ]);
    
    $output = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 302) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE     => $cookieFile,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => ['X-Testing: true']
        ]);
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }

    $status = 'PASS';
    $errorMsg = '';

    if ($httpCode !== 200) {
        $status = 'FAIL';
        $bodyPreview = json_encode(substr(trim(strip_tags($output)), 0, 150));
        $errorMsg = "HTTP Status: $httpCode | Body: $bodyPreview";
    } else {
        if (preg_match('/(Fatal error|Parse error|Warning|Notice|Exception):/i', $output, $m)) {
            $status = 'FAIL';
            $errorMsg = trim(strip_tags($m[0] . ' found in HTML'));
        }
    }

    if ($status === 'FAIL') {
        $failed++;
        printf("FAIL: [%3d] Section: %-12s | Name: %-30s | Url: %-45s | %s\n", 
            $it['id'], $it['section'], $it['name'], $it['url'], $errorMsg);
    } else {
        $passed++;
        printf("PASS: [%3d] %-30s | Url: %-45s\n", $it['id'], $it['name'], $it['url']);
    }
}

echo "\nChunk Tested: " . count($slicedItems) . "\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";

if ($start + count($slicedItems) >= $totalItems) {
    if (file_exists($cookieFile)) {
        unlink($cookieFile);
    }
}
