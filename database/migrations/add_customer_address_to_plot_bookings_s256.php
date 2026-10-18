<?php
/**
 * Restore plot_bookings.customer_address (Session 256).
 * Idempotent: ALTER runs ONLY when the column is absent.
 *
 * Background: live table lacks the column (restore drift — no migration ever
 * defined it) while AssociateController@bookPlot INSERTs it → 1054 on EVERY
 * associate booking. Type mirrors the customer_* siblings (VARCHAR 255 NULL).
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$row = $pdo->query(
    "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plot_bookings' AND COLUMN_NAME = 'customer_address'"
)->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo "SKIP plot_bookings.customer_address (present)\nDone.\n";
    return;
}

$pdo->exec("ALTER TABLE `plot_bookings` ADD COLUMN `customer_address` VARCHAR(255) NULL DEFAULT NULL AFTER `customer_phone`");
echo "FIXED plot_bookings.customer_address (restored)\nDone.\n";
