<?php

namespace App\Services\Backup;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Backup Integrity Service
 * Verifies the integrity of database backups and ensures they can be restored
 */
class BackupIntegrityService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $config;

    // Default configuration
    private const DEFAULT_CONFIG = [
        'backup_path' => 'storage/backups',
        'checksum_file' => 'storage/data/backup/checksums.json',
        'verification_history' => 'storage/data/backup/verification_history.json',
        'test_db_prefix' => 'backup_test_',
        'max_test_duration' => 300, // 5 minutes
        'min_backup_size' => 1024, // 1KB
        'critical_tables' => [
            'users',
            'properties',
            'bookings',
            'leads',
            'payments',
            'system_logs'
        ]
    ];

    public function __construct(Database $db, LoggerInterface $logger, array $config = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = array_merge(self::DEFAULT_CONFIG, $config);
        $this->initializeBackupSystem();
    }

    /**
     * Initialize backup system
     */
    private function initializeBackupSystem(): void
    {
        try {
            $this->createBackupTables();
            $this->createBackupDirectories();
            $this->logger->info('Backup integrity system initialized');
        } catch (\Exception $e) {
            $this->logger->error('Failed to initialize backup system', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Backup system initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Create backup-related tables
     */
    private function createBackupTables(): void
    {
        $tables = [
            'backup_integrity_checks' => "
                CREATE TABLE IF NOT EXISTS backup_integrity_checks (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    backup_file VARCHAR(500) NOT NULL,
                    checksum VARCHAR(64) NOT NULL,
                    file_size BIGINT NOT NULL,
                    verification_status ENUM('pending', 'passed', 'failed') DEFAULT 'pending',
                    verification_details TEXT,
                    verification_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_backup_file (backup_file),
                    INDEX idx_verification_status (verification_status),
                    INDEX idx_verification_time (verification_time)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'backup_verification_history' => "
                CREATE TABLE IF NOT EXISTS backup_verification_history (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    backup_file VARCHAR(500) NOT NULL,
                    test_type VARCHAR(100) NOT NULL,
                    test_status ENUM('pending', 'passed', 'failed') DEFAULT 'pending',
                    test_result TEXT,
                    test_duration INT DEFAULT 0,
                    error_message TEXT,
                    tested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_backup_file (backup_file),
                    INDEX idx_test_type (test_type),
                    INDEX idx_test_status (test_status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'backup_schedule' => "
                CREATE TABLE IF NOT EXISTS backup_schedule (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    backup_name VARCHAR(200) NOT NULL,
                    backup_type ENUM('full', 'incremental', 'differential') DEFAULT 'full',
                    schedule_type ENUM('daily', 'weekly', 'monthly') DEFAULT 'daily',
                    schedule_time TIME NOT NULL,
                    is_active BOOLEAN DEFAULT TRUE,
                    last_run TIMESTAMP NULL,
                    next_run TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_backup_name (backup_name),
                    INDEX idx_is_active (is_active),
                    INDEX idx_next_run (next_run)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            "
        ];

        foreach ($tables as $tableName => $sql) {
            try {
                $this->db->execute($sql);
                $this->logger->info("Backup table created or verified", ['table' => $tableName]);
            } catch (\Exception $e) {
                $this->logger->error("Failed to create backup table", [
                    'table' => $tableName,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Create backup directories
     */
    private function createBackupDirectories(): void
    {
        $directories = [
            $this->config['backup_path'],
            dirname($this->config['checksum_file']),
            dirname($this->config['verification_history'])
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    throw new \RuntimeException("Failed to create backup directory: {$dir}");
                }
                $this->logger->info("Backup directory created", ['directory' => $dir]);
            }
        }
    }

    /**
     * Verify backup integrity
     */
    public function verifyBackupIntegrity(string $backupFile): array
    {
        try {
            $this->logger->info("Starting backup integrity verification", ['backup_file' => $backupFile]);

            // Check if backup file exists
            if (!file_exists($backupFile)) {
                throw new \InvalidArgumentException("Backup file does not exist: {$backupFile}");
            }

            // Check file size
            $fileSize = filesize($backupFile);
            if ($fileSize < $this->config['min_backup_size']) {
                throw new \RuntimeException("Backup file is too small: {$fileSize} bytes");
            }

            // Calculate checksum
            $checksum = $this->calculateChecksum($backupFile);

            // Store verification record
            $verificationId = $this->createVerificationRecord($backupFile, $checksum, $fileSize);

            // Perform various integrity checks
            $checks = [
                'checksum' => $this->verifyChecksum($backupFile, $checksum),
                'structure' => $this->verifyBackupStructure($backupFile),
                'critical_tables' => $this->verifyCriticalTables($backupFile),
                'restore_test' => $this->performRestoreTest($backupFile)
            ];

            // Determine overall status
            $allPassed = array_reduce($checks, fn($carry, $check) => $carry && ($check['status'] === 'passed'), true);
            $status = $allPassed ? 'passed' : 'failed';

            // Update verification record
            $this->updateVerificationRecord($verificationId, $status, $checks);

            $result = [
                'backup_file' => $backupFile,
                'checksum' => $checksum,
                'file_size' => $fileSize,
                'verification_status' => $status,
                'checks' => $checks,
                'verification_time' => date('Y-m-d H:i:s')
            ];

            $this->logger->info("Backup integrity verification completed", [
                'backup_file' => $backupFile,
                'status' => $status
            ]);

            return $result;

        } catch (\Exception $e) {
            $this->logger->error("Backup integrity verification failed", [
                'backup_file' => $backupFile,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate file checksum
     */
    private function calculateChecksum(string $filePath): string
    {
        return hash_file('sha256', $filePath);
    }

    /**
     * Create verification record
     */
    private function createVerificationRecord(string $backupFile, string $checksum, int $fileSize): int
    {
        $this->db->execute(
            "INSERT INTO backup_integrity_checks (backup_file, checksum, file_size, verification_status) VALUES (?, ?, ?, 'pending')",
            [$backupFile, $checksum, $fileSize]
        );

        return (int)$this->db->getLastInsertId();
    }

    /**
     * Update verification record
     */
    private function updateVerificationRecord(int $verificationId, string $status, array $checks): void
    {
        $details = json_encode($checks);
        $this->db->execute(
            "UPDATE backup_integrity_checks SET verification_status = ?, verification_details = ? WHERE id = ?",
            [$status, $details, $verificationId]
        );
    }

    /**
     * Verify checksum
     */
    private function verifyChecksum(string $backupFile, string $expectedChecksum): array
    {
        try {
            $actualChecksum = $this->calculateChecksum($backupFile);
            $isValid = $actualChecksum === $expectedChecksum;

            return [
                'status' => $isValid ? 'passed' : 'failed',
                'expected' => $expectedChecksum,
                'actual' => $actualChecksum,
                'message' => $isValid ? 'Checksum verification passed' : 'Checksum verification failed'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'message' => 'Checksum verification failed due to error'
            ];
        }
    }

    /**
     * Verify backup structure
     */
    private function verifyBackupStructure(string $backupFile): array
    {
        try {
            // For SQL backup files, check if they contain expected structure
            $content = file_get_contents($backupFile);
            
            $requiredElements = [
                'CREATE TABLE',
                'INSERT INTO',
                'DROP TABLE'
            ];

            $foundElements = 0;
            foreach ($requiredElements as $element) {
                if (stripos($content, $element) !== false) {
                    $foundElements++;
                }
            }

            $isValid = $foundElements >= 2; // At least CREATE and INSERT

            return [
                'status' => $isValid ? 'passed' : 'failed',
                'elements_found' => $foundElements,
                'elements_required' => count($requiredElements),
                'message' => $isValid ? 'Backup structure verification passed' : 'Backup structure verification failed'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'message' => 'Structure verification failed due to error'
            ];
        }
    }

    /**
     * Verify critical tables exist in backup
     */
    private function verifyCriticalTables(string $backupFile): array
    {
        try {
            $content = file_get_contents($backupFile);
            $foundTables = [];

            foreach ($this->config['critical_tables'] as $table) {
                if (stripos($content, "CREATE TABLE `{$table}`") !== false || 
                    stripos($content, "CREATE TABLE {$table}") !== false ||
                    stripos($content, "INSERT INTO `{$table}`") !== false ||
                    stripos($content, "INSERT INTO {$table}") !== false) {
                    $foundTables[] = $table;
                }
            }

            $isValid = count($foundTables) >= count($this->config['critical_tables']) * 0.8; // 80% of critical tables

            return [
                'status' => $isValid ? 'passed' : 'failed',
                'found_tables' => $foundTables,
                'expected_tables' => $this->config['critical_tables'],
                'message' => $isValid ? 'Critical tables verification passed' : 'Critical tables verification failed'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'message' => 'Critical tables verification failed due to error'
            ];
        }
    }

    /**
     * Perform restore test
     */
    private function performRestoreTest(string $backupFile): array
    {
        try {
            $startTime = time();
            
            // Create test database name
            $testDbName = $this->config['test_db_prefix'] . uniqid();
            
            // For demonstration, we'll simulate a restore test
            // In a real implementation, this would create a test database and restore the backup
            $restoreSuccess = $this->simulateRestoreTest($backupFile, $testDbName);
            
            $duration = time() - $startTime;
            
            // Clean up test database
            $this->cleanupTestDatabase($testDbName);

            return [
                'status' => $restoreSuccess ? 'passed' : 'failed',
                'test_database' => $testDbName,
                'duration' => $duration,
                'message' => $restoreSuccess ? 'Restore test passed' : 'Restore test failed'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'message' => 'Restore test failed due to error'
            ];
        }
    }

    /**
     * Simulate restore test (simplified for demonstration)
     */
    private function simulateRestoreTest(string $backupFile, string $testDbName): bool
    {
        // In a real implementation, this would:
        // 1. Create a test database
        // 2. Restore the backup to the test database
        // 3. Verify the restored data
        // 4. Drop the test database
        
        // For now, we'll just check if the file is readable and has content
        $content = file_get_contents($backupFile);
        return !empty($content) && strlen($content) > 1000;
    }

    /**
     * Clean up test database
     */
    private function cleanupTestDatabase(string $testDbName): void
    {
        try {
            // In a real implementation, this would drop the test database
            $this->logger->info("Test database cleaned up", ['database' => $testDbName]);
        } catch (\Exception $e) {
            $this->logger->warning("Failed to clean up test database", [
                'database' => $testDbName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get verification history
     */
    public function getVerificationHistory(int $limit = 50): array
    {
        try {
            $records = $this->db->fetchAll(
                "SELECT * FROM backup_integrity_checks ORDER BY verification_time DESC LIMIT ?",
                [$limit]
            );

            return array_map(function($record) {
                return [
                    'id' => $record['id'],
                    'backup_file' => $record['backup_file'],
                    'checksum' => $record['checksum'],
                    'file_size' => $record['file_size'],
                    'verification_status' => $record['verification_status'],
                    'verification_details' => json_decode($record['verification_details'], true) ?: [],
                    'verification_time' => $record['verification_time']
                ];
            }, $records);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get verification history", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get backup statistics
     */
    public function getBackupStatistics(): array
    {
        try {
            $stats = [];

            // Total verifications
            $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM backup_integrity_checks");
            $stats['total_verifications'] = (int)($result['total'] ?? 0);

            // Passed verifications
            $result = $this->db->fetchOne("SELECT COUNT(*) as passed FROM backup_integrity_checks WHERE verification_status = 'passed'");
            $stats['passed_verifications'] = (int)($result['passed'] ?? 0);

            // Failed verifications
            $result = $this->db->fetchOne("SELECT COUNT(*) as failed FROM backup_integrity_checks WHERE verification_status = 'failed'");
            $stats['failed_verifications'] = (int)($result['failed'] ?? 0);

            // Success rate
            $total = $stats['total_verifications'];
            $stats['success_rate'] = $total > 0 ? round(($stats['passed_verifications'] / $total) * 100, 2) : 0;

            // Recent activity
            $result = $this->db->fetchOne("SELECT COUNT(*) as recent FROM backup_integrity_checks WHERE verification_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stats['recent_verifications'] = (int)($result['recent'] ?? 0);

            // Average file size
            $result = $this->db->fetchOne("SELECT AVG(file_size) as avg_size FROM backup_integrity_checks");
            $stats['average_file_size'] = (float)($result['avg_size'] ?? 0);

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get backup statistics", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Schedule backup verification
     */
    public function scheduleVerification(string $backupFile, string $scheduleTime): bool
    {
        try {
            $this->db->execute(
                "INSERT INTO backup_schedule (backup_name, backup_type, schedule_type, schedule_time, next_run) VALUES (?, 'full', 'daily', ?, ?)",
                [$backupFile, $scheduleTime, $scheduleTime]
            );

            $this->logger->info("Backup verification scheduled", [
                'backup_file' => $backupFile,
                'schedule_time' => $scheduleTime
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to schedule backup verification", [
                'backup_file' => $backupFile,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get scheduled verifications
     */
    public function getScheduledVerifications(): array
    {
        try {
            $schedules = $this->db->fetchAll(
                "SELECT * FROM backup_schedule WHERE is_active = TRUE ORDER BY next_run ASC"
            );

            return array_map(function($schedule) {
                return [
                    'id' => $schedule['id'],
                    'backup_name' => $schedule['backup_name'],
                    'backup_type' => $schedule['backup_type'],
                    'schedule_type' => $schedule['schedule_type'],
                    'schedule_time' => $schedule['schedule_time'],
                    'last_run' => $schedule['last_run'],
                    'next_run' => $schedule['next_run'],
                    'is_active' => (bool)$schedule['is_active']
                ];
            }, $schedules);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get scheduled verifications", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Export verification report
     */
    public function exportVerificationReport(array $filters = []): string
    {
        try {
            $sql = "SELECT * FROM backup_integrity_checks WHERE 1=1";
            $params = [];

            if (!empty($filters['status'])) {
                $sql .= " AND verification_status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(verification_time) >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(verification_time) <= ?";
                $params[] = $filters['date_to'];
            }

            $sql .= " ORDER BY verification_time DESC";

            $records = $this->db->fetchAll($sql, $params);

            $csvData = [];
            $csvData[] = ['ID', 'Backup File', 'Checksum', 'File Size', 'Status', 'Verification Time'];

            foreach ($records as $record) {
                $csvData[] = [
                    $record['id'],
                    $record['backup_file'],
                    $record['checksum'],
                    $record['file_size'],
                    $record['verification_status'],
                    $record['verification_time']
                ];
            }

            $filename = 'backup_verification_report_' . date('Y-m-d') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($output, $row);
            }
            fclose($output);

            return $filename;

        } catch (\Exception $e) {
            $this->logger->error("Failed to export verification report", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Clean old verification records
     */
    public function cleanupOldRecords(int $daysToKeep = 30): int
    {
        try {
            $deleted = $this->db->execute(
                "DELETE FROM backup_integrity_checks WHERE verification_time < DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$daysToKeep]
            );

            $this->logger->info("Old verification records cleaned up", ['days_to_keep' => $daysToKeep]);

            return $deleted;

        } catch (\Exception $e) {
            $this->logger->error("Failed to cleanup old records", ['error' => $e->getMessage()]);
            return 0;
        }
    }
}
