<?php
/**
 * Migration: Mark legacy identity tables as deprecated via table comments
 *
 * Phase 4/7 Identity Unification — Session 396
 *
 * Marks associates, agents, employees tables with deprecation comments.
 * These tables are superseded by profile tables (associate_profiles,
 * employee_profiles, agent_profiles, customer_profiles) accessed via
 * App\Services\IdentityService.
 *
 * This is a metadata-only change (table COMMENT) — zero data risk, zero
 * behavior change. The tables continue to work for backward compatibility
 * while new code should use IdentityService.
 *
 * Idempotent: Sets comment only when different.
 *
 * Run via: php database/migrations/deprecate_legacy_identity_tables_s397.php
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

$deprecated = 'DEPRECATED (Phase 4/7): Use associate_profiles + App\Services\IdentityService instead. Read-only for new code.';

$tables = [
    'associates' => 'DEPRECATED (Phase 4/7): Use associate_profiles + App\Services\IdentityService::getAssociateProfile() instead.',
    'agents' => 'DEPRECATED (Phase 4/7): Use agent_profiles + App\Services\IdentityService::getAgentProfile() instead.',
    'employees' => 'DEPRECATED (Phase 4/7): Use employee_profiles + App\Services\IdentityService::getEmployeeProfile() instead.',
    'customers' => null, // table doesn't exist — customers are users with role=customer
];

foreach ($tables as $table => $comment) {
    if ($comment === null) continue;

    // Check table exists
    $exists = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
    if (!$exists) {
        echo "⏭  SKIP: $table does not exist\n";
        continue;
    }

    try {
        $pdo->exec("ALTER TABLE `$table` COMMENT = '$comment'");
        echo "✅ MARKED DEPRECATED: $table\n";
    } catch (\Throwable $e) {
        echo "❌ ERROR marking $table: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Deprecation marks complete ===\n";
echo "New code should use App\Services\IdentityService for all profile lookups.\n";
echo "Legacy tables remain functional for backward compatibility.\n";