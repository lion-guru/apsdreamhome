<?php
/**
 * Automated Backup System
 * Handles database and file backups with rotation and compression
 */

namespace App\Core;

class BackupManager
{
    private static $instance = null;
    private $backupDir;
    private $maxBackups = 10;
    private $compressionEnabled = true;

    private function __construct()
    {
        $this->backupDir = __DIR__ . '/../backups/';
        $this->ensureBackupDirectory();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Create full system backup
     */
    public function createFullBackup($includeFiles = true)
    {
        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "full_backup_{$timestamp}";

        $backupData = [
            'timestamp' => $timestamp,
            'type' => 'full',
            'database' => $this->backupDatabase(),
            'files' => $includeFiles ? $this->backupFiles() : null,
            'system_info' => $this->getSystemInfo()
        ];

        $backupFile = $this->backupDir . $backupName . '.json';

        if (file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT))) {
            // Compress if enabled
            if ($this->compressionEnabled) {
                $this->compressBackup($backupFile, $backupName);
                unlink($backupFile); // Remove uncompressed version
            }

            // Rotate old backups
            $this->rotateBackups();

            return [
                'success' => true,
                'backup_name' => $backupName,
                'size' => $this->getBackupSize($backupName),
                'message' => 'Full backup created successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to create backup'
        ];
    }

    /**
     * Create database-only backup
     */
    public function createDatabaseBackup()
    {
        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "database_backup_{$timestamp}";

        $backupData = [
            'timestamp' => $timestamp,
            'type' => 'database',
            'database' => $this->backupDatabase(),
            'system_info' => $this->getSystemInfo()
        ];

        $backupFile = $this->backupDir . $backupName . '.json';

        if (file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT))) {
            if ($this->compressionEnabled) {
                $this->compressBackup($backupFile, $backupName);
                unlink($backupFile);
            }

            return [
                'success' => true,
                'backup_name' => $backupName,
                'message' => 'Database backup created successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to create database backup'
        ];
    }

    /**
     * Backup database structure and data
     */
    private function backupDatabase()
    {
        try {
            global $pdo;

            if (!$pdo) {
                return ['error' => 'Database connection not available'];
            }

            $tables = [];
            $stmt = $pdo->query("SHOW TABLES");
            $tableNames = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tableNames as $tableName) {
                // Get table structure
                $createStmt = $pdo->query("SHOW CREATE TABLE `$tableName`");
                $createTable = $createStmt->fetch(\PDO::FETCH_ASSOC);

                $tableData = [
                    'name' => $tableName,
                    'structure' => $createTable['Create Table'],
                    'data' => []
                ];

                // Get table data (limit to prevent memory issues)
                $countStmt = $pdo->query("SELECT COUNT(*) as count FROM `$tableName`");
                $rowCount = $countStmt->fetch(\PDO::FETCH_ASSOC)['count'];

                if ($rowCount < 10000) { // Only backup small tables to prevent memory issues
                    $dataStmt = $pdo->query("SELECT * FROM `$tableName`");
                    $tableData['data'] = $dataStmt->fetchAll(\PDO::FETCH_ASSOC);
                } else {
                    $tableData['data_count'] = $rowCount;
                    $tableData['note'] = 'Table too large for automatic backup';
                }

                $tables[] = $tableData;
            }

            return [
                'success' => true,
                'tables' => $tables,
                'table_count' => count($tables),
                'backup_date' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Backup important files
     */
    private function backupFiles()
    {
        $importantPaths = [
            'config/',
            'app/core/',
            '.env',
            '.htaccess',
            'index.php'
        ];

        $backedUpFiles = [];

        foreach ($importantPaths as $path) {
            $fullPath = __DIR__ . '/../' . $path;

            if (is_file($fullPath)) {
                $backedUpFiles[$path] = [
                    'content' => base64_encode(file_get_contents($fullPath)),
                    'size' => filesize($fullPath),
                    'modified' => date('Y-m-d H:i:s', filemtime($fullPath))
                ];
            } elseif (is_dir($fullPath)) {
                $backedUpFiles[$path] = $this->backupDirectory($fullPath, $path);
            }
        }

        return [
            'success' => true,
            'files' => $backedUpFiles,
            'backup_date' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Backup directory recursively
     */
    private function backupDirectory($dirPath, $relativePath, $maxDepth = 3)
    {
        if (!is_dir($dirPath) || $maxDepth <= 0) {
            return null;
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dirPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getSize() < 1024 * 1024) { // Max 1MB per file
                $relativeFilePath = $relativePath . '/' . $iterator->getSubPathName();
                $files[$relativeFilePath] = [
                    'content' => base64_encode($file->getContents()),
                    'size' => $file->getSize()
                ];
            }
        }

        return $files;
    }

    /**
     * Compress backup file
     */
    private function compressBackup($filePath, $backupName)
    {
        if (!extension_loaded('zlib')) {
            return false;
        }

        $compressedFile = $this->backupDir . $backupName . '.gz';
        $data = file_get_contents($filePath);

        if (file_put_contents($compressedFile, gzencode($data, 9))) {
            return true;
        }

        return false;
    }

    /**
     * Rotate old backups (keep only recent ones)
     */
    private function rotateBackups()
    {
        $backupFiles = glob($this->backupDir . '*.gz');
        $backupFiles = array_merge($backupFiles, glob($this->backupDir . '*.json'));

        // Sort by modification time (newest first)
        usort($backupFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Keep only the latest backups
        if (count($backupFiles) > $this->maxBackups) {
            $filesToDelete = array_slice($backupFiles, $this->maxBackups);

            foreach ($filesToDelete as $file) {
                unlink($file);
            }
        }
    }

    /**
     * List available backups
     */
    public function listBackups()
    {
        $backupFiles = glob($this->backupDir . '*.gz');
        $backupFiles = array_merge($backupFiles, glob($this->backupDir . '*.json'));

        $backups = [];
        foreach ($backupFiles as $file) {
            $filename = basename($file);
            $backups[] = [
                'filename' => $filename,
                'size' => filesize($file),
                'created' => date('Y-m-d H:i:s', filemtime($file)),
                'type' => pathinfo($file, PATHINFO_EXTENSION) === 'gz' ? 'compressed' : 'uncompressed'
            ];
        }

        // Sort by creation date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['created']) - strtotime($a['created']);
        });

        return $backups;
    }

    /**
     * Restore from backup
     */
    public function restoreBackup($backupName)
    {
        $backupFile = $this->backupDir . $backupName;

        if (!file_exists($backupFile)) {
            return [
                'success' => false,
                'message' => 'Backup file not found'
            ];
        }

        // Read backup data
        $data = file_get_contents($backupFile);

        if (pathinfo($backupFile, PATHINFO_EXTENSION) === 'gz') {
            $data = gzdecode($data);
        }

        $backupData = json_decode($data, true);

        if (!$backupData) {
            return [
                'success' => false,
                'message' => 'Invalid backup file format'
            ];
        }

        $result = [
            'success' => true,
            'restored' => [],
            'errors' => []
        ];

        // Restore database if included
        if (isset($backupData['database']) && $backupData['database']['success']) {
            $dbResult = $this->restoreDatabase($backupData['database']);
            if ($dbResult['success']) {
                $result['restored'][] = 'database';
            } else {
                $result['errors'][] = 'Database: ' . $dbResult['message'];
            }
        }

        // Restore files if included
        if (isset($backupData['files']) && $backupData['files']['success']) {
            $filesResult = $this->restoreFiles($backupData['files']);
            if ($filesResult['success']) {
                $result['restored'][] = 'files';
            } else {
                $result['errors'][] = 'Files: ' . $filesResult['message'];
            }
        }

        $result['message'] = count($result['restored']) . ' components restored successfully';
        if (!empty($result['errors'])) {
            $result['message'] .= ', ' . count($result['errors']) . ' errors occurred';
        }

        return $result;
    }

    /**
     * Restore database from backup
     */
    private function restoreDatabase($databaseBackup)
    {
        try {
            global $pdo;

            if (!$pdo) {
                return ['success' => false, 'message' => 'Database connection not available'];
            }

            // Start transaction
            $pdo->beginTransaction();

            foreach ($databaseBackup['tables'] as $table) {
                $tableName = $table['name'];

                // Drop existing table
                $pdo->exec("DROP TABLE IF EXISTS `$tableName`");

                // Create table structure
                $pdo->exec($table['structure']);

                // Insert data if available
                if (isset($table['data']) && !empty($table['data'])) {
                    $columns = array_keys($table['data'][0]);
                    $placeholders = array_fill(0, count($columns), '?');
                    $columnsStr = '`' . implode('`, `', $columns) . '`';
                    $placeholdersStr = implode(', ', $placeholders);

                    $insertStmt = $pdo->prepare("INSERT INTO `$tableName` ($columnsStr) VALUES ($placeholdersStr)");

                    foreach ($table['data'] as $row) {
                        $insertStmt->execute(array_values($row));
                    }
                }
            }

            $pdo->commit();

            return [
                'success' => true,
                'message' => 'Database restored successfully',
                'tables_restored' => count($databaseBackup['tables'])
            ];

        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'success' => false,
                'message' => 'Database restore failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Restore files from backup
     */
    private function restoreFiles($filesBackup)
    {
        $restored = 0;
        $errors = 0;

        foreach ($filesBackup['files'] as $filePath => $fileData) {
            $fullPath = __DIR__ . '/../' . $filePath;

            // Create directory if needed
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            if (file_put_contents($fullPath, base64_decode($fileData['content']))) {
                $restored++;
            } else {
                $errors++;
            }
        }

        return [
            'success' => $errors === 0,
            'message' => "$restored files restored, $errors errors",
            'files_restored' => $restored,
            'errors' => $errors
        ];
    }

    /**
     * Get system information for backup metadata
     */
    private function getSystemInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'os' => PHP_OS,
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database' => 'MySQL',
            'app_version' => APP_VERSION ?? '2.1',
            'backup_date' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get backup size
     */
    private function getBackupSize($backupName)
    {
        $backupFile = $this->backupDir . $backupName . '.gz';

        if (file_exists($backupFile)) {
            return filesize($backupFile);
        }

        $backupFile = $this->backupDir . $backupName . '.json';
        if (file_exists($backupFile)) {
            return filesize($backupFile);
        }

        return 0;
    }

    /**
     * Ensure backup directory exists
     */
    private function ensureBackupDirectory()
    {
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * Clean old backups
     */
    public function cleanupOldBackups($daysToKeep = 30)
    {
        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);
        $deleted = 0;

        $backupFiles = glob($this->backupDir . '*.gz');
        $backupFiles = array_merge($backupFiles, glob($this->backupDir . '*.json'));

        foreach ($backupFiles as $file) {
            if (filemtime($file) < $cutoffTime) {
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }

        return [
            'success' => true,
            'deleted_count' => $deleted,
            'message' => "$deleted old backups removed"
        ];
    }

    /**
     * Get backup statistics
     */
    public function getBackupStats()
    {
        $backups = $this->listBackups();
        $totalSize = 0;
        $totalBackups = count($backups);

        foreach ($backups as $backup) {
            $totalSize += $backup['size'];
        }

        return [
            'total_backups' => $totalBackups,
            'total_size' => $this->formatBytes($totalSize),
            'average_size' => $totalBackups > 0 ? $this->formatBytes($totalSize / $totalBackups) : '0 B',
            'oldest_backup' => $totalBackups > 0 ? $backups[$totalBackups - 1]['created'] : 'None',
            'newest_backup' => $totalBackups > 0 ? $backups[0]['created'] : 'None'
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

/**
 * Global backup functions
 */
function backup_manager()
{
    return BackupManager::getInstance();
}

function create_backup($type = 'full')
{
    $backup = BackupManager::getInstance();

    if ($type === 'database') {
        return $backup->createDatabaseBackup();
    }

    return $backup->createFullBackup();
}

function list_backups()
{
    return BackupManager::getInstance()->listBackups();
}

function restore_backup($backupName)
{
    return BackupManager::getInstance()->restoreBackup($backupName);
}

?>
