<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

function query($pdo, $sql) {
    echo "--- $sql ---\n";
    try {
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        print_r($results);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "<pre>";
query($pdo, "DESCRIBE user_wallets");
query($pdo, "DESCRIBE telecaller_performance");
query($pdo, "DESCRIBE employees");
echo "</pre>";
