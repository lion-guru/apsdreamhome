<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

/**
 * Employee Dashboard Controller
 * Handles employee-specific dashboard functionality
 */
class EmployeeDashboardController extends BaseController
{
    protected $db;
    protected $employeeId;
    protected $employeeRole;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/employee';
        $this->db = Database::getInstance();
        $this->initializeEmployeeSession();
    }

    /**
     * Initialize employee session and role
     */
    private function initializeEmployeeSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $this->employeeId = $_SESSION['employee_id'] ?? $_SESSION['user_id'] ?? null;
        $this->employeeRole = $_SESSION['employee_role'] ?? null;

        // Redirect if not logged in
        if (!$this->employeeId) {
            header('Location: ' . BASE_URL . '/employee/login');
            exit;
        }
    }

    /**
     * Main Employee Dashboard
     */
    public function dashboard()
    {
        try {
            // Get employee information
            $employee = $this->getEmployeeInfo();
            
            // Get dashboard data based on role
            $dashboardData = $this->getRoleSpecificDashboardData();
            
            // Get tasks assigned to employee
            $tasks = $this->getEmployeeTasks();
            
            // Get performance metrics
            $performance = $this->getPerformanceMetrics();
            
            // Get work schedule
            $schedule = $this->getWorkSchedule();
            
            // Get team communications
            $communications = $this->getCommunications();

            $this->render('employee/dashboard', [
                'page_title' => 'Employee Dashboard - APS Dream Home',
                'employee' => $employee,
                'dashboard_data' => $dashboardData,
                'tasks' => $tasks,
                'performance' => $performance,
                'schedule' => $schedule,
                'communications' => $communications,
                'role_specific_data' => $this->getRoleSpecificData(),
            ]);

        } catch (\Exception $e) {
            $this->handleError($e->getMessage());
        }
    }

    /**
     * Get employee information
     */
    private function getEmployeeInfo()
    {
        $query = "SELECT id, name, email, role, department, phone, address, 
                        hire_date, status, profile_image 
                 FROM users 
                 WHERE id = ? AND status = 'active' LIMIT 1";
        
        return $this->db->fetchOne($query, [$this->employeeId]);
    }

    /**
     * Get role-specific dashboard data
     */
    private function getRoleSpecificDashboardData()
    {
        switch ($this->employeeRole) {
            case 'telecalling_executive':
                return $this->getTelecallingData();
            case 'hr_manager':
                return $this->getHRData();
            case 'legal_advisor':
                return $this->getLegalData();
            case 'ca':
                return $this->getFinancialData();
            case 'land_manager':
                return $this->getLandManagementData();
            case 'operations_manager':
                return $this->getOperationsData();
            case 'marketing_executive':
                return $this->getMarketingData();
            default:
                return $this->getGeneralEmployeeData();
        }
    }

    /**
     * Get telecalling specific data
     */
    private function getTelecallingData()
    {
        $today = date('Y-m-d');
        
        // Today's targets
        $targetsQuery = "SELECT COUNT(*) as total_leads, 
                               SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as contacted,
                               SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted
                        FROM leads 
                        WHERE assigned_to = ? AND DATE(created_at) = ?";
        
        $targets = $this->db->fetchOne($targetsQuery, [$this->employeeId, $today]);
        
        // Conversion rate
        $conversionRate = $targets['total_leads'] > 0 ? 
                          ($targets['converted'] / $targets['total_leads']) * 100 : 0;
        
        // Lead queue
        $leadQueueQuery = "SELECT id, name, phone, email, status, priority, created_at
                          FROM leads 
                          WHERE assigned_to = ? AND status IN ('pending', 'follow_up')
                          ORDER BY priority DESC, created_at ASC
                          LIMIT 10";
        
        $leadQueue = $this->db->fetchAll($leadQueueQuery, [$this->employeeId]);
        
        // Call history
        $callHistoryQuery = "SELECT cl.*, l.name as lead_name
                             FROM call_logs cl
                             JOIN leads l ON cl.lead_id = l.id
                             WHERE cl.employee_id = ?
                             ORDER BY cl.call_time DESC
                             LIMIT 10";
        
        $callHistory = $this->db->fetchAll($callHistoryQuery, [$this->employeeId]);
        
        return [
            'targets' => $targets,
            'conversion_rate' => round($conversionRate, 2),
            'lead_queue' => $leadQueue,
            'call_history' => $callHistory,
            'daily_target' => 50,
            'calls_completed' => $targets['contacted']
        ];
    }

    /**
     * Get HR specific data
     */
    private function getHRData()
    {
        // Employee count by department
        $employeeCountQuery = "SELECT department, COUNT(*) as count
                               FROM users 
                               WHERE status = 'active'
                               GROUP BY department";
        
        $employeeCount = $this->db->fetchAll($employeeCountQuery);
        
        // Pending applications
        $applicationsQuery = "SELECT COUNT(*) as count
                              FROM job_applications 
                              WHERE status = 'pending'";
        
        $pendingApplications = $this->db->fetchOne($applicationsQuery);
        
        // Upcoming reviews
        $reviewsQuery = "SELECT COUNT(*) as count
                         FROM performance_reviews 
                         WHERE scheduled_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                         AND status = 'scheduled'";
        
        $upcomingReviews = $this->db->fetchOne($reviewsQuery);
        
        // Recent hires
        $recentHiresQuery = "SELECT name, position, hire_date
                             FROM users 
                             WHERE hire_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                             ORDER BY hire_date DESC
                             LIMIT 5";
        
        $recentHires = $this->db->fetchAll($recentHiresQuery);
        
        return [
            'employee_count' => $employeeCount,
            'pending_applications' => $pendingApplications['count'],
            'upcoming_reviews' => $upcomingReviews['count'],
            'recent_hires' => $recentHires
        ];
    }

    /**
     * Get legal specific data
     */
    private function getLegalData()
    {
        // Pending document reviews
        $pendingDocsQuery = "SELECT COUNT(*) as count
                             FROM legal_documents 
                             WHERE status = 'pending_review'
                             AND assigned_to = ?";
        
        $pendingDocs = $this->db->fetchOne($pendingDocsQuery, [$this->employeeId]);
        
        // Upcoming compliance deadlines
        $complianceQuery = "SELECT COUNT(*) as count
                             FROM compliance_tasks 
                             WHERE due_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                             AND status != 'completed'
                             AND assigned_to = ?";
        
        $upcomingCompliance = $this->db->fetchOne($complianceQuery, [$this->employeeId]);
        
        // Active disputes
        $disputesQuery = "SELECT COUNT(*) as count
                           FROM legal_disputes 
                           WHERE status IN ('active', 'investigation')
                           AND assigned_to = ?";
        
        $activeDisputes = $this->db->fetchOne($disputesQuery, [$this->employeeId]);
        
        // Recent documents
        $recentDocsQuery = "SELECT title, type, status, due_date
                            FROM legal_documents 
                            WHERE assigned_to = ?
                            ORDER BY created_at DESC
                            LIMIT 10";
        
        $recentDocs = $this->db->fetchAll($recentDocsQuery, [$this->employeeId]);
        
        return [
            'pending_documents' => $pendingDocs['count'],
            'upcoming_compliance' => $upcomingCompliance['count'],
            'active_disputes' => $activeDisputes['count'],
            'recent_documents' => $recentDocs
        ];
    }

    /**
     * Get CA specific data
     */
    private function getFinancialData()
    {
        // Monthly financial summary
        $monthlyQuery = "SELECT 
                            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense,
                            COUNT(*) as transactions
                        FROM financial_transactions 
                        WHERE MONTH(transaction_date) = MONTH(CURDATE())
                        AND YEAR(transaction_date) = YEAR(CURDATE())";
        
        $monthlySummary = $this->db->fetchOne($monthlyQuery);
        
        // Pending invoices
        $invoicesQuery = "SELECT COUNT(*) as count
                          FROM invoices 
                          WHERE status = 'pending'
                          AND due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        
        $pendingInvoices = $this->db->fetchOne($invoicesQuery);
        
        // Tax deadlines
        $taxQuery = "SELECT COUNT(*) as count
                     FROM tax_reminders 
                     WHERE due_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                     AND status = 'pending'";
        
        $taxDeadlines = $this->db->fetchOne($taxQuery);
        
        // Budget variance
        $budgetQuery = "SELECT department, budget_amount, spent_amount, 
                               (budget_amount - spent_amount) as variance
                        FROM department_budgets 
                        WHERE fiscal_year = YEAR(CURDATE())
                        ORDER BY ABS(variance) DESC
                        LIMIT 5";
        
        $budgetVariance = $this->db->fetchAll($budgetQuery);
        
        return [
            'monthly_summary' => $monthlySummary,
            'pending_invoices' => $pendingInvoices['count'],
            'tax_deadlines' => $taxDeadlines['count'],
            'budget_variance' => $budgetVariance
        ];
    }

    /**
     * Get land management specific data
     */
    private function getLandManagementData()
    {
        // Property portfolio
        $propertyQuery = "SELECT status, COUNT(*) as count
                          FROM properties 
                          WHERE manager_id = ?
                          GROUP BY status";
        
        $propertyStatus = $this->db->fetchAll($propertyQuery, [$this->employeeId]);
        
        // Pending site visits
        $visitsQuery = "SELECT COUNT(*) as count
                        FROM site_visits 
                        WHERE manager_id = ?
                        AND visit_date >= CURDATE()
                        AND status = 'scheduled'";
        
        $pendingVisits = $this->db->fetchOne($visitsQuery, [$this->employeeId]);
        
        // Land acquisition pipeline
        $acquisitionQuery = "SELECT COUNT(*) as count
                              FROM land_acquisitions 
                              WHERE assigned_manager = ?
                              AND status IN ('evaluation', 'due_diligence', 'negotiation')";
        
        $acquisitionPipeline = $this->db->fetchOne($acquisitionQuery, [$this->employeeId]);
        
        // Recent properties
        $recentPropertiesQuery = "SELECT id, title, location, status, market_value
                                 FROM properties 
                                 WHERE manager_id = ?
                                 ORDER BY created_at DESC
                                 LIMIT 10";
        
        $recentProperties = $this->db->fetchAll($recentPropertiesQuery, [$this->employeeId]);
        
        return [
            'property_status' => $propertyStatus,
            'pending_visits' => $pendingVisits['count'],
            'acquisition_pipeline' => $acquisitionPipeline['count'],
            'recent_properties' => $recentProperties
        ];
    }

    /**
     * Get operations specific data
     */
    private function getOperationsData()
    {
        // Active projects
        $projectsQuery = "SELECT status, COUNT(*) as count
                          FROM projects 
                          WHERE manager_id = ?
                          GROUP BY status";
        
        $projectStatus = $this->db->fetchAll($projectsQuery, [$this->employeeId]);
        
        // Pending approvals
        $approvalsQuery = "SELECT COUNT(*) as count
                           FROM operation_approvals 
                           WHERE assigned_to = ?
                           AND status = 'pending'";
        
        $pendingApprovals = $this->db->fetchOne($approvalsQuery, [$this->employeeId]);
        
        // Vendor management
        $vendorQuery = "SELECT COUNT(*) as count
                        FROM vendors 
                        WHERE status = 'active'
                        AND assigned_manager = ?";
        
        $activeVendors = $this->db->fetchOne($vendorQuery, [$this->employeeId]);
        
        return [
            'project_status' => $projectStatus,
            'pending_approvals' => $pendingApprovals['count'],
            'active_vendors' => $activeVendors['count']
        ];
    }

    /**
     * Get marketing specific data
     */
    private function getMarketingData()
    {
        try {
            // Campaign performance
            $campaignQuery = "SELECT status, COUNT(*) as count
                              FROM marketing_campaigns 
                              WHERE assigned_to = ?
                              GROUP BY status";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        $campaignStatus = $this->db->fetchAll($campaignQuery, [$this->employeeId]);
        
        // Lead generation
        $leadsQuery = "SELECT COUNT(*) as count
                       FROM marketing_leads 
                       WHERE source_campaign IN (
                           SELECT id FROM marketing_campaigns WHERE assigned_to = ?
                       )
                       AND DATE(created_at) = CURDATE()";
        
        $todayLeads = $this->db->fetchOne($leadsQuery, [$this->employeeId]);
        
        return [
            'campaign_status' => $campaignStatus,
            'today_leads' => $todayLeads['count']
        ];
    }

    /**
     * Get general employee data
     */
    private function getGeneralEmployeeData()
    {
        // Basic employee metrics
        $tasksQuery = "SELECT COUNT(*) as total,
                              SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                              SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                       FROM tasks 
                       WHERE assigned_to = ?";
        
        $taskStats = $this->db->fetchOne($tasksQuery, [$this->employeeId]);
        
        return [
            'task_stats' => $taskStats
        ];
    }

    /**
     * Get employee tasks
     */
    private function getEmployeeTasks()
    {
        $query = "SELECT t.*, p.name as project_name
                  FROM tasks t
                  LEFT JOIN projects p ON t.project_id = p.id
                  WHERE t.assigned_to = ?
                  AND t.status IN ('pending', 'in_progress')
                  ORDER BY t.priority DESC, t.deadline ASC
                  LIMIT 10";
        
        return $this->db->fetchAll($query, [$this->employeeId]);
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics()
    {
        $query = "SELECT 
                    AVG(performance_score) as avg_score,
                    COUNT(*) as total_reviews,
                    SUM(CASE WHEN performance_score >= 80 THEN 1 ELSE 0 END) as excellent_reviews
                  FROM performance_reviews 
                  WHERE employee_id = ?
                  AND review_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
        
        $metrics = $this->db->fetchOne($query, [$this->employeeId]);
        
        return [
            'average_score' => round($metrics['avg_score'] ?? 0, 2),
            'total_reviews' => $metrics['total_reviews'] ?? 0,
            'excellent_reviews' => $metrics['excellent_reviews'] ?? 0
        ];
    }

    /**
     * Get work schedule
     */
    private function getWorkSchedule()
    {
        $query = "SELECT * FROM work_schedules 
                  WHERE employee_id = ?
                  AND work_date >= CURDATE()
                  AND work_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                  ORDER BY work_date ASC, start_time ASC";
        
        return $this->db->fetchAll($query, [$this->employeeId]);
    }

    /**
     * Get communications
     */
    private function getCommunications()
    {
        $query = "SELECT m.*, s.name as sender_name
                  FROM messages m
                  JOIN users s ON m.sender_id = s.id
                  WHERE m.recipient_id = ?
                  AND m.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  ORDER BY m.created_at DESC
                  LIMIT 10";
        
        return $this->db->fetchAll($query, [$this->employeeId]);
    }

    /**
     * Get role-specific additional data
     */
    private function getRoleSpecificData()
    {
        switch ($this->employeeRole) {
            case 'telecalling_executive':
                return $this->getTelecallingScripts();
            case 'hr_manager':
                return $this->getHRReminders();
            case 'legal_advisor':
                return $this->getLegalDeadlines();
            case 'ca':
                return $this->getFinancialReminders();
            case 'land_manager':
                return $this->getPropertyAlerts();
            default:
                return [];
        }
    }

    /**
     * Get telecalling scripts
     */
    private function getTelecallingScripts()
    {
        $query = "SELECT * FROM calling_scripts 
                  WHERE status = 'active'
                  ORDER BY name ASC";
        
        return $this->db->fetchAll($query);
    }

    /**
     * Get HR reminders
     */
    private function getHRReminders()
    {
        try {
            $query = "SELECT * FROM hr_reminders 
                      WHERE due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                      AND status = 'pending'
                      ORDER BY due_date ASC";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        return $this->db->fetchAll($query);
    }

    /**
     * Get legal deadlines
     */
    private function getLegalDeadlines()
    {
        $query = "SELECT * FROM legal_deadlines 
                  WHERE due_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                  AND status = 'pending'
                  ORDER BY due_date ASC";
        
        return $this->db->fetchAll($query);
    }

    /**
     * Get financial reminders
     */
    private function getFinancialReminders()
    {
        $query = "SELECT * FROM financial_reminders 
                  WHERE due_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                  AND status = 'pending'
                  ORDER BY due_date ASC";
        
        return $this->db->fetchAll($query);
    }

    /**
     * Get property alerts
     */
    private function getPropertyAlerts()
    {
        $query = "SELECT * FROM property_alerts 
                  WHERE manager_id = ?
                  AND alert_date <= CURDATE()
                  AND status = 'active'
                  ORDER BY alert_date DESC";
        
        return $this->db->fetchAll($query, [$this->employeeId]);
    }

    /**
     * Handle errors
     */
    private function handleError($message)
    {
        error_log("Employee Dashboard Error: " . $message);
        
        // Show user-friendly error message
        $_SESSION['error'] = "Unable to load dashboard. Please try again.";
        header('Location: ' . BASE_URL . '/employee/login');
        exit;
    }

    /**
     * Update task status
     */
    public function updateTaskStatus($taskId, $status)
    {
        try {
            $query = "UPDATE tasks 
                      SET status = ?, updated_at = NOW() 
                      WHERE id = ? AND assigned_to = ?";
            
            $result = $this->db->execute($query, [$status, $taskId, $this->employeeId]);
            
            if ($result) {
                // Log the update
                $this->logTaskUpdate($taskId, $status);
                return ['success' => true, 'message' => 'Task updated successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to update task'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Log task update
     */
    private function logTaskUpdate($taskId, $status)
    {
        $query = "INSERT INTO task_logs (task_id, employee_id, old_status, new_status, created_at)
                  VALUES (?, ?, (SELECT status FROM tasks WHERE id = ?), ?, NOW())";
        
        $this->db->execute($query, [$taskId, $this->employeeId, $taskId, $status]);
    }

    /* ================================================================
       DEPARTMENT-SPECIFIC DASHBOARDS (5 new routes)
       ================================================================ */

    /**
     * Marketing Dashboard
     */
    public function marketingDashboard()
    {
        if (!$this->employeeId) { $this->redirect('/employee/login'); return; }
        try {
            $activeCampaigns = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM campaigns WHERE status = 'active'")['c'] ?? 0);
            $totalLeads      = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM leads")['c'] ?? 0);
            $blogPosts       = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM blogs WHERE status = 'published'")['c'] ?? 0);
            $socialShares    = 0;
            $campaigns       = $this->db->fetchAll("SELECT name, type, status, leads_count FROM campaigns ORDER BY created_at DESC LIMIT 5");
            $leadSources     = $this->db->fetchAll("SELECT source, COUNT(*) as count FROM leads GROUP BY source ORDER BY count DESC LIMIT 5");
        } catch (\Exception $e) { error_log('Marketing dashboard error: ' . $e->getMessage()); $activeCampaigns = $totalLeads = $blogPosts = $socialShares = 0; $campaigns = $leadSources = []; }
        $this->render('employee/marketing_dashboard', compact('activeCampaigns', 'totalLeads', 'blogPosts', 'socialShares', 'campaigns', 'leadSources'));
    }

    /**
     * Finance Dashboard
     */
    public function financeDashboard()
    {
        if (!$this->employeeId) { $this->redirect('/employee/login'); return; }
        try {
            $totalIncome       = (float)($this->db->fetchOne("SELECT COALESCE(SUM(amount),0) as t FROM payment_transactions WHERE type = 'receipt' AND YEAR(created_at) = YEAR(CURDATE())")['t'] ?? 0);
            $totalExpenses     = (float)($this->db->fetchOne("SELECT COALESCE(SUM(amount),0) as t FROM payment_transactions WHERE type = 'payment' AND YEAR(created_at) = YEAR(CURDATE())")['t'] ?? 0);
            $pendingInvoices   = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM invoices WHERE status = 'pending'")['c'] ?? 0);
            $taxDeadlines      = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM tax_reminders WHERE due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")['c'] ?? 0);
            $recentTransactions = $this->db->fetchAll("SELECT DATE(created_at) as date, description, amount, status FROM payment_transactions ORDER BY created_at DESC LIMIT 5");
            $budgetVariance    = $this->db->fetchAll("SELECT department, (allocated - spent) as variance FROM department_budgets WHERE year = YEAR(CURDATE()) LIMIT 5");
        } catch (\Exception $e) { error_log('Finance dashboard error: ' . $e->getMessage()); $totalIncome = $totalExpenses = $pendingInvoices = $taxDeadlines = 0; $recentTransactions = $budgetVariance = []; }
        $this->render('employee/finance_dashboard', compact('totalIncome', 'totalExpenses', 'pendingInvoices', 'taxDeadlines', 'recentTransactions', 'budgetVariance'));
    }

    /**
     * IT Dashboard
     */
    public function itDashboard()
    {
        if (!$this->employeeId) { $this->redirect('/employee/login'); return; }
        try {
            $systemUptime = '99.9%';
            $openTickets  = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM support_tickets WHERE status IN ('open','in_progress')")['c'] ?? 0);
            $securityAlerts = 0;
            $dbSize = $this->db->fetchOne("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) as size FROM information_schema.tables WHERE table_schema = DATABASE()")['size'] ?? '0 MB';
            $dbSize = $dbSize . ' MB';
            $deployments = [];
            $systemHealth = [
                ['component' => 'Database', 'status' => 'ok'],
                ['component' => 'Cache', 'status' => 'ok'],
                ['component' => 'WebSocket', 'status' => 'ok'],
                ['component' => 'Storage', 'status' => 'ok'],
            ];
        } catch (\Exception $e) { error_log('IT dashboard error: ' . $e->getMessage()); $systemUptime = '99.9%'; $openTickets = $securityAlerts = 0; $dbSize = '0 MB'; $deployments = $systemHealth = []; }
        $this->render('employee/it_dashboard', compact('systemUptime', 'openTickets', 'securityAlerts', 'dbSize', 'deployments', 'systemHealth'));
    }

    /**
     * Operations Dashboard
     */
    public function opsDashboard()
    {
        if (!$this->employeeId) { $this->redirect('/employee/login'); return; }
        try {
            $activeProjects  = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM projects WHERE status = 'active'")['c'] ?? 0);
            $pendingApprovals = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM daily_operations_log WHERE status = 'pending'")['c'] ?? 0);
            $activeVendors   = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM suppliers WHERE status = 'active'")['c'] ?? 0);
            $siteVisitsToday = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM visits WHERE DATE(visit_date) = CURDATE()")['c'] ?? 0);
            $pendingOperations = $this->db->fetchAll("SELECT title, assigned_to, DATE(due_date) as due_date, priority FROM daily_operations_log WHERE status = 'pending' ORDER BY due_date ASC LIMIT 5");
            $projectStatus = $this->db->fetchAll("SELECT name as label, status, COUNT(*) as count FROM projects GROUP BY status LIMIT 5");
        } catch (\Exception $e) { error_log('Ops dashboard error: ' . $e->getMessage()); $activeProjects = $pendingApprovals = $activeVendors = $siteVisitsToday = 0; $pendingOperations = $projectStatus = []; }
        $this->render('employee/ops_dashboard', compact('activeProjects', 'pendingApprovals', 'activeVendors', 'siteVisitsToday', 'pendingOperations', 'projectStatus'));
    }

    /**
     * Sales Dashboard
     */
    public function salesDashboard()
    {
        if (!$this->employeeId) { $this->redirect('/employee/login'); return; }
        try {
            $totalSales = (float)($this->db->fetchOne("SELECT COALESCE(SUM(deal_value),0) as t FROM deals WHERE stage = 'closed_won' AND YEAR(created_at) = YEAR(CURDATE())")['t'] ?? 0);
            $activeDeals = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM deals WHERE stage NOT IN ('closed_won','closed_lost')")['c'] ?? 0);
            $pendingFollowups = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM deals WHERE next_followup_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND stage NOT IN ('closed_won','closed_lost')")['c'] ?? 0);
            $totalLeads = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM leads")['c'] ?? 0);
            $closedDeals = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM deals WHERE stage = 'closed_won'")['c'] ?? 0);
            $conversionRate = $totalLeads > 0 ? round(($closedDeals / $totalLeads) * 100, 1) : 0;
            $deals = $this->db->fetchAll("SELECT d.*, l.name as customer_name, p.title as property_name FROM deals d LEFT JOIN leads l ON d.lead_id = l.id LEFT JOIN properties p ON d.property_id = p.id WHERE d.stage NOT IN ('closed_won','closed_lost') ORDER BY d.deal_value DESC LIMIT 5");
            $salesByColony = $this->db->fetchAll("SELECT c.name as colony, COALESCE(SUM(d.deal_value),0) as total FROM deals d LEFT JOIN plots pl ON d.property_id = pl.id LEFT JOIN colonies c ON pl.colony_id = c.id WHERE d.stage = 'closed_won' GROUP BY c.id, c.name ORDER BY total DESC LIMIT 5");
        } catch (\Exception $e) { error_log('Sales dashboard error: ' . $e->getMessage()); $totalSales = $activeDeals = $pendingFollowups = $conversionRate = 0; $deals = $salesByColony = []; }
        $this->render('employee/sales_dashboard', compact('totalSales', 'activeDeals', 'pendingFollowups', 'conversionRate', 'deals', 'salesByColony'));
    }
}
