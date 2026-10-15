<?php
/**
 * Migration: Rename Diwali bonus columns to target bonus
 * 
 * This migration renames Diwali-specific columns to generic target bonus columns
 * since salary/bonus should be given when target is achieved, not specifically for Diwali
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Database;

$pdo = Database::getInstance()->getPdo();

echo "=== Renaming Diwali Bonus to Target Bonus ===\n\n";

try {
    // Rename columns in associates table
    echo "Step 1: Renaming columns in associates table...\n";
    $pdo->exec("ALTER TABLE associates CHANGE COLUMN diwali_bonus_eligible target_bonus_eligible TINYINT(1) DEFAULT 0");
    $pdo->exec("ALTER TABLE associates CHANGE COLUMN diwali_bonus_amount target_bonus_amount DECIMAL(10,2) DEFAULT 0");
    echo "âœ“ Columns renamed\n\n";

    // Verify changes
    echo "Step 2: Verifying changes...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM associates LIKE 'target_bonus%'");
    while ($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  âœ“ {$col['Field']}: {$col['Type']}\n";
    }

    echo "\nâœ“ Migration completed successfully!\n";

} catch (Exception $e) {
    echo "âœ— Error: " . $e->getMessage() . "\n";
    exit(1);
}?>