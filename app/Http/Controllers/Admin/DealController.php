<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;
use PDO;

/**
 * Deal Controller - CRM Pipeline Management
 * Handles deals, pipeline stages, and deal activities
 */
class DealController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->checkAdminAuth();
    }

    /**
     * Display all deals with pipeline view
     */
    public function index()
    {
        $this->requireAdmin();
        $base = BASE_URL;
        $stages = [];
        $dealsByStage = [];
        $stats = ['total_deals' => 0, 'total_value' => 0, 'won_this_month' => ['count' => 0, 'total' => 0], 'lost_this_month' => ['count' => 0]];

        try {
            $stageRows = $this->db->fetchAll(
                "SELECT id, name AS stage_name, stage_order FROM deal_stages WHERE is_active = 1 ORDER BY stage_order"
            ) ?: [];
            foreach ($stageRows as $s) {
                $sid = intval($s['id'] ?? 1);
                $stages[] = ['id' => $sid, 'stage_name' => $s['stage_name'], 'stage_order' => $sid];
                $deals = $this->db->fetchAll(
                    "SELECT d.*, u.name as assigned_to_name FROM deals d LEFT JOIN users u ON d.assigned_to = u.id WHERE d.stage_id = ? AND d.status = 'active' ORDER BY d.expected_close_date ASC",
                    [$sid]
                );
                $dealsByStage[$sid] = $deals ?: [];
            }

            $stats = [
                'total_deals' => intval($this->db->fetch("SELECT COUNT(*) as count FROM deals WHERE status = 'active'")['count'] ?? 0),
                'total_value' => floatval($this->db->fetch("SELECT COALESCE(SUM(deal_value),0) as total FROM deals WHERE status = 'active'")['total'] ?? 0),
                'won_this_month' => $this->db->fetch("SELECT COUNT(*) as count, COALESCE(SUM(deal_value),0) as total FROM deals WHERE status = 'won' AND YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())") ?: ['count' => 0, 'total' => 0],
                'lost_this_month' => $this->db->fetch("SELECT COUNT(*) as count FROM deals WHERE status = 'lost' AND YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())") ?: ['count' => 0]
            ];
        } catch (\Throwable $e) {
            error_log("DealController index error: " . $e->getMessage());
        }

        $allDeals = [];
        try {
            $allDeals = $this->db->fetchAll("SELECT d.*, u.name as assigned_to_name, l.name as lead_name, l.email as lead_email FROM deals d LEFT JOIN users u ON d.assigned_to = u.id LEFT JOIN leads l ON d.lead_id = l.id WHERE d.status = 'active' ORDER BY d.created_at DESC") ?: [];
            foreach ($allDeals as &$deal) {
                $deal['stage'] = $deal['stage_id'];
                $deal['lead_name'] ??= '';
                $deal['lead_email'] ??= '';
                $deal['property_title'] ??= 'Not specified';
            }
        } catch (\Throwable $e) {
            error_log("DealController deals query: " . $e->getMessage());
        }
        $stats['pipeline_value'] = $stats['total_value'] ?? 0;
        $stats['won_count'] = intval($stats['won_this_month']['count'] ?? 0);
        $stats['lost_count'] = intval($stats['lost_this_month']['count'] ?? 0);
        $stats['total_revenue'] = floatval($stats['won_this_month']['total'] ?? 0);
        // Remap stages for view
        $viewStages = [];
        foreach ($stages as $s) {
            $viewStages[] = ['id' => $s['id'], 'name' => $s['stage_name'], 'color' => 'primary'];
        }
        $data = [
            'pageTitle' => 'Deals Pipeline',
            'stages' => $viewStages,
            'dealsByStage' => $dealsByStage,
            'deals' => $allDeals,
            'stats' => $stats,
            'filters' => [],
            'currentUrl' => '/admin/deals'
        ];
        return $this->render('admin/deals/index', $data);
    }

    /**
     * Display kanban board view
     */
    public function kanban()
    {
        $this->requireAdmin();
        $base = BASE_URL;
        $deals = []; $stages = []; $stats = ['total_deals' => 0, 'total_value' => 0.0, 'won_count' => 0, 'lost_count' => 0];

        try {
            $stages = $this->db->fetchAll("SELECT id, name, color FROM deal_stages WHERE is_active = 1 ORDER BY stage_order") ?: [];
            $deals  = $this->db->fetchAll(
                "SELECT d.*, u.name as assigned_to_name, l.name as lead_name, l.email as lead_email
                 FROM deals d
                 LEFT JOIN users u ON d.assigned_to = u.id
                 LEFT JOIN leads l ON d.lead_id = l.id
                 WHERE d.status = 'active'
                 ORDER BY d.expected_close_date ASC"
            ) ?: [];

            $wonRow  = $this->db->fetch("SELECT COUNT(*) c, COALESCE(SUM(deal_value),0) t FROM deals WHERE status = 'won' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())") ?: ['c' => 0, 't' => 0];
            $lostRow = $this->db->fetch("SELECT COUNT(*) c FROM deals WHERE status = 'lost' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())") ?: ['c' => 0];

            $stats = [
                'total_deals' => count($deals),
                'total_value' => array_sum(array_map('floatval', array_column($deals, 'deal_value'))),
                'won_count'   => (int)($wonRow['c'] ?? 0),
                'lost_count'  => (int)($lostRow['c'] ?? 0),
            ];
        } catch (\Throwable $e) {
            error_log("DealController kanban error: " . $e->getMessage());
        }

        $dealsByStage = [];
        foreach ($stages as $s) {
            $dealsByStage[(int)$s['id']] = [];
        }
        foreach ($deals as $deal) {
            $sid = (int)($deal['stage_id'] ?? 0);
            if (isset($dealsByStage[$sid])) {
                $dealsByStage[$sid][] = $deal;
            }
        }

        $data = [
            'pageTitle' => 'Deals Kanban Board',
            'stages' => $stages,
            'dealsByStage' => $dealsByStage,
            'deals' => $deals,
            'stats' => $stats,
            'filters' => [],
            'currentUrl' => '/admin/deals/kanban'
        ];
        return $this->render('admin/deals/kanban', $data);
    }

    /**
     * Display deal creation form
     */
    public function create()
    {
        $this->requireAdmin();
        $base = BASE_URL;
        $leads = []; $users = []; $properties = []; $stages = []; $users = [];

        try {
            $leads = $this->db->fetchAll("SELECT id, name, email, phone FROM leads WHERE status NOT IN ('closed_won','closed_lost','converted','not_interested') ORDER BY created_at DESC LIMIT 50") ?: [];
            $tid = (int)$this->tenantId();
            $tidWhere = $tid > 1 ? " WHERE tenant_id = ?" : "";
            $users = $this->db->fetchAll("SELECT id, name, email, phone FROM users{$tidWhere} ORDER BY created_at DESC LIMIT 50", $tid > 1 ? [$tid] : []) ?: [];
            [$tidSql, $tidParams] = $this->tenantWhere();
            $users = $this->db->fetchAll("SELECT id, name FROM users WHERE status = 'active'{$tidSql} ORDER BY name ASC", $tidParams) ?: [];
            $stages = $this->db->fetchAll("SELECT id, name AS stage_name FROM deal_stages WHERE is_active = 1 ORDER BY stage_order") ?: [];
        } catch (\Throwable $e) {
            error_log("DealController create error: " . $e->getMessage());
        }

        $data = [
            'pageTitle' => 'Create New Deal',
            'leads' => $leads,
            'users' => $users,
            'properties' => $properties,
            'stages' => $stages,
            'users' => $users,
            'currentUrl' => '/admin/deals/create'
        ];
        return $this->render('admin/deals/create', $data);
    }

    /**
     * Store new deal
     */
    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (BASE_URL) . '/admin/deals');
            exit;
        }

        $base = BASE_URL;

        $dealName = trim($_POST['deal_name'] ?? '');
        $dealValue = floatval($_POST['deal_value'] ?? 0);
        $currency = trim($_POST['currency'] ?? 'INR');
        $leadId = intval($_POST['lead_id'] ?? 0) ?: null;
        $customerId = intval($_POST['customer_id'] ?? 0) ?: null;
        $propertyId = intval($_POST['property_id'] ?? 0) ?: null;
        $dealStageId = intval($_POST['deal_stage_id'] ?? 1);
        $probability = intval($_POST['probability'] ?? 0);
        $expectedCloseDate = $_POST['expected_close_date'] ?? null;
        $assignedTo = intval($_POST['assigned_to'] ?? $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null);
        $description = trim($_POST['description'] ?? '');

        // Validation
        if (empty($dealName)) {
            $_SESSION['error'] = 'Deal name is required';
            header('Location: ' . $base . '/admin/deals/create');
            exit;
        }

        try {
            $tid = (int)$this->tenantId();
            $stmt = $this->db->prepare(
                "INSERT INTO deals (deal_name, deal_value, lead_id,
                                   stage_id, probability, expected_close_date, assigned_to,
                                   created_by, status, tenant_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)"
            );

            $stmt->execute([
                $dealName,
                $dealValue,
                $leadId,
                $dealStageId,
                $probability,
                $expectedCloseDate,
                $assignedTo,
                $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null,
                $tid ?: 1
            ]);

            $dealId = $this->db->lastInsertId();

            // Log activity
            $this->logDealActivity($dealId, 'stage_change', 'Deal created', 'Deal was created in ' . $this->getStageName($dealStageId));

            $_SESSION['success'] = 'Deal created successfully';
            header('Location: ' . $base . '/admin/deals');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Failed to create deal: ' . $e->getMessage();
            header('Location: ' . $base . '/admin/deals/create');
            exit;
        }
    }

    /**
     * Display deal details
     */
    public function show($id)
    {
        $this->requireAdmin();
        $base = BASE_URL;
        $dealId = intval($id);

        // Get deal details
        $deal = $this->db->fetch(
            "SELECT d.*,
                    l.name as lead_name,
                    l.name as customer_name, l.email as customer_email, l.phone as customer_phone,
                    u.name as assigned_to_name, u.email as assigned_to_email,
                    creator.name as created_by_name
             FROM deals d
             LEFT JOIN leads l ON d.lead_id = l.id
             LEFT JOIN users u ON d.assigned_to = u.id
             LEFT JOIN users creator ON d.created_by = creator.id
             WHERE d.id = ?",
            [$dealId]
        );

        if ($deal && !empty($deal['stage_id'])) {
            $stg = $this->db->fetch("SELECT name FROM deal_stages WHERE id = ?", [(int)$deal['stage_id']]);
            $deal['stage_name'] = $stg['name'] ?? 'New Lead';
        }

        if (!$deal) {
            $_SESSION['error'] = 'Deal not found';
            header('Location: ' . $base . '/admin/deals');
            exit;
        }

        try {
            // Get deal activities
            $activities = $this->db->fetchAll(
                "SELECT a.*, u.name as created_by_name
                 FROM deal_activities a
                 LEFT JOIN users u ON a.created_by = u.id
                 WHERE a.deal_id = ?
                 ORDER BY a.activity_date DESC LIMIT 50",
                [$dealId]
            );
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }

        // Get deal contacts
        $contacts = $this->db->fetchAll(
            "SELECT dc.*
             FROM deal_contacts dc
             WHERE dc.deal_id = ?",
            [$dealId]
        );

        // Get deal documents
        $documents = $this->db->fetchAll(
            "SELECT d.*
             FROM deal_documents d
             WHERE d.deal_id = ?
             ORDER BY d.created_at DESC",
            [$dealId]
        );

        // Get all stages for dropdown
        $stages = $this->db->fetchAll("SELECT * FROM deal_stages ORDER BY stage_order ASC");

        $data = [
            'pageTitle' => 'Deal Details - ' . htmlspecialchars($deal['deal_name']),
            'deal' => $deal,
            'activities' => $activities,
            'contacts' => $contacts,
            'documents' => $documents,
            'stages' => $stages,
            'currentUrl' => '/admin/deals/' . $dealId
        ];

        return $this->render('admin/deals/show', $data);
    }

    /**
     * Update deal
     */
    public function update($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (BASE_URL) . '/admin/deals');
            exit;
        }

        $base = BASE_URL;
        $dealId = intval($id);

        // Get current deal to track stage changes
        $currentDeal = $this->db->fetch("SELECT * FROM deals WHERE id = ?", [$dealId]);
        if (!$currentDeal) {
            $_SESSION['error'] = 'Deal not found';
            header('Location: ' . $base . '/admin/deals');
            exit;
        }

        $oldStageId = $currentDeal['deal_stage_id'];
        $newStageId = intval($_POST['deal_stage_id'] ?? $oldStageId);

        $dealName = trim($_POST['deal_name'] ?? '');
        $dealValue = floatval($_POST['deal_value'] ?? 0);
        $currency = trim($_POST['currency'] ?? 'INR');
        $leadId = intval($_POST['lead_id'] ?? 0) ?: null;
        $customerId = intval($_POST['customer_id'] ?? 0) ?: null;
        $propertyId = intval($_POST['property_id'] ?? 0) ?: null;
        $probability = intval($_POST['probability'] ?? 0);
        $expectedCloseDate = $_POST['expected_close_date'] ?? null;
        $assignedTo = intval($_POST['assigned_to'] ?? $currentDeal['assigned_to']) ?: null;
        $description = trim($_POST['description'] ?? '');

        try {
            $stmt = $this->db->prepare(
                "UPDATE deals
                 SET deal_name = ?, deal_value = ?, lead_id = ?,
                     stage_id = ?, probability = ?, expected_close_date = ?,
                     assigned_to = ?, updated_at = NOW()
                 WHERE id = ?"
            );

            $stmt->execute([
                $dealName,
                $dealValue,
                $leadId,
                $newStageId,
                $probability,
                $expectedCloseDate,
                $assignedTo,
                $dealId
            ]);

            // Log stage change if changed
            if ($oldStageId != $newStageId) {
                $this->logDealActivity(
                    $dealId,
                    'stage_change',
                    'Stage changed from ' . $this->getStageName($oldStageId) . ' to ' . $this->getStageName($newStageId)
                );
            }

            $_SESSION['success'] = 'Deal updated successfully';
            header('Location: ' . $base . '/admin/deals/' . $dealId);
            exit;
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Failed to update deal: ' . $e->getMessage();
            header('Location: ' . $base . '/admin/deals/' . $dealId);
            exit;
        }
    }

    /**
     * Move deal to new stage (AJAX)
     */
    /**
     * Delete deal
     */
    public function delete($id)
    {
        $this->requireAdmin();
        $base = BASE_URL;
        $dealId = intval($id);
        $tid = (int)$this->tenantId();

        try {
            $this->db->query("DELETE FROM deals WHERE id = ? AND tenant_id = ?", [$dealId, $tid]);
            $_SESSION['success'] = 'Deal deleted successfully';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Failed to delete deal: ' . $e->getMessage();
        }

        header('Location: ' . $base . '/admin/deals');
        exit;
    }

    /**
     * Add activity to deal
     */
    public function addActivity($dealId)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (BASE_URL) . '/admin/deals');
            exit;
        }

        $base = BASE_URL;

        $activityType = trim($_POST['activity_type'] ?? 'note');
        $activityTitle = trim($_POST['activity_title'] ?? '');
        $activityDescription = trim($_POST['activity_description'] ?? '');
        $activityDate = $_POST['activity_date'] ?? date('Y-m-d H:i:s');
        $duration = intval($_POST['duration_minutes'] ?? 0) ?: null;
        $outcome = trim($_POST['outcome'] ?? '') ?: null;

        if (empty($activityTitle)) {
            $_SESSION['error'] = 'Activity title is required';
            header('Location: ' . $base . '/admin/deals/' . $dealId);
            exit;
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO deal_activities (deal_id, activity_type, activity_title, activity_description, 
                                            activity_date, created_by, duration_minutes, outcome)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $dealId,
                $activityType,
                $activityTitle,
                $activityDescription,
                $activityDate,
                $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null,
                $duration,
                $outcome
            ]);

            $_SESSION['success'] = 'Activity added successfully';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Failed to add activity: ' . $e->getMessage();
        }

        header('Location: ' . $base . '/admin/deals/' . $dealId);
        exit;
    }

    /**
     * Log deal activity
     */
    protected function logDealActivity($dealId, $type, $title, $description = null)
    {
        try {
            try {
                $stmt = $this->db->prepare(
                    "INSERT INTO deal_activities (deal_id, activity_type, activity_title, activity_description, 
                                                 activity_date, created_by)
                     VALUES (?, ?, ?, ?, NOW(), ?)"
                );
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            $stmt->execute([$dealId, $type, $title, $description, $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0]);
        } catch (\Throwable $e) {
            error_log("Log activity error: " . $e->getMessage());
        }
    }

    protected function getStageName($stageId)
    {
        try {
            $stage = $this->db->fetch("SELECT name AS stage_name FROM deal_stages WHERE id = ?", [$stageId]);
            return $stage ? $stage['stage_name'] : 'Unknown';
        } catch (\Throwable $e) {
            return 'Unknown';
        }
    }

    /**
     * Check admin authentication
     */
    protected function checkAdminAuth()
    {
        if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
    }

    /**
     * Move deal to a new pipeline stage (AJAX, routed: POST /admin/deals/{id}/stage).
     * Contract consumed by the deals kanban JS: form field `stage` = deals stage id,
     * X-CSRF-Token header, JSON {success, message} response.
     */
    public function updateStage($id) {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
        try {
            $this->validateCsrfOrFail();
            $tid = (int)$this->tenantId();
            $stageId = intval($_POST['stage'] ?? 0);
            $stage = $this->db->fetch("SELECT id, name, probability FROM deal_stages WHERE id = ? AND is_active = 1", [$stageId]);
            if (!$stage) {
                echo json_encode(['success' => false, 'message' => 'Stage not found']);
                exit;
            }
            $name = (string)$stage['name'];
            $status = 'active';
            if ($name === 'Closed Won') { $status = 'won'; }
            elseif ($name === 'Closed Lost') { $status = 'lost'; }
            $probability = max(0, min(100, (int)round((float)($stage['probability'] ?? 50))));
            $this->db->query(
                "UPDATE deals SET stage_id = ?, probability = ?, status = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?",
                [$stageId, $probability, $status, intval($id), $tid]
            );
            echo json_encode(['success' => true, 'message' => 'Moved to ' . $name]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}
