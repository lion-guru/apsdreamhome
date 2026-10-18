<?php
/**
 * Converge referral_rewards schema (Session 264).
 * Idempotent: every step runs ONLY on mismatch. Safe to re-run after any restore.
 *
 * Background: the live table (8 cols, status pending/approved/paid/cancelled)
 * diverged from create_wallet_system.php (12 cols, status
 * pending/credited/expired/reversed). Result: all three writers
 * (ResellTransactionService, QuickAuthController, GoogleAuthController) died
 * with 1054 on every INSERT, and both WalletController readers
 * (status='credited') could never match. The table sits at 0 rows, so
 * convergence is widen-only and data-safe. Union keeps BOTH worlds working:
 * fresh installs (migration shape) and live (this patch).
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();
$abort = function ($m) { echo "ABORT: $m\n"; exit(1); };

$exists = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'referral_rewards'")->fetchColumn();
if (!$exists) {
    echo "SKIP referral_rewards (table absent — fresh-install migration owns it)\nDone.\n";
    return;
}

$cols = [];
foreach ($pdo->query("SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'referral_rewards'")->fetchAll(PDO::FETCH_ASSOC) as $c) {
    $cols[$c['COLUMN_NAME']] = $c;
}
$need = function ($name) use ($cols) { return !isset($cols[$name]); };

if ($need('reward_percentage')) {
    $pdo->exec("ALTER TABLE referral_rewards ADD COLUMN reward_percentage DECIMAL(5,2) DEFAULT 0");
    echo "FIXED +reward_percentage\n";
} else echo "SKIP reward_percentage\n";

if ($need('referral_code')) {
    $pdo->exec("ALTER TABLE referral_rewards ADD COLUMN referral_code VARCHAR(20) NULL");
    echo "FIXED +referral_code\n";
} else echo "SKIP referral_code\n";

if ($need('credited_at')) {
    $pdo->exec("ALTER TABLE referral_rewards ADD COLUMN credited_at TIMESTAMP NULL");
    echo "FIXED +credited_at\n";
} else echo "SKIP credited_at\n";

if ($need('expires_at')) {
    $pdo->exec("ALTER TABLE referral_rewards ADD COLUMN expires_at TIMESTAMP NULL");
    echo "FIXED +expires_at\n";
} else echo "SKIP expires_at\n";

// reward_type: ENUM would reject 'wallet_points' — widen to VARCHAR (values preserved).
if (isset($cols['reward_type']) && stripos($cols['reward_type']['COLUMN_TYPE'], 'enum') !== false) {
    $pdo->exec("ALTER TABLE referral_rewards MODIFY COLUMN reward_type VARCHAR(50) NULL");
    echo "FIXED reward_type -> varchar\n";
} else echo "SKIP reward_type\n";

// status: union of both worlds (widen-only; every existing value is inside).
$union = "'pending','approved','paid','cancelled','credited','expired','reversed'";
$cur = isset($cols['status']) ? $cols['status']['COLUMN_TYPE'] : '';
$missing = [];
foreach (['pending','approved','paid','cancelled','credited','expired','reversed'] as $v) {
    if (strpos($cur, "'$v'") === false) $missing[] = $v;
}
if ($missing) {
    $pdo->exec("ALTER TABLE referral_rewards MODIFY COLUMN status ENUM($union) NULL DEFAULT 'pending'");
    echo "FIXED status union (added: " . implode(',', $missing) . ")\n";
} else echo "SKIP status\n";

echo "Done.\n";
