<?php
define('APP_ROOT', dirname(__DIR__, 3));
$config = require __DIR__ . '/../../../config/database.php';
$dbConfig = $config['database'];
$dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
try {
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $stmt = $pdo->query("SHOW CREATE TABLE associates");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $row['Create Table'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
