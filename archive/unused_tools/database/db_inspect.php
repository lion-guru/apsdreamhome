<?php
require_once 'config/config.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "✅ Connected to: " . DB_NAME . "\n\n";

    // Show tables
    $tables = $pdo->query("SHOW TABLES");
    echo "📋 Tables:\n";
    foreach ($tables as $row) {
        $table = array_values($row)[0];
        echo "- {$table}\n";

        // Show columns
        $columns = $pdo->query("DESCRIBE {$table}");
        foreach ($columns as $col) {
            echo "  └─ {$col['Field']} ({$col['Type']}) {$col['Null']} {$col['Key']}\n";
        }
        echo "\n";
    }

    // Row counts
    echo "📊 Row counts:\n";
    foreach ($tables as $row) {
        $table = array_values($row)[0];
        $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
        echo "- {$table}: {$count} rows\n";
    }

} catch (PDOException $e) {
    echo "❌ DB Error: " . $e->getMessage() . "\n";
}