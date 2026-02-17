<?php
// Simple migrations runner for apsdreamhome
// Usage (CLI):
//   php database/run_migrations.php            # apply all pending .sql migrations
//   php database/run_migrations.php --dry-run  # list pending migrations without applying
//
// Migrations are plain SQL files placed in `database/migrations`.
// Files are applied in natural order. Each file is recorded in `schema_migrations`.

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/includes/config/config.php';

if (!isset($con) || !$con instanceof mysqli) {
    fwrite(STDERR, "[ERROR] Database connection \$con is not available (mysqli).\n");
    exit(1);
}

// Ensure migrations directory exists
$migrationsDir = __DIR__ . '/migrations';
if (!is_dir($migrationsDir)) {
    mkdir($migrationsDir, 0777, true);
}

// Create schema_migrations tracking table if missing
$createTrackingSql = <<<SQL
CREATE TABLE IF NOT EXISTS `schema_migrations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL UNIQUE,
  `run_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

$con->query($createTrackingSql);

// Parse CLI args
$argv = $_SERVER['argv'] ?? [];
$dryRun = in_array('--dry-run', $argv, true);
$onlyNames = [];
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--only=')) {
        $list = trim(substr($arg, 7), "\"' ");
        if ($list !== '') {
            $onlyNames = array_map('trim', explode(',', $list));
        }
    }
}

// Read applied migrations
$applied = [];
$res = $con->query("SELECT migration FROM schema_migrations");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $applied[$row['migration']] = true;
    }
    $res->free();
}

// Collect .sql migrations
$files = glob($migrationsDir . '/*.sql');
usort($files, function ($a, $b) {
    $na = basename($a);
    $nb = basename($b);
    return strnatcasecmp($na, $nb);
});

// Filter pending
$pending = [];
foreach ($files as $file) {
    $name = basename($file);
    if (!isset($applied[$name])) {
        $pending[] = $file;
    }
}

// If --only provided, restrict to those names
if (!empty($onlyNames)) {
    $pending = array_values(array_filter($pending, function ($path) use ($onlyNames) {
        $name = basename($path);
        return in_array($name, $onlyNames, true);
    }));
}

if (empty($pending)) {
    echo "No pending migrations" . (!empty($onlyNames) ? " for specified --only filter" : "") . ".\n";
    exit(0);
}

echo ($dryRun ? "Pending migrations (dry-run):\n" : "Applying migrations:\n");
foreach ($pending as $file) {
    $name = basename($file);
    echo " - $name\n";
}

if ($dryRun) {
    exit(0);
}

// Apply each migration inside a transaction
foreach ($pending as $file) {
    $name = basename($file);
    $sql = file_get_contents($file);
    if ($sql === false) {
        fwrite(STDERR, "[ERROR] Unable to read migration file: $name\n");
        exit(1);
    }

    try {
        $con->begin_transaction();
        if (!$con->multi_query($sql)) {
            throw new RuntimeException($con->error);
        }
        // Flush all result sets
        do {
            if ($result = $con->store_result()) {
                $result->free();
            }
        } while ($con->more_results() && $con->next_result());

        $stmt = $con->prepare('INSERT INTO schema_migrations (migration) VALUES (?)');
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $stmt->close();

        $con->commit();
        echo "Applied: $name\n";
    } catch (Throwable $e) {
        $con->rollback();
        fwrite(STDERR, "[ERROR] Migration failed ($name): " . $e->getMessage() . "\n");
        exit(1);
    }
}

echo "All pending migrations applied successfully.\n";
