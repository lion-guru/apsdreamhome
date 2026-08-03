<?php

namespace App\Services;

use App\Core\Database\Database;

use \App\Traits\ServiceTenantTrait;

/**
 * Backup & Restore Service - Automated Database Backup
 * Full backup, incremental backup, and restore functionality
 */
class BackupRestoreService
{
    use \App\Traits\ServiceTenantTrait;

    private $database;
    private $backupPath;
    private $retentionDays = 30;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->backupPath = STORAGE_PATH . '/backups/';
        
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
        
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure backup tracking table exists
     */
    private function ensureTablesExist(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS system_backups (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            backup_type ENUM('full', 'incremental', 'partial') NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_size BIGINT NOT NULL,
            checksum VARCHAR(64) NOT NULL,
            tables_backed JSON NULL,
            started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            completed_at TIMESTAMP NULL,
            status ENUM('running', 'completed', 'failed') DEFAULT 'running',
            error_message TEXT NULL,
            created_by INT NULL,
            INDEX idx_status (status),
            INDEX idx_type (backup_type),
            INDEX idx_created_at (started_at),
            INDEX idx_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->database->getConnection()->exec($sql);
    }
    
    /**
     * Create full database backup
     */
    public function createFullBackup(?int $createdBy = null): array
    {
        try {
            $backupId = $this->logBackupStart('full', $createdBy);
            $filename = 'full_backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $this->backupPath . $filename;
            
            // Get all tables
            $tables = $this->getAllTables();
            
            // Create backup file
            $handle = fopen($filepath, 'w');
            
            // Write header
            fwrite($handle, "-- APS Dream Home Full Backup\n");
            fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
            fwrite($handle, "-- Database: apsdreamhome\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n\n");
            
            foreach ($tables as $table) {
                $this->backupTable($table, $handle);
            }
            
            fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n");
            fclose($handle);
            
            // Calculate checksum and size
            $checksum = hash_file('sha256', $filepath);
            $fileSize = filesize($filepath);
            
            // Compress backup
            $compressedPath = $this->compressBackup($filepath);
            
            // Update log
            $this->logBackupComplete($backupId, $compressedPath, $fileSize, $checksum, $tables);
            
            // Clean old backups
            $this->cleanOldBackups();
            
            return [
                'success' => true,
                'backup_id' => $backupId,
                'file' => $compressedPath,
                'size' => $this->formatBytes($fileSize),
                'tables' => count($tables),
                'checksum' => $checksum
            ];
            
        } catch (\Exception $e) {
            $this->logBackupFailed($backupId ?? 0, $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Backup single table
     */
    private function backupTable(string $table, $handle): void
    {
        // Write drop and create statements
        fwrite($handle, "-- Table: {$table}\n");
        fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
        
        $create = $this->database->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
        $createSql = $create['Create Table'] ?? $create['Create Table'] ?? '';
        fwrite($handle, $createSql . ";\n\n");
        
        // Get data
        $rows = $this->database->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
        
        if (empty($rows)) {
            return;
        }
        
        // Write insert statements in batches
        $columns = array_keys($rows[0]);
        $batchSize = 100;
        $batches = array_chunk($rows, $batchSize);
        
        foreach ($batches as $batch) {
            fwrite($handle, "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES\n");
            
            $values = [];
            foreach ($batch as $row) {
                $rowValues = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $rowValues[] = 'NULL';
                    } else {
                        $rowValues[] = "'" . addslashes($value) . "'";
                    }
                }
                $values[] = "(" . implode(", ", $rowValues) . ")";
            }
            
            fwrite($handle, implode(",\n", $values) . ";\n\n");
        }
    }
    
    /**
     * Get all database tables
     */
    private function getAllTables(): array
    {
        $tables = [];
        $result = $this->database->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        
        foreach ($result as $table) {
            $tables[] = $table;
        }
        
        return $tables;
    }
    
    /**
     * Compress backup file
     */
    private function compressBackup(string $filepath): string
    {
        $compressedPath = $filepath . '.gz';
        
        // Use gzip compression
        $input = fopen($filepath, 'rb');
        $output = gzopen($compressedPath, 'wb9');
        
        while (!feof($input)) {
            gzwrite($output, fread($input, 1024 * 512));
        }
        
        fclose($input);
        gzclose($output);
        
        // Remove uncompressed file
        unlink($filepath);
        
        return $compressedPath;
    }
    
    /**
     * Restore from backup
     */
    public function restoreBackup(string $backupFile): array
    {
        try {
            if (!file_exists($backupFile)) {
                return ['success' => false, 'error' => 'Backup file not found'];
            }
            
            // Decompress if needed
            $sqlFile = $backupFile;
            if (strpos($backupFile, '.gz') !== false) {
                $sqlFile = $this->decompressBackup($backupFile);
            }
            
            // Read and execute SQL
            $sql = file_get_contents($sqlFile);
            
            // Split by semicolons and execute
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            $executed = 0;
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $this->database->exec($statement);
                    $executed++;
                }
            }
            
            // Clean up temp file
            if ($sqlFile !== $backupFile) {
                unlink($sqlFile);
            }
            
            return [
                'success' => true,
                'statements_executed' => $executed,
                'restored_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Decompress backup
     */
    private function decompressBackup(string $filepath): string
    {
        $outputPath = str_replace('.gz', '', $filepath);
        
        $input = gzopen($filepath, 'rb');
        $output = fopen($outputPath, 'wb');
        
        while (!gzeof($input)) {
            fwrite($output, gzread($input, 1024 * 512));
        }
        
        gzclose($input);
        fclose($output);
        
        return $outputPath;
    }
    
    /**
     * List available backups
     */
    public function listBackups(): array
    {
        try {
            $tsql = $this->tenantSql();
            $tparam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
            $sql = "SELECT * FROM system_backups{$tsql} ORDER BY started_at DESC";
            $backups = $this->database->query($sql, $tparam)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        $backups = [];
        }
        
        foreach ($backups as &$backup) {
            $backup['file_size_formatted'] = $this->formatBytes($backup['file_size']);
            $backup['tables_backed'] = json_decode($backup['tables_backed'] ?? '[]', true);
        }
        
        return $backups;
    }
    
    /**
     * Schedule automated backups
     */
    public function scheduleBackup(string $type = 'full', string $frequency = 'daily', 
                                   string $time = '02:00', ?int $createdBy = null): array
    {
        // Create cron job entry
        $cronFile = APP_PATH . '/../config/cron_jobs.php';
        $jobs = file_exists($cronFile) ? include $cronFile : [];
        
        $jobs[] = [
            'type' => $type,
            'frequency' => $frequency,
            'time' => $time,
            'created_by' => $createdBy,
            'created_at' => date('Y-m-d H:i:s'),
            'active' => true
        ];
        
        file_put_contents($cronFile, '<?php return ' . var_export($jobs, true) . ';');
        
        return [
            'success' => true,
            'message' => "Scheduled {$type} backup {$frequency} at {$time}"
        ];
    }
    
    /**
     * Clean old backups
     */
    private function cleanOldBackups(): void
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$this->retentionDays} days"));
        $tsql = $this->tenantSql();
        $tparam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        
        try {
            $sql = "SELECT * FROM system_backups WHERE started_at < ? AND status = 'completed'{$tsql}";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute(array_merge([$cutoff], $tparam));
        $oldBackups = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($oldBackups as $backup) {
            if (file_exists($backup['file_path'])) {
                unlink($backup['file_path']);
            }
            
            $this->database->execute(
                "DELETE FROM system_backups WHERE id = ?{$tsql}",
                array_merge([$backup['id']], $tparam)
            );
        }
    }
    
    /**
     * Log backup start
     */
    private function logBackupStart(string $type, ?int $createdBy): int
    {
        try {
            $tenantData = $this->tenantInsertData();
            $cols = array_merge(['backup_type', 'file_path', 'file_size', 'checksum', 'created_by'], array_keys($tenantData));
            $vals = array_merge([$type, '', 0, '', $createdBy], array_values($tenantData));
            $colStr = implode(', ', $cols);
            $placeholders = implode(', ', array_fill(0, count($vals), '?'));
            $sql = "INSERT INTO system_backups ($colStr) VALUES ($placeholders)";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute($vals);
        
        return $this->database->lastInsertId();
    }
    
    /**
     * Log backup completion
     */
    private function logBackupComplete(int $backupId, string $filepath, int $fileSize, 
                                        string $checksum, array $tables): void
    {
        $tsql = $this->tenantSql();
        $tparam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        $sql = "UPDATE system_backups 
                SET file_path = ?, file_size = ?, checksum = ?, tables_backed = ?, 
                    status = 'completed', completed_at = NOW() 
                WHERE id = ?{$tsql}";
        $stmt = $this->database->prepare($sql);
        $stmt->execute(array_merge([$filepath, $fileSize, $checksum, json_encode($tables), $backupId], $tparam));
    }
    
    /**
     * Log backup failure
     */
    private function logBackupFailed(int $backupId, string $error): void
    {
        $tsql = $this->tenantSql();
        $tparam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        try {
            $sql = "UPDATE system_backups 
                    SET status = 'failed', error_message = ? 
                    WHERE id = ?{$tsql}";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute(array_merge([$error, $backupId], $tparam));
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
