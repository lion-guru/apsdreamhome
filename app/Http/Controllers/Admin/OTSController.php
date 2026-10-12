<?php

namespace App\Http\Controllers\Admin;

/**
 * One-Time Settlement (OTS) / festival penalty-waiver schemes.
 * Routes under /admin/finance/ots-schemes*.
 */
class OTSController extends AdminController
{
    /**
     * Latest currently-running active scheme (or null).
     */
    public static function activeScheme($db): ?array
    {
        try {
            $row = $db->fetchOne(
                "SELECT * FROM ots_schemes
                 WHERE is_active = 1 AND start_date <= CURDATE() AND end_date >= CURDATE()
                 ORDER BY id DESC LIMIT 1"
            );
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[OTSController::activeScheme] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Overdue principal + accrued penalty for a plot-booking.
     */
    public static function overdueForBooking($db, int $bookingId): array
    {
        $out = ['principal' => 0.0, 'penalty' => 0.0, 'count' => 0];
        try {
            $r = $db->fetchOne(
                "SELECT COUNT(*) n, COALESCE(SUM(GREATEST(amount - paid_amount, 0)), 0) od,
                        COALESCE(SUM(accrued_penalty), 0) pen
                 FROM booking_payment_schedules WHERE booking_id = ? AND status = 'overdue'",
                [$bookingId]
            );
            if ($r) {
                $out = ['principal' => (float)$r['od'], 'penalty' => (float)$r['pen'], 'count' => (int)$r['n']];
            }
        } catch (\Throwable $e) {
            error_log('[OTSController::overdueForBooking] ' . $e->getMessage());
        }
        return $out;
    }

    /**
     * GET /admin/finance/ots-schemes
     */
    public function index()
    {
        $this->requireAdmin();
        try {
            [$tSql, $tParams] = $this->tenantWhere();
            $rows = $this->db->fetchAll(
                "SELECT * FROM ots_schemes WHERE 1=1" . $tSql . " ORDER BY id DESC LIMIT 100",
                $tParams
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('[OTSController::index] ' . $e->getMessage());
            $rows = [];
        }
        $this->render('admin/finance/ots_schemes', [
            'page_title' => 'OTS Schemes',
            'rows' => $rows,
            'active' => self::activeScheme($this->db),
        ]);
    }

    /**
     * POST /admin/finance/ots-schemes/create
     */
    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $name = trim((string)($_POST['scheme_name'] ?? ''));
        $start = trim((string)($_POST['start_date'] ?? ''));
        $end = trim((string)($_POST['end_date'] ?? ''));
        $waiver = (float)($_POST['waiver_percentage'] ?? 100);
        if ($name === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
            $this->setFlash('error', 'Scheme name, start and end dates are required.');
            return $this->redirect('/admin/finance/ots-schemes');
        }
        if ($waiver < 0) $waiver = 0;
        if ($waiver > 100) $waiver = 100;
        try {
            $this->db->execute(
                "INSERT INTO ots_schemes
                 (tenant_id, scheme_name, start_date, end_date, waiver_percentage, min_overdue_months, terms_conditions, is_active, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)",
                [
                    (int)$this->tenantId(), mb_substr($name, 0, 150), $start, $end, $waiver,
                    max(0, (int)($_POST['min_overdue_months'] ?? 2)),
                    mb_substr(trim((string)($_POST['terms_conditions'] ?? '')), 0, 2000) ?: null,
                    $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null,
                ]
            );
            $this->setFlash('success', 'OTS scheme launched: ' . $name);
        } catch (\Throwable $e) {
            error_log('[OTSController::store] ' . $e->getMessage());
            $this->setFlash('error', 'Failed to create scheme.');
        }
        return $this->redirect('/admin/finance/ots-schemes');
    }

    /**
     * GET /admin/finance/ots-schemes/letter/{booking_id} — sanction letter.
     */
    public function generateLetter($bookingId)
    {
        $this->requireAdmin();
        $bookingId = (int)$bookingId;
        try {
            $svc = new \App\Services\Sales\BookingLifecycleService();
            $bk = $svc->getBookingById($bookingId);
        } catch (\Throwable $e) {
            error_log('[OTSController::generateLetter] ' . $e->getMessage());
            $bk = null;
        }
        if (!$bk) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/admin/finance/ots-schemes');
        }
        $scheme = self::activeScheme($this->db);
        $od = self::overdueForBooking($this->db, $bookingId);
        $waived = round($od['penalty'] * ((float)($scheme['waiver_percentage'] ?? 0)) / 100, 2);
        $payoff = round($od['principal'] + $od['penalty'] - $waived, 2);
        $customer = [];
        try {
            $customer = $this->db->fetchOne(
                "SELECT name, phone, address FROM users WHERE id = ? LIMIT 1",
                [(int)($bk['customer_id'] ?? 0)]
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('[OTSController::generateLetter] customer: ' . $e->getMessage());
        }
        $this->render('admin/finance/ots_letter', [
            'page_title' => 'OTS Sanction Letter — ' . ($bk['booking_number'] ?? $bookingId),
            'booking' => $bk,
            'customer' => $customer,
            'scheme' => $scheme,
            'overdue' => $od,
            'waived' => $waived,
            'payoff' => $payoff,
        ]);
    }
}
