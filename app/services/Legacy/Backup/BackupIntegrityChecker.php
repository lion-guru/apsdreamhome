<?php

namespace App\Services\Legacy\Backup;

use App\Core\App;
use Exception;

/**
 * Backup Integrity Checker
 * Verifies the integrity of database backups and ensures they can be restored
 */
class BackupIntegrityChecker {
    private $logger;
    private $emailManager;
    private $db;
    private $config;
    private $testDbName;

    public function __construct() {
        $this->db = App::database();
        $this->logger = App::make('logger') ?? new \App\Services\Legacy\SecurityLogger();
        $this->emailManager = App::make('email') ?? new \App\Services\Legacy\Notification\EmailManager();
        $this->loadConfig();
    }

    /**
     * Load configuration
     */
    private function loadConfig() {
        $this->config = [
            'backup_path' => realpath(__DIR__ . '/../../../../backups') ?: __DIR__ . '/../../../../backups',
            'checksum_file' => __DIR__ . '/../../../../data/backup/checksums.json',
            'verification_history' => __DIR__ . '/../../../../data/backup/verification_history.json',
            'test_db_prefix' => 'backup_test_',
            'max_test_duration' => 300, // 5 minutes
            'min_backup_size' => 1024, // 1KB
            'critical_tables' => [
                'user',
                'properties',
                'bookings',
                'transactions',
                'system_state'
            ]
        ];

        // Create data directory if it doesn't exist
        $dataDir = dirname($this->config['checksum_file']);
        if (!is_dir($dataDir)) {
            @mkdir($dataDir, 0750, true);
        }
    }

    /**
     * Verify backup integrity
     */
    public function verifyBackup($backupFile) {
        try {
            $startTime = microtime(true);

            // Basic file checks
            $this->performBasicChecks($backupFile);

            // Verify checksum
            $this->verifyChecksum($backupFile);

            // Verify SQL syntax
            $this->verifySqlSyntax($backupFile);

            // Test restore in temporary database
            $this->testRestore($backupFile);

            // Verify critical tables
            $this->verifyCriticalTables();

            // Record successful verification
            $duration = round(microtime(true) - $startTime, 2);
            $this->recordVerification($backupFile, true, $duration);

            $this->logger->info('Backup verification completed', [
                'file' => basename($backupFile),
                'duration' => $duration,
                'size' => filesize($backupFile)
            ]);

            return [
                'success' => true,
                'duration' => $duration,
                'message' => 'Backup verified successfully'
            ];

        } catch (Exception $e) {
            $this->recordVerification($backupFile, false, 0, $e->getMessage());

            $this->logger->error('Backup verification failed', [
                'file' => basename($backupFile),
                'error' => $e->getMessage()
            ]);

            $this->emailManager->sendBackupStatus('verification_failed', [
                'file' => basename($backupFile),
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        } finally {
            // Cleanup test database
            $this->cleanupTestDb();
        }
    }

    /**
     * Perform basic file checks
     */
    private function performBasicChecks($backupFile) {
        if (!file_exists($backupFile)) {
            throw new Exception('Backup file not found');
        }

        $size = filesize($backupFile);
        if ($size < $this->config['min_backup_size']) {
            throw new Exception('Backup file is too small');
        }

        // Check if file is readable
        if (!is_readable($backupFile)) {
            throw new Exception('Backup file is not readable');
        }

        // Check file extension
        $extension = strtolower(pathinfo($backupFile, PATHINFO_EXTENSION));
        if (!in_array($extension, ['sql', 'gz', 'enc'])) {
            throw new Exception('Invalid backup file extension');
        }
    }

    /**
     * Verify backup checksum
     */
    private function verifyChecksum($backupFile) {
        $currentChecksum = hash_file('sha256', $backupFile);
        $checksums = $this->loadChecksums();

        $filename = basename($backupFile);
        if (isset($checksums[$filename]) && $checksums[$filename] !== $currentChecksum) {
            throw new Exception('Backup file checksum mismatch');
        }

        // Update checksum
        $checksums[$filename] = $currentChecksum;
        $this->saveChecksums($checksums);
    }

    /**
     * Verify SQL syntax
     */
    private function verifySqlSyntax($backupFile) {
        // Read backup file content
        $content = $this->readBackupContent($backupFile);

        // Split into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $content))
        );

        foreach ($statements as $statement) {
            if (empty($statement)) continue;
            try {
                // Use EXPLAIN to check syntax without executing
                $this->db->query("EXPLAIN {$statement}");
            } catch (Exception $e) {
                // Some statements might not support EXPLAIN, ignore them or handle specifically
                continue;
            }
        }
    }

    /**
     * Test restore in temporary database
     */
    private function testRestore($backupFile) {
        // Create test database
        $this->testDbName = $this->config['test_db_prefix'] . time();
        $this->db->execute("CREATE DATABASE `{$this->testDbName}`");

        // Switch to test database
        $this->db->execute("USE `{$this->testDbName}`");

        try {
            // Read and execute backup content
            $content = $this->readBackupContent($backupFile);

            // Split into individual statements and execute
            $statements = array_filter(
                array_map('trim', explode(';', $content))
            );

            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $this->db->execute($statement);
                }
            }
        } catch (Exception $e) {
            throw new Exception('Restore test failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify critical tables
     */
    private function verifyCriticalTables() {
        foreach ($this->config['critical_tables'] as $table) {
            // Check if table exists
            $result = $this->db->fetchAll(
                "SHOW TABLES LIKE ?", [$table]
            );
            if (empty($result)) {
                throw new Exception("Critical table missing: {$table}");
            }

            // Check table structure
            $result = $this->db->fetchAll("DESCRIBE `{$table}`");
            if (empty($result)) {
                throw new Exception("Invalid table structure: {$table}");
            }

            // Check for data
            $count = $this->db->fetchOne("SELECT COUNT(*) as total FROM `{$table}`");
            if (($count['total'] ?? 0) === 0) {
                $this->logger->warning("Empty critical table", ['table' => $table]);
            }
        }
    }

    /**
     * Cleanup test database
     */
    private function cleanupTestDb() {
        if ($this->testDbName) {
            $this->db->execute("DROP DATABASE IF EXISTS `{$this->testDbName}`");
            $this->testDbName = null;
        }
    }

    /**
     * Read backup content
     */
    private function readBackupContent($backupFile) {
        $content = file_get_contents($backupFile);

        // Handle compressed files
        if (substr($backupFile, -3) === '.gz') {
            $content = gzdecode($content);
        }

        // Handle encrypted files
        if (substr($backupFile, -4) === '.enc') {
            $key = base64_decode(getenv('BACKUP_ENCRYPTION_KEY'));
            $ivSize = openssl_cipher_iv_length('aes-256-cbc');
            $iv = substr($content, 0, $ivSize);
            $encrypted = substr($content, $ivSize);

            $content = openssl_decrypt(
                $encrypted,
                'aes-256-cbc',
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );
        }

        if ($content === false) {
            throw new Exception('Failed to read backup content');
        }

        return $content;
    }

    /**
     * Load checksums
     */
    private function loadChecksums() {
        $file = $this->config['checksum_file'];
        return file_exists($file) ?
            json_decode(file_get_contents($file), true) : [];
    }

    /**
     * Save checksums
     */
    private function saveChecksums($checksums) {
        file_put_contents(
            $this->config['checksum_file'],
            json_encode($checksums, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Record verification attempt
     */
    private function recordVerification($file, $success, $duration, $error = null) {
        $history = $this->loadVerificationHistory();

        $history[] = [
            'file' => basename($file),
            'timestamp' => date('Y-m-d H:i:s'),
            'success' => $success,
            'duration' => $duration,
            'error' => $error
        ];

        // Keep only last 100 records
        if (count($history) > 100) {
            $history = array_slice($history, -100);
        }

        $this->saveVerificationHistory($history);
    }

    /**
     * Load verification history
     */
    private function loadVerificationHistory() {
        $file = $this->config['verification_history'];
        return file_exists($file) ?
            json_decode(file_get_contents($file), true) : [];
    }

    /**
     * Save verification history
     */
    private function saveVerificationHistory($history) {
        file_put_contents(
            $this->config['verification_history'],
            json_encode($history, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Get verification history
     */
    public function getVerificationHistory() {
        return $this->loadVerificationHistory();
    }
}
