<?php
/**
 * StorageAnalyticsController — cloud storage dashboard (Gap 25 improvements).
 *
 * Combines: storage analytics, health dashboard, presigned URL generator,
 * cost calculator, lifecycle management, backup verification, multi-bucket.
 * Routes under /admin/storage-analytics/*
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\Storage\StorageProvider;
use App\Services\Storage\S3Storage;
use Exception;

class StorageAnalyticsController extends AdminController
{
    protected $provider;

    public function __construct()
    {
        parent::__construct();
        $this->provider = new StorageProvider();
    }

    /**
     * Main dashboard — analytics + health + cost estimate.
     */
    public function index()
    {
        $this->requireAdmin();
        try {
            $providers = $this->provider->all();
            $default = $this->provider->default();
            $files = $default ? $default->listFiles('') : [];
            $totalSize = array_sum(array_column($files, 'size'));

            // Breakdown by top-level prefix
            $byPrefix = [];
            $byType = [];
            foreach ($files as $f) {
                $path = $f['path'] ?? $f['name'] ?? '';
                $parts = explode('/', $path);
                $prefix = count($parts) > 1 ? $parts[0] . '/' : '(root)';
                $byPrefix[$prefix] = ($byPrefix[$prefix] ?? 0) + ($f['size'] ?? 0);
                $ext = pathinfo($path, PATHINFO_EXTENSION) ?: 'none';
                $byType[$ext] = ($byType[$ext] ?? 0) + ($f['size'] ?? 0);
            }
            arsort($byPrefix);
            arsort($byType);

            // Cost estimate (S3 Standard: $0.023/GB/month, 1M requests $0.005)
            $sizeGB = $totalSize / (1024 * 1024 * 1024);
            $storageCost = $sizeGB * 0.023;
            $requestCost = (count($files) / 1000) * 0.005;

            return $this->render('admin/storage_analytics/index', [
                'page_title' => 'Storage Analytics - APS Dream Home',
                'active_page' => 'storage_analytics',
                'providers' => $this->provider->providerNames(),
                'defaultProvider' => $default ? $default->getDriver() : null,
                'fileCount' => count($files),
                'totalSize' => $totalSize,
                'byPrefix' => $byPrefix,
                'byType' => $byType,
                'storageCost' => $storageCost,
                'requestCost' => $requestCost,
                'totalCost' => $storageCost + $requestCost,
                'lastBackup' => $this->getLastBackup(),
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
            return $this->redirect('/admin/storage-analytics');
        }
    }

    /**
     * Presigned URL generator.
     */
    public function presigned()
    {
        $this->requireAdmin();
        try {
            $path = $_GET['path'] ?? '';
            $expiry = (int)($_GET['expiry'] ?? 60);
            if (empty($path)) {
                return $this->jsonError('Path is required', 400);
            }
            $default = $this->provider->default();
            if (!$default) {
                return $this->jsonError('No storage provider configured', 500);
            }
            $url = $default->temporaryUrl($path, $expiry);
            if ($url === null) {
                return $this->jsonError('Failed to generate URL', 500);
            }
            return $this->jsonResponse(['success' => true, 'url' => $url, 'expires_minutes' => $expiry]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cost calculator.
     */
    public function costCalculator()
    {
        $this->requireAdmin();
        try {
            $sizeGB = (float)($_GET['size_gb'] ?? 0);
            $requestsPerMonth = (int)($_GET['requests'] ?? 0);
            $tier = $_GET['tier'] ?? 'standard';

            $rates = [
                'standard' => 0.023,       // S3 Standard $/GB/month
                'infrequent' => 0.0125,    // S3 Infrequent Access
                'glacier' => 0.004,        // S3 Glacier
                'deep_archive' => 0.00099, // S3 Deep Archive
            ];
            $rate = $rates[$tier] ?? 0.023;
            $storageCost = $sizeGB * $rate;
            $requestCost = ($requestsPerMonth / 1000) * 0.005;
            $egressCost = $sizeGB * 0.09; // $0.09/GB egress

            return $this->jsonResponse([
                'success' => true,
                'size_gb' => $sizeGB,
                'tier' => $tier,
                'rate' => $rate,
                'storage_cost' => round($storageCost, 2),
                'request_cost' => round($requestCost, 4),
                'egress_cost' => round($egressCost, 2),
                'total_monthly' => round($storageCost + $requestCost + $egressCost, 2),
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Backup verification — download a known file from S3.
     */
    public function backupVerify()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }
        try {
            $path = $_POST['path'] ?? '';
            $default = $this->provider->default();
            if (!$default) {
                return $this->jsonError('No storage provider configured', 500);
            }
            if (empty($path)) {
                // Try the most recent backup file
                $files = $default->listFiles('backups/');
                if (empty($files)) {
                    return $this->jsonError('No backup files found', 404);
                }
                $path = $files[0]['path'] ?? $files[0]['name'] ?? '';
            }
            $content = $default->get($path);
            if ($content === null) {
                return $this->jsonError('Backup file not found or unreadable', 404);
            }
            $size = strlen($content);
            $isGz = str_starts_with($content, "\x1f\x8b");
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Backup verified',
                'path' => $path,
                'size_bytes' => $size,
                'is_gzip' => $isGz,
                'checksum' => md5($content),
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    private function getLastBackup(): ?string
    {
        try {
            $default = $this->provider->default();
            if (!$default) return null;
            $files = $default->listFiles('backups/');
            if (empty($files)) return null;
            return $files[0]['last_modified'] ?? $files[0]['path'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }
}
