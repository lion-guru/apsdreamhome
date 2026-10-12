<?php

namespace App\Http\Controllers\Admin;

class ColonyTimelapseController extends AdminController
{
    /**
     * GET /admin/colonies/{id}/timelapse
     */
    public function index($id)
    {
        $this->requireAdmin();
        $colonyId = (int)$id;

        [$tSql, $tParams] = $this->tenantWhere();
        $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ? $tSql", array_merge([$colonyId], $tParams));

        if (!$colony) {
            $this->setFlash('error', 'Colony not found.');
            return $this->redirect('/admin/colonies');
        }

        $milestones = $this->db->fetchAll(
            "SELECT * FROM colony_timelapse_milestones 
             WHERE colony_id = ? $tSql 
             ORDER BY stage_order ASC, id ASC",
            array_merge([$colonyId], $tParams)
        ) ?: [];

        // Compute overall progress
        $totalPct = 0;
        $count = count($milestones);
        foreach ($milestones as $m) {
            $totalPct += (int)$m['completion_pct'];
        }
        $overallProgress = $count > 0 ? round($totalPct / $count) : 0;

        $this->render('admin/colonies/timelapse', [
            'page_title' => 'Construction Timelapse - ' . $colony['name'],
            'colony' => $colony,
            'milestones' => $milestones,
            'overall_progress' => $overallProgress
        ]);
    }

    /**
     * POST /admin/colonies/{id}/timelapse/store
     */
    public function store($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $colonyId = (int)$id;

        $milestoneId = !empty($_POST['milestone_id']) ? (int)$_POST['milestone_id'] : 0;
        $title = trim((string)($_POST['title'] ?? ''));
        $code = trim((string)($_POST['milestone_code'] ?? 'MILESTONE'));
        $order = max(1, (int)($_POST['stage_order'] ?? 1));
        $desc = trim((string)($_POST['description'] ?? ''));
        $pct = max(0, min(100, (int)($_POST['completion_pct'] ?? 0)));
        $status = in_array($_POST['status'] ?? '', ['planned', 'in_progress', 'completed'], true) ? $_POST['status'] : 'planned';
        $targetDate = !empty($_POST['target_date']) ? $_POST['target_date'] : null;
        $completedDate = !empty($_POST['completed_date']) ? $_POST['completed_date'] : null;
        $verifiedBy = trim((string)($_POST['verified_by'] ?? ''));
        $droneUrl = trim((string)($_POST['drone_video_url'] ?? ''));

        if ($title === '') {
            $this->setFlash('error', 'Milestone title is required.');
            return $this->redirect('/admin/colonies/' . $colonyId . '/timelapse');
        }

        // Handle image uploads (photo_before & photo_after)
        $uploadDir = __DIR__ . '/../../../../storage/uploads/timelapse';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $photoBefore = null;
        if (!empty($_FILES['photo_before']['name']) && $_FILES['photo_before']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['photo_before']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $fname = 'before_' . $colonyId . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                if (@move_uploaded_file($_FILES['photo_before']['tmp_name'], $uploadDir . '/' . $fname)) {
                    $photoBefore = 'storage/uploads/timelapse/' . $fname;
                }
            }
        }

        $photoAfter = null;
        if (!empty($_FILES['photo_after']['name']) && $_FILES['photo_after']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['photo_after']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $fname = 'after_' . $colonyId . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                if (@move_uploaded_file($_FILES['photo_after']['tmp_name'], $uploadDir . '/' . $fname)) {
                    $photoAfter = 'storage/uploads/timelapse/' . $fname;
                }
            }
        }

        $tid = (int)$this->tenantId();

        try {
            if ($milestoneId > 0) {
                // Update
                $sql = "UPDATE colony_timelapse_milestones 
                        SET milestone_code = ?, stage_order = ?, title = ?, description = ?, 
                            completion_pct = ?, status = ?, target_date = ?, completed_date = ?, 
                            verified_by = ?, drone_video_url = ?
                            " . ($photoBefore ? ", photo_before = ?" : "") . "
                            " . ($photoAfter ? ", photo_after = ?" : "") . "
                        WHERE id = ? AND colony_id = ? AND tenant_id = ?";
                $params = [
                    $code, $order, $title, $desc, $pct, $status,
                    $targetDate, $completedDate, $verifiedBy, $droneUrl
                ];
                if ($photoBefore) $params[] = $photoBefore;
                if ($photoAfter) $params[] = $photoAfter;
                $params[] = $milestoneId;
                $params[] = $colonyId;
                $params[] = $tid;

                $this->db->execute($sql, $params);
                $this->setFlash('success', 'Milestone updated successfully.');
            } else {
                // Insert
                $this->db->execute(
                    "INSERT INTO colony_timelapse_milestones 
                     (tenant_id, colony_id, milestone_code, stage_order, title, description, 
                      completion_pct, status, photo_before, photo_after, drone_video_url, 
                      target_date, completed_date, verified_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $tid, $colonyId, $code, $order, $title, $desc,
                        $pct, $status, $photoBefore, $photoAfter, $droneUrl,
                        $targetDate, $completedDate, $verifiedBy
                    ]
                );
                $this->setFlash('success', 'Milestone stage created successfully.');
            }
        } catch (\Throwable $e) {
            error_log('[ColonyTimelapseController::store] ' . $e->getMessage());
            $this->setFlash('error', 'Failed to save milestone: ' . $e->getMessage());
        }

        return $this->redirect('/admin/colonies/' . $colonyId . '/timelapse');
    }

    /**
     * POST /admin/colonies/{id}/timelapse/delete/{milestone_id}
     */
    public function delete($id, $milestoneId)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $colonyId = (int)$id;
        $mId = (int)$milestoneId;
        $tid = (int)$this->tenantId();

        try {
            $m = $this->db->fetchOne(
                "SELECT photo_before, photo_after FROM colony_timelapse_milestones 
                 WHERE id = ? AND colony_id = ? AND tenant_id = ?",
                [$mId, $colonyId, $tid]
            );

            if ($m) {
                if (!empty($m['photo_before']) && file_exists(__DIR__ . '/../../../../' . $m['photo_before'])) {
                    @unlink(__DIR__ . '/../../../../' . $m['photo_before']);
                }
                if (!empty($m['photo_after']) && file_exists(__DIR__ . '/../../../../' . $m['photo_after'])) {
                    @unlink(__DIR__ . '/../../../../' . $m['photo_after']);
                }
                $this->db->execute(
                    "DELETE FROM colony_timelapse_milestones WHERE id = ? AND colony_id = ? AND tenant_id = ?",
                    [$mId, $colonyId, $tid]
                );
                $this->setFlash('success', 'Milestone stage deleted.');
            }
        } catch (\Throwable $e) {
            error_log('[ColonyTimelapseController::delete] ' . $e->getMessage());
            $this->setFlash('error', 'Failed to delete milestone.');
        }

        return $this->redirect('/admin/colonies/' . $colonyId . '/timelapse');
    }
}
