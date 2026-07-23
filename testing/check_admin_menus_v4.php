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

$stmt = $pdo->query("SELECT name, url FROM admin_menu_items WHERE is_active = 1 LIMIT 20");
$items = $stmt->fetchAll();

$results = [];
foreach ($items as $item) {
    $url = "http://localhost/apsdreamhome" . $item['url'];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // REMOVED CURLOPT_NOBODY to send GET request
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // check if it's actually an error page
    $isError = strpos($body, 'Fatal error') !== false || strpos($body, 'Uncaught Error') !== false ? "PHP ERROR" : "OK";
    
    $results[] = str_pad($httpCode, 5) . " | " . str_pad($isError, 10) . " | " . str_pad($item['name'], 30) . " | " . $item['url'];
}
echo implode("\n", $results);
