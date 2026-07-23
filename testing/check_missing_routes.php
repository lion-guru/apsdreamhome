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

$webPhp = file_get_contents('c:/xampp/htdocs/apsdreamhome/routes/web.php');
$webPhpLines = explode("\n", $webPhp);

$missingRoutes = [];

foreach ($items as $item) {
    $url = $item['url'];
    // Sometimes URLs have dynamic parts, but let's just check if the prefix exists
    // Or just look for the literal string
    $found = false;
    foreach ($webPhpLines as $line) {
        if (strpos($line, "'" . $url . "'") !== false || strpos($line, '"' . $url . '"') !== false) {
            $found = true;
            break;
        }
    }
    
    // Also check api.php
    if (!$found) {
        $apiPhp = file_get_contents('c:/xampp/htdocs/apsdreamhome/routes/api.php');
        if (strpos($apiPhp, "'" . $url . "'") !== false || strpos($apiPhp, '"' . $url . '"') !== false) {
            $found = true;
        }
    }
    
    if (!$found) {
        $missingRoutes[] = $url;
    }
}

echo "Total missing routes from web.php: " . count($missingRoutes) . "\n";
foreach ($missingRoutes as $r) {
    echo $r . "\n";
}
