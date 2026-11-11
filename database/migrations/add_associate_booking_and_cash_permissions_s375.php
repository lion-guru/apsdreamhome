<?php
/**
 * Migration: Add Offline Booking and Cash Collection Permission Controls to associates table
 * Session: 375
 * Idempotent: checks for column existence before adding
 */

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

echo "Running Migration: add_associate_booking_and_cash_permissions_s375...\n";

// Helper to check column
$hasColumn = function(\PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$table, $column]);
    return ((int)$stmt->fetchColumn()) > 0;
};

if (!$hasColumn($pdo, 'associates', 'can_offline_book')) {
    $pdo->exec("ALTER TABLE `associates` ADD COLUMN `can_offline_book` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Admin toggle: 1=allowed to book plot offline, 0=blocked'");
    echo "  [+] Added can_offline_book column to associates table.\n";
} else {
    echo "  [*] can_offline_book column already exists. Skipping.\n";
}

if (!$hasColumn($pdo, 'associates', 'can_collect_cash')) {
    $pdo->exec("ALTER TABLE `associates` ADD COLUMN `can_collect_cash` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Admin toggle: 1=allowed to collect cash payments, 0=blocked'");
    echo "  [+] Added can_collect_cash column to associates table.\n";
} else {
    echo "  [*] can_collect_cash column already exists. Skipping.\n";
}

if (!$hasColumn($pdo, 'associates', 'daily_cash_limit')) {
    $pdo->exec("ALTER TABLE `associates` ADD COLUMN `daily_cash_limit` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Max cash an associate can hold before office deposit'");
    echo "  [+] Added daily_cash_limit column to associates table.\n";
} else {
    echo "  [*] daily_cash_limit column already exists. Skipping.\n";
}

if (!$hasColumn($pdo, 'associates', 'cash_policy_agreed')) {
    $pdo->exec("ALTER TABLE `associates` ADD COLUMN `cash_policy_agreed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=associate acknowledged cash liability policy'");
    echo "  [+] Added cash_policy_agreed column to associates table.\n";
} else {
    echo "  [*] cash_policy_agreed column already exists. Skipping.\n";
}

echo "Migration Complete.\n";
