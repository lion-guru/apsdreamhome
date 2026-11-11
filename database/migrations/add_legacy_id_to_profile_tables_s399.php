<?php
/**
 * Add legacy_id column to profile tables for backward compatibility.
 *
 * When AuthSessionTrait (and other code) is migrated from legacy tables
 * (associates, employees, agents) to profile tables (associate_profiles,
 * employee_profiles, agent_profiles), the session stores the legacy PK as
 * $_SESSION['associate_id'] / $_SESSION['employee_id'] / $_SESSION['agent_id'].
 *
 * Since profile table PKs (auto_increment) differ from legacy PKs, we store
 * the original legacy ID in a `legacy_id` column so that:
 *   1. AuthSessionTrait can store legacy_id as the session variable
 *   2. Downstream code that queries legacy tables by this ID continues to work
 *   3. New code can use the profile table PK (id) directly
 *
 * Safe: idempotent (checks column existence before ALTER).
 * Zero data loss: legacy_id populated from legacy tables where match exists.
 *
 * @session 399
 */
require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Map: profile_table => [legacy_table, legacy_pk_column]
$maps = [
    'associate_profiles' => ['legacy_table' => 'associates', 'legacy_pk' => 'id'],
    'employee_profiles'   => ['legacy_table' => 'employees',   'legacy_pk' => 'id'],
    'agent_profiles'      => ['legacy_table' => 'agents',     'legacy_pk' => 'id'],
];

foreach ($maps as $profileTable => $info) {
    $legacyTable = $info['legacy_table'];
    $legacyPk = $info['legacy_pk'];

    // Check if profile table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE '$profileTable'")->fetch();
    if (!$tableExists) {
        echo "SKIP: $profileTable does not exist\n";
        continue;
    }

    // Check if legacy table exists
    $legacyExists = $pdo->query("SHOW TABLES LIKE '$legacyTable'")->fetch();
    if (!$legacyExists) {
        echo "SKIP: $legacyTable does not exist (no legacy data to map)\n";
        // Still add the column for future use
    }

    // Check if legacy_id column already exists
    $colExists = $pdo->query("SHOW COLUMNS FROM `$profileTable` LIKE 'legacy_id'")->fetch();
    if ($colExists) {
        echo "SKIP: $profileTable already has legacy_id\n";
        continue;
    }

    // Add legacy_id column (nullable, indexed)
    $pdo->exec("ALTER TABLE `$profileTable` ADD COLUMN `legacy_id` int(11) unsigned NULL AFTER `id`");
    echo "ADDED: legacy_id to $profileTable\n";

    // Populate legacy_id from legacy table via user_id join
    if ($legacyExists) {
        $updated = $pdo->exec("
            UPDATE `$profileTable` p
            JOIN users u ON u.id = p.user_id
            JOIN `$legacyTable` l ON l.user_id = u.id
            SET p.legacy_id = l.$legacyPk
        ");
        echo "  POPULATED: $updated rows in $profileTable from $legacyTable\n";
    }

    // Add index for fast lookup
    try {
        $pdo->exec("CREATE INDEX idx_{$profileTable}_legacy ON `$profileTable` (legacy_id)");
        echo "  INDEX: idx_{$profileTable}_legacy created\n";
    } catch (\Throwable $e) {
        echo "  INDEX: " . $e->getMessage() . "\n";
    }
}

// Verify
echo "\n=== Verification ===\n";
foreach (array_keys($maps) as $table) {
    $tableExists = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
    if (!$tableExists) continue;
    $col = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'legacy_id'")->fetch();
    if ($col) {
        $total = $pdo->query("SELECT COUNT(*) as c FROM `$table`")->fetch()['c'];
        $mapped = $pdo->query("SELECT COUNT(*) as c FROM `$table` WHERE legacy_id IS NOT NULL")->fetch()['c'];
        echo "  $table: legacy_id=YES, rows=$total, mapped=$mapped\n";
    } else {
        echo "  $table: legacy_id=NO (ERROR!)\n";
    }
}

echo "\nDone. Profile tables now have legacy_id for backward-compatible session IDs.\n";
