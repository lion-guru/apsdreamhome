<?php
/**
 * Employee Salary Management System
 * Complete salary and payroll management for colonizer company employees
 */

class SalaryManager {
    private $conn;
    private $logger;

    public function __construct($conn, $logger = null) {
        $this->conn = $conn;
        $this->logger = $logger;
        $this->createSalaryTables();
    }

    /**
     * Create salary management tables
     */
    private function createSalaryTables() {
        // Employee salary structure table
        $sql = "CREATE TABLE IF NOT EXISTS employee_salary_structure (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            basic_salary DECIMAL(15,2) NOT NULL,
            hra DECIMAL(15,2) DEFAULT 0,
            da DECIMAL(15,2) DEFAULT 0,
            ta DECIMAL(15,2) DEFAULT 0,
            medical_allowance DECIMAL(15,2) DEFAULT 0,
            special_allowance DECIMAL(15,2) DEFAULT 0,
            other_allowance DECIMAL(15,2) DEFAULT 0,
            pf_deduction DECIMAL(15,2) DEFAULT 0,
            esi_deduction DECIMAL(15,2) DEFAULT 0,
            professional_tax DECIMAL(15,2) DEFAULT 0,
            tds_deduction DECIMAL(15,2) DEFAULT 0,
            other_deduction DECIMAL(15,2) DEFAULT 0,
            gross_salary DECIMAL(15,2) NOT NULL,
            net_salary DECIMAL(15,2) NOT NULL,
            effective_from DATE NOT NULL,
            effective_to DATE,
            is_active BOOLEAN DEFAULT TRUE,
            approved_by INT,
            approved_at TIMESTAMP NULL,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

        // Salary payments table
        $sql = "CREATE TABLE IF NOT EXISTS salary_payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            salary_structure_id INT,
            payment_month INT NOT NULL,
            payment_year INT NOT NULL,
            payment_date DATE NOT NULL,
            basic_amount DECIMAL(15,2),
            allowance_amount DECIMAL(15,2),
            gross_amount DECIMAL(15,2),
            deduction_amount DECIMAL(15,2),
            net_amount DECIMAL(15,2),
            payment_method ENUM('bank_transfer','cash','cheque') DEFAULT 'bank_transfer',
            transaction_id VARCHAR(100),
            bank_reference VARCHAR(100),
            payment_status ENUM('pending','processed','paid','failed','cancelled') DEFAULT 'pending',
            payment_processed_by INT,
            payment_processed_at TIMESTAMP NULL,
            remarks TEXT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (salary_structure_id) REFERENCES employee_salary_structure(id) ON DELETE SET NULL,
            FOREIGN KEY (payment_processed_by) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

        // Employee attendance table
        $sql = "CREATE TABLE IF NOT EXISTS employee_attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            attendance_date DATE NOT NULL,
            check_in_time TIME,
            check_out_time TIME,
            total_hours DECIMAL(4,2),
            attendance_status ENUM('present','absent','half_day','leave','holiday') DEFAULT 'present',
            leave_type ENUM('casual','sick','earned','maternity','paternity','other') DEFAULT NULL,
            remarks TEXT,
            approved_by INT,
            approved_at TIMESTAMP NULL,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
            UNIQUE KEY unique_attendance (employee_id, attendance_date)
        )";

        $this->conn->query($sql);

        // Employee advances table
        $sql = "CREATE TABLE IF NOT EXISTS employee_advances (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            advance_number VARCHAR(50) NOT NULL UNIQUE,
            advance_amount DECIMAL(15,2) NOT NULL,
            advance_date DATE NOT NULL,
            reason TEXT,
            repayment_method ENUM('lump_sum','installment') DEFAULT 'installment',
            installment_amount DECIMAL(15,2),
            total_installments INT DEFAULT 1,
            paid_installments INT DEFAULT 0,
            outstanding_amount DECIMAL(15,2) NOT NULL,
            status ENUM('pending','approved','disbursed','repaid','cancelled') DEFAULT 'pending',
            approved_by INT,
            approved_at TIMESTAMP NULL,
            disbursement_date DATE,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

        // Employee bonuses table
        $sql = "CREATE TABLE IF NOT EXISTS employee_bonuses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            bonus_number VARCHAR(50) NOT NULL UNIQUE,
            bonus_type ENUM('performance','attendance','target_achievement','festival','other') NOT NULL,
            bonus_amount DECIMAL(15,2) NOT NULL,
            bonus_month INT,
            bonus_year INT,
            reason TEXT,
            payment_status ENUM('pending','paid','cancelled') DEFAULT 'pending',
            payment_date DATE,
            transaction_id VARCHAR(100),
            approved_by INT,
            approved_at TIMESTAMP NULL,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

        // Insert sample data
        $this->insertSampleSalaryData();
    }

    /**
     * Insert sample salary data
     */
    private function insertSampleSalaryData() {
        $checkSql = "SELECT COUNT(*) as count FROM employee_salary_structure";
        $result = $this->conn->query($checkSql);
        $row = $result->fetch_assoc();

        if ($row['count'] == 0) {
            // Get admin user for sample data
            $adminSql = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
            $result = $this->conn->query($adminSql);
            $admin = $result->fetch_assoc();

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

                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("iddddddddddddds",
                    $sampleStructure['employee_id'],
                    $sampleStructure['basic_salary'],
                    $sampleStructure['hra'],
                    $sampleStructure['da'],
                    $sampleStructure['ta'],
                    $sampleStructure['medical_allowance'],
                    $sampleStructure['special_allowance'],
                    $sampleStructure['pf_deduction'],
                    $sampleStructure['esi_deduction'],
                    $sampleStructure['professional_tax'],
                    $sampleStructure['tds_deduction'],
                    $sampleStructure['gross_salary'],
                    $sampleStructure['net_salary'],
                    $sampleStructure['effective_from'],
                    $sampleStructure['created_by']
                );
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    /**
     * Create salary structure for employee
     */
    public function createSalaryStructure($data) {
        $sql = "INSERT INTO employee_salary_structure (
            employee_id, basic_salary, hra, da, ta, medical_allowance, special_allowance, other_allowance,
            pf_deduction, esi_deduction, professional_tax, tds_deduction, other_deduction,
            gross_salary, net_salary, effective_from, approved_by, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iddddddddddddddss",
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
        );

        $result = $stmt->execute();
        $structureId = $stmt->insert_id;
        $stmt->close();

        if ($result) {
            // Deactivate previous salary structures for this employee
            $this->deactivatePreviousSalaryStructures($data['employee_id'], $data['effective_from']);

            if ($this->logger) {
                $this->logger->log("Salary structure created for employee ID: {$data['employee_id']}", 'info', 'salary');
            }
        }

        return $result ? $structureId : false;
    }

    /**
     * Deactivate previous salary structures
     */
    private function deactivatePreviousSalaryStructures($employeeId, $effectiveFrom) {
        $sql = "UPDATE employee_salary_structure SET is_active = FALSE
                WHERE employee_id = ? AND effective_from < ? AND is_active = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $employeeId, $effectiveFrom);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Process monthly salary payment
     */
    public function processMonthlySalary($employeeId, $month, $year) {
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
        $createdBy = $_SESSION['user_id'] ?? 1;

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiisddddds",
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
        );

        $result = $stmt->execute();
        $paymentId = $stmt->insert_id;
        $stmt->close();

        if ($result && $this->logger) {
            $this->logger->log("Salary processed for employee ID: $employeeId, Month: $month/$year, Amount: $netAmount", 'info', 'salary');
        }

        return $result ? [
            'success' => true,
            'payment_id' => $paymentId,
            'basic_amount' => $basicAmount,
            'allowance_amount' => $allowanceAmount,
            'gross_amount' => $grossAmount,
            'deduction_amount' => $deductionAmount,
            'net_amount' => $netAmount,
            'attendance' => $attendanceData,
            'adjustments' => $adjustments
        ] : ['success' => false, 'message' => 'Failed to process salary'];
    }

    /**
     * Get current salary structure for employee
     */
    private function getCurrentSalaryStructure($employeeId) {
        $sql = "SELECT * FROM employee_salary_structure
                WHERE employee_id = ? AND is_active = TRUE
                ORDER BY effective_from DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $structure = $result->fetch_assoc();
        $stmt->close();

        return $structure;
    }

    /**
     * Check if salary already processed
     */
    private function isSalaryProcessed($employeeId, $month, $year) {
        $sql = "SELECT id FROM salary_payments
                WHERE employee_id = ? AND payment_month = ? AND payment_year = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $employeeId, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    /**
     * Calculate attendance data for the month
     */
    private function calculateAttendanceData($employeeId, $month, $year) {
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

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $employeeId, $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $attendance = $result->fetch_assoc();
        $stmt->close();

        $attendance['total_days'] = $totalDays;
        $attendance['working_days'] = $totalDays - ($attendance['absent_days'] ?? 0);

        return $attendance;
    }

    /**
     * Calculate adjustments (bonuses, advances, etc.)
     */
    private function calculateAdjustments($employeeId, $month, $year) {
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
        $stmt = $this->conn->prepare($bonusSql);
        $stmt->bind_param("iii", $employeeId, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $bonuses = $result->fetch_assoc();
        $stmt->close();

        $adjustments['total_additions'] += $bonuses['total_bonuses'] ?? 0;

        // Calculate advance deductions for the month
        $advanceSql = "SELECT SUM(installment_amount) as total_advances
                      FROM employee_advances
                      WHERE employee_id = ? AND status = 'disbursed' AND outstanding_amount > 0";
        $stmt = $this->conn->prepare($advanceSql);
        $stmt->bind_param("i", $employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $advances = $result->fetch_assoc();
        $stmt->close();

        $adjustments['total_deductions'] += $advances['total_advances'] ?? 0;

        return $adjustments;
    }

    /**
     * Mark salary as paid
     */
    public function markSalaryAsPaid($paymentId, $transactionId = null, $bankReference = null) {
        $sql = "UPDATE salary_payments SET
            payment_status = 'paid',
            transaction_id = ?,
            bank_reference = ?,
            payment_processed_by = ?,
            payment_processed_at = NOW()
            WHERE id = ?";

        $processedBy = $_SESSION['user_id'] ?? 1;

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $transactionId, $bankReference, $processedBy, $paymentId);

        $result = $stmt->execute();
        $stmt->close();

        if ($result && $this->logger) {
            $this->logger->log("Salary payment marked as paid: ID $paymentId", 'info', 'salary');
        }

        return $result;
    }

    /**
     * Record employee attendance
     */
    public function recordAttendance($data) {
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

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssdssss",
            $data['employee_id'],
            $data['attendance_date'],
            $data['check_in_time'],
            $data['check_out_time'],
            $data['total_hours'],
            $data['attendance_status'],
            $data['leave_type'],
            $data['remarks'],
            $data['created_by']
        );

        $result = $stmt->execute();
        $stmt->close();

        if ($result && $this->logger) {
            $this->logger->log("Attendance recorded for employee ID: {$data['employee_id']}, Date: {$data['attendance_date']}", 'info', 'attendance');
        }

        return $result;
    }

    /**
     * Create employee advance
     */
    public function createAdvance($data) {
        $sql = "INSERT INTO employee_advances (
            employee_id, advance_number, advance_amount, advance_date, reason,
            repayment_method, installment_amount, total_installments, status, approved_by, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isdsssiiss",
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
        );

        $result = $stmt->execute();
        $advanceId = $stmt->insert_id;
        $stmt->close();

        if ($result) {
            $outstandingAmount = $data['advance_amount'];
            $this->updateAdvanceOutstandingAmount($advanceId, $outstandingAmount);

            if ($this->logger) {
                $this->logger->log("Advance created: {$data['advance_number']}, Amount: {$data['advance_amount']}", 'info', 'advance');
            }
        }

        return $result ? $advanceId : false;
    }

    /**
     * Update advance outstanding amount
     */
    private function updateAdvanceOutstandingAmount($advanceId, $amount) {
        $sql = "UPDATE employee_advances SET outstanding_amount = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("di", $amount, $advanceId);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Create employee bonus
     */
    public function createBonus($data) {
        $sql = "INSERT INTO employee_bonuses (
            employee_id, bonus_number, bonus_type, bonus_amount, bonus_month, bonus_year,
            reason, approved_by, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issdiissi",
            $data['employee_id'],
            $data['bonus_number'],
            $data['bonus_type'],
            $data['bonus_amount'],
            $data['bonus_month'],
            $data['bonus_year'],
            $data['reason'],
            $data['approved_by'],
            $data['created_by']
        );

        $result = $stmt->execute();
        $bonusId = $stmt->insert_id;
        $stmt->close();

        if ($result && $this->logger) {
            $this->logger->log("Bonus created: {$data['bonus_number']}, Amount: {$data['bonus_amount']}", 'info', 'bonus');
        }

        return $result ? $bonusId : false;
    }

    /**
     * Get salary report for employee
     */
    public function getSalaryReport($employeeId, $startMonth, $startYear, $endMonth, $endYear) {
        $report = [];

        // Salary payments
        $sql = "SELECT * FROM salary_payments
                WHERE employee_id = ? AND
                ((payment_year = ? AND payment_month >= ?) OR
                 (payment_year > ? AND payment_year < ?) OR
                 (payment_year = ? AND payment_month <= ?))
                ORDER BY payment_year, payment_month";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiiiiii", $employeeId, $startYear, $startMonth, $startYear, $endYear, $endYear, $endMonth);
        $stmt->execute();
        $result = $stmt->get_result();
        $report['salary_payments'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['salary_payments'][] = $row;
        }
        $stmt->close();

        // Bonuses
        $sql = "SELECT * FROM employee_bonuses
                WHERE employee_id = ? AND payment_status = 'paid'
                AND ((bonus_year = ? AND (bonus_month IS NULL OR bonus_month >= ?)) OR
                     (bonus_year > ? AND bonus_year < ?) OR
                     (bonus_year = ? AND (bonus_month IS NULL OR bonus_month <= ?)))
                ORDER BY bonus_year, bonus_month";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiiiiii", $employeeId, $startYear, $startMonth, $startYear, $endYear, $endYear, $endMonth);
        $stmt->execute();
        $result = $stmt->get_result();
        $report['bonuses'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['bonuses'][] = $row;
        }
        $stmt->close();

        // Advances
        $sql = "SELECT * FROM employee_advances
                WHERE employee_id = ? AND status IN ('disbursed', 'repaid')
                ORDER BY advance_date";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $report['advances'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['advances'][] = $row;
        }
        $stmt->close();

        // Summary calculations
        $report['summary'] = $this->calculateSalarySummary($report);

        return $report;
    }

    /**
     * Calculate salary summary
     */
    private function calculateSalarySummary($report) {
        $summary = [
            'total_salary_paid' => 0,
            'total_bonuses_paid' => 0,
            'total_advances_disbursed' => 0,
            'total_advances_repaid' => 0,
            'net_salary_received' => 0
        ];

        foreach ($report['salary_payments'] as $payment) {
            if ($payment['payment_status'] === 'paid') {
                $summary['total_salary_paid'] += $payment['net_amount'];
            }
        }

        foreach ($report['bonuses'] as $bonus) {
            $summary['total_bonuses_paid'] += $bonus['bonus_amount'];
        }

        foreach ($report['advances'] as $advance) {
            if ($advance['status'] === 'disbursed') {
                $summary['total_advances_disbursed'] += $advance['advance_amount'];
            }
            if ($advance['status'] === 'repaid') {
                $summary['total_advances_repaid'] += $advance['advance_amount'];
            }
        }

        $summary['net_salary_received'] = $summary['total_salary_paid'] + $summary['total_bonuses_paid'] -
                                         $summary['total_advances_disbursed'] + $summary['total_advances_repaid'];

        return $summary;
    }

    /**
     * Get payroll dashboard data
     */
    public function getPayrollDashboard() {
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
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $currentMonth, $currentYear);
        $stmt->execute();
        $result = $stmt->get_result();
        $dashboard['monthly_payroll'] = $result->fetch_assoc();
        $stmt->close();

        // Pending salaries
        $sql = "SELECT COUNT(*) as pending_salaries FROM salary_payments
                WHERE payment_status = 'pending'";
        $result = $this->conn->query($sql);
        $dashboard['pending_salaries'] = $result->fetch_assoc();

        // Department wise salary
        $sql = "SELECT u.role,
                COUNT(DISTINCT sp.employee_id) as employees,
                SUM(sp.net_amount) as total_salary
                FROM salary_payments sp
                JOIN users u ON sp.employee_id = u.id
                WHERE sp.payment_month = ? AND sp.payment_year = ? AND sp.payment_status = 'paid'
                GROUP BY u.role";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $currentMonth, $currentYear);
        $stmt->execute();
        $result = $stmt->get_result();
        $dashboard['department_salary'] = [];
        while ($row = $result->fetch_assoc()) {
            $dashboard['department_salary'][] = $row;
        }
        $stmt->close();

        // Upcoming salary payments
        $sql = "SELECT sp.*, u.full_name, u.role
                FROM salary_payments sp
                JOIN users u ON sp.employee_id = u.id
                WHERE sp.payment_status = 'pending'
                ORDER BY sp.payment_date LIMIT 10";
        $result = $this->conn->query($sql);
        $dashboard['upcoming_payments'] = [];
        while ($row = $result->fetch_assoc()) {
            $dashboard['upcoming_payments'][] = $row;
        }

        return $dashboard;
    }
}
?>
