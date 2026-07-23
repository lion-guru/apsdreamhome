<?php

namespace App\Services\Backup;

use App\Core\Database\Database;
use Exception;

/**
 * Backup Integrity Service
 * Automated backup verification, checksum validation, test restore, scheduling
 */
class BackupIntegrityService
{
    private $db;
    private $config;
    
    const DEFAULT_CONFIG = [
        'backup_paths' => ['/var/backups/apsdreamhome', '/backup'],
        'checksum_file' => 'backup_checksums.json',
        'test_db_prefix' => 'test_restore_',
        'critical_tables' => [
            'users', 'leads', 'plot_bookings', 'payment_transactions',
            'mlm_commission_ledger', 'associates', 'properties',
            'booking_payment_schedules', 'penalty_audit', 'wallet_points'
        ]
    ];
    
    public function __construct(array $config = [])
    {
        $this->db = Database::getInstance();
        $this->config = array_merge(self::DEFAULT_CONFIG, $config);
        $this->initializeBackupSystem();
    }
    
    private function initializeBackupSystem(): void
    {
        $this->createBackupTables();
        $this->createBackupDirectories();
    }
    
    private function createBackupTables(): void
    {
        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS backup_verifications (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    backup_file VARCHAR(500) NOT NULL,
                    checksum VARCHAR(64) NOT NULL,
                    file_size BIGINT UNSIGNED DEFAULT 0,
                    status ENUM('pending','verified','failed','test_restored') DEFAULT 'pending',
                    checks JSON,
                    verified_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_backup_file (backup_file),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            $this->db->query("
                CREATE TABLE IF NOT EXISTS backup_schedules (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    backup_file VARCHAR(500) NOT NULL,
                    schedule_time DATETIME NOT NULL,
                    status ENUM('pending','completed','failed') DEFAULT 'pending',
                    result JSON,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_schedule_time (schedule_time)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            $this->db->query("
                CREATE TABLE IF NOT EXISTS backup_integrity_log (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    backup_file VARCHAR(500) NOT NULL,
                    check_type VARCHAR(50) NOT NULL,
                    status ENUM('passed','failed','warning') NOT NULL,
                    details JSON,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_backup_file (backup_file)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) {
            error_log('Backup tables creation error: ' . $e->getMessage());
        }
    }
    
    private function createBackupDirectories(): void
    {
        foreach ($this->config['backup_paths'] as $path) {
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }
        }
    }
    
    /**
     * Verify backup file integrity
     */
    public function verifyBackupIntegrity(string $backupFile): array
    {
        $results = [
            'file' => $backupFile,
            'checksum' => null,
            'structure' => null,
            'critical_tables' => null,
            'restore_test' => null,
            'overall_status' => 'failed'
        ];
        
        try {
            // 1. Checksum verification
            $checksumResult = $this->verifyChecksum($backupFile);
            $results['checksum'] = $checksumResult;
            
            // 2. Structure verification
            $structureResult = $this->verifyBackupStructure($backupFile);
            $results['structure'] = $structureResult;
            
            // 3. Critical tables verification
            $tablesResult = $this->verifyCriticalTables($backupFile);
            $results['critical_tables'] = $tablesResult;
            
            // 4. Restore test (optional, can be skipped for large backups)
            if ($checksumResult['passed'] && $structureResult['passed']) {
                $restoreResult = $this->performRestoreTest($backupFile);
                $results['restore_test'] = $restoreResult;
            }
            
            // Determine overall status
            $allPassed = $checksumResult['passed'] 
                && $structureResult['passed'] 
                && $tablesResult['passed']
                && ($results['restore_test']['passed'] ?? true);
            
            $results['overall_status'] = $allPassed ? 'verified' : 'failed';
            
            // Save verification record
            $this->createVerificationRecord($backupFile, $results['checksum']['details']['checksum'] ?? '', $results['checksum']['details']['file_size'] ?? 0);
            
        } catch (Exception $e) {
            $results['error'] = $e->getMessage();
            $this->logIntegrityCheck($backupFile, 'overall', 'failed', ['error' => $e->getMessage()]);
        }
        
        return $results;
    }
    
    private function calculateChecksum(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new Exception("Backup file not found: $filePath");
        }
        return hash_file('sha256', $filePath);
    }
    
    private function verifyChecksum(string $backupFile, string $expectedChecksum = null): array
    {
        $actualChecksum = $this->calculateChecksum($backupFile);
        $fileSize = filesize($backupFile);
        
        $passed = true;
        $details = ['algorithm' => 'sha256', 'checksum' => $actualChecksum, 'file_size' => $fileSize];
        
        if ($expectedChecksum) {
            $passed = $actualChecksum === $expectedChecksum;
            $details['expected'] = $expectedChecksum;
            $details['match'] = $passed;
        }
        
        // Store checksum
        $this->createVerificationRecord($backupFile, $actualChecksum, $fileSize);
        
        $this->logIntegrityCheck($backupFile, 'checksum', $passed ? 'passed' : 'failed', $details);
        
        return ['passed' => $passed, 'details' => $details];
    }
    
    private function createVerificationRecord(string $backupFile, string $checksum, int $fileSize): int
    {
        $this->db->query("
            INSERT INTO backup_verifications (backup_file, checksum, file_size, status)
            VALUES (?, ?, ?, 'verified')
        ", [$backupFile, $checksum, $fileSize]);
        
        return $this->db->lastInsertId();
    }
    
    private function updateVerificationRecord(int $verificationId, string $status, array $checks): void
    {
        $this->db->query("
            UPDATE backup_verifications 
            SET status = ?, checks = ?, verified_at = NOW()
            WHERE id = ?
        ", [$status, json_encode($checks), $verificationId]);
    }
    
    private function verifyBackupStructure(string $backupFile): array
    {
        $details = ['tables_found' => 0, 'tables_expected' => count($this->config['critical_tables']), 'missing_tables' => []];
        
        try {
            // Use mysqldump to list tables in backup
            $cmd = "gunzip -c " . escapeshellarg($backupFile) . " | head -5000 | grep 'CREATE TABLE' | sed 's/.*CREATE TABLE \`\\([^`]*\\)\\`.*/\\1/'";
            $output = shell_exec($cmd);
            
            if ($output) {
                $tables = array_filter(array_map('trim', explode("\n", $output)));
                $details['tables_found'] = count($tables);
                
                foreach ($this->config['critical_tables'] as $criticalTable) {
                    if (!in_array($criticalTable, $tables)) {
                        $details['missing_tables'][] = $criticalTable;
                    }
                }
            }
            
            $passed = empty($details['missing_tables']);
            $details['missing_count'] = count($details['missing_tables']);
            
        } catch (Exception $e) {
            $passed = false;
            $details['error'] = $e->getMessage();
        }
        
        $this->logIntegrityCheck($backupFile, 'structure', $passed ? 'passed' : 'failed', $details);
        
        return ['passed' => $passed, 'details' => $details];
    }
    
    private function verifyCriticalTables(string $backupFile): array
    {
        $details = ['verified_tables' => [], 'failed_tables' => []];
        
        try {
            // Create test database and restore backup
            $testDbName = $this->config['test_db_prefix'] . 'verify_' . time();
            $this->db->query("CREATE DATABASE `$testDbName`");
            
            // Restore to test database
            $cmd = "gunzip -c " . escapeshellarg($backupFile) . " | mysql -u root " . escapeshellarg($testDbName);
            $output = shell_exec($cmd . " 2>&1");
            
            // Check critical tables
            foreach ($this->config['critical_tables'] as $table) {
                $count = $this->db->fetch("SELECT COUNT(*) as cnt FROM `$testDbName`.`$table`")['cnt'] ?? 0;
                if ($count > 0) {
                    $details['verified_tables'][] = ['table' => $table, 'row_count' => $count];
                } else {
                    $details['failed_tables'][] = ['table' => $table, 'reason' => 'Empty or missing'];
                }
            }
            
            // Cleanup test database
            $this->db->query("DROP DATABASE `$testDbName`");
            
            $passed = empty($details['failed_tables']);
            
        } catch (Exception $e) {
            $passed = false;
            $details['error'] = $e->getMessage();
        }
        
        $this->logIntegrityCheck($backupFile, 'critical_tables', $passed ? 'passed' : 'failed', $details);
        
        return ['passed' => $passed, 'details' => $details];
    }
    
    private function performRestoreTest(string $backupFile): array
    {
        $testDbName = $this->config['test_db_prefix'] . 'restore_' . time();
        $details = ['test_db' => $testDbName];
        
        try {
            $this->db->query("CREATE DATABASE `$testDbName`");
            
            $cmd = "gunzip -c " . escapeshellarg($backupFile) . " | mysql -u root " . escapeshellarg($testDbName);
            $output = shell_exec($cmd . " 2>&1");
            
            // Verify basic structure
            $tableCount = $this->db->fetch("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = ?", [$testDbName])['cnt'] ?? 0;
            
            $details['tables_restored'] = $tableCount;
            $details['success'] = $tableCount > 0;
            
            // Cleanup
            $this->db->query("DROP DATABASE `$testDbName`");
            
            $passed = $details['success'];
            
        } catch (Exception $e) {
            $passed = false;
            $details['error'] = $e->getMessage();
        }
        
        $this->logIntegrityCheck($backupFile, 'restore_test', $passed ? 'passed' : 'failed', $details);
        
        return ['passed' => $passed, 'details' => $details];
    }
    
    private function logIntegrityCheck(string $backupFile, string $checkType, string $status, array $details): void
    {
        $this->db->query("
            INSERT INTO backup_integrity_log (backup_file, check_type, status, details)
            VALUES (?, ?, ?, ?)
        ", [$backupFile, $checkType, $status, json_encode($details)]);
    }
    
    public function getVerificationHistory(int $limit = 50): array
    {
        try {
            return $this->db->fetchAll("
                SELECT * FROM backup_verifications 
                ORDER BY created_at DESC 
                LIMIT ?
            ", [$limit]) ?? [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function getBackupStatistics(): array
    {
        try {
            $stats = $this->db->fetch("
                SELECT 
                    COUNT(*) as total_backups,
                    SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                    SUM(CASE WHEN status = 'test_restored' THEN 1 ELSE 0 END) as test_restored,
                    AVG(file_size) as avg_file_size
                FROM backup_verifications
            ");
            
            $recent = $this->db->fetchAll("
                SELECT backup_file, status, file_size, created_at
                FROM backup_verifications
                ORDER BY created_at DESC LIMIT 10
            ");
            
            return array_merge($stats ?? [], ['recent' => $recent ?? []]);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function scheduleVerification(string $backupFile, string $scheduleTime): bool
    {
        try {
            $this->db->query("
                INSERT INTO backup_schedules (backup_file, schedule_time)
                VALUES (?, ?)
            ", [$backupFile, $scheduleTime]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function getScheduledVerifications(): array
    {
        try {
            return $this->db->fetchAll("
                SELECT * FROM backup_schedules 
                WHERE status = 'pending' AND schedule_time <= NOW()
                ORDER BY schedule_time ASC
            ") ?? [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function exportVerificationReport(array $filters = []): string
    {
        $where = '1=1';
        $params = [];
        
        if (!empty($filters['status'])) {
            $where .= ' AND status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['from_date'])) {
            $where .= ' AND created_at >= ?';
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $where .= ' AND created_at <= ?';
            $params[] = $filters['to_date'];
        }
        
        $results = $this->db->fetchAll("
            SELECT * FROM backup_verifications 
            WHERE $where 
            ORDER BY created_at DESC
        ", $params);
        
        $csv = "Backup File,Status,File Size (MB),Created At,Verified At\n";
        foreach ($results as $row) {
            $csv .= sprintf(
                "\"%s\",\"%s\",%.2f,\"%s\",\"%s\"\n",
                $row['backup_file'],
                $row['status'],
                ($row['file_size'] ?? 0) / 1024 / 1024,
                $row['created_at'],
                $row['verified_at'] ?? 'N/A'
            );
        }
        
        return $csv;
    }
    
    public function cleanupOldRecords(int $daysToKeep = 30): int
    {
        try {
            $this->db->query("
                DELETE FROM backup_verifications 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ", [$daysToKeep]);
            
            $this->db->query("
                DELETE FROM backup_integrity_log 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ", [$daysToKeep]);
            
            return $this->db->rowCount();
        } catch (Exception $e) {
            return 0;
        }
    }
}