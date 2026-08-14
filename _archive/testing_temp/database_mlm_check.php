<?php
require_once dirname(__DIR__) . '/app/Core/ConfigService.php';
require_once dirname(__DIR__) . '/app/Core/Database/Database.php';

try {
    $dbInstance = \App\Core\Database\Database::getInstance();
    $db = $dbInstance->getConnection();

    echo "--- DATABASE MLM TABLES & COUNTS ---\n";
    $tables = [
        'mlm_settings',
        'mlm_rank_benefits',
        'mlm_levels',
        'mlm_commission_ledger',
        'mlm_network_tree',
        'associates',
        'plot_bookings',
        'booking_payment_schedules',
        'mlm_matching_bonuses',
        'mlm_generation_bonuses',
        'mlm_royalty_pool_claims',
        'mlm_payouts'
    ];

    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM `$table`");
            $count = $stmt->fetchColumn();
            echo "$table: $count rows\n";
        } catch (Exception $e) {
            echo "$table: ERROR - " . $e->getMessage() . "\n";
        }
    }

    echo "\n--- MLM SETTINGS ---\n";
    try {
        $stmt = $db->query("SELECT setting_key, setting_value FROM mlm_settings");
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($settings as $s) {
            echo "  {$s['setting_key']} = {$s['setting_value']}\n";
        }
    } catch (Exception $e) {
        echo "Error reading mlm_settings: " . $e->getMessage() . "\n";
    }

    echo "\n--- MLM COMMISSION LEDGER SUMMARY ---\n";
    try {
        $stmt = $db->query("SELECT commission_type, COUNT(*) as count, SUM(amount) as total FROM mlm_commission_ledger GROUP BY commission_type");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalCommission = 0;
        foreach ($rows as $r) {
            echo "  Type: " . str_pad($r['commission_type'], 25) . " | Count: " . str_pad($r['count'], 5) . " | Total: â‚¹" . number_format($r['total'], 2) . "\n";
            $totalCommission += $r['total'];
        }
        echo "  TOTAL COMMISSION DISTRIBUTED: â‚¹" . number_format($totalCommission, 2) . "\n";
    } catch (Exception $e) {
        echo "Error reading mlm_commission_ledger: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}?>