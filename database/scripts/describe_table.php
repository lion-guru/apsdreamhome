<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'apsdreamhome';
$table = isset($argv[1]) ? $argv[1] : 'visits';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("DESCRIBE $table");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Table: $table\n";
    echo str_pad("Field", 20) . str_pad("Type", 20) . str_pad("Null", 10) . "\n";
    echo str_repeat("-", 50) . "\n";
    foreach ($columns as $col) {
        echo str_pad($col['Field'], 20) . str_pad($col['Type'], 20) . str_pad($col['Null'], 10) . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
