<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Adding columns to foreclosure_logs...\n";
    
    $sqls = [
        "ALTER TABLE foreclosure_logs ADD COLUMN foreclosure_amount DECIMAL(10,2) DEFAULT 0.00 AFTER emi_plan_id",
        "ALTER TABLE foreclosure_logs ADD COLUMN original_amount DECIMAL(10,2) DEFAULT 0.00 AFTER foreclosure_amount",
        "ALTER TABLE foreclosure_logs ADD COLUMN penalty_amount DECIMAL(10,2) DEFAULT 0.00 AFTER original_amount",
        "ALTER TABLE foreclosure_logs ADD COLUMN waiver_amount DECIMAL(10,2) DEFAULT 0.00 AFTER penalty_amount",
        "ALTER TABLE foreclosure_logs ADD COLUMN notes TEXT AFTER message",
        // Rename emi_plan_id to plan_id if I want consistent naming? No, emi_plan_id is fine.
        // But my model used plan_id. I will stick to emi_plan_id in DB and update model.
    ];
    
    foreach ($sqls as $sql) {
        try {
            $conn->exec($sql);
            echo "Executed: $sql\n";
        } catch (PDOException $e) {
            // Ignore if column exists (error code 42S21)
            if ($e->getCode() == '42S21') {
                echo "Column already exists (skipped): $sql\n";
            } else {
                echo "Error executing $sql: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "Done.\n";
    
    // Verify
    $stmt = $conn->query("DESCRIBE foreclosure_logs");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Field']} ({$row['Type']})\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
