<?php
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
echo "=== leads ===\n";
$r = $pdo->query('SHOW COLUMNS FROM leads');
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . ($row['Key'] ?: '') . "\n";
}?>