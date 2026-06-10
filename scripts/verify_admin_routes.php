<?php
/**
 * Verify Admin Routes - Test all active admin_menu_items URLs
 */

$host = '127.0.0.1';
$port = '3307';
$user = 'root';
$pass = '';
$db   = 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo "DB connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$stmt = $pdo->query("SELECT url, name FROM admin_menu_items WHERE is_active = 1 ORDER BY section, order_index");
$routes = $stmt->fetchAll();

echo "=== Admin Route Verification ===\n";
echo "Total active routes found: " . count($routes) . "\n\n";

$counts = ['200' => 0, '302' => 0, '403' => 0, '500' => 0, 'other' => 0];
$failed = [];
$unexpected = [];
$results = [];

foreach ($routes as $i => $route) {
    $url = $route['url'];
    $name = $route['name'];

    // Build full URL with test_login param
    $fullUrl = 'http://localhost/apsdreamhome' . $url;
    if (strpos($url, '?') !== false) {
        $fullUrl .= '&test_login=1';
    } else {
        $fullUrl .= '?test_login=1';
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $fullUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_NOBODY         => false,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER         => false,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        $httpCode = 0;
    }

    $status = (string)$httpCode;
    if (isset($counts[$status])) {
        $counts[$status]++;
    } else {
        $counts['other']++;
    }

    $results[] = [
        'name'  => $name,
        'url'   => $url,
        'code'  => $httpCode,
        'error' => $error,
    ];

    if ($httpCode === 500) {
        $failed[] = $route;
    } elseif (!in_array($httpCode, [200, 302, 403])) {
        $unexpected[] = ['name' => $name, 'url' => $url, 'code' => $httpCode, 'error' => $error];
    }

    printf("[%3d/%d] %-45s %s %s\n", $i + 1, count($routes), substr($url, 0, 45), str_pad((string)$httpCode, 4), $name);
}

echo "\n";
echo "=========================================\n";
echo "           SUMMARY TABLE\n";
echo "=========================================\n";
echo sprintf("  %-20s %d\n", "Total routes:", count($routes));
echo sprintf("  %-20s %d\n", "HTTP 200:", $counts['200']);
echo sprintf("  %-20s %d\n", "HTTP 302:", $counts['302']);
echo sprintf("  %-20s %d\n", "HTTP 403:", $counts['403']);
echo sprintf("  %-20s %d\n", "HTTP 500:", $counts['500']);
echo sprintf("  %-20s %d\n", "Other:", $counts['other']);
echo "=========================================\n";

if (!empty($failed)) {
    echo "\n### BUGS (HTTP 500) ###\n";
    foreach ($failed as $f) {
        echo sprintf("  %-50s %s\n", $f['url'], $f['name']);
    }
}

if (!empty($unexpected)) {
    echo "\n### UNEXPECTED STATUS CODES ###\n";
    foreach ($unexpected as $u) {
        $err = $u['error'] ? " (curl: {$u['error']})" : '';
        echo sprintf("  %-50s %-6s %s%s\n", $u['url'], $u['code'], $u['name'], $err);
    }
}

if (empty($failed) && empty($unexpected)) {
    echo "\nAll routes returned 200/302/403. No bugs detected.\n";
}

echo "\nDone.\n";
