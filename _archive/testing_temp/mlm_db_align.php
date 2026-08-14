<?php
// Phase 2: DB Alignment â€” add missing columns, fix ENUM, normalize rank names
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== Phase 2: DB Alignment ===\n\n";

// 1. Add missing columns to mlm_commission_ledger
echo "--- 1. Adding missing columns to mlm_commission_ledger ---\n";
$alterCols = [
    "ADD COLUMN source_user_name VARCHAR(255) DEFAULT NULL AFTER source_user_id",
    "ADD COLUMN payment_amount DECIMAL(15,2) DEFAULT NULL AFTER amount",
    "ADD COLUMN rank_at_time VARCHAR(50) DEFAULT NULL AFTER commission_percentage",
    "ADD COLUMN period VARCHAR(20) DEFAULT NULL COMMENT 'Month/Year for batching e.g. 6/2026' AFTER rank_at_time",
];

foreach ($alterCols as $col) {
    try {
        $pdo->exec("ALTER TABLE mlm_commission_ledger $col");
        echo "  OK: $col\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "  SKIP (already exists): $col\n";
        } else {
            echo "  ERROR: $col â€” " . $e->getMessage() . "\n";
        }
    }
}

// 2. Add 'clawback' to commission_type ENUM
echo "\n--- 2. Adding 'clawback' to commission_type ENUM ---\n";
try {
    $pdo->exec("ALTER TABLE mlm_commission_ledger MODIFY COLUMN commission_type ENUM('referral','direct_sale','team_bonus','level_bonus','performance_bonus','special_reward','override','associate_referral','agent_referral','team_override','mlm_level_1','mlm_level_2','mlm_level_3','investment_sale','royalty_pool','clawback') DEFAULT NULL");
    echo "  OK: clawback added to ENUM\n";
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

// 3. Backfill existing override rows with source_user_name and period
echo "\n--- 3. Backfilling existing data ---\n";
try {
    $rows = $pdo->exec("UPDATE mlm_commission_ledger l
        JOIN users u ON l.source_user_id = u.id
        SET l.source_user_name = u.name
        WHERE l.source_user_name IS NULL");
    echo "  source_user_name backfilled: $rows rows\n";
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

try {
    $rows = $pdo->exec("UPDATE mlm_commission_ledger
        SET period = CONCAT(MONTH(created_at), '/', YEAR(created_at))
        WHERE period IS NULL");
    echo "  period backfilled: $rows rows\n";
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

// 4. Add unique index on mlm_network_tree.associate_id
echo "\n--- 4. Adding unique index on mlm_network_tree.associate_id ---\n";
try {
    $pdo->exec("ALTER TABLE mlm_network_tree ADD UNIQUE INDEX idx_unique_associate (associate_id)");
    echo "  OK: unique index added\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate') !== false) {
        echo "  WARN: Duplicate associate_id values exist. Cleaning duplicates first...\n";
        // Remove duplicates, keep the latest entry per associate_id
        $pdo->exec("DELETE t1 FROM mlm_network_tree t1
            INNER JOIN mlm_network_tree t2
            WHERE t1.associate_id = t2.associate_id
            AND t1.id < t2.id");
        $cleaned = $pdo->exec("ALTER TABLE mlm_network_tree ADD UNIQUE INDEX idx_unique_associate (associate_id)");
        echo "  OK: duplicates cleaned, unique index added\n";
    } else {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
}

// 5. Normalize current_level in mlm_profiles
echo "\n--- 5. Normalizing mlm_profiles.current_level ---\n";
$levelMap = [
    'assistant' => 'Ass.',
    'sr_assistant' => 'Sr. Ass.',
];
foreach ($levelMap as $old => $new) {
    $rows = $pdo->exec("UPDATE mlm_profiles SET current_level = '$new' WHERE current_level = '$old'");
    echo "  '$old' â†’ '$new': $rows rows updated\n";
}

// Also check associates.level ENUM
echo "\n--- 6. associates.level â€” current values ---\n";
$rows = $pdo->query("SELECT level, COUNT(*) as cnt FROM associates GROUP BY level")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "  '{$r['level']}' â†’ {$r['cnt']}\n";
}

// Verify
echo "\n=== VERIFICATION ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger")->fetchAll(PDO::FETCH_COLUMN);
foreach (['source_user_name', 'payment_amount', 'rank_at_time', 'period'] as $c) {
    echo "  $c: " . (in_array($c, $cols) ? 'EXISTS' : 'MISSING') . "\n";
}

$r = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger LIKE 'commission_type'")->fetch(PDO::FETCH_ASSOC);
$values = str_getcsv(preg_replace("/^enum\('(.*)'\)$/", "$1", $r['Type']));
echo "  commission_type ENUM has clawback: " . (in_array('clawback', $values) ? 'YES' : 'NO') . "\n";

$levels = $pdo->query("SELECT current_level, COUNT(*) as cnt FROM mlm_profiles GROUP BY current_level")->fetchAll(PDO::FETCH_ASSOC);
echo "  mlm_profiles current_level: ";
foreach ($levels as $l) {
    echo "'{$l['current_level']}' ({$l['cnt']}), ";
}
echo "\n";

$uniqueCheck = $pdo->query("SHOW INDEX FROM mlm_network_tree WHERE Column_name = 'associate_id' AND Non_unique = 0")->fetchAll();
echo "  mlm_network_tree unique on associate_id: " . (count($uniqueCheck) > 0 ? 'YES' : 'NO') . "\n";

echo "\n=== DONE ===\n";?>