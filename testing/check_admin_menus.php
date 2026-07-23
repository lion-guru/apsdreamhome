<?php
require_once __DIR__ . '/../app/core/Autoloader.php';
spl_autoload_register('App\Core\Autoloader::load');
require_once __DIR__ . '/../config/database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("SELECT name, url FROM admin_menu_items WHERE is_active = 1");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$results = [];
foreach ($items as $item) {
    $url = "http://localhost/apsdreamhome" . $item['url'];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true); 
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $results[] = str_pad($httpCode, 5) . " | " . str_pad($item['name'], 30) . " | " . $item['url'];
}

echo implode("\n", $results);
