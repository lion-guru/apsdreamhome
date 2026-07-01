<?php
require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';
$db = \App\Core\Database\Database::getInstance();

$tables = ['mlm_profiles', 'mlm_network_tree', 'associates', 'mlm_commission_ledger', 'booking_commissions', 'user_wallets', 'mlm_rank_benefits', 'mlm_commission_levels'];
foreach ($tables as $t) {
    echo "=== $t ===\n";
    $cols = $db->fetchAll("SHOW COLUMNS FROM $t");
    foreach ($cols as $c) {
        echo "  {$c['Field']}: {$c['Type']}\n";
    }
    echo "\n";
}