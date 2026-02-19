<?php
// Hardcoded for XAMPP environment
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'apsdreamhome');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "Connected to database: " . DB_NAME . "\n";

    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Total Tables: " . count($tables) . "\n\n";

    echo "Table Analysis:\n";
    echo str_pad("Table Name", 40) . str_pad("Rows", 10) . "Columns\n";
    echo str_repeat("-", 60) . "\n";

    foreach ($tables as $table) {
        try {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
            $rowCount = $countStmt->fetchColumn();

            $colStmt = $pdo->query("DESCRIBE `$table`");
            $columns = $colStmt->fetchAll(PDO::FETCH_COLUMN);
            $colCount = count($columns);

            echo str_pad($table, 40) . str_pad($rowCount, 10) . $colCount . "\n";
        } catch (Exception $e) {
            echo str_pad($table, 40) . "ERROR: " . $e->getMessage() . "\n";
        }
    }

    echo "\nPotential Duplicates Analysis:\n";
    $suspects = [
        ['user', 'users'],
        ['agent', 'agents'],
        ['associate', 'associates'],
        ['customer', 'customers'],
        ['plot', 'plots'],
        ['property', 'properties']
    ];

    foreach ($suspects as $pair) {
        $t1 = $pair[0];
        $t2 = $pair[1];
        if (in_array($t1, $tables) && in_array($t2, $tables)) {
            echo "WARNING: Both '$t1' and '$t2' exist!\n";
            // Compare columns
            $c1 = $pdo->query("DESCRIBE `$t1`")->fetchAll(PDO::FETCH_COLUMN);
            $c2 = $pdo->query("DESCRIBE `$t2`")->fetchAll(PDO::FETCH_COLUMN);

            // Check rows to see which one is active
            $r1 = $pdo->query("SELECT COUNT(*) FROM `$t1`")->fetchColumn();
            $r2 = $pdo->query("SELECT COUNT(*) FROM `$t2`")->fetchColumn();

            echo "  $t1 ($r1 rows): " . implode(', ', $c1) . "\n";
            echo "  $t2 ($r2 rows): " . implode(', ', $c2) . "\n";
        }
    }
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
