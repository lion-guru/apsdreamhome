<?php

namespace App\Http\Controllers\Payroll;

use App\Services\Payroll\SalaryService;
use Psr\Log\LoggerInterface;

class SalaryController
{
    private SalaryService $salaryService;
    private LoggerInterface $logger;

    public function __construct(SalaryService $salaryService, LoggerInterface $logger)
    {
        $this->salaryService = $salaryService;
        $this->logger = $logger;
    }

    /**
     * Create salary structure
     */
    public function createSalaryStructure()
    {
        try {
            $data = request()->all();

            // Validate required fields
            $required = ['employee_id', 'basic_salary', 'gross_salary', 'net_salary', 'effective_from', 'created_by'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Field {$field} is required"
                    ], 400);
                }
            }

            $structureId = $this->salaryService->createSalaryStructure($data);

            if ($structureId > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Salary structure created successfully',
                    'structure_id' => $structureId
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create salary structure'
                ], 500);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to create salary structure", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create salary structure'
            ], 500);
        }
    }

    /**
     * Update salary structure
     */
    public function updateSalaryStructure()
    {
        try {
            $structureId = request()->input('structure_id');
            $data = request()->all();

            if (!$structureId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Structure ID is required'
                ], 400);
            }

            if ($this->salaryService->updateSalaryStructure((int)$structureId, $data)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Salary structure updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update salary structure'
                ], 500);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to update salary structure", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update salary structure'
            ], 500);
        }
    }

    /**
     * Process monthly salary
     */
    public function processMonthlySalary()
    {
        try {
            $employeeId = request()->input('employee_id');
            $month = request()->input('month', (int)date('m'));
            $year = request()->input('year', (int)date('Y'));

            if (!$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee ID is required'
                ], 400);
            }

            $result = $this->salaryService->processMonthlySalary((int)$employeeId, (int)$month, (int)$year);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Monthly salary processed successfully',
                    'payment_id' => $result['payment_id'],
                    'salary_data' => $result['salary_data']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to process monthly salary", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to process monthly salary'
            ], 500);
        }
    }

    /**
     * Get salary history
     */
    public function getSalaryHistory()
    {
        try {
            $employeeId = request()->input('employee_id');
            $limit = request()->input('limit', 12);

            if (!$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee ID is required'
                ], 400);
            }

            $history = $this->salaryService->getSalaryHistory((int)$employeeId, (int)$limit);

            return response()->json([
                'success' => true,
                'history' => $history,
                'total' => count($history)
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get salary history", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get salary history'
            ], 500);
        }
    }

    /**
     * Get payroll statistics
     */
    public function getPayrollStatistics()
    {
        try {
            $stats = $this->salaryService->getPayrollStatistics();

            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get payroll statistics", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get payroll statistics'
            ], 500);
        }
    }

    /**
     * Get payroll settings
     */
    public function getPayrollSettings()
    {
        try {
            $settings = $this->salaryService->getPayrollSettings();

            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get payroll settings", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get payroll settings'
            ], 500);
        }
    }

    /**
     * Update payroll setting
     */
    public function updatePayrollSetting()
    {
        try {
            $key = request()->input('key');
            $value = request()->input('value');

            if (!$key || !$value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Key and value are required'
                ], 400);
            }

            if ($this->salaryService->updatePayrollSetting($key, $value)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payroll setting updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update payroll setting'
                ], 500);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to update payroll setting", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payroll setting'
            ], 500);
        }
    }

    /**
     * Bulk process monthly salaries
     */
    public function bulkProcessSalaries()
    {
        try {
            $month = request()->input('month', (int)date('m'));
            $year = request()->input('year', (int)date('Y'));
            $employeeIds = request()->input('employee_ids', []);

            if (empty($employeeIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee IDs are required'
                ], 400);
            }

            $results = [];
            $successCount = 0;
            $failureCount = 0;

            foreach ($employeeIds as $employeeId) {
                $result = $this->salaryService->processMonthlySalary((int)$employeeId, (int)$month, (int)$year);
                $results[] = [
                    'employee_id' => $employeeId,
                    'success' => $result['success'],
                    'message' => $result['message'] ?? 'Processed'
                ];

                if ($result['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bulk salary processing completed',
                'results' => $results,
                'summary' => [
                    'total' => count($employeeIds),
                    'success' => $successCount,
                    'failure' => $failureCount
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to bulk process salaries", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk process salaries'
            ], 500);
        }
    }

    /**
     * Get salary slip
     */
    public function getSalarySlip()
    {
        try {
            $employeeId = request()->input('employee_id');
            $month = request()->input('month');
            $year = request()->input('year');

            if (!$employeeId || !$month || !$year) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee ID, month, and year are required'
                ], 400);
            }

            $history = $this->salaryService->getSalaryHistory((int)$employeeId, 24);
            
            // Find the specific month/year record
            $salaryRecord = null;
            foreach ($history as $record) {
                if ($record['month'] == (int)$month && $record['year'] == (int)$year) {
                    $salaryRecord = $record;
                    break;
                }
            }

            if (!$salaryRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salary record not found for the specified period'
                ], 404);
            }

            // Get employee details
            $employee = $this->db->fetchOne(
                "SELECT id, name, email, employee_id, department, designation FROM users WHERE id = ?",
                [$employeeId]
            );

            return response()->json([
                'success' => true,
                'salary_slip' => [
                    'employee' => $employee,
                    'salary' => $salaryRecord,
                    'period' => [
                        'month' => (int)$month,
                        'year' => (int)$year,
                        'month_name' => date('F', mktime(0, 0, 0, (int)$month, 1, (int)$year))
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get salary slip", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get salary slip'
            ], 500);
        }
    }

    /**
     * Export salary report
     */
    public function exportSalaryReport()
    {
        try {
            $month = request()->input('month', (int)date('m'));
            $year = request()->input('year', (int)date('Y'));
            $format = request()->input('format', 'csv');

            // Get all processed salaries for the month
            $salaries = $this->db->fetchAll(
                "SELECT msp.*, u.name, u.email, u.employee_id 
                 FROM monthly_salary_payments msp 
                 JOIN users u ON msp.employee_id = u.id 
                 WHERE msp.month = ? AND msp.year = ? AND msp.payment_status = 'paid'
                 ORDER BY u.name",
                [$month, $year]
            );

            if ($format === 'csv') {
                $filename = "salary_report_{$year}_{$month}.csv";
                
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                
                $output = fopen('php://output', 'w');
                
                // Header
                fputcsv($output, [
                    'Employee ID', 'Name', 'Email', 'Gross Salary', 'Total Deductions', 
                    'Net Salary', 'Working Days', 'Present Days', 'Leave Days', 
                    'Overtime Hours', 'Overtime Amount'
                ]);
                
                // Data
                foreach ($salaries as $salary) {
                    fputcsv($output, [
                        $salary['employee_id'],
                        $salary['name'],
                        $salary['email'],
                        $salary['gross_salary'],
                        $salary['total_deductions'],
                        $salary['net_salary'],
                        $salary['working_days'],
                        $salary['present_days'],
                        $salary['leave_days'],
                        $salary['overtime_hours'],
                        $salary['overtime_amount']
                    ]);
                }
                
                fclose($output);
                exit;
            }

            return response()->json([
                'success' => true,
                'salaries' => $salaries,
                'period' => ['month' => $month, 'year' => $year]
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to export salary report", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to export salary report'
            ], 500);
        }
    }

    /**
     * Payroll management page
     */
    public function management()
    {
        try {
            $stats = $this->salaryService->getPayrollStatistics();
            $settings = $this->salaryService->getPayrollSettings();
            
            return view('payroll.management', [
                'stats' => $stats,
                'settings' => $settings,
                'page_title' => 'Payroll Management - APS Dream Home'
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to load payroll management", ['error' => $e->getMessage()]);
            return view('errors.500');
        }
    }

    /**
     * Salary structure page
     */
    public function salaryStructure()
    {
        try {
            $settings = $this->salaryService->getPayrollSettings();
            
            return view('payroll.salary_structure', [
                'settings' => $settings,
                'page_title' => 'Salary Structure - APS Dream Home'
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to load salary structure", ['error' => $e->getMessage()]);
            return view('errors.500');
        }
    }

    /**
     * Salary processing page
     */
    public function salaryProcessing()
    {
        try {
            $stats = $this->salaryService->getPayrollStatistics();
            $settings = $this->salaryService->getPayrollSettings();
            
            return view('payroll.processing', [
                'stats' => $stats,
                'settings' => $settings,
                'page_title' => 'Salary Processing - APS Dream Home'
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to load salary processing", ['error' => $e->getMessage()]);
            return view('errors.500');
        }
    }

    /**
     * Salary reports page
     */
    public function salaryReports()
    {
        try {
            $stats = $this->salaryService->getPayrollStatistics();
            
            return view('payroll.reports', [
                'stats' => $stats,
                'page_title' => 'Salary Reports - APS Dream Home'
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to load salary reports", ['error' => $e->getMessage()]);
            return view('errors.500');
        }
    }
}
