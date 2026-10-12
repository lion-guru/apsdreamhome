<?php

namespace App\Http\Controllers\Admin;

/**
 * Land litigation & court stay risk radar (khasra-wise cases).
 */
class LandLitigationController extends AdminController
{
    /**
     * GET /admin/land-inventory/litigations
     */
    public function index()
    {
        $this->requireAdmin();
        $status = trim((string)($_GET['status'] ?? ''));
        if (!in_array($status, ['pending', 'decided_in_favor', 'decided_against', 'settled'], true)) {
            $status = '';
        }
        $q = trim((string)($_GET['q'] ?? ''));
        try {
            [$tSql, $tParams] = $this->tenantWhere();
            $where = "1 = 1";
            $params = [];
            if ($status !== '') {
                $where .= " AND status = ?";
                $params[] = $status;
            }
            if ($q !== '') {
                $where .= " AND (khasra_no LIKE ? OR village LIKE ? OR case_number LIKE ?)";
                $params[] = "%$q%";
                $params[] = "%$q%";
                $params[] = "%$q%";
            }
            $rows = $this->db->fetchAll(
                "SELECT * FROM land_litigations WHERE $where" . $tSql . " ORDER BY stay_order_active DESC, next_hearing_date ASC, id DESC LIMIT 200",
                array_merge($params, $tParams)
            ) ?: [];
            $stayCount = $this->db->fetchOne(
                "SELECT COUNT(*) c FROM land_litigations WHERE stay_order_active = 1 AND status = 'pending'" . $tSql,
                $tParams
            )['c'] ?? 0;
        } catch (\Throwable $e) {
            error_log('[LandLitigationController::index] ' . $e->getMessage());
            $rows = [];
            $stayCount = 0;
        }
        $this->render('admin/land-inventory/litigations', [
            'page_title' => 'Land Litigation Radar',
            'rows' => $rows,
            'status_filter' => $status,
            'q' => $q,
            'stay_count' => (int)$stayCount,
        ]);
    }

    /**
     * POST /admin/land-inventory/litigations/store
     */
    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $khasra = trim((string)($_POST['khasra_no'] ?? ''));
        $village = trim((string)($_POST['village'] ?? ''));
        $court = trim((string)($_POST['court_name'] ?? ''));
        $caseNo = trim((string)($_POST['case_number'] ?? ''));
        $pet = trim((string)($_POST['petitioner_name'] ?? ''));
        $resp = trim((string)($_POST['respondent_name'] ?? ''));
        if ($khasra === '' || $village === '' || $court === '' || $caseNo === '' || $pet === '' || $resp === '') {
            $this->setFlash('error', 'Khasra, village, court, case number, petitioner and respondent are required.');
            return $this->redirect('/admin/land-inventory/litigations');
        }
        $hearing = trim((string)($_POST['next_hearing_date'] ?? ''));
        if ($hearing !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hearing)) {
            $hearing = '';
        }
        try {
            $this->db->execute(
                "INSERT INTO land_litigations
                 (tenant_id, khasra_no, village, court_name, case_number, petitioner_name, respondent_name,
                  case_type, stay_order_active, stay_order_details, next_hearing_date,
                  advocate_name, advocate_phone, status, notes, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    (int)$this->tenantId(),
                    mb_substr($khasra, 0, 100), mb_substr($village, 0, 100),
                    mb_substr($court, 0, 150), mb_substr($caseNo, 0, 100),
                    mb_substr($pet, 0, 150), mb_substr($resp, 0, 150),
                    mb_substr(trim((string)($_POST['case_type'] ?? '')), 0, 100) ?: null,
                    !empty($_POST['stay_order_active']) ? 1 : 0,
                    mb_substr(trim((string)($_POST['stay_order_details'] ?? '')), 0, 2000) ?: null,
                    $hearing !== '' ? $hearing : null,
                    mb_substr(trim((string)($_POST['advocate_name'] ?? '')), 0, 100) ?: null,
                    mb_substr(preg_replace('/\D/', '', (string)($_POST['advocate_phone'] ?? '')), 0, 20) ?: null,
                    'pending',
                    mb_substr(trim((string)($_POST['notes'] ?? '')), 0, 2000) ?: null,
                    $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null,
                ]
            );
            $this->setFlash('success', 'Case ' . $caseNo . ' tracked.');
        } catch (\Throwable $e) {
            error_log('[LandLitigationController::store] ' . $e->getMessage());
            $this->setFlash('error', 'Failed to save case.');
        }
        return $this->redirect('/admin/land-inventory/litigations');
    }

    /**
     * POST /admin/land-inventory/litigations/update/{id}
     */
    public function update($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $id = (int)$id;
        $status = strtolower(trim((string)($_POST['status'] ?? 'pending')));
        if (!in_array($status, ['pending', 'decided_in_favor', 'decided_against', 'settled'], true)) {
            $status = 'pending';
        }
        $hearing = trim((string)($_POST['next_hearing_date'] ?? ''));
        if ($hearing !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hearing)) {
            $hearing = '';
        }
        try {
            $tid = (int)$this->tenantId();
            $stmt = $this->db->prepare(
                "UPDATE land_litigations SET status = ?, stay_order_active = ?, next_hearing_date = ?, notes = ?
                 WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "")
            );
            $params = [
                $status,
                !empty($_POST['stay_order_active']) ? 1 : 0,
                $hearing !== '' ? $hearing : null,
                mb_substr(trim((string)($_POST['notes'] ?? '')), 0, 2000) ?: null,
                $id,
            ];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $this->setFlash('success', $stmt->rowCount() > 0 ? 'Case updated.' : 'Case not found.');
        } catch (\Throwable $e) {
            error_log('[LandLitigationController::update] ' . $e->getMessage());
            $this->setFlash('error', 'Update failed.');
        }
        return $this->redirect('/admin/land-inventory/litigations');
    }

    /**
     * POST /admin/land-inventory/litigations/delete/{id}
     */
    public function destroy($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $tid = (int)$this->tenantId();
            $stmt = $this->db->prepare(
                "DELETE FROM land_litigations WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "")
            );
            $params = [(int)$id];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $this->setFlash('success', $stmt->rowCount() > 0 ? 'Case removed.' : 'Case not found.');
        } catch (\Throwable $e) {
            error_log('[LandLitigationController::destroy] ' . $e->getMessage());
            $this->setFlash('error', 'Delete failed.');
        }
        return $this->redirect('/admin/land-inventory/litigations');
    }
}
