<?php
/**
 * Migration: Add KYC, Notes, and Agreement Tracking fields to land_brokers
 *
 * Adds:
 * - specialization (varchar 255)
 * - notes (text)
 * - kyc_verified (tinyint 1)
 * - agreement_signed (tinyint 1)
 * - agreement_signed_at (datetime)
 * - agreement_ref_number (varchar 50)
 *
 * Idempotent: checks existing columns and skips cleanly.
 */

define('APS_ROOT', dirname(__DIR__, 2));
require_once APS_ROOT . '/config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "=== Starting Migration: Add Broker KYC & Agreement Fields (S456) ===\n\n";

    $existingCols = [];
    $stmt = $pdo->query("SHOW COLUMNS FROM land_brokers");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existingCols[] = $row['Field'];
    }

    $colsToAdd = [
        'specialization'       => "VARCHAR(255) NULL AFTER address",
        'notes'                => "TEXT NULL AFTER specialization",
        'kyc_verified'         => "TINYINT(1) DEFAULT 0 AFTER active",
        'agreement_signed'     => "TINYINT(1) DEFAULT 0 AFTER kyc_verified",
        'agreement_signed_at'  => "DATETIME NULL AFTER agreement_signed",
        'agreement_ref_number' => "VARCHAR(50) NULL AFTER agreement_signed_at",
    ];

    foreach ($colsToAdd as $col => $def) {
        if (!in_array($col, $existingCols, true)) {
            $pdo->exec("ALTER TABLE land_brokers ADD COLUMN `{$col}` {$def}");
            echo "  ✓ Added column: {$col}\n";
        } else {
            echo "  ✓ Column already exists: {$col} (skipped)\n";
        }
    }

    echo "\n=== Migration Completed Successfully! ===\n";
    exit(0);
} catch (\Throwable $e) {
    echo "❌ Migration Error: " . $e->getMessage() . "\n";
    exit(1);
}

