<?php
/**
 * Migration S523: Wallet consistency — remove NULL-owner ghost rows, enforce identity.
 *
 * Context (S523, owner instruction: "data sab testing/fake hai, jaise ho thik karo"):
 *   wallet_points had 76 rows, 37 of them with user_id IS NULL — junk seed rows with NO
 *   owner and NO FK. 3 of them carried phantom balances:
 *     id 40: 200.00 (referral_earnings 200.00, 2026-05-12)
 *     id 250: 5000.00 (2026-10-15)
 *     id 660: 5000.00 (2026-11-05, E2E-journey era)
 *   = 10,400.00 of "money owed to nobody" with ZERO matching rows in wallet_transactions,
 *   i.e. balance-vs-ledger double counting. The one row that matters
 *   (user_id=1, 600.00 = 3 x 200 referral credits) already reconciles exactly
 *   with wallet_transactions (3 rows, balance_after 200/400/600).
 *
 * Fix (idempotent):
 *   1. DELETE wallet_points WHERE user_id IS NULL.
 *      Pre-flight safety dump: backups/db/wallet_pre_s523_2026-11-14.sql
 *   2. wallet_points.user_id: MODIFY -> BIGINT UNSIGNED NOT NULL (matches users.id).
 *   3. Add UNIQUE KEY uniq_wp_tenant_user (tenant_id, user_id) if missing.
 *   4. Print reconciliation report (SUM(balance) == signed SUM(amount) expected: 600.00).
 *
 * Re-runs: step 1 deletes 0 rows, steps 2-3 are skipped by information_schema checks,
 * step 4 prints SKIP / reconciled as-is.
 */
require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = \App\Core\Database\Database::getInstance()->getConnection();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S523: Wallet consistency ===\n";
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

// Step 1: delete NULL-owner ghost rows.
$ghost = (int)$pdo->query("SELECT COUNT(*) FROM wallet_points WHERE user_id IS NULL")->fetchColumn();
if ($ghost > 0) {
    $phantom = $pdo->query("SELECT id, points_balance, total_earned FROM wallet_points WHERE user_id IS NULL AND points_balance > 0")
        ->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($phantom as $p) {
        echo "[S523-1] deleting ghost id={$p['id']} (balance {$p['points_balance']})\n";
    }
    $del = $pdo->prepare("DELETE FROM wallet_points WHERE user_id IS NULL");
    $del->execute();
    echo "[S523-1] DELETED {$ghost} ghost rows.\n";
} else {
    echo "[S523-1] SKIP: 0 ghost rows.\n";
}

// Step 1b: FK ON DELETE SET NULL -> RESTRICT.
// SET NULL is the root cause of the ghost rows: it orphaned a wallet (with balance)
// whenever its user was hard-deleted. RESTRICT makes that fail loudly. Users in this
// app are soft-deleted/anonymized by design (email -> deleted_*@deleted.com), which is
// exactly why the NULL-owner rows existed. Hard-delete paths in UserService::deleteUser
// and TeamManagementController::deleteMember will now throw for users WITH a wallet.
$deleteRule = (string)$pdo->query(
    "SELECT rc.DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS rc
     WHERE rc.CONSTRAINT_SCHEMA = DATABASE() AND rc.TABLE_NAME = 'wallet_points'
       AND rc.CONSTRAINT_NAME = 'fk_wallet_points_user_id'"
)->fetchColumn();
if (strtoupper((string)$deleteRule) === 'SET NULL') {
    $pdo->exec("ALTER TABLE wallet_points DROP FOREIGN KEY fk_wallet_points_user_id");
    $pdo->exec(
        "ALTER TABLE wallet_points
         ADD CONSTRAINT fk_wallet_points_user_id
         FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE RESTRICT ON UPDATE CASCADE"
    );
    echo "[S523-1b] fk_wallet_points_user_id: ON DELETE SET NULL -> RESTRICT.\n";
} else {
    echo "[S523-1b] SKIP: fk_wallet_points_user_id is already RESTRICT (or absent).\n";
}

// Step 2: user_id NOT NULL, UNSIGNED (match users.id).
$nullable = $pdo->query(
    "SELECT IS_NULLABLE FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'wallet_points' AND COLUMN_NAME = 'user_id'"
)->fetchColumn();
$type = (string)$pdo->query(
    "SELECT COLUMN_TYPE FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'wallet_points' AND COLUMN_NAME = 'user_id'"
)->fetchColumn();
if ($nullable === 'YES' || stripos((string)$type, 'unsigned') === false) {
    $pdo->exec("ALTER TABLE wallet_points MODIFY user_id bigint(20) unsigned NOT NULL");
    echo "[S523-2] wallet_points.user_id -> bigint(20) unsigned NOT NULL.\n";
} else {
    echo "[S523-2] SKIP: user_id already bigint UNSIGNED NOT NULL.\n";
}

// Step 3: UNIQUE (tenant_id, user_id).
$hasUnique = (int)$pdo->query(
    "SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'wallet_points' AND INDEX_NAME = 'uniq_wp_tenant_user'"
)->fetchColumn() > 0;
if (!$hasUnique) {
    $pdo->exec("ALTER TABLE wallet_points ADD UNIQUE KEY uniq_wp_tenant_user (tenant_id, user_id)");
    echo "[S523-3] added UNIQUE KEY uniq_wp_tenant_user (tenant_id, user_id).\n";
} else {
    echo "[S523-3] SKIP: uniq_wp_tenant_user exists.\n";
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

// Step 4: reconciliation report.
$balance = (float)$pdo->query("SELECT COALESCE(SUM(points_balance),0) FROM wallet_points")->fetchColumn();
$earned  = (float)$pdo->query("SELECT COALESCE(SUM(total_earned),0) FROM wallet_points")->fetchColumn();
$ledger  = (float)$pdo->query(
    "SELECT COALESCE(SUM(CASE WHEN transaction_type='credit' THEN amount ELSE -amount END),0)
     FROM wallet_transactions"
)->fetchColumn();
$rows    = (int)$pdo->query("SELECT COUNT(*) FROM wallet_points")->fetchColumn();
echo "[S523-4] reconciliation: rows={$rows} total_earned={$earned} sum(points_balance)={$balance} ledger_signed_sum={$ledger} ";
echo (abs($balance - $ledger) < 0.001 ? "RECONCILED" : "DRIFT!") . "\n";

echo "=== S523 migration complete ===\n";
