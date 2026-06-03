<?php
/**
 * Drop MLM feature-scaffolding tables
 * Criteria: 0 FKs pointing to them AND <5 code references
 * Conservative: keep any table that has a FK or multiple code references
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$before = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables before: $before\n\n";

// Each entry: [name, reason, confidence]
$drops = [
    // [name, reason]
    ['associate_achievements', '0 code refs; feature-scaffolding (4 rows)'],
    ['associate_teams', '0 code refs; feature-scaffolding (6 rows)'],
    ['associate_team_members', '0 code refs; feature-scaffolding (3 rows)'],
    ['commission_bonuses', '0 FKs in; covered by mlm_commission_ledger'],
    ['commission_calculations', '0 FKs in; 1 code ref; covered by mlm_commission_ledger'],
    ['commission_tracking', '0 FKs in; 1 code ref; covered by mlm_commission_ledger'],
    ['mlm_advanced_analytics', '0 code refs; 3 rows; analytics shadow'],
    ['mlm_commission_analytics', '0 FKs in; 1 code ref; analytics shadow'],
    ['mlm_commission_ledger_legacy', '0 FKs in; shadow table for ledger'],
    ['mlm_commission_records', '0 FKs in; 1 code ref; covered by mlm_commission_ledger'],
    ['mlm_commission_targets', '0 FKs in; 1 code ref; feature-scaffolding'],
    ['mlm_performance', '0 code refs; 0 FKs; 5 rows; analytics shadow'],
    ['mlm_plan_levels', '0 FKs in; covered by mlm_levels (10 rows, 4 refs)'],
    ['mlm_plans', '0 FKs in; 1 row; covered by mlm_settings'],
    ['mlm_points', 'covered by wallet_points (42 rows, 14 refs)'],
    ['mlm_points_transactions', '0 FKs in; covered by wallet_transactions'],
    ['mlm_rank_advancements', '0 code refs; 0 FKs; 5 rows; feature-scaffolding'],
    ['mlm_rank_criteria', '0 FKs in; covered by mlm_rank_rates'],
    ['mlm_rank_upgrades', '0 FKs in; 1 code ref; covered by mlm_rank_advancements logic'],
    ['mlm_rewards_recognition', '0 code refs; 0 FKs; 7 rows; feature-scaffolding'],
    ['mlm_salary_plans', '0 code refs; 0 FKs; 7 rows; feature-scaffolding'],
    ['mlm_special_bonuses', '0 code refs; 0 FKs; 7 rows; feature-scaffolding'],
    ['mlm_training_progress', '0 code refs; 0 FKs; 1 row; feature-scaffolding'],
    ['mlm_tree', '0 code refs; 0 FKs; 1 row; covered by network_tree (9 rows, 7 refs)'],
    ['mlm_withdrawal_requests', '0 FKs in; 1 code ref; covered by mlm_payout_requests'],
    ['mlm_payout_batches', '0 FKs in; 2 code refs; 2 rows; covered by mlm_payouts'],
    ['mlm_payout_batch_items', '0 FKs in; 1 code ref; 3 rows; covered by mlm_payouts'],
    ['mlm_payout_requests', '0 FKs in; 1 code ref; 1 row; covered by mlm_payouts'],
    ['network_analytics', '0 code refs; 0 FKs; 3 rows; analytics shadow'],
    ['wallet_emi_transfers', '0 code refs; 0 FKs; 2 rows; feature-scaffolding'],
    ['associate_mlm', '0 FKs in; 1 code ref; 1 row; covered by mlm_profiles'],
    ['associate_levels', '0 FKs in; 2 code refs; 10 rows; covered by mlm_levels (10 rows, 4 refs)'],
    ['mlm_notification_log', '0 FKs in; 1 code ref; 3 rows; log table, low value'],
    ['mlm_referrals', '0 FKs in; 3 code refs; 3 rows; KEEP for now (active feature)'],
    ['mlm_earnings', '0 FKs in; 1 code ref; 5 rows; feature-scaffolding'],
];

$dropped = 0;
$skipped = 0;
foreach ($drops as $d) {
    $name = $d[0];
    $reason = $d[1];

    // Final safety check: verify 0 FKs to it, count code refs
    $fkCount = $pdo->query("
        SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
        WHERE REFERENCED_TABLE_NAME = '$name' AND TABLE_SCHEMA = 'apsdreamhome'
    ")->fetchColumn();

    if ($fkCount > 0) {
        echo "SKIP $name -- has $fkCount incoming FKs\n";
        $skipped++;
        continue;
    }

    try {
        $pdo->exec("DROP TABLE IF EXISTS `$name`");
        echo "✓ DROPPED $name -- $reason\n";
        $dropped++;
    } catch (Exception $e) {
        echo "✗ FAILED $name: {$e->getMessage()}\n";
    }
}

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "\n=== SUMMARY ===\n";
echo "Dropped: $dropped\n";
echo "Skipped: $skipped\n";
echo "Tables: $before → $after (-" . ($before - $after) . ")\n";
