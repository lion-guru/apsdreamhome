<?php
define('APP_ROOT', dirname(__DIR__));
// Check featured properties status
$config = require dirname(__DIR__) . '/config/database.php';
$dbConfig = $config['database'];
try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);

    $stmt = $pdo->query("SELECT id, title, featured, status FROM properties WHERE featured=1");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
