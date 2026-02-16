<?php
require_once 'app/Core/Database.php';

try {
    $db = App\Core\Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'mlm_commission_ledger'");
    // We need to know the database name. It's usually in the connection or env.
    // Let's guess or get it from the connection.
    // Actually, simple DESCRIBE is better if we can capture output.
    // Let's use fetchAll on DESCRIBE.
    $stmt = $db->query("DESCRIBE mlm_commission_ledger");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(", ", $columns) . "\n";
    
    if (in_array('commission_type', $columns)) {
        echo "commission_type EXISTS\n";
    } else {
        echo "commission_type MISSING\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
