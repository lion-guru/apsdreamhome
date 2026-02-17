<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Core\Database;
use App\Core\App;

// Initialize App to load config
$app = new App(dirname(__DIR__, 3));

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables found:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }

    // Check specific tables for columns
    $checkTables = ['bookings', 'customers', 'farmers', 'users', 'properties', 'leads', 'admin'];
    foreach ($checkTables as $table) {
        if (in_array($table, $tables)) {
            echo "\nColumns in $table:\n";
            $stmt = $pdo->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($columns as $col) {
                echo "  - $col\n";
            }
        } else {
            echo "\nWARNING: Table '$table' NOT FOUND!\n";
        }
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
