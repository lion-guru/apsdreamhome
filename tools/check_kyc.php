<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/bootstrap.php';

use App\Core\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

echo "Checking tables with 'kyc' or 'bank'...\n";
$tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    if (strpos($table, 'kyc') !== false || strpos($table, 'bank') !== false || strpos($table, 'account') !== false) {
        echo "Found Table: $table\n";
        $cols = $conn->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
        echo "Columns:\n";
        foreach ($cols as $col) {
            echo " - " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
        echo "----------------------------------------\n";
    }
}
