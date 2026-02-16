<?php
require_once 'app/Core/Database.php';

try {
    $db = App\Core\Database::getInstance()->getConnection();
    echo "Checking mlm_commission_ledger schema...\n";

    // Check if commission_type column exists
    $stmt = $db->query("DESCRIBE mlm_commission_ledger");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('commission_type', $columns)) {
        echo "Adding commission_type column...\n";
        $db->exec("ALTER TABLE mlm_commission_ledger ADD COLUMN commission_type VARCHAR(50) DEFAULT 'standard' AFTER source_user_id");
        echo "Column added successfully.\n";
    } else {
        echo "commission_type column already exists.\n";
    }

    // Check if commission_percentage column exists (used in recordCommission)
    if (!in_array('commission_percentage', $columns)) {
        echo "Adding commission_percentage column...\n";
        $db->exec("ALTER TABLE mlm_commission_ledger ADD COLUMN commission_percentage DECIMAL(5,2) DEFAULT 0.00 AFTER sale_amount");
        echo "Column added successfully.\n";
    } else {
        echo "commission_percentage column already exists.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
