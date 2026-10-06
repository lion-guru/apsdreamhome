<?php
/**
 * Migrate wallet_activation_packages: flat referral_reward -> pct L1/L2.
 * Run once: php scripts/migrate_wallet_pct.php
 */
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $cols = array_column($conn->query('DESCRIBE wallet_activation_packages')->fetchAll(PDO::FETCH_ASSOC), 'Field');

    if (!in_array('referral_pct_l1', $cols, true)) {
        $conn->exec('ALTER TABLE wallet_activation_packages ADD COLUMN referral_pct_l1 DECIMAL(5,2) NOT NULL DEFAULT 20.00 AFTER referral_reward');
        echo "added referral_pct_l1\n";
    }
    if (!in_array('referral_pct_l2', $cols, true)) {
        $conn->exec('ALTER TABLE wallet_activation_packages ADD COLUMN referral_pct_l2 DECIMAL(5,2) NOT NULL DEFAULT 5.00 AFTER referral_pct_l1');
        echo "added referral_pct_l2\n";
    }

    // Backfill uniform 20/5 (only where still default/zero)
    $conn->exec('UPDATE wallet_activation_packages SET referral_pct_l1 = 20.00 WHERE referral_pct_l1 = 0');
    $conn->exec('UPDATE wallet_activation_packages SET referral_pct_l2 = 5.00 WHERE referral_pct_l2 = 0');
    echo "backfilled 20/5\n";

    if (in_array('referral_reward', $cols, true)) {
        $conn->exec('ALTER TABLE wallet_activation_packages DROP COLUMN referral_reward');
        echo "dropped flat referral_reward\n";
    }

    foreach ($conn->query('SELECT slug, price, referral_pct_l1, referral_pct_l2 FROM wallet_activation_packages ORDER BY sort_order')->fetchAll(PDO::FETCH_ASSOC) as $p) {
        echo sprintf("  %s: price=%s l1=%s%% l2=%s%%\n", $p['slug'], $p['price'], $p['referral_pct_l1'], $p['referral_pct_l2']);
    }
    echo "OK\n";
} catch (Throwable $e) {
    echo 'FAIL: ' . $e->getMessage() . "\n";
    exit(1);
}
