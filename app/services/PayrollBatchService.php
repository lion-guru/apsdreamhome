<?php
/**
 * PayrollBatchService
 * Monthly payroll batch generation using salary_structures + salary_payments.
 * Uses SalaryCalculationService for Indian payroll math (PF/ESI/TDS/PT).
 */
namespace App\Services;

class PayrollBatchService
{
    private ?\PDO $pdo = null;
    private SalaryCalculationService $calc;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getPdo();
        $this->calc = new SalaryCalculationService();
    }

    /**
     * Generate payslips for a given month/year for all active salary structures.
     */
    public function generateMonth(int $month, int $year, int $processedBy): array
    {
        $structures = $this->getActiveStructures();
        $generated = 0;
        $skipped = 0;
        $errors = [];
        $totalNet = 0;

        $existing = $this->fetchColumn(
            "SELECT COUNT(*) FROM salary_payments WHERE payment_month = ? AND payment_year = ? AND payment_status != 'cancelled'",
            [$month, $year]
        );
        if ($existing > 0) {
            throw new \RuntimeException("Payroll for $month/$year already has $existing payments. Void them first to regenerate.");
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO salary_payments
            (employee_id, salary_structure_id, payment_month, payment_year, payment_date,
             basic_amount, allowance_amount, gross_amount, deduction_amount, net_amount,
             payment_status, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
        ");

        foreach ($structures as $s) {
            try {
                $breakdown = $this->calc->calculate($s);
                $paymentDate = date("Y-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-28");
                $allowances = $breakdown['hra'] + $breakdown['conveyance'] + $breakdown['medical_allowance'] + $breakdown['special_allowance'] + $breakdown['other_allowances'];

                $stmt->execute([
                    $s['employee_id'], $s['id'], $month, $year, $paymentDate,
                    $breakdown['basic_salary'], $allowances, $breakdown['gross_salary'],
                    $breakdown['total_deductions'], $breakdown['net_salary'], $processedBy,
                ]);
                $generated++;
                $totalNet += $breakdown['net_salary'];
            } catch (\Exception $e) {
                $errors[] = "Employee #{$s['employee_id']}: " . $e->getMessage();
                $skipped++;
            }
        }

        return ['generated' => $generated, 'skipped' => $skipped, 'errors' => $errors, 'total_net' => $totalNet];
    }

    /**
     * Process payments: mark pending → paid.
     */
    public function processPayments(array $paymentIds, string $method, string $reference, int $processedBy): int
    {
        $updated = 0;
        $stmt = $this->pdo->prepare("
            UPDATE salary_payments
            SET payment_status = 'paid', payment_method = ?, bank_reference = ?,
                payment_processed_by = ?, payment_processed_at = NOW()
            WHERE id = ? AND payment_status = 'pending'
        ");
        foreach ($paymentIds as $pid) {
            $stmt->execute([$method, $reference, $processedBy, $pid]);
            $updated += $stmt->rowCount();
        }
        return $updated;
    }

    /**
     * Get monthly summary for dashboard.
     */
    public function getMonthlySummary(int $month, int $year): array
    {
        return $this->fetch("
            SELECT
                COUNT(*) as total_employees,
                COALESCE(SUM(gross_amount), 0) as total_gross,
                COALESCE(SUM(deduction_amount), 0) as total_deductions,
                COALESCE(SUM(net_amount), 0) as total_net,
                COALESCE(SUM(CASE WHEN payment_status='paid' THEN net_amount ELSE 0 END), 0) as total_paid,
                COALESCE(SUM(CASE WHEN payment_status='pending' THEN net_amount ELSE 0 END), 0) as total_pending,
                COUNT(CASE WHEN payment_status='paid' THEN 1 END) as paid_count,
                COUNT(CASE WHEN payment_status='pending' THEN 1 END) as pending_count
            FROM salary_payments
            WHERE payment_month = ? AND payment_year = ?
        ", [$month, $year]) ?? [];
    }

    /**
     * Get payslips for an employee.
     */
    public function getEmployeePayslips(int $employeeId, int $limit = 12): array
    {
        return $this->fetchAll("
            SELECT sp.*, ss.basic_salary, ss.hra, ss.pf_employee, ss.esi_employee, ss.tds,
                   u.name as employee_name
            FROM salary_payments sp
            LEFT JOIN salary_structures ss ON sp.salary_structure_id = ss.id
            LEFT JOIN users u ON sp.employee_id = u.id
            WHERE sp.employee_id = ?
            ORDER BY sp.payment_year DESC, sp.payment_month DESC
            LIMIT ?
        ", [$employeeId, $limit]);
    }

    /**
     * Preview: calculate what payroll WOULD be without saving.
     */
    public function previewMonth(int $month, int $year): array
    {
        $structures = $this->getActiveStructures();
        $entries = [];
        $totalGross = 0;
        $totalDeductions = 0;
        $totalNet = 0;

        foreach ($structures as $s) {
            $breakdown = $this->calc->calculate($s);
            $entries[] = array_merge($breakdown, [
                'employee_id'   => $s['employee_id'],
                'employee_name' => $s['employee_name'] ?? 'Employee #' . $s['employee_id'],
                'designation'   => $s['designation'] ?? '',
                'department'    => $s['department'] ?? '',
            ]);
            $totalGross += $breakdown['gross_salary'];
            $totalDeductions += $breakdown['total_deductions'];
            $totalNet += $breakdown['net_salary'];
        }

        return [
            'entries'        => $entries,
            'total_employees' => count($entries),
            'total_gross'    => $totalGross,
            'total_deductions' => $totalDeductions,
            'total_net'      => $totalNet,
        ];
    }

    // ─── Helpers ─────────────────────────────────
    private function getActiveStructures(): array
    {
        return $this->fetchAll(
            "SELECT ss.*, u.name as employee_name
             FROM salary_structures ss
             JOIN users u ON ss.employee_id = u.id
             WHERE ss.status = 'active'
             ORDER BY ss.employee_id"
        );
    }

    private function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    private function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    private function fetchColumn(string $sql, array $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
