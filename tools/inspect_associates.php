<?php
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$tables = ['associates'];

foreach ($tables as $table) {
    echo "--- Schema for table: $table ---\n";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo "{$col['Field']}: {$col['Type']} (Null: {$col['Null']}, Key: {$col['Key']}, Default: {$col['Default']}, Extra: {$col['Extra']})\n";
        }
    } catch (PDOException $e) {
        echo "Error describing $table: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
