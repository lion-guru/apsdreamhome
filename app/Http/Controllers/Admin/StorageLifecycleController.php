<?php
/**
 * StorageLifecycleController — lifecycle rule management (Gap 25).
 *
 * Rules auto-delete/archive files older than N days by prefix.
 * Cron applies rules; admin can also run manually.
 * Routes: /admin/storage-lifecycle (index/store/toggle/delete/run)
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\Storage\S3Storage;
use Exception;

class StorageLifecycleController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        $rules = $this->db->fetchAll("SELECT * FROM storage_lifecycle_rules WHERE tenant_id = ? ORDER BY prefix ASC", [$this->tenantId()]) ?: [];
        return $this->render('admin/storage_lifecycle/index', [
            'page_title' => 'Storage Lifecycle Rules - APS Dream Home',
            'active_page' => 'storage_lifecycle',
            'rules' => $rules,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $prefix = preg_replace('/[^A-Za-z0-9\-_\/]/', '', $_POST['prefix'] ?? '');
            $action = $_POST['action'] ?? 'delete';
            $days = max(1, (int)($_POST['days'] ?? 30));
            if (!in_array($action, ['delete', 'archive', 'warn'], true)) return $this->jsonError('Invalid action', 400);
            if (empty($prefix)) return $this->jsonError('Prefix is required', 400);
            $id = (int)$this->db->insert('storage_lifecycle_rules', [
                'tenant_id' => $this->tenantId(), 'provider' => $_POST['provider'] ?? 'aws_s3',
                'prefix' => $prefix, 'action' => $action, 'days' => $days, 'is_active' => 1,
            ]);
            return $this->jsonResponse(['success' => true, 'message' => 'Rule created', 'id' => $id]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function toggle($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        $this->db->query("UPDATE storage_lifecycle_rules SET is_active = 1 - is_active WHERE id = ? AND tenant_id = ?", [(int)$id, $this->tenantId()]);
        return $this->jsonResponse(['success' => true]);
    }

    public function delete($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        $this->db->query("DELETE FROM storage_lifecycle_rules WHERE id = ? AND tenant_id = ?", [(int)$id, $this->tenantId()]);
        return $this->jsonResponse(['success' => true]);
    }

    /**
     * Run all active lifecycle rules now.
     */
    public function run()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $rules = $this->db->fetchAll("SELECT * FROM storage_lifecycle_rules WHERE tenant_id = ? AND is_active = 1", [$this->tenantId()]) ?: [];
            $processed = 0;
            foreach ($rules as $rule) {
                $storage = new S3Storage(['bucket' => $rule['provider'] === 'aws_s3' ? (getenv('AWS_BUCKET') ?: '') : '']);
                if (!$storage->isConfigured()) continue;
                $files = $storage->listFiles($rule['prefix']);
                $cutoff = time() - ((int)$rule['days'] * 86400);
                foreach ($files as $file) {
                    $mtime = strtotime($file['last_modified'] ?? 'now');
                    if ($mtime < $cutoff) {
                        if ($rule['action'] === 'delete') $storage->delete($file['path'] ?? $file['name'] ?? '');
                        $processed++;
                    }
                }
                $this->db->query("UPDATE storage_lifecycle_rules SET last_run = NOW() WHERE id = ?", [(int)$rule['id']]);
            }
            return $this->jsonResponse(['success' => true, 'message' => "Processed {$processed} file(s)", 'processed' => $processed]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    protected function tenantId(): int
    {
        return (int)($_SESSION['tenant_id'] ?? 1);
    }
}
