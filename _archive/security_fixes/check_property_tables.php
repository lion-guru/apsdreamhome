<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

$stmt = $pdo->query("SHOW TABLES LIKE 'property_%'");
while ($r = $stmt->fetch()) {
    echo $r[0] . PHP_EOL;
}