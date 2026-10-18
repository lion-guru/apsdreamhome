<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use Exception;

class LeaveEncashmentService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get leave encashment policy for an employee
     */
    public function getEncashmentPolicy(int $employeeId): array
    {
        $emp = $this->fetchOne("
            SELECT e.id, e.designation, e.department, e.joining_date, e.salary
            FROM employees e
            WHERE e.id = ?{$this->tenantSql()}
        ", array_merge([$employeeId], $this->tVal()));

        if (!$emp) return ['eligible' => false, 'message' => 'Employee not found'];

        // Get leave balances
        $balances = $this->fetchAll("
            SELECT lb.*, lt.name as leave_type_name, lt.code as leave_type_code, lt.is_encashable, lt.max_encashable_days
            FROM employee_leave_balances lb
            JOIN leave_types lt ON lb.leave_type_id = lt.id
            WHERE lb.employee_id = ? AND lb.year = YEAR(CURDATE()) AND lt.is_encashable = 1{$this->tenantSql()}
        ", array_merge([$employeeId], $this->tVal()));

        $totalEncashable = 0;
        foreach ($balances as $b) {
            $encashable = min($b['remaining_days'] ?? 0, $b['max_encashable_days'] ?? 0);
            $totalEncashable += $encashable;
        }

        return [
            'eligible' => $totalEncashable > 0,
            'employee' => $emp,
            'balances' => $balances,
            'total_encashable_days' => $totalEncashable,
            'max_encashable_per_request' => 15, // Configurable
            'min_balance_to_keep' => 5 // Must keep minimum days
        ];
    }

    /**
     * Request leave encashment
     */
    public function requestEncashment(int $employeeId, array $data): array
    {
        $policy = $this->getEncashmentPolicy($employeeId);
        if (!$policy['eligible']) {
            return ['success' => false, 'message' => 'Not eligible for leave encashment'];
        }

        $leaveTypeId = (int)($data['leave_type_id'] ?? 0);
        $daysToEncash = (float)($data['days_to_encash'] ?? 0);

        if ($daysToEncash <= 0) {
            return ['success' => false, 'message' => 'Days to encash must be greater than zero'];
        }

        // Validate against policy
        $leaveType = null;
        foreach ($policy['balances'] as $b) {
            if ($b['leave_type_id'] == $leaveTypeId) {
                $leaveType = $b;
                break;
            }
        }

        if (!$leaveType) {
            return ['success' => false, 'message' => 'Invalid leave type'];
        }

        $maxEncashable = min($leaveType['remaining_days'] ?? 0, $leaveType['max_encashable_days'] ?? 0);
        if ($daysToEncash > $maxEncashable) {
            return ['success' => false, 'message' => "Cannot encash more than $maxEncashable days for this leave type"];
        }

        // Check minimum balance to keep
        $remainingAfter = ($leaveType['remaining_days'] ?? 0) - $daysToEncash;
        if ($remainingAfter < 5) { // Min 5 days to keep
            return ['success' => false, 'message' => 'Must keep at least 5 days balance after encashment'];
        }

        // Get employee salary for calculation
        $emp = $this->fetchOne("
            SELECT e.*, ss.basic_salary
            FROM employees e
            LEFT JOIN salary_structures ss ON ss.employee_id = e.id AND ss.status = 'active'
            WHERE e.id = ? ORDER BY ss.effective_date DESC LIMIT 1
        ", [$employeeId]);

        if (!$emp) {
            return ['success' => false, 'message' => 'Employee not found'];
        }

        $basicSalary = (float)($emp['basic_salary'] ?? $emp['salary'] ?? 0);
        if ($basicSalary <= 0) {
            return ['success' => false, 'message' => 'Salary structure not found'];
        }

        // Calculate amount per day (basic / 30)
        $amountPerDay = round($basicSalary / 30, 2);
        $totalAmount = round($daysToEncash * $amountPerDay, 2);

        // Tax deduction (10% for taxable)
        $taxDeduction = round($totalAmount * 0.10, 2);
        $netAmount = round($totalAmount - $taxDeduction, 2);

        $this->db->beginTransaction();
        try {
            $tid = $this->tenantId();
            $this->execute("
                INSERT INTO leave_encashment_requests
                (tenant_id, employee_id, leave_type_id, days_to_encash, eligible_days, amount_per_day, total_amount, tax_deduction, net_amount, status, requested_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ", [$this->tenantId(), $employeeId, $leaveTypeId, $daysToEncash, $leaveType['remaining_days'], $amountPerDay, $totalAmount, $taxDeduction, $netAmount, $tid]);

            $requestId = $this->db->lastInsertId();
            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Leave encashment request submitted',
                'request_id' => $requestId,
                'details' => [
                    'days_requested' => $daysToEncash,
                    'amount_per_day' => $amountPerDay,
                    'total_amount' => $totalAmount,
                    'tax_deduction' => $taxDeduction,
                    'net_amount' => $netAmount
                ]
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get encashment request
     */
    public function getRequest(int $requestId): ?array
    {
        return $this->db->fetchOne("
            SELECT ler.*, lt.name as leave_type_name, lt.code as leave_type_code,
                   e.name as employee_name, e.employee_code, e.department
            FROM leave_encashment_requests ler
            JOIN leave_types lt ON ler.leave_type_id = lt.id
            JOIN employees e ON ler.employee_id = e.id
            WHERE ler.id = ?{$this->tenantSql()}
        ", array_merge([$requestId], $this->tVal()));
    }

    /**
     * Get employee's encashment requests
     */
    public function getEmployeeRequests(int $employeeId): array
    {
        return $this->db->fetchAll("
            SELECT ler.*, lt.name as leave_type_name, lt.code as leave_type_code
            FROM leave_encashment_requests ler
            JOIN leave_types lt ON ler.leave_type_id = lt.id
            WHERE ler.employee_id = ?{$this->tenantSql()}
            ORDER BY ler.requested_at DESC
        ", array_merge([$employeeId], $this->tVal()));
    }

    /**
     * Approve encashment request
     */
    public function approveRequest(int $requestId, int $approverId): array
    {
        $req = $this->getRequest($requestId);
        if (!$req) return ['success' => false, 'message' => 'Request not found'];
        if ($req['status'] !== 'pending') return ['success' => false, 'message' => 'Request is not pending'];

        $this->execute("
            UPDATE leave_encashment_requests
            SET status = 'approved', approved_at = NOW(), approved_by = ?
            WHERE id = ?{$this->tenantSql()}
        ", array_merge([$approverId, $requestId], $this->tVal()));

        return ['success' => true, 'message' => 'Leave encashment approved'];
    }

    /**
     * Pay approved encashment
     */
    public function payEncashment(int $requestId, int $payerId, string $paymentRef = ''): array
    {
        $req = $this->getRequest($requestId);
        if (!$req) return ['success' => false, 'message' => 'Request not found'];
        if ($req['status'] !== 'approved') return ['success' => false, 'message' => 'Request not approved'];

        $this->db->beginTransaction();
        try {
            $tid = $this->tenantId();

            // Deduct from leave balance
            $this->execute("
                UPDATE employee_leave_balances
                SET used_days = used_days + ?, remaining_days = remaining_days - ?
                WHERE employee_id = ? AND leave_type_id = ?{$this->tenantSql()}
            ", [$req['days_to_encash'], $req['days_to_encash'], $req['employee_id'], $req['leave_type_id']]);

            // Update request
            $this->execute("
                UPDATE leave_encashment_requests
                SET status = 'paid', paid_at = NOW(), paid_by = ?, payment_ref = ?
                WHERE id = ?{$this->tenantSql()}
            ", array_merge([$payerId, $paymentRef, $requestId], $this->tVal()));

            // Credit to wallet
            $this->creditWallet($req['employee_id'], $req['net_amount'], 'Leave encashment', 'leave_encashment_' . $req['id']);

            $this->db->commit();
            return ['success' => true, 'message' => 'Leave encashment paid successfully'];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Reject encashment request
     */
    public function rejectRequest(int $requestId, int $rejecterId, string $reason): array
    {
        $req = $this->getRequest($requestId);
        if (!$req) return ['success' => false, 'message' => 'Request not found'];
        if ($req['status'] !== 'pending') return ['success' => false, 'message' => 'Request is not pending'];

        $this->execute("
            UPDATE leave_encashment_requests
            SET status = 'rejected', rejected_at = NOW(), rejected_by = ?, rejection_reason = ?
            WHERE id = ?{$this->tenantSql()}
        ", array_merge([$rejecterId, $reason, $requestId], $this->tVal()));

        return ['success' => true, 'message' => 'Request rejected'];
    }

    /**
     * Get all pending requests (admin)
     */
    public function getPendingRequests(): array
    {
        return $this->db->fetchAll("
            SELECT ler.*, e.name as employee_name, e.employee_code, e.department,
                   lt.name as leave_type_name, lt.code as leave_type_code
            FROM leave_encashment_requests ler
            JOIN employees e ON ler.employee_id = e.id
            JOIN leave_types lt ON ler.leave_type_id = lt.id
            WHERE ler.status = 'pending' AND ler.tenant_id = 1
            ORDER BY ler.requested_at ASC
        ");
    }

    /**
     * Get employee's encashment history
     */
    public function getEmployeeHistory(int $employeeId): array
    {
        return $this->db->fetchAll("
            SELECT ler.*, lt.name as leave_type_name, lt.code as leave_type_code
            FROM leave_encashment_requests ler
            JOIN leave_types lt ON ler.leave_type_id = lt.id
            WHERE ler.employee_id = ?
            ORDER BY ler.requested_at DESC
        ", [$employeeId]);
    }

    /**
     * Credit to employee wallet
     */
    private function creditWallet(int $employeeId, float $amount, string $description, string $reference): void
    {
        try {
            $emp = $this->fetchOne("SELECT user_id FROM employees WHERE id = ?", [$employeeId]);
            if (!$emp) return;

            $wallet = $this->fetchOne("SELECT * FROM user_wallets WHERE user_id = ?", [$emp['user_id']]);
            if (!$wallet) return;

            $newBalance = (float)$wallet['balance'] + $amount;
            $this->execute("UPDATE user_wallets SET balance = ?, total_earned = total_earned + ? WHERE id = ?", [$newBalance, $amount, $wallet['id']]);

            $this->execute("
                INSERT INTO wallet_transactions (wallet_id, type, amount, balance_after, description, reference, status, tenant_id, created_at)
                VALUES (?, 'credit', ?, ?, ?, ?, 'completed', 1, NOW())
            ", [$wallet['id'], $amount, $newBalance, $reference, $reference]);
        } catch (\Exception $e) {
            error_log("Wallet credit failed: " . $e->getMessage());
        }
    }
}