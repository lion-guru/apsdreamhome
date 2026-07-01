<?php
require_once __DIR__ . '/vendor/autoload.php';

$pdo = \App\Core\Database\Database::getInstance()->getPdo();

echo "=== mlm_commission_ledger Table Structure ===\n\n";
$rows = $pdo->query("SHOW CREATE TABLE mlm_commission_ledger")->fetchAll(PDO::FETCH_ASSOC);
echo $rows[0]['Create Table'] . "\n\n";
