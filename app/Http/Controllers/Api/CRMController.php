<?php
/**
 * CRM API Controller — REST endpoints for Flutter CRM
 * Handles: pipeline, leads CRUD, interactions, tasks, assignments, analytics
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\CRMService;

class CRMController extends BaseController
{
    private $crm;

    public function __construct() {
        parent::__construct();
        $this->crm = new CRMService();
    }

    private function getUser() {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'role' => $_SESSION['role'] ?? null,
        ];
    }

    public function json($data, int $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // ─── Dashboard Stats ─────────────────────────────────────────────

    public function dashboard() {
        $user = $this->getUser();
        $stats = $this->crm->getDashboardStats($user['id'], $user['role'] ?? 'admin');
        $tasks = $this->crm->getMyTasks($user['id'], 'pending');
        $overdue = $this->crm->getOverdueTasks($user['id']);
        $recent = $this->crm->getMyInteractions($user['id'], 10);

        $this->json([
            'success' => true,
            'stats' => $stats,
            'pending_tasks' => $tasks,
            'overdue_tasks' => $overdue,
            'recent_interactions' => $recent,
        ]);
    }

    // ─── Pipeline (Kanban) ───────────────────────────────────────────

    public function pipeline() {
        $user = $this->getUser();
        $filters = [
            'role' => $user['role'] ?? 'admin',
            'assigned_to' => $_GET['assigned_to'] ?? null,
            'source' => $_GET['source'] ?? null,
        ];
        $board = $this->crm->getPipelineBoard($filters);
        $stages = $this->crm->getPipelineStages($filters['role']);
        $this->json(['success' => true, 'board' => $board, 'stages' => $stages]);
    }

    public function moveStage() {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $leadId = $input['lead_id'] ?? null;
        $stage = $input['stage'] ?? null;
        if (!$leadId || !$stage) {
            $this->json(['success' => false, 'error' => 'lead_id and stage required'], 400);
            return;
        }
        $user = $this->getUser();
        $result = $this->crm->moveLeadToStage($leadId, $stage, $user['id']);
        $this->json($result);
    }

    // ─── Leads CRUD ──────────────────────────────────────────────────

    public function leads() {
        $user = $this->getUser();
        $filters = [
            'search' => $_GET['search'] ?? null,
            'status' => $_GET['status'] ?? null,
            'source' => $_GET['source'] ?? null,
            'priority' => $_GET['priority'] ?? null,
            'lead_category' => $_GET['category'] ?? null,
            'min_score' => $_GET['min_score'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 25),
            'sort' => $_GET['sort'] ?? 'created_at',
            'direction' => $_GET['direction'] ?? 'DESC',
        ];

        // Non-admins see only their leads
        if (!in_array($user['role'], ['admin', 'manager'])) {
            $filters['assigned_to'] = $user['id'];
        }

        $result = $this->crm->getLeads($filters);
        $this->json(['success' => true] + $result);
    }

    public function leadDetail($id) {
        $lead = $this->crm->getLeadById($id);
        if (!$lead) {
            $this->json(['success' => false, 'error' => 'Lead not found'], 404);
            return;
        }
        $this->json(['success' => true, 'lead' => $lead]);
    }

    public function createLead() {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $user = $this->getUser();
        $input['created_by'] = $user['id'];

        if (empty($input['name'])) {
            $this->json(['success' => false, 'error' => 'Name is required'], 400);
            return;
        }

        $result = $this->crm->createLead($input);
        $this->json($result, $result['success'] ? 201 : 400);
    }

    public function updateLead($id) {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $result = $this->crm->updateLead($id, $input);
        $this->json($result);
    }

    public function deleteLead($id) {
        $result = $this->crm->deleteLead($id);
        $this->json($result);
    }

    // ─── Interactions ────────────────────────────────────────────────

    public function addInteraction($leadId) {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $user = $this->getUser();

        $result = $this->crm->addInteraction($leadId, $user['id'], $input['type'] ?? 'note', [
            'direction' => $input['direction'] ?? 'outbound',
            'subject' => $input['subject'] ?? null,
            'body' => $input['body'] ?? null,
            'duration_seconds' => $input['duration_seconds'] ?? null,
            'outcome' => $input['outcome'] ?? null,
            'next_action' => $input['next_action'] ?? null,
            'next_action_date' => $input['next_action_date'] ?? null,
        ]);

        // Create follow-up task if next_action_date is set
        if (!empty($input['next_action']) && !empty($input['next_action_date'])) {
            $this->crm->createTask([
                'lead_id' => $leadId,
                'assigned_to' => $user['id'],
                'created_by' => $user['id'],
                'task_type' => $input['type'] ?? 'follow_up',
                'title' => $input['next_action'],
                'priority' => 'medium',
                'due_date' => $input['next_action_date'],
                'due_time' => $input['next_action_time'] ?? null,
            ]);
        }

        $this->json($result, $result['success'] ? 201 : 400);
    }

    public function getInteractions($leadId) {
        $limit = (int)($_GET['limit'] ?? 50);
        $interactions = $this->crm->getLeadInteractions($leadId, $limit);
        $this->json(['success' => true, 'interactions' => $interactions]);
    }

    // ─── Tasks ───────────────────────────────────────────────────────

    public function myTasks() {
        $user = $this->getUser();
        $status = $_GET['status'] ?? 'pending';
        $date = $_GET['date'] ?? null;
        $tasks = $this->crm->getMyTasks($user['id'], $status, $date);
        $overdue = $this->crm->getOverdueTasks($user['id']);
        $this->json(['success' => true, 'tasks' => $tasks, 'overdue' => $overdue]);
    }

    public function createTask() {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $user = $this->getUser();
        $input['assigned_to'] = $input['assigned_to'] ?? $user['id'];
        $input['created_by'] = $user['id'];

        if (empty($input['title']) || empty($input['due_date'])) {
            $this->json(['success' => false, 'error' => 'title and due_date required'], 400);
            return;
        }

        $result = $this->crm->createTask($input);
        $this->json($result, $result['success'] ? 201 : 400);
    }

    public function completeTask($taskId) {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $user = $this->getUser();
        $result = $this->crm->completeTask($taskId, $user['id'], $input['notes'] ?? null);
        $this->json($result);
    }

    // ─── Assignment ──────────────────────────────────────────────────

    public function assignLead($leadId) {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $user = $this->getUser();

        if (empty($input['assigned_to'])) {
            $this->json(['success' => false, 'error' => 'assigned_to required'], 400);
            return;
        }

        $result = $this->crm->assignLead($leadId, $input['assigned_to'], $user['id'], $input['reason'] ?? null, $input['notes'] ?? null);
        $this->json($result);
    }

    public function autoAssign() {
        $result = $this->crm->autoAssignLeads();
        $this->json($result);
    }

    // ─── Scoring ─────────────────────────────────────────────────────

    public function rescoreAll() {
        $result = $this->crm->rescoreAllLeads();
        $this->json($result);
    }

    public function rescoreLead($leadId) {
        $score = $this->crm->recalculateScore($leadId);
        $this->json(['success' => true, 'score' => $score]);
    }

    // ─── Forms (Public — no auth needed) ─────────────────────────────

    public function captureForm() {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $formCode = $input['form_code'] ?? 'WEB_ENQ';
        $data = $input['data'] ?? $input;
        $meta = [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'utm_source' => $input['utm_source'] ?? $_GET['utm_source'] ?? null,
            'utm_medium' => $input['utm_medium'] ?? $_GET['utm_medium'] ?? null,
            'utm_campaign' => $input['utm_campaign'] ?? $_GET['utm_campaign'] ?? null,
            'page_url' => $input['page_url'] ?? $_SERVER['HTTP_REFERER'] ?? null,
            'device_type' => $input['device_type'] ?? null,
        ];

        $result = $this->crm->submitForm($formCode, $data, $meta);
        $this->json($result, $result['success'] ? 201 : 400);
    }

    // ─── Campaigns ───────────────────────────────────────────────────

    public function campaigns() {
        $campaigns = $this->crm->getCampaigns();
        $this->json(['success' => true, 'campaigns' => $campaigns]);
    }

    public function createCampaign() {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $user = $this->getUser();
        $input['created_by'] = $user['id'];
        $result = $this->crm->createCampaign($input);
        $this->json($result, $result['success'] ? 201 : 400);
    }

    // ─── Forms List ──────────────────────────────────────────────────

    public function forms() {
        $forms = $this->crm->getForms();
        $this->json(['success' => true, 'forms' => $forms]);
    }

    // ─── Search (Global) ─────────────────────────────────────────────

    // ─── Admin Overview (for admin dashboard) ──────────────────────────

    public function adminOverview() {
        $pdo = $this->db->getConnection();
        $stats = [
            'total_users' => 0, 'active_associates' => 0, 'bookings_today' => 0,
            'total_revenue' => 0, 'pending_commissions' => 0, 'total_leads' => 0,
            'hot_leads' => 0, 'total_colonies' => 0, 'total_plots' => 0,
        ];
        $recent_activity = [];

        try {
            $r = $pdo->query("SELECT COUNT(*) as c FROM users WHERE deleted_at IS NULL")->fetch();
            $stats['total_users'] = (int)($r['c'] ?? 0);
        } catch (\Throwable $e) {}
        try {
            $r = $pdo->query("SELECT COUNT(*) as c FROM users WHERE role='associate' AND deleted_at IS NULL")->fetch();
            $stats['active_associates'] = (int)($r['c'] ?? 0);
        } catch (\Throwable $e) {}
        try {
            $r = $pdo->query("SELECT COUNT(*) as c FROM plot_bookings WHERE DATE(created_at)=CURDATE()")->fetch();
            $stats['bookings_today'] = (int)($r['c'] ?? 0);
        } catch (\Throwable $e) {}
        try {
            $r = $pdo->query("SELECT COALESCE(SUM(total_plot_value),0) as v FROM plot_bookings WHERE status NOT IN ('cancelled')")->fetch();
            $stats['total_revenue'] = (float)($r['v'] ?? 0);
        } catch (\Throwable $e) {}
        try {
            $r = $pdo->query("SELECT COUNT(*) as c FROM leads WHERE deleted_at IS NULL")->fetch();
            $stats['total_leads'] = (int)($r['c'] ?? 0);
        } catch (\Throwable $e) {}
        try {
            $r = $pdo->query("SELECT COUNT(*) as c FROM leads WHERE lead_category='hot' AND deleted_at IS NULL")->fetch();
            $stats['hot_leads'] = (int)($r['c'] ?? 0);
        } catch (\Throwable $e) {}
        try {
            $r = $pdo->query("SELECT COUNT(*) as c FROM colonies WHERE deleted_at IS NULL")->fetch();
            $stats['total_colonies'] = (int)($r['c'] ?? 0);
        } catch (\Throwable $e) {}
        try {
            $r = $pdo->query("SELECT COUNT(*) as c FROM plots WHERE deleted_at IS NULL")->fetch();
            $stats['total_plots'] = (int)($r['c'] ?? 0);
        } catch (\Throwable $e) {}
        try {
            $r = $pdo->query("SELECT COUNT(*) as c FROM mlm_commission_ledger WHERE status='pending'")->fetch();
            $stats['pending_commissions'] = (int)($r['c'] ?? 0);
        } catch (\Throwable $e) {}

        // Recent activity from CRM interactions
        try {
            $stmt = $pdo->prepare("
                SELECT i.interaction_type, i.subject, i.body, i.created_at, l.name as lead_name
                FROM crm_interactions i
                JOIN leads l ON l.id = i.lead_id
                ORDER BY i.created_at DESC LIMIT 10
            ");
            $stmt->execute();
            $recent_activity = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {}

        $this->json(['success' => true, 'stats' => $stats, 'recent_activity' => $recent_activity]);
    }

    // ─── Admin Employees List ──────────────────────────────────────

    public function adminEmployees() {
        $pdo = $this->db->getConnection();
        $stats = ['total' => 0, 'active' => 0, 'on_leave' => 0, 'inactive' => 0, 'by_role' => []];
        $employees = [];

        try {
            $r = $pdo->query("SELECT COUNT(*) as c FROM users WHERE deleted_at IS NULL AND role IN ('employee','agent','associate')")->fetch();
            $stats['total'] = (int)($r['c'] ?? 0);
        } catch (\Throwable $e) {}
        try {
            $r = $pdo->query("SELECT COUNT(*) as c FROM users WHERE deleted_at IS NULL AND role IN ('employee','agent','associate') AND status='active'")->fetch();
            $stats['active'] = (int)($r['c'] ?? 0);
        } catch (\Throwable $e) {}
        try {
            $r = $pdo->query("SELECT COUNT(*) as c FROM users WHERE deleted_at IS NULL AND role IN ('employee','agent','associate') AND status='inactive'")->fetch();
            $stats['inactive'] = (int)($r['c'] ?? 0);
        } catch (\Throwable $e) {}
        try {
            $r = $pdo->query("SELECT role, COUNT(*) as c FROM users WHERE deleted_at IS NULL AND role IN ('employee','agent','associate') GROUP BY role")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($r as $row) { $stats['by_role'][$row['role']] = (int)$row['c']; }
        } catch (\Throwable $e) {}

        try {
            $search = $_GET['search'] ?? '';
            $roleFilter = $_GET['role'] ?? '';
            $statusFilter = $_GET['status'] ?? '';
            $offset = max(0, (int)($_GET['offset'] ?? 0));
            $limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));

            $where = "u.deleted_at IS NULL AND u.role IN ('employee','agent','associate')";
            $params = [];

            if ($search) {
                $s = "%$search%";
                $where .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
                $params = array_merge($params, [$s, $s, $s]);
            }
            if ($roleFilter) { $where .= " AND u.role = ?"; $params[] = $roleFilter; }
            if ($statusFilter) { $where .= " AND u.status = ?"; $params[] = $statusFilter; }

            $stmt = $pdo->prepare("
                SELECT u.id, u.name, u.email, u.phone, u.role, u.status, u.created_at,
                       e.department, e.designation, e.employee_code as emp_code,
                       a.level as associate_level
                FROM users u
                LEFT JOIN employees e ON e.user_id = u.id
                LEFT JOIN associates a ON a.user_id = u.id
                WHERE $where
                ORDER BY u.name ASC
                LIMIT $limit OFFSET $offset
            ");
            $stmt->execute($params);
            $employees = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {}

        $this->json(['success' => true, 'stats' => $stats, 'employees' => $employees]);
    }

    // ─── Admin Finance Overview ────────────────────────────────────

    public function financeOverview() {
        $pdo = $this->db->getConnection();
        $stats = [
            'todays_collection' => 0, 'pending_emi' => 0,
            'total_outstanding' => 0, 'monthly_target_pct' => 0,
            'monthly_target_amount' => 0, 'collected_this_month' => 0,
            'total_bookings_value' => 0, 'active_emi_count' => 0,
            'overdue_emi_count' => 0, 'total_vendors' => 0,
        ];
        $collections = [];
        $emi_schedule = [];

        // Today's collections
        try {
            $r = $pdo->query("SELECT COALESCE(SUM(paid_amount),0) as v FROM booking_payment_schedules WHERE DATE(payment_date)=CURDATE() AND paid_amount > 0")->fetch();
            $stats['todays_collection'] = (float)($r['v'] ?? 0);
        } catch (\Throwable $e) {}

        // Monthly collection
        try {
            $r = $pdo->query("SELECT COALESCE(SUM(paid_amount),0) as v FROM booking_payment_schedules WHERE MONTH(payment_date)=MONTH(CURDATE()) AND YEAR(payment_date)=YEAR(CURDATE()) AND paid_amount > 0")->fetch();
            $stats['collected_this_month'] = (float)($r['v'] ?? 0);
        } catch (\Throwable $e) {}

        // Pending EMI
        try {
            $r = $pdo->query("SELECT COUNT(*) as c, COALESCE(SUM(emi_amount - paid_amount),0) as v FROM booking_payment_schedules WHERE status='pending' AND due_date < CURDATE()")->fetch();
            $stats['pending_emi'] = (float)($r['v'] ?? 0);
            $stats['overdue_emi_count'] = (int)($r['c'] ?? 0);
        } catch (\Throwable $e) {}

        // Total outstanding
        try {
            $r = $pdo->query("SELECT COALESCE(SUM(emi_amount - paid_amount),0) as v FROM booking_payment_schedules WHERE status IN ('pending','overdue')")->fetch();
            $stats['total_outstanding'] = (float)($r['v'] ?? 0);
        } catch (\Throwable $e) {}

        // Active EMI count
        try {
            $r = $pdo->query("SELECT COUNT(*) as c FROM booking_payment_schedules WHERE status IN ('pending','overdue')")->fetch();
            $stats['active_emi_count'] = (int)($r['c'] ?? 0);
        } catch (\Throwable $e) {}

        // Total bookings value
        try {
            $r = $pdo->query("SELECT COALESCE(SUM(total_plot_value),0) as v FROM plot_bookings WHERE status NOT IN ('cancelled')")->fetch();
            $stats['total_bookings_value'] = (float)($r['v'] ?? 0);
        } catch (\Throwable $e) {}

        // Vendors count
        try {
            $r = $pdo->query("SELECT COUNT(*) as c FROM vendors")->fetch();
            $stats['total_vendors'] = (int)($r['c'] ?? 0);
        } catch (\Throwable $e) {}

        // Recent collections
        try {
            $stmt = $pdo->query("
                SELECT s.id, s.installment_number, s.emi_amount, s.paid_amount, s.due_date, s.payment_date, s.status,
                       pb.booking_number, pb.total_plot_value, u.name as customer_name
                FROM booking_payment_schedules s
                JOIN plot_bookings pb ON pb.id = s.booking_id
                JOIN users u ON u.id = pb.user_id
                WHERE s.paid_amount > 0
                ORDER BY s.payment_date DESC LIMIT 15
            ");
            $collections = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {}

        // Upcoming EMI schedule
        try {
            $stmt = $pdo->query("
                SELECT s.id, s.installment_number, s.emi_amount, s.paid_amount, s.due_date, s.status,
                       pb.booking_number, u.name as customer_name,
                       DATEDIFF(s.due_date, CURDATE()) as days_until_due
                FROM booking_payment_schedules s
                JOIN plot_bookings pb ON pb.id = s.booking_id
                JOIN users u ON u.id = pb.user_id
                WHERE s.status IN ('pending','overdue')
                ORDER BY s.due_date ASC LIMIT 20
            ");
            $emi_schedule = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {}

        $this->json([
            'success' => true, 'stats' => $stats,
            'collections' => $collections, 'emi_schedule' => $emi_schedule,
        ]);
    }

    public function search() {
        $q = $_GET['q'] ?? '';
        if (strlen($q) < 2) {
            $this->json(['success' => true, 'results' => []]);
            return;
        }
        $user = $this->getUser();
        $s = "%$q%";
        $where = "deleted_at IS NULL AND (name LIKE ? OR phone LIKE ? OR email LIKE ? OR lead_number LIKE ?)";
        $params = [$s, $s, $s, $s];

        if (!in_array($user['role'], ['admin', 'manager'])) {
            $where .= " AND assigned_to = ?";
            $params[] = $user['id'];
        }

        $stmt = $this->db->query("SELECT id, lead_number, name, phone, email, status, lead_score, assigned_to FROM leads WHERE $where ORDER BY lead_score DESC LIMIT 20", $params);
        $this->json(['success' => true, 'results' => $stmt->fetchAll() ?: []]);
    }

    // ─── CSV Import ────────────────────────────────────────────────────

    public function importCsv() {
        $user = $this->getUser();
        if (!in_array($user['role'] ?? '', ['admin', 'manager'])) {
            $this->json(['success' => false, 'error' => 'Admin only'], 403);
            return;
        }

        if (empty($_FILES['csv_file'])) {
            $this->json(['success' => false, 'error' => 'No CSV file uploaded'], 400);
            return;
        }

        $file = $_FILES['csv_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'error' => 'Upload error: ' . $file['error']], 400);
            return;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            $this->json(['success' => false, 'error' => 'Cannot read CSV file'], 400);
            return;
        }

        $headers = fgetcsv($handle);
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $rows[] = array_combine($headers, $row);
            }
        }
        fclose($handle);

        $result = $this->crm->importLeadsFromCsv($rows, $user['id']);
        $this->json($result, $result['success'] ? 201 : 400);
    }

    // ─── Deals Pipeline ──────────────────────────────────────────────

    public function deals() {
        $filters = [
            'stage' => $_GET['stage'] ?? null,
            'assigned_to' => $_GET['assigned_to'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 25),
        ];
        $result = $this->crm->getDeals($filters);
        $this->json(['success' => true] + $result);
    }

    public function dealDetail($id) {
        $deal = $this->crm->getDealById($id);
        if (!$deal) {
            $this->json(['success' => false, 'error' => 'Deal not found'], 404);
            return;
        }
        $this->json(['success' => true, 'deal' => $deal]);
    }

    public function createDeal() {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $user = $this->getUser();
        $input['created_by'] = $user['id'];
        if (empty($input['lead_id'])) {
            $this->json(['success' => false, 'error' => 'lead_id required'], 400);
            return;
        }
        $result = $this->crm->createDeal($input);
        $this->json($result, $result['success'] ? 201 : 400);
    }

    public function updateDeal($id) {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $result = $this->crm->updateDeal($id, $input);
        $this->json($result);
    }

    public function moveDealStage($id) {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        if (empty($input['stage'])) {
            $this->json(['success' => false, 'error' => 'stage required'], 400);
            return;
        }
        $result = $this->crm->moveDealStage($id, $input['stage']);
        $this->json($result);
    }

    public function deleteDeal($id) {
        $result = $this->crm->deleteDeal($id);
        $this->json($result);
    }

    public function dealPipeline() {
        $summary = $this->crm->getDealPipelineSummary();
        $this->json(['success' => true] + $summary);
    }

    // ─── Score Breakdown ─────────────────────────────────────────────

    public function scoreBreakdown($leadId) {
        $breakdown = $this->crm->getScoreBreakdown($leadId);
        $this->json(['success' => true] + $breakdown);
    }

    // ─── Follow-up Reminders ─────────────────────────────────────────

    public function followUpReminders() {
        $user = $this->getUser();
        $reminders = $this->crm->getFollowUpReminders($user['id'], $user['role'] ?? 'associate');
        $overdueCount = $this->crm->getOverdueRemindersCount($user['id'], $user['role'] ?? 'associate');
        $this->json(['success' => true, 'reminders' => $reminders, 'overdue_count' => $overdueCount]);
    }

    // ─── Bulk Operations ─────────────────────────────────────────────

    public function bulkUpdate() {
        $user = $this->getUser();
        if (!in_array($user['role'] ?? '', ['admin', 'manager'])) {
            $this->json(['success' => false, 'error' => 'Admin only'], 403);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $leadIds = $input['lead_ids'] ?? [];
        $updates = $input['updates'] ?? [];

        if (empty($leadIds) || empty($updates)) {
            $this->json(['success' => false, 'error' => 'lead_ids and updates required'], 400);
            return;
        }

        $result = $this->crm->bulkUpdateLeads($leadIds, $updates, $user['id']);
        $this->json($result);
    }

    // ─── Timeline ────────────────────────────────────────────────────

    public function leadTimeline($leadId) {
        $limit = (int)($_GET['limit'] ?? 50);
        $timeline = $this->crm->getLeadTimeline($leadId, $limit);
        $this->json(['success' => true, 'timeline' => $timeline]);
    }

    // ─── Commission Calculator ───────────────────────────────────────

    public function commissionEstimate($leadId) {
        $estimate = $this->crm->estimateCommission($leadId);
        $this->json(['success' => true] + $estimate);
    }

    // ─── Analytics ───────────────────────────────────────────────────

    public function sourceAnalytics() {
        $period = $_GET['period'] ?? '30d';
        $result = $this->crm->getSourceAnalytics($period);
        $this->json(['success' => true] + $result);
    }

    public function conversionFunnel() {
        $result = $this->crm->getConversionFunnel();
        $this->json(['success' => true] + $result);
    }

    public function agentPerformance() {
        $result = $this->crm->getAgentPerformance();
        $this->json(['success' => true, 'performance' => $result]);
    }
}
