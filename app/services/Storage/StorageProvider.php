<?php
/**
 * StorageProvider — multi-provider cloud storage chain.
 *
 * Providers (first success wins):
 *   1. aws_s3   — AWS S3 (SigV4, existing)
 *   2. gcs      — Google Cloud Storage (JSON key auth)
 *   3. azure    — Azure Blob Storage (connection string)
 *   4. backblaze — Backblaze B2 (S3-compatible)
 *   5. r2       — Cloudflare R2 (S3-compatible, zero egress)
 *
 * All S3-compatible providers (B2, R2, MinIO) reuse S3Storage's SigV4 with a
 * custom endpoint. GCS uses JSON key auth. Azure uses a connection string.
 */
namespace App\Services\Storage;

use App\Core\Database\Database;
use App\Services\ServiceConfigService;
use App\Services\Storage\AzureBlobStorage;
use App\Services\Storage\GCSStorage;
use App\Services\Storage\LocalStorage;
use App\Services\Storage\S3Storage;

class StorageProvider
{
    private array $providers = [];

    public function __construct()
    {
        $this->registerProviders();
    }

    private function registerProviders(): void
    {
        $cfg = ServiceConfigService::getInstance();

        // AWS S3
        $s3Cfg = $cfg->getApiConfig('aws_s3');
        if (!empty($s3Cfg['access_key']) && !empty($s3Cfg['secret_key'])) {
            $this->providers[] = new S3Storage([
                'access_key' => $s3Cfg['access_key'],
                'secret_key' => $s3Cfg['secret_key'],
                'region' => $s3Cfg['region'] ?? 'ap-south-1',
                'bucket' => $s3Cfg['bucket'] ?? '',
                'endpoint' => $s3Cfg['endpoint'] ?? null,
            ]);
        }

        // Backblaze B2 (S3-compatible)
        $b2Cfg = $cfg->getApiConfig('backblaze_b2');
        if (!empty($b2Cfg['access_key']) && !empty($b2Cfg['secret_key'])) {
            $this->providers[] = new S3Storage([
                'access_key' => $b2Cfg['access_key'],
                'secret_key' => $b2Cfg['secret_key'],
                'region' => $b2Cfg['region'] ?? 'us-west-002',
                'bucket' => $b2Cfg['bucket'] ?? '',
                'endpoint' => $b2Cfg['endpoint'] ?? 'https://s3.us-west-002.backblazeb2.com',
            ]);
        }

        // Cloudflare R2 (S3-compatible, zero egress)
        $r2Cfg = $cfg->getApiConfig('cloudflare_r2');
        if (!empty($r2Cfg['access_key']) && !empty($r2Cfg['secret_key'])) {
            $this->providers[] = new S3Storage([
                'access_key' => $r2Cfg['access_key'],
                'secret_key' => $r2Cfg['secret_key'],
                'region' => $r2Cfg['region'] ?? 'auto',
                'bucket' => $r2Cfg['bucket'] ?? '',
                'endpoint' => $r2Cfg['endpoint'] ?? null,
            ]);
        }

        // Google Cloud Storage (JSON key auth via REST)
        $gcsCfg = $cfg->getApiConfig('gcs');
        if (!empty($gcsCfg['project_id']) && (!empty($gcsCfg['key_file']) || !empty($gcsCfg['key_json']))) {
            $this->providers[] = new GCSStorage($gcsCfg);
        }

        // Local Filesystem Provider (office PC / local server / NAS)
        $localCfg = $cfg->getApiConfig('local_fs');
        if (!empty($localCfg['root_path'])) {
            $this->providers[] = new LocalStorage($localCfg);
        }

        // Azure Blob Storage (full implementation)
        $azureCfg = $cfg->getApiConfig('azure_blob');
        if (!empty($azureCfg['connection_string']) && !empty($azureCfg['container'])) {
            $this->providers[] = new AzureBlobStorage($azureCfg);
        }
    }

    /**
     * Get all configured providers.
     * @return array<int, object>
     */
    public function all(): array
    {
        return $this->providers;
    }

    /**
     * Get the first working provider (default).
     */
    public function default(): ?object
    {
        return $this->providers[0] ?? null;
    }

    /**
     * Get a provider by name (aws_s3, backblaze_b2, cloudflare_r2, gcs, azure).
     */
    public function byName(string $name): ?object
    {
        foreach ($this->providers as $p) {
            if ($p->getDriver() === $name || str_contains($p->getDriver(), $name)) {
                return $p;
            }
        }
        return null;
    }

    /**
     * Get names of all configured providers.
     */
    public function providerNames(): array
    {
        $names = [];
        foreach ($this->providers as $p) {
            $names[] = $p->getDriver();
        }
        return $names;
    }
}
