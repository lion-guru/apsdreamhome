<?php
/**
 * Advanced Database Schema Version Control System
 * Provides comprehensive tracking, versioning, and management of database schema
 */
class DatabaseSchemaVersionControl {
    private $dbConnection;
    private $schemaHistoryTable = 'schema_version_history';
    private $migrationDirectory;
    private $configFile;
    private $logDirectory;
    private $config;

    /**
     * Constructor initializes database schema version control
     * @param PDO $dbConnection Database connection
     * @param string $projectRoot Project root directory
     */
    public function __construct(PDO $dbConnection, $projectRoot = null) {
        $this->dbConnection = $dbConnection;
        $this->projectRoot = $projectRoot ?? __DIR__;
        $this->migrationDirectory = $this->projectRoot . '/database/migrations';
        $this->configFile = $this->projectRoot . '/config/db_schema_version_config.json';
        $this->logDirectory = $this->projectRoot . '/logs/schema_version_control';

        $this->setupDirectories();
        $this->loadConfiguration();
        $this->initializeSchemaHistoryTable();
    }

    /**
     * Create necessary directories
     */
    private function setupDirectories() {
        $directories = [
            dirname($this->configFile),
            $this->migrationDirectory,
            $this->logDirectory
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        // Create default config if not exists
        $defaultConfigPath = $this->projectRoot . '/config/db_schema_version_config.json';
        if (!file_exists($defaultConfigPath)) {
            $defaultConfig = [
                'migration_naming_pattern' => '^\\d{14}_[a-z0-9_]+\\.(sql|php)$',
                'backup_before_migration' => true,
                'max_migration_history' => 50,
                'allowed_migration_types' => ['sql', 'php'],
                'migration_directories' => [
                    'database/migrations',
                    'database/schema_changes'
                ],
                'backup_directory' => 'backups/schema_migrations',
                'report_directory' => 'reports/schema_diff',
                'logging' => [
                    'log_directory' => 'logs/schema_version_control',
                    'log_level' => 'info'
                ],
                'security' => [
                    'require_checksum_verification' => true,
                    'prevent_duplicate_migrations' => true
                ]
            ];
            
            file_put_contents(
                $defaultConfigPath, 
                json_encode($defaultConfig, JSON_PRETTY_PRINT)
            );
        }
    }

    /**
     * Load or create configuration
     */
    private function loadConfiguration() {
        $defaultConfig = [
            'migration_naming_pattern' => '/^\d{14}_[a-z0-9_]+\.sql$/',
            'backup_before_migration' => true,
            'max_migration_history' => 50,
            'allowed_migration_types' => ['sql', 'php']
        ];

        if (!file_exists($this->configFile)) {
            file_put_contents(
                $this->configFile, 
                json_encode($defaultConfig, JSON_PRETTY_PRINT)
            );
            $this->config = $defaultConfig;
        } else {
            $this->config = json_decode(file_get_contents($this->configFile), true);
            $this->config = array_merge_recursive($defaultConfig, $this->config);
        }
    }

    /**
     * Initialize schema history tracking table
     */
    private function initializeSchemaHistoryTable() {
        $createTableQuery = "
            CREATE TABLE IF NOT EXISTS {$this->schemaHistoryTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration_name VARCHAR(255) NOT NULL,
                applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                execution_time FLOAT,
                status ENUM('success', 'failed') NOT NULL,
                error_message TEXT,
                user VARCHAR(100),
                checksum VARCHAR(64)
            )
        ";

        $this->dbConnection->exec($createTableQuery);
    }

    /**
     * Create a new migration script
     * @param string $migrationName Migration name
     * @param string $type Migration type (sql or php)
     * @return string Path to created migration script
     */
    public function createMigration($migrationName, $type = 'sql') {
        // Validate migration type
        if (!in_array($type, $this->config['allowed_migration_types'])) {
            throw new InvalidArgumentException("Invalid migration type");
        }

        // Generate timestamp-based filename
        $timestamp = date('YmdHis');
        $sanitizedName = preg_replace('/[^a-z0-9_]/', '_', strtolower($migrationName));
        $filename = "{$timestamp}_{$sanitizedName}.{$type}";
        $filepath = $this->migrationDirectory . '/' . $filename;

        // Create migration template
        $templateContent = $this->generateMigrationTemplate($type, $migrationName);
        
        file_put_contents($filepath, $templateContent);

        $this->log('Migration created', [
            'name' => $migrationName,
            'type' => $type,
            'file' => $filepath
        ]);

        return $filepath;
    }

    /**
     * Generate migration template
     * @param string $type Migration type
     * @param string $migrationName Migration name
     * @return string Migration template content
     */
    private function generateMigrationTemplate($type, $migrationName) {
        switch ($type) {
            case 'sql':
                return "-- Migration: {$migrationName}
-- Created: " . date('Y-m-d H:i:s') . "

-- Up migration
START TRANSACTION;

-- Add your database schema changes here
-- Example: ALTER TABLE users ADD COLUMN new_column VARCHAR(255);

COMMIT;

-- Down migration (optional)
-- START TRANSACTION;
-- Rollback changes if needed
-- ROLLBACK;
";
            case 'php':
                return "<?php
/**
 * Migration: {$migrationName}
 * Created: " . date('Y-m-d H:i:s') . "
 */
class Migration_" . str_replace(' ', '_', ucwords($migrationName)) . " {
    private \$db;

    public function __construct(PDO \$db) {
        \$this->db = \$db;
    }

    /**
     * Apply migration
     */
    public function up() {
        try {
            // Perform database schema changes
            // Example: 
            // \$this->db->exec('ALTER TABLE users ADD COLUMN new_column VARCHAR(255)');
        } catch (PDOException \$e) {
            throw new Exception('Migration up failed: ' . \$e->getMessage());
        }
    }

    /**
     * Rollback migration
     */
    public function down() {
        try {
            // Rollback database schema changes
            // Example:
            // \$this->db->exec('ALTER TABLE users DROP COLUMN new_column');
        } catch (PDOException \$e) {
            throw new Exception('Migration down failed: ' . \$e->getMessage());
        }
    }
}
";
            default:
                throw new InvalidArgumentException("Unsupported migration type");
        }
    }

    /**
     * Apply pending migrations
     * @return array Applied migrations
     */
    public function applyMigrations() {
        $pendingMigrations = $this->getPendingMigrations();
        $appliedMigrations = [];

        if (empty($pendingMigrations)) {
            $this->log('No pending migrations');
            return $appliedMigrations;
        }

        // Optional: Create backup before migrations
        if ($this->config['backup_before_migration']) {
            $this->createDatabaseBackup();
        }

        foreach ($pendingMigrations as $migration) {
            try {
                $startTime = microtime(true);
                
                // Determine migration type and apply
                $migrationPath = $this->migrationDirectory . '/' . $migration;
                $migrationType = pathinfo($migrationPath, PATHINFO_EXTENSION);

                switch ($migrationType) {
                    case 'sql':
                        $this->applySQLMigration($migrationPath);
                        break;
                    case 'php':
                        $this->applyPHPMigration($migrationPath);
                        break;
                    default:
                        throw new Exception("Unsupported migration type");
                }

                $executionTime = microtime(true) - $startTime;

                // Record migration in history
                $this->recordMigrationHistory($migration, 'success', $executionTime);
                $appliedMigrations[] = $migration;

                $this->log('Migration applied', [
                    'migration' => $migration,
                    'execution_time' => $executionTime
                ]);

            } catch (Exception $e) {
                // Record failed migration
                $this->recordMigrationHistory($migration, 'failed', 0, $e->getMessage());
                
                $this->log('Migration failed', [
                    'migration' => $migration,
                    'error' => $e->getMessage()
                ]);

                // Stop further migrations on error
                break;
            }
        }

        return $appliedMigrations;
    }

    /**
     * Get pending migrations
     * @return array Pending migration files
     */
    private function getPendingMigrations() {
        // Validate migration directory
        if (!is_dir($this->migrationDirectory)) {
            throw new Exception("Migration directory not found");
        }

        // Get all migration files
        $migrationFiles = glob($this->migrationDirectory . '/*');
        $appliedMigrations = $this->getAppliedMigrations();

        // Filter out already applied migrations
        $pendingMigrations = array_filter(
            array_map('basename', $migrationFiles),
            function($migration) use ($appliedMigrations) {
                // Validate migration filename
                if (!preg_match($this->config['migration_naming_pattern'], $migration)) {
                    return false;
                }

                return !in_array($migration, $appliedMigrations);
            }
        );

        // Sort migrations chronologically
        sort($pendingMigrations);

        return $pendingMigrations;
    }

    /**
     * Get applied migrations from history
     * @return array Applied migration names
     */
    private function getAppliedMigrations() {
        $query = "SELECT migration_name FROM {$this->schemaHistoryTable} WHERE status = 'success'";
        $stmt = $this->dbConnection->query($query);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Apply SQL migration
     * @param string $migrationPath Path to SQL migration file
     */
    private function applySQLMigration($migrationPath) {
        $sqlContent = file_get_contents($migrationPath);
        
        // Split SQL statements
        $statements = array_filter(array_map('trim', explode(';', $sqlContent)));

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $this->dbConnection->exec($statement);
            }
        }
    }

    /**
     * Apply PHP migration
     * @param string $migrationPath Path to PHP migration file
     */
    private function applyPHPMigration($migrationPath) {
        require_once $migrationPath;
        
        $className = 'Migration_' . basename($migrationPath, '.php');
        $migrationInstance = new $className($this->dbConnection);
        
        // Apply migration
        $migrationInstance->up();
    }

    /**
     * Record migration history
     * @param string $migrationName Migration name
     * @param string $status Migration status
     * @param float $executionTime Execution time
     * @param string $errorMessage Error message (optional)
     */
    private function recordMigrationHistory($migrationName, $status, $executionTime, $errorMessage = null) {
        $query = "
            INSERT INTO {$this->schemaHistoryTable} 
            (migration_name, status, execution_time, error_message, user, checksum) 
            VALUES (:name, :status, :time, :error, :user, :checksum)
        ";

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            ':name' => $migrationName,
            ':status' => $status,
            ':time' => $executionTime,
            ':error' => $errorMessage,
            ':user' => get_current_user(),
            ':checksum' => hash_file('sha256', $this->migrationDirectory . '/' . $migrationName)
        ]);

        // Manage migration history size
        $this->manageMigrationHistorySize();
    }

    /**
     * Manage migration history size
     */
    private function manageMigrationHistorySize() {
        $maxHistory = $this->config['max_migration_history'];
        
        $query = "
            DELETE FROM {$this->schemaHistoryTable}
            WHERE id NOT IN (
                SELECT id FROM (
                    SELECT id 
                    FROM {$this->schemaHistoryTable}
                    ORDER BY applied_at DESC
                    LIMIT :max_history
                ) AS recent_migrations
            )
        ";

        $stmt = $this->dbConnection->prepare($query);
        $stmt->bindParam(':max_history', $maxHistory, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Create database backup
     */
    private function createDatabaseBackup() {
        $backupDir = $this->projectRoot . '/backups/schema_migrations';
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupFilename = 'db_backup_' . date('YmdHis') . '.sql';
        $backupPath = $backupDir . '/' . $backupFilename;

        // Use mysqldump for backup
        $dbName = $this->dbConnection->query("SELECT DATABASE()")->fetchColumn();
        $command = sprintf(
            "mysqldump -u %s -p%s %s > %s",
            getenv('DB_USER') ?: 'root',
            getenv('DB_PASS') ?: '',
            $dbName,
            $backupPath
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception("Database backup failed");
        }

        $this->log('Database backup created', [
            'backup_file' => $backupPath
        ]);
    }

    /**
     * Generate schema diff report
     * @return string Path to diff report
     */
    public function generateSchemaDiffReport() {
        $reportDir = $this->projectRoot . '/reports/schema_diff';
        
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }

        $reportFilename = 'schema_diff_' . date('YmdHis') . '.txt';
        $reportPath = $reportDir . '/' . $reportFilename;

        // Get current schema
        $currentSchema = $this->getCurrentSchema();

        // Get previous migrations
        $previousMigrations = $this->getAppliedMigrations();

        // Compare and generate diff
        $diffReport = "Schema Difference Report - " . date('Y-m-d H:i:s') . "\n\n";
        $diffReport .= "Applied Migrations: " . count($previousMigrations) . "\n";
        $diffReport .= "Current Schema Tables:\n";
        
        foreach ($currentSchema as $table => $structure) {
            $diffReport .= "\nTable: {$table}\n";
            $diffReport .= "Columns:\n";
            foreach ($structure['columns'] as $column => $details) {
                $diffReport .= "  - {$column}: {$details}\n";
            }
        }

        file_put_contents($reportPath, $diffReport);

        $this->log('Schema diff report generated', [
            'report_file' => $reportPath
        ]);

        return $reportPath;
    }

    /**
     * Get current database schema
     * @return array Current schema details
     */
    private function getCurrentSchema() {
        $schema = [];
        
        // Get all tables
        $tablesQuery = "SHOW TABLES";
        $tables = $this->dbConnection->query($tablesQuery)->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // Skip schema history table
            if ($table === $this->schemaHistoryTable) {
                continue;
            }

            // Get table columns
            $columnsQuery = "DESCRIBE `{$table}`";
            $columns = $this->dbConnection->query($columnsQuery)->fetchAll(PDO::FETCH_ASSOC);

            $tableSchema = ['columns' => []];
            foreach ($columns as $column) {
                $tableSchema['columns'][$column['Field']] = 
                    "{$column['Type']} " . 
                    ($column['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . 
                    ($column['Key'] ? " ({$column['Key']})" : '');
            }

            $schema[$table] = $tableSchema;
        }

        return $schema;
    }

    /**
     * Log messages
     * @param string $message Log message
     * @param array $context Additional context
     */
    private function log($message, $context = []) {
        $logFile = $this->logDirectory . '/schema_version_control_' . date('Y-m-d') . '.log';
        
        $logEntry = sprintf(
            "[%s] %s\n%s\n\n",
            date('Y-m-d H:i:s'),
            $message,
            json_encode($context, JSON_PRETTY_PRINT)
        );

        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}

// Example usage if run directly
if (php_sapi_name() === 'cli') {
    try {
        // Database connection (replace with your actual connection details)
        $dsn = 'mysql:host=localhost;dbname=your_database';
        $username = 'your_username';
        $password = 'your_password';
        
        $dbConnection = new PDO($dsn, $username, $password);
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $schemaVersionControl = new DatabaseSchemaVersionControl($dbConnection);

        // Create a new migration
        $migrationPath = $schemaVersionControl->createMigration('Add User Roles');

        // Apply pending migrations
        $appliedMigrations = $schemaVersionControl->applyMigrations();

        // Generate schema diff report
        $reportPath = $schemaVersionControl->generateSchemaDiffReport();

        echo "Migration process completed.\n";
        echo "Created migration: {$migrationPath}\n";
        echo "Applied migrations: " . count($appliedMigrations) . "\n";
        echo "Schema diff report: {$reportPath}\n";

    } catch (Exception $e) {
        echo "Schema version control failed: " . $e->getMessage() . "\n";
    }
} else {
    // Web interface for reporting
    try {
        // Database connection (replace with your actual connection details)
        $dsn = 'mysql:host=localhost;dbname=your_database';
        $username = 'your_username';
        $password = 'your_password';
        
        $dbConnection = new PDO($dsn, $username, $password);
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $schemaVersionControl = new DatabaseSchemaVersionControl($dbConnection);

        // Generate and display schema diff report
        $reportPath = $schemaVersionControl->generateSchemaDiffReport();
        echo file_get_contents($reportPath);

    } catch (Exception $e) {
        echo "Report generation failed: " . $e->getMessage();
    }
}
