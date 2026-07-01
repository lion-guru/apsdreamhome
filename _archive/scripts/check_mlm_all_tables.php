<?php
require_once __DIR__ . '/../app/Core/autoload.php';

$db = \App\Core\Database\Database::getInstance();

$tables = [
    'mlm_rank_benefits',
    'mlm_commission_levels',
    'mlm_payouts',
    'mlm_payout_batches',
    'mlm_rank_history',
    'mlm_cron_log',
    'mlm_clawback_log',
    'mlm_commission_analytics',
    'mlm_network_tree',
    'mlm_profiles',
    'associates',
    'user_wallets',
    'mlm_commission_ledger',
];

foreach ($tables as $t) {
    echo "=== $t ===\n";
    try {
        $cols = $db->fetchAll("SHOW COLUMNS FROM $t");
        foreach ($cols as $c) {
            echo "  {$c['Field']}: {$c['Type']}\n";
        }
    } catch (\Throwable $e) {
        echo "  TABLE NOT FOUND: " . $e->getMessage() . "\n";
    }
    echo "\n";
}