<?php

namespace App\Http\Controllers\Admin;

/**
 * Spot token advance (bayana) + 7-day plot lock.
 * Routes under /admin/sales/token-booking*.
 */
class TokenBookingController extends AdminController
{
    /**
     * Release token holds whose 7-day lock lapsed (plot back to available).
     * Runs on index/create so stale locks never strand inventory.
     */
    private function releaseExpiredLocks(): int
    {
        $n = 0;
        try {
            $tid = (int)$this->tenantId();
            $rows = $this->db->fetchAll(
                "SELECT id, plot_id FROM plot_token_bookings
                 WHERE status = 'active' AND lock_expires_at < NOW() AND tenant_id = ?",
                [$tid]
            ) ?: [];
            foreach ($rows as $r) {
                $this->db->execute(
                    "UPDATE plot_token_bookings SET status = 'expired' WHERE id = ?",
                    [(int)$r['id']]
                );
                $this->db->execute(
                    "UPDATE plots SET status = 'available', held_by = NULL, held_at = NULL, hold_expires_at = NULL
                     WHERE id = ? AND status = 'hold' AND tenant_id = ?",
                    [(int)$r['plot_id'], $tid]
                );
                $n++;
            }
        } catch (\Throwable $e) {
            error_log('[TokenBookingController::releaseExpiredLocks] ' . $e->getMessage());
        }
        return $n;
    }

    /**
     * GET /admin/sales/token-bookings
     */
    public function index()
    {
        $this->requireAdmin();
        $released = $this->releaseExpiredLocks();
        $status = trim((string)($_GET['status'] ?? ''));
        if (!in_array($status, ['active', 'converted', 'expired', 'refunded'], true)) {
            $status = '';
        }
        try {
            [$tSql, $tParams] = $this->tenantWhere();
            $where = "1 = 1";
            $params = [];
            if ($status !== '') {
                $where .= " AND t.status = ?";
                $params[] = $status;
            }
            $rows = $this->db->fetchAll(
                "SELECT t.*, p.plot_number, c.name AS colony_name
                 FROM plot_token_bookings t
                 LEFT JOIN plots p ON p.id = t.plot_id
                 LEFT JOIN colonies c ON c.id = t.colony_id
                 WHERE $where" . $tSql . " ORDER BY t.id DESC LIMIT 200",
                array_merge($params, $tParams)
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('[TokenBookingController::index] ' . $e->getMessage());
            $rows = [];
        }
        $this->render('admin/sales/token_bookings', [
            'page_title' => 'Token Bookings (Bayana)',
            'rows' => $rows,
            'status_filter' => $status,
            'released' => $released,
        ]);
    }

    /**
     * GET /admin/sales/token-booking/create/{plot_id}
     */
    public function create($plotId)
    {
        $this->requireAdmin();
        $plotId = (int)$plotId;
        try {
            $tid = (int)$this->tenantId();
            $plot = $this->db->fetchOne(
                "SELECT p.*, c.name AS colony_name FROM plots p
                 LEFT JOIN colonies c ON c.id = p.colony_id
                 WHERE p.id = ? AND p.tenant_id = ? LIMIT 1",
                [$plotId, $tid]
            );
        } catch (\Throwable $e) {
            error_log('[TokenBookingController::create] ' . $e->getMessage());
            $plot = null;
        }
        if (!$plot) {
            $this->setFlash('error', 'Plot not found');
            return $this->redirect('/admin/plots/map');
        }
        $this->render('admin/sales/token_booking_form', [
            'page_title' => 'Token Booking — Plot ' . ($plot['plot_number'] ?? $plotId),
            'plot' => $plot,
        ]);
    }

    /**
     * POST /admin/sales/token-booking/store
     */
    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $plotId = (int)($_POST['plot_id'] ?? 0);
        $name = trim((string)($_POST['customer_name'] ?? ''));
        $phone = preg_replace('/\D/', '', (string)($_POST['customer_phone'] ?? ''));
        $amount = (float)($_POST['token_amount'] ?? 0);
        $mode = strtolower(trim((string)($_POST['payment_mode'] ?? 'upi')));
        if ($plotId <= 0 || $name === '' || !preg_match('/^[6-9]\d{9}$/', $phone) || $amount <= 0) {
            $this->setFlash('error', 'Plot, buyer name, valid 10-digit phone and token amount are required.');
            return $this->redirect('/admin/sales/token-booking/create/' . $plotId);
        }
        if (!in_array($mode, ['upi', 'cash', 'bank_transfer', 'cheque'], true)) {
            $mode = 'upi';
        }
        try {
            $tid = (int)$this->tenantId();
            $me = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 1;
            $plot = $this->db->fetchOne(
                "SELECT id, status, plot_number, colony_id FROM plots WHERE id = ? AND tenant_id = ? LIMIT 1",
                [$plotId, $tid]
            );
            if (!$plot) {
                $this->setFlash('error', 'Plot not found');
                return $this->redirect('/admin/plots/map');
            }
            if (($plot['status'] ?? '') !== 'available') {
                $this->setFlash('error', 'Only available plots can take token (current: ' . ($plot['status'] ?? '?') . ').');
                return $this->redirect('/admin/plots/map');
            }
            $receiptNo = 'TKN-' . date('Ymd') . '-' . strtoupper(substr(md5($plotId . $phone . microtime(true)), 0, 6));
            $this->db->execute(
                "INSERT INTO plot_token_bookings
                 (tenant_id, plot_id, colony_id, associate_id, customer_name, customer_phone, customer_aadhaar,
                  token_amount, payment_mode, payment_ref, receipt_no, lock_expires_at, status, notes, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY), 'active', ?, ?)",
                [
                    $tid, $plotId, $plot['colony_id'] ?? null,
                    !empty($_POST['associate_id']) ? (int)$_POST['associate_id'] : null,
                    mb_substr($name, 0, 150), $phone,
                    mb_substr(preg_replace('/\D/', '', (string)($_POST['customer_aadhaar'] ?? '')), 0, 20) ?: null,
                    $amount, $mode,
                    mb_substr(trim((string)($_POST['payment_ref'] ?? '')), 0, 100) ?: null,
                    $receiptNo,
                    mb_substr(trim((string)($_POST['notes'] ?? '')), 0, 1000) ?: null,
                    $me,
                ]
            );
            $tokenId = (int)$this->db->lastInsertId();
            $this->db->execute(
                "UPDATE plots SET status = 'hold', held_by = ?, held_at = NOW(), hold_expires_at = DATE_ADD(NOW(), INTERVAL 7 DAY)
                 WHERE id = ? AND tenant_id = ?",
                [$me, $plotId, $tid]
            );
            try {
                $this->db->insert('plot_status_log', [
                    'plot_id' => $plotId, 'old_status' => 'available', 'new_status' => 'hold',
                    'changed_by' => $me, 'created_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $e) { error_log('TokenBookingController hold log: ' . $e->getMessage()); }
            $this->setFlash('success', 'Token ' . $receiptNo . ' recorded. Plot locked for 7 days.');
            return $this->redirect('/admin/sales/token-booking/receipt/' . urlencode($receiptNo));
        } catch (\Throwable $e) {
            error_log('[TokenBookingController::store] ' . $e->getMessage());
            $this->setFlash('error', 'Token booking failed.');
            return $this->redirect('/admin/sales/token-booking/create/' . $plotId);
        }
    }

    /**
     * GET /admin/sales/token-booking/receipt/{receipt_no} — printable slip.
     */
    public function printReceipt($receiptNo)
    {
        $this->requireAdmin();
        $receiptNo = trim((string)$receiptNo);
        try {
            [$tSql, $tParams] = $this->tenantWhere();
            $row = $this->db->fetchOne(
                "SELECT t.*, p.plot_number, p.area_sqft, c.name AS colony_name
                 FROM plot_token_bookings t
                 LEFT JOIN plots p ON p.id = t.plot_id
                 LEFT JOIN colonies c ON c.id = t.colony_id
                 WHERE t.receipt_no = ?" . $tSql . " LIMIT 1",
                array_merge([$receiptNo], $tParams)
            );
        } catch (\Throwable $e) {
            error_log('[TokenBookingController::printReceipt] ' . $e->getMessage());
            $row = null;
        }
        if (!$row) {
            $this->setFlash('error', 'Token receipt not found');
            return $this->redirect('/admin/sales/token-bookings');
        }
        $this->render('admin/sales/token_receipt', [
            'page_title' => 'Token Receipt ' . $receiptNo,
            'token' => $row,
        ]);
    }
}
