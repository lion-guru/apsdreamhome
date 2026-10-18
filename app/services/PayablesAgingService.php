<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class PayablesAgingService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAging(string $asOfDate, int $vendorId = 0): array
    {
        $tid = $this->tenantId();
        $params = [$asOfDate];
        $where = "status IN ('pending','approved') AND COALESCE(balance, 0) > 0 AND bill_date <= ?";
        if ($tid > 1) {
            $where .= " AND tenant_id = ?";
            $params[] = $tid;
        }
        if ($vendorId > 0) {
            $where .= " AND vendor_id = ?";
            $params[] = $vendorId;
        }

        $sql = "SELECT id, vendor_id, vendor_name, bill_number, bill_date, gross_amount, paid_amount, balance, status, currency,
                       DATEDIFF(?, bill_date) AS age_days
                FROM vendor_payments
                WHERE $where
                ORDER BY bill_date ASC, id ASC";
        $rows = $this->db->fetchAll($sql, array_merge([$asOfDate], $params)) ?: [];

        $buckets = [
            'current' => ['label' => '0-30', 'count' => 0, 'amount' => 0.0],
            'days_31_60' => ['label' => '31-60', 'count' => 0, 'amount' => 0.0],
            'days_61_90' => ['label' => '61-90', 'count' => 0, 'amount' => 0.0],
            'days_90_plus' => ['label' => '90+', 'count' => 0, 'amount' => 0.0],
        ];
        $total = 0.0;
        $totalRows = 0;

        foreach ($rows as &$row) {
            $balance = (float)($row['balance'] ?? 0);
            if ($balance < 0) {
                $balance = 0.0;
            }
            $age = (int)($row['age_days'] ?? 0);
            if ($age < 0) {
                $age = 0;
            }
            if ($age <= 30) {
                $key = 'current';
            } elseif ($age <= 60) {
                $key = 'days_31_60';
            } elseif ($age <= 90) {
                $key = 'days_61_90';
            } else {
                $key = 'days_90_plus';
            }
            $row['balance'] = $balance;
            $row['bucket'] = $key;
            $buckets[$key]['count']++;
            $buckets[$key]['amount'] += $balance;
            $total += $balance;
            $totalRows++;
        }
        unset($row);

        return [
            'as_of_date' => $asOfDate,
            'rows' => $rows,
            'buckets' => $buckets,
            'total' => round($total, 2),
            'count' => $totalRows,
        ];
    }
}
