<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

/**
 * Deal Pipeline Controller - Kanban Deal Management
 * Manages deals with Kanban board, drag-and-drop stages, timeline tracking
 */
class DealPipelineController extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        parent::__construct();
    }

    /**
     * Display Kanban board with all deals
     * Route: /admin/deal-pipeline
     */
    public function index()
    {
        try {
            $conn = $this->db->getConnection();
            
            // Get all deals with customer and property details, grouped by stage
            $sql = "SELECT d.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone,
                    p.title as property_title, p.location as property_location, p.price as property_price,
                    CASE d.stage
                        WHEN 'lead' THEN 'Lead'
                        WHEN 'qualified' THEN 'Qualified'
                        WHEN 'site_visit' THEN 'Site Visit'
                        WHEN 'negotiation' THEN 'Negotiation'
                        WHEN 'booking' THEN 'Booking'
                        WHEN 'agreement' THEN 'Agreement'
                        WHEN 'closed_won' THEN 'Closed Won'
                        WHEN 'closed_lost' THEN 'Closed Lost'
                        ELSE d.stage
                    END as stage_label,
                    CASE d.priority
                        WHEN 'low' THEN 'Low'
                        WHEN 'medium' THEN 'Medium'
                        WHEN 'high' THEN 'High'
                        WHEN 'urgent' THEN 'Urgent'
                        ELSE d.priority
                    END as priority_label
                    FROM deals d
                    LEFT JOIN customers c ON d.customer_id = c.id
                    LEFT JOIN properties p ON d.property_id = p.id
                    WHERE d.status = 'active'
                    ORDER BY d.stage, d.priority DESC, d.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $deals = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Group deals by stage
            $dealsByStage = [
                'lead' => [],
                'qualified' => [],
                'site_visit' => [],
                'negotiation' => [],
                'booking' => [],
                'agreement' => [],
                'closed_won' => [],
                'closed_lost' => []
            ];
            
            foreach ($deals as $deal) {
                if (isset($dealsByStage[$deal['stage']])) {
                    $dealsByStage[$deal['stage']][] = $deal;
                }
            }
            
            // Get pipeline statistics
            $stats = $this->getPipelineStatistics();
            
            $data = [
                'page_title' => 'Deal Pipeline - Kanban Board',
                'deals' => $dealsByStage,
                'stats' => $stats
            ];
            
            return $this->render('admin.deal-pipeline.index', $data);
            
        } catch (\Exception $e) {
            return $this->render('admin.deal-pipeline.index', [
                'page_title' => 'Deal Pipeline - Kanban Board',
                'deals' => [],
                'stats' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display create deal form
     * Route: /admin/deal-pipeline/create
     */
    public function create()
    {
        try {
            $conn = $this->db->getConnection();
            
            // Get customers for dropdown
            $customers = $conn->query("SELECT id, name, email, phone FROM customers ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get properties for dropdown
            $properties = $conn->query("SELECT id, title, location, price FROM properties WHERE status = 'available' ORDER BY title ASC")->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get users for assignment
            $users = $conn->query("SELECT id, name FROM users WHERE role = 'agent' OR role = 'sales' ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
            
            $data = [
                'page_title' => 'Create New Deal',
                'customers' => $customers,
                'properties' => $properties,
                'users' => $users
            ];
            
            return $this->render('admin.deal-pipeline.create', $data);
            
        } catch (\Exception $e) {
            return $this->render('admin.deal-pipeline.create', [
                'page_title' => 'Create New Deal',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Store new deal
     * Route: /admin/deal-pipeline/store
     */
    public function store()
    {
        try {
            $conn = $this->db->getConnection();
            
            $dealNumber = 'DEAL-' . date('Ymd-His');
            $customerId = $_POST['customer_id'] ?? 0;
            $propertyId = $_POST['property_id'] ?? 0;
            $assignedTo = $_POST['assigned_to'] ?? 0;
            $dealValue = $_POST['deal_value'] ?? 0;
            $expectedCloseDate = $_POST['expected_close_date'] ?? null;
            $probability = $_POST['probability'] ?? 50;
            $priority = $_POST['priority'] ?? 'medium';
            $stage = $_POST['stage'] ?? 'lead';
            $notes = $_POST['notes'] ?? '';
            $source = $_POST['source'] ?? 'manual';
            
            $sql = "INSERT INTO deals 
                    (deal_number, customer_id, property_id, assigned_to, deal_value, 
                     expected_close_date, probability, priority, stage, source, notes, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $dealNumber, $customerId, $propertyId, $assignedTo, $dealValue,
                $expectedCloseDate, $probability, $priority, $stage, $source, $notes
            ]);
            
            header('Location: /admin/deal-pipeline?success=Deal created successfully');
            exit;
            
        } catch (\Exception $e) {
            header('Location: /admin/deal-pipeline/create?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Display single deal details
     * Route: /admin/deal-pipeline/{id}
     */
    public function show($id)
    {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT d.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone,
                    c.address as customer_address,
                    p.title as property_title, p.location as property_location, p.price as property_price,
                    u.name as assigned_to_name,
                    CASE d.stage
                        WHEN 'lead' THEN 'Lead'
                        WHEN 'qualified' THEN 'Qualified'
                        WHEN 'site_visit' THEN 'Site Visit'
                        WHEN 'negotiation' THEN 'Negotiation'
                        WHEN 'booking' THEN 'Booking'
                        WHEN 'agreement' THEN 'Agreement'
                        WHEN 'closed_won' THEN 'Closed Won'
                        WHEN 'closed_lost' THEN 'Closed Lost'
                        ELSE d.stage
                    END as stage_label,
                    CASE d.priority
                        WHEN 'low' THEN 'Low'
                        WHEN 'medium' THEN 'Medium'
                        WHEN 'high' THEN 'High'
                        WHEN 'urgent' THEN 'Urgent'
                        ELSE d.priority
                    END as priority_label
                    FROM deals d
                    LEFT JOIN customers c ON d.customer_id = c.id
                    LEFT JOIN properties p ON d.property_id = p.id
                    LEFT JOIN users u ON d.assigned_to = u.id
                    WHERE d.id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            $deal = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$deal) {
                header('Location: /admin/deal-pipeline?error=Deal not found');
                exit;
            }
            
            // Get deal history/timeline
            $history = $conn->prepare("SELECT * FROM deal_history WHERE deal_id = ? ORDER BY created_at DESC LIMIT 20");
            $history->execute([$id]);
            $dealHistory = $history->fetchAll(\PDO::FETCH_ASSOC);
            
            $data = [
                'page_title' => 'Deal Details - ' . $deal['deal_number'],
                'deal' => $deal,
                'deal_history' => $dealHistory
            ];
            
            return $this->render('admin.deal-pipeline.show', $data);
            
        } catch (\Exception $e) {
            header('Location: /admin/deal-pipeline?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Move deal to next stage
     * Route: /admin/deal-pipeline/{id}/move-stage
     */
    public function moveStage($id)
    {
        try {
            $conn = $this->db->getConnection();
            
            $newStage = $_POST['stage'] ?? '';
            
            // Validate stage
            $validStages = ['lead', 'qualified', 'site_visit', 'negotiation', 'booking', 'agreement', 'closed_won', 'closed_lost'];
            if (!in_array($newStage, $validStages)) {
                header('Location: /admin/deal-pipeline?error=Invalid stage');
                exit;
            }
            
            // Get current stage
            $current = $conn->prepare("SELECT stage FROM deals WHERE id = ?");
            $current->execute([$id]);
            $currentStage = $current->fetchColumn();
            
            // Update deal stage
            $conn->prepare("UPDATE deals SET stage = ?, updated_at = NOW() WHERE id = ?")->execute([$newStage, $id]);
            
            // Log stage change in history
            $historySql = "INSERT INTO deal_history (deal_id, action, old_value, new_value, created_at)
                          VALUES (?, 'stage_change', ?, ?, NOW())";
            $conn->prepare($historySql)->execute([$id, $currentStage, $newStage]);
            
            header('Location: /admin/deal-pipeline?success=Deal stage updated');
            exit;
            
        } catch (\Exception $e) {
            header('Location: /admin/deal-pipeline?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Update deal probability
     * Route: /admin/deal-pipeline/{id}/update-probability
     */
    public function updateProbability($id)
    {
        try {
            $conn = $this->db->getConnection();
            
            $probability = $_POST['probability'] ?? 50;
            
            // Validate probability
            if ($probability < 0 || $probability > 100) {
                header('Location: /admin/deal-pipeline?error=Invalid probability value');
                exit;
            }
            
            // Update deal probability
            $conn->prepare("UPDATE deals SET probability = ?, updated_at = NOW() WHERE id = ?")->execute([$probability, $id]);
            
            header('Location: /admin/deal-pipeline?success=Deal probability updated');
            exit;
            
        } catch (\Exception $e) {
            header('Location: /admin/deal-pipeline?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Mark deal as won
     * Route: /admin/deal-pipeline/{id}/mark-won
     */
    public function markWon($id)
    {
        try {
            $conn = $this->db->getConnection();
            
            // Get current stage
            $current = $conn->prepare("SELECT stage, customer_id, property_id, deal_value FROM deals WHERE id = ?");
            $current->execute([$id]);
            $dealData = $current->fetch(\PDO::FETCH_ASSOC);
            
            // Update deal stage to closed won
            $conn->prepare("UPDATE deals SET stage = 'closed_won', status = 'completed', closed_at = NOW() WHERE id = ?")->execute([$id]);
            
            // Log in history
            $historySql = "INSERT INTO deal_history (deal_id, action, old_value, new_value, created_at)
                          VALUES (?, 'deal_won', ?, 'closed_won', NOW())";
            $conn->prepare($historySql)->execute([$id, $dealData['stage']]);
            
            // If property is assigned, mark it as booked
            if ($dealData['property_id']) {
                $conn->prepare("UPDATE properties SET status = 'booked' WHERE id = ?")->execute([$dealData['property_id']]);
            }
            
            header('Location: /admin/deal-pipeline?success=Deal marked as won');
            exit;
            
        } catch (\Exception $e) {
            header('Location: /admin/deal-pipeline?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Mark deal as lost
     * Route: /admin/deal-pipeline/{id}/mark-lost
     */
    public function markLost($id)
    {
        try {
            $conn = $this->db->getConnection();
            
            // Get current stage
            $current = $conn->prepare("SELECT stage FROM deals WHERE id = ?");
            $current->execute([$id]);
            $currentStage = $current->fetchColumn();
            
            // Update deal stage to closed lost
            $conn->prepare("UPDATE deals SET stage = 'closed_lost', status = 'completed', closed_at = NOW() WHERE id = ?")->execute([$id]);
            
            // Log in history
            $historySql = "INSERT INTO deal_history (deal_id, action, old_value, new_value, created_at)
                          VALUES (?, 'deal_lost', ?, 'closed_lost', NOW())";
            $conn->prepare($historySql)->execute([$id, $currentStage]);
            
            header('Location: /admin/deal-pipeline?success=Deal marked as lost');
            exit;
            
        } catch (\Exception $e) {
            header('Location: /admin/deal-pipeline?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Display deal timeline/history
     * Route: /admin/deal-pipeline/{id}/timeline
     */
    public function timeline($id)
    {
        try {
            $conn = $this->db->getConnection();
            
            // Get deal details
            $deal = $conn->prepare("SELECT * FROM deals WHERE id = ?");
            $deal->execute([$id]);
            $dealData = $deal->fetch(\PDO::FETCH_ASSOC);
            
            if (!$dealData) {
                header('Location: /admin/deal-pipeline?error=Deal not found');
                exit;
            }
            
            // Get timeline history
            $history = $conn->prepare("SELECT * FROM deal_history WHERE deal_id = ? ORDER BY created_at ASC");
            $history->execute([$id]);
            $timeline = $history->fetchAll(\PDO::FETCH_ASSOC);
            
            $data = [
                'page_title' => 'Deal Timeline - ' . $dealData['deal_number'],
                'deal' => $dealData,
                'timeline' => $timeline
            ];
            
            return $this->render('admin.deal-pipeline.timeline', $data);
            
        } catch (\Exception $e) {
            header('Location: /admin/deal-pipeline?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Get pipeline statistics
     */
    private function getPipelineStatistics()
    {
        try {
            $conn = $this->db->getConnection();
            
            $stats = [
                'total_deals' => $conn->query("SELECT COUNT(*) FROM deals WHERE status = 'active'")->fetchColumn(),
                'total_value' => $conn->query("SELECT COALESCE(SUM(deal_value), 0) FROM deals WHERE status = 'active'")->fetchColumn(),
                'won_deals' => $conn->query("SELECT COUNT(*) FROM deals WHERE stage = 'closed_won'")->fetchColumn(),
                'won_value' => $conn->query("SELECT COALESCE(SUM(deal_value), 0) FROM deals WHERE stage = 'closed_won'")->fetchColumn(),
                'lost_deals' => $conn->query("SELECT COUNT(*) FROM deals WHERE stage = 'closed_lost'")->fetchColumn(),
                'lost_value' => $conn->query("SELECT COALESCE(SUM(deal_value), 0) FROM deals WHERE stage = 'closed_lost'")->fetchColumn(),
                'lead_count' => $conn->query("SELECT COUNT(*) FROM deals WHERE stage = 'lead' AND status = 'active'")->fetchColumn(),
                'qualified_count' => $conn->query("SELECT COUNT(*) FROM deals WHERE stage = 'qualified' AND status = 'active'")->fetchColumn(),
                'site_visit_count' => $conn->query("SELECT COUNT(*) FROM deals WHERE stage = 'site_visit' AND status = 'active'")->fetchColumn(),
                'negotiation_count' => $conn->query("SELECT COUNT(*) FROM deals WHERE stage = 'negotiation' AND status = 'active'")->fetchColumn(),
                'booking_count' => $conn->query("SELECT COUNT(*) FROM deals WHERE stage = 'booking' AND status = 'active'")->fetchColumn(),
                'average_probability' => $conn->query("SELECT AVG(probability) FROM deals WHERE status = 'active'")->fetchColumn()
            ];
            
            return $stats;
            
        } catch (\Exception $e) {
            return [];
        }
    }
}
