<?php
try {
    $dsn = "mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger LIKE 'status'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $type = $row['Type']; 
        if (strpos($type, "'missed'") === false) {
            $newType = str_replace(")", ",'missed')", $type);
            $pdo->exec("ALTER TABLE mlm_commission_ledger MODIFY COLUMN status $newType DEFAULT 'pending'");
            echo "Successfully added 'missed' to status enum.\n";
        } else {
            echo "'missed' already exists in status enum.\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
