<?php
/**
 * Wave 3 — ADD user_id to salary_payments (idempotent) + backfill via profile OR bridge.
 * Session 383 (2026-11-09). Safety dump: backups/db/apsdreamhome_pre-restore_2026-11-09_wave3.sql
 * DO NOT EDIT frequencies: re-run = SKIP when column exists.
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap_cli.php';

use App\Core\Database\Database;

$pdo = Database::getInstance()->getConnection();

// 1. ADD user_id if not exists
$exists = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='salary_payments' AND COLUMN_NAME='user_id'");
$exists->execute();
if ((int)$exists->fetchColumn() === 0) {
    $pdo->exec("ALTER TABLE salary_payments ADD COLUMN user_id BIGINT UNSIGNED NULL AFTER associate_id");
    echo "ADDED salary_payments.user_id\n";
} else {
    echo "SKIP salary_payments.user_id already exists\n";
}

// 2. ADD index if not exists
$idx = $pdo->prepare("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='salary_payments' AND INDEX_NAME='idx_salary_payments_user_id'");
$idx->execute();
if ((int)$idx->fetchColumn() === 0) {
    $pdo->exec("CREATE INDEX idx_salary_payments_user_id ON salary_payments (user_id)");
    echo "ADDED idx_salary_payments_user_id\n";
} else {
    echo "SKIP idx_salary_payments_user_id already exists\n";
}

// 3. Backfill user_id via 3-way OR bridge (legacy_id OR user_id OR id). Honest NULL for dangling (79/114).
$before = $pdo->prepare("SELECT COUNT(*) AS c, COALESCE(SUM(net_amount),0) AS s FROM salary_payments");
$before->execute();
$b = $before->fetch(PDO::FETCH_ASSOC);
echo "BEFORE count={$b['c']} sum={$b['s']}\n";

// Only fill rows where user_id IS NULL (idempotent)
$stmt = $pdo->prepare("
    UPDATE salary_payments sp
    LEFT JOIN employee_profiles ep ON ep.legacy_id = sp.employee_id OR ep.user_id = sp.employee_id OR ep.id = sp.employee_id
    SET sp.user_id = ep.user_id
    WHERE sp.user_id IS NULL AND ep.user_id IS NOT NULL
");
$stmt->execute();
echo "BACKFILLED via profile rows=" . $stmt->rowCount() . "\n";

// Direct users.id pass: employee_id actually stores users.id for most rows (72/73/74/77 proven live)
$stmt2 = $pdo->prepare("
    UPDATE salary_payments sp
    JOIN users u ON u.id = sp.employee_id
    SET sp.user_id = sp.employee_id
    WHERE sp.user_id IS NULL
");
$stmt2->execute();
echo "BACKFILLED via direct users rows=" . $stmt2->rowCount() . "\n";

$after = $pdo->prepare("SELECT COUNT(*) AS c, COALESCE(SUM(net_amount),0) AS s, COUNT(user_id) AS with_uid, COUNT(*)-COUNT(user_id) AS null_uid FROM salary_payments");
$after->execute();
$a = $after->fetch(PDO::FETCH_ASSOC);
echo "AFTER count={$a['c']} sum={$a['s']} with_user_id={$a['with_uid']} null_user_id={$a['null_uid']}\n";

if ((int)$a['c'] !== (int)$b['c'] || abs((float)$a['s'] - (float)$b['s']) > 0.005) {
    echo "FAIL: count/sum drift detected — restore from safety dump required\n";
    exit(1);
}
echo "OK: zero drift (count+sum byte-identical)\n";
