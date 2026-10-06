<?php
/**
 * AdvanceRecoveryService
 * Handles salary advance/loan deduction from monthly payroll
 */

namespace App\Services;

use App\Traits\ServiceTenantTrait;

class AdvanceRecoveryService
{
    use ServiceTenantTrait;

    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getPdo();
    }

    /**
     * Create a new advance/loan for an employee
     */
    public function createAdvance(array $data): array
    {
        $this->pdo->beginTransaction();
        try {
            $employeeId = (int)($data['employee_id'] ?? 0);
            $amount = (float)($data['amount'] ?? 0);
            $reason = $data['reason'] ?? '';
            $repayMonths = (int)($data['repay_months'] ?? 0);
            $interestRate = (float)($data['interest_rate'] ?? 0);
            $startMonth = (int)($data['start_month'] ?? date('n'));
            $startYear = (int)($data['start_year'] ?? date('Y'));

            if ($employeeId <= 0 || $amount <= 0) {
                throw new \Exception('Invalid employee or amount');
            }

            // Check existing active advances
            $active = $this->fetchOne("
                SELECT COUNT(*) as cnt FROM employee_advances 
                WHERE employee_id=? AND status IN ('active','pending') AND tenant_id=?
            ", [$employeeId, $this->tenantId()]);
            
            if (($active['cnt'] ?? 0) > 0) {
                throw new \Exception('Employee already has an active advance');
            }

            $emi = 0;
            if ($repayMonths > 0) {
                // Calculate EMI with interest
                $monthlyRate = $interestRate / 12 / 100;
                if ($monthlyRate > 0) {
                    $emi = round($amount * $monthlyRate * pow(1 + $monthlyRate, $repayMonths) / (pow(1 + $monthlyRate, $repayMonths) - 1), 2);
                } else {
                    $emi = round($amount / $repayMonths, 2);
                }
            }

            $advanceNo = 'ADV-' . date('Ymd') . '-' . str_pad($employeeId, 4, '0', STR_PAD_LEFT);
            
            $insertData = $this->tenantInsertData();
            $columns = "employee_id, advance_no, amount, reason, repay_months, emi_amount, interest_rate, start_month, start_year, status, approved_by";
            $values = str_repeat('?,', 10) . '?';
            $params = [$employeeId, $advanceNo, $amount, $reason, $repayMonths, $emi, $interestRate, $startMonth, $startYear, 'pending', (int)($_SESSION['admin_id'] ?? 0)];
            
            if (!empty($insertData)) {
                $columns .= ", " . implode(', ', array_keys($insertData));
                $values .= ", ?";
                $params = array_merge($params, array_values($insertData));
            }
            
            $advanceId = $this->execute("INSERT INTO employee_advances ($columns) VALUES ($values)", $params);

            $this->pdo->commit();
            return ['success' => true, 'advance_id' => $advanceId, 'advance_no' => $advanceNo, 'emi' => $emi];

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Approve an advance (starts recovery from next payroll)
     */
    public function approveAdvance(int $advanceId, int $approvedBy): bool
    {
        $advance = $this->fetchOne("SELECT * FROM employee_advances WHERE id=?{$this->tenantSql()}", array_merge([$advanceId], $this->tVal()));
        if (!$advance) throw new \Exception('Advance not found');
        if ($advance['status'] !== 'pending') return false;

        $sql = "UPDATE employee_advances SET status='active', approved_by=?, approved_at=NOW() WHERE id=? AND status='pending'{$this->tenantSql()}";
        $params = [$approvedBy, $advanceId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('AdvanceRecoveryService::approveAdvance: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate recovery amount for a payroll period.
     * $writeLog=true persists the recovery log (use only on the path that
     * actually creates the payment — previews must pass false).
     */
    public function calculateRecovery(int $employeeId, int $month, int $year, bool $writeLog = true): float
    {
        $advances = $this->fetchAll("
            SELECT * FROM employee_advances 
            WHERE employee_id=? AND status='active' 
            AND (start_year < ? OR (start_year = ? AND start_month <= ?))
            AND tenant_id=?
        ", [$employeeId, $year, $year, $month, $this->tenantId()]);

        $totalRecovery = 0;
        // MySQL forbids reading advance_recovery_log inside its own INSERT
        // (1093), so compute the prior sum first in PHP.
        $sumStmt = $this->pdo->prepare("SELECT COALESCE(SUM(emi_recovered),0) FROM advance_recovery_log WHERE advance_id=?");
        $logStmt = $writeLog ? $this->pdo->prepare("
            INSERT IGNORE INTO advance_recovery_log (advance_id, recovery_month, recovery_year, emi_recovered, remaining_balance, tenant_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ") : null;
        foreach ($advances as $adv) {
            try {
                if (!$writeLog) {
                    $totalRecovery += (float)$adv['emi_amount'];
                    continue;
                }
                // Idempotent: uk_advance_month skips already-logged months.
                $sumStmt->execute([$adv['id']]);
                $prior = (float)$sumStmt->fetchColumn();
                $logStmt->execute([$adv['id'], $month, $year, $adv['emi_amount'], $prior + (float)$adv['emi_amount'], $this->tenantId()]);
                // Count the EMI whenever a log row exists for this month
                // (this run logged it, or an earlier run did).
                $totalRecovery += (float)$adv['emi_amount'];
            } catch (\Throwable $e) {
                error_log('AdvanceRecoveryService::calculateRecovery: ' . $e->getMessage());
            }
        }

        return $totalRecovery;
    }

    /**
     * Get advance summary for employee
     */
    public function getAdvanceSummary(int $employeeId): array
    {
        $advances = $this->fetchAll("
            SELECT a.*, 
                   COALESCE(SUM(r.emi_recovered),0) as total_recovered,
                   (a.amount - COALESCE(SUM(r.emi_recovered),0)) as outstanding
            FROM employee_advances a
            LEFT JOIN advance_recovery_log r ON a.id = r.advance_id
            WHERE a.employee_id=? AND a.tenant_id=?
            GROUP BY a.id
            ORDER BY a.created_at DESC
        ", array_merge([$employeeId], $this->tVal()));

        return $advances;
    }

    private function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('AdvanceRecoveryService::fetchOne: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('AdvanceRecoveryService::fetchAll: ' . $e->getMessage());
            return [];
        }
    }

    private function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int)$this->pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('AdvanceRecoveryService::execute: ' . $e->getMessage());
            return 0;
        }
    }
}