<?php
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "--- Table: user ---\n";
    $stmt = $pdo->query("DESCRIBE user");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo "{$col['Field']} ({$col['Type']})\n";
    }

    echo "\n--- Table: agents ---\n";
    try {
        $stmt = $pdo->query("DESCRIBE agents");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            echo "{$col['Field']} ({$col['Type']})\n";
        }
    } catch (Exception $e) {
        echo "Table 'agents' not found.\n";
    }

    echo "\n--- Table: associates ---\n";
    try {
        $stmt = $pdo->query("DESCRIBE associates");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            echo "{$col['Field']} ({$col['Type']})\n";
        }
    } catch (Exception $e) {
        echo "Table 'associates' not found.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
