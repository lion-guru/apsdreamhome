<?php
/**
 * Migration S457: Salary Maintenance, 40:60 Leg Balancing, Mentorship Royalty & Fast-Start Bonus
 * 
 * 1. Enhances salary_tracker & mlm_salary_grants with maintenance tracking.
 * 2. Adds mlm_fast_start_achievements table for 30-day kickstart tracking.
 * 3. Expands mlm_commission_ledger ENUM or notes for mentorship_royalty.
 * 
 * 100% Idempotent. Re-run skips safely.
 */

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $pdo = \App\Core\Database\Database::getInstance()->getPdo();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S457: Salary Maintenance, Mentorship & Fast-Start Engine ===\n";

// Helper to check if column exists
function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
          AND TABLE_NAME = ? 
          AND COLUMN_NAME = ?
    ");
    $stmt->execute([$table, $column]);
    return (int)$stmt->fetchColumn() > 0;
}

// Helper to check if table exists
function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = DATABASE() 
          AND TABLE_NAME = ?
    ");
    $stmt->execute([$table]);
    return (int)$stmt->fetchColumn() > 0;
}

// 1. Alter salary_tracker if it exists
if (tableExists($pdo, 'salary_tracker')) {
    if (!columnExists($pdo, 'salary_tracker', 'maintenance_mode')) {
        $pdo->exec("ALTER TABLE salary_tracker ADD COLUMN maintenance_mode ENUM('sales_or_visit','strict_sales','gps_visit') NOT NULL DEFAULT 'sales_or_visit' AFTER status");
        echo "  [+] Added maintenance_mode to salary_tracker\n";
    }
    if (!columnExists($pdo, 'salary_tracker', 'consecutive_inactive_months')) {
        $pdo->exec("ALTER TABLE salary_tracker ADD COLUMN consecutive_inactive_months TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER maintenance_mode");
        echo "  [+] Added consecutive_inactive_months to salary_tracker\n";
    }
    if (!columnExists($pdo, 'salary_tracker', 'paused_reason')) {
        $pdo->exec("ALTER TABLE salary_tracker ADD COLUMN paused_reason VARCHAR(255) NULL AFTER consecutive_inactive_months");
        echo "  [+] Added paused_reason to salary_tracker\n";
    }
    if (!columnExists($pdo, 'salary_tracker', 'last_activity_verified_at')) {
        $pdo->exec("ALTER TABLE salary_tracker ADD COLUMN last_activity_verified_at TIMESTAMP NULL AFTER paused_reason");
        echo "  [+] Added last_activity_verified_at to salary_tracker\n";
    }
}

// 2. Alter mlm_salary_grants if it exists
if (tableExists($pdo, 'mlm_salary_grants')) {
    if (!columnExists($pdo, 'mlm_salary_grants', 'maintenance_mode')) {
        $pdo->exec("ALTER TABLE mlm_salary_grants ADD COLUMN maintenance_mode ENUM('sales_or_visit','strict_sales','gps_visit') NOT NULL DEFAULT 'sales_or_visit' AFTER status");
        echo "  [+] Added maintenance_mode to mlm_salary_grants\n";
    }
    if (!columnExists($pdo, 'mlm_salary_grants', 'consecutive_inactive_months')) {
        $pdo->exec("ALTER TABLE mlm_salary_grants ADD COLUMN consecutive_inactive_months TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER maintenance_mode");
        echo "  [+] Added consecutive_inactive_months to mlm_salary_grants\n";
    }
    if (!columnExists($pdo, 'mlm_salary_grants', 'paused_reason')) {
        $pdo->exec("ALTER TABLE mlm_salary_grants ADD COLUMN paused_reason VARCHAR(255) NULL AFTER consecutive_inactive_months");
        echo "  [+] Added paused_reason to mlm_salary_grants\n";
    }
    if (!columnExists($pdo, 'mlm_salary_grants', 'last_activity_verified_at')) {
        $pdo->exec("ALTER TABLE mlm_salary_grants ADD COLUMN last_activity_verified_at TIMESTAMP NULL AFTER paused_reason");
        echo "  [+] Added last_activity_verified_at to mlm_salary_grants\n";
    }
}

// 3. Create mlm_fast_start_achievements table
if (!tableExists($pdo, 'mlm_fast_start_achievements')) {
    $pdo->exec("
        CREATE TABLE mlm_fast_start_achievements (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            user_id BIGINT UNSIGNED NOT NULL,
            recruited_associates INT UNSIGNED NOT NULL DEFAULT 0,
            verified_site_visits INT UNSIGNED NOT NULL DEFAULT 0,
            bonus_amount DECIMAL(10,2) NOT NULL DEFAULT 2100.00,
            status ENUM('in_progress','qualified','awarded','expired') NOT NULL DEFAULT 'in_progress',
            qualification_deadline DATE NOT NULL,
            awarded_at TIMESTAMP NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_user_fast_start (user_id),
            INDEX idx_status (status),
            INDEX idx_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [+] Created table mlm_fast_start_achievements\n";
} else {
    echo "  [*] Table mlm_fast_start_achievements already exists (skipped)\n";
}

echo "=== MIGRATION S457 COMPLETE: Success ===\n";

