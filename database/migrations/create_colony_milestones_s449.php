<?php
/**
 * Migration: create `colony_milestones`
 *
 * The colony site-development milestone feature is implemented in FIVE
 * controllers but the table was never created:
 *
 *   - Admin\ProjectProgressController   (colony progress desk: list/create/update)
 *   - Admin\ColonyPipelineController     (site development milestones + photos)
 *   - Api\ConstructionApiController      (mobile colony progress + milestones)
 *   - Front\CustomerPassbookController   (customer colony development feed)
 *   - Front\PageController (colony pipeline)
 *
 * Every one of those reads/writes `colony_milestones`, so the whole feature
 * 500'd (mobile) or silently returned an empty list (web) because the table
 * was missing:
 *
 *   ERROR 1146 (42S02): Table 'apsdreamhome.colony_milestones' doesn't exist
 *
 * The schema below is derived from the LIVE column usage of all five
 * callers (not from a guess), so every existing query works unchanged:
 *
 *   id, tenant_id, colony_id, milestone_name, category, status,
 *   progress_pct, start_date, expected_completion, actual_completion,
 *   contractor_name, contractor_contact, estimated_cost, actual_cost,
 *   notes, remarks, site_photo_path, created_at, updated_at
 *
 * `status` and `category` are VARCHAR (not ENUM) on purpose - an ENUM in a
 * migration can silently NARROW the live value set on a re-run after a DB
 * restore (see AGENTS.md lesson _444). The full allowed sets are:
 *
 *   status  : not_started | in_progress | on_hold | completed | cancelled
 *   category: roads | drainage | electricity | boundary | water |
 *             landscaping | other
 *
 * Idempotent: CREATE TABLE IF NOT EXISTS. Re-running is a no-op.
 *
 * Run via: php database/migrations/create_colony_milestones_s449.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "OK  Database connected\n\n";
} catch (\Throwable $e) {
    echo "ERR Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$table = 'colony_milestones';

$exists = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
if ($exists) {
    echo "SKIP table $table already exists\n";
    exit(0);
}

$sql = "CREATE TABLE IF NOT EXISTS `$table` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `tenant_id` INT(10) UNSIGNED NOT NULL DEFAULT 1,
    `colony_id` INT(11) NOT NULL,
    `milestone_name` VARCHAR(255) NOT NULL,
    `category` VARCHAR(50) NOT NULL DEFAULT 'other',
    `status` VARCHAR(50) NOT NULL DEFAULT 'not_started',
    `progress_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `start_date` DATE NULL DEFAULT NULL,
    `expected_completion` DATE NULL DEFAULT NULL,
    `actual_completion` DATE NULL DEFAULT NULL,
    `contractor_name` VARCHAR(255) NULL DEFAULT NULL,
    `contractor_contact` VARCHAR(20) NULL DEFAULT NULL,
    `estimated_cost` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `actual_cost` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `notes` TEXT NULL DEFAULT NULL,
    `remarks` TEXT NULL DEFAULT NULL,
    `site_photo_path` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cm_colony` (`colony_id`),
    KEY `idx_cm_tenant` (`tenant_id`),
    KEY `idx_cm_status` (`status`),
    KEY `idx_cm_colony_status` (`colony_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    $pdo->exec($sql);
    echo "OK  created table $table\n";
} catch (\Throwable $e) {
    echo "ERR failed to create $table: " . $e->getMessage() . "\n";
    exit(1);
}

// Verify the columns the five callers rely on really exist.
$required = [
    'id', 'tenant_id', 'colony_id', 'milestone_name', 'category', 'status',
    'progress_pct', 'start_date', 'expected_completion', 'actual_completion',
    'contractor_name', 'contractor_contact', 'estimated_cost', 'actual_cost',
    'notes', 'remarks', 'site_photo_path', 'created_at', 'updated_at',
];
$missing = [];
foreach ($required as $col) {
    $r = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$col'")->fetch();
    if (!$r) {
        $missing[] = $col;
    }
}
if ($missing) {
    echo "ERR missing columns: " . implode(', ', $missing) . "\n";
    exit(1);
}
echo "OK  verified " . count($required) . " columns\n";
echo "DONE\n";
