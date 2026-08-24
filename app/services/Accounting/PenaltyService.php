<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Penalty Service
 * Handles daily penalties, overdue penalty summaries, and penalty automation
 */
class PenaltyService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function applyDailyPenalties(): array
    {
        $tid = TenantContext::getId();
        $today = date('Y-m-d');

        // Find overdue installments
        $stmt = $this->db->fetchAll("
            SELECT bps.*, pb.plot_id, pb.customer_id
            FROM booking_payment_schedules bps
            JOIN plot_bookings pb ON pb.id = bps.booking_id
            WHERE bps.due_date < ?
            AND bps.status IN ('pending', 'partial')
            AND pb.status NOT IN ('cancelled', 'refunded')
            " . ($tid > 1 ? " AND pb.tenant_id = ?" : ""),
            $tid > 1 ? [$today, $tid] : [$today]
        );

        $results = ['processed' => 0, 'total_penalty' => 0.0, 'details' => []];

        foreach ($stmt as $installment) {
            $daysOverdue = (int)((strtotime($today) - strtotime($installment['due_date'])) / 86400);
            $graceDays = 5;
            if ($daysOverdue <= $graceDays) continue;

            $penaltyRate = 0.18 / 365; // 18% per annum
            $penaltyAmount = round($installment['amount_due'] * $penaltyRate * ($daysOverdue - $graceDays), 2);

            if ($penaltyAmount <= 0) continue;

            // Update or insert penalty
            $existing = $this->db->fetchOne("SELECT * FROM penalty_audit WHERE installment_id = ? AND penalty_date = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), array_merge([$installment['id'], $today], $tid > 1 ? [$tid] : []));

            if ($existing) {
                $newAccrued = $existing['accrued_penalty'] + $penaltyAmount;
                $this->db->execute("UPDATE penalty_audit SET accrued_penalty = ?, days_overdue = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), array_merge([$newAccrued, $daysOverdue, $existing['id']], $tid > 1 ? [$tid] : []));
            } else {
                $this->db->insert('penalty_audit', [
                    'installment_id'    => $installment['id'],
                    'penalty_date'      => $today,
                    'days_overdue'      => $daysOverdue,
                    'penalty_rate'      => 0.18,
                    'accrued_penalty'   => $penaltyAmount,
                    'paid_penalty'      => 0,
                    'status'            => 'accrued',
                    'tenant_id'         => TenantContext::getId(),
                ]);
            }

            // Update installment
            $this->db->execute("UPDATE booking_payment_schedules SET accrued_penalty = accrued_penalty + ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), array_merge([$penaltyAmount, $installment['id']], $tid > 1 ? [$tid] : []));

            $results['processed']++;
            $results['total_penalty'] += $penaltyAmount;
            $results['details'][] = [
                'installment_id' => $installment['id'],
                'days_overdue'   => $daysOverdue,
                'penalty'        => $penaltyAmount,
            ];
        }

        return ['success' => true, ...$results];
    }

    public function getOverduePenaltySummary(): array
    {
        $tid = TenantContext::getId();
        $today = date('Y-m-d');

        $stmt = $this->db->fetchAll("
            SELECT pa.*, bps.amount_due, pb.booking_number, pb.customer_id
            FROM penalty_audit pa
            JOIN booking_payment_schedules bps ON bps.id = pa.installment_id
            JOIN plot_bookings pb ON pb.id = bps.booking_id
            WHERE pa.status = 'accrued'
            " . ($tid > 1 ? " AND pa.tenant_id = ?" : ""),
            $tid > 1 ? [$tid] : []
        );

        $totalAccrued = array_sum(array_column($stmt, 'accrued_penalty'));
        $totalPaid = array_sum(array_column($stmt, 'paid_penalty'));

        return [
            'total_overdue_installments' => count($stmt),
            'total_accrued_penalty'      => round(array_sum(array_column($stmt, 'accrued_penalty')), 2),
            'total_paid_penalty'         => round($totalPaid, 2),
            'outstanding_penalty'        => round($totalAccrued - $totalPaid, 2),
            'details'                    => $stmt,
        ];
    }

    public function recordPenaltyPayment(int $installmentId, float $amount): bool
    {
        $tid = TenantContext::getId();
        $this->db->execute("UPDATE penalty_audit SET paid_penalty = paid_penalty + ?, status = CASE WHEN paid_penalty + ? >= accrued_penalty THEN 'paid' ELSE 'partial' END WHERE installment_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), array_merge([$amount, $amount, $installmentId], $tid > 1 ? [$tid] : []));

        // Also update installment
        $this->db->execute("UPDATE booking_payment_schedules SET accrued_penalty = GREATEST(0, accrued_penalty - ?) WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), array_merge([$amount, $installmentId], $tid > 1 ? [$tid] : []));

        return true;
    }
}