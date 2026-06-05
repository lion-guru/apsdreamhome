<?php

namespace App\Http\Controllers\Admin;

use App\Services\Storage\StorageManager;
use App\Services\Storage\S3Storage;
use App\Services\Storage\LocalStorage;

/**
 * StorageGatewayController - admin UI for the storage layer.
 *
 * Routes (all admin-only):
 *   GET  /admin/storage              - index: status page
 *   POST /admin/storage/test         - test S3 connection (round-trip)
 *   GET  /admin/storage/list         - list first N objects in bucket
 *   POST /admin/storage/switch       - switch driver (local|s3) at runtime
 */
class StorageGatewayController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        $storage = StorageManager::getInstance();
        $s3 = new S3Storage();

        $info = [
            'driver'            => $storage->getDriverName(),
            'configured_driver' => strtolower((string) (getenv('STORAGE_DRIVER') ?: 'local')),
            's3_configured'     => $s3->isConfigured(),
            's3_bucket'         => getenv('AWS_BUCKET') ?: '',
            's3_region'         => getenv('AWS_DEFAULT_REGION') ?: '',
            's3_endpoint'       => getenv('AWS_ENDPOINT') ?: '',
            's3_url_expiry'     => (int) (getenv('AWS_URL_EXPIRY') ?: 60),
            's3_path_style'     => ((getenv('AWS_S3_USE_PATH_STYLE') ?: '') === 'true') || (getenv('AWS_ENDPOINT') ?: '') !== '',
            'local_path'        => getenv('STORAGE_LOCAL_PATH') ?: (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 3)) . '/public/uploads',
            'local_url'         => getenv('STORAGE_LOCAL_URL') ?: '/uploads',
        ];

        // Local counts (cheap, filesystem walk)
        $local = new LocalStorage();
        $localFiles = $local->listFiles('');
        $info['local_count'] = count($localFiles);
        $info['local_size_bytes'] = array_sum(array_column($localFiles, 'size'));

        $this->render('admin/storage/index', [
            'page_title' => 'Storage Gateways - APS Dream Home',
            'info'       => $info,
            'flash'      => $this->grabFlash(),
        ]);
    }

    public function test()
    {
        $this->requireAdmin();
        $storage = StorageManager::getInstance();
        $result = $storage->testS3();
        if (!empty($result['success'])) {
            $this->setFlash('success', 'S3 connection OK: ' . $result['message']);
        } else {
            $this->setFlash('error', 'S3 test failed: ' . ($result['error'] ?? 'unknown'));
        }
        $this->redirect('/admin/storage');
    }

    public function listBucket()
    {
        $this->requireAdmin();
        $prefix = trim($_GET['prefix'] ?? '', '/');
        $limit  = (int) ($_GET['limit'] ?? 10);
        $limit  = max(1, min($limit, 100));
        $storage = StorageManager::getInstance();
        $files = $storage->listFiles($prefix);
        $this->jsonResponse([
            'success' => true,
            'driver'  => $storage->getDriverName(),
            'prefix'  => $prefix,
            'count'   => count($files),
            'files'   => array_slice($files, 0, $limit),
        ]);
    }

    public function switchDriver()
    {
        $this->requireAdmin();
        $driver = strtolower(trim($_POST['driver'] ?? ''));
        if (!in_array($driver, ['local', 's3'], true)) {
            $this->setFlash('error', "Invalid driver: $driver");
            $this->redirect('/admin/storage');
            return;
        }
        // Runtime switch - persists in env via setenv. Real production would
        // also patch the .env file, but for the admin UI we just update the
        // in-memory state and warn that a reload is recommended.
        @putenv('STORAGE_DRIVER=' . $driver);
        $_ENV['STORAGE_DRIVER'] = $driver;
        StorageManager::reset();
        $this->setFlash('success', "Driver switched to '$driver' for this session. Update .env for persistence.");
        $this->redirect('/admin/storage');
    }

    /**
     * Collect both $_SESSION['success'] and $_SESSION['flash_success'] etc.
     * and clear them so the message shows once.
     */
    private function grabFlash(): array
    {
        $out = ['success' => null, 'error' => null, 'warning' => null, 'info' => null];
        foreach ($out as $k => $_) {
            if (isset($_SESSION[$k]))            { $out[$k] = $_SESSION[$k]; unset($_SESSION[$k]); }
            if (isset($_SESSION['flash_' . $k])) { $out[$k] = $_SESSION['flash_' . $k]; unset($_SESSION['flash_' . $k]); }
        }
        return $out;
    }
}
