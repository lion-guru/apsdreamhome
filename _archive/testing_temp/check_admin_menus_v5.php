<?php
$host = '127.0.0.1';
$port = '3307';
$db   = 'apsdreamhome';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$stmt = $pdo->query("SELECT name, url FROM admin_menu_items WHERE is_active = 1");
$items = $stmt->fetchAll();

$cookieFile = __DIR__ . '/cookie.txt';
// Login first
$chLogin = curl_init("http://localhost/apsdreamhome/admin/login?test_login=1");
curl_setopt($chLogin, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chLogin, CURLOPT_COOKIEJAR, $cookieFile);
curl_exec($chLogin);
curl_close($chLogin);

$results = [];
foreach ($items as $item) {
    $url = "http://localhost/apsdreamhome" . $item['url'];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Check for PHP errors or 404
    $isError = "OK";
    if (strpos($body, 'Fatal error') !== false || strpos($body, 'Uncaught Error') !== false) {
        $isError = "PHP 500";
    } elseif ($httpCode == 404 || strpos($body, 'Page Not Found') !== false) {
        $isError = "404 Route";
    } elseif ($httpCode == 500) {
        $isError = "HTTP 500";
    }
    
    // Only print errors to find the broken ones!
    if ($isError !== "OK" || $httpCode >= 400) {
        $results[] = str_pad($httpCode, 5) . " | " . str_pad($isError, 10) . " | " . str_pad($item['name'], 30) . " | " . $item['url'];
    }
}
echo count($results) > 0 ? implode("\n", $results) : "All routes OK";
