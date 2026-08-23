<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Traits\TenantAwareTrait;
use Exception;

/**
 * HR Manager Controller
 * Handles human resources management, payroll, and employee relations
 */
class HRManagerController extends BaseController
{
    use TenantAwareTrait;

    protected $db;
    protected $employeeId;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/employee';
        $this->db = Database::getInstance();
        $this->initializeEmployeeSession();
    }

    /**
     * Initialize employee session
     */
    private function initializeEmployeeSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $this->employeeId = $_SESSION['employee_id'] ?? $_SESSION['user_id'] ?? null;

        if (!$this->employeeId) {
            header('Location: ' . BASE_URL . '/employee/login');
            exit;
        }
    }

    /**
     * HR Manager dashboard
     */
    public function dashboard()
    {
        try {
            // Get employee count by department
            $employeeStats = $this->getEmployeeStats();
            
            // Get pending applications
            $pendingApplications = $this->getPendingApplications();
            
            // Get upcoming reviews
            $upcomingReviews = $this->getUpcomingReviews();
            
            // Get payroll status
            $payrollStatus = $this->getPayrollStatus();
            
            // Get recruitment metrics
            $recruitmentMetrics = $this->getRecruitmentMetrics();
            
            // Get recent activities
            $recentActivities = $this->getRecentActivities();

            $this->render('employee/hr_dashboard', [
                'page_title' => 'HR Dashboard - APS Dream Home',
                'employee_stats' => $employeeStats,
                'pending_applications' => $pendingApplications,
                'upcoming_reviews' => $upcomingReviews,
                'payroll_status' => $payrollStatus,
                'recruitment_metrics' => $recruitmentMetrics,
                'recent_activities' => $recentActivities
            ]);

        } catch (\Exception $e) {
            error_log("HR Manager Controller Error: " . $e->getMessage());
            $this->render('employees/department', [
                'page_title' => 'HR Dashboard',
                'dept_title' => 'HR Dashboard',
                'dept_icon'  => 'fas fa-users-cog',
                'dept_desc'  => 'Human resources overview: attendance, leaves, payroll, and employee management.',
                'dept_color' => '#f59e0b',
                'dept_slug'  => 'hr-dashboard',
                'employee_id' => $this->employeeId,
                'employee_name' => $_SESSION['employee_name'] ?? $_SESSION['user_name'] ?? 'Employee',
            ]);
        }
    }

    /**
     * Get employee statistics
     */
    private function getEmployeeStats()
    {
        // Employee count by department
        $deptQuery = "SELECT d.name as department, 
                             COUNT(e.id) as total_employees,
                             SUM(CASE WHEN e.status = 'active' THEN 1 ELSE 0 END) as active_employees,
                             SUM(CASE WHEN e.joining_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_hires,
                             NULL as avg_performance
                      FROM departments d
                      LEFT JOIN employees e ON d.name COLLATE utf8mb4_unicode_ci = e.department
                      GROUP BY d.id, d.name
                      ORDER BY total_employees DESC";
        
        $departmentStats = $this->db->fetchAll($deptQuery);
        
        // Overall employee statistics
        [$tidSql, $tidParams] = $this->tenantWhere();
        if ($tidSql) {
            $tidSql = ' WHERE tenant_id = ?';
        }
        $overallQuery = "SELECT 
                            COUNT(*) as total_employees,
                            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_employees,
                            SUM(CASE WHEN status = 'on_leave' THEN 1 ELSE 0 END) as on_leave,
                            SUM(CASE WHEN status = 'terminated' THEN 1 ELSE 0 END) as `terminated`,
                            SUM(CASE WHEN joining_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_hires_this_month,
                            SUM(CASE WHEN joining_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) THEN 1 ELSE 0 END) as new_hires_this_quarter,
                            NULL as avg_performance_score
                         FROM employees{$tidSql}";
        
        $overallStats = $this->db->fetchOne($overallQuery, $tidParams);
        
        // Employee turnover rate
        [$tidSql, $tidParams] = $this->tenantWhere();
        if ($tidSql) {
            $tidSql = ' WHERE tenant_id = ?';
        }
        $turnoverQuery = "SELECT 
                             (SUM(CASE WHEN status = 'terminated' THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0)) * 100 as turnover_rate
                          FROM employees{$tidSql}";
        
        $turnoverRate = $this->db->fetchOne($turnoverQuery, $tidParams);
        
        return [
            'department_stats' => $departmentStats,
            'overall_stats' => $overallStats,
            'turnover_rate' => round($turnoverRate['turnover_rate'] ?? 0, 2)
        ];
    }

    /**
     * Get pending job applications
     */
    private function getPendingApplications()
    {
        $query = "SELECT ja.*, ja.message AS position_title
                 FROM job_applications ja
                 ORDER BY ja.id DESC
                 LIMIT 10";
        
        return $this->db->fetchAll($query);
    }

    /**
     * Get upcoming performance reviews
     */
    private function getUpcomingReviews()
    {
        $query = "SELECT pr.*, e.name as employee_name, e.department as employee_department
                 FROM performance_reviews pr
                 JOIN employees e ON pr.employee_id = e.id
                 WHERE pr.review_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                 AND pr.status IN ('draft', 'submitted', 'under_review')
                 ORDER BY pr.review_date ASC
                 LIMIT 15";
        
        return $this->db->fetchAll($query);
    }

    /**
     * Get payroll status
     */
    private function getPayrollStatus()
    {
        // Current month payroll status
        $currentMonthQuery = "SELECT 
                                 COUNT(*) as total_employees,
                                 SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as processed,
                                 SUM(CASE WHEN status IN ('draft', 'approved') THEN 1 ELSE 0 END) as pending,
                                 SUM(net_salary) as total_payroll_amount
                              FROM payroll_entries 
                              WHERE MONTH(created_at) = MONTH(CURDATE()) 
                              AND YEAR(created_at) = YEAR(CURDATE())";
        
        $currentMonth = $this->db->fetchOne($currentMonthQuery);
        
        // Last month payroll for comparison
        $lastMonthQuery = "SELECT 
                              SUM(net_salary) as total_amount,
                              COUNT(*) as processed_count
                           FROM payroll_entries 
                           WHERE MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                           AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                           AND status = 'paid'";
        
        $lastMonth = $this->db->fetchOne($lastMonthQuery);
        
        // Payroll deadlines module not yet implemented
        $deadlines = null;
        
        return [
            'current_month' => $currentMonth,
            'last_month' => $lastMonth,
            'deadlines' => $deadlines,
            'processing_percentage' => $currentMonth['total_employees'] > 0 ? 
                                     round(($currentMonth['processed'] / $currentMonth['total_employees']) * 100, 2) : 0
        ];
    }

    /**
     * Get recruitment metrics
     */
    private function getRecruitmentMetrics()
    {
        // Open positions (careers table drives the public job board)
        $openPositionsQuery = "SELECT COUNT(*) as count, 0 as urgent 
                               FROM careers 
                               WHERE status = 'open'";
        
        $openPositions = $this->db->fetchOne($openPositionsQuery);
        
        // Recruitment pipeline (job_applications tracks applicant contact only)
        $pipeline = [
            'applied' => (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM job_applications")['c'] ?? 0),
            'screening' => 0,
            'interview' => 0,
            'offer' => 0,
            'rejected' => 0
        ];
        
        // Time to hire not tracked (no hire_date column on applications)
        $timeToHire = [
            'avg_time_to_hire' => null,
            'min_time_to_hire' => null,
            'max_time_to_hire' => null
        ];
        
        // Source effectiveness not tracked (no source/hire_date on applications)
        $sourceEffectiveness = [];
        
        return [
            'open_positions' => $openPositions,
            'pipeline' => $pipeline,
            'time_to_hire' => $timeToHire,
            'source_effectiveness' => $sourceEffectiveness
        ];
    }

    /**
     * Get recent HR activities
     */
    private function getRecentActivities()
    {
        $query = "SELECT * FROM employee_activities 
                  WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  ORDER BY created_at DESC
                  LIMIT 10";
        
        return $this->db->fetchAll($query);
    }

    /**
     * Process payroll for users
     */
    public function processPayroll()
    {
        try {
            // Inputs come from POST form (router dispatches with zero args)
            $month = (int)($_POST['month'] ?? date('n'));
            $year = (int)($_POST['year'] ?? date('Y'));

            // Validate payroll period
            if ($this->isPayrollProcessed($month, $year)) {
                throw new Exception("Payroll for {$month}-{$year} has already been processed");
            }
            
            // Get all active employees
            [$tidSql, $tidParams] = $this->tenantWhere();
            $employeesQuery = "SELECT e.*, e.department AS department_name,
                                      e.salary AS base_salary, 0 AS allowances, 0 AS deductions
                               FROM employees e
                               WHERE e.status = 'active'{$tidSql}";
            
            $users = $this->db->fetchAll($employeesQuery, $tidParams);
            
            // Create payroll run
            $periodStart = "{$year}-" . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . "-01";
            $periodEnd = date('Y-m-t', strtotime($periodStart));
            $runName = "Payroll " . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . "/{$year}";
            
            [$runTidSql, $runTidParams] = $this->tenantWhere();
            if ($runTidSql) {
                $runTidSql = ', tenant_id';
            }
            $runQuery = "INSERT INTO payroll_runs (
                            run_name, pay_period_start, pay_period_end, pay_date,
                            total_employees, status, processed_by,
                            created_at{$runTidSql}
                        ) VALUES (?, ?, ?, ?, ?, 'processing', ?, NOW(){$runTidSql})";
            
            $runParams = [$runName, $periodStart, $periodEnd, $periodEnd, count($users), $this->employeeId];
            if (!empty($runTidParams)) {
                $runParams[] = current($runTidParams);
            }
            $this->db->execute($runQuery, $runParams);
            $runId = (int)($this->db->fetchOne("SELECT MAX(id) as id FROM payroll_runs")['id'] ?? 0);
            
            $processedCount = 0;
            $totalAmount = 0;
            $totalGross = 0;
            $totalDeductionsSum = 0;
            
            foreach ($users as $employee) {
                // Calculate salary components
                $grossSalary = $this->calculateGrossSalary($employee);
                $deductions = $this->calculateDeductions($employee, $grossSalary);
                $netSalary = $grossSalary - $deductions;
                
                // Insert payroll entry
                $pf = round(((float)$employee['base_salary']) * 0.12, 2);
                $otherDeductions = max(0, round($deductions - $pf, 2));
                
                $payrollQuery = "INSERT INTO payroll_entries (
                                    tenant_id, payroll_run_id, employee_id,
                                    basic_salary, gross_salary,
                                    pf_deduction, other_deductions, total_deductions,
                                    net_salary, status, created_at
                                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', NOW())";
                
                $entryTenant = !empty($runTidParams) ? current($runTidParams) : 1;
                $this->db->execute($payrollQuery, [
                    $entryTenant,
                    $runId,
                    $employee['id'],
                    $employee['base_salary'],
                    $grossSalary,
                    $pf,
                    $otherDeductions,
                    round($deductions, 2),
                    $netSalary
                ]);
                
                $totalAmount += $netSalary;
                $totalGross += $grossSalary;
                $totalDeductionsSum += $deductions;
                $processedCount++;
                
                // Log payroll activity
                $this->logHRActivity('payroll_processed', "Processed payroll for {$employee['name']}", $employee['id']);
            }
            
            // Mark run completed with totals
            if ($runId > 0) {
                $updateRun = "UPDATE payroll_runs 
                              SET status = 'completed', total_gross = ?,
                                  total_net = ?, total_deductions = ?
                              WHERE id = ?";
                $this->db->execute($updateRun, [
                    round($totalGross, 2),
                    round($totalAmount, 2),
                    round($totalDeductionsSum, 2),
                    $runId
                ]);
            }
            
            return [
                'success' => true,
                'processed_employees' => $processedCount,
                'total_amount' => $totalAmount,
                'message' => "Payroll processed successfully for {$processedCount} employees"
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if payroll is already processed
     */
    private function isPayrollProcessed($month, $year)
    {
        $query = "SELECT COUNT(*) as count FROM payroll_entries 
                  WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?";
        $result = $this->db->fetchOne($query, [$month, $year]);
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Calculate gross salary
     */
    private function calculateGrossSalary($employee)
    {
        $baseSalary = $employee['base_salary'];
        $allowances = json_decode($employee['allowances'] ?? '[]', true);
        
        $totalAllowances = is_array($allowances) ? array_sum($allowances) : 0;
        
        // Add performance bonus if applicable
        $performanceScore = (float)($employee['performance_score'] ?? 0);
        if ($performanceScore >= 90) {
            $totalAllowances += $baseSalary * 0.10; // 10% bonus for excellent performance
        } elseif ($performanceScore >= 80) {
            $totalAllowances += $baseSalary * 0.05; // 5% bonus for good performance
        }
        
        return $baseSalary + $totalAllowances;
    }

    /**
     * Calculate deductions
     */
    private function calculateDeductions($employee, $grossSalary)
    {
        $deductions = json_decode($employee['deductions'] ?? '[]', true);
        $totalDeductions = is_array($deductions) ? array_sum($deductions) : 0;
        
        // Calculate statutory deductions
        // PF (Provident Fund) - 12% of basic salary
        $pf = $employee['base_salary'] * 0.12;
        
        // Professional Tax
        $professionalTax = $grossSalary > 20000 ? 200 : 0;
        
        // TDS (Tax Deducted at Source) - simplified calculation
        $tds = $grossSalary > 50000 ? $grossSalary * 0.10 : 0;
        
        $totalDeductions += $pf + $professionalTax + $tds;
        
        return $totalDeductions;
    }

    /**
     * Schedule performance review
     */
    public function scheduleReview()
    {
        try {
            // Inputs come from POST form (router dispatches with zero args)
            $employeeId = (int)($_POST['employee_id'] ?? 0);
            $reviewData = [
                'reviewer_id' => (int)($_POST['reviewer_id'] ?? ($this->employeeId ?? 1)),
                'review_type' => $_POST['review_type'] ?? 'annual',
                'scheduled_date' => $_POST['scheduled_date'] ?? date('Y-m-d'),
                'next_review_date' => $_POST['next_review_date'] ?? null,
                'overall_rating' => $_POST['overall_rating'] ?? null,
            ];

            // Validate employee
            [$tidSql, $tidParams] = $this->tenantWhere();
            $employeeQuery = "SELECT name, department FROM employees WHERE id = ? AND status = 'active'{$tidSql}";
            $employee = $this->db->fetchOne($employeeQuery, array_merge([$employeeId], $tidParams));
            
            if (!$employee) {
                throw new Exception("Employee not found or not active");
            }
            
            // Insert performance review
            $query = "INSERT INTO performance_reviews (
                        employee_id, reviewer_id, review_type, review_date,
                        next_review_date, overall_rating, status
                    ) VALUES (?, ?, ?, ?, ?, ?, 'draft')";
            
            $this->db->execute($query, [
                $employeeId,
                $reviewData['reviewer_id'],
                $reviewData['review_type'],
                $reviewData['scheduled_date'] ?? date('Y-m-d'),
                $reviewData['next_review_date'] ?? null,
                $reviewData['overall_rating'] ?? null
            ]);
            
            // Notify employee and reviewer
            $this->notifyReviewScheduled($employeeId, $reviewData);
            
            // Log activity
            $this->logHRActivity('review_scheduled', "Performance review scheduled for {$employee['name']}", $employeeId);
            
            return [
                'success' => true,
                'message' => "Performance review scheduled successfully for {$employee['name']}"
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Process job application
     */
    public function processApplication()
    {
        try {
            // Inputs come from POST form (router dispatches with zero args)
            $applicationId = (int)($_POST['application_id'] ?? 0);
            $action = $_POST['action'] ?? '';
            $notes = $_POST['notes'] ?? '';

            // Get application details
            $appQuery = "SELECT ja.*, ja.message AS position_title
                         FROM job_applications ja
                         WHERE ja.id = ?";
            
            $application = $this->db->fetchOne($appQuery, [$applicationId]);
            
            if (!$application) {
                throw new Exception("Application not found");
            }
            
            // job_applications has no status/notes columns - log the decision only
            $this->logHRActivity('application_processed', "Application {$action} for {$application['name']} ({$notes})", $applicationId);
            
            // Log activity
            $this->logHRActivity('application_processed', "Application {$action} for {$application['name']}", $applicationId);
            
            // If hired, create employee record
            if ($action === 'hired') {
                $this->createEmployeeFromApplication($application);
            }
            
            return [
                'success' => true,
                'message' => "Application status updated to {$action}"
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create employee from job application
     */
    private function createEmployeeFromApplication($application)
    {
        // Generate employee code
        $employeeCode = 'EMP' . date('Y') . str_pad((string)mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $tidData = $this->tenantInsertData();
        $tidCol = !empty($tidData) ? ', tenant_id' : '';
        $tidVal = !empty($tidData) ? ', ?' : '';
        $tidParams = !empty($tidData) ? [current($tidData)] : [];
        
        $query = "INSERT INTO employees (
                    employee_code, name, email, phone, designation,
                    joining_date, status, created_at{$tidCol}
                ) VALUES (?, ?, ?, ?, ?, CURDATE(), 'active', NOW(){$tidVal})";
        
        $this->db->execute($query, array_merge([
            $employeeCode,
            $application['name'],
            $application['email'],
            $application['phone'],
            $application['position_title'] ?? 'Staff'
        ], $tidParams));
    }

    /**
     * Notify review scheduled
     */
    private function notifyReviewScheduled($employeeId, $reviewData)
    {
        // Create notification for employee
        $this->createNotification($employeeId, 'performance_review', 
            "Performance review scheduled on {$reviewData['scheduled_date']}");
        
        // Create notification for reviewer
        $this->createNotification($reviewData['reviewer_id'], 'review_assignment', 
            "You have been assigned to conduct a performance review");
    }

    /**
     * Create notification
     */
    private function createNotification($employeeId, $type, $message)
    {
        $query = "INSERT INTO notifications (
                    user_id, type, title, message, is_read, created_at
                ) VALUES (?, ?, ?, ?, 0, NOW())";
        
        $this->db->execute($query, [
            $employeeId,
            $type,
            ucfirst(str_replace('_', ' ', $type)),
            $message
        ]);
    }

    /**
     * Log HR activity
     */
    private function logHRActivity($activityType, $description, $relatedId = null)
    {
        $query = "INSERT INTO employee_activities (
                    employee_id, activity_type, description, metadata, created_at
                ) VALUES (?, ?, ?, ?, NOW())";
        
        $this->db->execute($query, [
            $this->employeeId,
            $activityType,
            $description,
            json_encode(['related_id' => $relatedId])
        ]);
    }

    /**
     * Handle errors
     */
    private function handleError($message)
    {
        error_log("HR Manager Controller Error: " . $message);
        
        $_SESSION['error'] = "Unable to load HR dashboard. Please try again.";
        header('Location: ' . BASE_URL . '/employee/dashboard');
        exit;
    }
}
