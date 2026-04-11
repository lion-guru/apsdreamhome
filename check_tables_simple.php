<?php
require 'config/bootstrap.php';
$pdo = \App\Core\Database::getInstance()->getConnection();
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "TOTAL: " . count($tables) . "\n";
foreach ($tables as $t) {
    echo $t . "\n";
}
