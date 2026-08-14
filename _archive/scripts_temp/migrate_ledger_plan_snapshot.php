<?php
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== Adding plan snapshot columns to mlm_commission_ledger ===\n";

$colsToAdd = [
    'plan_id INT UNSIGNED NULL COMMENT "Commission plan ID used for this entry"',
    'plan_version INT UNSIGNED NULL COMMENT "Plan version at time of calculation"',
    'plan_snapshot JSON NULL COMMENT "Full plan snapshot: rates, caps, overrides used for this entry"',
    'calculation_engine VARCHAR(50) DEFAULT "hybrid" COMMENT "Engine used: hybrid, legacy, plan"',
];

$existing = [];
$r = $pdo->query("DESCRIBE mlm_commission_ledger");
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    $existing[] = $row['Field'];
}

foreach ($colsToAdd as $colDef) {
    $colName = explode(' ', $colDef)[0];
    if (!in_array($colName, $existing)) {
        try {
            $pdo->exec("ALTER TABLE mlm_commission_ledger ADD COLUMN $colDef");
            echo "  ADD $colName: OK\n";
        } catch (Exception $e) {
            echo "  ADD $colName: ERROR - " . $e->getMessage() . "\n";
        }
    } else {
        echo "  $colName: already exists\n";
    }
}

// Add indexes
try {
    $pdo->exec("ALTER TABLE mlm_commission_ledger ADD INDEX idx_plan_id (plan_id)");
    echo "  idx_plan_id: OK\n";
} catch (Exception $e) { echo "  idx_plan_id: already exists\n"; }

try {
    $pdo->exec("ALTER TABLE mlm_commission_ledger ADD INDEX idx_plan_version (plan_version)");
    echo "  idx_plan_version: OK\n";
} catch (Exception $e) { echo "  idx_plan_version: already exists\n"; }

// Now create the retroactive recalculation table
echo "\n=== Creating commission_recalculations table ===\n";
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS commission_recalculations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        original_ledger_id BIGINT UNSIGNED NOT NULL,
        new_ledger_id BIGINT UNSIGNED NULL,
        plan_id INT UNSIGNED NOT NULL,
        plan_version INT UNSIGNED NOT NULL,
        reason TEXT NOT NULL,
        original_amount DECIMAL(15,2) NOT NULL,
        new_amount DECIMAL(15,2) NOT NULL,
        amount_diff DECIMAL(15,2) NOT NULL,
        requested_by INT UNSIGNED NOT NULL,
        approved_by INT UNSIGNED NULL,
        status ENUM('pending','approved','rejected','applied') NOT NULL DEFAULT 'pending',
        admin_notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_original (original_ledger_id),
        INDEX idx_status (status),
        INDEX idx_plan (plan_id, plan_version)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "  commission_recalculations: OK\n";
} catch (Exception $e) {
    echo "  commission_recalculations: ERROR - " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n";
echo "\nFinal mlm_commission_ledger schema (new cols):\n";
$r = $pdo->query("DESCRIBE mlm_commission_ledger");
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    if (in_array($row['Field'], ['plan_id','plan_version','plan_snapshot','calculation_engine'])) {
        echo "  {$row['Field']} {$row['Type']}\n";
    }
}?>