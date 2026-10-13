<?php

namespace App\Http\Controllers\Admin;

/**
 * Township society maintenance billing (monthly/quarterly per-sqft).
 * Bills feed RegistryNocService::checkNoDues() — pending dues block NOC.
 */
class MaintenanceDuesController extends AdminController
{
    /**
     * GET /admin/maintenance-dues
     */
    public function index()
    {
        $this->requireAdmin();
        $colonyId = (int)($_GET['colony_id'] ?? 0);
        $status = trim((string)($_GET['status'] ?? ''));
        if (!in_array($status, ['pending', 'paid', 'waived'], true)) {
            $status = '';
        }
        try {
            [$tSql, $tParams] = $this->tenantWhere();
            $colonies = $this->db->fetchAll(
                "SELECT id, name FROM colonies WHERE 1=1" . $tSql . " ORDER BY name ASC",
                $tParams
            ) ?: [];
            $where = "1 = 1";
            $params = [];
            if ($colonyId > 0) {
                $where .= " AND d.colony_id = ?";
                $params[] = $colonyId;
            }
            if ($status !== '') {
                $where .= " AND d.status = ?";
                $params[] = $status;
            }
            $rows = $this->db->fetchAll(
                "SELECT d.*, c.name AS colony_name, u.name AS customer_name, b.booking_number
                 FROM maintenance_dues d
                 LEFT JOIN colonies c ON c.id = d.colony_id
                 LEFT JOIN users u ON u.id = d.customer_id
                 LEFT JOIN plot_bookings b ON b.id = d.booking_id
                 WHERE $where" . $tSql . " ORDER BY d.bill_year DESC, d.bill_month DESC, d.id DESC LIMIT 200",
                array_merge($params, $tParams)
            ) ?: [];
            $totals = $this->db->fetchOne(
                "SELECT COALESCE(SUM(total_due - paid_amount), 0) AS outstanding,
                        COALESCE(SUM(paid_amount), 0) AS collected
                 FROM maintenance_dues d WHERE d.status = 'pending'" . $tSql,
                $tParams
            );
        } catch (\Throwable $e) {
            error_log('[MaintenanceDuesController::index] ' . $e->getMessage());
            $rows = [];
            $totals = ['outstanding' => 0, 'collected' => 0];
        }
        $this->render('admin/maintenance_dues', [
            'page_title' => 'Maintenance Dues',
            'rows' => $rows,
            'colonies' => $colonies,
            'colony_id' => $colonyId,
            'status_filter' => $status,
            'totals' => $totals ?: ['outstanding' => 0, 'collected' => 0],
        ]);
    }

    /**
     * POST /admin/maintenance-dues/generate-batch
     * One bill per active booking in the colony (idempotent per month).
     */
    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $this->generateBatch();
    }

    public function generateBatch()
    {
        $colonyId = (int)($_POST['colony_id'] ?? 0);
        $month = (int)($_POST['bill_month'] ?? date('n'));
        $year = (int)($_POST['bill_year'] ?? date('Y'));
        $rate = max(0, (float)($_POST['rate_per_sqft'] ?? 1));
        if ($colonyId <= 0 || $month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
            $this->setFlash('error', 'Colony and a valid billing month are required.');
            return $this->redirect('/admin/maintenance-dues');
        }
        $created = 0;
        $skipped = 0;
        try {
            $tid = (int)$this->tenantId();
            $tSql = $tid > 1 ? " AND b.tenant_id = ?" : "";
            $tParams = $tid > 1 ? [$tid] : [];
            // Colony may live on the booking row or (when null) on its plot.
            $bookings = $this->db->fetchAll(
                "SELECT b.id, b.customer_id, p.area_sqft,
                        COALESCE(b.colony_id, p.colony_id) AS eff_colony
                 FROM plot_bookings b
                 LEFT JOIN plots p ON p.id = b.plot_id
                 WHERE (b.colony_id = ? OR (b.colony_id IS NULL AND p.colony_id = ?))
                   AND b.status NOT IN ('cancelled')" . $tSql,
                array_merge([$colonyId, $colonyId], $tParams)
            ) ?: [];
            foreach ($bookings as $b) {
                $exists = $this->db->fetchOne(
                    "SELECT id FROM maintenance_dues WHERE booking_id = ? AND bill_month = ? AND bill_year = ? LIMIT 1",
                    [(int)$b['id'], $month, $year]
                );
                if ($exists) {
                    $skipped++;
                    continue;
                }
                $area = max(0, (float)($b['area_sqft'] ?? 0));
                $base = round($area * $rate, 2);
                $this->db->execute(
                    "INSERT INTO maintenance_dues
                     (tenant_id, booking_id, colony_id, customer_id, bill_month, bill_year,
                      rate_per_sqft, base_amount, other_charges, total_due, paid_amount, status, due_date)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, 0, 'pending', LAST_DAY(CONCAT(?, '-', LPAD(?, 2, '0'), '-01')))",
                    [$tid, (int)$b['id'], (int)($b['eff_colony'] ?? $colonyId) ?: $colonyId,
                     (int)($b['customer_id'] ?? 0) ?: null,
                     $month, $year, $rate, $base, $base, $year, $month]
                );
                $created++;
            }
            $this->setFlash('success', "Batch done: $created billed, $skipped already existed.");
        } catch (\Throwable $e) {
            error_log('[MaintenanceDuesController::generateBatch] ' . $e->getMessage());
            $this->setFlash('error', 'Batch generation failed.');
        }
        return $this->redirect('/admin/maintenance-dues?colony_id=' . $colonyId);
    }

    /**
     * POST /admin/maintenance-dues/collect/{id}
     */
    public function collectPayment($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $id = (int)$id;
        $mode = strtolower(trim((string)($_POST['payment_mode'] ?? 'upi')));
        if (!in_array($mode, ['upi', 'cash', 'bank_transfer', 'cheque'], true)) {
            $mode = 'upi';
        }
        try {
            $tid = (int)$this->tenantId();
            $row = $this->db->fetchOne(
                "SELECT * FROM maintenance_dues WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1",
                $tid > 1 ? [$id, $tid] : [$id]
            );
            if (!$row) {
                $this->setFlash('error', 'Bill not found.');
                return $this->redirect('/admin/maintenance-dues');
            }
            if (($row['status'] ?? '') === 'paid') {
                $this->setFlash('error', 'Already paid.');
                return $this->redirect('/admin/maintenance-dues');
            }
            $receiptId = 'MNT-' . date('Ymd') . '-' . strtoupper(substr(md5($id . microtime(true)), 0, 6));
            $this->db->execute(
                "UPDATE maintenance_dues SET status = 'paid', paid_amount = total_due,
                        paid_at = NOW(), receipt_id = ? WHERE id = ? LIMIT 1",
                [$receiptId, $id]
            );
            $this->setFlash('success', "Collected {$receiptId} via {$mode}. NOC dues updated.");
        } catch (\Throwable $e) {
            error_log('[MaintenanceDuesController::collectPayment] ' . $e->getMessage());
            $this->setFlash('error', 'Collection failed.');
        }
        return $this->redirect('/admin/maintenance-dues');
    }

    /**
     * GET /admin/maintenance-dues/receipt/{id}
     * Printable payment receipt for a paid dues row.
     */
    public function receipt($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        try {
            $tid = (int)$this->tenantId();
            $row = $this->db->fetchOne(
                "SELECT d.*, c.name AS colony_name, u.name AS customer_name,
                        u.phone AS customer_phone, b.booking_number, p.plot_number
                 FROM maintenance_dues d
                 LEFT JOIN colonies c ON c.id = d.colony_id
                 LEFT JOIN users u ON u.id = d.customer_id
                 LEFT JOIN plot_bookings b ON b.id = d.booking_id
                 LEFT JOIN plots p ON p.id = d.plot_id
                 WHERE d.id = ?" . ($tid > 1 ? " AND d.tenant_id = ?" : "") . " LIMIT 1",
                $tid > 1 ? [$id, $tid] : [$id]
            );
            if (!$row) {
                $this->setFlash('error', 'Receipt not found.');
                return $this->redirect('/admin/maintenance-dues');
            }
        } catch (\Throwable $e) {
            error_log('[MaintenanceDuesController::receipt] ' . $e->getMessage());
            $this->setFlash('error', 'Could not load receipt.');
            return $this->redirect('/admin/maintenance-dues');
        }
        $this->render('admin/maintenance/receipt_print', [
            'page_title' => 'Maintenance Receipt — ' . ($row['receipt_id'] ?? ('#' . $row['id'])),
            'due' => $row,
        ]);
    }
}
