<?php
// Check database connection without assuming namespace
try {
    $dsn = "mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $tables = ['mlm_network_tree', 'user_wallets', 'mlm_commission_ledger', 'associates', 'users'];
    $out = "";
    foreach($tables as $t) {
        $out .= "=== $t ===\n";
        try {
            $stmt = $pdo->query("DESCRIBE $t");
            foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $out .= $row['Field'] . " - " . $row['Type'] . "\n";
            }
        } catch (Exception $e) { $out .= $e->getMessage() . "\n"; }
    }
    echo nl2br($out);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
