<?php
require_once __DIR__ . '/app/Core/Database.php';

try {
    $db = App\Core\Database::getInstance()->getConnection();
    echo "Database connection successful!\n";
    
    // Check mlm_commission_ledger schema
    $stmt = $db->query("DESCRIBE mlm_commission_ledger");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "mlm_commission_ledger columns:\n";
    foreach ($columns as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
