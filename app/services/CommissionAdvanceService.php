<?php

namespace App\Services;

use App\Core\Database\Database;

/**
 * Enterprise Associate Commission Advance & Festival Loan Service
 * Manages advance requests, approval, bank disbursement, and automatic
 * recovery deduction from weekly/monthly commission payouts.
 */
class CommissionAdvanceService
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Associate applies for a commission advance.
     */
    public function requestAdvance(int $associateId, float $amount, int $tenureMonths, string $purpose): array
    {
        if ($amount < 1000) {
            return ['success' => false, 'message' => 'Minimum advance request amount is ₹1,000.'];
        }

        // Check for active unsettled advance
        $active = $this->pdo->prepare(
            "SELECT id, status, remaining_amount
             FROM (SELECT id, status, (approved_amount - recovered_amount) as remaining_amount FROM commission_advances WHERE associate_id = ? AND status IN ('pending', 'approved', 'disbursed')) as t
             WHERE remaining_amount > 0 OR status = 'pending'
             LIMIT 1"
        );
        $active->execute([$associateId]);
        if ($active->fetch()) {
            return ['success' => false, 'message' => 'You already have an active advance under review or repayment. Settle prior advance before requesting a new one.'];
        }

        $st = $this->pdo->prepare(
            "INSERT INTO commission_advances (tenant_id, associate_id, requested_amount, approved_amount, tenure_months, deduction_pct, recovered_amount, status, purpose, created_at)
             VALUES (1, ?, ?, 0.00, ?, 25.00, 0.00, 'pending', ?, NOW())"
        );
        $st->execute([$associateId, $amount, max(1, $tenureMonths), trim($purpose)]);
        $id = (int)$this->pdo->lastInsertId();

        return ['success' => true, 'advance_id' => $id, 'message' => "Advance request #{$id} submitted for management review."];
    }

    /**
     * Admin approves the advance request with approved amount and deduction rate.
     */
    public function approveAdvance(int $advanceId, int $adminId, float $approvedAmount, float $deductionPct = 25.0, string $remarks = ''): array
    {
        $st = $this->pdo->prepare(
            "UPDATE commission_advances
             SET approved_amount = ?,
                 deduction_pct = ?,
                 admin_remarks = ?,
                 approved_by = ?,
                 status = 'approved',
                 updated_at = NOW()
             WHERE id = ? AND status = 'pending'"
        );
        $st->execute([$approvedAmount, $deductionPct, $remarks, $adminId, $advanceId]);

        return ['success' => true, 'message' => "Advance #{$advanceId} approved for ₹" . number_format($approvedAmount, 2)];
    }

    /**
     * Admin records disbursement with bank UTR reference.
     */
    public function disburseAdvance(int $advanceId, int $adminId, string $disbursementRef): array
    {
        $st = $this->pdo->prepare(
            "UPDATE commission_advances
             SET disbursement_ref = ?,
                 status = 'disbursed',
                 disbursed_at = NOW(),
                 updated_at = NOW()
             WHERE id = ? AND status = 'approved'"
        );
        $st->execute([$disbursementRef, $advanceId]);

        return ['success' => true, 'message' => "Advance #{$advanceId} marked disbursed (Ref: {$disbursementRef})."];
    }

    /**
     * Admin rejects the advance request.
     */
    public function rejectAdvance(int $advanceId, int $adminId, string $remarks): array
    {
        $st = $this->pdo->prepare(
            "UPDATE commission_advances
             SET admin_remarks = ?,
                 approved_by = ?,
                 status = 'rejected',
                 updated_at = NOW()
             WHERE id = ? AND status = 'pending'"
        );
        $st->execute([$remarks, $adminId, $advanceId]);

        return ['success' => true, 'message' => "Advance #{$advanceId} rejected."];
    }

    /**
     * Calculate deduction for a pending commission payout.
     */
    public function calculateDeduction(int $associateId, float $grossCommission): float
    {
        if ($grossCommission <= 0) return 0.0;

        $st = $this->pdo->prepare(
            "SELECT id, approved_amount, recovered_amount, deduction_pct
             FROM commission_advances
             WHERE associate_id = ? AND status = 'disbursed' AND recovered_amount < approved_amount
             ORDER BY id ASC LIMIT 1"
        );
        $st->execute([$associateId]);
        $adv = $st->fetch(\PDO::FETCH_ASSOC);

        if (!$adv) return 0.0;

        $pending = max(0, (float)$adv['approved_amount'] - (float)$adv['recovered_amount']);
        $pct = (float)($adv['deduction_pct'] ?: 25.0);
        $scheduledDeduction = round(($grossCommission * $pct) / 100, 2);

        return min($scheduledDeduction, $pending);
    }

    /**
     * Apply recovery deduction when payout is disbursed.
     */
    public function recordRecovery(int $associateId, float $amount): void
    {
        if ($amount <= 0) return;

        $st = $this->pdo->prepare(
            "SELECT id, approved_amount, recovered_amount
             FROM commission_advances
             WHERE associate_id = ? AND status = 'disbursed' AND recovered_amount < approved_amount
             ORDER BY id ASC"
        );
        $st->execute([$associateId]);
        $advances = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $toRecover = $amount;
        foreach ($advances as $adv) {
            if ($toRecover <= 0) break;

            $pending = (float)$adv['approved_amount'] - (float)$adv['recovered_amount'];
            $chunk = min($toRecover, $pending);
            $newRecovered = (float)$adv['recovered_amount'] + $chunk;
            $newStatus = ($newRecovered >= (float)$adv['approved_amount']) ? 'repaid' : 'disbursed';
            $repaidAt = ($newStatus === 'repaid') ? date('Y-m-d H:i:s') : null;

            $upd = $this->pdo->prepare(
                "UPDATE commission_advances
                 SET recovered_amount = ?, status = ?, repaid_at = COALESCE(?, repaid_at), updated_at = NOW()
                 WHERE id = ?"
            );
            $upd->execute([$newRecovered, $newStatus, $repaidAt, (int)$adv['id']]);

            $toRecover -= $chunk;
        }
    }

    /**
     * Get advances for an associate.
     */
    public function getAssociateAdvances(int $associateId): array
    {
        $st = $this->pdo->prepare(
            "SELECT * FROM commission_advances
             WHERE associate_id = ?
             ORDER BY created_at DESC"
        );
        $st->execute([$associateId]);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get all advances with associate details for admin desk.
     */
    public function getAllAdvances(?string $status = null): array
    {
        $sql = "SELECT ca.*, u.name AS associate_name, u.phone AS associate_phone, u.email AS associate_email
                FROM commission_advances ca
                JOIN users u ON u.id = ca.associate_id";

        $params = [];
        if ($status && $status !== 'all') {
            $sql .= " WHERE ca.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY ca.created_at DESC";

        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
