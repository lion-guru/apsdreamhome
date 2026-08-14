<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');

echo "=== Distinct current_level values ===\n";
$rows = $pdo->query("SELECT current_level, COUNT(*) as cnt FROM mlm_profiles GROUP BY current_level ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "  '{$r['current_level']}' â†’ {$r['cnt']} profiles\n";
}

echo "\n=== mlm_commission_ledger missing columns ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger")->fetchAll(PDO::FETCH_COLUMN);
$check = ['source_user_name', 'payment_amount', 'rank_at_time', 'period'];
foreach ($check as $c) {
    echo "  $c: " . (in_array($c, $cols) ? 'EXISTS' : 'MISSING') . "\n";
}

echo "\n=== commission_type ENUM values ===\n";
$r = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger LIKE 'commission_type'")->fetch(PDO::FETCH_ASSOC);
echo "  " . $rows = preg_replace("/^enum\('(.*)'\)$/", "$1", $r['Type']) . "\n";

echo "\n=== Does clawback exist in ENUM? ===\n";
$r = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger LIKE 'commission_type'")->fetch(PDO::FETCH_ASSOC);
$values = str_getcsv($r['Type']);
echo "  Values: " . implode(", ", $values) . "\n";
echo "  Has 'clawback': " . (in_array('clawback', $values) ? 'YES' : 'NO') . "\n";

echo "\n=== mlm_network_tree: is associate_id unique? ===\n";
$idx = $pdo->query("SHOW INDEX FROM mlm_network_tree WHERE Column_name = 'associate_id' AND Non_unique = 0")->fetchAll();
echo "  Unique on associate_id: " . (count($idx) > 0 ? 'YES' : 'NO') . "\n";

echo "\n=== associates.level column ===\n";
try {
    $cols = $pdo->query("SHOW COLUMNS FROM associates LIKE 'level'")->fetchAll();
    if (count($cols) > 0) {
        echo "  EXISTS: " . $cols[0]['Type'] . "\n";
        $rows = $pdo->query("SELECT level, COUNT(*) as cnt FROM associates GROUP BY level")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            echo "    '{$r['level']}' â†’ {$r['cnt']}\n";
        }
    } else {
        echo "  NOT EXISTS\n";
    }
} catch (Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\n=== How many total associates vs users ===\n";
echo "  users with role=associate: " . $pdo->query("SELECT COUNT(*) FROM users WHERE role='associate'")->fetchColumn() . "\n";
echo "  associates table rows: " . $pdo->query("SELECT COUNT(*) FROM associates")->fetchColumn() . "\n";
echo "  mlm_profiles with user_type=associate: " . $pdo->query("SELECT COUNT(*) FROM mlm_profiles WHERE user_type='associate'")->fetchColumn() . "\n";

echo "\n=== mlm_commission_ledger: override entries breakdown ===\n";
$rows = $pdo->query("SELECT beneficiary_user_id, source_user_id, amount, commission_percentage, property_id, booking_id, status, created_at FROM mlm_commission_ledger WHERE commission_type = 'override' ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "  beneficiary={$r['beneficiary_user_id']}, source={$r['source_user_id']}, amount=" . number_format($r['amount']) . ", pct={$r['commission_percentage']}%, status={$r['status']}\n";
}

echo "\n=== How are override entries created? ===\n";
$engineFile = file_get_contents(__DIR__ . '/../app/Services/HybridCommissionEngine.php');
preg_match_all('/commission_type.*override|override.*commission_type|writeLedger.*override/', $engineFile, $matches);
echo "  override refs in HybridCommissionEngine: " . count($matches[0]) . "\n";
$mlmFile = file_get_contents(__DIR__ . '/../app/Services/MLM/MLMCommissionEngine.php');
preg_match_all('/override/', $mlmFile, $matches2);
echo "  override refs in MLMCommissionEngine: " . count($matches2[0]) . "\n";

echo "\n=== DONE ===\n";?>