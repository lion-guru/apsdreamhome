<?php
/**
 * Advanced Database Backup and Migration Management System
 * Provides comprehensive database backup, migration, and version control capabilities
 */
class DatabaseBackupMigrationManager {
    private $config;
    private $configFile;
    private $backupDirectory;
    private $migrationDirectory;
    private $logFile;

    /**
     * Constructor initializes backup and migration configurations
     */
    public function __construct() {
        $this->configFile = __DIR__ . '/config/db_backup_migration_config.json';
        $this->backupDirectory = __DIR__ . '/backups/database/';
        $this->migrationDirectory = __DIR__ . '/migrations/';
        $this->logFile = __DIR__ . '/logs/db_backup_migration_' . date('Y-m-d') . '.log';

        $this->ensureDirectoriesExist();
        $this->loadConfiguration();
    }

    /**
     * Ensure required directories exist
     */
    private function ensureDirectoriesExist() {
        $directories = [
            $this->backupDirectory,
            $this->migrationDirectory,
            dirname($this->logFile),
            dirname($this->configFile)
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Load or create configuration
     */
    private function loadConfiguration() {
        $defaultConfig = [
            'database' => [
                'host' => 'localhost',
                'username' => 'your_username',
                'password' => '',
                'name' => 'your_database'
            ],
            'backup' => [
                'frequency' => 'daily',
                'max_backups' => 10,
                'compression' => true,
                'backup_tables' => '*'
            ],
            'migration' => [
                'version_control' => true,
                'auto_migrate' => false
            ]
        ];

        if (file_exists($this->configFile)) {
            $this->config = json_decode(file_get_contents($this->configFile), true);
            $this->config = array_merge_recursive($defaultConfig, $this->config);
        } else {
            $this->config = $defaultConfig;
            $this->saveConfiguration();
        }
    }

    /**
     * Save configuration to file
     */
    private function saveConfiguration() {
        file_put_contents(
            $this->configFile, 
            json_encode($this->config, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Create database backup
     * @return array Backup details
     */
    public function createBackup() {
        $this->log('Starting database backup');

        // Validate database connection
        $connection = $this->getDatabaseConnection();

        // Generate backup filename
        $backupFilename = sprintf(
            'backup_%s_%s.sql%s',
            $this->config['database']['name'],
            date('YmdHis'),
            $this->config['backup']['compression'] ? '.gz' : ''
        );
        $backupPath = $this->backupDirectory . $backupFilename;

        // Determine tables to backup
        $tables = $this->getTablesToBackup($connection);

        // Perform backup
        $backupCommand = $this->generateBackupCommand($tables, $backupPath);
        $this->executeBackupCommand($backupCommand);

        // Rotate backups
        $this->rotateBackups();

        $backupDetails = [
            'filename' => $backupFilename,
            'path' => $backupPath,
            'timestamp' => date('Y-m-d H:i:s'),
            'tables_backed_up' => $tables
        ];

        $this->log('Database backup completed', $backupDetails);
        return $backupDetails;
    }

    /**
     * Get database connection
     * @return PDO Database connection
     */
    private function getDatabaseConnection() {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $this->config['database']['host'],
                $this->config['database']['name']
            );

            $connection = new PDO(
                $dsn, 
                $this->config['database']['username'], 
                $this->config['database']['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5
                ]
            );

            return $connection;
        } catch (PDOException $e) {
            $this->log('Database connection failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get tables to backup
     * @param PDO $connection Database connection
     * @return array List of tables to backup
     */
    private function getTablesToBackup($connection) {
        $backupTables = $this->config['backup']['backup_tables'];

        if ($backupTables === '*') {
            $stmt = $connection->query('SHOW TABLES');
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        return is_array($backupTables) ? $backupTables : [$backupTables];
    }

    /**
     * Generate backup command
     * @param array $tables Tables to backup
     * @param string $backupPath Backup file path
     * @return string Backup command
     */
    private function generateBackupCommand($tables, $backupPath) {
        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s %s',
            escapeshellarg($this->config['database']['host']),
            escapeshellarg($this->config['database']['username']),
            escapeshellarg($this->config['database']['password']),
            escapeshellarg($this->config['database']['name']),
            implode(' ', array_map('escapeshellarg', $tables))
        );

        // Add compression if enabled
        if ($this->config['backup']['compression']) {
            $command .= ' | gzip > ' . escapeshellarg($backupPath);
        } else {
            $command .= ' > ' . escapeshellarg($backupPath);
        }

        return $command;
    }

    /**
     * Execute backup command
     * @param string $command Backup command
     */
    private function executeBackupCommand($command) {
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->log('Backup command failed', [
                'command' => $command,
                'output' => $output,
                'return_var' => $returnVar
            ]);
            throw new Exception('Database backup failed');
        }
    }

    /**
     * Rotate backups to manage storage
     */
    private function rotateBackups() {
        $backupFiles = glob($this->backupDirectory . 'backup_*.sql*');
        
        // Sort backups by modification time
        usort($backupFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Remove excess backups
        $filesToDelete = array_slice(
            $backupFiles, 
            $this->config['backup']['max_backups']
        );

        foreach ($filesToDelete as $file) {
            unlink($file);
            $this->log('Removed old backup', ['file' => $file]);
        }
    }

    /**
     * Create a new database migration
     * @param string $migrationName Name of the migration
     * @return string Migration file path
     */
    public function createMigration($migrationName) {
        $timestamp = date('YmdHis');
        $migrationFileName = sprintf(
            '%s_%s.sql',
            $timestamp,
            preg_replace('/[^a-zA-Z0-9_]/', '_', $migrationName)
        );
        $migrationPath = $this->migrationDirectory . $migrationFileName;

        // Create migration template
        $migrationTemplate = sprintf(
            "-- Migration: %s\n" .
            "-- Created: %s\n\n" .
            "-- Up migration\n" .
            "BEGIN;\n\n" .
            "-- Add your migration SQL here\n\n" .
            "COMMIT;\n\n" .
            "-- Down migration (optional)\n" .
            "-- BEGIN;\n" .
            "-- Rollback changes if needed\n" .
            "-- COMMIT;\n",
            $migrationName,
            date('Y-m-d H:i:s')
        );

        file_put_contents($migrationPath, $migrationTemplate);

        $this->log('Created migration', [
            'name' => $migrationName,
            'file' => $migrationPath
        ]);

        return $migrationPath;
    }

    /**
     * Apply pending migrations
     * @return array Applied migrations
     */
    public function applyMigrations() {
        if (!$this->config['migration']['auto_migrate']) {
            $this->log('Auto migration is disabled');
            return [];
        }

        $connection = $this->getDatabaseConnection();
        $appliedMigrations = $this->getAppliedMigrations($connection);
        $pendingMigrations = $this->getPendingMigrations($appliedMigrations);

        $migrationsApplied = [];
        foreach ($pendingMigrations as $migration) {
            try {
                $this->executeMigration($connection, $migration);
                $migrationsApplied[] = $migration;
            } catch (Exception $e) {
                $this->log('Migration failed', [
                    'migration' => $migration,
                    'error' => $e->getMessage()
                ]);
                break;
            }
        }

        return $migrationsApplied;
    }

    /**
     * Get list of already applied migrations
     * @param PDO $connection Database connection
     * @return array Applied migrations
     */
    private function getAppliedMigrations($connection) {
        try {
            $connection->exec("
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255) NOT NULL,
                    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");

            $stmt = $connection->query("SELECT migration FROM migrations ORDER BY applied_at");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            $this->log('Failed to retrieve applied migrations', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get list of pending migrations
     * @param array $appliedMigrations Already applied migrations
     * @return array Pending migrations
     */
    private function getPendingMigrations($appliedMigrations) {
        $migrationFiles = glob($this->migrationDirectory . '*.sql');
        $migrationNames = array_map(function($file) {
            return basename($file);
        }, $migrationFiles);

        return array_diff($migrationNames, $appliedMigrations);
    }

    /**
     * Execute a single migration
     * @param PDO $connection Database connection
     * @param string $migrationFile Migration file name
     */
    private function executeMigration($connection, $migrationFile) {
        $migrationPath = $this->migrationDirectory . $migrationFile;
        $migrationSQL = file_get_contents($migrationPath);

        try {
            $connection->beginTransaction();
            $connection->exec($migrationSQL);
            
            $stmt = $connection->prepare("INSERT INTO migrations (migration) VALUES (?)");
            $stmt->execute([$migrationFile]);
            
            $connection->commit();

            $this->log('Migration applied', ['migration' => $migrationFile]);
        } catch (PDOException $e) {
            $connection->rollBack();
            $this->log('Migration failed', [
                'migration' => $migrationFile,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Log messages
     * @param string $message Log message
     * @param array $context Additional context
     */
    private function log($message, $context = []) {
        $logEntry = sprintf(
            "[%s] %s\n%s\n\n",
            date('Y-m-d H:i:s'),
            $message,
            json_encode($context, JSON_PRETTY_PRINT)
        );

        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    /**
     * Generate comprehensive report
     * @return array Backup and migration report
     */
    public function generateReport() {
        $backupFiles = glob($this->backupDirectory . 'backup_*.sql*');
        $migrationFiles = glob($this->migrationDirectory . '*.sql');

        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'database' => $this->config['database']['name'],
            'backups' => [
                'total' => count($backupFiles),
                'latest' => !empty($backupFiles) ? basename(max($backupFiles)) : null
            ],
            'migrations' => [
                'total' => count($migrationFiles),
                'latest' => !empty($migrationFiles) ? basename(max($migrationFiles)) : null
            ]
        ];
    }

    /**
     * Generate HTML report
     * @param array $report Backup and migration report
     * @return string HTML report
     */
    public function generateHTMLReport($report) {
        $html = "<html><body>";
        $html .= "<h1>Database Backup and Migration Report</h1>";
        $html .= "<p>Timestamp: {$report['timestamp']}</p>";
        $html .= "<h2>Database: {$report['database']}</h2>";

        // Backups Section
        $html .= "<h3>Backups</h3>";
        $html .= "<ul>";
        $html .= "<li>Total Backups: {$report['backups']['total']}</li>";
        $html .= "<li>Latest Backup: " . ($report['backups']['latest'] ?? 'None') . "</li>";
        $html .= "</ul>";

        // Migrations Section
        $html .= "<h3>Migrations</h3>";
        $html .= "<ul>";
        $html .= "<li>Total Migrations: {$report['migrations']['total']}</li>";
        $html .= "<li>Latest Migration: " . ($report['migrations']['latest'] ?? 'None') . "</li>";
        $html .= "</ul>";

        $html .= "</body></html>";

        return $html;
    }
}

// Execute backup and migration tasks if run directly
if (php_sapi_name() === 'cli') {
    try {
        $dbManager = new DatabaseBackupMigrationManager();
        
        // Create backup
        $backupDetails = $dbManager->createBackup();
        
        // Apply migrations
        $appliedMigrations = $dbManager->applyMigrations();
        
        // Generate and save report
        $report = $dbManager->generateReport();
        $htmlReport = $dbManager->generateHTMLReport($report);
        file_put_contents(__DIR__ . '/logs/db_backup_migration_report.html', $htmlReport);
        
        echo "Database Backup and Migration Completed.\n";
        echo "Backup: " . $backupDetails['filename'] . "\n";
        echo "Migrations Applied: " . count($appliedMigrations) . "\n";
        echo "Report saved to: " . __DIR__ . "/logs/db_backup_migration_report.html\n";
    } catch (Exception $e) {
        echo "Database backup and migration failed: " . $e->getMessage() . "\n";
    }
} else {
    // Web interface for report
    try {
        $dbManager = new DatabaseBackupMigrationManager();
        $report = $dbManager->generateReport();
        echo $dbManager->generateHTMLReport($report);
    } catch (Exception $e) {
        echo "Report generation failed: " . $e->getMessage();
    }
}
