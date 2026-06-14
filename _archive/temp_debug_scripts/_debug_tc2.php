<?php
$cfg = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO(
    'mysql:host=' . $cfg['host'] . ';port=' . $cfg['port'] . ';dbname=' . $cfg['database'] . ';charset=utf8mb4',
    $cfg['username'], $cfg['password']
);

echo "=== LEADS ===" . PHP_EOL;
$rows = $pdo->query("SELECT id, name, email, phone, assigned_to FROM leads WHERE email LIKE '%test_cust%' OR phone = '9876543210'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  id={$r['id']} email={$r['email']} phone={$r['phone']} assigned_to={$r['assigned_to']}" . PHP_EOL;

echo "\n=== USERS ===" . PHP_EOL;
$rows = $pdo->query("SELECT id, name, role FROM users WHERE id IN (1001,1002,1003,1004)")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  id={$r['id']} name={$r['name']} role={$r['role']}" . PHP_EOL;

echo "\n=== ASSOCIATES ===" . PHP_EOL;
$rows = $pdo->query("SELECT user_id, agent_track, telecaller_parent_id FROM associates WHERE user_id IN (1001,1002,1003,1004)")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  uid={$r['user_id']} track={$r['agent_track']} parent={$r['telecaller_parent_id']}" . PHP_EOL;

echo "\n=== LEDGER (telecaller related) ===" . PHP_EOL;
$rows = $pdo->query("SELECT beneficiary_user_id, amount, commission_type, level, notes FROM mlm_commission_ledger WHERE notes LIKE '%Telecaller%' OR notes LIKE '%Override%' ORDER BY id DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  bid={$r['beneficiary_user_id']} amt={$r['amount']} type={$r['commission_type']} lvl={$r['level']} notes={$r['notes']}" . PHP_EOL;

echo "\n=== ALL LEDGER for users 1002/1003 ===" . PHP_EOL;
$rows = $pdo->query("SELECT beneficiary_user_id, amount, commission_type, level, notes FROM mlm_commission_ledger WHERE beneficiary_user_id IN (1002,1003) ORDER BY id DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  bid={$r['beneficiary_user_id']} amt={$r['amount']} type={$r['commission_type']} lvl={$r['level']} notes={$r['notes']}" . PHP_EOL;
