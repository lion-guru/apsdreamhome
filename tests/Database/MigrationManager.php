<?php

namespace Tests\Database;

use mysqli;
use RuntimeException;

class MigrationManager
{
    private mysqli $db;
    private string $migrationsTable = 'migrations';
    private string $migrationsPath;

    public function __construct(mysqli $db, string $migrationsPath)
    {
        $this->db = $db;
        $this->migrationsPath = rtrim($migrationsPath, '/') . '/';
        $this->ensureMigrationsTableExists();
    }

    /**
     * Run all pending migrations
     */
    public function migrate(): void
    {
        $migrations = $this->getPendingMigrations();
        
        if (empty($migrations)) {
            echo "No pending migrations.\n";
            return;
        }

        echo "Running " . count($migrations) . " migration(s)...\n";
        
        foreach ($migrations as $migration) {
            $this->runMigration($migration);
        }
        
        echo "Migrations completed successfully.\n";
    }

    /**
     * Rollback the last batch of migrations
     */
    public function rollback(): void
    {
        $migrations = $this->getLastBatchMigrations();
        
        if (empty($migrations)) {
            echo "No migrations to rollback.\n";
            return;
        }

        echo "Rolling back " . count($migrations) . " migration(s)...\n";
        
        foreach (array_reverse($migrations) as $migration) {
            $this->rollbackMigration($migration);
        }
        
        echo "Rollback completed successfully.\n";
    }

    /**
     * Run a specific migration
     */
    private function runMigration(string $migrationClass): void
    {
        require_once $this->migrationsPath . $migrationClass . '.php';
        
        $fullClassName = "Tests\\Database\\Migrations\\$migrationClass";
        $migration = new $fullClassName($this->db);
        
        echo "Running migration: $migrationClass... ";
        
        try {
            $this->db->begin_transaction();
            $migration->up();
            $this->recordMigration($migrationClass);
            $this->db->commit();
            echo "DONE\n";
        } catch (\Exception $e) {
            $this->db->rollback();
            echo "FAILED: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    /**
     * Rollback a specific migration
     */
    private function rollbackMigration(string $migrationClass): void
    {
        require_once $this->migrationsPath . $migrationClass . '.php';
        
        $fullClassName = "Tests\\Database\\Migrations\\$migrationClass";
        $migration = new $fullClassName($this->db);
        
        echo "Rolling back: $migrationClass... ";
        
        try {
            $this->db->begin_transaction();
            $migration->down();
            $this->deleteMigration($migrationClass);
            $this->db->commit();
            echo "DONE\n";
        } catch (\Exception $e) {
            $this->db->rollback();
            echo "FAILED: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    /**
     * Get all pending migrations
     */
    private function getPendingMigrations(): array
    {
        $migrations = [];
        $files = glob($this->migrationsPath . '*.php');
        
        foreach ($files as $file) {
            $migration = basename($file, '.php');
            if (!$this->isMigrated($migration)) {
                $migrations[] = $migration;
            }
        }
        
        sort($migrations);
        return $migrations;
    }

    /**
     * Get the last batch of migrations
     */
    private function getLastBatchMigrations(): array
    {
        $result = $this->db->query(
            "SELECT migration FROM {$this->migrationsTable} " .
            "WHERE batch = (SELECT MAX(batch) FROM {$this->migrationsTable}) " .
            "ORDER BY migration DESC"
        );
        
        $migrations = [];
        while ($row = $result->fetch_assoc()) {
            $migrations[] = $row['migration'];
        }
        
        return $migrations;
    }

    /**
     * Check if a migration has been run
     */
    private function isMigrated(string $migration): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM {$this->migrationsTable} WHERE migration = ?"
        );
        
        $stmt->bind_param('s', $migration);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result['count'] > 0;
    }

    /**
     * Record a migration in the database
     */
    private function recordMigration(string $migration): void
    {
        $batch = $this->getNextBatchNumber();
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->migrationsTable} (migration, batch) VALUES (?, ?)"
        );
        
        $stmt->bind_param('si', $migration, $batch);
        $stmt->execute();
    }

    /**
     * Remove a migration record from the database
     */
    private function deleteMigration(string $migration): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->migrationsTable} WHERE migration = ?"
        );
        
        $stmt->bind_param('s', $migration);
        $stmt->execute();
    }

    /**
     * Get the next batch number
     */
    private function getNextBatchNumber(): int
    {
        $result = $this->db->query(
            "SELECT COALESCE(MAX(batch), 0) + 1 as next_batch FROM {$this->migrationsTable}"
        );
        
        return (int) $result->fetch_assoc()['next_batch'];
    }

    /**
     * Create the migrations table if it doesn't exist
     */
    private function ensureMigrationsTableExists(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_migration (migration)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }
}
