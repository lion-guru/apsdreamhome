<?php
/**
 * S3RecordingStorage — AWS S3 storage for voice call recordings
 * Provides secure, scalable storage with lifecycle policies
 */

namespace App\Services\Providers;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class S3RecordingStorage
{
    use ServiceTenantTrait;

    private $db;
    private $s3Client = null;
    private $bucket = '';
    private $region = '';
    private $keyPrefix = 'voice-recordings/';

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadConfig();
    }

    /**
     * Load S3 configuration from service_configs
     */
    private function loadConfig(): void
    {
        $config = $this->db->fetch(
            "SELECT * FROM service_configs WHERE group_name = 'aws' AND service_name = 's3' LIMIT 1"
        );
        
        if ($config) {
            $this->bucket = $config['config_value']['bucket'] ?? '';
            $this->region = $config['config_value']['region'] ?? 'ap-south-1';
            $this->accessKey = $config['config_value']['access_key'] ?? '';
            $this->secretKey = $config['config_value']['secret_key'] ?? '';
            $this->keyPrefix = $config['config_value']['key_prefix'] ?? 'voice-recordings/';
        }
    }

    /**
     * Initialize S3 client
     */
    private function getS3Client()
    {
        if ($this->s3Client) {
            return $this->s3Client;
        }

        if (empty($this->accessKey) || empty($this->secretKey) || empty($this->bucket)) {
            return null;
        }

        // Check if AWS SDK is available
        if (!class_exists('Aws\S3\S3Client')) {
            error_log('AWS SDK not installed. Run: composer require aws/aws-sdk-php');
            return null;
        }

        $this->s3Client = new \Aws\S3\S3Client([
            'region' => $this->region,
            'version' => 'latest',
            'credentials' => [
                'key' => $this->accessKey,
                'secret' => $this->secretKey,
            ],
        ]);

        return $this->s3Client;
    }

    /**
     * Upload recording to S3
     */
    public function uploadRecording(string $localPath, string $callSid, string $provider): array
    {
        $s3 = $this->getS3Client();
        if (!$s3) {
            return ['success' => false, 'error' => 'S3 not configured'];
        }

        $key = $this->generateKey($callSid, $provider);
        
        try {
            $result = $s3->putObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'SourceFile' => $localPath,
                'ContentType' => 'audio/mpeg',
                'Metadata' => [
                    'call-sid' => $callSid,
                    'provider' => $provider,
                    'uploaded-at' => date('c'),
                ],
                'ServerSideEncryption' => 'AES256',
                'StorageClass' => 'STANDARD_IA', // Cost-effective for recordings
            ]);

            // Store reference in database
            $this->storeRecordingReference($callSid, $provider, $key, $result['ObjectURL'] ?? '');

            return [
                'success' => true,
                's3_key' => $key,
                's3_url' => $result['ObjectURL'] ?? '',
                'etag' => $result['ETag'] ?? '',
            ];
        } catch (\Exception $e) {
            error_log('S3 upload failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get presigned URL for playback
     */
    public function getPresignedUrl(string $s3Key, int $expiry = 3600): ?string
    {
        $s3 = $this->getS3Client();
        if (!$s3) {
            return null;
        }

        try {
            $cmd = $s3->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key' => $s3Key,
            ]);

            $request = $s3->createPresignedRequest($cmd, '+'.$expiry.' seconds');
            return (string)$request->getUri();
        } catch (\Exception $e) {
            error_log('Presigned URL generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete recording from S3
     */
    public function deleteRecording(string $s3Key): array
    {
        $s3 = $this->getS3Client();
        if (!$s3) {
            return ['success' => false, 'error' => 'S3 not configured'];
        }

        try {
            $s3->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $s3Key,
            ]);

            // Remove database reference
            $this->db->execute(
                "DELETE FROM voice_recordings WHERE s3_key = ?",
                [$s3Key]
            );

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * List recordings for a call
     */
    public function getRecordingsForCall(string $callSid): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM voice_recordings WHERE call_sid = ? ORDER BY created_at DESC",
            [$callSid]
        );
    }

    /**
     * Get recording statistics
     */
    public function getStorageStats(): array
    {
        $stats = $this->db->fetch(
            "SELECT COUNT(*) as total_recordings, 
                    COALESCE(SUM(file_size), 0) as total_size_bytes,
                    COUNT(DISTINCT provider) as providers_count
             FROM voice_recordings"
        );

        return $stats ?: [
            'total_recordings' => 0,
            'total_size_bytes' => 0,
            'providers_count' => 0,
        ];
    }

    /**
     * Generate S3 key for recording
     */
    private function generateKey(string $callSid, string $provider): string
    {
        $date = date('Y/m/d');
        $sanitizedSid = preg_replace('/[^a-zA-Z0-9_-]/', '_', $callSid);
        return $this->keyPrefix . $provider . '/' . $date . '/' . $sanitizedSid . '.mp3';
    }

    /**
     * Store recording reference in database
     */
    private function storeRecordingReference(string $callSid, string $provider, string $s3Key, string $s3Url): void
    {
        $this->db->execute(
            "INSERT INTO voice_recordings (call_sid, provider, s3_key, s3_url, file_size, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE s3_key = VALUES(s3_key), s3_url = VALUES(s3_url), updated_at = NOW()",
            [$callSid, $provider, $s3Key, $s3Url, 0]
        );
    }

    /**
     * Check if S3 is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->accessKey) && !empty($this->secretKey) && !empty($this->bucket);
    }

    /**
     * Test S3 connection
     */
    public function testConnection(): array
    {
        $s3 = $this->getS3Client();
        if (!$s3) {
            return ['success' => false, 'error' => 'S3 not configured'];
        }

        try {
            $s3->headBucket(['Bucket' => $this->bucket]);
            return [
                'success' => true,
                'message' => 'S3 connection successful',
                'bucket' => $this->bucket,
                'region' => $this->region,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}