<?php
define('APP_ROOT', dirname(__DIR__));
$config = require APP_ROOT . '/config/database.php';
$db_config = $config['database'];

try {
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['database']};charset={$db_config['charset']}";
    $username = $db_config['username'];
    $password = $db_config['password'];

    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking status of featured properties:\n";
    $stmt = $pdo->query("SELECT id, title, featured, status FROM properties WHERE featured = 1");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo "ID: {$row['id']}, Title: {$row['title']}, Featured: {$row['featured']}, Status: {$row['status']}\n";
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
