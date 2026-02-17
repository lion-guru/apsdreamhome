<?php
require_once 'vendor/autoload.php';

use App\Core\Database\Database;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get all migration files
$migrationsPath = __DIR__ . '/database/migrations';
$migrationFiles = glob("$migrationsPath/*.php");

// Sort migrations by filename
sort($migrationFiles);

// Database connection
$db = Database::getInstance();

// Create migrations table if it doesn't exist
$db->query("CREATE TABLE IF NOT EXISTS migrations (
    migration VARCHAR(255) PRIMARY KEY,
    batch INT
)");

// Get already run migrations
$migrated = $db->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);

// Run new migrations
$batch = 1;
$newMigrations = [];

foreach ($migrationFiles as $file) {
    $migration = basename($file);
    
    if (!in_array($migration, $migrated)) {
        require_once $file;
        
        $className = '\\' . str_replace('.php', '', $migration);
        $instance = new $className();
        $instance->up();
        
        $newMigrations[] = $migration;
        
        echo "Migrated: $migration\n";
    }
}

// Save migrations to database
if (!empty($newMigrations)) {
    $stmt = $db->prepare("INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)");
    foreach ($newMigrations as $migration) {
        $stmt->execute([
            'migration' => $migration,
            'batch' => $batch
        ]);
    }
    
    echo "\nMigration completed. " . count($newMigrations) . " migration(s) run.\n";
} else {
    echo "No new migrations to run.\n";
}
