<?php
/**
 * MultiStorageController — Multi-provider cloud storage management.
 *
 * Features:
 * - Provider configuration & health monitoring
 * - Manual/auto failover controls
 * - Provider priority management
 * - Health dashboard with real-time status
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\Storage\StorageProvider;
use App\Services\Storage\StorageHealthMonitor;
use Exception;

class MultiStorageController extends AdminController
{
    protected $provider;
    protected $healthMonitor;

    public function __construct()
    {
        parent::__construct();
        $this->provider = new \App\Services\Storage\StorageProvider();
        $this->healthMonitor = new \App\Services\Storage\StorageHealthMonitor();

        // Register all providers with health monitor
        foreach ($this->provider->all() as $provider) {
            $this->healthMonitor->registerProvider($provider->getDriver(), $provider);
        }
    }

    /**
     * Main dashboard — provider overview + health status.
     */
    public function index()
    {
        $this->requireAdmin();

        // Run health checks
        $health = $this->healthMonitor->checkAll();
        $providers = $this->provider->all();
        $activeProvider = $this->healthMonitor->getActiveProvider();

        return $this->render('admin/multi_storage/index', [
            'page_title' => 'Multi-Provider Storage Management',
            'active_page' => 'multi_storage',
            'providers' => $this->provider->all(),
            'health' => $health,
            'activeProvider' => $activeProvider,
            'providerNames' => $this->provider->providerNames(),
        ]);
    }

    /**
     * Run health check on all providers (AJAX).
     */
    public function healthCheck()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }

        $health = $this->healthMonitor->checkAll();
        return $this->jsonResponse([
            'success' => true,
            'health' => $health,
            'active_provider' => $this->healthMonitor->getActiveProvider(),
        ]);
    }

    /**
     * Force failover to a specific provider.
     */
    public function forceFailover()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }

        $target = $_POST['target'] ?? '';
        if (empty($target)) {
            return $this->jsonError('Target provider required', 400);
        }

        $ok = $this->healthMonitor->forceFailover($target);
        if (!$ok) {
            return $this->jsonError('Cannot failover to ' . $target . ' (not healthy or not registered)', 400);
        }

        $this->logActivity('storage_failover', ['target' => $target]);
        return $this->jsonResponse(['success' => true, 'message' => 'Failover initiated to ' . $target]);
    }

    /**
     * Get provider details for configuration.
     */
    public function configure(string $provider)
    {
        $this->requireAdmin();

        $config = \App\Services\ServiceConfigService::getInstance()->getApiConfig($provider);
        $driverMap = [
            'aws_s3' => 'AWS S3',
            'gcs' => 'Google Cloud Storage',
            'local_fs' => 'Local Filesystem',
            'backblaze_b2' => 'Backblaze B2',
            'cloudflare_r2' => 'Cloudflare R2',
            'azure_blob' => 'Azure Blob Storage',
        ];

        return $this->render('admin/multi_storage/configure', [
            'page_title' => 'Configure ' . ($driverMap[$provider] ?? $provider),
            'active_page' => 'multi_storage',
            'provider' => $provider,
            'driver' => $driverMap[$provider] ?? $provider,
            'config' => $config,
            'isConfigured' => !empty(array_filter($providerConfig ?? [])),
        ]);
    }
}