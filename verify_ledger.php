<?php
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    echo "Checking mlm_commission_ledger schema...\n";
    $stmt = $conn->query("DESCRIBE mlm_commission_ledger");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $missing = [];
    if (!in_array('commission_type', $columns)) $missing[] = 'commission_type';
    if (!in_array('commission_percentage', $columns)) $missing[] = 'commission_percentage';
    if (!in_array('level', $columns)) $missing[] = 'level';

    if (!empty($missing)) {
        echo "MISSING COLUMNS: " . implode(', ', $missing) . "\n";
        
        // Attempt to add them
        foreach ($missing as $col) {
            echo "Attempting to add $col...\n";
            if ($col === 'commission_type') {
                $conn->exec("ALTER TABLE mlm_commission_ledger ADD COLUMN commission_type VARCHAR(50) DEFAULT 'standard' AFTER source_user_id");
            } elseif ($col === 'commission_percentage') {
                $conn->exec("ALTER TABLE mlm_commission_ledger ADD COLUMN commission_percentage DECIMAL(5,2) DEFAULT 0.00 AFTER amount");
            } elseif ($col === 'level') {
                 $conn->exec("ALTER TABLE mlm_commission_ledger ADD COLUMN level INT DEFAULT 0 AFTER amount");
            }
        }
        echo "Columns added successfully.\n";
    } else {
        echo "All required columns present.\n";
    }

    echo "\nChecking associate_levels schema...\n";
    $stmt = $conn->query("DESCRIBE associate_levels");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    print_r($columns);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
