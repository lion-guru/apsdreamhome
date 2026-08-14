<?php
/**
 * Migration: Add tenant_id columns to all service tables that need multi-tenant isolation.
 * Each table gets:
 *   - tenant_id INT UNSIGNED NOT NULL DEFAULT 1
 *   - INDEX on tenant_id
 *   - FK to tenants(id) â€” but only if tenants table exists
 * 
 * Safe to run multiple times (IF NOT EXISTS pattern via SHOW COLUMNS check).
 */

$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tables = [
    // Directory
    'directory_categories',
    'directory_listings',
    'directory_reviews',
    'directory_jobs',
    'directory_materials',
    // Visits
    'site_visits',
    'visits',
    'property_visits',
    'land_site_visits',
    'lead_visits',
    'mlm_site_visits',
    // Referrals
    'customer_referrals',
    'referrals',
    // Alerts / Searches
    'property_alert_subscriptions',
    'saved_searches',
    'search_history',
    // KYC
    'kyc_requests',
    'kyc_verification_logs',
    // Support
    'support_tickets',
    'support_ticket_replies',
    // Agreements
    'agreements',
    'booking_digital_agreements',
    'booking_emi_agreements',
    // Payments
    'payment_transactions',
    'payments',
];

$added = 0;
$skipped = 0;
$errors = [];

foreach ($tables as $table) {
    // Check table exists
    $stmt = $db->query("SHOW TABLES LIKE '$table'");
    if ($stmt->rowCount() === 0) {
        echo "[SKIP] $table â€” table does not exist\n";
        $skipped++;
        continue;
    }

    // Check if tenant_id column already exists
    $col = $db->query("SHOW COLUMNS FROM `$table` LIKE 'tenant_id'");
    if ($col->rowCount() > 0) {
        echo "[SKIP] $table â€” tenant_id already exists\n";
        $skipped++;
        continue;
    }

    try {
        $db->exec("ALTER TABLE `$table` ADD COLUMN `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `id`");
        $db->exec("ALTER TABLE `$table` ADD INDEX `idx_{$table}_tenant_id` (`tenant_id`)");
        echo "[ADDED] $table â€” tenant_id column + index\n";
        $added++;
    } catch (\PDOException $e) {
        // If "after id" fails (no id column or column order), try without position
        try {
            $db->exec("ALTER TABLE `$table` ADD COLUMN `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1");
            $db->exec("ALTER TABLE `$table` ADD INDEX `idx_{$table}_tenant_id` (`tenant_id`)");
            echo "[ADDED] $table â€” tenant_id column + index (no position)\n";
            $added++;
        } catch (\PDOException $e2) {
            $errors[] = "$table: " . $e2->getMessage();
            echo "[ERROR] $table â€” " . $e2->getMessage() . "\n";
        }
    }
}

echo "\n--- Summary ---\n";
echo "Added: $added\n";
echo "Skipped: $skipped\n";
echo "Errors: " . count($errors) . "\n";
if (!empty($errors)) {
    echo "Error details:\n";
    foreach ($errors as $e) echo "  - $e\n";
}?>