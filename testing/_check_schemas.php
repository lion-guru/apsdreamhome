<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO('mysql:host='.$config['host'].';port='.$config['port'].';dbname='.$config['database'], $config['username'], $config['password']);

$tables = ['mlm_rank_bonuses', 'mlm_generation_commissions', 'mlm_infinity_overrides', 'mlm_matching_bonuses', 'mlm_qualification_log'];
foreach ($tables as $t) {
    echo "=== $t ===" . PHP_EOL;
    try {
        $row = $pdo->query("SHOW CREATE TABLE `$t`")->fetch(PDO::FETCH_NUM);
        echo $row[1] . PHP_EOL . PHP_EOL;
    } catch (Exception $e) {
        echo "TABLE NOT FOUND: " . $e->getMessage() . PHP_EOL . PHP_EOL;
    }
}

echo "=== mlm_commission_ledger ENUM values ===" . PHP_EOL;
$row = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger WHERE Field='commission_type'")->fetch(PDO::FETCH_ASSOC);
echo $row['Type'] . PHP_EOL . PHP_EOL;

echo "=== mlm_settings current state ===" . PHP_EOL;
foreach ($pdo->query("SELECT setting_key, LEFT(setting_value, 80) as val FROM mlm_settings ORDER BY id") as $r) {
    echo $r['setting_key'] . ' = ' . $r['val'] . PHP_EOL;
}
