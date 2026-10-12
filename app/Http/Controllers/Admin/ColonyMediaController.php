<?php

namespace App\Http\Controllers\Admin;

/**
 * Colony media hub: drone videos, site photos, 3D layouts, embeds.
 */
class ColonyMediaController extends AdminController
{
    /**
     * GET /admin/colonies/{id}/media-hub
     */
    public function index($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        try {
            [$tSql, $tParams] = $this->tenantWhere();
            $colony = $this->db->fetchOne(
                "SELECT id, name FROM colonies WHERE id = ?" . $tSql . " LIMIT 1",
                array_merge([$id], $tParams)
            );
            $rows = $colony ? ($this->db->fetchAll(
                "SELECT * FROM colony_media WHERE colony_id = ? ORDER BY is_featured DESC, id DESC",
                [$id]
            ) ?: []) : [];
        } catch (\Throwable $e) {
            error_log('[ColonyMediaController::index] ' . $e->getMessage());
            $colony = null;
            $rows = [];
        }
        if (!$colony) {
            $this->setFlash('error', 'Colony not found');
            return $this->redirect('/admin/plots/map');
        }
        $this->render('admin/colonies/media_hub', [
            'page_title' => 'Media Hub — ' . ($colony['name'] ?? $id),
            'colony' => $colony,
            'rows' => $rows,
        ]);
    }

    /**
     * POST /admin/colonies/{id}/media-hub/upload
     * Link-based (URL/youtube) always allowed; photo upload optional.
     */
    public function store($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $id = (int)$id;
        $title = trim((string)($_POST['title'] ?? ''));
        $type = strtolower(trim((string)($_POST['media_type'] ?? '')));
        $url = trim((string)($_POST['media_url'] ?? ''));
        if ($title === '' || !in_array($type, ['drone_video', 'site_photo', 'layout_3d', 'youtube_embed'], true)) {
            $this->setFlash('error', 'Title and media type are required.');
            return $this->redirect('/admin/colonies/' . $id . '/media-hub');
        }
        $mediaUrl = $url;
        if (isset($_FILES['media_file']) && ($_FILES['media_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $v = \UploadValidator::validate($_FILES['media_file'], ['types' => 'images', 'max_size' => 8]);
            if (empty($v['valid'])) {
                $this->setFlash('error', 'Photo: ' . ($v['error'] ?? 'invalid file'));
                return $this->redirect('/admin/colonies/' . $id . '/media-hub');
            }
            $dir = dirname(__DIR__, 4) . '/assets/uploads/colony-media/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $safe = \UploadValidator::safeFilename($_FILES['media_file']['name']);
            $ext = pathinfo($safe, PATHINFO_EXTENSION);
            $filename = 'colony' . $id . '_' . time() . '.' . $ext;
            if (!move_uploaded_file($_FILES['media_file']['tmp_name'], $dir . $filename)) {
                $this->setFlash('error', 'Could not store photo.');
                return $this->redirect('/admin/colonies/' . $id . '/media-hub');
            }
            $mediaUrl = 'assets/uploads/colony-media/' . $filename;
            $type = 'site_photo';
        }
        if ($mediaUrl === '') {
            $this->setFlash('error', 'Provide a media URL or upload a photo.');
            return $this->redirect('/admin/colonies/' . $id . '/media-hub');
        }
        try {
            $this->db->execute(
                "INSERT INTO colony_media
                 (tenant_id, colony_id, title, media_type, media_url, thumbnail_url, tags, is_featured, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    (int)$this->tenantId(), $id, mb_substr($title, 0, 150), $type,
                    mb_substr($mediaUrl, 0, 255),
                    mb_substr(trim((string)($_POST['thumbnail_url'] ?? '')), 0, 255) ?: null,
                    mb_substr(trim((string)($_POST['tags'] ?? '')), 0, 100) ?: null,
                    !empty($_POST['is_featured']) ? 1 : 0,
                    $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null,
                ]
            );
            $this->setFlash('success', 'Media added: ' . $title);
        } catch (\Throwable $e) {
            error_log('[ColonyMediaController::store] ' . $e->getMessage());
            if (isset($filename) && is_file($dir . $filename)) {
                unlink($dir . $filename);
            }
            $this->setFlash('error', 'Failed to save media.');
        }
        return $this->redirect('/admin/colonies/' . $id . '/media-hub');
    }

    /**
     * POST /admin/colonies/media/{id}/delete
     */
    public function destroy($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $tid = (int)$this->tenantId();
            $row = $this->db->fetchOne("SELECT * FROM colony_media WHERE id = ?", [(int)$id]);
            if ($row && ($tid <= 1 || (int)($row['tenant_id'] ?? 1) === $tid)) {
                if (!empty($row['media_url']) && strpos($row['media_url'], 'assets/uploads/colony-media/') === 0) {
                    $p = dirname(__DIR__, 4) . '/' . $row['media_url'];
                    if (is_file($p)) unlink($p);
                }
                $this->db->execute("DELETE FROM colony_media WHERE id = ? LIMIT 1", [(int)$id]);
                $this->setFlash('success', 'Media removed.');
            } else {
                $this->setFlash('error', 'Media not found.');
            }
        } catch (\Throwable $e) {
            error_log('[ColonyMediaController::destroy] ' . $e->getMessage());
            $this->setFlash('error', 'Delete failed.');
        }
        return $this->redirect('/admin/colonies/' . (int)($_POST['colony_id'] ?? 0) . '/media-hub');
    }
}
