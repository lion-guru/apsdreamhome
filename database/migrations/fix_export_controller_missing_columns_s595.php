<?php
/**
 * Restore missing columns used by Associate\ExportController CSV exports.
 *
 * ExportController wraps fetchAll() in try/catch (e.g. myPayouts line 123-128,
 * downline line 178-180, plotSales line 244-246) and silently returns an empty
 * CSV when a column is missing. These failures never surface as HTTP 500 —
 * they only appear in php_error.log. This migration adds the missing columns
 * so the exports return real data.
 *
 * Idempotent: checks SHOW COLUMNS before each ALTER; re-runs skip safely.
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function columnExists($pdo, $table, $column): bool {
    $row = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$column'")->fetch();
    return $row !== false;
}

echo "== S595: fix export controller missing columns ==" . PHP_EOL;

// payouts: payout_percent, period, generated_on (used by myPayouts)
if (!columnExists($pdo, 'payouts', 'payout_percent')) {
    $pdo->exec("ALTER TABLE `payouts` ADD COLUMN `payout_percent` DECIMAL(5,2) DEFAULT 0.00 AFTER `amount`");
    echo "  added payouts.payout_percent" . PHP_EOL;
} else {
    echo "  skip payouts.payout_percent" . PHP_EOL;
}
if (!columnExists($pdo, 'payouts', 'period')) {
    $pdo->exec("ALTER TABLE `payouts` ADD COLUMN `period` VARCHAR(20) DEFAULT '' AFTER `payout_percent`");
    echo "  added payouts.period" . PHP_EOL;
} else {
    echo "  skip payouts.period" . PHP_EOL;
}
if (!columnExists($pdo, 'payouts', 'generated_on')) {
    $pdo->exec("ALTER TABLE `payouts` ADD COLUMN `generated_on` TIMESTAMP NULL DEFAULT NULL AFTER `period`");
    echo "  added payouts.generated_on" . PHP_EOL;
} else {
    echo "  skip payouts.generated_on" . PHP_EOL;
}

// users: parent_id (used by downline/newDirects)
if (!columnExists($pdo, 'users', 'parent_id')) {
    $pdo->exec("ALTER TABLE `users` ADD COLUMN `parent_id` BIGINT(20) UNSIGNED DEFAULT NULL AFTER `referred_by`");
    echo "  added users.parent_id" . PHP_EOL;
} else {
    echo "  skip users.parent_id" . PHP_EOL;
}

// user_properties: associate_id (used by plotSales)
if (!columnExists($pdo, 'user_properties', 'associate_id')) {
    $pdo->exec("ALTER TABLE `user_properties` ADD COLUMN `associate_id` BIGINT(20) UNSIGNED DEFAULT NULL AFTER `user_id`");
    echo "  added user_properties.associate_id" . PHP_EOL;
} else {
    echo "  skip user_properties.associate_id" . PHP_EOL;
}

echo "== S595 done ==" . PHP_EOL;
