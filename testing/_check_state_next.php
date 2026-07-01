<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// 1. Commission ledger types
echo "=== COMMISSION LEDGER TYPES ===\n";
$r = $pdo->query('SELECT commission_type, COUNT(*) as cnt, SUM(CAST(amount AS DECIMAL(15,2))) as total FROM mlm_commission_ledger GROUP BY commission_type ORDER BY total DESC');
while($row = $r->fetch(PDO::FETCH_ASSOC)) echo "  {$row['commission_type']}: {$row['cnt']} entries, Rs" . number_format($row['total']) . "\n";

$r = $pdo->query('SELECT COUNT(*) as cnt, SUM(CAST(amount AS DECIMAL(15,2))) as total FROM mlm_commission_ledger');
$t = $r->fetch(PDO::FETCH_ASSOC);
echo "  TOTAL: {$t['cnt']} entries, Rs" . number_format($t['total']) . "\n";

// 2. commission_type ENUM
$r = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger LIKE 'commission_type'");
$col = $r->fetch(PDO::FETCH_ASSOC);
echo "\ncommission_type ENUM: {$col['Type']}\n";

// 3. MLM Settings
echo "\n=== MLM SETTINGS ===\n";
$r = $pdo->query('SELECT setting_key, setting_value FROM mlm_settings ORDER BY setting_key');
while($row = $r->fetch(PDO::FETCH_ASSOC)) echo "  {$row['setting_key']} = {$row['setting_value']}\n";

// 4. Deep SM test users
echo "\n=== DEEP SM TEST USERS ===\n";
$r = $pdo->query('SELECT u.id, u.name, u.referred_by, a.id as assoc_id, a.level FROM users u LEFT JOIN associates a ON a.user_id = u.id WHERE u.id BETWEEN 2106 AND 2112 ORDER BY u.id');
while($row = $r->fetch(PDO::FETCH_ASSOC)) {
    echo "  User {$row['id']} ({$row['name']}) referred_by={$row['referred_by']} assoc_id={$row['assoc_id']} level={$row['level']}\n";
}

// 5. Network tree for Deep SM
echo "\n=== NETWORK TREE FOR DEEP SM ===\n";
$r = $pdo->query('SELECT nt.associate_id, nt.parent_id, nt.level as tree_level FROM mlm_network_tree nt WHERE nt.associate_id IN (SELECT a.id FROM associates a WHERE a.user_id BETWEEN 2106 AND 2112)');
$ntRows = $r->fetchAll();
echo "  Count: " . count($ntRows) . "\n";
foreach($ntRows as $row) echo "  assoc_id={$row['associate_id']} parent={$row['parent_id']} level={$row['tree_level']}\n";

// 6. All associate levels and counts
echo "\n=== ALL ASSOCIATE LEVELS ===\n";
$r = $pdo->query('SELECT level, COUNT(*) as cnt FROM associates GROUP BY level ORDER BY FIELD(level, "associate","senior_associate","bdm","sr_bdm","vice_president","president","site_manager")');
while($row = $r->fetch(PDO::FETCH_ASSOC)) echo "  {$row['level']}: {$row['cnt']}\n";

// 7. Royalty pool
echo "\n=== ROYALTY POOL ===\n";
$r = $pdo->query('SELECT * FROM mlm_royalty_pool');
$rp = $r->fetch(PDO::FETCH_ASSOC);
if($rp) {
    foreach($rp as $k => $v) echo "  {$k} = {$v}\n";
}

// 8. mlm_commission_plans
echo "\n=== COMMISSION PLANS ===\n";
$r = $pdo->query('SELECT id, plan_name, status FROM mlm_commission_plans');
while($row = $r->fetch(PDO::FETCH_ASSOC)) echo "  {$row['id']}: {$row['plan_name']} ({$row['status']})\n";

// 9. Booking 99947 ledger entries
echo "\n=== BOOKING 99947 LEDGER ===\n";
$r = $pdo->query('SELECT id, beneficiary_user_id, commission_type, amount, status, notes FROM mlm_commission_ledger WHERE booking_id = 99947 ORDER BY id');
while($row = $r->fetch(PDO::FETCH_ASSOC)) echo "  {$row['id']}: user={$row['beneficiary_user_id']} {$row['commission_type']} Rs" . number_format($row['amount']) . " {$row['status']}\n";

// 10. Check existing tables that might relate
echo "\n=== RELEVANT TABLES ===\n";
$tables = ['mlm_royalty_pool', 'mlm_royalty_pool_entries', 'mlm_generation_commissions', 'mlm_infinity_overrides', 'mlm_matching_bonuses', 'mlm_rank_bonuses', 'mlm_qualification_log'];
foreach($tables as $tbl) {
    $r = $pdo->query("SHOW TABLES LIKE '{$tbl}'");
    $exists = $r->fetch() ? 'YES' : 'no';
    echo "  {$tbl}: {$exists}\n";
}

// 11. Rank benefits
echo "\n=== RANK BENEFITS ===\n";
$r = $pdo->query('SELECT rank_name, direct_sale_pct, l1_pct, l2_pct, l3_pct FROM mlm_rank_benefits ORDER BY direct_sale_pct');
while($row = $r->fetch(PDO::FETCH_ASSOC)) echo "  {$row['rank_name']}: direct={$row['direct_sale_pct']}% L1={$row['l1_pct']}% L2={$row['l2_pct']}% L3={$row['l3_pct']}%\n";
