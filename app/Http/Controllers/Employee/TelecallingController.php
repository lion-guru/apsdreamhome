<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Admin\AdminController;
use App\Core\Database\Database;
use App\Traits\TenantAwareTrait;
use Exception;

/**
 * Telecalling Controller
 * Handles lead management, calling system, and conversion tracking
 */
class TelecallingController extends AdminController
{
    use TenantAwareTrait;

    protected $db;
    protected $employeeId;

    public function __construct()
    {
        parent::__construct();
        // Use admin layout when accessed via /admin/telecalling/ routes
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($requestUri, '/admin/telecalling') !== false) {
            $this->layout = 'layouts/admin';
        } else {
            $this->layout = 'layouts/employee';
        }
        $this->db = Database::getInstance();
        $this->initializeEmployeeSession();
    }

    /**
     * Initialize employee session (also allows admin access)
     */
    private function initializeEmployeeSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $this->employeeId = $_SESSION['employee_id'] ?? null;

        // Detect admin/super-admin/manager session
        $hasAdminSession = isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
        $hasAdminRole = isset($_SESSION['admin_role']) && in_array($_SESSION['admin_role'], ['admin', 'super_admin', 'manager']);
        $hasGenericRole = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin', 'manager', 'employee', 'telecaller']);

        $isAdmin = $hasAdminSession || $hasAdminRole || $hasGenericRole;

        // If admin is accessing without employee_id, use their admin_id so DB queries still work
        if (!$this->employeeId && $hasAdminSession) {
            $this->employeeId = (int)$_SESSION['admin_id'];
        }

        if (!$this->employeeId && !$isAdmin) {
            header('Location: ' . BASE_URL . '/employee/login');
            exit;
        }
    }

    /**
     * Telecalling dashboard
     */
    public function dashboard()
    {
        try {
            // Get today's targets and performance
            $todayStats = $this->getTodayStats();
            
            // Get lead queue
            $leadQueue = $this->getLeadQueue();
            
            // Get call history
            $callHistory = $this->getCallHistory();
            
            // Get performance metrics
            $performance = $this->getPerformanceMetrics();
            
            // Get calling scripts
            $scripts = $this->getCallingScripts();
            
            // Get follow-up schedule
            $followUps = $this->getFollowUpSchedule();

            $this->render('employee/telecalling_dashboard', [
                'page_title' => 'Telecalling Dashboard - APS Dream Home',
                'today_stats' => $todayStats,
                'lead_queue' => $leadQueue,
                'call_history' => $callHistory,
                'performance' => $performance,
                'scripts' => $scripts,
                'follow_ups' => $followUps
            ]);

        } catch (\Exception $e) {
            $this->handleError($e->getMessage());
        }
    }

    /**
     * Get today's statistics
     */
    private function getTodayStats()
    {
        $today = date('Y-m-d');
        
        // Today's targets
        $targetsQuery = "SELECT 
                            COUNT(*) as total_leads,
                            SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as contacted,
                            SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted,
                            SUM(CASE WHEN status = 'not_interested' THEN 1 ELSE 0 END) as not_interested,
                            SUM(CASE WHEN status = 'follow_up' THEN 1 ELSE 0 END) as follow_up_required
                        FROM leads 
                        WHERE assigned_to = ? AND DATE(created_at) = ?";
        
        $targets = $this->db->fetchOne($targetsQuery, [$this->employeeId, $today]);
        
        // Today's call logs
        $callsQuery = "SELECT COUNT(*) as total_calls,
                              SUM(CASE WHEN duration > 0 THEN 1 ELSE 0 END) as connected_calls,
                              AVG(duration) as avg_duration
                        FROM call_logs 
                        WHERE agent_id = ? AND DATE(call_time) = ?";
        
        $calls = $this->db->fetchOne($callsQuery, [$this->employeeId, $today]);
        
        // Calculate conversion rate
        $conversionRate = $targets['total_leads'] > 0 ? 
                          ($targets['converted'] / $targets['total_leads']) * 100 : 0;
        
        // Calculate connection rate
        $connectionRate = $calls['total_calls'] > 0 ? 
                           ($calls['connected_calls'] / $calls['total_calls']) * 100 : 0;
        
        return [
            'total_leads' => $targets['total_leads'],
            'contacted' => $targets['contacted'],
            'converted' => $targets['converted'],
            'not_interested' => $targets['not_interested'],
            'follow_up_required' => $targets['follow_up_required'],
            'total_calls' => $calls['total_calls'],
            'connected_calls' => $calls['connected_calls'],
            'avg_duration' => round($calls['avg_duration'] ?? 0, 2),
            'conversion_rate' => round($conversionRate, 2),
            'connection_rate' => round($connectionRate, 2),
            'daily_target' => 50,
            'target_achievement' => round(($calls['total_calls'] / 50) * 100, 2)
        ];
    }

    /**
     * Get lead queue for telecalling
     */
    private function getLeadQueue()
    {
        try {
            $query = "SELECT l.*, 
                            c.name as campaign_name
                     FROM leads l
                     LEFT JOIN marketing_campaigns c ON l.campaign_id = c.id
                     WHERE l.assigned_to = ?
                     AND l.status IN ('pending', 'follow_up')
                     ORDER BY l.priority DESC, l.created_at ASC
                     LIMIT 20";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        return $this->db->fetchAll($query, [$this->employeeId]);
    }

    /**
     * Get call history
     */
    private function getCallHistory()
    {
        $query = "SELECT cl.*, l.name as lead_name, l.phone as lead_phone
                 FROM call_logs cl
                 JOIN leads l ON cl.lead_id = l.id
                 WHERE cl.agent_id = ?
                 ORDER BY cl.call_time DESC
                 LIMIT 15";
        
        return $this->db->fetchAll($query, [$this->employeeId]);
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics()
    {
        // Last 7 days performance
        $weeklyQuery = "SELECT 
                          DATE(cl.call_time) as call_date,
                          COUNT(*) as total_calls,
                          SUM(CASE WHEN l.status = 'converted' THEN 1 ELSE 0 END) as conversions
                       FROM call_logs cl
                        JOIN leads l ON cl.lead_id = l.id
                       WHERE cl.agent_id = ?
                       AND DATE(cl.call_time) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                       GROUP BY DATE(cl.call_time)
                       ORDER BY call_date DESC";
        
        $weeklyStats = $this->db->fetchAll($weeklyQuery, [$this->employeeId]);
        
        // Monthly performance
        $monthlyQuery = "SELECT 
                           COUNT(*) as total_calls,
                           SUM(CASE WHEN l.status = 'converted' THEN 1 ELSE 0 END) as conversions,
                           AVG(cl.duration) as avg_duration,
                           COUNT(DISTINCT cl.lead_id) as unique_leads
                        FROM call_logs cl
                        JOIN leads l ON cl.lead_id = l.id
                        WHERE cl.agent_id = ?
                        AND MONTH(cl.call_time) = MONTH(CURDATE())
                        AND YEAR(cl.call_time) = YEAR(CURDATE())";
        
        $monthlyStats = $this->db->fetchOne($monthlyQuery, [$this->employeeId]);
        
        // Performance ranking
        $rankingQuery = "SELECT 
                           COUNT(*) as total_calls,
                           SUM(CASE WHEN l.status = 'converted' THEN 1 ELSE 0 END) as conversions,
                           (SUM(CASE WHEN l.status = 'converted' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as conversion_rate
                        FROM call_logs cl
                        JOIN leads l ON cl.lead_id = l.id
                        WHERE cl.agent_id = ?
                        AND MONTH(cl.call_time) = MONTH(CURDATE())
                        AND YEAR(cl.call_time) = YEAR(CURDATE())";
        
        $myStats = $this->db->fetchOne($rankingQuery, [$this->employeeId]);
        
        // Get team ranking
        $teamRankingQuery = "SELECT e.name, 
                                   COUNT(cl.id) as calls,
                                   SUM(CASE WHEN l.status = 'converted' THEN 1 ELSE 0 END) as conversions,
                                   (SUM(CASE WHEN l.status = 'converted' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as conversion_rate
                            FROM users e
                            JOIN call_logs cl ON e.id = cl.agent_id
                            JOIN leads l ON cl.lead_id = l.id
                            WHERE e.role IN ('telecaller', 'telecalling_executive')
                            AND MONTH(cl.call_time) = MONTH(CURDATE())
                            AND YEAR(cl.call_time) = YEAR(CURDATE())
                            GROUP BY e.id, e.name
                            ORDER BY conversions DESC
                            LIMIT 10";
        
        $teamRanking = $this->db->fetchAll($teamRankingQuery);
        
        return [
            'weekly_stats' => $weeklyStats,
            'monthly_stats' => $monthlyStats,
            'my_stats' => $myStats,
            'team_ranking' => $teamRanking
        ];
    }

    /**
     * Get calling scripts
     */
    private function getCallingScripts()
    {
        $query = "SELECT * FROM ai_calling_scripts 
                  WHERE is_active = 1
                  ORDER BY category ASC, script_name ASC";
        
        return $this->db->fetchAll($query);
    }

    /**
     * Get follow-up schedule
     */
    private function getFollowUpSchedule()
    {
        $query = "SELECT l.*, t.due_date as next_follow_up, t.title as follow_up_title
                 FROM leads l
                 JOIN crm_tasks t ON l.id = t.lead_id
                 WHERE l.assigned_to = ?
                 AND t.task_type = 'follow_up_call'
                 AND t.status IN ('pending', 'in_progress')
                 AND t.due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                 ORDER BY t.due_date ASC
                 LIMIT 20";
        
        return $this->db->fetchAll($query, [$this->employeeId]);
    }

    /**
     * Log a call
     */
    public function logCall()
    {
        try {
            // Inputs come from POST form (router dispatches with zero args)
            $leadId = (int)($_POST['lead_id'] ?? 0);
            $callData = [
                'duration' => (int)($_POST['duration'] ?? 0),
                'call_status' => $_POST['call_status'] ?? 'connected',
                'outcome' => $_POST['outcome'] ?? '',
                'notes' => $_POST['notes'] ?? '',
                'next_follow_up' => $_POST['next_follow_up'] ?? null,
            ];

            // Validate lead assignment
            $leadQuery = "SELECT id, name, status FROM leads WHERE id = ? AND assigned_to = ?";
            $lead = $this->db->fetchOne($leadQuery, [$leadId, $this->employeeId]);
            
            if (!$lead) {
                throw new Exception("Lead not found or not assigned to you");
            }
            
            // Insert call log
            $tidData = $this->tenantInsertData();
            $tidCol = !empty($tidData) ? ', tenant_id' : '';
            $tidVal = !empty($tidData) ? ', ?' : '';
            
            $noteParts = [];
            if (!empty($callData['outcome'])) {
                $noteParts[] = 'Outcome: ' . $callData['outcome'];
            }
            if (!empty($callData['notes'])) {
                $noteParts[] = $callData['notes'];
            }
            $notes = implode(' | ', $noteParts);
            
            $query = "INSERT INTO call_logs (
                        lead_id, agent_id, call_type, duration,
                        status, notes, call_time{$tidCol}
                    ) VALUES (?, ?, 'outbound', ?, ?, ?, NOW(){$tidVal})";
            
            $params = [
                $leadId,
                $this->employeeId,
                $callData['duration'] ?? 0,
                $callData['call_status'] ?? 'connected',
                $notes
            ];
            
            if (!empty($tidData)) {
                $params[] = current($tidData);
            }
            
            $this->db->execute($query, $params);
            
            // Update lead status based on outcome
            $this->updateLeadStatus($leadId, $callData['outcome'] ?? 'no_answer');
            
            // Schedule follow-up if needed
            if (!empty($callData['next_follow_up'])) {
                $this->scheduleFollowUp($leadId, $callData['next_follow_up']);
            }
            
            return [
                'success' => true,
                'message' => 'Call logged successfully',
                'lead_name' => $lead['name']
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update lead status
     */
    private function updateLeadStatus($leadId, $outcome)
    {
        $statusMap = [
            'interested' => 'follow_up',
            'not_interested' => 'not_interested',
            'converted' => 'converted',
            'follow_up' => 'follow_up',
            'no_answer' => 'pending'
        ];
        
        $newStatus = $statusMap[$outcome] ?? 'pending';
        
        $query = "UPDATE leads 
                  SET status = ?, last_activity_at = NOW(), updated_at = NOW()
                  WHERE id = ?";
        
        $this->db->execute($query, [$newStatus, $leadId]);
    }

    /**
     * Schedule follow-up
     */
    private function scheduleFollowUp($leadId, $followUpDate)
    {
        // Cancel any pending follow-up tasks for this lead first
        try {
            $cancelQuery = "UPDATE crm_tasks 
                            SET status = 'cancelled', updated_at = NOW()
                            WHERE lead_id = ? AND task_type = 'follow_up_call' 
                            AND status IN ('pending', 'in_progress')";
            $this->db->execute($cancelQuery, [$leadId]);
        } catch (\Throwable $e) {
            error_log("Telecalling scheduleFollowUp cancel: " . $e->getMessage());
        }
        
        try {
            $tidData = $this->tenantInsertData();
            $tidCol = !empty($tidData) ? ', tenant_id' : '';
            $tidVal = !empty($tidData) ? ', ?' : '';
            
            $query = "INSERT INTO crm_tasks (
                        lead_id, assigned_to, task_type, title, due_date, status, created_at{$tidCol}
                    ) VALUES (?, ?, 'follow_up_call', 'Telecalling follow-up', ?, 'pending', NOW(){$tidVal})";
            
            $params = [$leadId, $this->employeeId, $followUpDate];
            if (!empty($tidData)) {
                $params[] = current($tidData);
            }
            
            $this->db->execute($query, $params);
        } catch (\Throwable $e) {
            error_log("Telecalling scheduleFollowUp: " . $e->getMessage());
        }
    }

    /**
     * Get lead details for calling
     */
    public function getLeadDetails($leadId)
    {
        try {
            $query = "SELECT l.*, 
                        c.name as campaign_name,
                        cl.last_call_time,
                        cl.call_count
                    FROM leads l
                    LEFT JOIN marketing_campaigns c ON l.campaign_id = c.id
                    LEFT JOIN (
                        SELECT lead_id, 
                               MAX(call_time) as last_call_time,
                               COUNT(*) as call_count
                        FROM call_logs 
                        GROUP BY lead_id
                    ) cl ON l.id = cl.lead_id
                    WHERE l.id = ? AND l.assigned_to = ?";
            
            $lead = $this->db->fetchOne($query, [$leadId, $this->employeeId]);
            
            if (!$lead) {
                throw new Exception("Lead not found or not assigned to you");
            }
            
            // Get call history for this lead
            $historyQuery = "SELECT * FROM call_logs 
                             WHERE lead_id = ? AND agent_id = ?
                             ORDER BY call_time DESC";
            
            $callHistory = $this->db->fetchAll($historyQuery, [$leadId, $this->employeeId]);
            
            return [
                'success' => true,
                'lead' => $lead,
                'call_history' => $callHistory
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get recommended calling script
     */
    public function getRecommendedScript($leadId)
    {
        try {
            // Get lead details
            $leadQuery = "SELECT l.lead_category, l.source, l.property_interest, l.budget_range
                         FROM leads l
                         WHERE l.id = ? AND l.assigned_to = ?";
            
            $lead = $this->db->fetchOne($leadQuery, [$leadId, $this->employeeId]);
            
            if (!$lead) {
                throw new Exception("Lead not found");
            }
            
            // Determine script category based on lead category (valid enum values only)
            $category = 'general';
            
            if ($lead['lead_category'] === 'hot_lead') {
                $category = 'sales';
            } elseif ($lead['lead_category'] === 'cold_lead') {
                $category = 'cold_call';
            }
            
            // Get script for category
            $scriptQuery = "SELECT * FROM ai_calling_scripts 
                            WHERE category = ? AND is_active = 1
                            ORDER BY conversion_rate DESC, total_calls_made ASC
                            LIMIT 1";
            
            $script = $this->db->fetchOne($scriptQuery, [$category]);
            
            // If no specific script found, get general script
            if (!$script) {
                $scriptQuery = "SELECT * FROM ai_calling_scripts 
                                WHERE category = 'general' AND is_active = 1
                                ORDER BY conversion_rate DESC, total_calls_made ASC
                                LIMIT 1";
                
                $script = $this->db->fetchOne($scriptQuery);
            }
            
            // Update script usage count
            if ($script) {
                $updateQuery = "UPDATE ai_calling_scripts 
                                SET total_calls_made = total_calls_made + 1 
                                WHERE id = ?";
                $this->db->execute($updateQuery, [$script['id']]);
            }
            
            return [
                'success' => true,
                'script' => $script,
                'category' => $category
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get today's follow-ups
     */
    public function getTodayFollowUps()
    {
        try {
            $query = "SELECT l.*, t.due_date as scheduled_date, t.status as follow_up_status
                     FROM leads l
                     JOIN crm_tasks t ON l.id = t.lead_id
                     WHERE t.assigned_to = ?
                     AND t.task_type = 'follow_up_call'
                     AND DATE(t.due_date) = CURDATE()
                     AND t.status IN ('pending', 'in_progress')
                     ORDER BY t.due_date ASC";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        return $this->db->fetchAll($query, [$this->employeeId]);
    }

    /**
     * Complete follow-up
     */
    public function completeFollowUp()
    {
        try {
            // Inputs come from POST form (router dispatches with zero args)
            $leadId = (int)($_POST['lead_id'] ?? 0);
            $result = $_POST['result'] ?? 'interested';

            // Mark pending follow-up tasks for this lead as completed
            $query = "UPDATE crm_tasks 
                      SET status = 'completed', completed_at = NOW(), completed_notes = ?
                      WHERE lead_id = ? AND assigned_to = ? 
                      AND task_type = 'follow_up_call' 
                      AND status IN ('pending', 'in_progress')";
            
            $this->db->execute($query, [$result, $leadId, $this->employeeId]);
            
            // Update lead status based on result
            $statusMap = [
                'interested' => 'follow_up',
                'converted' => 'converted',
                'not_interested' => 'not_interested'
            ];
            
            $newStatus = $statusMap[$result] ?? 'follow_up';
            
            $updateQuery = "UPDATE leads 
                            SET status = ?, updated_at = NOW()
                            WHERE id = ?";
            
            $this->db->execute($updateQuery, [$newStatus, $leadId]);
            
            return [
                'success' => true,
                'message' => 'Follow-up completed successfully'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Assign leads to telecallers
     */
    public function assign()
    {
        $leads = [];
        $telecallers = [];
        try {
            $leads = $this->db->fetchAll("SELECT id, name, phone, source, status, created_at FROM leads ORDER BY created_at DESC LIMIT 50");
            [$tidSql, $tidParams] = $this->tenantWhere();
            $telecallers = $this->db->fetchAll("SELECT u.id, u.name FROM users u WHERE u.role = 'employee'{$tidSql} ORDER BY u.name", $tidParams);
        } catch (\Exception $e) {
            error_log("Telecalling assign error: " . $e->getMessage());
        }
        $this->render('employee/telecalling_assign', [
            'page_title' => 'Assign Leads - Telecalling',
            'leads' => $leads,
            'telecallers' => $telecallers,
        ]);
    }

    /**
     * Telecalling commissions page
     */
    public function commissions()
    {
        $this->render('employee/telecalling_dashboard', [
            'page_title' => 'Telecalling Commissions',
            'today_stats' => [],
            'lead_queue' => [],
            'call_history' => [],
            'performance' => [],
            'scripts' => [],
            'follow_ups' => [],
        ]);
    }

    /**
     * Telecalling approvals page
     */
    public function approvals()
    {
        $pendingApprovals = [];
        try {
            $pendingApprovals = $this->db->fetchAll("SELECT l.id, l.name, l.phone, l.status, l.created_at, 
                (SELECT COUNT(*) FROM leads WHERE id = l.id AND status = 'converted') as deals 
                FROM leads l WHERE l.status = 'pending_approval' ORDER BY l.created_at DESC LIMIT 20");
        } catch (\Exception $e) {
            error_log("Telecalling approvals error: " . $e->getMessage());
        }
        $this->render('employee/telecalling_approvals', [
            'page_title' => 'Lead Approvals - Telecalling',
            'pending_approvals' => $pendingApprovals,
        ]);
    }

    /**
     * Handle errors
     */
    private function handleError($message)
    {
        error_log("Telecalling Controller Error: " . $message);
        
        $_SESSION['error'] = "Unable to load telecalling dashboard. Please try again.";
        header('Location: ' . BASE_URL . '/employee/dashboard');
        exit;
    }
}
