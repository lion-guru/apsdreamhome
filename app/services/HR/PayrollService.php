<?php

// TODO: Add proper error handling with try-catch blocks


namespace App\Services\HR;

use App\Core\Database;
use App\Models\Employee;
use App\Models\EmployeeAttendance;

/**
 * Payroll Service
 * Handles salary processing, deductions, and payslips
 */
class PayrollService
{
    private $db;

    // Salary components
    const COMPONENT_BASIC = 'basic';
    const COMPONENT_HRA = 'hra';
    const COMPONENT_DA = 'da';
    const COMPONENT_CONVEYANCE = 'conveyance';
    const COMPONENT_MEDICAL = 'medical';
    const COMPONENT_SPECIAL = 'special';
    const COMPONENT_PF = 'pf';
    const COMPONENT_ESI = 'esi';
    const COMPONENT_TDS = 'tds';
    const COMPONENT_PROFESSIONAL_TAX = 'professional_tax';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Calculate monthly salary for employee
     */
    public function calculateSalary(int $employeeId, int $month, int $year): array
    {
        $employee = (new Employee())->find($employeeId);
        if (!$employee) {
            return ['success' => false, 'error' => 'Employee not found'];
        }

        // Get salary structure
        $structure = $this->getSalaryStructure($employeeId);
        if (!$structure) {
            return ['success' => false, 'error' => 'Salary structure not found'];
        }

        // Get attendance summary
        $attendanceModel = new EmployeeAttendance();
        $attendance = $attendanceModel->getMonthlySummary($employeeId, $month, $year);

        // Get approved leaves
        $leaves = $this->getApprovedLeaves($employeeId, $month, $year);

        // Calculate working days
        $totalWorkingDays = $this->getWorkingDays($month, $year);
        $daysWorked = $attendance['present'] + $attendance['late'] + $attendance['wfh'];
        $paidLeaves = $leaves['paid'] ?? 0;
        $unpaidLeaves = $leaves['unpaid'] ?? 0;

        // Calculate per day salary
        $grossSalary = $structure['gross_salary'];
        $perDaySalary = $grossSalary / $totalWorkingDays;

        // Deductions for unpaid leave
        $unpaidDeduction = $unpaidLeaves * $perDaySalary;

        // Calculate earnings
        $earnings = $this->calculateEarnings($structure, $daysWorked, $totalWorkingDays);

        // Calculate deductions
        $deductions = $this->calculateDeductions($structure, $earnings['total']);

        // Add unpaid leave deduction
        $deductions['unpaid_leave'] = $unpaidDeduction;

        // Calculate overtime
        $overtimePay = $this->calculateOvertime($employeeId, $month, $year, $structure);

        // Calculate net salary
        $totalEarnings = $earnings['total'] + $overtimePay;
        $totalDeductions = array_sum($deductions);
        $netSalary = $totalEarnings - $totalDeductions;

        return [
            'success' => true,
            'employee_id' => $employeeId,
            'employee_name' => $employee['name'],
            'employee_code' => $employee['employee_code'],
            'month' => $month,
            'year' => $year,
            'working_days' => $totalWorkingDays,
            'days_worked' => $daysWorked,
            'paid_leaves' => $paidLeaves,
            'unpaid_leaves' => $unpaidLeaves,
            'earnings' => $earnings,
            'deductions' => $deductions,
            'overtime_pay' => $overtimePay,
            'gross_salary' => $totalEarnings,
            'total_deductions' => $totalDeductions,
            'net_salary' => round($netSalary, 2)
        ];
    }

    /**
     * Process payroll for all employees
     */
    public function processPayroll(int $month, int $year, array $options = []): array
    {
        $employees = $this->getActiveEmployees($options['department_id'] ?? null);
        $processed = [];
        $errors = [];

        foreach ($employees as $emp) {
            $result = $this->calculateSalary($emp['id'], $month, $year);

            if ($result['success']) {
                // Save payroll record
                $payrollId = $this->savePayroll($result);
                $processed[] = [
                    'employee_id' => $emp['id'],
                    'payroll_id' => $payrollId,
                    'net_salary' => $result['net_salary']
                ];
            } else {
                $errors[] = [
                    'employee_id' => $emp['id'],
                    'error' => $result['error']
                ];
            }
        }

        return [
            'success' => true,
            'processed_count' => count($processed),
            'error_count' => count($errors),
            'processed' => $processed,
            'errors' => $errors
        ];
    }

    /**
     * Get salary structure for employee
     */
    public function getSalaryStructure(int $employeeId): ?array
    {
        $sql = "SELECT * FROM employee_salary_structures WHERE employee_id = ? AND status = 'active'";
        $structure = $this->db->query($sql, [$employeeId])->fetch(\PDO::FETCH_ASSOC);

        if (!$structure) {
            return null;
        }

        // Parse components
        $structure['components'] = json_decode($structure['components'] ?? '{}', true);
        $structure['gross_salary'] = $structure['ctc'] / 12;

        return $structure;
    }

    /**
     * Create or update salary structure
     */
    public function setSalaryStructure(int $employeeId, array $data): bool
    {
        $components = [
            self::COMPONENT_BASIC => $data['basic'] ?? 0,
            self::COMPONENT_HRA => $data['hra'] ?? 0,
            self::COMPONENT_DA => $data['da'] ?? 0,
            self::COMPONENT_CONVEYANCE => $data['conveyance'] ?? 0,
            self::COMPONENT_MEDICAL => $data['medical'] ?? 0,
            self::COMPONENT_SPECIAL => $data['special_allowance'] ?? 0
        ];

        $gross = array_sum($components);
        $ctc = $gross * 12;

        $sql = "INSERT INTO employee_salary_structures 
                (employee_id, ctc, gross_salary, components, effective_from, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'active', NOW())
                ON DUPLICATE KEY UPDATE 
                    ctc = VALUES(ctc),
                    gross_salary = VALUES(gross_salary),
                    components = VALUES(components),
                    effective_from = VALUES(effective_from),
                    updated_at = NOW()";

        return $this->db->query($sql, [
            $employeeId,
            $ctc,
            $gross,
            json_encode($components),
            $data['effective_from'] ?? date('Y-m-d')
        ])->rowCount() > 0;
    }

    /**
     * Calculate earnings breakdown
     */
    private function calculateEarnings(array $structure, int $daysWorked, int $totalDays): array
    {
        $components = $structure['components'] ?? [];
        $ratio = $daysWorked / $totalDays;

        $earnings = [];
        $total = 0;

        foreach ($components as $name => $amount) {
            $proRated = round($amount * $ratio, 2);
            $earnings[$name] = $proRated;
            $total += $proRated;
        }

        $earnings['total'] = $total;
        return $earnings;
    }

    /**
     * Calculate deductions
     */
    private function calculateDeductions(array $structure, float $grossEarnings): array
    {
        $deductions = [];
        $components = $structure['components'] ?? [];
        $basic = $components[self::COMPONENT_BASIC] ?? 0;

        // PF (12% of basic, employer + employee)
        $deductions[self::COMPONENT_PF] = round($basic * 0.12, 2);

        // ESI (0.75% if gross <= 21000)
        if ($grossEarnings <= 21000) {
            $deductions[self::COMPONENT_ESI] = round($grossEarnings * 0.0075, 2);
        }

        // Professional Tax (state-specific, default 200)
        $deductions[self::COMPONENT_PROFESSIONAL_TAX] = 200;

        // TDS (to be calculated based on tax regime)
        $deductions[self::COMPONENT_TDS] = $this->calculateTDS($structure['ctc'] ?? 0);

        return $deductions;
    }

    /**
     * Calculate TDS
     */
    private function calculateTDS(float $annualCTC): float
    {
        // Simplified TDS calculation (new regime)
        $taxableIncome = $annualCTC - 50000; // Standard deduction

        $tax = 0;
        if ($taxableIncome > 1500000) {
            $tax = ($taxableIncome - 1500000) * 0.30 + 195000;
        } elseif ($taxableIncome > 1200000) {
            $tax = ($taxableIncome - 1200000) * 0.20 + 135000;
        } elseif ($taxableIncome > 900000) {
            $tax = ($taxableIncome - 900000) * 0.15 + 90000;
        } elseif ($taxableIncome > 600000) {
            $tax = ($taxableIncome - 600000) * 0.10 + 60000;
        } elseif ($taxableIncome > 300000) {
            $tax = ($taxableIncome - 300000) * 0.05;
        }

        return round($tax / 12, 2); // Monthly TDS
    }

    /**
     * Calculate overtime pay
     */
    private function calculateOvertime(int $employeeId, int $month, int $year, array $structure): float
    {
        $sql = "SELECT SUM(overtime_hours) as total_overtime 
                FROM employee_attendance 
                WHERE employee_id = ? AND MONTH(check_in_time) = ? AND YEAR(check_in_time) = ?";
        
        $result = $this->db->query($sql, [$employeeId, $month, $year])->fetch(\PDO::FETCH_ASSOC);
        $overtimeHours = $result['total_overtime'] ?? 0;

        if ($overtimeHours <= 0) {
            return 0;
        }

        // Overtime rate = 2x hourly rate
        $monthlyGross = $structure['gross_salary'];
        $hourlyRate = $monthlyGross / (8 * 26); // 8 hours * 26 working days

        return round($overtimeHours * $hourlyRate * 2, 2);
    }

    /**
     * Get approved leaves for month
     */
    private function getApprovedLeaves(int $employeeId, int $month, int $year): array
    {
        $sql = "SELECT leave_type, SUM(total_days) as days 
                FROM employee_leaves 
                WHERE employee_id = ? 
                AND status = 'approved'
                AND MONTH(start_date) = ? AND YEAR(start_date) = ?
                GROUP BY leave_type";

        $leaves = $this->db->query($sql, [$employeeId, $month, $year])
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        return [
            'paid' => ($leaves['sick'] ?? 0) + ($leaves['casual'] ?? 0) + ($leaves['earned'] ?? 0),
            'unpaid' => $leaves['unpaid'] ?? 0
        ];
    }

    /**
     * Get working days in month (excluding Sundays and holidays)
     */
    private function getWorkingDays(int $month, int $year): int
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $workingDays = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
            $dayOfWeek = date('N', strtotime($date));

            if ($dayOfWeek < 7) { // Not Sunday
                $workingDays++;
            }
        }

        // Subtract holidays
        $sql = "SELECT COUNT(*) FROM holidays WHERE MONTH(date) = ? AND YEAR(date) = ?";
        $holidays = $this->db->query($sql, [$month, $year])->fetchColumn();

        return $workingDays - $holidays;
    }

    /**
     * Get active employees
     */
    private function getActiveEmployees(?int $departmentId): array
    {
        $sql = "SELECT id, name, employee_code FROM employees WHERE status = 'active'";
        $params = [];

        if ($departmentId) {
            $sql .= " AND department_id = ?";
            $params[] = $departmentId;
        }

        return $this->db->query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Save payroll record
     */
    private function savePayroll(array $salaryData): int
    {
        $sql = "INSERT INTO employee_payrolls 
                (employee_id, month, year, working_days, days_worked, paid_leaves, unpaid_leaves,
                 earnings, deductions, overtime_pay, gross_salary, total_deductions, net_salary,
                 status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'processed', NOW())
                ON DUPLICATE KEY UPDATE 
                    working_days = VALUES(working_days),
                    days_worked = VALUES(days_worked),
                    earnings = VALUES(earnings),
                    deductions = VALUES(deductions),
                    net_salary = VALUES(net_salary),
                    updated_at = NOW()";

        $this->db->query($sql, [
            $salaryData['employee_id'],
            $salaryData['month'],
            $salaryData['year'],
            $salaryData['working_days'],
            $salaryData['days_worked'],
            $salaryData['paid_leaves'],
            $salaryData['unpaid_leaves'],
            json_encode($salaryData['earnings']),
            json_encode($salaryData['deductions']),
            $salaryData['overtime_pay'],
            $salaryData['gross_salary'],
            $salaryData['total_deductions'],
            $salaryData['net_salary']
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Generate payslip PDF
     */
    public function generatePayslip(int $payrollId): string
    {
        $sql = "SELECT p.*, e.name, e.employee_code, e.department_id, e.designation,
                       d.name as department_name
                FROM employee_payrolls p
                JOIN employees e ON p.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE p.id = ?";

        $payroll = $this->db->query($sql, [$payrollId])->fetch(\PDO::FETCH_ASSOC);

        if (!$payroll) {
            throw new \Exception('Payroll not found');
        }

        // Generate PDF using library (TCPDF/DOMPDF)
        // Return PDF path or content
        return $this->createPayslipPDF($payroll);
    }

    /**
     * Create payslip PDF
     */
    private function createPayslipPDF(array $data): string
    {
        // PDF generation logic
        // Return file path
        $filename = "payslip_{$data['employee_code']}_{$data['month']}_{$data['year']}.pdf";
        return storage_path('payslips/' . $filename);
    }
}
