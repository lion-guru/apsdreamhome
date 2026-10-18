<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class FundFlowService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getFundFlow(string $startDate, string $endDate): array
    {
        $tid = $this->tenantId();
        $tSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $rangeParams = $tid > 1 ? [$startDate, $endDate, $tid] : [$startDate, $endDate];

        $income = (float)($this->db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) AS total FROM income_records WHERE income_date >= ? AND income_date <= ? AND status != 'cancelled'" . $tSql,
            $rangeParams
        )['total'] ?? 0);

        $expense = (float)($this->db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) AS total FROM expense_records WHERE created_at >= ? AND created_at <= ? AND status != 'rejected'" . $tSql,
            $rangeParams
        )['total'] ?? 0);

        $netProfit = $income - $expense;

        $depreciation = (float)($this->db->fetchOne(
            "SELECT COALESCE(SUM(depreciation_amount), 0) AS total FROM asset_depreciation_schedules WHERE created_at >= ? AND created_at <= ?" . $tSql,
            $rangeParams
        )['total'] ?? 0);

        $fixedAssetPurchases = (float)($this->db->fetchOne(
            "SELECT COALESCE(SUM(purchase_cost), 0) AS total FROM fixed_assets WHERE purchase_date >= ? AND purchase_date <= ?" . $tSql,
            $rangeParams
        )['total'] ?? 0);

        $fixedAssetDisposals = (float)($this->db->fetchOne(
            "SELECT COALESCE(SUM(disposal_amount), 0) AS total FROM fixed_assets WHERE disposal_date >= ? AND disposal_date <= ?" . $tSql,
            $rangeParams
        )['total'] ?? 0);

        $openingDate = date('Y-m-d', strtotime($startDate . ' -1 day'));
        $closingDate = $endDate;

        $opening = $this->workingCapitalBalances($openingDate, $tid);
        $closing = $this->workingCapitalBalances($closingDate, $tid);

        $receivablesChange = $closing['receivables'] - $opening['receivables'];
        $inventoryChange = $closing['inventory'] - $opening['inventory'];
        $payablesChange = $closing['payables'] - $opening['payables'];

        $sources = [
            'net_profit' => $netProfit,
            'depreciation' => $depreciation,
            'decrease_receivables' => $receivablesChange < 0 ? abs($receivablesChange) : 0.0,
            'decrease_inventory' => $inventoryChange < 0 ? abs($inventoryChange) : 0.0,
            'increase_payables' => $payablesChange > 0 ? $payablesChange : 0.0,
            'fixed_asset_disposals' => $fixedAssetDisposals,
            'increase_long_term_liabilities' => 0.0,
            'issue_of_capital' => 0.0,
        ];

        $uses = [
            'increase_receivables' => $receivablesChange > 0 ? $receivablesChange : 0.0,
            'increase_inventory' => $inventoryChange > 0 ? $inventoryChange : 0.0,
            'decrease_payables' => $payablesChange < 0 ? abs($payablesChange) : 0.0,
            'purchase_fixed_assets' => $fixedAssetPurchases,
            'decrease_long_term_liabilities' => 0.0,
            'dividends' => 0.0,
        ];

        $totalSources = array_sum($sources);
        $totalUses = array_sum($uses);
        $netChange = $totalSources - $totalUses;

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'net_profit' => $netProfit,
            'depreciation' => $depreciation,
            'fixed_asset_purchases' => $fixedAssetPurchases,
            'fixed_asset_disposals' => $fixedAssetDisposals,
            'opening_working_capital' => $opening['total'],
            'closing_working_capital' => $closing['total'],
            'working_capital_change' => $closing['total'] - $opening['total'],
            'sources' => $sources,
            'uses' => $uses,
            'total_sources' => $totalSources,
            'total_uses' => $totalUses,
            'net_change' => $netChange,
            'components' => [
                'receivables' => ['opening' => $opening['receivables'], 'closing' => $closing['receivables'], 'change' => $receivablesChange],
                'inventory' => ['opening' => $opening['inventory'], 'closing' => $closing['inventory'], 'change' => $inventoryChange],
                'payables' => ['opening' => $opening['payables'], 'closing' => $closing['payables'], 'change' => $payablesChange],
            ],
        ];
    }

    private function workingCapitalBalances(string $date, int $tid): array
    {
        $tSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$date, $tid] : [$date];

        $receivables = (float)($this->db->fetchOne(
            "SELECT COALESCE(SUM(b.total_plot_value - COALESCE(r.paid, 0)), 0) AS total
             FROM plot_bookings b
             LEFT JOIN (SELECT booking_id, SUM(amount) AS paid FROM booking_payment_receipts WHERE status = 'cleared' AND receipt_date <= ? GROUP BY booking_id) r ON r.booking_id = b.id
             WHERE b.booking_date <= ? AND (b.status IS NULL OR b.status NOT IN ('cancelled'))" . $tSql,
            array_merge([$date], $params)
        )['total'] ?? 0);

        $inventory = (float)($this->db->fetchOne(
            "SELECT COALESCE(SUM(total_price), 0) AS total FROM plots WHERE status = 'available' AND created_at <= ?" . $tSql,
            $params
        )['total'] ?? 0);

        $payables = (float)($this->db->fetchOne(
            "SELECT COALESCE(SUM(balance), 0) AS total FROM vendor_payments WHERE status IN ('pending','approved') AND bill_date <= ?" . $tSql,
            $params
        )['total'] ?? 0);

        return [
            'receivables' => $receivables,
            'inventory' => $inventory,
            'payables' => $payables,
            'total' => $receivables + $inventory - $payables,
        ];
    }
}
