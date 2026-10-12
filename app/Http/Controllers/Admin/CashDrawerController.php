<?php

namespace App\Http\Controllers\Admin;

/**
 * Day-end cash drawer & safe closing reconciliation.
 * System figure = opening + cash receipts − petty expenses − bank deposits.
 */
class CashDrawerController extends AdminController
{
    /**
     * Live system cash position for a date (no writes).
     */
    public static function systemPosition($db, string $date, int $tid): array
    {
        $out = ['opening' => 0.0, 'receipts' => 0.0, 'expenses' => 0.0, 'deposits' => 0.0, 'system' => 0.0];
        try {
            $tSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $tParams = $tid > 1 ? [$tid] : [];
            $prev = $db->fetchOne(
                "SELECT physical_closing FROM cash_drawer_closings
                 WHERE closing_date < ?" . $tSql . " ORDER BY closing_date DESC LIMIT 1",
                array_merge([$date], $tParams)
            );
            $out['opening'] = (float)($prev['physical_closing'] ?? 0);
            $rcpt = $db->fetchOne(
                "SELECT COALESCE(SUM(payment_amount), 0) amt FROM booking_payments
                 WHERE payment_method = 'cash' AND DATE(payment_date) = ?" . $tSql,
                array_merge([$date], $tParams)
            );
            $out['receipts'] = (float)($rcpt['amt'] ?? 0);
            try {
                $exp = $db->fetchOne(
                    "SELECT COALESCE(SUM(amount), 0) amt FROM petty_cash
                     WHERE transaction_type != 'topup' AND DATE(transaction_date) = ?" . $tSql,
                    array_merge([$date], $tParams)
                );
                $out['expenses'] = (float)($exp['amt'] ?? 0);
            } catch (\Throwable $e) {
                error_log('[CashDrawer systemPosition petty] ' . $e->getMessage());
            }
            try {
                $dep = $db->fetchOne(
                    "SELECT COALESCE(SUM(bank_deposit), 0) amt FROM cash_drawer_closings
                     WHERE closing_date = ?" . $tSql,
                    array_merge([$date], $tParams)
                );
                $out['deposits'] = (float)($dep['amt'] ?? 0);
            } catch (\Throwable $e) {
                error_log('[CashDrawer systemPosition deposits] ' . $e->getMessage());
            }
            $out['system'] = round($out['opening'] + $out['receipts'] - $out['expenses'] - $out['deposits'], 2);
        } catch (\Throwable $e) {
            error_log('[CashDrawerController::systemPosition] ' . $e->getMessage());
        }
        return $out;
    }

    /**
     * GET /admin/finance/cash-drawer
     */
    public function index()
    {
        $this->requireAdmin();
        $date = trim((string)($_GET['date'] ?? date('Y-m-d')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');
        $tid = (int)$this->tenantId();
        $pos = self::systemPosition($this->db, $date, $tid);
        try {
            $tSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $tParams = $tid > 1 ? [$tid] : [];
            $today = $this->db->fetchOne(
                "SELECT * FROM cash_drawer_closings WHERE closing_date = ?" . $tSql . " LIMIT 1",
                array_merge([$date], $tParams)
            );
            $history = $this->db->fetchAll(
                "SELECT * FROM cash_drawer_closings WHERE 1=1" . $tSql . " ORDER BY closing_date DESC LIMIT 30",
                $tParams
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('[CashDrawerController::index] ' . $e->getMessage());
            $today = null;
            $history = [];
        }
        $this->render('admin/finance/cash_drawer', [
            'page_title' => 'Cash Drawer — ' . $date,
            'date' => $date,
            'pos' => $pos,
            'today' => $today,
            'history' => $history,
        ]);
    }

    /**
     * POST /admin/finance/cash-drawer/close
     */
    public function submitClosing()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $date = trim((string)($_POST['closing_date'] ?? date('Y-m-d')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->setFlash('error', 'Valid closing date required.');
            return $this->redirect('/admin/finance/cash-drawer');
        }
        $tid = (int)$this->tenantId();
        $me = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
        // Denominations → physical total.
        $denoms = [500 => 0, 200 => 0, 100 => 0, 50 => 0, 20 => 0, 10 => 0, 'coins' => 0];
        foreach ($denoms as $k => $v) {
            $denoms[$k] = max(0, (int)($_POST['denom_' . $k] ?? 0));
        }
        $physical = $denoms[500] * 500 + $denoms[200] * 200 + $denoms[100] * 100
            + $denoms[50] * 50 + $denoms[20] * 20 + $denoms[10] * 10 + $denoms['coins'];
        $deposit = max(0, (float)($_POST['bank_deposit'] ?? 0));
        $pos = self::systemPosition($this->db, $date, $tid);
        // Include today's deposit in the system figure.
        $system = round($pos['opening'] + $pos['receipts'] - $pos['expenses'] - $deposit, 2);
        $discrepancy = round($physical - $system, 2);

        // Optional bank challan photo (images ≤5MB).
        $slipPath = null;
        if (isset($_FILES['bank_slip']) && ($_FILES['bank_slip']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $v = \UploadValidator::validate($_FILES['bank_slip'], ['types' => 'images', 'max_size' => 5]);
            if (empty($v['valid'])) {
                $this->setFlash('error', 'Bank slip: ' . ($v['error'] ?? 'invalid file'));
                return $this->redirect('/admin/finance/cash-drawer?date=' . $date);
            }
            $dir = dirname(__DIR__, 4) . '/assets/uploads/challans/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $safe = \UploadValidator::safeFilename($_FILES['bank_slip']['name']);
            $ext = pathinfo($safe, PATHINFO_EXTENSION);
            $filename = 'challan_' . str_replace('-', '', $date) . '_' . time() . '.' . $ext;
            if (!move_uploaded_file($_FILES['bank_slip']['tmp_name'], $dir . $filename)) {
                $this->setFlash('error', 'Could not store bank slip.');
                return $this->redirect('/admin/finance/cash-drawer?date=' . $date);
            }
            $slipPath = 'assets/uploads/challans/' . $filename;
        }

        try {
            $exists = $this->db->fetchOne(
                "SELECT id FROM cash_drawer_closings WHERE tenant_id = ? AND closing_date = ? LIMIT 1",
                [$tid, $date]
            );
            if ($exists) {
                if ($slipPath && is_file(dirname(__DIR__, 4) . '/' . $slipPath)) {
                    unlink(dirname(__DIR__, 4) . '/' . $slipPath);
                }
                $this->setFlash('error', 'This date is already closed (freeze is final).');
                return $this->redirect('/admin/finance/cash-drawer?date=' . $date);
            }
            $this->db->execute(
                "INSERT INTO cash_drawer_closings
                 (tenant_id, closing_date, cashier_id, opening_cash, cash_receipts, cash_expenses,
                  bank_deposit, system_closing, physical_closing, discrepancy, denominations_json,
                  bank_slip_path, status, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'closed', ?)",
                [
                    $tid, $date, $me, $pos['opening'], $pos['receipts'], $pos['expenses'],
                    $deposit, $system, $physical, $discrepancy, json_encode($denoms),
                    $slipPath, mb_substr(trim((string)($_POST['notes'] ?? '')), 0, 1000) ?: null,
                ]
            );
            $flag = $discrepancy == 0 ? 'Tally OK' : ('Discrepancy Rs.' . number_format($discrepancy, 2));
            $this->setFlash('success', "Day closed. System Rs." . number_format($system, 2) . ", physical Rs." . number_format($physical, 2) . " — $flag.");
        } catch (\Throwable $e) {
            error_log('[CashDrawerController::submitClosing] ' . $e->getMessage());
            if ($slipPath && is_file(dirname(__DIR__, 4) . '/' . $slipPath)) {
                unlink(dirname(__DIR__, 4) . '/' . $slipPath);
            }
            $this->setFlash('error', 'Closing failed.');
        }
        return $this->redirect('/admin/finance/cash-drawer?date=' . $date);
    }
}
