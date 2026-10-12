<?php

namespace App\Http\Controllers\Admin;

/**
 * Tracked possession handover certificates (kabza patra records).
 * Complements PossessionController (handover workflow) and the stateless
 * Stage-7 PDF download with issued, numbered, photo-backed records.
 */
class PossessionCertController extends AdminController
{
    /**
     * GET /admin/sales/bookings/{id}/possession-certificate
     */
    public function show($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        try {
            $svc = new \App\Services\Sales\BookingLifecycleService();
            $booking = $svc->getBookingById($id);
        } catch (\Throwable $e) {
            error_log('[PossessionCertController::show] ' . $e->getMessage());
            $booking = null;
        }
        if (!$booking) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/admin/sales/bookings');
        }
        try {
            $certs = $this->db->fetchAll(
                "SELECT * FROM possession_certificates WHERE booking_id = ? ORDER BY id DESC",
                [$id]
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('[PossessionCertController::show] certs: ' . $e->getMessage());
            $certs = [];
        }
        $this->render('admin/sales/possession_certificate', [
            'page_title' => 'Possession — ' . ($booking['booking_number'] ?? $id),
            'booking' => $booking,
            'certs' => $certs,
        ]);
    }

    /**
     * POST /admin/sales/bookings/{id}/possession-certificate/issue
     */
    public function issue($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $id = (int)$id;
        try {
            $svc = new \App\Services\Sales\BookingLifecycleService();
            $booking = $svc->getBookingById($id);
        } catch (\Throwable $e) {
            error_log('[PossessionCertController::issue] ' . $e->getMessage());
            $booking = null;
        }
        if (!$booking) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/admin/sales/bookings');
        }
        $handover = trim((string)($_POST['handover_date'] ?? ''));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $handover)) {
            $this->setFlash('error', 'Valid handover date is required.');
            return $this->redirect('/admin/sales/bookings/' . $id . '/possession-certificate');
        }
        $status = strtolower(trim((string)($_POST['handover_status'] ?? 'final')));
        if (!in_array($status, ['provisional', 'final'], true)) {
            $status = 'final';
        }

        // Optional pillar photo (images ≤5MB).
        $photoPath = null;
        if (isset($_FILES['pillar_photo']) && ($_FILES['pillar_photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $v = \UploadValidator::validate($_FILES['pillar_photo'], ['types' => 'images', 'max_size' => 5]);
            if (empty($v['valid'])) {
                $this->setFlash('error', 'Pillar photo: ' . ($v['error'] ?? 'invalid file'));
                return $this->redirect('/admin/sales/bookings/' . $id . '/possession-certificate');
            }
            $dir = dirname(__DIR__, 4) . '/assets/uploads/possession/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $safe = \UploadValidator::safeFilename($_FILES['pillar_photo']['name']);
            $ext = pathinfo($safe, PATHINFO_EXTENSION);
            $filename = 'pillar_' . $id . '_' . time() . '.' . $ext;
            if (!move_uploaded_file($_FILES['pillar_photo']['tmp_name'], $dir . $filename)) {
                $this->setFlash('error', 'Could not store pillar photo.');
                return $this->redirect('/admin/sales/bookings/' . $id . '/possession-certificate');
            }
            $photoPath = 'assets/uploads/possession/' . $filename;
        }

        try {
            $tid = (int)$this->tenantId();
            $me = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
            $certNo = 'POS-' . date('Y') . '-' . strtoupper(substr(md5($id . microtime(true)), 0, 6));
            $this->db->execute(
                "INSERT INTO possession_certificates
                 (tenant_id, booking_id, plot_id, customer_id, certificate_no, handover_date,
                  demarcation_done, pillar_photo_path, site_engineer_name, site_engineer_phone,
                  handover_status, remarks, issued_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $tid, $id,
                    (int)($booking['plot_id'] ?? 0) ?: null,
                    (int)($booking['customer_id'] ?? 0) ?: null,
                    $certNo, $handover,
                    !empty($_POST['demarcation_done']) ? 1 : 0,
                    $photoPath,
                    mb_substr(trim((string)($_POST['site_engineer_name'] ?? '')), 0, 100) ?: null,
                    mb_substr(preg_replace('/\D/', '', (string)($_POST['site_engineer_phone'] ?? '')), 0, 20) ?: null,
                    $status,
                    mb_substr(trim((string)($_POST['remarks'] ?? '')), 0, 1000) ?: null,
                    $me,
                ]
            );
            $this->setFlash('success', 'Possession certificate ' . $certNo . ' issued.');
        } catch (\Throwable $e) {
            error_log('[PossessionCertController::issue] ' . $e->getMessage());
            if ($photoPath && is_file(dirname(__DIR__, 4) . '/' . $photoPath)) {
                unlink(dirname(__DIR__, 4) . '/' . $photoPath);
            }
            $this->setFlash('error', 'Issue failed.');
        }
        return $this->redirect('/admin/sales/bookings/' . $id . '/possession-certificate');
    }
}
