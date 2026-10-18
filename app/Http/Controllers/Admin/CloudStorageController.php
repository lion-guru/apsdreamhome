<?php
/**
 * CloudStorageController — S3 file manager (Gap 25).
 *
 * Upload/list/download/delete files via S3Storage (DB-configured credentials).
 * Routes: /admin/cloud-storage (index/upload/download/delete)
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\Storage\S3Storage;
use Exception;

class CloudStorageController extends AdminController
{
    protected $storage;

    public function __construct()
    {
        parent::__construct();
        $this->storage = new S3Storage();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $configured = $this->storage->isConfigured();
            $files = [];
            if ($configured) {
                $files = $this->storage->listFiles();
            }
            return $this->render('admin/cloud_storage/index', [
                'page_title' => 'Cloud Storage (S3) - APS Dream Home',
                'active_page' => 'cloud_storage',
                'configured' => $configured,
                'files' => $files,
                'driver' => $this->storage->getDriver(),
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
            return $this->redirect('/admin/cloud-storage');
        }
    }

    public function upload()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }
        try {
            if (empty($_FILES['file'])) {
                return $this->jsonError('No file uploaded', 400);
            }
            $file = $_FILES['file'];
            $prefix = preg_replace('/[^A-Za-z0-9\-_\/]/', '', $_POST['prefix'] ?? '');
            $path = ($prefix ? $prefix . '/' : '') . basename($file['name']);
            $result = $this->storage->put($path, file_get_contents($file['tmp_name']));
            if (empty($result['success'])) {
                return $this->jsonError('Upload failed: ' . ($result['error'] ?? 'unknown'), 500);
            }
            $this->logActivity('s3_upload', ['path' => $path, 'size' => $file['size']]);
            return $this->jsonResponse(['success' => true, 'message' => 'File uploaded', 'path' => $path]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function download()
    {
        $this->requireAdmin();
        try {
            $path = $_GET['path'] ?? '';
            if (empty($path)) {
                return $this->jsonError('Path is required', 400);
            }
            $content = $this->storage->get($path);
            if ($content === null) {
                return $this->jsonError('File not found', 404);
            }
            $mime = $this->storage->mimeType($path) ?? 'application/octet-stream';
            header('Content-Type: ' . $mime);
            header('Content-Disposition: attachment; filename="' . basename($path) . '"');
            header('Content-Length: ' . strlen($content));
            echo $content;
            exit;
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function delete()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }
        try {
            $path = $_POST['path'] ?? '';
            if (empty($path)) {
                return $this->jsonError('Path is required', 400);
            }
            $ok = $this->storage->delete($path);
            if (!$ok) {
                return $this->jsonError('Delete failed', 500);
            }
            $this->logActivity('s3_delete', ['path' => $path]);
            return $this->jsonResponse(['success' => true, 'message' => 'File deleted']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }
}
