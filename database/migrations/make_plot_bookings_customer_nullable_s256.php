<?php
/**
 * Make plot_bookings.customer_id NULLable (Session 256).
 * Idempotent: ALTER runs ONLY when the column is still NOT NULL.
 *
 * Background: associate/counter bookings capture name+phone as TEXT (no user
 * account yet — S160's claim flow links them later). NOT NULL with no default
 * 1364'd EVERY such insert. NULL-customer bookings already exist in the wild
 * (S263 measured 13), so NULLable is the honest schema, not a relaxation.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$row = $pdo->query(
    "SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plot_bookings' AND COLUMN_NAME = 'customer_id'"
)->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "SKIP plot_bookings.customer_id (column absent)\nDone.\n";
    return;
}

if ($row['IS_NULLABLE'] === 'YES') {
    echo "SKIP plot_bookings.customer_id (already NULLable)\nDone.\n";
    return;
}

$pdo->exec("ALTER TABLE `plot_bookings` MODIFY `customer_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL");
echo "FIXED plot_bookings.customer_id (now NULLable)\nDone.\n";
