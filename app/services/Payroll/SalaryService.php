<?php

namespace App\Services\Payroll;

use App\Core\Database\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Salary Management Service
 * Handles employee payroll, attendance, and financial tracking
 */
class SalaryService
{
    private Database $db;
    private LoggerInterface $logger;

    public function __construct(Database $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->initializePayrollSystem();
    }

    /**
     * Initialize payroll system
     */
    private function initializePayrollSystem(): void
    {
        try {
            $this->createPayrollTables();
            $this->insertSampleData();
            $this->logger->info('Payroll system initialized');
        } catch (\Exception $e) {
            $this->logger->error('Failed to initialize payroll system', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Payroll system initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Create payroll tables
     */
    private function createPayrollTables(): void
    {
        $tables = [
            'employee_salary_structure' => "
                CREATE TABLE IF NOT EXISTS employee_salary_structure (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    employee_id INT NOT NULL,
                    basic_salary DECIMAL(10,2) NOT NULL,
                    hra DECIMAL(10,2) DEFAULT 0,
                    da DECIMAL(10,2) DEFAULT 0,
                    ta DECIMAL(10,2) DEFAULT 0,
                    medical_allowance DECIMAL(10,2) DEFAULT 0,
                    special_allowance DECIMAL(10,2) DEFAULT 0,
                    other_allowance DECIMAL(10,2) DEFAULT 0,
                    pf_deduction DECIMAL(10,2) DEFAULT 0,
                    esi_deduction DECIMAL(10,2) DEFAULT 0,
                    professional_tax DECIMAL(10,2) DEFAULT 0,
                    tds_deduction DECIMAL(10,2) DEFAULT 0,
                    other_deduction DECIMAL(10,2) DEFAULT 0,
                    gross_salary DECIMAL(10,2) NOT NULL,
                    net_salary DECIMAL(10,2) NOT NULL,
                    effective_from DATE NOT NULL,
                    is_active BOOLEAN DEFAULT TRUE,
                    approved_by INT,
                    created_by INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_employee_id (employee_id),
                    INDEX idx_effective_from (effective_from),
                    INDEX idx_is_active (is_active)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'monthly_salary_payments' => "
                CREATE TABLE IF NOT EXISTS monthly_salary_payments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    employee_id INT NOT NULL,
                    month INT NOT NULL,
                    year INT NOT NULL,
                    gross_salary DECIMAL(10,2) NOT NULL,
                    total_deductions DECIMAL(10,2) NOT NULL,
                    net_salary DECIMAL(10,2) NOT NULL,
                    working_days INT DEFAULT 0,
                    present_days INT DEFAULT 0,
                    leave_days INT DEFAULT 0,
                    overtime_hours DECIMAL(5,2) DEFAULT 0,
                    overtime_amount DECIMAL(10,2) DEFAULT 0,
                    payment_date DATE,
                    payment_status ENUM('pending', 'processed', 'paid') DEFAULT 'pending',
                    payment_method VARCHAR(50),
                    transaction_id VARCHAR(100),
                    processed_by INT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_employee_month_year (employee_id, month, year),
                    INDEX idx_employee_id (employee_id),
                    INDEX idx_payment_status (payment_status),
                    INDEX idx_payment_date (payment_date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'payroll_settings' => "
                CREATE TABLE IF NOT EXISTS payroll_settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    setting_key VARCHAR(100) NOT NULL UNIQUE,
                    setting_value TEXT NOT NULL,
                    description TEXT,
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            "
        ];

        foreach ($tables as $tableName => $sql) {
            try {
                $this->db->execute($sql);
                $this->logger->info("Payroll table created or verified", ['table' => $tableName]);
            } catch (\Exception $e) {
                $this->logger->error("Failed to create payroll table", [
                    'table' => $tableName,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->insertDefaultSettings();
    }

    /**
     * Insert default settings
     */
    private function insertDefaultSettings(): void
    {
        $defaultSettings = [
            'pf_percentage' => '12',
            'esi_percentage' => '1.75',
            'professional_tax' => '200',
            'working_days_per_month' => '26',
            'overtime_rate' => '2',
            'minimum_wage' => '15000'
        ];

        foreach ($defaultSettings as $key => $value) {
            try {
                $this->db->execute(
                    "INSERT IGNORE INTO payroll_settings (setting_key, setting_value, description) VALUES (?, ?, ?)",
                    [$key, $value, "Default setting for {$key}"]
                );
            } catch (\Exception $e) {
                $this->logger->warning("Failed to insert default setting", [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Insert sample data
     */
    private function insertSampleData(): void
    {
        try {
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM employee_salary_structure");
            
            if ($result['count'] == 0) {
                $admin = $this->db->fetchOne("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
                
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

                    $this->createSalaryStructure($sampleStructure);
                }
            }
        } catch (\Exception $e) {
            $this->logger->warning("Failed to insert sample data", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create salary structure for employee
     */
    public function createSalaryStructure(array $data): int
    {
        try {
            $this->db->execute(
                "INSERT INTO employee_salary_structure (
                    employee_id, basic_salary, hra, da, ta, medical_allowance, special_allowance, other_allowance,
                    pf_deduction, esi_deduction, professional_tax, tds_deduction, other_deduction,
                    gross_salary, net_salary, effective_from, approved_by, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $data['employee_id'],
                    $data['basic_salary'],
                    $data['hra'] ?? 0,
                    $data['da'] ?? 0,
                    $data['ta'] ?? 0,
                    $data['medical_allowance'] ?? 0,
                    $data['special_allowance'] ?? 0,
                    $data['other_allowance'] ?? 0,
                    $data['pf_deduction'] ?? 0,
                    $data['esi_deduction'] ?? 0,
                    $data['professional_tax'] ?? 0,
                    $data['tds_deduction'] ?? 0,
                    $data['other_deduction'] ?? 0,
                    $data['gross_salary'],
                    $data['net_salary'],
                    $data['effective_from'],
                    $data['approved_by'] ?? null,
                    $data['created_by']
                ]
            );

            $structureId = (int)$this->db->lastInsertId();

            if ($structureId) {
                $this->deactivatePreviousSalaryStructures($data['employee_id'], $data['effective_from']);
                $this->logger->info("Salary structure created", ['employee_id' => $data['employee_id']]);
                return $structureId;
            }

            return 0;
        } catch (\Exception $e) {
            $this->logger->error("Failed to create salary structure", [
                'employee_id' => $data['employee_id'] ?? null,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Deactivate previous salary structures
     */
    private function deactivatePreviousSalaryStructures(int $employeeId, string $effectiveFrom): void
    {
        try {
            $this->db->execute(
                "UPDATE employee_salary_structure SET is_active = FALSE
                 WHERE employee_id = ? AND effective_from < ? AND is_active = TRUE",
                [$employeeId, $effectiveFrom]
            );
        } catch (\Exception $e) {
            $this->logger->error("Failed to deactivate previous salary structures", [
                'employee_id' => $employeeId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process monthly salary payment
     */
    public function processMonthlySalary(int $employeeId, int $month, int $year): array
    {
        try {
            $salaryStructure = $this->getCurrentSalaryStructure($employeeId);
            if (!$salaryStructure) {
                return ['success' => false, 'message' => 'No salary structure found'];
            }

            if ($this->isSalaryProcessed($employeeId, $month, $year)) {
                return ['success' => false, 'message' => 'Salary already processed'];
            }

            $attendanceData = $this->calculateAttendanceData($employeeId, $month, $year);
            $salaryData = $this->calculateMonthlySalary($salaryStructure, $attendanceData);

            $paymentId = $this->saveMonthlyPayment($salaryData);
            
            if ($paymentId) {
                $this->logger->info("Monthly salary processed", [
                    'employee_id' => $employeeId,
                    'month' => $month,
                    'year' => $year,
                    'payment_id' => $paymentId
                ]);
                
                return ['success' => true, 'payment_id' => $paymentId, 'salary_data' => $salaryData];
            }

            return ['success' => false, 'message' => 'Failed to process salary'];
        } catch (\Exception $e) {
            $this->logger->error("Failed to process monthly salary", [
                'employee_id' => $employeeId,
                'month' => $month,
                'year' => $year,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Salary processing failed'];
        }
    }

    /**
     * Get current salary structure
     */
    private function getCurrentSalaryStructure(int $employeeId): ?array
    {
        try {
            return $this->db->fetchOne(
                "SELECT * FROM employee_salary_structure 
                 WHERE employee_id = ? AND is_active = TRUE 
                 ORDER BY effective_from DESC LIMIT 1",
                [$employeeId]
            );
        } catch (\Exception $e) {
            $this->logger->error("Failed to get current salary structure", [
                'employee_id' => $employeeId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Check if salary already processed
     */
    private function isSalaryProcessed(int $employeeId, int $month, int $year): bool
    {
        try {
            $result = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM monthly_salary_payments 
                 WHERE employee_id = ? AND month = ? AND year = ?",
                [$employeeId, $month, $year]
            );
            return $result['count'] > 0;
        } catch (\Exception $e) {
            $this->logger->error("Failed to check salary processed status", [
                'employee_id' => $employeeId,
                'month' => $month,
                'year' => $year,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Calculate attendance data
     */
    private function calculateAttendanceData(int $employeeId, int $month, int $year): array
    {
        // Simplified attendance calculation
        return [
            'working_days' => 26,
            'present_days' => 24,
            'leave_days' => 2,
            'overtime_hours' => 4.5
        ];
    }

    /**
     * Calculate monthly salary
     */
    private function calculateMonthlySalary(array $salaryStructure, array $attendanceData): array
    {
        $workingDays = $attendanceData['working_days'];
        $presentDays = $attendanceData['present_days'];
        $overtimeHours = $attendanceData['overtime_hours'];

        // Calculate pro-rata salary based on attendance
        $attendanceRatio = $workingDays > 0 ? $presentDays / $workingDays : 1;
        
        $grossSalary = $salaryStructure['gross_salary'] * $attendanceRatio;
        $totalDeductions = ($salaryStructure['pf_deduction'] + $salaryStructure['esi_deduction'] + 
                              $salaryStructure['professional_tax'] + $salaryStructure['tds_deduction']) * $attendanceRatio;
        
        $netSalary = $grossSalary - $totalDeductions;
        $overtimeAmount = $overtimeHours * 200; // Simplified overtime calculation

        return [
            'employee_id' => $salaryStructure['employee_id'],
            'month' => (int)date('m'),
            'year' => (int)date('Y'),
            'gross_salary' => $grossSalary,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'working_days' => $workingDays,
            'present_days' => $presentDays,
            'leave_days' => $workingDays - $presentDays,
            'overtime_hours' => $overtimeHours,
            'overtime_amount' => $overtimeAmount,
            'final_net_salary' => $netSalary + $overtimeAmount
        ];
    }

    /**
     * Save monthly payment
     */
    private function saveMonthlyPayment(array $salaryData): int
    {
        try {
            $this->db->execute(
                "INSERT INTO monthly_salary_payments (
                    employee_id, month, year, gross_salary, total_deductions, net_salary,
                    working_days, present_days, leave_days, overtime_hours, overtime_amount,
                    payment_status, processed_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $salaryData['employee_id'],
                    $salaryData['month'],
                    $salaryData['year'],
                    $salaryData['gross_salary'],
                    $salaryData['total_deductions'],
                    $salaryData['net_salary'],
                    $salaryData['working_days'],
                    $salaryData['present_days'],
                    $salaryData['leave_days'],
                    $salaryData['overtime_hours'],
                    $salaryData['overtime_amount'],
                    'processed',
                    $_SESSION['user_id'] ?? null
                ]
            );

            return (int)$this->db->lastInsertId();
        } catch (\Exception $e) {
            $this->logger->error("Failed to save monthly payment", ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Get salary history
     */
    public function getSalaryHistory(int $employeeId, int $limit = 12): array
    {
        try {
            $payments = $this->db->fetchAll(
                "SELECT * FROM monthly_salary_payments 
                 WHERE employee_id = ? 
                 ORDER BY year DESC, month DESC 
                 LIMIT ?",
                [$employeeId, $limit]
            );

            return array_map(function($payment) {
                return [
                    'id' => $payment['id'],
                    'month' => $payment['month'],
                    'year' => $payment['year'],
                    'gross_salary' => (float)$payment['gross_salary'],
                    'net_salary' => (float)$payment['net_salary'],
                    'payment_status' => $payment['payment_status'],
                    'payment_date' => $payment['payment_date'],
                    'working_days' => $payment['working_days'],
                    'present_days' => $payment['present_days'],
                    'overtime_hours' => (float)$payment['overtime_hours'],
                    'overtime_amount' => (float)$payment['overtime_amount']
                ];
            }, $payments);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get salary history", [
                'employee_id' => $employeeId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get payroll statistics
     */
    public function getPayrollStatistics(): array
    {
        try {
            $stats = [];

            // Total employees with salary structure
            $result = $this->db->fetchOne(
                "SELECT COUNT(DISTINCT employee_id) as total FROM employee_salary_structure WHERE is_active = TRUE"
            );
            $stats['total_employees'] = (int)($result['total'] ?? 0);

            // Total salary processed this month
            $result = $this->db->fetchOne(
                "SELECT SUM(net_salary) as total, COUNT(*) as count FROM monthly_salary_payments 
                 WHERE month = ? AND year = ? AND payment_status = 'paid'",
                [(int)date('m'), (int)date('Y')]
            );
            $stats['monthly_payroll_total'] = (float)($result['total'] ?? 0);
            $stats['monthly_payments_count'] = (int)($result['count'] ?? 0);

            // Average salary
            $result = $this->db->fetchOne(
                "SELECT AVG(net_salary) as avg_salary FROM employee_salary_structure WHERE is_active = TRUE"
            );
            $stats['average_salary'] = (float)($result['avg_salary'] ?? 0);

            return $stats;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get payroll statistics", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Update salary structure
     */
    public function updateSalaryStructure(int $structureId, array $data): bool
    {
        try {
            $this->db->execute(
                "UPDATE employee_salary_structure SET 
                 basic_salary = ?, hra = ?, da = ?, ta = ?, medical_allowance = ?, 
                 special_allowance = ?, other_allowance = ?, pf_deduction = ?, esi_deduction = ?, 
                 professional_tax = ?, tds_deduction = ?, other_deduction = ?, 
                 gross_salary = ?, net_salary = ?, updated_at = CURRENT_TIMESTAMP
                 WHERE id = ?",
                [
                    $data['basic_salary'],
                    $data['hra'] ?? 0,
                    $data['da'] ?? 0,
                    $data['ta'] ?? 0,
                    $data['medical_allowance'] ?? 0,
                    $data['special_allowance'] ?? 0,
                    $data['other_allowance'] ?? 0,
                    $data['pf_deduction'] ?? 0,
                    $data['esi_deduction'] ?? 0,
                    $data['professional_tax'] ?? 0,
                    $data['tds_deduction'] ?? 0,
                    $data['other_deduction'] ?? 0,
                    $data['gross_salary'],
                    $data['net_salary'],
                    $structureId
                ]
            );

            $this->logger->info("Salary structure updated", ['structure_id' => $structureId]);
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to update salary structure", [
                'structure_id' => $structureId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get payroll settings
     */
    public function getPayrollSettings(): array
    {
        try {
            $settings = $this->db->fetchAll("SELECT * FROM payroll_settings WHERE is_active = TRUE");
            
            $result = [];
            foreach ($settings as $setting) {
                $result[$setting['setting_key']] = $setting['setting_value'];
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get payroll settings", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Update payroll setting
     */
    public function updatePayrollSetting(string $key, string $value): bool
    {
        try {
            $this->db->execute(
                "UPDATE payroll_settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP 
                 WHERE setting_key = ?",
                [$value, $key]
            );

            $this->logger->info("Payroll setting updated", ['key' => $key]);
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to update payroll setting", [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
