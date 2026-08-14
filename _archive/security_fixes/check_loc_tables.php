<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    if (stripos($t, 'state') !== false || stripos($t, 'district') !== false || stripos($t, 'city') !== false || stripos($t, 'location') !== false || stripos($t, 'area') !== false || stripos($t, 'region') !== false) {
        echo "$t\n";
    }
}?>