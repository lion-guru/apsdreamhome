<?php
try {
    $dsn = "mysql:host=localhost;dbname=apsdreamhome";
    $pdo = new PDO($dsn, "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Table: payments\n";
    $stmt = $pdo->query("DESCRIBE payments");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }

    echo "\nTable: commissions\n";
    try {
        $stmt = $pdo->query("DESCRIBE commissions");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    } catch (PDOException $e) {
        echo "Table commissions does not exist.\n";
    }
    
    echo "\nTable: mlm_commissions\n";
    try {
        $stmt = $pdo->query("DESCRIBE mlm_commissions");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    } catch (PDOException $e) {
        echo "Table mlm_commissions does not exist.\n";
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
