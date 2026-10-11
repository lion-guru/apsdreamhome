<?php

namespace App\Http\Controllers\Admin;

/**
 * NRI cross-border investment & FIRC compliance desk (PRO-5).
 */
class NRIController extends AdminController
{
    /**
     * GET /admin/sales/nri-desk
     */
    public function index()
    {
        $this->requireAdmin();
        $status = trim((string)($_GET['status'] ?? ''));
        if (!in_array($status, ['pending', 'verified', 'rejected'], true)) {
            $status = '';
        }
        $where = "1 = 1";
        $params = [];
        if ($status !== '') {
            $where .= " AND n.verification_status = ?";
            $params[] = $status;
        }
        try {
            $rows = $this->db->fetchAll(
                "SELECT n.*, b.booking_number, u.name AS customer_name, u.phone AS customer_phone,
                        p.plot_number, c.name AS colony_name
                 FROM nri_investments n
                 LEFT JOIN plot_bookings b ON b.id = n.booking_id
                 LEFT JOIN users u ON u.id = n.customer_id
                 LEFT JOIN plots p ON p.id = b.plot_id
                 LEFT JOIN colonies c ON c.id = p.colony_id
                 WHERE $where ORDER BY n.id DESC",
                $params
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('[NRIController::index] ' . $e->getMessage());
            $rows = [];
        }
        $this->render('admin/sales/nri_desk', [
            'page_title' => 'NRI Investment & FIRC Desk',
            'rows' => $rows,
            'status_filter' => $status,
        ]);
    }

    /**
     * POST /admin/sales/nri-desk/verify/{id}
     */
    public function verify($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $id = (int)$id;
        $decision = strtolower(trim((string)($_POST['decision'] ?? '')));
        if (!in_array($decision, ['verified', 'rejected'], true)) {
            $this->setFlash('error', 'Invalid decision');
            return $this->redirect('/admin/sales/nri-desk');
        }
        try {
            $stmt = $this->db->prepare(
                "UPDATE nri_investments SET verification_status = ?, notes = ?,
                        verified_by = ?, verified_at = NOW() WHERE id = ?"
            );
            $stmt->execute([
                $decision,
                mb_substr(trim((string)($_POST['notes'] ?? '')), 0, 1000) ?: null,
                (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0) ?: null,
                $id,
            ]);
            $this->setFlash('success', $stmt->rowCount() > 0 ? "Record $decision" : 'Record not found');
        } catch (\Throwable $e) {
            error_log('[NRIController::verify] ' . $e->getMessage());
            $this->setFlash('error', 'Verification failed');
        }
        return $this->redirect('/admin/sales/nri-desk');
    }
}
