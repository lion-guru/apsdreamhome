<?php
// MLM Deep Audit Script â€” compares MLM_PLAN.md claims vs actual DB state
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== 1. mlm_network_tree SCHEMA ===\n";
$r = $pdo->query("SHOW CREATE TABLE mlm_network_tree")->fetch(PDO::FETCH_NUM);
echo $r[1] . "\n\n";

echo "=== 2. mlm_network_tree SAMPLE (10 rows) ===\n";
$rows = $pdo->query("SELECT * FROM mlm_network_tree LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    echo json_encode($row) . "\n";
}

echo "\n=== 3. mlm_network_tree TOTAL ROWS ===\n";
$count = $pdo->query("SELECT COUNT(*) FROM mlm_network_tree")->fetchColumn();
echo "Total: $count\n";

echo "\n=== 4. mlm_network_tree COLUMN NAMES ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM mlm_network_tree")->fetchAll(PDO::FETCH_COLUMN);
echo "Columns: " . implode(", ", $cols) . "\n";

echo "\n=== 5. mlm_rank_benefits SCHEMA ===\n";
$r = $pdo->query("SHOW CREATE TABLE mlm_rank_benefits")->fetch(PDO::FETCH_NUM);
echo $r[1] . "\n";
echo "\n=== 5b. mlm_rank_benefits (all rows) ===\n";
$rows = $pdo->query("SELECT * FROM mlm_rank_benefits ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo json_encode($r) . "\n";
}

echo "\n=== 6. mlm_commission_ledger SCHEMA ===\n";
$r = $pdo->query("SHOW CREATE TABLE mlm_commission_ledger")->fetch(PDO::FETCH_NUM);
echo $r[1] . "\n";
echo "\n=== 6b. mlm_commission_ledger SUMMARY BY TYPE ===\n";
$rows = $pdo->query("SELECT commission_type, COUNT(*) as cnt, SUM(amount) as total FROM mlm_commission_ledger GROUP BY commission_type ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "Type: {$r['commission_type']}, Count: {$r['cnt']}, Total: " . number_format($r['total'], 2) . "\n";
}

echo "\n=== 7. mlm_commission_ledger SUMMARY BY STATUS ===\n";
$rows = $pdo->query("SELECT status, COUNT(*) as cnt, SUM(amount) as total FROM mlm_commission_ledger GROUP BY status ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "Status: {$r['status']}, Count: {$r['cnt']}, Total: " . number_format($r['total'], 2) . "\n";
}

echo "\n=== 8. mlm_payout_batches ===\n";
$rows = $pdo->query("SELECT * FROM mlm_payout_batches ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo json_encode($r) . "\n";
}

echo "\n=== 9. mlm_profiles SCHEMA ===\n";
$r = $pdo->query("SHOW CREATE TABLE mlm_profiles")->fetch(PDO::FETCH_NUM);
echo $r[1] . "\n";
echo "\n=== 9b. mlm_profiles SUMMARY ===\n";
$rows = $pdo->query("SELECT * FROM mlm_profiles ORDER BY lifetime_sales DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo json_encode($r) . "\n";
}

echo "\n=== 10. investOR_levels SUMMARY ===\n";
try {
    $rows = $pdo->query("SELECT user_id, level_name, total_invested FROM investor_levels ORDER BY total_invested DESC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo "User {$r['user_id']}: Level={$r['level_name']}, Invested=" . number_format($r['total_invested']) . "\n";
    }
} catch (Exception $e) {
    echo "Table not found or error: " . $e->getMessage() . "\n";
}

echo "\n=== 11. hybrid_commission_records (if exists) ===\n";
try {
    $count = $pdo->query("SELECT COUNT(*) FROM hybrid_commission_records")->fetchColumn();
    echo "hybrid_commission_records rows: $count\n";
} catch (Exception $e) {
    echo "Table not found: " . $e->getMessage() . "\n";
}

echo "\n=== 12. investments SUMMARY ===\n";
try {
    $rows = $pdo->query("SELECT user_id, plan_id, principal_amount, status, company_contribution FROM investments ORDER BY principal_amount DESC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo "User {$r['user_id']}: Plan={$r['plan_id']}, Amount=" . number_format($r['principal_amount']) . ", Status={$r['status']}, Company=" . number_format($r['company_contribution'] ?? 0) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== 13. commission_plans (if exists) ===\n";
try {
    $rows = $pdo->query("SELECT * FROM commission_plans")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo json_encode($r) . "\n";
    }
} catch (Exception $e) {
    echo "Table not found: " . $e->getMessage() . "\n";
}

echo "\n=== 14. HybridCommissionEngine methods ===\n";
$engineFile = file_get_contents(__DIR__ . '/../app/Services/HybridCommissionEngine.php');
preg_match_all('/public\s+function\s+(\w+)/', $engineFile, $matches);
echo "Public methods: " . implode(", ", $matches[1]) . "\n";

echo "\n=== 15. MLMCommissionEngine methods ===\n";
$mlmFile = file_get_contents(__DIR__ . '/../app/Services/MLM/MLMCommissionEngine.php');
preg_match_all('/public\s+function\s+(\w+)/', $mlmFile, $matches);
echo "Public methods: " . implode(", ", $matches[1]) . "\n";

echo "\n=== 16. CommissionManager routing ===\n";
$mgrFile = file_get_contents(__DIR__ . '/../app/Services/MLM/CommissionManager.php');
preg_match_all('/public\s+function\s+(\w+)/', $mgrFile, $matches);
echo "Public methods: " . implode(", ", $matches[1]) . "\n";

echo "\n=== 17. royalypool tables ===\n";
try {
    $count = $pdo->query("SELECT COUNT(*) FROM royalty_pool_contributions")->fetchColumn();
    echo "royalty_pool_contributions: $count rows\n";
} catch (Exception $e) {
    echo "royalty_pool_contributions: " . $e->getMessage() . "\n";
}
try {
    $count = $pdo->query("SELECT COUNT(*) FROM royalty_pool_distributions")->fetchColumn();
    echo "royalty_pool_distributions: $count rows\n";
} catch (Exception $e) {
    echo "royalty_pool_distributions: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n";?>