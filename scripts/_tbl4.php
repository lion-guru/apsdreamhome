<?php
require __DIR__ . '/../vendor/autoload.php';
use App\Core\Database\Database;
$db = Database::getInstance();
$tables = ['user_activity_logs_unified', 'security_log', 'performance_log', 'system_logs'];
foreach ($tables as $table) {
    echo "=== $table ===\n";
    try {
        $cols = $db->fetchAll("SHOW COLUMNS FROM `$table`");
        foreach ($cols as $c) echo "  " . $c['Field'] . "\n";
    } catch (Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
