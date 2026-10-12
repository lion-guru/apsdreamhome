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
    // agents.id for call_logs.agent_id (FK to agents, NULL when the
    // telecaller has no agent profile — column is nullable).
    protected $agentId;

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

        // Lead ownership (leads.assigned_to) and call_logs.agent_id live in
        // USERS.id space (assign UI lists users.id). Prefer the user id —
        // employees.id is HR-internal and matches nothing in those tables.
        $this->employeeId = $_SESSION['employee_user_id'] ?? $_SESSION['user_id'] ?? $_SESSION['employee_id'] ?? null;

        // Detect admin/super-admin/manager session
        $hasAdminSession = isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
        $hasAdminRole = isset($_SESSION['admin_role']) && in_array($_SESSION['admin_role'], ['admin', 'super_admin', 'manager']);
        $hasGenericRole = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin', 'manager', 'employee', 'telecaller']);

        $isAdmin = $hasAdminSession || $hasAdminRole || $hasGenericRole;

        // If admin is accessing without employee_id, use their admin_id so DB queries still work
        if (!$this->employeeId && $hasAdminSession) {
            $this->employeeId = (int)$_SESSION['admin_id'];
        }

        // Resolve the agent profile for call_logs.agent_id (FK to agents.id).
        // Stays null for telecallers without an agent profile (nullable col).
        $this->agentId = null;
        try {
            $uid = (int)($this->employeeId ?: 0);
            if ($uid > 0) {
                $arow = $this->db->fetchOne(
                    "SELECT id FROM agents WHERE user_id = ? LIMIT 1",
                    [$uid]
                );
                if (!empty($arow['id'])) {
                    $this->agentId = (int)$arow['id'];
                }
            }
        } catch (\Throwable $e) {
            error_log('[TelecallingController] agentId resolve: ' . $e->getMessage());
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
            return $this->db->fetchAll($query, [$this->employeeId]);
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        return [];
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
     * Get objection handling cheat-sheet
     */
    private function getObjectionHandling()
    {
        return [
            'price' => [
                'label' => 'Rate zyada hai / Budget nahi hai',
                'scripts' => [
                    'acknowledge' => '"Samajh sakta hoon, budget important hai. But let me show you value..."',
                    'value_stack' => 'Plot ki location, amenities, future appreciation, bank loan facility — sab milega.',
                    'emi_breakdown' => 'EMI ₹X/month — rent se kam, aur asset banta hai.',
                    'urgency' => 'Price revision next month — today lock karein toh save karein.',
                ],
                'rebuttals' => [
                    'Competitor cheaper' => 'Competitor ki location/amenities check karein — humara colony gated, RERA approved, bank loan ready.',
                    'Market down' => 'Exactly isliye invest karo — low price me entry, future me high return.',
                    'Need time' => 'Samajh sakta hoon. 48-hour hold laga deta hoon — aap soch saken, pressure nahi.',
                ]
            ],
            'timing' => [
                'label' => 'Abhi time nahi hai / Baad me sochenge',
                'scripts' => [
                    'acknowledge' => '"Bilkul, decision jaldi nahi karni chahiye. But..."',
                    'hook' => 'Colony me 15 plots hi bache hain — delay me koi aur book kar lega.',
                    'action' => '48-hour hold laga deta hoon — aap soch saken, pressure nahi.',
                ],
                'rebuttals' => [
                    'Busy' => 'Sirf 5 minute ka call — sirf details confirm karni hain.',
                    'Consult family' => 'Family ko bhi confidence aayega jab site visit karenge. Sunday free cab arrange kar dete hain.',
                ]
            ],
            'trust' => [
                'label' => 'Builder pe bharosa nahi / Kya guarantee hai',
                'scripts' => [
                    'acknowledge' => 'Valid sawal. Trust kamana padta hai.',
                    'proof' => 'RERA registration, bank loan approved, 500+ happy families, 10+ saal se market mein.',
                    'assurance' => 'Site visit karke khud dekh lijiye. Free Sunday cab arrange kar dete hain.',
                ],
                'rebuttals' => [
                    'Legal issues' => 'RERA registered, clear title, bank approved — legal clean.',
                    'Delayed possession' => 'Construction on track, RERA timeline binding, penalty clause in agreement.',
                ]
            ],
            'authority' => [
                'label' => 'Pata karke batana / Partner se puchna padega',
                'scripts' => [
                    'acknowledge' => 'Bilkul sahi — bade decision me partner involve hona chahiye.',
                    'hook' => 'Par information aaj hi le lein, taaki decision informed ho.',
                    'action' => 'Main partner ko bhi call karke details de deta hoon. Joint call schedule kar dein?',
                ],
                'rebuttals' => [
                    'Busy partner' => 'Sirf 10 minute ka joint call — Saturday/Sunday convenient.',
                    'Not decision maker' => 'Information aapko mil jaye, partner ko forward kar dein. Main bhi call kar doonga.',
                ]
            ]
        ];
    }
    
    /**
     * Get calling scripts organized by category
     */
    private function getScriptsByCategory()
    {
        $scripts = $this->getCallingScripts();
        $byCategory = [];
        foreach ($scripts as $script) {
            $cat = $script['category'] ?? 'general';
            $byCategory[$cat][] = $script;
        }
        return $byCategory;
    }
    
    /**
     * Get quick access scripts for sidebar
     */
    private function getQuickScripts()
    {
        $scripts = $this->getCallingScripts();
        $quick = [];
        $priorityCategories = ['sales', 'cold_call', 'follow_up', 'objection_handling'];
        foreach ($priorityCategories as $cat) {
            foreach ($scripts as $script) {
                if (($script['category'] ?? '') === $cat) {
                    $quick[] = $script;
                    break;
                }
            }
        }
        return $quick;
    }
    
    /**
     * Live Calling Desk — main entry point
     */
    public function callingDesk()
    {
        $this->initializeEmployeeSession();
        try {
            $todayStats = $this->getTodayStats();
            $leadQueue = $this->getLeadQueue();
            $todaysFollowUps = $this->getTodayFollowUps();
            $scripts = $this->getCallingScripts();
            $objections = $this->getObjectionHandling();
            $scriptsByCategory = $this->getScriptsByCategory();
            $quickScripts = $this->getQuickScripts();
            
            $this->render('employee/telecalling_desk', [
                'page_title' => 'Telecalling Desk - Live Calling',
                'page_heading' => 'Live Calling Desk',
                'today_stats' => $todayStats,
                'lead_queue' => $leadQueue,
                'todays_follow_ups' => $todaysFollowUps,
                'scripts' => $scripts,
                'objections' => $objections,
                'scripts_by_category' => $scriptsByCategory,
                'quick_scripts' => $quickScripts,
            ]);
            
        } catch (\Exception $e) {
            $this->handleError($e->getMessage());
        }
    }

    /**
     * Book site visit from telecaller's desk (1-click Sunday booking)
     */
    public function bookSiteVisit()
    {
        try {
            // Inputs come from POST form (router dispatches with zero args)
            $leadId = (int)($_POST['lead_id'] ?? 0);
            $visitDate = $_POST['visit_date'] ?? null; // Expected: next Sunday
            $pickupPoint = trim((string)($_POST['pickup_point'] ?? ''));
            $notes = trim((string)($_POST['notes'] ?? ''));
            
            if (!$leadId || !$visitDate) {
                $this->jsonResponse(['success' => false, 'error' => 'Missing required fields'], 400);
                return;
            }
            
            // Validate lead assignment (leads has no property/colony FKs —
            // keep to its own columns so the lookup can't 1054).
            $lead = $this->db->fetchOne(
                "SELECT l.* FROM leads l
                 WHERE l.id = ? AND l.assigned_to = ?",
                [$leadId, $this->employeeId]
            );
            
            if (!$lead) {
                $this->jsonResponse(['success' => false, 'error' => 'Lead not found or not assigned'], 404);
                return;
            }
            
            // Determine next Sunday if not specified
            if (!$visitDate) {
                $today = new \DateTime();
                $daysUntilSunday = (7 - $today->format('w')) % 7;
                if ($daysUntilSunday === 0) $daysUntilSunday = 7; // Next Sunday if today is Sunday
                $visitDate = $today->modify("+$daysUntilSunday days")->format('Y-m-d');
            }
            
            // Create site visit record
            // site_visits has pickup_location (not pickup_point) and no
            // source/created_by columns — insert only real columns.
            $visitData = [
                'lead_id' => $leadId,
                'colony_id' => null,
                'property_id' => null,
                'visit_date' => $visitDate,
                'visit_time' => '10:00:00', // Default 10 AM
                'pickup_location' => $pickupPoint ?: 'Customer location',
                'assigned_to' => $this->employeeId,
                'status' => 'scheduled',
                'notes' => $notes,
            ];
            
            // Insert site visit
            $visitId = $this->db->insert('site_visits', $visitData);
            
            // Create fleet trip if vehicles available
            $tripId = $this->createFleetTripForVisit($visitId, $lead, $visitDate);
            
            // Log activity
            $this->logActivity('site_visit_scheduled', [
                'visit_id' => $visitId,
                'lead_id' => $leadId,
                'visit_date' => $visitDate,
                'assigned_to' => $this->employeeId,
            ]);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Site visit scheduled for ' . date('l, d M Y', strtotime($visitDate)),
                'visit_id' => $visitId,
                'trip_id' => $tripId,
            ]);
            
        } catch (\Exception $e) {
            error_log('TelecallingController::bookSiteVisit: ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Failed to book site visit'], 500);
        }
    }
    
    /**
     * Create fleet trip for the site visit (best-effort)
     */
    private function createFleetTripForVisit($visitId, $lead, $visitDate)
    {
        try {
            $pdo = $this->db->getConnection();
            $tid = $this->tenantId();
            
            // Find available vehicle for the date
            $vehicle = $this->db->fetchOne(
                "SELECT id FROM fleet_vehicles 
                 WHERE is_active = 1 AND tenant_id = ? 
                 AND id NOT IN (
                     SELECT vehicle_id FROM fleet_trips 
                     WHERE trip_date = ? AND status != 'cancelled' AND tenant_id = ?
                 )
                 LIMIT 1", [$this->tenantId(), $visitDate, $this->tenantId()]);
            
            if (!$vehicle) return null;
            
            // Find associate for this lead
            $associate = $this->db->fetchOne(
                "SELECT associate_id FROM leads WHERE id = ?", [$lead['id']]
            );
            
            // Create trip
            $tripData = [
                'tenant_id' => $this->tenantId(),
                'trip_date' => $visitDate,
                'vehicle_id' => $vehicle['id'],
                'colony_id' => $lead['colony_id'] ?? null,
                'associate_id' => $lead['associate_id'] ?? null,
                'pickup_point' => 'Customer location',
                'customers_planned' => 1,
                'notes' => 'Auto-scheduled from telecaller booking',
                'status' => 'scheduled',
                'created_by' => $this->employeeId,
            ];
            
            $this->db->insert('fleet_trips', $tripData);
            return $this->db->lastInsertId();
            
        } catch (\Exception $e) {
            error_log('TelecallingController::createFleetTripForVisit: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get objection handling cheat-sheet
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
                $this->agentId,
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
            return $this->db->fetchAll($query, [$this->employeeId]);
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        return [];
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
