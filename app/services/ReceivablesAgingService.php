<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class ReceivablesAgingService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAging(string $asOfDate, int $colonyId = 0): array
    {
        $tid = $this->tenantId();
        $params = [$asOfDate];
        $where = "s.due_date <= ? AND s.status IN ('pending','overdue','partial') AND (s.amount - COALESCE(s.paid_amount, 0)) > 0";
        if ($tid > 1) {
            $where .= " AND b.tenant_id = ?";
            $params[] = $tid;
        }
        if ($colonyId > 0) {
            $where .= " AND b.colony_id = ?";
            $params[] = $colonyId;
        }

        $sql = "SELECT s.id AS schedule_id, s.installment_no, s.due_date, s.amount, s.paid_amount,
                       b.id AS booking_id, b.booking_number, b.customer_name, b.customer_phone,
                       b.colony_id, c.name AS colony_name, DATEDIFF(?, s.due_date) AS age_days
                FROM booking_payment_schedules s
                INNER JOIN plot_bookings b ON b.id = s.booking_id
                LEFT JOIN colonies c ON c.id = b.colony_id
                WHERE $where
                ORDER BY s.due_date ASC, b.id ASC";
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
            $outstanding = (float)$row['amount'] - (float)($row['paid_amount'] ?? 0);
            if ($outstanding < 0) {
                $outstanding = 0.0;
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
            $row['outstanding'] = $outstanding;
            $row['bucket'] = $key;
            $buckets[$key]['count']++;
            $buckets[$key]['amount'] += $outstanding;
            $total += $outstanding;
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
