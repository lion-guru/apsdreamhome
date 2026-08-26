<?php

namespace App\Http\Controllers\Admin;

use App\Services\BackupRestoreService;
use App\Core\Database\Database;
use App\Services\Storage\StorageManager;

/**
 * Backup management UI + REST endpoints.
 *
 * Routes (all admin-only):
 *   GET  /admin/backup              - index
 *   POST /admin/backup/create       - create new backup
 *   POST /admin/backup/restore/{id} - restore from backup
 *   POST /admin/backup/upload       - upload a backup file
 *   GET  /admin/backup/health       - JSON health status
 *   GET  /admin/backup/download/{id} - download backup file
 *
 * Note: a separate controller (AdminWorkflowController@backups) already
 * exposes a parallel /admin/backups* set of routes. This controller covers
 * /admin/backup* (singular) which is what the sidebar links to.
 */
class BackupController extends AdminController
{
    private $backupService;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->backupService = new BackupRestoreService();
        } catch (\Exception $e) {
            error_log('BackupController: service init failed: ' . $e->getMessage());
            $this->backupService = null;
        }
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $backups    = $this->backupService ? $this->backupService->listBackups() : [];
            $health     = $this->computeHealth($backups);
            $stats      = $this->computeStats($backups);

            $this->render('admin/backup/index', [
                'page_title' => 'System Backup - APS Dream Home',
                'backups'    => $backups,
                'health'     => $health,
                'stats'      => $stats,
            ]);
        } catch (\Exception $e) {
            error_log('BackupController::index error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to load backup page: ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    /**
     * POST /admin/backup/create
     */
    public function create()
    {
        $this->requireAdmin();
        try {
            $result = $this->backupService
                ? $this->backupService->createFullBackup($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null)
                : ['success' => false, 'error' => 'Backup service unavailable'];

            if (!empty($result['success'])) {
                $this->setFlash('success', 'Backup created: ' . ($result['size'] ?? 'OK') . ' (' . ($result['tables'] ?? '?') . ' tables)');
            } else {
                $this->setFlash('error', 'Backup failed: ' . ($result['error'] ?? 'unknown error'));
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Backup failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/backup');
    }

    /**
     * POST /admin/backup/restore/{id}
     */
    public function restore($id)
    {
        $this->requireAdmin();
        $id = (int) $id;

        try {
            $db = Database::getInstance()->getConnection();
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $stmt = $db->prepare("SELECT file_path, status FROM system_backups WHERE id = ?" . $tenantSql);
            $stmt->execute(array_merge([$id], $tenantParams));
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) {
                $this->setFlash('error', "Backup #{$id} not found");
                $this->redirect('/admin/backup');
                return;
            }
            if (($row['status'] ?? '') !== 'completed') {
                $this->setFlash('error', "Backup #{$id} is not in 'completed' status, refusing to restore");
                $this->redirect('/admin/backup');
                return;
            }
            if (empty($row['file_path']) || !file_exists($row['file_path'])) {
                $this->setFlash('error', "Backup file missing on disk: " . ($row['file_path'] ?? 'no path'));
                $this->redirect('/admin/backup');
                return;
            }

            $result = $this->backupService
                ? $this->backupService->restoreBackup($row['file_path'])
                : ['success' => false, 'error' => 'Backup service unavailable'];

            if (!empty($result['success'])) {
                $this->setFlash('success', "Backup #{$id} restored. Statements executed: " . ($result['statements_executed'] ?? '?'));
            } else {
                $this->setFlash('error', "Restore failed: " . ($result['error'] ?? 'unknown'));
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Restore failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/backup');
    }

    /**
     * POST /admin/backup/upload  (multipart/form-data, field "backup_file")
     */
    public function upload()
    {
        $this->requireAdmin();

        try {
            if (empty($_FILES['backup_file']) || ($_FILES['backup_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $this->setFlash('error', 'No file uploaded');
                $this->redirect('/admin/backup');
                return;
            }

            $file = $_FILES['backup_file'];
            $name = basename($file['name']);
            // Sanity: only allow .sql or .sql.gz extensions
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if ($ext !== 'sql' && $ext !== 'gz') {
                $this->setFlash('error', "Invalid file type '.{$ext}'. Only .sql or .sql.gz allowed.");
                $this->redirect('/admin/backup');
                return;
            }
            if (($file['size'] ?? 0) > 500 * 1024 * 1024) {
                $this->setFlash('error', 'File too large (max 500 MB)');
                $this->redirect('/admin/backup');
                return;
            }

            $destDir  = STORAGE_PATH . '/backups';
            if (!is_dir($destDir)) {
                @mkdir($destDir, 0755, true);
            }
            $destName = 'uploaded_' . date('Y-m-d_H-i-s') . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $name);
            $destPath = $destDir . '/' . $destName;

            if (!@move_uploaded_file($file['tmp_name'], $destPath)) {
                $this->setFlash('error', 'Failed to move uploaded file');
                $this->redirect('/admin/backup');
                return;
            }

            // Log into system_backups — tenant-scoped (system_backups has tenant_id)
            $db = Database::getInstance()->getConnection();
            $checksum = hash_file('sha256', $destPath);
            $fileSize = filesize($destPath);
            $tid = (int)$this->tenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";
            $tenantInsert = $tid > 1 ? [$tid] : [];
            $stmt = $db->prepare("INSERT INTO system_backups (backup_type, file_path, file_size, checksum, status, completed_at, created_by{$tenantCol}) VALUES ('uploaded', ?, ?, ?, 'completed', NOW(), ?{$tenantVal})");
            $stmt->execute(array_merge([$destPath, $fileSize, $checksum, $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null], $tenantInsert));

            $this->setFlash('success', "Upload OK: {$destName} (" . $this->humanBytes($fileSize) . ")");
        } catch (\Exception $e) {
            $this->setFlash('error', 'Upload failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/backup');
    }

    /**
     * GET /admin/backup/health  (JSON)
     */
    public function health()
    {
        $this->requireAdmin();
        $backups = $this->backupService ? $this->backupService->listBackups() : [];
        $h = $this->computeHealth($backups);
        $this->jsonResponse($h);
    }

    /**
     * GET /admin/backup/download/{id}
     */
    public function download($id)
    {
        $this->requireAdmin();
        $id = (int) $id;

        try {
            $db = Database::getInstance()->getConnection();
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $stmt = $db->prepare("SELECT file_path, status FROM system_backups WHERE id = ?" . $tenantSql);
            $stmt->execute(array_merge([$id], $tenantParams));
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row || empty($row['file_path']) || !file_exists($row['file_path'])) {
                $this->setFlash('error', "Backup #{$id} or its file not found");
                $this->redirect('/admin/backup');
                return;
            }

            $filepath = $row['file_path'];
            $filename = basename($filepath);
            $mime     = (substr($filename, -3) === '.gz') ? 'application/gzip' : 'application/sql';

            // Clear any output buffers
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header('Content-Type: ' . $mime);
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filepath));
            header('Cache-Control: no-cache');
            readfile($filepath);
            exit;
        } catch (\Exception $e) {
            $this->setFlash('error', 'Download failed: ' . $e->getMessage());
            $this->redirect('/admin/backup');
        }
    }

    /**
     * POST /admin/backup/to-s3
     * Uploads an existing local backup to S3.
     */
    public function toS3()
    {
        $this->requireAdmin();
        $id = (int) ($_POST['backup_id'] ?? $_GET['backup_id'] ?? 0);
        if (!$id) {
            $this->setFlash('error', 'Missing backup_id');
            $this->redirect('/admin/backup');
            return;
        }
        try {
            $db = Database::getInstance()->getConnection();
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $stmt = $db->prepare("SELECT id, file_path, status FROM system_backups WHERE id = ?" . $tenantSql);
            $stmt->execute(array_merge([$id], $tenantParams));
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row || empty($row['file_path']) || !is_file($row['file_path'])) {
                $this->setFlash('error', "Backup #{$id} file not found on local disk");
                $this->redirect('/admin/backup');
                return;
            }

            $storage = StorageManager::getInstance();
            if (!$storage->isS3Enabled()) {
                $this->setFlash('error', 'S3 is not configured or not enabled. Set STORAGE_DRIVER=s3 and AWS_* env vars.');
                $this->redirect('/admin/backup');
                return;
            }

            $localPath = $row['file_path'];
            $key = 'backups/' . date('Y/m/d') . '/' . basename($localPath);
            $result = $storage->put($key, file_get_contents($localPath), [
                'ContentType'   => 'application/sql',
                'Cache-Control' => 'private, no-cache',
            ]);
            if (empty($result['success'])) {
                $this->setFlash('error', 'S3 upload failed: ' . ($result['error'] ?? 'unknown'));
                $this->redirect('/admin/backup');
                return;
            }

            // Persist S3 location on the row.
            try {
                [$tenantSql2, $tenantParams2] = $this->tenantWhere();
                $stmt = $db->prepare("UPDATE system_backups SET s3_key = ?, s3_uploaded_at = NOW() WHERE id = ?" . $tenantSql2);
                $stmt->execute(array_merge([$key, $id], $tenantParams2));
            } catch (\Throwable $e) {
                // column may not exist - log and continue
                error_log('BackupController::toS3: column update failed: ' . $e->getMessage());
            }

            $this->setFlash('success', "Backup #{$id} uploaded to S3 as {$key} (" . $this->humanBytes($result['size'] ?? 0) . ")");
        } catch (\Throwable $e) {
            $this->setFlash('error', 'S3 upload failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/backup');
    }

    /**
     * GET /admin/backup/from-s3
     * Returns a JSON list of S3 backup objects under the backups/ prefix.
     */
    public function fromS3()
    {
        $this->requireAdmin();
        try {
            $storage = StorageManager::getInstance();
            $prefix = trim($_GET['prefix'] ?? 'backups/', '/');
            $files = $storage->listFiles($prefix . '/');
            $this->jsonResponse([
                'success' => true,
                'driver'  => $storage->getDriverName(),
                'prefix'  => $prefix,
                'count'   => count($files),
                'files'   => $files,
            ]);
        } catch (\Throwable $e) {
            $this->jsonResponse([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /admin/backup/s3-download?key=...
     * Returns a presigned URL for downloading an S3 backup object.
     */
    public function s3Download()
    {
        $this->requireAdmin();
        $key = trim($_GET['key'] ?? '', '/');
        if ($key === '') {
            $this->jsonResponse(['success' => false, 'error' => 'Missing key'], 400);
            return;
        }
        try {
            $storage = StorageManager::getInstance();
            $url = $storage->temporaryUrl($key, 30);
            if ($url === null) {
                $this->jsonResponse(['success' => false, 'error' => 'Could not generate URL'], 500);
                return;
            }
            $this->jsonResponse(['success' => true, 'url' => $url, 'expires_in_minutes' => 30]);
        } catch (\Throwable $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ---------- helpers ----------

    private function computeHealth(array $backups): array
    {
        $last = null;
        foreach ($backups as $b) {
            if (($b['status'] ?? '') === 'completed') {
                $last = $b;
                break;
            }
        }
        $maxAgeH = 24;
        $ageH    = null;
        $status  = 'stale';
        if ($last) {
            $ts = strtotime($last['completed_at'] ?? $last['started_at'] ?? '');
            if ($ts) {
                $ageH   = round((time() - $ts) / 3600, 2);
                $status = $ageH <= $maxAgeH ? 'ok' : 'stale';
            }
        }
        return [
            'status'         => $status,
            'last_backup_id' => $last['id'] ?? null,
            'last_backup_at' => $last['completed_at'] ?? $last['started_at'] ?? null,
            'age_hours'      => $ageH,
            'max_age_hours'  => $maxAgeH,
            'file_exists'    => $last && !empty($last['file_path']) ? file_exists($last['file_path']) : false,
            'checked_at'     => date('Y-m-d H:i:s'),
        ];
    }

    private function computeStats(array $backups): array
    {
        $total       = count($backups);
        $completed   = 0;
        $failed      = 0;
        $totalBytes  = 0;
        $oldest      = null;
        $newest      = null;
        foreach ($backups as $b) {
            $status = $b['status'] ?? '';
            if ($status === 'completed') $completed++;
            if ($status === 'failed')    $failed++;
            $totalBytes += (int) ($b['file_size'] ?? 0);
            $ts = strtotime($b['started_at'] ?? '');
            if ($ts) {
                if ($oldest === null || $ts < $oldest) $oldest = $ts;
                if ($newest === null || $ts > $newest) $newest = $ts;
            }
        }
        return [
            'total'      => $total,
            'completed'  => $completed,
            'failed'     => $failed,
            'total_size' => $this->humanBytes($totalBytes),
            'oldest'     => $oldest ? date('Y-m-d H:i:s', $oldest) : null,
            'newest'     => $newest ? date('Y-m-d H:i:s', $newest) : null,
        ];
    }

    private function humanBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
