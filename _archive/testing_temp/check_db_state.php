<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== ASSOCIATES (level, user_id) ===\n";
$stmt = $pdo->query('SELECT a.id, a.user_id, u.name, a.level, a.status FROM associates a JOIN users u ON u.id = a.user_id ORDER BY a.id');
while ($r = $stmt->fetch()) echo "  assoc_id={$r['id']} user_id={$r['user_id']} name={$r['name']} level={$r['level']}\n";

echo "\n=== USERS referred_by (who has chain) ===\n";
$stmt = $pdo->query('SELECT id, name, role, referred_by FROM users WHERE referred_by IS NOT NULL AND referred_by > 0 ORDER BY id');
while ($r = $stmt->fetch()) echo "  user={$r['id']} name={$r['name']} role={$r['role']} referred_by={$r['referred_by']}\n";

echo "\n=== BOOKINGS (associate_id) ===\n";
$stmt = $pdo->query('SELECT id, plot_id, customer_id, associate_id, status, total_amount FROM plot_bookings WHERE status IN ("emi_active","token_paid") ORDER BY id');
while ($r = $stmt->fetch()) echo "  id={$r['id']} plot={$r['plot_id']} customer={$r['customer_id']} assoc={$r['associate_id']} status={$r['status']} amount={$r['total_amount']}\n";

echo "\n=== RANK RATES ===\n";
$stmt = $pdo->query('SELECT rank_name, rank_order, direct_sale_pct FROM mlm_rank_benefits ORDER BY rank_order');
while ($r = $stmt->fetch()) echo "  {$r['rank_name']}: direct={$r['direct_sale_pct']}%\n";

echo "\n=== MLM SETTINGS ===\n";
$stmt = $pdo->query('SELECT setting_key, setting_value FROM mlm_settings');
while ($r = $stmt->fetch()) echo "  {$r['setting_key']}={$r['setting_value']}\n";?>