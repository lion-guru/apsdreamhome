<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Database;

$db = Database::getInstance();

$tables = ['properties'];
foreach ($tables as $table) {
    echo "=== $table ===\n";
    $cols = $db->fetchAll("SHOW COLUMNS FROM `$table`");
    foreach ($cols as $c) {
        echo "  " . $c['Field'] . " (" . $c['Type'] . ")\n";
    }
    echo "\n";
}
