<?php
/**
 * Migration: Add vendor KYC fields + entity type for 194C TDS auto-detection
 *
 * Adds:
 *   - gstin VARCHAR(15) — GST Identification Number
 *   - entity_type ENUM('individual','company','partnership','proprietorship') — for TDS classification
 *   - tds_section VARCHAR(10) DEFAULT '194C' — auto-detected from entity_type
 *   - is_tds_applicable TINYINT(1) DEFAULT 1
 *   - kyc_status ENUM('pending','verified','rejected')
 *   - kyc_verified_at DATETIME NULL
 *
 * Note: pan_number and gst_number already exist in the vendors table.
 *       vendor_type already exists with business categories (contractor/supplier/etc.)
 *       entity_type is added separately for TDS classification purposes.
 *
 * Run: php scripts/add_vendor_kyc_columns.php
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "=== Vendor KYC Migration ===" . PHP_EOL;

    // Get existing columns
    $stmt = $pdo->query("DESCRIBE vendors");
    $existing = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existing[] = $row['Field'];
    }

    $columns = [
        'gstin' => "ALTER TABLE vendors ADD COLUMN gstin VARCHAR(15) NULL AFTER pan_number",
        'entity_type' => "ALTER TABLE vendors ADD COLUMN entity_type ENUM('individual','company','partnership','proprietorship') DEFAULT 'individual' AFTER gstin",
        'tds_section' => "ALTER TABLE vendors ADD COLUMN tds_section VARCHAR(10) DEFAULT '194C' AFTER entity_type",
        'is_tds_applicable' => "ALTER TABLE vendors ADD COLUMN is_tds_applicable TINYINT(1) DEFAULT 1 AFTER tds_section",
        'kyc_status' => "ALTER TABLE vendors ADD COLUMN kyc_status ENUM('pending','verified','rejected') DEFAULT 'pending' AFTER is_tds_applicable",
        'kyc_verified_at' => "ALTER TABLE vendors ADD COLUMN kyc_verified_at DATETIME NULL AFTER kyc_status",
    ];

    $added = 0;
    $skipped = 0;

    foreach ($columns as $col => $sql) {
        if (in_array($col, $existing)) {
            echo "  [SKIP] Column '{$col}' already exists" . PHP_EOL;
            $skipped++;
            continue;
        }
        $pdo->exec($sql);
        echo "  [ADD]  Column '{$col}' added successfully" . PHP_EOL;
        $added++;
    }

    echo PHP_EOL . "Migration complete: {$added} added, {$skipped} skipped" . PHP_EOL;

    // Verify final schema
    echo PHP_EOL . "--- Final vendors table schema ---" . PHP_EOL;
    $stmt = $pdo->query("DESCRIBE vendors");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $default = $row['Default'] ?? 'NULL';
        printf("  %-25s %-40s %s" . PHP_EOL, $row['Field'], $row['Type'], $default);
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
