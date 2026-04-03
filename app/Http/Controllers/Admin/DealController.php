<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Core\Database\Database;

/**
 * Deal Controller
 * Handles deal tracking and pipeline management
 */
class DealController extends AdminController
{
    protected $db;
    private $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }

    /**
     * Deal list view
     */
    public function index()
    {
        $stage = $_GET['stage'] ?? '';
        $search = $_GET['search'] ?? '';

        $deals = $this->getDeals($stage, $search);
        $stats = $this->getDealStats();
        $stages = $this->getStages();

        $data = [
            'page_title' => 'Deal Tracking - APS Dream Home',
            'deals' => $deals,
            'stats' => $stats,
            'stages' => $stages,
            'filters' => ['stage' => $stage, 'search' => $search]
        ];

        $this->render('admin/deals/index', $data);
    }

    /**
     * Kanban board view
     */
    public function kanban()
    {
        $stages = $this->getStages();
        $deals = $this->getDealsForKanban();
        $stats = $this->getDealStats();

        $data = [
            'page_title' => 'Deal Pipeline - APS Dream Home',
            'deals' => $deals,
            'stats' => $stats,
            'stages' => $stages
        ];

        $this->render('admin/deals/kanban', $data);
    }

    /**
     * Create deal from lead
     */
    public function createFromLead()
    {
        $leadId = $_GET['lead_id'] ?? '';
        $leads = $this->getQualifiedLeads();
        $agents = $this->getAgents();

        $selectedLead = null;
        if (!empty($leadId)) {
            $selectedLead = $this->getLeadById($leadId);
        }

        $data = [
            'page_title' => 'Create Deal - APS Dream Home',
            'leads' => $leads,
            'agents' => $agents,
            'selected_lead' => $selectedLead
        ];

        $this->render('admin/deals/create', $data);
    }

    /**
     * Store deal
     */
    public function store()
    {
        try {
            $data = [
                'lead_id' => $_POST['lead_id'],
                'deal_value' => $_POST['deal_value'],
                'expected_close_date' => $_POST['expected_close_date'],
                'stage' => $_POST['stage'] ?? 'lead',
                'assigned_to' => $_POST['assigned_to'] ?? null,
                'property_id' => $_POST['property_id'] ?? null,
                'notes' => $_POST['notes'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $sql = "INSERT INTO lead_deals (lead_id, deal_value, expected_close_date, stage, assigned_to, property_id, notes, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_values($data));

            $dealId = $this->pdo->lastInsertId();

            // Update lead status to qualified
            $sql = "UPDATE leads SET status = 'qualified', updated_at = NOW() WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data['lead_id']]);

            $this->setFlash('success', 'Deal created successfully');
            $this->redirect('/admin/deals');

        } catch (\Exception $e) {
            error_log("DealController::store error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to create deal');
            $this->redirect('/admin/deals/create');
        }
    }

    /**
     * Update deal stage (for kanban drag-drop)
     */
    public function updateStage($dealId)
    {
        try {
            $stage = $_POST['stage'] ?? '';
            $validStages = ['lead', 'qualified', 'proposal', 'negotiation', 'won', 'lost'];

            if (!in_array($stage, $validStages)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Invalid stage']);
            }

            $sql = "UPDATE lead_deals SET stage = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$stage, $dealId]);

            // If won or lost, update lead status and close date
            if ($stage === 'won' || $stage === 'lost') {
                $sql = "UPDATE lead_deals SET actual_close_date = CURDATE() WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$dealId]);

                // Get lead ID
                $sql = "SELECT lead_id FROM lead_deals WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$dealId]);
                $deal = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($deal) {
                    $leadStatus = $stage === 'won' ? 'converted' : 'lost';
                    $sql = "UPDATE leads SET status = ?, updated_at = NOW() WHERE id = ?";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([$leadStatus, $deal['lead_id']]);
                }
            }

            return $this->jsonResponse(['success' => true]);

        } catch (\Exception $e) {
            error_log("DealController::updateStage error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to update stage']);
        }
    }

    /**
     * Get deals
     */
    private function getDeals($stage = '', $search = '')
    {
        $sql = "SELECT ld.*, l.name as lead_name, l.email as lead_email, l.phone as lead_phone,
                       p.title as property_title, u.name as assigned_to_name
                FROM lead_deals ld
                LEFT JOIN leads l ON ld.lead_id = l.id
                LEFT JOIN properties p ON ld.property_id = p.id
                LEFT JOIN users u ON ld.assigned_to = u.id
                WHERE 1=1";

        $params = [];

        if (!empty($stage)) {
            $sql .= " AND ld.stage = ?";
            $params[] = $stage;
        }

        if (!empty($search)) {
            $sql .= " AND (l.name LIKE ? OR l.email LIKE ? OR p.title LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY ld.created_at DESC LIMIT 100";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get deals grouped by stage for kanban
     */
    private function getDealsForKanban()
    {
        $sql = "SELECT ld.*, l.name as lead_name, l.email as lead_email, l.phone as lead_phone,
                       p.title as property_title, u.name as assigned_to_name
                FROM lead_deals ld
                LEFT JOIN leads l ON ld.lead_id = l.id
                LEFT JOIN properties p ON ld.property_id = p.id
                LEFT JOIN users u ON ld.assigned_to = u.id
                WHERE ld.stage NOT IN ('won', 'lost')
                ORDER BY ld.expected_close_date ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $deals = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Group by stage
        $grouped = [
            'lead' => [],
            'qualified' => [],
            'proposal' => [],
            'negotiation' => []
        ];

        foreach ($deals as $deal) {
            $grouped[$deal['stage']][] = $deal;
        }

        return $grouped;
    }

    /**
     * Get deal statistics
     */
    private function getDealStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_deals,
                    SUM(CASE WHEN stage = 'lead' THEN 1 ELSE 0 END) as lead_count,
                    SUM(CASE WHEN stage = 'qualified' THEN 1 ELSE 0 END) as qualified_count,
                    SUM(CASE WHEN stage = 'proposal' THEN 1 ELSE 0 END) as proposal_count,
                    SUM(CASE WHEN stage = 'negotiation' THEN 1 ELSE 0 END) as negotiation_count,
                    SUM(CASE WHEN stage = 'won' THEN 1 ELSE 0 END) as won_count,
                    SUM(CASE WHEN stage = 'lost' THEN 1 ELSE 0 END) as lost_count,
                    SUM(CASE WHEN stage = 'won' THEN deal_value ELSE 0 END) as total_revenue,
                    SUM(CASE WHEN stage NOT IN ('won', 'lost') THEN deal_value ELSE 0 END) as pipeline_value,
                    AVG(CASE WHEN actual_close_date IS NOT NULL THEN DATEDIFF(actual_close_date, created_at) END) as avg_cycle_days
                FROM lead_deals";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get pipeline stages
     */
    private function getStages()
    {
        return [
            ['id' => 'lead', 'name' => 'Lead', 'color' => 'secondary'],
            ['id' => 'qualified', 'name' => 'Qualified', 'color' => 'info'],
            ['id' => 'proposal', 'name' => 'Proposal', 'color' => 'primary'],
            ['id' => 'negotiation', 'name' => 'Negotiation', 'color' => 'warning'],
            ['id' => 'won', 'name' => 'Won', 'color' => 'success'],
            ['id' => 'lost', 'name' => 'Lost', 'color' => 'danger']
        ];
    }

    /**
     * Get qualified leads for deal creation
     */
    private function getQualifiedLeads()
    {
        $sql = "SELECT id, name, email, phone FROM leads WHERE status NOT IN ('converted', 'lost') ORDER BY name";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get lead by ID
     */
    private function getLeadById($leadId)
    {
        $sql = "SELECT * FROM leads WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$leadId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get agents
     */
    private function getAgents()
    {
        $sql = "SELECT id, name FROM users WHERE role IN ('agent', 'manager', 'admin') AND status = 'active' ORDER BY name";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
