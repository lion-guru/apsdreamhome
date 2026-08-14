<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

// Which users have MLM profiles with data?
echo "=== Users with MLM data ===\n";
$rows = $pdo->query('SELECT user_id, current_level, total_team_size, direct_referrals, lifetime_sales FROM mlm_profiles WHERE total_team_size > 0 OR direct_referrals > 0 OR lifetime_sales > 0 ORDER BY lifetime_sales DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "User {$r['user_id']}: level={$r['current_level']}, team={$r['total_team_size']}, directs={$r['direct_referrals']}, sales=â‚¹{$r['lifetime_sales']}\n";
}

// Which users have network_tree entries?
echo "\n=== Users with network_tree ===\n";
$rows = $pdo->query('SELECT parent_id, COUNT(*) as cnt FROM network_tree GROUP BY parent_id ORDER BY cnt DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "Parent {$r['parent_id']}: {$r['cnt']} children\n";
}

// Which users have commissions?
echo "\n=== Users with commissions ===\n";
$rows = $pdo->query('SELECT beneficiary_user_id, COUNT(*) as cnt, SUM(amount) as total FROM mlm_commission_ledger GROUP BY beneficiary_user_id ORDER BY total DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "User {$r['beneficiary_user_id']}: {$r['cnt']} entries, â‚¹{$r['total']}\n";
}

// Which users have plot bookings?
echo "\n=== Users with plot bookings ===\n";
$rows = $pdo->query('SELECT user_id, COUNT(*) as cnt, SUM(total_plot_value) as total FROM plot_bookings GROUP BY user_id ORDER BY total DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "User {$r['user_id']}: {$r['cnt']} bookings, â‚¹{$r['total']}\n";
}

// Users referred by someone
echo "\n=== Users with referrals ===\n";
$rows = $pdo->query('SELECT referred_by, COUNT(*) as cnt FROM users WHERE referred_by IS NOT NULL AND referred_by > 0 GROUP BY referred_by ORDER BY cnt DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "User {$r['referred_by']}: {$r['cnt']} referrals\n";
}?>