<?php
/**
 * Link plot_bookings → leads (Session 256, convert-to-booking loop).
 * Idempotent: each step runs ONLY when absent.
 * NULLable + SET NULL: bookings survive lead deletes; old rows stay NULL
 * (no backfill — historical links cannot be invented).
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$col = $pdo->query(
    "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plot_bookings' AND COLUMN_NAME = 'lead_id'"
)->fetch(PDO::FETCH_ASSOC);

if (!$col) {
    $pdo->exec("ALTER TABLE `plot_bookings` ADD COLUMN `lead_id` INT(11) NULL DEFAULT NULL AFTER `customer_id`");
    echo "FIXED plot_bookings.lead_id (added)\n";
} else {
    echo "SKIP plot_bookings.lead_id (present)\n";
}

$idx = $pdo->query(
    "SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plot_bookings' AND COLUMN_NAME = 'lead_id'"
)->fetch(PDO::FETCH_ASSOC);

if (!$idx) {
    $pdo->exec("CREATE INDEX `idx_pb_lead` ON `plot_bookings` (`lead_id`)");
    echo "FIXED idx_pb_lead (created)\n";
} else {
    echo "SKIP idx_pb_lead (present)\n";
}

$fk = $pdo->query(
    "SELECT 1 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plot_bookings'
      AND COLUMN_NAME = 'lead_id' AND REFERENCED_TABLE_NAME = 'leads'"
)->fetch(PDO::FETCH_ASSOC);

if (!$fk) {
    $pdo->exec("ALTER TABLE `plot_bookings` ADD CONSTRAINT `fk_pb_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL");
    echo "FIXED fk_pb_lead (created)\n";
} else {
    echo "SKIP fk_pb_lead (present)\n";
}
echo "Done.\n";
