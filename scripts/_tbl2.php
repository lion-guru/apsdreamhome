<?php
require __DIR__ . '/../vendor/autoload.php';
use App\Core\Database\Database;
$db = Database::getInstance();
$tables = ['activity_logs', 'activity_logs_unified', 'log_entries', 'system_logs'];
foreach ($tables as $table) {
    echo "=== $table ===\n";
    $cols = $db->fetchAll("SHOW COLUMNS FROM `$table`");
    foreach ($cols as $c) echo "  " . $c['Field'] . "\n";
    echo "\n";
}
