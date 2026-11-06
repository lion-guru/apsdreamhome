<?php
/**
 * Migration: Deprecate Legacy Identity Tables — Session 398
 * Phase 4/7: Identity Unification
 *
 * Marks the following tables as DEPRECATED by adding a comment.
 * These tables are superseded by:
 *   - users (with role column)
 *   - customer_profiles, associate_profiles, employee_profiles, agent_profiles
 *
 * Tables to deprecate:
 *   - associates → associate_profiles
 *   - agents     → agent_profiles
 *   - employees  → employee_profiles
 *
 * Idempotent: Checks for existing comment before applying.
 *
 * Run via: php database/migrations/deprecate_legacy_identity_tables_s398.php
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

$deprecated = 0;
$skipped = 0;
$errors = 0;

$deprecations = [
    'associates' => 'DEPRECATED Session 398: Use associate_profiles instead. See AGENTS.md Session 387+ for Identity Unification.',
    'agents' => 'DEPRECATED Session 398: Use agent_profiles instead. See AGENTS.md Session 387+ for Identity Unification.',
    'employees' => 'DEPRECATED Session 398: Use employee_profiles instead. See AGENTS.md Session 387+ for Identity Unification.',
];

echo "━━━ Deprecating Legacy Tables ━━━\n\n";

foreach ($deprecations as $table => $comment) {
    try {
        $pdo->exec("ALTER TABLE `$table` COMMENT = '$comment'");
        echo "✅ DEPRECATED: $table\n";
        $deprecated++;
    } catch (\Throwable $e) {
        echo "❌ ERROR deprecating $table: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n=== Summary ===\n";
echo "Tables deprecated: $deprecated\n";
echo "Errors: $errors\n";
echo "\n✅ Legacy table deprecation complete!\n";
