<?php

namespace App\Services\Legacy\Backup;

use App\Core\App;
use Exception;
use Aws\S3\S3Client;

/**
 * Database Backup Manager
 * Handles automated database backups, compression, and cloud storage
 */
class BackupManager {
    private $db;
    private $logger;
    private $emailManager;
    private $config;
    private $backupPath;

    public function __construct($security_logger = null, $email_manager = null) {
        $this->db = App::database();
        $this->logger = $security_logger ?? (App::make('logger') ?? new \App\Services\Legacy\SecurityLogger());
        $this->emailManager = $email_manager ?? (App::make('email') ?? new \App\Services\Legacy\Notification\EmailManager());
        $this->loadConfig();
        $this->initializeBackupDirectory();
    }

    /**
     * Load backup configuration
     */
    private function loadConfig() {
        $this->config = [
            'backup_path' => realpath(__DIR__ . '/../../../../backups') ?: __DIR__ . '/../../../../backups',
            'retention_days' => [
                'daily' => 7,    // Keep daily backups for 7 days
                'weekly' => 4,   // Keep weekly backups for 4 weeks
                'monthly' => 12  // Keep monthly backups for 12 months
            ],
            'compression' => true,
            'encrypt_backups' => true,
            'encryption_key' => getenv('BACKUP_ENCRYPTION_KEY'),
            'cloud_storage' => [
                'enabled' => getenv('CLOUD_BACKUP_ENABLED') === 'true',
                'provider' => getenv('CLOUD_STORAGE_PROVIDER') ?: 'local',
                'credentials' => [
                    'aws_key' => getenv('AWS_ACCESS_KEY_ID'),
                    'aws_secret' => getenv('AWS_SECRET_ACCESS_KEY'),
                    'aws_bucket' => getenv('AWS_BACKUP_BUCKET'),
                    'aws_region' => getenv('AWS_REGION')
                ]
            ]
        ];

        $this->backupPath = $this->config['backup_path'];
    }

    /**
     * Initialize backup directory structure
     */
    private function initializeBackupDirectory() {
        $directories = ['daily', 'weekly', 'monthly'];
        foreach ($directories as $dir) {
            $path = "{$this->backupPath}/{$dir}";
            if (!is_dir($path)) {
                @mkdir($path, 0750, true);
            }
        }
    }

    /**
     * Create database backup
     */
    public function createBackup($type = 'daily') {
        try {
            $startTime = microtime(true);
            $timestamp = date('Y-m-d_His');
            $filename = "backup_{$type}_{$timestamp}.sql";
            $filepath = "{$this->backupPath}/{$type}/{$filename}";

            // Get database credentials from environment variables
            $dbHost = getenv('DB_HOST') ?: 'localhost';
            $dbName = getenv('DB_NAME') ?: 'apsdreamhome';
            $dbUser = getenv('DB_USER') ?: 'root';
            $dbPass = getenv('DB_PASS') ?: '';

            // Create backup using mysqldump
            $command = sprintf(
                'mysqldump --host=%s --user=%s --password=%s %s > %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($filepath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new Exception("Backup failed with code {$returnCode}");
            }

            $currentFilepath = $filepath;

            // Compress backup
            if ($this->config['compression']) {
                $this->compressBackup($currentFilepath);
                @unlink($currentFilepath); // Remove uncompressed file
                $currentFilepath .= '.gz';
            }

            // Encrypt backup
            if (($this->config['encrypt_backups'] ?? false) && $this->config['encryption_key']) {
                $this->encryptBackup($currentFilepath);
                $currentFilepath .= '.enc';
            }

            // Upload to cloud if enabled
            if ($this->config['cloud_storage']['enabled']) {
                $this->uploadToCloud($currentFilepath, $type);
            }

            // Clean old backups
            $this->cleanOldBackups($type);

            // Calculate backup size and duration
            $backupSize = file_exists($currentFilepath) ? filesize($currentFilepath) : 0;
            $duration = round(microtime(true) - $startTime, 2);

            // Log success
            $this->logger->info("Database backup completed", [
                'type' => $type,
                'file' => basename($currentFilepath),
                'size' => $this->formatBytes($backupSize),
                'duration' => $duration,
                'compressed' => $this->config['compression'],
                'encrypted' => $this->config['encrypt_backups'] ?? false
            ]);

            // Send notification
            $this->emailManager->sendBackupStatus('success', [
                'type' => $type,
                'file' => basename($currentFilepath),
                'size' => $this->formatBytes($backupSize),
                'duration' => $duration . 's',
                'location' => $currentFilepath
            ]);

            return [
                'success' => true,
                'file' => $currentFilepath,
                'size' => $backupSize,
                'duration' => $duration
            ];

        } catch (Exception $e) {
            $this->logger->error("Backup failed", [
                'error' => $e->getMessage(),
                'type' => $type
            ]);

            $this->emailManager->sendBackupStatus('failed', [
                'error' => $e->getMessage(),
                'type' => $type
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Compress backup file using gzip
     */
    private function compressBackup($filepath) {
        $gzFile = $filepath . '.gz';
        $fp = gzopen($gzFile, 'w9');

        if ($fp) {
            gzwrite($fp, file_get_contents($filepath));
            gzclose($fp);

            if (!file_exists($gzFile)) {
                throw new Exception("Failed to compress backup file");
            }
        } else {
            throw new Exception("Failed to create compressed file");
        }
    }

    /**
     * Encrypt backup file
     */
    private function encryptBackup($filepath) {
        if (!extension_loaded('openssl')) {
            throw new Exception("OpenSSL extension is required for encryption");
        }

        $key = base64_decode($this->config['encryption_key']);
        $iv = \App\Helpers\SecurityHelper::secureRandomBytes(openssl_cipher_iv_length('aes-256-cbc'));
        $content = file_get_contents($filepath);
        $encrypted = openssl_encrypt(
            $content,
            'aes-256-cbc',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            throw new Exception("Failed to encrypt backup file");
        }

        $encFile = $filepath . '.enc';
        file_put_contents($encFile, $iv . $encrypted);
        @unlink($filepath); // Remove unencrypted file
    }

    /**
     * Upload backup to cloud storage
     */
    private function uploadToCloud($filepath, $type) {
        switch ($this->config['cloud_storage']['provider']) {
            case 'aws':
                $this->uploadToS3($filepath, $type);
                break;
            case 'local':
                // Already saved locally
                break;
            default:
                throw new Exception("Unsupported cloud storage provider");
        }
    }

    /**
     * Upload backup to AWS S3
     */
    private function uploadToS3($filepath, $type) {
        if (!class_exists('Aws\S3\S3Client')) {
            throw new Exception("AWS SDK is required for S3 uploads");
        }

        $credentials = $this->config['cloud_storage']['credentials'];

        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => $credentials['aws_region'],
            'credentials' => [
                'key'    => $credentials['aws_key'],
                'secret' => $credentials['aws_secret']
            ]
        ]);

        $s3->putObject([
            'Bucket' => $credentials['aws_bucket'],
            'Key'    => "{$type}/" . basename($filepath),
            'Body'   => fopen($filepath, 'r'),
            'ACL'    => 'private',
            'ServerSideEncryption' => 'AES256'
        ]);

        $this->logger->info("Backup uploaded to S3", [
            'bucket' => $credentials['aws_bucket'],
            'key' => "{$type}/" . basename($filepath)
        ]);
    }

    /**
     * Clean old backups based on retention policy
     */
    private function cleanOldBackups($type) {
        if (!isset($this->config['retention_days'][$type])) {
            return;
        }

        $retention = $this->config['retention_days'][$type];
        $directory = "{$this->backupPath}/{$type}";

        if (!is_dir($directory)) {
            return;
        }

        $files = glob("{$directory}/*");
        foreach ($files as $file) {
            if (is_file($file)) {
                $fileTime = filemtime($file);
                if ($fileTime < strtotime("-{$retention} days")) {
                    @unlink($file);
                    $this->logger->info("Deleted old backup", [
                        'file' => basename($file),
                        'type' => $type
                    ]);
                }
            }
        }
    }

    /**
     * Restore database from backup
     */
    public function restoreBackup($filepath) {
        try {
            if (!file_exists($filepath)) {
                throw new Exception("Backup file not found");
            }

            $startTime = microtime(true);
            $currentFilepath = $filepath;

            // Decrypt if necessary
            if (substr($currentFilepath, -4) === '.enc') {
                $currentFilepath = $this->decryptBackup($currentFilepath);
            }

            // Decompress if necessary
            if (substr($currentFilepath, -3) === '.gz') {
                $currentFilepath = $this->decompressBackup($currentFilepath);
            }

            // Get database credentials from environment variables
            $dbHost = getenv('DB_HOST') ?: 'localhost';
            $dbName = getenv('DB_NAME') ?: 'apsdreamhome';
            $dbUser = getenv('DB_USER') ?: 'root';
            $dbPass = getenv('DB_PASS') ?: '';

            // Restore using mysql command
            $command = sprintf(
                'mysql --host=%s --user=%s --password=%s %s < %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($currentFilepath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new Exception("Restore failed with code {$returnCode}");
            }

            $duration = round(microtime(true) - $startTime, 2);

            $this->logger->info("Database restored successfully", [
                'file' => basename($filepath),
                'duration' => $duration
            ]);

            return [
                'success' => true,
                'duration' => $duration
            ];

        } catch (Exception $e) {
            $this->logger->error("Restore failed", [
                'error' => $e->getMessage(),
                'file' => basename($filepath)
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Decrypt backup file
     */
    private function decryptBackup($filepath) {
        $key = base64_decode($this->config['encryption_key']);
        $data = file_get_contents($filepath);

        $ivSize = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivSize);
        $encrypted = substr($data, $ivSize);

        $decrypted = openssl_decrypt(
            $encrypted,
            'aes-256-cbc',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            throw new Exception("Failed to decrypt backup file");
        }

        $decFile = substr($filepath, 0, -4); // Remove .enc
        file_put_contents($decFile, $decrypted);
        return $decFile;
    }

    /**
     * Decompress backup file
     */
    private function decompressBackup($filepath) {
        $outFile = substr($filepath, 0, -3); // Remove .gz
        $sfp = gzopen($filepath, 'rb');
        $fp = fopen($outFile, 'wb');

        if (!$sfp || !$fp) {
            throw new Exception("Failed to decompress backup file");
        }

        while (!gzeof($sfp)) {
            fwrite($fp, gzread($sfp, 4096));
        }

        gzclose($sfp);
        fclose($fp);

        return $outFile;
    }

    /**
     * Format bytes to human readable size
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * List available backups
     */
    public function listBackups($type = null) {
        $backups = [];
        $types = $type ? [$type] : array_keys($this->config['retention_days']);

        foreach ($types as $t) {
            $directory = "{$this->backupPath}/{$t}";
            if (!is_dir($directory)) {
                continue;
            }

            $files = glob("{$directory}/*");
            foreach ($files as $file) {
                if (is_file($file)) {
                    $backups[] = [
                        'type' => $t,
                        'file' => basename($file),
                        'size' => $this->formatBytes(filesize($file)),
                        'date' => date('Y-m-d H:i:s', filemtime($file)),
                        'path' => $file
                    ];
                }
            }
        }

        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $backups;
    }
}
