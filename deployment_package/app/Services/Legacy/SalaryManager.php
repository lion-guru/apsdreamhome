<?php

namespace App\Services\Legacy;

/**
 * Salary Management System
 * Handles employee payroll, attendance, and financial tracking
 */

class SalaryManager
{
    private $db;
    private $logger;

    public function __construct($db = null, $logger = null)
    {
        $this->db = $db ?: \App\Core\App::database();
        $this->logger = $logger;
        $this->insertSampleSalaryData();
    }

    private function insertSampleSalaryData()
    {
        $checkSql = "SELECT COUNT(*) as count FROM employee_salary_structure";
        $row = $this->db->fetch($checkSql);

        if ($row && $row['count'] == 0) {
            // Get admin user for sample data
            $adminSql = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
            $admin = $this->db->fetch($adminSql);

            if ($admin) {
                $sampleStructure = [
                    'employee_id' => $admin['id'],
                    'basic_salary' => 50000,
                    'hra' => 15000,
                    'da' => 5000,
                    'ta' => 3000,
                    'medical_allowance' => 2000,
                    'special_allowance' => 5000,
                    'pf_deduction' => 6000,
                    'esi_deduction' => 750,
                    'professional_tax' => 200,
                    'tds_deduction' => 5000,
                    'gross_salary' => 78000,
                    'net_salary' => 65050,
                    'effective_from' => date('Y-m-d'),
                    'created_by' => $admin['id']
                ];

                $sql = "INSERT INTO employee_salary_structure (
                    employee_id, basic_salary, hra, da, ta, medical_allowance, special_allowance,
                    pf_deduction, esi_deduction, professional_tax, tds_deduction, gross_salary, net_salary,
                    effective_from, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $this->db->execute($sql, array_values($sampleStructure));
            }
        }
    }

    /**
     * Create salary structure for employee
     */
    public function createSalaryStructure($data)
    {
        $sql = "INSERT INTO employee_salary_structure (
            employee_id, basic_salary, hra, da, ta, medical_allowance, special_allowance, other_allowance,
            pf_deduction, esi_deduction, professional_tax, tds_deduction, other_deduction,
            gross_salary, net_salary, effective_from, approved_by, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['employee_id'],
            $data['basic_salary'],
            $data['hra'],
            $data['da'],
            $data['ta'],
            $data['medical_allowance'],
            $data['special_allowance'],
            $data['other_allowance'],
            $data['pf_deduction'],
            $data['esi_deduction'],
            $data['professional_tax'],
            $data['tds_deduction'],
            $data['other_deduction'],
            $data['gross_salary'],
            $data['net_salary'],
            $data['effective_from'],
            $data['approved_by'],
            $data['created_by']
        ];

        try {
            $this->db->execute($sql, $params);
            $structureId = $this->db->lastInsertId();

            if ($structureId) {
                // Deactivate previous salary structures for this employee
                $this->deactivatePreviousSalaryStructures($data['employee_id'], $data['effective_from']);

                if ($this->logger) {
                    $this->logger->log("Salary structure created for employee ID: {$data['employee_id']}", 'info', 'salary');
                }
                return $structureId;
            }
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error creating salary structure: " . $e->getMessage(), 'error', 'salary');
            }
        }

        return false;
    }

    /**
     * Deactivate previous salary structures
     */
    private function deactivatePreviousSalaryStructures($employeeId, $effectiveFrom)
    {
        $sql = "UPDATE employee_salary_structure SET is_active = FALSE
                WHERE employee_id = ? AND effective_from < ? AND is_active = TRUE";
        try {
            $this->db->execute($sql, [$employeeId, $effectiveFrom]);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error deactivating previous salary structures: " . $e->getMessage(), 'error', 'salary');
            }
        }
    }

    /**
     * Process monthly salary payment
     */
    public function processMonthlySalary($employeeId, $month, $year)
    {
        // Get current salary structure
        $salaryStructure = $this->getCurrentSalaryStructure($employeeId);
        if (!$salaryStructure) {
            return ['success' => false, 'message' => 'No salary structure found for employee'];
        }

        // Check if salary already processed for this month
        if ($this->isSalaryProcessed($employeeId, $month, $year)) {
            return ['success' => false, 'message' => 'Salary already processed for this month'];
        }

        // Calculate attendance and deductions
        $attendanceData = $this->calculateAttendanceData($employeeId, $month, $year);
        $adjustments = $this->calculateAdjustments($employeeId, $month, $year);

        $basicAmount = $salaryStructure['basic_salary'] * $attendanceData['present_days'] / $attendanceData['total_days'];
        $allowanceAmount = ($salaryStructure['hra'] + $salaryStructure['da'] + $salaryStructure['ta'] +
            $salaryStructure['medical_allowance'] + $salaryStructure['special_allowance']) *
            $attendanceData['present_days'] / $attendanceData['total_days'];

        $grossAmount = $basicAmount + $allowanceAmount;
        $deductionAmount = $salaryStructure['pf_deduction'] + $salaryStructure['esi_deduction'] +
            $salaryStructure['professional_tax'] + $salaryStructure['tds_deduction'] +
            $adjustments['total_deductions'];

        $netAmount = $grossAmount - $deductionAmount + $adjustments['total_additions'];

        // Create salary payment record
        $sql = "INSERT INTO salary_payments (
            employee_id, salary_structure_id, payment_month, payment_year, payment_date,
            basic_amount, allowance_amount, gross_amount, deduction_amount, net_amount,
            payment_status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)";

        $paymentDate = date('Y-m-d');
        if (session_status() === PHP_SESSION_NONE) {
            require_once __DIR__ . '/session_helpers.php';
            ensureSessionStarted();
        }
        $createdBy = $_SESSION['user_id'] ?? 1;

        $params = [
            $employeeId,
            $salaryStructure['id'],
            $month,
            $year,
            $paymentDate,
            $basicAmount,
            $allowanceAmount,
            $grossAmount,
            $deductionAmount,
            $netAmount,
            $createdBy
        ];

        try {
            $this->db->execute($sql, $params);
            $paymentId = $this->db->lastInsertId();

            if ($paymentId && $this->logger) {
                $this->logger->log("Salary processed for employee ID: $employeeId, Month: $month/$year, Amount: $netAmount", 'info', 'salary');
            }

            return [
                'success' => true,
                'payment_id' => $paymentId,
                'basic_amount' => $basicAmount,
                'allowance_amount' => $allowanceAmount,
                'gross_amount' => $grossAmount,
                'deduction_amount' => $deductionAmount,
                'net_amount' => $netAmount,
                'attendance' => $attendanceData,
                'adjustments' => $adjustments
            ];
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error processing salary: " . $e->getMessage(), 'error', 'salary');
            }
            return ['success' => false, 'message' => 'Failed to process salary: ' . $e->getMessage()];
        }
    }

    /**
     * Get current salary structure for employee
     */
    private function getCurrentSalaryStructure($employeeId)
    {
        $sql = "SELECT * FROM employee_salary_structure
                WHERE employee_id = ? AND is_active = TRUE
                ORDER BY effective_from DESC LIMIT 1";
        return $this->db->fetch($sql, [$employeeId]);
    }

    private function isSalaryProcessed($employeeId, $month, $year)
    {
        $sql = "SELECT id FROM salary_payments
                WHERE employee_id = ? AND payment_month = ? AND payment_year = ?";
        $row = $this->db->fetch($sql, [$employeeId, $month, $year]);
        return !empty($row);
    }

    private function calculateAttendanceData($employeeId, $month, $year)
    {
        $startDate = date('Y-m-d', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t', strtotime("$year-$month-01"));
        $totalDays = date('t', strtotime("$year-$month-01"));

        $sql = "SELECT
            COUNT(*) as present_days,
            SUM(CASE WHEN attendance_status = 'absent' THEN 1 ELSE 0 END) as absent_days,
            SUM(CASE WHEN attendance_status = 'half_day' THEN 0.5 ELSE 0 END) as half_days,
            SUM(total_hours) as total_hours
            FROM employee_attendance
            WHERE employee_id = ? AND attendance_date >= ? AND attendance_date <= ?";

        $attendance = $this->db->fetch($sql, [$employeeId, $startDate, $endDate]);

        $attendance['total_days'] = $totalDays;
        $attendance['working_days'] = $totalDays - ($attendance['absent_days'] ?? 0);

        return $attendance;
    }

    private function calculateAdjustments($employeeId, $month, $year)
    {
        $adjustments = [
            'total_additions' => 0,
            'total_deductions' => 0,
            'bonuses' => [],
            'advances' => [],
            'other_deductions' => []
        ];

        // Calculate bonuses for the month
        $bonusSql = "SELECT SUM(bonus_amount) as total_bonuses
                     FROM employee_bonuses
                     WHERE employee_id = ? AND payment_status = 'pending'
                     AND ((bonus_month = ? AND bonus_year = ?) OR (bonus_month IS NULL AND bonus_year IS NULL))";
        $bonuses = $this->db->fetch($bonusSql, [$employeeId, $month, $year]);

        $adjustments['total_additions'] += $bonuses['total_bonuses'] ?? 0;

        // Calculate advance deductions for the month
        $advanceSql = "SELECT SUM(installment_amount) as total_advances
                      FROM employee_advances
                      WHERE employee_id = ? AND status = 'disbursed' AND outstanding_amount > 0";
        $advances = $this->db->fetch($advanceSql, [$employeeId]);

        $adjustments['total_deductions'] += $advances['total_advances'] ?? 0;

        return $adjustments;
    }

    /**
     * Mark salary as paid
     */
    public function markSalaryAsPaid($paymentId, $transactionId = null, $bankReference = null)
    {
        $sql = "UPDATE salary_payments SET
            payment_status = 'paid',
            transaction_id = ?,
            bank_reference = ?,
            payment_processed_by = ?,
            payment_processed_at = NOW()
            WHERE id = ?";

        if (session_status() === PHP_SESSION_NONE) {
            require_once __DIR__ . '/session_helpers.php';
            ensureSessionStarted();
        }
        $processedBy = $_SESSION['user_id'] ?? 1;

        try {
            $this->db->execute($sql, [$transactionId, $bankReference, $processedBy, $paymentId]);
            if ($this->logger) {
                $this->logger->log("Salary payment marked as paid: ID $paymentId", 'info', 'salary');
            }
            return true;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error marking salary as paid: " . $e->getMessage(), 'error', 'salary');
            }
            return false;
        }
    }

    /**
     * Record employee attendance
     */
    public function recordAttendance($data)
    {
        $sql = "INSERT INTO employee_attendance (
            employee_id, attendance_date, check_in_time, check_out_time, total_hours,
            attendance_status, leave_type, remarks, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            check_in_time = VALUES(check_in_time),
            check_out_time = VALUES(check_out_time),
            total_hours = VALUES(total_hours),
            attendance_status = VALUES(attendance_status),
            leave_type = VALUES(leave_type),
            remarks = VALUES(remarks),
            updated_at = NOW()";

        $params = [
            $data['employee_id'],
            $data['attendance_date'],
            $data['check_in_time'],
            $data['check_out_time'],
            $data['total_hours'],
            $data['attendance_status'],
            $data['leave_type'],
            $data['remarks'],
            $data['created_by']
        ];

        try {
            $this->db->execute($sql, $params);
            if ($this->logger) {
                $this->logger->log("Attendance recorded for employee ID: {$data['employee_id']}, Date: {$data['attendance_date']}", 'info', 'attendance');
            }
            return true;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error recording attendance: " . $e->getMessage(), 'error', 'attendance');
            }
            return false;
        }
    }

    /**
     * Create employee advance
     */
    public function createAdvance($data)
    {
        $sql = "INSERT INTO employee_advances (
            employee_id, advance_number, advance_amount, advance_date, reason,
            repayment_method, installment_amount, total_installments, status, approved_by, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['employee_id'],
            $data['advance_number'],
            $data['advance_amount'],
            $data['advance_date'],
            $data['reason'],
            $data['repayment_method'],
            $data['installment_amount'],
            $data['total_installments'],
            $data['status'],
            $data['approved_by'],
            $data['created_by']
        ];

        try {
            $this->db->execute($sql, $params);
            $advanceId = $this->db->lastInsertId();

            if ($advanceId) {
                $outstandingAmount = $data['advance_amount'];
                $this->updateAdvanceOutstandingAmount($advanceId, $outstandingAmount);

                if ($this->logger) {
                    $this->logger->log("Advance created: {$data['advance_number']}, Amount: {$data['advance_amount']}", 'info', 'advance');
                }
                return $advanceId;
            }
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error creating advance: " . $e->getMessage(), 'error', 'advance');
            }
        }

        return false;
    }

    /**
     * Update advance outstanding amount
     */
    private function updateAdvanceOutstandingAmount($advanceId, $amount)
    {
        $sql = "UPDATE employee_advances SET outstanding_amount = ? WHERE id = ?";
        try {
            $this->db->execute($sql, [$amount, $advanceId]);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error updating advance outstanding amount: " . $e->getMessage(), 'error', 'advance');
            }
        }
    }

    /**
     * Create employee bonus
     */
    public function createBonus($data)
    {
        $sql = "INSERT INTO employee_bonuses (
            employee_id, bonus_number, bonus_type, bonus_amount, bonus_month, bonus_year,
            reason, approved_by, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['employee_id'],
            $data['bonus_number'],
            $data['bonus_type'],
            $data['bonus_amount'],
            $data['bonus_month'],
            $data['bonus_year'],
            $data['reason'],
            $data['approved_by'],
            $data['created_by']
        ];

        try {
            $this->db->execute($sql, $params);
            $bonusId = $this->db->lastInsertId();

            if ($bonusId && $this->logger) {
                $this->logger->log("Bonus created: {$data['bonus_number']}, Amount: {$data['bonus_amount']}", 'info', 'bonus');
            }
            return $bonusId;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error creating bonus: " . $e->getMessage(), 'error', 'bonus');
            }
            return false;
        }
    }

    /**
     * Get salary report for employee
     */
    public function getSalaryReport($employeeId, $startMonth, $startYear, $endMonth, $endYear)
    {
        $report = [];

        // Salary payments
        $sql = "SELECT * FROM salary_payments
                WHERE employee_id = ? AND
                ((payment_year = ? AND payment_month >= ?) OR
                 (payment_year > ? AND payment_year < ?) OR
                 (payment_year = ? AND payment_month <= ?))
                ORDER BY payment_year, payment_month";
        $report['salary_payments'] = $this->db->fetchAll($sql, [$employeeId, $startYear, $startMonth, $startYear, $endYear, $endYear, $endMonth]);

        // Bonuses
        $sql = "SELECT * FROM employee_bonuses
                WHERE employee_id = ? AND payment_status = 'paid'
                AND ((bonus_year = ? AND (bonus_month IS NULL OR bonus_month >= ?)) OR
                     (bonus_year > ? AND bonus_year < ?) OR
                     (bonus_year = ? AND (bonus_month IS NULL OR bonus_month <= ?)))
                ORDER BY bonus_year, bonus_month";
        $report['bonuses'] = $this->db->fetchAll($sql, [$employeeId, $startYear, $startMonth, $startYear, $endYear, $endYear, $endMonth]);

        return $report;
    }

    /**
     * Calculate salary summary
     */
    public function calculateSalarySummary($month, $year)
    {
        $sql = "SELECT
            COUNT(DISTINCT employee_id) as total_employees,
            SUM(basic_amount) as total_basic,
            SUM(allowance_amount) as total_allowances,
            SUM(gross_amount) as total_gross,
            SUM(deduction_amount) as total_deductions,
            SUM(net_amount) as total_net
            FROM salary_payments
            WHERE payment_month = ? AND payment_year = ? AND payment_status != 'cancelled'";

        return $this->db->fetch($sql, [$month, $year]);
    }

    /**
     * Get payroll dashboard data
     */
    public function getPayrollDashboard()
    {
        $dashboard = [];

        // Monthly payroll summary
        $currentMonth = date('m');
        $currentYear = date('Y');

        $sql = "SELECT
            COUNT(DISTINCT employee_id) as employees_paid,
            SUM(net_amount) as total_salary_paid,
            AVG(net_amount) as average_salary
            FROM salary_payments
            WHERE payment_month = ? AND payment_year = ? AND payment_status = 'paid'";
        $dashboard['monthly_payroll'] = $this->db->fetch($sql, [$currentMonth, $currentYear]);

        // Pending salaries
        $sql = "SELECT COUNT(*) as pending_salaries FROM salary_payments
                WHERE payment_status = 'pending'";
        $dashboard['pending_salaries'] = $this->db->fetch($sql);

        // Department wise salary
        $sql = "SELECT u.role,
                COUNT(DISTINCT sp.employee_id) as employees,
                SUM(sp.net_amount) as total_salary
                FROM salary_payments sp
                JOIN users u ON sp.employee_id = u.id
                WHERE sp.payment_month = ? AND sp.payment_year = ? AND sp.payment_status = 'paid'
                GROUP BY u.role";
        $dashboard['department_salary'] = $this->db->fetchAll($sql, [$currentMonth, $currentYear]);

        // Upcoming salary payments
        $sql = "SELECT sp.*, u.name as full_name, u.role
                FROM salary_payments sp
                JOIN users u ON sp.employee_id = u.id
                WHERE sp.payment_status = 'pending'
                ORDER BY sp.payment_date LIMIT 10";
        $dashboard['upcoming_payments'] = $this->db->fetchAll($sql);

        return $dashboard;
    }
}
