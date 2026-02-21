<?php
define('APP_ROOT', dirname(__DIR__));
$config = require __DIR__ . '/../config/database.php';
$dbConfig = $config['database'];
try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $stmt = $pdo->query("DESCRIBE properties");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
