<?php
// tools/backup_data.php

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/bootstrap.php';

use App\Core\Database;

echo "Starting Data Backup...\n";
echo "----------------------------------------\n";

$backupDir = APP_ROOT . '/database/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $tables = ['users', 'employees', 'customers', 'associates'];
    
    foreach ($tables as $table) {
        echo "Backing up '$table'...\n";
        $stmt = $conn->query("SELECT * FROM $table");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $filename = $backupDir . '/' . $table . '_backup_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
        
        echo "✔ Saved " . count($data) . " records to $filename\n";
    }

} catch (Exception $e) {
    echo "Error during backup: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nBackup Complete.\n";
