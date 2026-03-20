<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

/**
 * Telecalling Controller
 * Handles lead management, calling system, and conversion tracking
 */
class TelecallingController extends BaseController
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

        } catch (Exception $e) {
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
                              SUM(CASE WHEN call_duration > 0 THEN 1 ELSE 0 END) as connected_calls,
                              AVG(call_duration) as avg_duration
                        FROM call_logs 
                        WHERE employee_id = ? AND DATE(call_time) = ?";
        
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
        $query = "SELECT l.*, 
                        p.title as property_title,
                        p.location as property_location,
                        c.name as campaign_name
                 FROM leads l
                 LEFT JOIN properties p ON l.interested_property_id = p.id
                 LEFT JOIN marketing_campaigns c ON l.source_campaign_id = c.id
                 WHERE l.assigned_to = ?
                 AND l.status IN ('pending', 'follow_up')
                 ORDER BY l.priority DESC, l.created_at ASC
                 LIMIT 20";
        
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
                 WHERE cl.employee_id = ?
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
                       WHERE cl.employee_id = ?
                       AND DATE(cl.call_time) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                       GROUP BY DATE(cl.call_time)
                       ORDER BY call_date DESC";
        
        $weeklyStats = $this->db->fetchAll($weeklyQuery, [$this->employeeId]);
        
        // Monthly performance
        $monthlyQuery = "SELECT 
                           COUNT(*) as total_calls,
                           SUM(CASE WHEN l.status = 'converted' THEN 1 ELSE 0 END) as conversions,
                           AVG(cl.call_duration) as avg_duration,
                           COUNT(DISTINCT cl.lead_id) as unique_leads
                        FROM call_logs cl
                        JOIN leads l ON cl.lead_id = l.id
                        WHERE cl.employee_id = ?
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
                        WHERE cl.employee_id = ?
                        AND MONTH(cl.call_time) = MONTH(CURDATE())
                        AND YEAR(cl.call_time) = YEAR(CURDATE())";
        
        $myStats = $this->db->fetchOne($rankingQuery, [$this->employeeId]);
        
        // Get team ranking
        $teamRankingQuery = "SELECT e.name, 
                                   COUNT(cl.id) as calls,
                                   SUM(CASE WHEN l.status = 'converted' THEN 1 ELSE 0 END) as conversions,
                                   (SUM(CASE WHEN l.status = 'converted' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as conversion_rate
                            FROM employees e
                            JOIN call_logs cl ON e.id = cl.employee_id
                            JOIN leads l ON cl.lead_id = l.id
                            WHERE e.role = 'telecalling_executive'
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
        $query = "SELECT * FROM calling_scripts 
                  WHERE status = 'active'
                  ORDER BY category ASC, name ASC";
        
        return $this->db->fetchAll($query);
    }

    /**
     * Get follow-up schedule
     */
    private function getFollowUpSchedule()
    {
        $query = "SELECT l.*, cl.call_time, cl.next_follow_up
                 FROM leads l
                 JOIN call_logs cl ON l.id = cl.lead_id
                 WHERE l.assigned_to = ?
                 AND l.status = 'follow_up'
                 AND cl.next_follow_up IS NOT NULL
                 AND cl.next_follow_up <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                 ORDER BY cl.next_follow_up ASC
                 LIMIT 20";
        
        return $this->db->fetchAll($query, [$this->employeeId]);
    }

    /**
     * Log a call
     */
    public function logCall($leadId, $callData)
    {
        try {
            // Validate lead assignment
            $leadQuery = "SELECT id, name, status FROM leads WHERE id = ? AND assigned_to = ?";
            $lead = $this->db->fetchOne($leadQuery, [$leadId, $this->employeeId]);
            
            if (!$lead) {
                throw new Exception("Lead not found or not assigned to you");
            }
            
            // Insert call log
            $query = "INSERT INTO call_logs (
                        lead_id, employee_id, call_time, call_duration,
                        call_status, outcome, notes, next_follow_up
                    ) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)";
            
            $params = [
                $leadId,
                $this->employeeId,
                $callData['duration'] ?? 0,
                $callData['call_status'] ?? 'connected',
                $callData['outcome'] ?? 'no_answer',
                $callData['notes'] ?? '',
                $callData['next_follow_up'] ?? null
            ];
            
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
            
        } catch (Exception $e) {
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
                  SET status = ?, last_contacted = NOW(), updated_at = NOW()
                  WHERE id = ?";
        
        $this->db->execute($query, [$newStatus, $leadId]);
    }

    /**
     * Schedule follow-up
     */
    private function scheduleFollowUp($leadId, $followUpDate)
    {
        $query = "INSERT INTO follow_ups (
                    lead_id, employee_id, scheduled_date, status, created_at
                ) VALUES (?, ?, ?, 'scheduled', NOW())
                ON DUPLICATE KEY UPDATE 
                scheduled_date = ?, status = 'scheduled', updated_at = NOW()";
        
        $this->db->execute($query, [$leadId, $this->employeeId, $followUpDate, $followUpDate]);
    }

    /**
     * Get lead details for calling
     */
    public function getLeadDetails($leadId)
    {
        try {
            $query = "SELECT l.*, 
                        p.title as property_title,
                        p.location as property_location,
                        p.price as property_price,
                        c.name as campaign_name,
                        cl.last_call_time,
                        cl.call_count
                    FROM leads l
                    LEFT JOIN properties p ON l.interested_property_id = p.id
                    LEFT JOIN marketing_campaigns c ON l.source_campaign_id = c.id
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
                             WHERE lead_id = ? AND employee_id = ?
                             ORDER BY call_time DESC";
            
            $callHistory = $this->db->fetchAll($historyQuery, [$leadId, $this->employeeId]);
            
            return [
                'success' => true,
                'lead' => $lead,
                'call_history' => $callHistory
            ];
            
        } catch (Exception $e) {
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
            $leadQuery = "SELECT l.lead_type, l.source, l.interested_property_id,
                            p.type as property_type, p.price_range
                         FROM leads l
                         LEFT JOIN properties p ON l.interested_property_id = p.id
                         WHERE l.id = ? AND l.assigned_to = ?";
            
            $lead = $this->db->fetchOne($leadQuery, [$leadId, $this->employeeId]);
            
            if (!$lead) {
                throw new Exception("Lead not found");
            }
            
            // Determine script category based on lead type and property
            $category = 'general';
            
            if ($lead['lead_type'] === 'hot') {
                $category = 'hot_lead';
            } elseif ($lead['lead_type'] === 'cold') {
                $category = 'cold_call';
            } elseif ($lead['property_type'] === 'residential') {
                $category = 'residential';
            } elseif ($lead['property_type'] === 'commercial') {
                $category = 'commercial';
            }
            
            // Get script for category
            $scriptQuery = "SELECT * FROM calling_scripts 
                            WHERE category = ? AND status = 'active'
                            ORDER BY priority DESC, usage_count ASC
                            LIMIT 1";
            
            $script = $this->db->fetchOne($scriptQuery, [$category]);
            
            // If no specific script found, get general script
            if (!$script) {
                $scriptQuery = "SELECT * FROM calling_scripts 
                                WHERE category = 'general' AND status = 'active'
                                ORDER BY priority DESC, usage_count ASC
                                LIMIT 1";
                
                $script = $this->db->fetchOne($scriptQuery);
            }
            
            // Update script usage count
            if ($script) {
                $updateQuery = "UPDATE calling_scripts 
                                SET usage_count = usage_count + 1 
                                WHERE id = ?";
                $this->db->execute($updateQuery, [$script['id']]);
            }
            
            return [
                'success' => true,
                'script' => $script,
                'category' => $category
            ];
            
        } catch (Exception $e) {
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
        $query = "SELECT l.*, fu.scheduled_date, fu.status as follow_up_status
                 FROM leads l
                 JOIN follow_ups fu ON l.id = fu.lead_id
                 WHERE fu.employee_id = ?
                 AND DATE(fu.scheduled_date) = CURDATE()
                 AND fu.status = 'scheduled'
                 ORDER BY fu.scheduled_date ASC";
        
        return $this->db->fetchAll($query, [$this->employeeId]);
    }

    /**
     * Complete follow-up
     */
    public function completeFollowUp($leadId, $result)
    {
        try {
            // Update follow-up status
            $query = "UPDATE follow_ups 
                      SET status = 'completed', completed_at = NOW(), result = ?
                      WHERE lead_id = ? AND employee_id = ? AND status = 'scheduled'";
            
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
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
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
