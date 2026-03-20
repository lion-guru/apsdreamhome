<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

/**
 * HR Manager Controller
 * Handles human resources management, payroll, and employee relations
 */
class HRManagerController extends BaseController
{
    protected $db;
    protected $employeeId;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->initializeEmployeeSession();
    }

    /**
     * Initialize employee session
     */
    private function initializeEmployeeSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->employeeId = $_SESSION['employee_id'] ?? null;

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

        } catch (Exception $e) {
            $this->handleError($e->getMessage());
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
                             SUM(CASE WHEN e.hire_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_hires,
                             AVG(e.performance_score) as avg_performance
                      FROM departments d
                      LEFT JOIN employees e ON d.id = e.department_id
                      GROUP BY d.id, d.name
                      ORDER BY total_employees DESC";
        
        $departmentStats = $this->db->fetchAll($deptQuery);
        
        // Overall employee statistics
        $overallQuery = "SELECT 
                            COUNT(*) as total_employees,
                            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_employees,
                            SUM(CASE WHEN status = 'on_leave' THEN 1 ELSE 0 END) as on_leave,
                            SUM(CASE WHEN status = 'terminated' THEN 1 ELSE 0 END) as terminated,
                            SUM(CASE WHEN hire_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_hires_this_month,
                            SUM(CASE WHEN hire_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) THEN 1 ELSE 0 END) as new_hires_this_quarter,
                            AVG(performance_score) as avg_performance_score
                         FROM employees";
        
        $overallStats = $this->db->fetchOne($overallQuery);
        
        // Employee turnover rate
        $turnoverQuery = "SELECT 
                             (SUM(CASE WHEN status = 'terminated' AND termination_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) THEN 1 ELSE 0 END) / 
                              SUM(CASE WHEN hire_date <= DATE_SUB(CURDATE(), INTERVAL 90 DAY) THEN 1 ELSE 0 END)) * 100 as turnover_rate
                          FROM employees";
        
        $turnoverRate = $this->db->fetchOne($turnoverQuery);
        
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
        $query = "SELECT ja.*, p.title as position_title, p.department
                 FROM job_applications ja
                 JOIN positions p ON ja.position_id = p.id
                 WHERE ja.status = 'pending'
                 ORDER BY ja.applied_at DESC
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
                 WHERE pr.scheduled_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                 AND pr.status = 'scheduled'
                 ORDER BY pr.scheduled_date ASC
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
                                 SUM(CASE WHEN status = 'processed' THEN 1 ELSE 0 END) as processed,
                                 SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                                 SUM(net_salary) as total_payroll_amount
                              FROM payroll 
                              WHERE month = MONTH(CURDATE()) 
                              AND year = YEAR(CURDATE())";
        
        $currentMonth = $this->db->fetchOne($currentMonthQuery);
        
        // Last month payroll for comparison
        $lastMonthQuery = "SELECT 
                              SUM(net_salary) as total_amount,
                              COUNT(*) as processed_count
                           FROM payroll 
                           WHERE month = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                           AND year = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                           AND status = 'processed'";
        
        $lastMonth = $this->db->fetchOne($lastMonthQuery);
        
        // Payroll deadlines
        $deadlineQuery = "SELECT * FROM payroll_deadlines 
                          WHERE month = MONTH(CURDATE()) 
                          AND year = YEAR(CURDATE())";
        
        $deadlines = $this->db->fetchOne($deadlineQuery);
        
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
        // Open positions
        $openPositionsQuery = "SELECT COUNT(*) as count, 
                                      SUM(CASE WHEN urgency = 'high' THEN 1 ELSE 0 END) as urgent
                               FROM positions 
                               WHERE status = 'open'";
        
        $openPositions = $this->db->fetchOne($openPositionsQuery);
        
        // Recruitment pipeline
        $pipelineQuery = "SELECT 
                             SUM(CASE WHEN status = 'applied' THEN 1 ELSE 0 END) as applied,
                             SUM(CASE WHEN status = 'screening' THEN 1 ELSE 0 END) as screening,
                             SUM(CASE WHEN status = 'interview' THEN 1 ELSE 0 END) as interview,
                             SUM(CASE WHEN status = 'offer' THEN 1 ELSE 0 END) as offer,
                             SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                          FROM job_applications 
                          WHERE applied_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        
        $pipeline = $this->db->fetchOne($pipelineQuery);
        
        // Time to hire metrics
        $timeToHireQuery = "SELECT 
                               AVG(DATEDIFF(hire_date, applied_at)) as avg_time_to_hire,
                               MIN(DATEDIFF(hire_date, applied_at)) as min_time_to_hire,
                               MAX(DATEDIFF(hire_date, applied_at)) as max_time_to_hire
                            FROM job_applications 
                            WHERE status = 'hired' 
                            AND hire_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)";
        
        $timeToHire = $this->db->fetchOne($timeToHireQuery);
        
        // Source effectiveness
        $sourceQuery = "SELECT source, 
                               COUNT(*) as applications,
                               SUM(CASE WHEN status = 'hired' THEN 1 ELSE 0 END) as hires,
                               (SUM(CASE WHEN status = 'hired' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as conversion_rate
                        FROM job_applications 
                        WHERE applied_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                        GROUP BY source
                        ORDER BY hires DESC
                        LIMIT 5";
        
        $sourceEffectiveness = $this->db->fetchAll($sourceQuery);
        
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
        $query = "SELECT * FROM hr_activities 
                  WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  ORDER BY created_at DESC
                  LIMIT 10";
        
        return $this->db->fetchAll($query);
    }

    /**
     * Process payroll for employees
     */
    public function processPayroll($month, $year)
    {
        try {
            // Validate payroll period
            if ($this->isPayrollProcessed($month, $year)) {
                throw new Exception("Payroll for {$month}-{$year} has already been processed");
            }
            
            // Get all active employees
            $employeesQuery = "SELECT e.*, d.name as department_name,
                                      p.base_salary, p.allowances, p.deductions
                               FROM employees e
                               JOIN departments d ON e.department_id = d.id
                               JOIN payroll_settings p ON e.id = p.employee_id
                               WHERE e.status = 'active'";
            
            $employees = $this->db->fetchAll($employeesQuery);
            
            $processedCount = 0;
            $totalAmount = 0;
            
            foreach ($employees as $employee) {
                // Calculate salary components
                $grossSalary = $this->calculateGrossSalary($employee);
                $deductions = $this->calculateDeductions($employee, $grossSalary);
                $netSalary = $grossSalary - $deductions;
                
                // Insert payroll record
                $payrollQuery = "INSERT INTO payroll (
                                    employee_id, month, year, base_salary, 
                                    allowances, deductions, gross_salary, 
                                    net_salary, status, processed_at, processed_by
                                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'processed', NOW(), ?)";
                
                $this->db->execute($payrollQuery, [
                    $employee['id'],
                    $month,
                    $year,
                    $employee['base_salary'],
                    json_encode($employee['allowances'] ?? []),
                    $deductions,
                    $grossSalary,
                    $netSalary,
                    $this->employeeId
                ]);
                
                $totalAmount += $netSalary;
                $processedCount++;
                
                // Log payroll activity
                $this->logHRActivity('payroll_processed', "Processed payroll for {$employee['name']}", $employee['id']);
            }
            
            return [
                'success' => true,
                'processed_employees' => $processedCount,
                'total_amount' => $totalAmount,
                'message' => "Payroll processed successfully for {$processedCount} employees"
            ];
            
        } catch (Exception $e) {
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
        $query = "SELECT COUNT(*) as count FROM payroll WHERE month = ? AND year = ? AND status = 'processed'";
        $result = $this->db->fetchOne($query, [$month, $year]);
        return $result['count'] > 0;
    }

    /**
     * Calculate gross salary
     */
    private function calculateGrossSalary($employee)
    {
        $baseSalary = $employee['base_salary'];
        $allowances = json_decode($employee['allowances'] ?? '[]', true);
        
        $totalAllowances = array_sum($allowances);
        
        // Add performance bonus if applicable
        if ($employee['performance_score'] >= 90) {
            $totalAllowances += $baseSalary * 0.10; // 10% bonus for excellent performance
        } elseif ($employee['performance_score'] >= 80) {
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
        $totalDeductions = array_sum($deductions);
        
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
    public function scheduleReview($employeeId, $reviewData)
    {
        try {
            // Validate employee
            $employeeQuery = "SELECT name, department FROM employees WHERE id = ? AND status = 'active'";
            $employee = $this->db->fetchOne($employeeQuery, [$employeeId]);
            
            if (!$employee) {
                throw new Exception("Employee not found or not active");
            }
            
            // Insert performance review
            $query = "INSERT INTO performance_reviews (
                        employee_id, reviewer_id, review_type, scheduled_date,
                        goals, created_by, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $this->db->execute($query, [
                $employeeId,
                $reviewData['reviewer_id'],
                $reviewData['review_type'],
                $reviewData['scheduled_date'],
                json_encode($reviewData['goals'] ?? []),
                $this->employeeId
            ]);
            
            // Notify employee and reviewer
            $this->notifyReviewScheduled($employeeId, $reviewData);
            
            // Log activity
            $this->logHRActivity('review_scheduled', "Performance review scheduled for {$employee['name']}", $employeeId);
            
            return [
                'success' => true,
                'message' => "Performance review scheduled successfully for {$employee['name']}"
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Process job application
     */
    public function processApplication($applicationId, $action, $notes = '')
    {
        try {
            // Get application details
            $appQuery = "SELECT ja.*, p.title as position_title
                         FROM job_applications ja
                         JOIN positions p ON ja.position_id = p.id
                         WHERE ja.id = ?";
            
            $application = $this->db->fetchOne($appQuery, [$applicationId]);
            
            if (!$application) {
                throw new Exception("Application not found");
            }
            
            // Update application status
            $query = "UPDATE job_applications 
                      SET status = ?, notes = ?, processed_by = ?, processed_at = NOW()
                      WHERE id = ?";
            
            $this->db->execute($query, [$action, $notes, $this->employeeId, $applicationId]);
            
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
            
        } catch (Exception $e) {
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
        // Generate employee ID
        $employeeId = 'EMP' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $query = "INSERT INTO employees (
                    employee_id, name, email, phone, position_id, department_id,
                    hire_date, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, CURDATE(), 'active', NOW())";
        
        $this->db->execute($query, [
            $employeeId,
            $application['name'],
            $application['email'],
            $application['phone'],
            $application['position_id'],
            $application['department_id'] ?? null
        ]);
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
                    employee_id, type, message, created_at, status
                ) VALUES (?, ?, ?, NOW(), 'unread')";
        
        $this->db->execute($query, [$employeeId, $type, $message]);
    }

    /**
     * Log HR activity
     */
    private function logHRActivity($activityType, $description, $relatedId = null)
    {
        $query = "INSERT INTO hr_activities (
                    activity_type, description, related_id, 
                    performed_by, created_at
                ) VALUES (?, ?, ?, ?, NOW())";
        
        $this->db->execute($query, [$activityType, $description, $relatedId, $this->employeeId]);
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
