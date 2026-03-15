<?php

/**
 * Agent Dashboard Controller
 * Advanced real estate agent dashboard with comprehensive features
 */

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\BaseController;
use App\Core\Database;
use App\Core\Security;
use Exception;

class AgentDashboardController extends BaseController
{
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    /**
     * Display the agent dashboard
     */
    public function index()
    {
        $this->requireLogin();
        
        $userId = $_SESSION['user_id'] ?? 0;

        try {
            // Get agent statistics
            $agentStats = $this->getAgentStatistics($userId);
            
            // Get recent leads
            $recentLeads = $this->getRecentLeads($userId);
            
            // Get properties assigned to agent
            $assignedProperties = $this->getAssignedProperties($userId);
            
            // Get commission summary
            $commissionSummary = $this->getCommissionSummary($userId);
            
            $this->render('agent/dashboard', [
                'page_title' => 'Agent Dashboard - APS Dream Home',
                'page_description' => 'Manage your real estate business',
                'agent_stats' => $agentStats,
                'recent_leads' => $recentLeads,
                'assigned_properties' => $assignedProperties,
                'commission_summary' => $commissionSummary
            ]);
            
        } catch (Exception $e) {
            error_log("Agent Dashboard Error: " . $e->getMessage());
            $this->render('agent/dashboard', [
                'page_title' => 'Agent Dashboard - APS Dream Home',
                'page_description' => 'Manage your real estate business',
                'error' => 'Failed to load dashboard data'
            ]);
        }
    }

    /**
     * Get agent statistics
     */
    private function getAgentStatistics($userId)
    {
        try {
            // Get total leads
            $totalLeads = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM leads WHERE agent_id = ?",
                [$userId]
            );

            // Get converted leads
            $convertedLeads = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM leads WHERE agent_id = ? AND status = 'converted'",
                [$userId]
            );

            // Get total properties
            $totalProperties = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM properties WHERE agent_id = ?",
                [$userId]
            );

            // Get sold properties
            $soldProperties = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM properties WHERE agent_id = ? AND status = 'sold'",
                [$userId]
            );

            // Get total commission
            $totalCommission = $this->db->fetchOne(
                "SELECT COALESCE(SUM(amount), 0) as total FROM commissions WHERE user_id = ?",
                [$userId]
            );

            // Get conversion rate
            $conversionRate = $totalLeads['count'] > 0 ? round(($convertedLeads['count'] / $totalLeads['count']) * 100, 2) : 0;

            return [
                'total_leads' => $totalLeads['count'] ?? 0,
                'converted_leads' => $convertedLeads['count'] ?? 0,
                'total_properties' => $totalProperties['count'] ?? 0,
                'sold_properties' => $soldProperties['count'] ?? 0,
                'total_commission' => number_format($totalCommission['total'] ?? 0),
                'conversion_rate' => $conversionRate . '%'
            ];

        } catch (Exception $e) {
            error_log("Agent Statistics Error: " . $e->getMessage());
            return [
                'total_leads' => 0,
                'converted_leads' => 0,
                'total_properties' => 0,
                'sold_properties' => 0,
                'total_commission' => 0,
                'conversion_rate' => '0%'
            ];
        }
    }

    /**
     * Get recent leads
     */
    private function getRecentLeads($userId)
    {
        try {
            $leads = $this->db->fetchAll(
                "SELECT 
                    l.name,
                    l.email,
                    l.phone,
                    l.status,
                    l.created_at,
                    p.title as property_title
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
     * Get assigned properties
     */
    private function getAssignedProperties($userId)
    {
        try {
            $properties = $this->db->fetchAll(
                "SELECT 
                    p.id,
                    p.title,
                    p.price,
                    p.location,
                    p.status,
                    p.created_at
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
     * Get commission summary
     */
    private function getCommissionSummary($userId)
    {
        try {
            $commissions = $this->db->fetchAll(
                "SELECT 
                    c.amount,
                    c.type,
                    c.description,
                    c.created_at
                 FROM commissions c 
                 WHERE c.user_id = ? 
                 ORDER BY c.created_at DESC 
                 LIMIT 10",
                [$userId]
            );

            // Calculate totals
            $totalCommission = 0;
            $propertyCommission = 0;
            $referralCommission = 0;

            foreach ($commissions as $commission) {
                $totalCommission += $commission['amount'];
                if ($commission['type'] === 'property') {
                    $propertyCommission += $commission['amount'];
                } elseif ($commission['type'] === 'referral') {
                    $referralCommission += $commission['amount'];
                }
            }

            return [
                'commissions' => $commissions,
                'total_commission' => number_format($totalCommission),
                'property_commission' => number_format($propertyCommission),
                'referral_commission' => number_format($referralCommission)
            ];

        } catch (Exception $e) {
            error_log("Commission Summary Error: " . $e->getMessage());
            return [
                'commissions' => [],
                'total_commission' => '0.00',
                'property_commission' => '0.00',
                'referral_commission' => '0.00'
            ];
        }
    }

    /**
     * Add new lead
     */
    public function addLead()
    {
        $this->requireLogin();

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
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Insert lead
            $this->db->execute(
                "INSERT INTO leads (agent_id, name, email, phone, property_interest, budget, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $leadData['agent_id'],
                    $leadData['name'],
                    $leadData['email'],
                    $leadData['phone'],
                    $leadData['property_interest'],
                    $leadData['budget'],
                    $leadData['status'],
                    $leadData['created_at']
                ]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Lead added successfully',
                'lead' => $leadData
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to add lead: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update lead status
     */
    public function updateLeadStatus($leadId)
    {
        $this->requireLogin();

        try {
            $data = $this->getRequestData();

            $status = Security::sanitize($data['status'] ?? 'new');

            // Update lead
            $this->db->execute(
                "UPDATE leads SET status = ?, updated_at = ? WHERE id = ?",
                [$status, date('Y-m-d H:i:s'), $leadId]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Lead status updated successfully',
                'status' => $status
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update lead status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get request data from various sources
     */
    private function getRequestData(): array
    {
        $data = [];
        
        // Get JSON data
        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $data = json_decode($input, true) ?: [];
        }
        
        // Merge with POST data
        if (!empty($_POST)) {
            $data = array_merge($data, $_POST);
        }
        
        // Merge with GET data
        if (!empty($_GET)) {
            $data = array_merge($data, $_GET);
        }
        
        return $data;
    }
}