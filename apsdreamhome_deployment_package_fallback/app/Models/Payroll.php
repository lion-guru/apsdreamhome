<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Payroll Management Model
 * Handles salary structures, payroll runs, tax calculations, and payment processing
 */
class Payroll extends Model
{
    protected $table = 'payroll_entries';
    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'basic_salary',
        'house_rent_allowance',
        'conveyance_allowance',
        'medical_allowance',
        'lta_allowance',
        'special_allowance',
        'other_allowances',
        'overtime_hours',
        'overtime_rate',
        'overtime_amount',
        'provident_fund',
        'professional_tax',
        'income_tax',
        'loan_deductions',
        'other_deductions',
        'gross_earnings',
        'total_deductions',
        'net_salary',
        'payment_status',
        'payment_date',
        'payment_method',
        'bank_reference',
        'remarks',
        'created_at',
        'updated_at'
    ];

    /**
     * Create a new payroll run
     */
    public function createPayrollRun(array $data): array
    {
        $runData = [
            'run_name' => $data['run_name'],
            'pay_period_start' => $data['pay_period_start'],
            'pay_period_end' => $data['pay_period_end'],
            'pay_date' => $data['pay_date'],
            'status' => 'draft',
            'processed_by' => $data['processed_by'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $runId = $this->insertInto('payroll_runs', $runData);

        return [
            'success' => true,
            'run_id' => $runId,
            'message' => 'Payroll run created successfully'
        ];
    }

    /**
     * Process payroll for all employees in a run
     */
    public function processPayrollRun(int $runId): array
    {
        $db = Database::getInstance();

        // Get payroll run details
        $run = $db->query("SELECT * FROM payroll_runs WHERE id = ?", [$runId])->fetch();
        if (!$run) {
            return ['success' => false, 'message' => 'Payroll run not found'];
        }

        if ($run['status'] !== 'draft') {
            return ['success' => false, 'message' => 'Payroll run is not in draft status'];
        }

        // Get all active employees
        $employees = $db->query("SELECT id, name FROM employees WHERE status = 'active'")->fetchAll();

        $totalGross = 0;
        $totalNet = 0;
        $totalDeductions = 0;
        $processedCount = 0;

        foreach ($employees as $employee) {
            $salaryData = $this->calculateEmployeeSalary($employee['id'], $run['pay_period_start'], $run['pay_period_end']);

            if ($salaryData) {
                $entryData = [
                    'payroll_run_id' => $runId,
                    'employee_id' => $employee['id'],
                    'basic_salary' => $salaryData['basic_salary'],
                    'house_rent_allowance' => $salaryData['house_rent_allowance'],
                    'conveyance_allowance' => $salaryData['conveyance_allowance'],
                    'medical_allowance' => $salaryData['medical_allowance'],
                    'lta_allowance' => $salaryData['lta_allowance'],
                    'special_allowance' => $salaryData['special_allowance'],
                    'other_allowances' => $salaryData['other_allowances'],
                    'overtime_hours' => $salaryData['overtime_hours'],
                    'overtime_rate' => $salaryData['overtime_rate'],
                    'overtime_amount' => $salaryData['overtime_amount'],
                    'provident_fund' => $salaryData['provident_fund'],
                    'professional_tax' => $salaryData['professional_tax'],
                    'income_tax' => $salaryData['income_tax'],
                    'loan_deductions' => $salaryData['loan_deductions'],
                    'other_deductions' => $salaryData['other_deductions'],
                    'gross_earnings' => $salaryData['gross_earnings'],
                    'total_deductions' => $salaryData['total_deductions'],
                    'net_salary' => $salaryData['net_salary'],
                    'payment_status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $this->insert($entryData);

                $totalGross += $salaryData['gross_earnings'];
                $totalNet += $salaryData['net_salary'];
                $totalDeductions += $salaryData['total_deductions'];
                $processedCount++;
            }
        }

        // Update payroll run summary
        $db->query(
            "UPDATE payroll_runs SET
             total_employees = ?, total_gross = ?, total_net = ?, total_deductions = ?, status = 'processing'
             WHERE id = ?",
            [$processedCount, $totalGross, $totalNet, $totalDeductions, $runId]
        );

        return [
            'success' => true,
            'processed_employees' => $processedCount,
            'total_gross' => $totalGross,
            'total_net' => $totalNet,
            'message' => 'Payroll processed successfully'
        ];
    }

    /**
     * Calculate salary for an employee
     */
    public function calculateEmployeeSalary(int $employeeId, string $periodStart, string $periodEnd): ?array
    {
        $db = Database::getInstance();

        // Get active salary structure
        $salaryStructure = $db->query(
            "SELECT * FROM salary_structures
             WHERE employee_id = ? AND is_active = 1
             AND effective_from <= ?
             ORDER BY effective_from DESC LIMIT 1",
            [$employeeId, $periodEnd]
        )->fetch();

        if (!$salaryStructure) {
            return null;
        }

        // Calculate overtime (from attendance records)
        $overtimeData = $this->calculateOvertime($employeeId, $periodStart, $periodEnd);
        $overtimeAmount = $overtimeData['hours'] * $overtimeData['rate'];

        // Calculate tax
        $annualSalary = $salaryStructure['gross_salary'] * 12;
        $monthlyTax = $this->calculateIncomeTax($annualSalary) / 12;

        // Calculate loan deductions (if any)
        $loanDeductions = $this->getLoanDeductions($employeeId);

        $grossEarnings = $salaryStructure['gross_salary'] + $overtimeAmount;
        $totalDeductions = $salaryStructure['provident_fund'] +
                          $salaryStructure['professional_tax'] +
                          $monthlyTax +
                          $loanDeductions;
        $netSalary = $grossEarnings - $totalDeductions;

        return [
            'basic_salary' => $salaryStructure['basic_salary'],
            'house_rent_allowance' => $salaryStructure['house_rent_allowance'],
            'conveyance_allowance' => $salaryStructure['conveyance_allowance'],
            'medical_allowance' => $salaryStructure['medical_allowance'],
            'lta_allowance' => $salaryStructure['lta_allowance'],
            'special_allowance' => $salaryStructure['special_allowance'],
            'other_allowances' => $salaryStructure['other_allowances'] ?? 0,
            'overtime_hours' => $overtimeData['hours'],
            'overtime_rate' => $overtimeData['rate'],
            'overtime_amount' => $overtimeAmount,
            'provident_fund' => $salaryStructure['provident_fund'],
            'professional_tax' => $salaryStructure['professional_tax'],
            'income_tax' => $monthlyTax,
            'loan_deductions' => $loanDeductions,
            'other_deductions' => $salaryStructure['other_deductions'] ?? 0,
            'gross_earnings' => $grossEarnings,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary
        ];
    }

    /**
     * Calculate overtime hours and rate
     */
    private function calculateOvertime(int $employeeId, string $periodStart, string $periodEnd): array
    {
        // This would integrate with attendance system to calculate overtime
        // For now, return default values
        return [
            'hours' => 0,
            'rate' => 0
        ];
    }

    /**
     * Calculate income tax based on tax slabs
     */
    private function calculateIncomeTax(float $annualIncome): float
    {
        $db = Database::getInstance();
        $currentYear = date('Y');

        $taxSlabs = $db->query(
            "SELECT * FROM tax_slabs
             WHERE financial_year = ? AND is_active = 1
             ORDER BY min_income",
            [$currentYear]
        )->fetchAll();

        $tax = 0;
        $remainingIncome = $annualIncome;

        foreach ($taxSlabs as $slab) {
            if ($remainingIncome <= 0) break;

            $slabMin = $slab['min_income'];
            $slabMax = $slab['max_income'] ?? PHP_FLOAT_MAX;

            $taxableInSlab = min($remainingIncome, $slabMax - $slabMin);
            $slabTax = ($taxableInSlab * $slab['tax_rate']) / 100;

            // Add cess
            $cess = ($slabTax * $slab['cess_rate']) / 100;
            $slabTax += $cess;

            $tax += $slabTax;
            $remainingIncome -= $taxableInSlab;
        }

        return round($tax, 2);
    }

    /**
     * Get loan deductions for employee
     */
    private function getLoanDeductions(int $employeeId): float
    {
        // This would integrate with loan/advance system
        // For now, return 0
        return 0;
    }

    /**
     * Approve and process payments
     */
    public function approvePayrollRun(int $runId, int $approverId): array
    {
        $db = Database::getInstance();

        $run = $db->query("SELECT * FROM payroll_runs WHERE id = ?", [$runId])->fetch();
        if (!$run) {
            return ['success' => false, 'message' => 'Payroll run not found'];
        }

        if ($run['status'] !== 'processing') {
            return ['success' => false, 'message' => 'Payroll run is not ready for approval'];
        }

        // Update payroll run status
        $db->query(
            "UPDATE payroll_runs SET
             status = 'completed',
             approved_by = ?,
             approved_at = ?
             WHERE id = ?",
            [$approverId, date('Y-m-d H:i:s'), $runId]
        );

        // Mark all entries as paid
        $db->query(
            "UPDATE payroll_entries SET
             payment_status = 'paid',
             payment_date = ?,
             updated_at = ?
             WHERE payroll_run_id = ?",
            [date('Y-m-d'), date('Y-m-d H:i:s'), $runId]
        );

        return [
            'success' => true,
            'message' => 'Payroll approved and payments processed successfully'
        ];
    }

    /**
     * Get payroll runs with pagination
     */
    public function getPayrollRuns(int $limit = 20, int $offset = 0): array
    {
        $db = Database::getInstance();

        $sql = "SELECT pr.*, a.auser as processed_by_name, aa.auser as approved_by_name
                FROM payroll_runs pr
                LEFT JOIN admin a ON pr.processed_by = a.aid
                LEFT JOIN admin aa ON pr.approved_by = aa.aid
                ORDER BY pr.created_at DESC
                LIMIT ? OFFSET ?";

        return $db->query($sql, [$limit, $offset])->fetchAll();
    }

    /**
     * Get payroll entries for a specific run
     */
    public function getPayrollEntries(int $runId): array
    {
        $db = Database::getInstance();

        $sql = "SELECT pe.*, e.name as employee_name, e.employee_code
                FROM payroll_entries pe
                LEFT JOIN employees e ON pe.employee_id = e.id
                WHERE pe.payroll_run_id = ?
                ORDER BY e.name";

        return $db->query($sql, [$runId])->fetchAll();
    }

    /**
     * Update employee salary structure
     */
    public function updateSalaryStructure(int $employeeId, array $data): array
    {
        // Set existing structures as inactive
        $this->query(
            "UPDATE salary_structures SET is_active = 0, effective_to = ?
             WHERE employee_id = ? AND is_active = 1",
            [date('Y-m-d'), $employeeId]
        );

        // Calculate totals
        $grossSalary = $data['basic_salary'] +
                      $data['house_rent_allowance'] +
                      $data['conveyance_allowance'] +
                      $data['medical_allowance'] +
                      $data['lta_allowance'] +
                      $data['special_allowance'];

        $totalDeductions = $data['provident_fund'] +
                          $data['professional_tax'] +
                          $data['income_tax'];

        $netSalary = $grossSalary - $totalDeductions;

        $structureData = [
            'employee_id' => $employeeId,
            'basic_salary' => $data['basic_salary'],
            'house_rent_allowance' => $data['house_rent_allowance'],
            'conveyance_allowance' => $data['conveyance_allowance'],
            'medical_allowance' => $data['medical_allowance'],
            'lta_allowance' => $data['lta_allowance'],
            'special_allowance' => $data['special_allowance'],
            'other_allowances' => $data['other_allowances'] ?? null,
            'provident_fund' => $data['provident_fund'],
            'professional_tax' => $data['professional_tax'],
            'income_tax' => $data['income_tax'],
            'other_deductions' => $data['other_deductions'] ?? null,
            'gross_salary' => $grossSalary,
            'net_salary' => $netSalary,
            'effective_from' => $data['effective_from'],
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $structureId = $this->insertInto('salary_structures', $structureData);

        return [
            'success' => true,
            'structure_id' => $structureId,
            'message' => 'Salary structure updated successfully'
        ];
    }

    /**
     * Get employee salary structure
     */
    public function getSalaryStructure(int $employeeId): ?array
    {
        return $this->query(
            "SELECT * FROM salary_structures
             WHERE employee_id = ? AND is_active = 1
             ORDER BY effective_from DESC LIMIT 1",
            [$employeeId]
        )->fetch();
    }

    /**
     * Generate payslip for employee
     */
    public function generatePayslip(int $employeeId, int $payrollRunId): ?array
    {
        $db = Database::getInstance();

        $sql = "SELECT pe.*, pr.run_name, pr.pay_period_start, pr.pay_period_end,
                       pr.pay_date, e.name as employee_name, e.employee_code,
                       e.designation, e.department
                FROM payroll_entries pe
                LEFT JOIN payroll_runs pr ON pe.payroll_run_id = pr.id
                LEFT JOIN employees e ON pe.employee_id = e.id
                WHERE pe.employee_id = ? AND pe.payroll_run_id = ?";

        $payslip = $db->query($sql, [$employeeId, $payrollRunId])->fetch();

        return $payslip ?: null;
    }
}
