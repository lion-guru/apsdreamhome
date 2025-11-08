<?php
namespace Database;

class MigrationManager {
    private $db;
    private $migrations_table = 'migrations';

    public function __construct() {
        $this->db = Database::getInstance();
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrations_table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration_name VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->db->query($sql);
    }

    public function getMigrationsInDirectory($directory) {
        $migrations = [];
        $files = glob($directory . '/*.sql');
        foreach ($files as $file) {
            $migrations[] = [
                'name' => basename($file),
                'path' => $file
            ];
        }
        return $migrations;
    }

    public function getExecutedMigrations() {
        return $this->db->fetchAll("SELECT * FROM {$this->migrations_table} ORDER BY id");
    }

    public function runMigration($migration_file) {
        $sql = file_get_contents($migration_file);
        $queries = array_filter(array_map('trim', explode(';', $sql)));

        try {
            foreach ($queries as $query) {
                if (!empty($query)) {
                    $this->db->query($query);
                }
            }
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Migration failed: ' . $e->getMessage());
        }
    }

    public function migrate($migrations_path) {
        $migrations = $this->getMigrationsInDirectory($migrations_path);
        $executed = $this->getExecutedMigrations();
        $executed_names = array_column($executed, 'migration_name');
        $batch = $this->getLastBatch() + 1;

        foreach ($migrations as $migration) {
            if (!in_array($migration['name'], $executed_names)) {
                try {
                    $this->runMigration($migration['path']);
                    $this->logMigration($migration['name'], $batch);
                    echo "Migrated: {$migration['name']}\n";
                } catch (\Exception $e) {
                    echo "Error migrating {$migration['name']}: {$e->getMessage()}\n";
                    break;
                }
            }
        }
    }

    private function getLastBatch() {
        $result = $this->db->fetch("SELECT MAX(batch) as last_batch FROM {$this->migrations_table}");
        return $result['last_batch'] ?? 0;
    }

    private function logMigration($name, $batch) {
        $this->db->insert($this->migrations_table, [
            'migration_name' => $name,
            'batch' => $batch
        ]);
    }

    public function rollback($steps = 1) {
        $batch = $this->getLastBatch();
        $migrations = $this->db->fetchAll(
            "SELECT * FROM {$this->migrations_table} WHERE batch >= ? ORDER BY id DESC",
            [$batch - $steps + 1]
        );

        foreach ($migrations as $migration) {
            try {
                // Get rollback SQL from the migration file
                $sql = file_get_contents(ROOT_PATH . '/database/migrations/' . $migration['migration_name']);
                $matches = [];
                if (preg_match('/-- Rollback(.+)/s', $sql, $matches)) {
                    $rollback_sql = trim($matches[1]);
                    $queries = array_filter(array_map('trim', explode(';', $rollback_sql)));
                    
                    foreach ($queries as $query) {
                        if (!empty($query)) {
                            $this->db->query($query);
                        }
                    }
                    
                    $this->db->delete($this->migrations_table, "id = {$migration['id']}");
                    echo "Rolled back: {$migration['migration_name']}\n";
                }
            } catch (\Exception $e) {
                echo "Error rolling back {$migration['migration_name']}: {$e->getMessage()}\n";
                break;
            }
        }
    }

    public function reset() {
        $migrations = $this->getExecutedMigrations();
        foreach (array_reverse($migrations) as $migration) {
            try {
                $sql = file_get_contents(ROOT_PATH . '/database/migrations/' . $migration['migration_name']);
                $matches = [];
                if (preg_match('/-- Rollback(.+)/s', $sql, $matches)) {
                    $rollback_sql = trim($matches[1]);
                    $queries = array_filter(array_map('trim', explode(';', $rollback_sql)));
                    
                    foreach ($queries as $query) {
                        if (!empty($query)) {
                            $this->db->query($query);
                        }
                    }
                }
            } catch (\Exception $e) {
                echo "Error resetting {$migration['migration_name']}: {$e->getMessage()}\n";
                break;
            }
        }
        
        $this->db->query("TRUNCATE TABLE {$this->migrations_table}");
        echo "Database reset completed\n";
    }
}