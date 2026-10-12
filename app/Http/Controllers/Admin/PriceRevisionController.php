<?php

namespace App\Http\Controllers\Admin;

/**
 * Colony price revision countdowns (FOMO ticker source).
 */
class PriceRevisionController extends AdminController
{
    /**
     * GET /admin/colonies/price-revisions
     */
    public function index()
    {
        $this->requireAdmin();
        try {
            [$tSql, $tParams] = $this->tenantWhere();
            $rows = $this->db->fetchAll(
                "SELECT r.*, c.name AS colony_name FROM colony_price_revisions r
                 LEFT JOIN colonies c ON c.id = r.colony_id
                 WHERE 1=1" . $tSql . " ORDER BY r.effective_date DESC, r.id DESC LIMIT 100",
                $tParams
            ) ?: [];
            $colonies = $this->db->fetchAll(
                "SELECT id, name FROM colonies WHERE 1=1" . $tSql . " ORDER BY name ASC",
                $tParams
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('[PriceRevisionController::index] ' . $e->getMessage());
            $rows = [];
            $colonies = [];
        }
        $this->render('admin/colonies/price_revisions', [
            'page_title' => 'Price Revisions',
            'rows' => $rows,
            'colonies' => $colonies,
        ]);
    }

    /**
     * POST /admin/colonies/price-revisions/store
     */
    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $colonyId = (int)($_POST['colony_id'] ?? 0);
        $eff = trim((string)($_POST['effective_date'] ?? ''));
        $title = trim((string)($_POST['announcement_title'] ?? ''));
        $current = (float)($_POST['current_price'] ?? 0);
        $new = (float)($_POST['new_price'] ?? 0);
        if ($colonyId <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $eff) || $title === '' || $new <= 0) {
            $this->setFlash('error', 'Colony, title, new price and a valid effective date are required.');
            return $this->redirect('/admin/colonies/price-revisions');
        }
        try {
            $this->db->execute(
                "INSERT INTO colony_price_revisions
                 (tenant_id, colony_id, current_price, new_price, effective_date, announcement_title, is_active, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, 1, ?)",
                [
                    (int)$this->tenantId(), $colonyId, $current, $new, $eff,
                    mb_substr($title, 0, 150),
                    $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null,
                ]
            );
            $this->setFlash('success', 'Price revision scheduled.');
        } catch (\Throwable $e) {
            error_log('[PriceRevisionController::store] ' . $e->getMessage());
            $this->setFlash('error', 'Failed to schedule revision.');
        }
        return $this->redirect('/admin/colonies/price-revisions');
    }

    /**
     * POST /admin/colonies/price-revisions/deactivate/{id}
     */
    public function deactivate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $tid = (int)$this->tenantId();
            $stmt = $this->db->prepare(
                "UPDATE colony_price_revisions SET is_active = 0 WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "")
            );
            $params = [(int)$id];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $this->setFlash('success', 'Revision deactivated.');
        } catch (\Throwable $e) {
            error_log('[PriceRevisionController::deactivate] ' . $e->getMessage());
            $this->setFlash('error', 'Deactivate failed.');
        }
        return $this->redirect('/admin/colonies/price-revisions');
    }

    /**
     * Active upcoming revision for a colony (public banner source).
     */
    public static function activeForColony($db, int $colonyId): ?array
    {
        try {
            $row = $db->fetchOne(
                "SELECT * FROM colony_price_revisions
                 WHERE colony_id = ? AND is_active = 1 AND effective_date > CURDATE()
                 ORDER BY effective_date ASC LIMIT 1",
                [$colonyId]
            );
            return $row ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
