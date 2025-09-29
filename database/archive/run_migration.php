<?php
/**
 * Database Migration Script for APS Dream Home
 * 
 * This script applies the latest database migrations.
 */

// Load configuration
require_once __DIR__ . '/../includes/config/DatabaseConfig.php';

// Initialize database connection
try {
    // Initialize the database configuration
    DatabaseConfig::init();
    
    // Get the database connection
    $conn = DatabaseConfig::getConnection();
    
    if (!$conn) {
        throw new Exception('Failed to establish database connection');
    }
    
    echo "Database connection established successfully.\n";
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get all migration files
        $migrationDir = __DIR__ . '/migrations';
        $migrationFiles = glob($migrationDir . '/*.sql');
        
        // Sort files by name (which should include timestamp)
        sort($migrationFiles);
        
        // Create migrations table if it doesn't exist
        $createTableSql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($createTableSql);
        
        // Get the batch number for new migrations
        $result = $conn->query("SELECT MAX(batch) as max_batch FROM migrations");
        $batch = $result->fetch_assoc()['max_batch'] ?? 0;
        $batch++;
        
        // Apply migrations
        foreach ($migrationFiles as $file) {
            $migrationName = basename($file);
            
            // Check if migration was already run
            $stmt = $conn->prepare("SELECT id FROM migrations WHERE migration = ?");
            $stmt->bind_param('s', $migrationName);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                // Read SQL file
                $sql = file_get_contents($file);
                
                if ($sql === false) {
                    throw new Exception("Failed to read migration file: " . $migrationName);
                }
                
                echo "Applying migration: " . $migrationName . "\n";
                
                // Execute SQL queries
                if ($conn->multi_query($sql)) {
                    do {
                        // Consume results to avoid "Commands out of sync" error
                        if ($result = $conn->store_result()) {
                            $result->free();
                        }
                    } while ($conn->more_results() && $conn->next_result());
                    
                    if ($conn->error) {
                        throw new Exception("Error executing migration " . $migrationName . ": " . $conn->error);
                    }
                    
                    // Record migration
                    $stmt = $conn->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                    $stmt->bind_param('si', $migrationName, $batch);
                    $stmt->execute();
                    
                    echo "Applied migration: " . $migrationName . "\n";
                } else {
                    throw new Exception("Error executing migration " . $migrationName . ": " . $conn->error);
                }
            } else {
                echo "Skipping already applied migration: " . $migrationName . "\n";
            }
        }
        
        // Commit transaction
        $conn->commit();
        echo "All migrations completed successfully.\n";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
} finally {
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
}
