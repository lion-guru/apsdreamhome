<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use Exception;

class SalaryAdvanceService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Request a salary advance or loan
     */
    public function requestAdvance(int $employeeId, array $data): array
    {
        $tid = $this->tenantId();

        // Validate
        $type = $data['type'] ?? 'advance';
        if (!in_array($type, ['advance', 'loan'])) {
            return ['success' => false, 'message' => 'Invalid type. Must be advance or loan'];
        }

        $amount = (float)($data['amount'] ?? 0);
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Amount must be greater than zero'];
        }

        // Check employee exists and is active
        $emp = $this->fetchOne("
            SELECT e.id, e.user_id, e.name, e.department, e.designation, e.salary, e.status
            FROM employees e
            WHERE e.id = ? AND e.status = 'active'{$this->tenantSql()}
        ", array_merge([$employeeId], $this->tVal()));

        if (!$emp) {
            return ['success' => false, 'message' => 'Employee not found or inactive'];
        }

        // Check for existing pending requests
        $pending = $this->fetchOne(
            "SELECT id FROM salary_advances WHERE employee_id = ? AND status IN ('pending','approved'){$this->tenantSql()}",
            array_merge([$employeeId], $this->tVal())
        );
        if ($pending) {
            return ['success' => false, 'message' => 'You already have a pending/approved advance/loan'];
        }

        // Calculate EMI for loans
        $installments = (int)($data['installments'] ?? 1);
        $interestRate = (float)($data['interest_rate'] ?? 0);

        if ($type === 'loan' && $installments < 2) {
            return ['success' => false, 'message' => 'Loans require at least 2 installments'];
        }

        if ($type === 'loan' && $installments > 24) {
            return ['success' => false, 'message' => 'Maximum 24 installments allowed'];
        }

        $emiAmount = 0;
        if ($type === 'loan' && $installments > 1) {
            $monthlyRate = $interestRate / 100 / 12;
            if ($monthlyRate > 0) {
                $emiAmount = $amount * $monthlyRate * pow(1 + $monthlyRate, $installments) / (pow(1 + $monthlyRate, $installments) - 1);
            } else {
                $emiAmount = $amount / $installments;
            }
            $emiAmount = round($emiAmount, 2);
        }

        $this->db->beginTransaction();
        try {
            $this->execute("
                INSERT INTO salary_advances 
                (tenant_id, employee_id, type, amount, installments, emi_amount, interest_rate, status, requested_at, tenant_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), ?)
            ", [$tid, $employeeId, $type, $amount, $installments, $emiAmount, $interestRate, $tid]);

            $advanceId = $this->db->lastInsertId();

            // Generate repayment schedule for loans
            if ($type === 'loan' && $installments > 1) {
                $this->generateRepaymentSchedule($advanceId, $amount, $installments, $emiAmount, $interestRate);
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => ucfirst($type) . ' request submitted successfully',
                'advance_id' => $advanceId,
                'emi_amount' => $emiAmount
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Generate repayment schedule for loans
     */
    private function generateRepaymentSchedule(int $advanceId, float $amount, int $installments, float $emiAmount, float $interestRate): void
    {
        $principalRemaining = $amount;
        $monthlyRate = ($interestRate / 100) / 12;

        for ($i = 1; $i <= $installments; $i++) {
            $interestAmt = round($principalRemaining * ($interestRate / 100 / 12), 2);
            $principalAmt = round($emiAmount - $interestAmt, 2);

            // Adjust for last installment
            if ($i === $installments) {
                $principalAmt = round($principalRemaining, 2);
                $emiAmount = $principalAmt + $interestAmt;
            }

            $principalRemaining = max(0, $principalRemaining - $principalAmt);
            $dueDate = date('Y-m-d', strtotime("+$i months"));

            $this->execute("
                INSERT INTO salary_advance_repayments
                (tenant_id, advance_id, employee_id, installment_no, principal_amount, interest_amount, total_amount, due_date, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ", [1, $advanceId, $this->getEmployeeIdFromAdvance($advanceId), $i, $principalAmt, $interestAmt, $emiAmount, $dueDate]);
        }
    }

    /**
     * Get employee ID from advance
     */
    private function getEmployeeIdFromAdvance(int $advanceId): int
    {
        return (int)$this->fetchOne("SELECT employee_id FROM salary_advances WHERE id = ?", [$advanceId])['employee_id'] ?? 0;
    }

    /**
     * Get advance details
     */
    public function getAdvance(int $advanceId): ?array
    {
        return $this->fetchOne("
            SELECT sa.*, e.name as employee_name, e.employee_code, e.department, u.email
            FROM salary_advances sa
            JOIN employees e ON sa.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE sa.id = ?{$this->tenantSql()}
        ", array_merge([$advanceId], $this->tVal()));
    }

    /**
     * Get employee's advances
     */
    public function getEmployeeAdvances(int $employeeId): array
    {
        return $this->fetchAll("
            SELECT * FROM salary_advances
            WHERE employee_id = ?{$this->tenantSql()}
            ORDER BY requested_at DESC
        ", array_merge([$employeeId], $this->tVal()));
    }

    /**
     * Get advance repayment schedule
     */
    public function getRepaymentSchedule(int $advanceId): array
    {
        return $this->fetchAll("
            SELECT * FROM salary_advance_repayments
            WHERE advance_id = ?{$this->tenantSql()}
            ORDER BY installment_no
        ", array_merge([$advanceId], $this->tVal()));
    }

    /**
     * Approve advance/loan
     */
    public function approveAdvance(int $advanceId, int $approverId): array
    {
        $advance = $this->getAdvance($advanceId);
        if (!$advance) return ['success' => false, 'message' => 'Advance not found'];
        if ($advance['status'] !== 'pending') return ['success' => false, 'message' => 'Advance is not pending'];

        $this->execute("
            UPDATE salary_advances 
            SET status = 'approved', approved_at = NOW(), approved_by = ?
            WHERE id = ?{$this->tenantSql()}
        ", array_merge([$approverId, $advanceId], $this->tVal()));

        // If advance, create wallet credit entry
        if ($advance['type'] === 'advance') {
            // Credit to employee wallet
            $this->creditWallet($advance['employee_id'], $advance['amount'], "Salary advance approved", 'salary_advance_' . $advanceId);
        }

        return ['success' => true, 'message' => 'Advance approved'];
    }

    /**
     * Disburse advance/loan
     */
    public function disburseAdvance(int $advanceId, int $disburserId, string $disbursementRef = ''): array
    {
        $advance = $this->getAdvance($advanceId);
        if (!$advance) return ['success' => false, 'message' => 'Advance not found'];
        if ($advance['status'] !== 'approved') return ['success' => false, 'message' => 'Advance not approved'];

        $this->db->beginTransaction();
        try {
            $this->execute("
                UPDATE salary_advances 
                SET status = 'disbursed', disbursed_at = NOW(), disbursed_by = ?, disbursement_ref = ?, remaining_balance = amount
                WHERE id = ?{$this->tenantSql()}
            ", array_merge([$disburserId, $disbursementRef, $advanceId], $this->tVal()));

            // Credit to wallet
            $this->creditWallet($advance['employee_id'], $advance['amount'], $advance['type'] === 'loan' ? 'Loan disbursed' : 'Salary advance disbursed', $advance['type'] . '_' . $advanceId);

            $this->db->commit();
            return ['success' => true, 'message' => 'Amount disbursed successfully'];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Reject advance/loan
     */
    public function rejectAdvance(int $advanceId, int $rejecterId, string $reason): array
    {
        $advance = $this->getAdvance($advanceId);
        if (!$advance) return ['success' => false, 'message' => 'Advance not found'];
        if ($advance['status'] !== 'pending') return ['success' => false, 'message' => 'Advance is not pending'];

        $this->execute("
            UPDATE salary_advances 
            SET status = 'rejected', rejected_at = NOW(), rejected_by = ?, rejection_reason = ?
            WHERE id = ?{$this->tenantSql()}
        ", array_merge([$rejecterId, $reason, $advanceId], $this->tVal()));

        return ['success' => true, 'message' => 'Advance rejected'];
    }

    /**
     * Process repayment (called from payroll)
     */
    public function processRepayment(int $repaymentId, float $paidAmount): array
    {
        $repayment = $this->fetchOne("SELECT * FROM salary_advance_repayments WHERE id = ?", [$repaymentId]);
        if (!$repayment) return ['success' => false, 'message' => 'Repayment not found'];

        $advance = $this->getAdvance($repayment['advance_id']);
        if (!$advance) return ['success' => false, 'message' => 'Advance not found'];

        $paidAmount = min($paidAmount, $repayment['total_amount'] - $repayment['paid_amount']);
        $newPaid = $repayment['paid_amount'] + $paidAmount;
        $newStatus = $newPaid >= $repayment['total_amount'] ? 'paid' : 'pending';

        $this->execute("
            UPDATE salary_advance_repayments
            SET paid_amount = ?, status = ?, paid_at = CASE WHEN ? = 'paid' THEN NOW() ELSE NULL END
            WHERE id = ?
        ", [$newPaid, $newStatus, $newStatus, $repayment['id']]);

        // Update advance remaining balance
        $this->execute("
            UPDATE salary_advances
            SET remaining_balance = remaining_balance - ?
            WHERE id = ?
        ", [$paidAmount, $repayment['advance_id']]);

        // Check if all repayments done
        $pending = $this->fetchOne("SELECT COUNT(*) as cnt FROM salary_advance_repayments WHERE advance_id = ? AND status != 'paid'", [$repayment['advance_id']]);
        if ($pending['cnt'] == 0) {
            $this->execute("UPDATE salary_advances SET status = 'repaid', completed_at = NOW() WHERE id = ?", [$repayment['advance_id']]);
        }

        return ['success' => true, 'message' => 'Repayment processed'];
    }

    /**
     * Credit to employee wallet
     */
    private function creditWallet(int $employeeId, float $amount, string $description, string $reference): void
    {
        try {
            // Get wallet
            $wallet = $this->fetchOne("SELECT * FROM user_wallets WHERE user_id = (SELECT user_id FROM employees WHERE id = ?)", [(int)$employeeId]);
            if (!$wallet) return;

            $newBalance = (float)$wallet['balance'] + $amount;
            $this->execute("UPDATE user_wallets SET balance = ?, total_earned = total_earned + ? WHERE id = ?", [$newBalance, $amount, $wallet['id']]);

            // Log transaction
            $this->execute("
                INSERT INTO wallet_transactions (wallet_id, type, amount, balance_after, description, reference, status, tenant_id, created_at)
                VALUES (?, 'credit', ?, ?, ?, ?, 'completed', 1, NOW())
            ", [$wallet['id'], $amount, $newBalance, $description, 'advance_' . $reference]);
        } catch (\Exception $e) {
            error_log("Wallet credit failed: " . $e->getMessage());
        }
    }

    /**
     * Get pending advances for payroll processing
     */
    public function getPendingRepayments(int $employeeId): array
    {
        return $this->fetchAll("
            SELECT sar.*, sa.type as advance_type
            FROM salary_advance_repayments sar
            JOIN salary_advances sa ON sar.advance_id = sa.id
            WHERE sar.employee_id = ? AND sar.status IN ('pending','overdue') AND sar.due_date <= CURDATE()
            ORDER BY sar.due_date
        ", [$this->tenantId(), $employeeId]);
    }
}