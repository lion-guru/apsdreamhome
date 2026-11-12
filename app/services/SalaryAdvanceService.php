<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use Exception;

class SalaryAdvanceService
{
    /**
     * Query runner: fetch a single row.
     *
     * NOTE (S451): this service calls $this->fetchOne($sql, $params) but the
     * helper never existed -> "Call to undefined method <Service>::fetchOne()".
     */
    private function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[' . __CLASS__ . '::fetchOne] ' . $e->getMessage());
            return null;
        }
    }

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

        // Check employee exists and is active (via profile, legacy-id compatible)
        $emp = $this->fetchOne("
            SELECT ep.legacy_id as id, u.id as user_id, u.name as name, ep.department, ep.designation,
                   CAST(ep.salary AS DECIMAL(12,2)) as salary, ep.status as status
            FROM employee_profiles ep
            JOIN users u ON u.id = ep.user_id
            WHERE (ep.legacy_id = ? OR ep.user_id = ? OR ep.id = ?) AND ep.status = 'active'{$this->tenantSql()}
        ", array_merge([$employeeId, $employeeId, $employeeId], $this->tVal()));

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
        } catch (\Throwable $e) {
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
            SELECT sa.*, u_emp.name as employee_name, ep.employee_code, ep.department, u.email
            FROM salary_advances sa
            LEFT JOIN employee_profiles ep ON ep.legacy_id = sa.employee_id OR ep.user_id = sa.employee_id OR ep.id = sa.employee_id
            LEFT JOIN users u_emp ON u_emp.id = ep.user_id
            LEFT JOIN users u ON u.id = ep.user_id
            WHERE sa.id = ?{$this->tenantSql()}
        ", array_merge([$advanceId], $this->tVal()));
    }

    /**
     * Get employee's advances
     */
    public function getEmployeeAdvances(int $employeeId): array
    {
        return $this->db->fetchAll("
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
        return $this->db->fetchAll("
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
            $this->creditWallet($advance['employee_id'], $advance['amount'], "Salary advance approved", (int)$advanceId, 'salary_advance');
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
            $this->creditWallet($advance['employee_id'], $advance['amount'], $advance['type'] === 'loan' ? 'Loan disbursed' : 'Salary advance disbursed', (int)$advanceId, 'salary_advance');

            $this->db->commit();
            return ['success' => true, 'message' => 'Amount disbursed successfully'];
        } catch (\Throwable $e) {
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
    /**
     * Credit the employee's wallet after an advance is approved / disbursed.
     *
     * S466: this used to do its own money movement, and EVERY statement it
     * wrote was wrong:
     *   UPDATE user_wallets SET balance = ?, total_earned = total_earned + ?
     *       - `user_wallets` is a SECOND, parallel wallet table, and its
     *         column is `total_credited`, not `total_earned` -> 1054
     *   INSERT INTO wallet_transactions (wallet_id, type, ..., reference)
     *       - none of `wallet_id`, `type` or `reference` exists on that
     *         table (it has user_id, transaction_type, transaction_category,
     *         reference_id, reference_type) -> 1054
     * Both statements were inside a try/catch that swallowed the error into
     * `error_log("Wallet credit failed: ...")`, so an APPROVED salary advance
     * never reached the employee's wallet at all - and the failure was
     * invisible to the user and to the admin who approved it.
     *
     * The canonical path is App\Services\WalletService::credit(), which
     * locks the wallet row FOR UPDATE, fails closed on a non-positive
     * amount, writes the balance AND the ledger row in ONE transaction and
     * uses the real column names.
     */
    private function creditWallet(int $employeeId, float $amount, string $description, int $referenceId, string $referenceType = 'salary_advance'): void
    {
        try {
            // employees live in `users`; employee_profiles only carries the
            // legacy bridge (legacy_id / user_id / id).
            $row = $this->fetchOne(
                "SELECT user_id FROM employee_profiles WHERE legacy_id = ? OR user_id = ? OR id = ? LIMIT 1",
                [(int)$employeeId, (int)$employeeId, (int)$employeeId]
            );
            $userId = (int)($row['user_id'] ?? 0);
            if ($userId <= 0) {
                error_log("SalaryAdvanceService: no users.id for employee {$employeeId} - wallet NOT credited");
                return;
            }

            $walletService = new \App\Services\WalletService();
            $credited = $walletService->credit(
                $userId,
                $amount,
                'adjustment',
                $description,
                $referenceId > 0 ? $referenceId : null,
                $referenceType
            );
            if (!$credited) {
                error_log("SalaryAdvanceService: wallet credit REJECTED for user {$userId} (amount {$amount})");
            }
        } catch (\Throwable $e) {
            error_log("Wallet credit failed: " . $e->getMessage());
        }
    }

    /**
     * Get pending advances for payroll processing
     */
    public function getPendingRepayments(int $employeeId): array
    {
        return $this->db->fetchAll("
            SELECT sar.*, sa.type as advance_type
            FROM salary_advance_repayments sar
            JOIN salary_advances sa ON sar.advance_id = sa.id
            WHERE sar.employee_id = ? AND sar.status IN ('pending','overdue') AND sar.due_date <= CURDATE()" . $this->tenantSql() . "
            ORDER BY sar.due_date
        ", [$employeeId]);
    }
}