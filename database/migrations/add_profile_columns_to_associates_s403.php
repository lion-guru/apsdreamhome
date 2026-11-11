<?php
/**
 * Migration: Add profile columns to associates table
 *
 * The AssociateDashboardController and SmartRoutingService expect these columns
 * on the associates table, but they were never added.
 *
 * Idempotent: Checks if column exists before adding.
 *
 * Run via: php database/migrations/add_profile_columns_to_associates_s403.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "✅ Database connected\n\n";
} catch (\Throwable $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$columns = [
    ['max_leads_capacity', 'INT DEFAULT 50'],
    ['current_leads_count', 'INT DEFAULT 0'],
    ['specializations', 'VARCHAR(500) DEFAULT NULL'],
    ['preferred_locations', 'VARCHAR(500) DEFAULT NULL'],
    ['preferred_budget_min', 'DECIMAL(15,2) DEFAULT NULL'],
    ['preferred_budget_max', 'DECIMAL(15,2) DEFAULT NULL'],
    ['performance_score', 'DECIMAL(5,2) DEFAULT 50.00'],
    ['response_time_avg_hours', 'DECIMAL(5,2) DEFAULT NULL'],
];

$added = 0;
$skipped = 0;
$errors = 0;

foreach ($columns as [$col, $def]) {
    $cols = $pdo->query("SHOW COLUMNS FROM associates LIKE '$col'")->fetch();
    if ($cols) {
        echo "⏭  SKIP: $col already exists\n";
        $skipped++;
        continue;
    }
    try {
        $pdo->exec("ALTER TABLE associates ADD COLUMN $col $def");
        echo "✅ Added $col to associates\n";
        $added++;
    } catch (\Throwable $e) {
        echo "❌ ERROR adding $col: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n=== Summary ===\n";
echo "Added: $added, Skipped: $skipped, Errors: $errors\n";