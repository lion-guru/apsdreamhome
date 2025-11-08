<?php
/**
 * APS Dream Home - Database Migration Runner
 * 
 * This script runs pending database migrations.
 */

// Set header for browser output
header('Content-Type: text/plain; charset=utf-8');

echo "=== APS Dream Home Database Migration Runner ===\n\n";

// Load configuration
require_once __DIR__ . '/../includes/config.php';

// Database connection
try {
    $config = AppConfig::getInstance()->get('database');
    $conn = new mysqli(
        $config['host'],
        $config['user'],
        $config['pass'],
        $config['name']
    );
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
    echo "✅ Connected to database: " . $config['name'] . "\n";
    
    // Create migrations table if it doesn't exist
    echo "\n🔍 Checking migrations table...\n";
    $conn->query("CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        version VARCHAR(20) NOT NULL,
        migration_name VARCHAR(255) NOT NULL,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_migration (version, migration_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    echo "✅ Migrations table ready\n";
    
    // Get applied migrations
    $appliedMigrations = [];
    $result = $conn->query("SELECT version, migration_name FROM migrations ORDER BY version ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $appliedMigrations[] = $row['version'] . '_' . $row['migration_name'];
        }
    }
    
    echo "\n📋 Applied migrations: " . count($appliedMigrations) . "\n";
    
    // Find migration files
    $migrationFiles = glob(__DIR__ . '/migrations/V*.sql');
    $pendingMigrations = [];
    
    foreach ($migrationFiles as $file) {
        $filename = basename($file);
        if (preg_match('/^V(\d+\.\d+\.\d+)__(.+)\.sql$/', $filename, $matches)) {
            $version = $matches[1];
            $name = strtolower(str_replace('_', '', $matches[2]));
            
            $migrationKey = $version . '_' . $name;
            
            if (!in_array($migrationKey, $appliedMigrations)) {
                $pendingMigrations[$version] = [
                    'file' => $file,
                    'name' => $name,
                    'version' => $version
                ];
            }
        }
    }
    
    // Sort by version
    uksort($pendingMigrations, 'version_compare');
    
    if (empty($pendingMigrations)) {
        echo "\n✅ No pending migrations found.\n";
        exit(0);
    }
    
    echo "\n🔄 Found " . count($pendingMigrations) . " pending migrations:\n";
    
    // Apply pending migrations
    foreach ($pendingMigrations as $migration) {
        echo "\n🔧 Applying migration: " . $migration['version'] . ' - ' . $migration['name'] . "\n";
        
        // Read migration file
        $sql = file_get_contents($migration['file']);
        
        // Split into individual statements
        $statements = array_filter(
            array_map('trim', 
                preg_split('/;/', $sql, -1, PREG_SPLIT_NO_EMPTY)
            )
        );
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    echo "  - Executing: " . substr($statement, 0, 100) . (strlen($statement) > 100 ? '...' : '') . "\n";
                    if (!$conn->query($statement)) {
                        throw new Exception("Migration failed: " . $conn->error);
                    }
                }
            }
            
            // Record migration
            $stmt = $conn->prepare("INSERT INTO migrations (version, migration_name) VALUES (?, ?)");
            $stmt->bind_param("ss", $migration['version'], $migration['name']);
            $stmt->execute();
            
            $conn->commit();
            echo "✅ Migration applied successfully\n";
            
        } catch (Exception $e) {
            $conn->rollback();
            echo "❌ Error applying migration: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    echo "\n✨ All migrations completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

echo "\n=== Migration process completed ===\n";
