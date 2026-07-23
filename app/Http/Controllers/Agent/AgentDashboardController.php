<?php
// TODO: Consider async file operations for better performance

/**
 * Agent Dashboard Controller
 * Supports two agent types:
 * - mlm_company: Company MLM agents with network, team, differential commissions
 * - freelancer: Independent freelancer agents with flat commission per sale
 * - independent: Independent agents with flat fee or custom percentage
 */

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\BaseController;
use App\Core\Database;
use App\Core\Security;
use Exception;

class AgentDashboardController extends BaseController
{
    protected $db;
    protected $agentType;
    protected $associateId;
    protected $userId;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        $this->layout = 'layouts/agent';
    }

    /**
     * Display the agent dashboard - main entry point
     */
    public function index()
    {
        $this->requireLogin();

        if (($_SESSION['role'] ?? '') !== 'agent') {
            $this->redirect('/auth/login');
            return;
        }

        $this->userId = $_SESSION['user_id'] ?? 0;
        $this->associateId = $_SESSION['associate_id'] ?? 0;

        // If no associate_id in session, try to fetch it
        if (!$this->associateId) {
            try {
                $assoc = $this->db->fetchOne("SELECT id, agent_type FROM associates WHERE user_id = ?", [$this->userId]);
                if ($assoc) {
                    $this->associateId = $assoc['id'];
                    $this->agentType = $assoc['agent_type'] ?? 'mlm_company';
                    $_SESSION['associate_id'] = $this->associateId;
                    $_SESSION['agent_type'] = $this->agentType;
                }
            } catch (Exception $e) {
                error_log("Agent Dashboard: Failed to fetch associate: " . $e->getMessage());
            }
        } else {
            $this->agentType = $_SESSION['agent_type'] ?? 'mlm_company';
        }

        try {
            // Get data based on agent type
            if ($this->agentType === 'freelancer' || $this->agentType === 'independent') {
                $dashboardData = $this->getFreelancerDashboardData();
            } else {
                $dashboardData = $this->getMLMCompanyDashboardData();
            }

            $this->render('agent/dashboard', array_merge([
                'page_title' => 'Agent Dashboard - APS Dream Home',
                'page_description' => $this->agentType === 'freelancer' ? 'Freelancer Agent Dashboard' : 'MLM Company Agent Dashboard',
                'agent_type' => $this->agentType,
                'associate_id' => $this->associateId,
            ], $dashboardData));

        } catch (Exception $e) {
            error_log("Agent Dashboard Error: " . $e->getMessage());
            $this->render('agent/dashboard', [
                'page_title' => 'Agent Dashboard - APS Dream Home',
                'page_description' => 'Manage your real estate business',
                'agent_type' => $this->agentType,
                'error' => 'Failed to load dashboard data',
            ]);
        }
    }

    /**
     * Get dashboard data for MLM Company agents
     */
    private function getMLMCompanyDashboardData()
    {
        $userId = $this->userId;
        $associateId = $this->associateId;

        // Agent statistics
        $agentStats = $this->getAgentStatistics($userId);

        // Recent leads
        $recentLeads = $this->getRecentLeads($userId);

        // Assigned properties
        $assignedProperties = $this->getAssignedProperties($userId);

        // Commission summary (MLM style with network commissions)
        $commissionSummary = $this->getMLMCommissionSummary($associateId);

        // Network/Team stats (only for MLM)
        $networkStats = $this->getNetworkStats($associateId);

        // Gamification
        $gamify = $this->safeGamify('forAgent', (int)$userId, (int)$associateId);

        return [
            'agent_stats' => $agentStats,
            'recent_leads' => $recentLeads,
            'assigned_properties' => $assignedProperties,
            'commission_summary' => $commissionSummary,
            'network_stats' => $networkStats,
            'gamify' => $gamify,
        ];
    }

    /**
     * Get dashboard data for Freelancer/Independent agents
     */
    private function getFreelancerDashboardData()
    {
        $userId = $this->userId;
        $associateId = $this->associateId;

        // Basic stats
        $agentStats = $this->getAgentStatistics($userId);

        // Recent leads
        $recentLeads = $this->getRecentLeads($userId);

        // My properties (freelancers manage their own listings)
        $myProperties = $this->getMyProperties($userId);

        // Commission summary (flat commission per sale)
        $commissionSummary = $this->getFreelancerCommissionSummary($associateId);

        // Upcoming site visits
        $siteVisits = $this->getUpcomingSiteVisits($userId);

        // Performance metrics
        $performance = $this->getFreelancerPerformance($associateId);

        return [
            'agent_stats' => $agentStats,
            'recent_leads' => $recentLeads,
            'my_properties' => $myProperties,
            'commission_summary' => $commissionSummary,
            'site_visits' => $siteVisits,
            'performance' => $performance,
            'gamify' => $this->safeGamify('forAgent', (int)$userId, (int)$associateId),
        ];
    }

    /**
     * Common agent statistics
     */
    private function getAgentStatistics($userId)
    {
        try {
            $totalLeads = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM leads WHERE agent_id = ?",
                [$userId]
            );

            $convertedLeads = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM leads WHERE agent_id = ? AND status = 'converted'",
                [$userId]
            );

            $totalProperties = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM properties WHERE agent_id = ?",
                [$userId]
            );

            $soldProperties = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM properties WHERE agent_id = ? AND status = 'sold'",
                [$userId]
            );

            $conversionRate = $totalLeads['count'] > 0 ? round(($convertedLeads['count'] / $totalLeads['count']) * 100, 2) : 0;

            return [
                'total_leads' => $totalLeads['count'] ?? 0,
                'converted_leads' => $convertedLeads['count'] ?? 0,
                'total_properties' => $totalProperties['count'] ?? 0,
                'sold_properties' => $soldProperties['count'] ?? 0,
                'conversion_rate' => $conversionRate . '%',
            ];
        } catch (Exception $e) {
            error_log("Agent Statistics Error: " . $e->getMessage());
            return [
                'total_leads' => 0,
                'converted_leads' => 0,
                'total_properties' => 0,
                'sold_properties' => 0,
                'conversion_rate' => '0%',
            ];
        }
    }

    /**
     * Recent leads for agent
     */
    private function getRecentLeads($userId)
    {
        try {
            $leads = $this->db->fetchAll(
                "SELECT l.name, l.email, l.phone, l.status, l.created_at, p.title as property_title
                 FROM leads l 
                 LEFT JOIN properties p ON l.property_id = p.id 
                 WHERE l.agent_id = ? 
                 ORDER BY l.created_at DESC 
                 LIMIT 10",
                [$userId]
            );
            return $leads;
        } catch (Exception $e) {
            error_log("Recent Leads Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Assigned properties (for MLM company agents)
     */
    private function getAssignedProperties($userId)
    {
        try {
            $properties = $this->db->fetchAll(
                "SELECT p.id, p.title, p.price, p.location, p.status, p.created_at
                 FROM properties p 
                 WHERE p.agent_id = ? 
                 ORDER BY p.created_at DESC 
                 LIMIT 20",
                [$userId]
            );
            return $properties;
        } catch (Exception $e) {
            error_log("Assigned Properties Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * My properties (for freelancer agents - they create/manage their own)
     */
    private function getMyProperties($userId)
    {
        try {
            $properties = $this->db->fetchAll(
                "SELECT p.id, p.title, p.price, p.location, p.status, p.area_sqft, p.created_at,
                        col.name as colony_name
                 FROM properties p 
                 LEFT JOIN colonies col ON p.colony_id = col.id
                 WHERE p.agent_id = ? 
                 ORDER BY p.created_at DESC 
                 LIMIT 20",
                [$userId]
            );
            return $properties;
        } catch (Exception $e) {
            error_log("My Properties Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * MLM Commission Summary - includes network/downline commissions
     */
    private function getMLMCommissionSummary($associateId)
    {
        try {
            // Direct commissions
            $directCommissions = $this->db->fetchAll(
                "SELECT c.amount, c.type, c.description, c.created_at
                 FROM mlm_commission_ledger c
                 WHERE c.associate_id = ? AND c.type IN ('direct_sale', 'level_bonus')
                 ORDER BY c.created_at DESC LIMIT 10",
                [$associateId]
            );

            // Network/Override commissions
            $networkCommissions = $this->db->fetchAll(
                "SELECT c.amount, c.type, c.description, c.created_at
                 FROM mlm_commission_ledger c
                 WHERE c.associate_id = ? AND c.type IN ('override', 'generation_bonus', 'matching_bonus', 'royalty_pool', 'rank_bonus')
                 ORDER BY c.created_at DESC LIMIT 10",
                [$associateId]
            );

            // Calculate totals
            $totalDirect = 0;
            $totalNetwork = 0;

            foreach ($directCommissions as $c) $totalDirect += $c['amount'];
            foreach ($networkCommissions as $c) $totalNetwork += $c['amount'];

            return [
                'direct_commissions' => $directCommissions,
                'network_commissions' => $networkCommissions,
                'total_direct' => number_format($totalDirect, 2),
                'total_network' => number_format($totalNetwork, 2),
                'total_commission' => number_format($totalDirect + $totalNetwork, 2),
            ];
        } catch (Exception $e) {
            error_log("MLM Commission Summary Error: " . $e->getMessage());
            return [
                'direct_commissions' => [],
                'network_commissions' => [],
                'total_direct' => '0.00',
                'total_network' => '0.00',
                'total_commission' => '0.00',
            ];
        }
    }

    /**
     * Freelancer Commission Summary - flat commission per sale
     */
    private function getFreelancerCommissionSummary($associateId)
    {
        try {
            // Get brokerage rate from associate
            $associate = $this->db->fetchOne(
                "SELECT brokerage_model, brokerage_rate, brokerage_flat_fee FROM associates WHERE id = ?",
                [$associateId]
            );

            $model = $associate['brokerage_model'] ?? 'flat_percentage';
            $rate = $associate['brokerage_rate'] ?? 0;
            $flatFee = $associate['brokerage_flat_fee'] ?? 0;

            // Recent commissions
            $commissions = $this->db->fetchAll(
                "SELECT c.amount, c.type, c.description, c.created_at, p.title as property_title
                 FROM mlm_commission_ledger c
                 LEFT JOIN plot_bookings pb ON c.booking_id = pb.id
                 LEFT JOIN properties p ON pb.plot_id = p.id
                 WHERE c.associate_id = ? AND c.type = 'direct_sale'
                 ORDER BY c.created_at DESC LIMIT 10",
                [$associateId]
            );

            $totalCommission = 0;
            foreach ($commissions as $c) $totalCommission += $c['amount'];

            return [
                'brokerage_model' => $model,
                'brokerage_rate' => $rate,
                'flat_fee' => $flatFee,
                'commissions' => $commissions,
                'total_commission' => number_format($totalCommission, 2),
            ];
        } catch (Exception $e) {
            error_log("Freelancer Commission Summary Error: " . $e->getMessage());
            return [
                'brokerage_model' => 'flat_percentage',
                'brokerage_rate' => 0,
                'flat_fee' => 0,
                'commissions' => [],
                'total_commission' => '0.00',
            ];
        }
    }

    /**
     * Network stats for MLM agents
     */
    private function getNetworkStats($associateId)
    {
        try {
            $directCount = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM associates WHERE sponsor_id = ? AND status = 'active'",
                [$associateId]
            );

            $teamSize = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM mlm_network_tree WHERE parent_id = ? AND level > 0",
                [$associateId]
            );

            $teamGV = $this->db->fetchOne(
                "SELECT COALESCE(SUM(total_bv), 0) as gv FROM (
                    SELECT associate_id, SUM(personal_bv) as total_bv 
                    FROM mlm_network_tree 
                    WHERE parent_id = ? 
                    GROUP BY associate_id
                ) t",
                [$associateId]
            );

            return [
                'direct_count' => $directCount['count'] ?? 0,
                'team_size' => $teamSize['count'] ?? 0,
                'team_gv' => number_format($teamGV['gv'] ?? 0, 2),
            ];
        } catch (Exception $e) {
            error_log("Network Stats Error: " . $e->getMessage());
            return [
                'direct_count' => 0,
                'team_size' => 0,
                'team_gv' => '0.00',
            ];
        }
    }

    /**
     * Upcoming site visits
     */
    private function getUpcomingSiteVisits($userId)
    {
        try {
            $visits = $this->db->fetchAll(
                "SELECT sv.*, l.name as lead_name, l.phone as lead_phone, col.name as colony_name
                 FROM site_visits sv
                 LEFT JOIN leads l ON sv.lead_id = l.id
                 LEFT JOIN colonies col ON sv.colony_id = col.id
                 WHERE (sv.assigned_to = ? OR sv.user_id = ?) 
                 AND sv.visit_date >= CURDATE()
                 AND sv.status NOT IN ('completed', 'cancelled')
                 ORDER BY sv.visit_date ASC, sv.visit_time ASC
                 LIMIT 10",
                [$userId, $userId]
            );
            return $visits;
        } catch (Exception $e) {
            error_log("Site Visits Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Freelancer performance metrics
     */
    private function getFreelancerPerformance($associateId)
    {
        try {
            // This month's sales
            $thisMonth = $this->db->fetchOne(
                "SELECT COUNT(*) as count, COALESCE(SUM(pb.total_amount), 0) as volume
                 FROM plot_bookings pb
                 JOIN associates a ON pb.associate_id = a.id
                 WHERE a.id = ? AND MONTH(pb.created_at) = MONTH(CURDATE()) AND YEAR(pb.created_at) = YEAR(CURDATE())",
                [$associateId]
            );

            // Last month's sales
            $lastMonth = $this->db->fetchOne(
                "SELECT COUNT(*) as count, COALESCE(SUM(pb.total_amount), 0) as volume
                 FROM plot_bookings pb
                 JOIN associates a ON pb.associate_id = a.id
                 WHERE a.id = ? AND MONTH(pb.created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(pb.created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))",
                [$associateId]
            );

            // Total career sales
            $career = $this->db->fetchOne(
                "SELECT COUNT(*) as count, COALESCE(SUM(pb.total_amount), 0) as volume
                 FROM plot_bookings pb
                 JOIN associates a ON pb.associate_id = a.id
                 WHERE a.id = ? AND pb.status IN ('booked', 'confirmed', 'completed')",
                [$associateId]
            );

            return [
                'this_month' => [
                    'count' => $thisMonth['count'] ?? 0,
                    'volume' => number_format($thisMonth['volume'] ?? 0, 2),
                ],
                'last_month' => [
                    'count' => $lastMonth['count'] ?? 0,
                    'volume' => number_format($lastMonth['volume'] ?? 0, 2),
                ],
                'career' => [
                    'count' => $career['count'] ?? 0,
                    'volume' => number_format($career['volume'] ?? 0, 2),
                ],
            ];
        } catch (Exception $e) {
            error_log("Freelancer Performance Error: " . $e->getMessage());
            return [
                'this_month' => ['count' => 0, 'volume' => '0.00'],
                'last_month' => ['count' => 0, 'volume' => '0.00'],
                'career' => ['count' => 0, 'volume' => '0.00'],
            ];
        }
    }

    // ============ API Endpoints ============

    /**
     * Add new lead
     */
    public function addLead()
    {
        $this->requireLogin();
        $this->requireAgentRole();

        try {
            $data = $this->getRequestData();

            $leadData = [
                'agent_id' => $_SESSION['user_id'],
                'name' => Security::sanitize($data['name'] ?? ''),
                'email' => Security::sanitize($data['email'] ?? ''),
                'phone' => Security::sanitize($data['phone'] ?? ''),
                'property_interest' => Security::sanitize($data['property_interest'] ?? ''),
                'budget' => Security::sanitize($data['budget'] ?? ''),
                'status' => 'new',
                'source' => Security::sanitize($data['source'] ?? 'manual'),
                'notes' => Security::sanitize($data['notes'] ?? ''),
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $this->db->execute(
                "INSERT INTO leads (agent_id, name, email, phone, property_interest, budget, status, source, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $leadData['agent_id'],
                    $leadData['name'],
                    $leadData['email'],
                    $leadData['phone'],
                    $leadData['property_interest'],
                    $leadData['budget'],
                    $leadData['status'],
                    $leadData['source'],
                    $leadData['notes'],
                    $leadData['created_at'],
                ]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Lead added successfully',
                'lead' => $leadData,
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to add lead: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update lead status
     */
    public function updateLeadStatus($leadId)
    {
        $this->requireLogin();
        $this->requireAgentRole();

        try {
            $data = $this->getRequestData();
            $status = Security::sanitize($data['status'] ?? 'new');

            $this->db->execute(
                "UPDATE leads SET status = ?, updated_at = ? WHERE id = ? AND agent_id = ?",
                [$status, date('Y-m-d H:i:s'), $leadId, $_SESSION['user_id']]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Lead status updated successfully',
                'status' => $status,
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update lead status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get request data from various sources
     */
    private function getRequestData(): array
    {
        $data = [];
        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $data = json_decode($input, true) ?: [];
        }
        if (!empty($_POST)) {
            $data = array_merge($data, $_POST);
        }
        if (!empty($_GET)) {
            $data = array_merge($data, $_GET);
        }
        return $data;
    }

    private function requireAgentRole()
    {
        if (($_SESSION['role'] ?? '') !== 'agent') {
            $this->redirect('/auth/login');
            return false;
        }
        return true;
    }

    private function safeGamify(string $method, int ...$args): array
    {
        try {
            $role = strtolower(str_replace('for', '', $method));
            $cacheKey1 = $args[0] ?? 0;
            $cacheKey2 = $args[1] ?? 0;
            return \App\Services\CacheService::getGamification(
                $role,
                (int)$cacheKey1,
                (int)$cacheKey2,
                function () use ($method, $args) {
                    $svc = new \App\Services\GamificationService();
                    return $svc->{$method}(...$args);
                }
            );
        } catch (\Throwable $e) {
            error_log('Gamification error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Agent leads list
     */
    public function leads()
    {
        $this->requireAgentRole();
        $userId = $this->userId;
        $leads = [];
        try {
            $leads = $this->db->fetchAll(
                "SELECT l.*, u.name as assigned_by_name FROM leads l 
                 LEFT JOIN users u ON u.id = l.assigned_by 
                 WHERE l.assigned_to = ? ORDER BY l.created_at DESC LIMIT 50",
                [$userId]
            ) ?: [];
        } catch (\Throwable $e) { error_log('Agent leads error: ' . $e->getMessage()); }

        return $this->render('agent/leads', [
            'page_title' => 'My Leads',
            'leads' => $leads,
        ]);
    }

    /**
     * Agent properties list
     */
    public function properties()
    {
        $this->requireAgentRole();
        $userId = $this->userId;
        $properties = [];
        try {
            $properties = $this->db->fetchAll(
                "SELECT * FROM user_properties WHERE posted_by = ? ORDER BY created_at DESC LIMIT 50",
                [$userId]
            ) ?: [];
        } catch (\Throwable $e) { error_log('Agent properties error: ' . $e->getMessage()); }

        return $this->render('agent/properties', [
            'page_title' => 'My Properties',
            'properties' => $properties,
        ]);
    }

    /**
     * Agent commissions
     */
    public function commissions()
    {
        $this->requireAgentRole();
        $userId = $this->userId;
        $commissions = [];
        $total = 0;
        try {
            $commissions = $this->db->fetchAll(
                "SELECT * FROM mlm_commission_ledger WHERE user_id = ? ORDER BY created_at DESC LIMIT 50",
                [$userId]
            ) ?: [];
            $total = (int)$this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE user_id = ?",
                [$userId]
            );
        } catch (\Throwable $e) { error_log('Agent commissions error: ' . $e->getMessage()); }

        return $this->render('agent/commissions', [
            'page_title' => 'Commissions',
            'commissions' => $commissions,
            'total_earned' => $total,
        ]);
    }

    /**
     * Agent profile
     */
    public function profile()
    {
        $this->requireAgentRole();
        $userId = $this->userId;
        $user = null;
        try {
            $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        } catch (\Throwable $e) { error_log('Agent profile error: ' . $e->getMessage()); }

        return $this->render('agent/profile', [
            'page_title' => 'My Profile',
            'user' => $user,
        ]);
    }

    /**
     * Update agent profile
     */
    public function updateProfile()
    {
        $this->requireAgentRole();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/agent/profile');
            return;
        }
        $userId = $this->userId;
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        try {
            $this->db->query("UPDATE users SET name = ?, phone = ? WHERE id = ?", [$name, $phone, $userId]);
            $_SESSION['user_name'] = $name;
            $this->setFlash('success', 'Profile updated');
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Update failed');
        }
        $this->redirect('/agent/profile');
    }

    /**
     * Agent wallet - balance, transactions, withdrawal
     */
    public function wallet()
    {
        $this->requireAgentRole();
        $userId = $this->userId;
        $balance = 0;
        $transactions = [];
        try {
            $wallet = $this->db->fetch("SELECT * FROM wallet_points WHERE user_id = ? ORDER BY created_at DESC LIMIT 1", [$userId]);
            $balance = $wallet['points'] ?? $wallet['balance'] ?? 0;
            $transactions = $this->db->fetchAll(
                "SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 50",
                [$userId]
            ) ?: [];
        } catch (\Throwable $e) { error_log('Agent wallet error: ' . $e->getMessage()); }

        return $this->render('agent/wallet', [
            'page_title' => 'My Wallet',
            'balance' => $balance,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Agent deals - bookings and transactions
     */
    public function deals()
    {
        $this->requireAgentRole();
        $userId = $this->userId;
        $deals = [];
        try {
            $deals = $this->db->fetchAll(
                "SELECT pb.*, p.title as property_title, u.name as customer_name
                 FROM plot_bookings pb
                 LEFT JOIN plots p ON pb.plot_id = p.id
                 LEFT JOIN users u ON pb.user_id = u.id
                 WHERE pb.associate_id = (SELECT id FROM associates WHERE user_id = ? LIMIT 1)
                 ORDER BY pb.created_at DESC LIMIT 50",
                [$userId]
            ) ?: [];
        } catch (\Throwable $e) { error_log('Agent deals error: ' . $e->getMessage()); }

        return $this->render('agent/deals', [
            'page_title' => 'My Deals',
            'deals' => $deals,
        ]);
    }
}