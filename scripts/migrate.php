<?php

/**
 * Simple Database Migration System for APS Dream Home
 * 
 * Usage:
 *   php scripts/migrate.php              # Run all pending migrations
 *   php scripts/migrate.php --status     # Show migration status
 *   php scripts/migrate.php --create "migration_name"  # Create new migration file
 */

define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

class MigrationManager
{
    private $pdo;
    private $migrationsPath;
    private $tableName = 'schema_migrations';

    public function __construct()
    {
        $this->pdo = \App\Core\Database\Database::getInstance()->getPdo();
        $this->migrationsPath = APS_ROOT . '/migrations';
        
        if (!is_dir($this->migrationsPath)) {
            mkdir($this->migrationsPath, 0755, true);
        }
        
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable()
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS {$this->tableName} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                version VARCHAR(255) NOT NULL UNIQUE,
                name VARCHAR(255) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function create(string $name): string
    {
        $timestamp = date('YmdHis');
        $version = $timestamp . '_' . strtolower(preg_replace('/[^a-z0-9]+/', '_', $name));
        $filename = "{$version}.php";
        $filepath = $this->migrationsPath . '/' . $filename;
        
        $template = <<<'PHP'
<?php

/**
 * Migration: {NAME}
 * Created: {DATE}
 */

use App\Core\Database\Database;

return [
    'up' => function (PDO $pdo) {
        // Write your UP migration here
        // Example:
        // $pdo->exec("CREATE TABLE example (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255)) ENGINE=InnoDB");
    },
    'down' => function (PDO $pdo) {
        // Write your DOWN migration here (rollback)
        // Example:
        // $pdo->exec("DROP TABLE example");
    },
];
PHP;
        
        $content = str_replace(
            ['{NAME}', '{DATE}'],
            [ucwords(str_replace('_', ' ', $name)), date('Y-m-d H:i:s')],
            $template
        );
        
        file_put_contents($filepath, $content);
        echo "Created migration: {$filepath}\n";
        return $filepath;
    }

    public function migrate(): void
    {
        $executed = $this->getExecutedMigrations();
        $migrations = $this->getMigrationFiles();
        
        $pending = array_filter($migrations, function($m) use ($executed) {
            return !in_array($m['version'], $executed);
        });
        
        if (empty($pending)) {
            echo "No pending migrations.\n";
            return;
        }
        
        echo "Running " . count($pending) . " pending migration(s)...\n";
        
        foreach ($pending as $migration) {
            echo "  Migrating: {$migration['name']}...\n";
            
            try {
                $this->pdo->beginTransaction();
                
                $migrationCode = require $migration['path'];
                if (isset($migrationCode['up']) && is_callable($migrationCode['up'])) {
                    $migrationCode['up']($this->pdo);
                }
                
                $stmt = $this->pdo->prepare(
                    "INSERT INTO {$this->tableName} (version, name) VALUES (?, ?)"
                );
                $stmt->execute([$migration['version'], $migration['name']]);
                
                $this->pdo->commit();
                echo "    ✓ Done\n";
                
            } catch (Throwable $e) {
                $this->pdo->rollBack();
                echo "    ✗ FAILED: " . $e->getMessage() . "\n";
                throw $e;
            }
        }
        
        echo "\nAll migrations completed successfully.\n";
    }

    public function rollback(string $version = null): void
    {
        $executed = $this->getExecutedMigrations();
        
        if ($version) {
            $executed = array_filter($executed, fn($v) => $v === $version);
        } else {
            $executed = array_slice($executed, -1); // Last one only
        }
        
        if (empty($executed)) {
            echo "No migrations to rollback.\n";
            return;
        }
        
        foreach (array_reverse($executed) as $version) {
            $migration = $this->findMigrationByVersion($version);
            if (!$migration) {
                echo "Migration file not found for version: {$version}\n";
                continue;
            }
            
            echo "Rolling back: {$migration['name']}...\n";
            
            try {
                $this->pdo->beginTransaction();
                
                $migrationCode = require $migration['path'];
                if (isset($migrationCode['down']) && is_callable($migrationCode['down'])) {
                    $migrationCode['down']($this->pdo);
                }
                
                $stmt = $this->pdo->prepare(
                    "DELETE FROM {$this->tableName} WHERE version = ?"
                );
                $stmt->execute([$version]);
                
                $this->pdo->commit();
                echo "  ✓ Rolled back\n";
                
            } catch (Throwable $e) {
                $this->pdo->rollBack();
                echo "  ✗ FAILED: " . $e->getMessage() . "\n";
                throw $e;
            }
        }
    }

    public function status(): void
    {
        $executed = $this->getExecutedMigrations();
        $migrations = $this->getMigrationFiles();
        
        echo "Migration Status:\n";
        echo str_repeat("-", 80) . "\n";
        
        foreach ($migrations as $m) {
            $status = in_array($m['version'], $executed) ? '✓ EXECUTED' : '○ PENDING';
            $executedAt = '';
            if (in_array($m['version'], $executed)) {
                $stmt = $this->pdo->prepare("SELECT executed_at FROM {$this->tableName} WHERE version = ?");
                $stmt->execute([$m['version']]);
                $executedAt = ' (' . $stmt->fetchColumn() . ')';
            }
            echo "{$status} | {$m['version']} | {$m['name']}{$executedAt}\n";
        }
        
        echo str_repeat("-", 80) . "\n";
        echo "Total: " . count($migrations) . " | Executed: " . count($executed) . " | Pending: " . (count($migrations) - count($executed)) . "\n";
    }

    private function getExecutedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT version FROM {$this->tableName} ORDER BY executed_at");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.php');
        $migrations = [];
        
        foreach ($files as $file) {
            $basename = basename($file, '.php');
            $parts = explode('_', $basename, 2);
            $version = $parts[0] . '_' . $parts[1];
            $name = $parts[1] ?? '';
            
            $migrations[] = [
                'version' => $version,
                'name' => $name,
                'path' => $file,
            ];
        }
        
        usort($migrations, fn($a, $b) => strcmp($a['version'], $b['version']));
        return $migrations;
    }

    private function findMigrationByVersion(string $version): ?array
    {
        $migrations = $this->getMigrationFiles();
        foreach ($migrations as $m) {
            if ($m['version'] === $version) {
                return $m;
            }
        }
        return null;
    }
}

// CLI handling
if (php_sapi_name() === 'cli') {
    $manager = new MigrationManager();
    
    $args = $argv;
    array_shift($args); // Remove script name
    
    if (empty($args) || $args[0] === 'migrate') {
        $manager->migrate();
    } elseif ($args[0] === 'rollback') {
        $version = $args[1] ?? null;
        $manager->rollback($version);
    } elseif ($args[0] === 'status') {
        $manager->status();
    } elseif ($args[0] === 'create') {
        if (!isset($args[1])) {
            echo "Usage: php migrate.php create \"migration_name\"\n";
            exit(1);
        }
        $manager->create($args[1]);
    } else {
        echo "Usage:\n";
        echo "  php migrate.php              # Run pending migrations\n";
        echo "  php migrate.php status       # Show migration status\n";
        echo "  php migrate.php rollback     # Rollback last migration\n";
        echo "  php migrate.php rollback v   # Rollback specific version\n";
        echo "  php migrate.php create name  # Create new migration\n";
    }
}