<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class ErpDashboardController extends AdminController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function inventory()
    {
        $plots = $this->db->fetchAll("
            SELECT p.id, p.plot_number, p.area_sqft, p.total_price, p.status, p.block, p.sector,
                   c.name as colony_name,
                   b.id as booking_id, b.booking_number, b.status as booking_status,
                   u.name as customer_name, u.phone as customer_phone,
                   (SELECT SUM(amount) FROM booking_payments WHERE booking_id = b.id) as amount_paid,
                   p.booking_date, p.customer_id
            FROM plots p
            LEFT JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN bookings b ON b.property_id = p.id
            LEFT JOIN users u ON b.customer_id = u.id
            ORDER BY FIELD(p.status,'available','booked','sold','hold','reserved','under_construction'), ISNULL(b.id), c.name, p.plot_number
        ");

        $stats = ['available' => 0, 'reserved' => 0, 'booked' => 0, 'sold' => 0, 'hold' => 0, 'under_construction' => 0];
        foreach ($plots as $p) {
            $s = $p['status'] ?? 'available';
            $stats[$s] = ($stats[$s] ?? 0) + 1;
        }
        $stats['total'] = count($plots);

        $this->render('admin/erp/inventory', [
            'page_title' => 'Plot Inventory',
            'plots' => $plots,
            'stats' => $stats,
            'currentPage' => 'erp-inventory'
        ]);
    }

    public function plotProfit()
    {
        $plots = $this->db->fetchAll("
            SELECT p.id, p.plot_number, p.area_sqft, p.total_price as sale_price, p.status,
                   c.name as colony_name,
                   COALESCE(lp.amount, 0) as land_cost,
                   COALESCE((SELECT SUM(amount) FROM colony_development_costs WHERE colony_id = p.colony_id), 0) as dev_cost
            FROM plots p
            LEFT JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN land_purchases lp ON lp.land_holding_id = p.land_holding_id
            ORDER BY c.name, p.plot_number
        ");

        $totals = ['land_cost' => 0, 'dev_cost' => 0, 'sale_price' => 0, 'profit' => 0];
        foreach ($plots as &$p) {
            $p['total_cost'] = $p['land_cost'] + $p['dev_cost'];
            $p['profit'] = $p['sale_price'] - $p['total_cost'];
            $p['margin_pct'] = $p['total_cost'] > 0 ? round(($p['profit'] / $p['total_cost']) * 100, 1) : 0;
            $totals['land_cost'] += $p['land_cost'];
            $totals['dev_cost'] += $p['dev_cost'];
            $totals['sale_price'] += $p['sale_price'];
            $totals['profit'] += $p['profit'];
        }
        $totalCost = $totals['land_cost'] + $totals['dev_cost'];
        $totals['margin_pct'] = $totalCost > 0 ? round(($totals['profit'] / $totalCost) * 100, 1) : 0;

        $this->render('admin/erp/plot_profit', [
            'page_title' => 'Plot P&L Report',
            'plots' => $plots,
            'totals' => $totals,
            'currentPage' => 'erp-profit'
        ]);
    }

    public function landMapping()
    {
        $data = $this->db->fetchAll("
            SELECT f.name as farmer_name, f.phone as farmer_phone,
                   flh.khasra_number, flh.land_area, flh.land_area_unit,
                   lp.amount as purchase_amount, lp.purchase_date, lp.registry_no,
                   p.plot_number, p.plot_code, p.area_sqft,
                   p.total_price as sale_price,
                   p.status as plot_status, p.block, p.sector,
                   c.name as colony_name
            FROM farmers f
            JOIN farmer_land_holdings flh ON flh.farmer_id = f.id
            LEFT JOIN land_purchases lp ON lp.land_holding_id = flh.id
            LEFT JOIN plots p ON p.land_holding_id = flh.id
            LEFT JOIN colonies c ON p.colony_id = c.id
            ORDER BY f.name, flh.khasra_number
        ");

        $this->render('admin/erp/land_mapping', [
            'page_title' => 'Farmer → Land → Plot Mapping',
            'data' => $data,
            'currentPage' => 'erp-land'
        ]);
    }

    /**
     * Tenant condition qualified with a table alias.
     * Returns ['', []] for superadmin tenant (single-tenant mode no-op).
     */
    private function tCond(string $alias): array
    {
        $tid = (int)$this->tenantId();
        if ($tid <= 1) return ['', []];
        return [" AND {$alias}.tenant_id = ?", [$tid]];
    }

    /**
     * Colony-by-colony P&L ledger.
     * Land cost = DISTINCT linked land_purchases via plots.land_holding_id
     * (fallback: colonies.estimated_land_cost, then colonies.land_cost).
     * Dev cost = SUM(colony_development_costs) for the colony (direct FK).
     * Booking value = active bookings (status NOT cancelled) + active
     * plot_bookings with no overlapping active booking on the same plot.
     * Collected = cleared booking_payment_receipts via bookings.
     */
    public function colonyPnl()
    {
        $this->requireAdmin();
        try {
            [$cSql, $cParams] = $this->tCond('c');
            $colonies = $this->db->fetchAll("
                SELECT c.id, c.name, c.location, c.total_area_sqft,
                       c.estimated_land_cost, c.land_cost
                FROM colonies c
                WHERE 1=1{$cSql}
                ORDER BY c.name
            ", $cParams) ?: [];
        } catch (\Exception $e) {
            error_log('ErpDashboardController::colonyPnl colonies: ' . $e->getMessage());
            $colonies = [];
        }

        $rows = [];
        $totals = ['land' => 0, 'dev' => 0, 'cost' => 0, 'value' => 0, 'collected' => 0,
            'outstanding' => 0, 'realized' => 0, 'projected' => 0, 'plots' => 0, 'sold' => 0, 'area' => 0];
        foreach ($colonies as $col) {
            $cid = (int)($col['id'] ?? 0);
            try {
                [$pSql, $pParams] = $this->tCond('p');
                $plot = $this->db->fetch("
                    SELECT COUNT(*) AS plots,
                           COALESCE(SUM(p.area_sqft), 0) AS area,
                           COALESCE(SUM(CASE WHEN p.status IN ('sold','booked') THEN 1 ELSE 0 END), 0) AS sold
                    FROM plots p
                    WHERE p.colony_id = ?{$pSql}
                ", array_merge([$cid], $pParams));

                [$lpSql, $lpParams] = $this->tCond('lp');
                $landLinked = $this->db->fetch("
                    SELECT COALESCE(SUM(lp.amount), 0) AS land
                    FROM land_purchases lp
                    WHERE lp.land_holding_id IN (
                        SELECT DISTINCT p2.land_holding_id FROM plots p2
                        WHERE p2.colony_id = ? AND p2.land_holding_id IS NOT NULL
                    ){$lpSql}
                ", array_merge([$cid], $lpParams));
                $land = (float)($landLinked['land'] ?? 0);
                if ($land <= 0) $land = (float)($col['estimated_land_cost'] ?? 0);
                if ($land <= 0) $land = (float)($col['land_cost'] ?? 0);

                [$dSql, $dParams] = $this->tCond('d');
                $dev = $this->db->fetch("
                    SELECT COALESCE(SUM(d.amount), 0) AS dev
                    FROM colony_development_costs d
                    WHERE d.colony_id = ?{$dSql}
                ", array_merge([$cid], $dParams));
                $devCost = (float)($dev['dev'] ?? 0);

                [$bSql, $bParams] = $this->tCond('b');
                $book = $this->db->fetch("
                    SELECT COALESCE(SUM(b.total_amount), 0) AS val
                    FROM bookings b
                    WHERE b.colony_id = ?
                      AND (b.status IS NULL OR b.status NOT IN ('cancelled')){$bSql}
                ", array_merge([$cid], $bParams));

                [$pbSql, $pbParams] = $this->tCond('pb');
                $pbook = $this->db->fetch("
                    SELECT COALESCE(SUM(pb.total_plot_value), 0) AS val
                    FROM plot_bookings pb
                    WHERE pb.colony_id = ?
                      AND (pb.status IS NULL OR pb.status NOT IN ('cancelled'))
                      AND NOT EXISTS (
                          SELECT 1 FROM bookings bx
                          WHERE bx.plot_id = pb.plot_id
                            AND (bx.status IS NULL OR bx.status NOT IN ('cancelled'))
                      ){$pbSql}
                ", array_merge([$cid], $pbParams));

                [$rSql, $rParams] = $this->tCond('r');
                [$rbSql, $rbParams] = $this->tCond('b');
                $coll = $this->db->fetch("
                    SELECT COALESCE(SUM(r.amount), 0) AS collected
                    FROM booking_payment_receipts r
                    JOIN bookings b ON b.id = r.booking_id
                    WHERE b.colony_id = ? AND r.status = 'cleared'{$rSql}{$rbSql}
                ", array_merge([$cid], $rParams, $rbParams));

                $cost = $land + $devCost;
                $value = (float)($book['val'] ?? 0) + (float)($pbook['val'] ?? 0);
                $collected = (float)($coll['collected'] ?? 0);
                $outstanding = $value - $collected;
                $realized = $collected - $cost;
                $projected = $value - $cost;
                $area = (float)($plot['area'] ?? 0);
                $row = [
                    'id' => $cid,
                    'name' => $col['name'] ?? ('Colony #' . $cid),
                    'location' => $col['location'] ?? '',
                    'plots' => (int)($plot['plots'] ?? 0),
                    'sold' => (int)($plot['sold'] ?? 0),
                    'area' => $area,
                    'land' => $land,
                    'dev' => $devCost,
                    'cost' => $cost,
                    'value' => $value,
                    'collected' => $collected,
                    'outstanding' => $outstanding,
                    'realized' => $realized,
                    'projected' => $projected,
                    'margin' => $cost > 0 ? round(($projected / $cost) * 100, 2) : 0,
                    'cost_per_sqft' => $area > 0 ? round($cost / $area, 2) : 0,
                ];
                $rows[] = $row;
                foreach (['land', 'dev', 'cost', 'value', 'collected', 'outstanding', 'realized', 'projected'] as $k) $totals[$k] += $row[$k];
                $totals['plots'] += $row['plots'];
                $totals['sold'] += $row['sold'];
                $totals['area'] += $row['area'];
            } catch (\Exception $e) {
                error_log('ErpDashboardController::colonyPnl colony ' . $cid . ': ' . $e->getMessage());
            }
        }
        $totals['margin'] = $totals['cost'] > 0 ? round(($totals['projected'] / $totals['cost']) * 100, 2) : 0;
        $totals['cost_per_sqft'] = $totals['area'] > 0 ? round($totals['cost'] / $totals['area'], 2) : 0;

        if (($_GET['export'] ?? '') === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="colony_pnl_' . date('Ymd') . '.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Colony', 'Plots', 'Sold', 'Area Sqft', 'Land Cost', 'Dev Cost', 'Total Cost', 'Booking Value', 'Collected', 'Outstanding', 'Realized Profit', 'Projected Profit', 'Margin %', 'Cost/Sqft']);
            foreach ($rows as $r) {
                fputcsv($out, [$r['name'], $r['plots'], $r['sold'], $r['area'], $r['land'], $r['dev'], $r['cost'], $r['value'], $r['collected'], $r['outstanding'], $r['realized'], $r['projected'], $r['margin'], $r['cost_per_sqft']]);
            }
            fputcsv($out, ['TOTAL', $totals['plots'], $totals['sold'], $totals['area'], $totals['land'], $totals['dev'], $totals['cost'], $totals['value'], $totals['collected'], $totals['outstanding'], $totals['realized'], $totals['projected'], $totals['margin'], $totals['cost_per_sqft']]);
            fclose($out);
            exit;
        }

        $this->render('admin/erp/colony_pnl', [
            'page_title' => 'Colony P&L Ledger',
            'rows' => $rows,
            'totals' => $totals,
            'currentPage' => 'erp-pnl'
        ]);
    }

    /**
     * Overdue EMI aging tracker from booking_payment_schedules.
     * Buckets: 1-30 grace, 31-60 warning, 61-90 late-fee, 90+ critical.
     */
    public function emiDefaulters()
    {
        $this->requireAdmin();
        try {
            [$sSql, $sParams] = $this->tCond('s');
            $rows = $this->db->fetchAll("
                SELECT s.id AS schedule_id, s.booking_id, s.installment_no, s.due_date, s.amount,
                       (s.amount - COALESCE(s.paid_amount, 0)) AS due_amount,
                       DATEDIFF(CURDATE(), s.due_date) AS overdue_days,
                       (COALESCE(s.late_fee, 0) + COALESCE(s.accrued_penalty, 0)) AS penalty,
                       s.reminder_count,
                       b.booking_number, b.plot_id,
                       p.plot_number,
                       c.name AS colony_name,
                       COALESCE(u1.name, u2.name,
                           (SELECT pb.customer_name FROM plot_bookings pb
                            WHERE pb.plot_id = b.plot_id
                              AND (pb.status IS NULL OR pb.status NOT IN ('cancelled'))
                            ORDER BY pb.id DESC LIMIT 1)) AS customer_name,
                       COALESCE(u1.phone, u2.phone,
                           (SELECT pb.customer_phone FROM plot_bookings pb
                            WHERE pb.plot_id = b.plot_id
                              AND (pb.status IS NULL OR pb.status NOT IN ('cancelled'))
                            ORDER BY pb.id DESC LIMIT 1)) AS customer_phone
                FROM booking_payment_schedules s
                LEFT JOIN bookings b ON b.id = s.booking_id
                LEFT JOIN plots p ON p.id = b.plot_id
                LEFT JOIN colonies c ON c.id = COALESCE(b.colony_id, p.colony_id)
                LEFT JOIN users u1 ON u1.id = b.customer_id
                LEFT JOIN users u2 ON u2.id = b.user_id
                WHERE s.status IN ('pending','overdue') AND s.due_date < CURDATE(){$sSql}
                ORDER BY overdue_days DESC
                LIMIT 500
            ", $sParams) ?: [];
        } catch (\Exception $e) {
            error_log('ErpDashboardController::emiDefaulters: ' . $e->getMessage());
            $rows = [];
        }
        $buckets = ['grace' => 0, 'warning' => 0, 'late' => 0, 'critical' => 0];
        $totalDue = 0.0;
        foreach ($rows as &$r) {
            $d = (int)($r['overdue_days'] ?? 0);
            if ($d >= 90) { $r['bucket'] = 'critical'; $r['bucket_label'] = '90+ Days — Critical'; }
            elseif ($d >= 61) { $r['bucket'] = 'late'; $r['bucket_label'] = '61–90 Days — Late Fee'; }
            elseif ($d >= 31) { $r['bucket'] = 'warning'; $r['bucket_label'] = '31–60 Days — Warning'; }
            else { $r['bucket'] = 'grace'; $r['bucket_label'] = '1–30 Days — Gentle Reminder'; }
            $buckets[$r['bucket']]++;
            $totalDue += (float)($r['due_amount'] ?? 0);
        }
        unset($r);
        $this->render('admin/erp/emi_defaulters', [
            'page_title' => 'Overdue EMI Tracker',
            'rows' => $rows,
            'buckets' => $buckets,
            'total_due' => $totalDue,
            'currentPage' => 'erp-defaulters'
        ]);
    }

    /**
     * 1-click EMI reminder: pre-filled WhatsApp click-to-chat URL + optional
     * template send via WhatsAppTemplateService. Updates reminder counters.
     * POST {schedule_id}
     */
    public function sendEmiReminder()
    {
        $this->requireAdmin();
        $input = method_exists($this, 'getPostInput') ? $this->getPostInput() : $_POST;
        $scheduleId = (int)($input['schedule_id'] ?? 0);
        if ($scheduleId <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid schedule ID'], 400);
            return;
        }
        try {
            [$sSql, $sParams] = $this->tCond('s');
            $row = $this->db->fetch("
                SELECT s.id AS schedule_id, s.installment_no, s.due_date, s.amount,
                       (s.amount - COALESCE(s.paid_amount, 0)) AS due_amount,
                       DATEDIFF(CURDATE(), s.due_date) AS overdue_days,
                       b.booking_number, p.plot_number, c.name AS colony_name,
                       COALESCE(u1.name, u2.name, 'Customer') AS customer_name,
                       COALESCE(u1.phone, u2.phone, '') AS customer_phone
                FROM booking_payment_schedules s
                LEFT JOIN bookings b ON b.id = s.booking_id
                LEFT JOIN plots p ON p.id = b.plot_id
                LEFT JOIN colonies c ON c.id = COALESCE(b.colony_id, p.colony_id)
                LEFT JOIN users u1 ON u1.id = b.customer_id
                LEFT JOIN users u2 ON u2.id = b.user_id
                WHERE s.id = ?{$sSql}
            ", array_merge([$scheduleId], $sParams));
        } catch (\Exception $e) {
            error_log('ErpDashboardController::sendEmiReminder load: ' . $e->getMessage());
            $row = null;
        }
        if (!$row) {
            $this->json(['success' => false, 'error' => 'EMI schedule not found'], 404);
            return;
        }
        $digits = preg_replace('/[^0-9]/', '', (string)($row['customer_phone'] ?? ''));
        if (strlen($digits) === 10) $digits = '91' . $digits;
        if (strlen($digits) < 12) {
            $this->json(['success' => false, 'error' => 'Customer phone number not available'], 422);
            return;
        }
        $base = defined('BASE_URL') ? BASE_URL : '';
        $msg = 'Namaste ' . ($row['customer_name'] ?? 'Customer') . ', APS Dream Home: '
            . 'Plot ' . ($row['plot_number'] ?? '-') . ' (' . ($row['colony_name'] ?? '') . ') ki EMI '
            . 'Rs.' . number_format((float)($row['due_amount'] ?? 0), 2)
            . ' due date ' . ($row['due_date'] ?? '') . ' se pending hai ('
            . (int)($row['overdue_days'] ?? 0) . ' din overdue, Booking '
            . ($row['booking_number'] ?? ('#' . ($row['schedule_id'] ?? ''))) . '). '
            . 'Kripya jald bhugtan karein. Sampark: +91 92771 21112. ' . $base;
        $waUrl = 'https://wa.me/' . $digits . '?text=' . urlencode($msg);
        $apiStatus = 'click-to-chat link generated (WhatsApp API not configured)';
        try {
            $wa = new \App\Services\Communication\WhatsAppTemplateService();
            $res = $wa->sendEmiReminder($digits, [
                'customer_name' => $row['customer_name'] ?? 'Customer',
                'emi_number' => 'EMI #' . ($row['installment_no'] ?? ''),
                'amount' => (float)($row['due_amount'] ?? 0),
                'due_date' => $row['due_date'] ?? '',
                'booking_number' => $row['booking_number'] ?? '',
            ]);
            if (!empty($res['success'])) $apiStatus = 'WhatsApp template sent';
            else $apiStatus = 'Template API: ' . ($res['error'] ?? 'not sent') . ' — use click-to-chat link';
        } catch (\Throwable $e) {
            error_log('ErpDashboardController::sendEmiReminder template: ' . $e->getMessage());
        }
        try {
            $tid = (int)$this->tenantId();
            $uSql = $tid > 1 ? ' AND tenant_id = ?' : '';
            $uParams = $tid > 1 ? [$tid] : [];
            $this->db->execute("
                UPDATE booking_payment_schedules
                SET reminder_count = COALESCE(reminder_count, 0) + 1, last_reminder_at = NOW()
                WHERE id = ?{$uSql}
            ", array_merge([$scheduleId], $uParams));
        } catch (\Exception $e) {
            error_log('ErpDashboardController::sendEmiReminder counter: ' . $e->getMessage());
        }
        $this->json(['success' => true, 'whatsapp_url' => $waUrl, 'api_status' => $apiStatus]);
    }
}
