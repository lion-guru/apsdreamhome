<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Cash Flow Forecast Service
 * Handles cash flow forecasting (inflow/outflow with probability)
 */
class CashFlowForecastService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function forecastCashFlow(int $days = 30): array
    {
        $tid = TenantContext::getId();
        $endDate = date('Y-m-d', strtotime("+$days days"));

        // Projected inflows: collections from bookings, EMI receipts
        $inflows = $this->getProjectedInflows($days);

        // Projected outflows: vendor payments, expenses, salaries, TDS/GST deposits
        $outflows = $this->getProjectedOutflows($days);

        $netFlow = array_sum(array_column($inflows, 'amount')) - array_sum(array_column($outflows, 'amount'));

        return [
            'period_days'       => $days,
            'end_date'          => $endDate,
            'total_inflow'      => round(array_sum(array_column($inflows, 'amount')), 2),
            'total_outflow'     => round(array_sum(array_column($outflows, 'amount')), 2),
            'net_cash_flow'     => round($netFlow, 2),
            'inflows'           => $inflows,
            'outflows'          => $outflows,
            'generated_at'      => date('Y-m-d H:i:s'),
        ];
    }

    public function generateForecast(int $days = 30): array
    {
        return $this->forecastCashFlow($days);
    }

    public function getCashForecasts(int $days = 30): array
    {
        // Return stored forecasts if any
        $tid = TenantContext::getId();
        $where = "WHERE forecast_date >= CURDATE() AND forecast_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->fetchAll("SELECT * FROM cash_forecasts $where ORDER BY forecast_date", $tid > 1 ? [$days, $tid] : [$days]) ?: [];
    }

    public function getActualVsForecast(string $fromDate, string $toDate): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE actual_date BETWEEN ? AND ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$fromDate, $toDate];
        if ($tid > 1) $params[] = $tid;

        return $this->db->fetchAll("SELECT * FROM cash_forecasts $where ORDER BY actual_date", $params) ?: [];
    }

    private function getProjectedInflows(int $days): array
    {
        $tid = TenantContext::getId();
        $endDate = date('Y-m-d', strtotime("+$days days"));

        // EMI receipts due in period
        $emis = $this->db->fetchAll("
            SELECT COALESCE(SUM(bps.amount_due), 0) AS total
            FROM booking_payment_schedules bps
            JOIN plot_bookings pb ON pb.id = bps.booking_id
            WHERE bps.due_date BETWEEN CURDATE() AND ?
            AND bps.status IN ('pending', 'partial')
            " . ($tid > 1 ? " AND pb.tenant_id = ?" : ""),
            $tid > 1 ? [$days, $tid] : [$days]
        );

        // Plot sale completions
        $bookings = $this->db->fetchAll("
            SELECT COALESCE(SUM(pb.agreement_value), 0) AS total
            FROM plot_bookings pb
            WHERE pb.possession_date BETWEEN CURDATE() AND ?
            AND pb.status IN ('confirmed', 'emi_active')
            " . ($tid > 1 ? " AND pb.tenant_id = ?" : ""),
            $tid > 1 ? [$days, $tid] : [$days]
        );

        // EMI collections from existing schedule
        $collections = $this->db->fetchAll("
            SELECT COALESCE(SUM(amount), 0) AS total
            FROM cash_collections cc
            JOIN plot_bookings pb ON pb.id = cc.plot_booking_id
            WHERE cc.collection_date BETWEEN CURDATE() AND ?
            AND cc.status = 'pending'
            " . ($tid > 1 ? " AND pb.tenant_id = ?" : ""),
            $tid > 1 ? [$days, $tid] : [$days]
        );

        return [
            ['category' => 'EMI Receipts', 'amount' => (float)($emis[0]['total'] ?? 0), 'probability' => 0.85],
            ['category' => 'Plot Completions', 'amount' => (float)($bookings[0]['total'] ?? 0), 'probability' => 0.70],
            ['category' => 'Pending Collections', 'amount' => (float)($collections[0]['total'] ?? 0), 'probability' => 0.75],
        ];
    }

    private function getProjectedOutflows(int $days): array
    {
        $tid = TenantContext::getId();
        $endDate = date('Y-m-d', strtotime("+$days days"));

        // Vendor payments due
        $vendors = $this->db->fetchAll("
            SELECT COALESCE(SUM(vp.amount), 0) AS total
            FROM vendor_payments vp
            WHERE vp.due_date BETWEEN CURDATE() AND ?
            AND vp.status = 'pending'
            " . ($tid > 1 ? " AND vp.tenant_id = ?" : ""),
            $tid > 1 ? [$days, $tid] : [$days]
        );

        // TDS/GST deposits
        $tdsGst = $this->db->fetchAll("
            SELECT COALESCE(SUM(tds_amount), 0) AS tds, COALESCE(SUM(total_gst), 0) AS gst
            FROM tds_register tr
            LEFT JOIN gst_transactions gt ON gt.financial_year = tr.financial_year AND gt.quarter = tr.quarter
            WHERE tr.deposit_status = 'pending' AND tr.deposit_date BETWEEN CURDATE() AND ?
            " . ($tid > 1 ? " AND tr.tenant_id = ?" : ""),
            $tid > 1 ? [$days, $tid] : [$days]
        );

        // Salaries
        $salaries = $this->db->fetchAll("
            SELECT COALESCE(SUM(net_salary), 0) AS total
            FROM payroll p
            WHERE p.pay_date BETWEEN CURDATE() AND ?
            AND p.status = 'pending'
            " . ($tid > 1 ? " AND p.tenant_id = ?" : ""),
            $tid > 1 ? [$days, $tid] : [$days]
        );

        // Expenses
        $expenses = $this->db->fetchAll("
            SELECT COALESCE(SUM(amount), 0) AS total
            FROM expenses e
            WHERE e.due_date BETWEEN CURDATE() AND ?
            AND e.status = 'pending'
            " . ($tid > 1 ? " AND e.tenant_id = ?" : ""),
            $tid > 1 ? [$days, $tid] : [$days]
        );

        return [
            ['category' => 'Vendor Payments', 'amount' => (float)($vendors[0]['total'] ?? 0), 'probability' => 0.95],
            ['category' => 'TDS Deposits', 'amount' => (float)($tdsGst[0]['tds'] ?? 0), 'probability' => 1.0],
            ['category' => 'GST Deposits', 'amount' => (float)($tdsGst[0]['gst'] ?? 0), 'probability' => 1.0],
            ['category' => 'Salaries', 'amount' => (float)($salaries[0]['total'] ?? 0), 'probability' => 0.95],
            ['category' => 'Expenses', 'amount' => (float)($expenses[0]['total'] ?? 0), 'probability' => 0.80],
        ];
    }
}