<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$logFile = __DIR__ . '/../../migration_output.log';
ob_start();

require_once __DIR__ . '/../../vendor/autoload.php';

use Database\Database;

$db = Database::getInstance();

// Ensure foreign key checks are disabled for the entire operation
$db->query("USE apsdreamhome;");
$db->query("SET FOREIGN_KEY_CHECKS=0;");

// Drop the users table if it exists
$db->query("DROP TABLE IF EXISTS `users`;");

// Get all migration files
$migrationFiles = glob(__DIR__ . '/*.php');
$migrations = [];

foreach ($migrationFiles as $file) {
    $fileName = basename($file);
    if ($fileName === 'run_migrations.php' || $fileName === 'LoggingMigration.php') {
        continue;
    }
    $migrations[] = $file;
}

// Sort migrations chronologically
usort($migrations, function($a, $b) {
    return basename($a) <=> basename($b);
});

foreach ($migrations as $migrationFile) {
    require_once $migrationFile;
    $className = str_replace('.php', '', basename($migrationFile));
    if (class_exists($className)) {
        $migration = new $className();
        if (method_exists($migration, 'up')) {
            echo "Running migration: " . $className . "\n";
            try {
                $migration->up();
                echo $className . " migration completed successfully.\n";
            } catch (\Exception $e) {
                echo "Error running migration " . $className . ": " . $e->getMessage() . "\n";
                // Re-throw the exception to stop further migrations if desired, or handle it gracefully
                throw $e;
            }
        }
    }
}

// Re-enable foreign key checks
$db->query("SET FOREIGN_KEY_CHECKS=1;");

$output = ob_get_clean();
file_put_contents($logFile, $output);

if (strpos($output, "Fatal error") !== false || strpos($output, "Error") !== false) {
    echo "Migration completed with errors. Check " . $logFile . " for details.\n";
} else {
    echo "All migrations completed successfully. Output saved to " . $logFile . "\n";
}

?>