<?php
/**
 * Add structured geo columns to leads (idempotent).
 * Nullable => instant on large tables, zero backfill needed.
 */
define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();
$have = $db->query("SHOW COLUMNS FROM leads")->fetchAll(PDO::FETCH_COLUMN);
$want = [
    'state_id' => 'INT NULL',
    'district_id' => 'INT NULL',
    'latitude' => 'DECIMAL(10,8) NULL',
    'longitude' => 'DECIMAL(11,8) NULL',
];
foreach ($want as $col => $def) {
    if (in_array($col, $have, true)) { echo "exists: $col\n"; continue; }
    $db->exec("ALTER TABLE leads ADD COLUMN $col $def");
    echo "added: $col\n";
}
foreach (['state_id', 'district_id'] as $col) {
    try { $db->exec("ALTER TABLE leads ADD INDEX idx_leads_$col ($col)"); echo "indexed: $col\n"; }
    catch (\Throwable $e) { echo "index exists: $col\n"; }
}
echo "done\n";