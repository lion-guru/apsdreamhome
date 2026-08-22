<?php
/**
 * CRM API Controller — REST endpoints for Flutter CRM
 * Handles: pipeline, leads CRUD, interactions, tasks, assignments, analytics
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\CRMService;
use App\Models\BookingPaymentSchedule;
use App\Models\PlotBooking;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Middleware\RateLimiter;
use \App\Traits\TenantAwareTrait;

class CRMController extends BaseController
{
    use TenantAwareTrait;

    private $crm;

    public function __construct() {
        parent::__construct();
        $this->crm = new CRMService();
    }

    protected function skipCsrfProtection(): bool
    {
        return false;
    }

    private function getUser() {
        // Check session first (web admin panel)
        $userId = $_SESSION['user_id'] ?? null;
        $role = $_SESSION['role'] ?? null;

        // Fallback: check Bearer token (Flutter mobile app)
        if (!$userId) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $m)) {
                $token = $m[1];
                try {
                    $db = \App\Core\Database::getInstance()->getPdo();
                    $stmt = $db->prepare("SELECT user_id FROM api_tokens WHERE token = ? AND expires_at > NOW() LIMIT 1");
                    $stmt->execute([$token]);
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                    if ($row) {
                        $userId = (int)$row['user_id'];
                        // Fetch user role
                        $stmt2 = $db->prepare("SELECT role FROM users WHERE id = ?");
                        $stmt2->execute([$userId]);
                        $u = $stmt2->fetch(\PDO::FETCH_ASSOC);
                        $role = $u['role'] ?? 'associate';
                    }
                } catch (\Throwable $e) {
                // ignore
                error_log($e->getMessage());
                }
            }
        }

        // Also check $GLOBALS set by ApiAuthMiddleware
        if (!$userId) {
            $userId = $GLOBALS['api_user_id'] ?? null;
            $role = $GLOBALS['api_user_role'] ?? null;
        }

        return [
            'id' => $userId,
            'role' => $role,
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
        if ($result['success'] && !empty($result['lead_id'])) {
            // Fetch the full lead object so Flutter can parse it
            $lead = $this->crm->getLeadById($result['lead_id']);
            $result['data'] = $lead ?? $result;
        }
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
        // Rate limiting for public form submissions (30 requests per minute per IP)
        \App\Middleware\RateLimiter::check('capture_form', 30, 60);
        
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
        $tid = (int)$this->tenantId();
        $tidSql = $tid > 1 ? " AND leads.tenant_id = ?" : "";
        
        $uFilter = $tid > 1 ? "tenant_id = ?" : "";
        $uFilterParams = $tid > 1 ? [$tid] : [];

        // Use Query Builder for all queries to prevent SQL injection
        $query = User::query()->where('deleted_at', null);
        if ($tid > 1) {
            $query->where('tenant_id', $tid);
        }
        $stats['total_users'] = $query->count();

        $stats['active_associates'] = User::query()
            ->where('deleted_at', null)
            ->where('role', 'associate')
            ->when($tid > 1, fn($q) => $q->where('tenant_id', $tid))
            ->count();

        $stats['bookings_today'] = PlotBooking::query()
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $stats['total_revenue'] = PlotBooking::query()
            ->where('status', '!=', 'cancelled')
            ->sum('total_plot_value');

        $stats['total_leads'] = Lead::query()
            ->whereNull('deleted_at')
            ->when($tid > 1, fn($q) => $q->where('tenant_id', $tid))
            ->count();

        $stats['hot_leads'] = Lead::query()
            ->where('lead_category', 'hot')
            ->whereNull('deleted_at')
            ->when($tid > 1, fn($q) => $q->where('tenant_id', $tid))
            ->count();

        $stats['total_colonies'] = Colony::query()
            ->whereNull('deleted_at')
            ->count();

        $stats['total_plots'] = \App\Models\Plot::query()
            ->whereNull('deleted_at')
            ->count();

        $stats['pending_commissions'] = \App\Models\MlmCommissionLedger::query()
            ->where('status', 'pending')
            ->count();

        // Recent activity from CRM interactions
        $recent_activity = \App\Models\CrmInteraction::query()
            ->join('leads', 'leads.id', '=', 'crm_interactions.lead_id')
            ->select('crm_interactions.interaction_type', 'crm_interactions.subject', 'crm_interactions.body', 'crm_interactions.created_at', 'leads.name as lead_name')
            ->when($tid > 1, fn($q) => $q->where('leads.tenant_id', $tid))
            ->orderBy('crm_interactions.created_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        $stats = [
            'total_users' => User::query()->where('deleted_at', null)->when($tid > 1, fn($q) => $q->where('tenant_id', $tid))->count(),
            'active_associates' => User::query()
                ->where('deleted_at', null)
                ->where('role', 'associate')
                ->when($tid > 1, fn($q) => $q->where('tenant_id', $tid))
                ->count(),
            'bookings_today' => PlotBooking::query()
                ->whereDate('created_at', now()->toDateString())
                ->count(),
            'total_revenue' => PlotBooking::query()
                ->where('status', '!=', 'cancelled')
                ->sum('total_plot_value'),
            'pending_commissions' => \App\Models\MlmCommissionLedger::query()
                ->where('status', 'pending')
                ->count(),
            'total_leads' => Lead::query()
                ->whereNull('deleted_at')
                ->when($tid > 1, fn($q) => $q->where('tenant_id', $tid))
                ->count(),
            'hot_leads' => Lead::query()
                ->where('lead_category', 'hot')
                ->whereNull('deleted_at')
                ->when($tid > 1, fn($q) => $q->where('tenant_id', $tid))
                ->count(),
            'total_colonies' => \App\Models\Colony::query()
                ->whereNull('deleted_at')
                ->count(),
            'total_plots' => \App\Models\Plot::query()
                ->whereNull('deleted_at')
                ->count(),
            'pending_commissions' => \App\Models\MlmCommissionLedger::query()
                ->where('status', 'pending')
                ->count(),
        ];

        // Recent activity from CRM interactions
        $recent_activity = \App\Models\CrmInteraction::query()
            ->join('leads', 'leads.id', '=', 'crm_interactions.lead_id')
            ->select('crm_interactions.interaction_type', 'crm_interactions.subject', 'crm_interactions.body', 'crm_interactions.created_at', 'leads.name as lead_name')
            ->when($tid > 1, fn($q) => $q->where('leads.tenant_id', $tid))
            ->orderBy('crm_interactions.created_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        $this->json(['success' => true, 'stats' => $stats, 'recent_activity' => $recent_activity]);
    }

    // ─── Admin Employees List ──────────────────────────────────────

    public function adminEmployees() {
        $pdo = $this->db->getConnection();
        $tid2 = (int)$this->tenantId();
        $uFilter2 = $tid2 > 1 ? ' AND tenant_id = ?' : '';
        $uParam2 = $tid2 > 1 ? [$tid2] : [];
        $stats = ['total' => 0, 'active' => 0, 'on_leave' => 0, 'inactive' => 0, 'by_role' => []];
        $employees = [];

        try {
            $r = $pdo->query("SELECT COUNT(*) as c FROM users WHERE deleted_at IS NULL AND role IN ('employee','agent','associate'){$uFilter2}", $uParam2)->fetch();
            $stats['total'] = (int)($r['c'] ?? 0);
        } catch (\Throwable $e) { error_log("CRMController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }
        try {
            $r = $pdo->query("SELECT COUNT(*) as c FROM users WHERE deleted_at IS NULL AND role IN ('employee','agent','associate') AND status='active'{$uFilter2}", $uParam2)->fetch();
            $stats['active'] = (int)($r['c'] ?? 0);
        } catch (\Throwable $e) { error_log("CRMController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }
        try {
            $r = $pdo->query("SELECT COUNT(*) as c FROM users WHERE deleted_at IS NULL AND role IN ('employee','agent','associate') AND status='inactive'{$uFilter2}")->fetch();
            $stats['inactive'] = (int)($r['c'] ?? 0);
        } catch (\Throwable $e) { error_log("CRMController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }
        try {
            $r = $pdo->query("SELECT role, COUNT(*) as c FROM users WHERE deleted_at IS NULL AND role IN ('employee','agent','associate'){$uFilter2} GROUP BY role")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($r as $row) { $stats['by_role'][$row['role']] = (int)$row['c']; }
        } catch (\Throwable $e) { error_log("CRMController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

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
        } catch (\Throwable $e) { error_log("CRMController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $this->json(['success' => true, 'stats' => $stats, 'employees' => $employees]);
    }

    use App\Models\BookingPaymentSchedule;
use App\Models\PlotBooking;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

// ─── Admin Finance Overview ────────────────────────────────────

    public function financeOverview() {
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
        $stats['todays_collection'] = (float) BookingPaymentSchedule::query()
            ->whereDate('payment_date', now()->toDateString())
            ->where('paid_amount', '>', 0)
            ->sum('paid_amount');

        // Monthly collection
        $stats['collected_this_month'] = (float) BookingPaymentSchedule::query()
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->where('paid_amount', '>', 0)
            ->sum('paid_amount');

        // Pending EMI
        $pendingEmi = BookingPaymentSchedule::query()
            ->where('status', 'pending')
            ->whereDate('due_date', '<', now()->toDateString())
            ->selectRaw('COUNT(*) as c, COALESCE(SUM(emi_amount - paid_amount),0) as v')
            ->first();
        $stats['pending_emi'] = (float)($pendingEmi->v ?? 0);
        $stats['overdue_emi_count'] = (int)($pendingEmi->c ?? 0);

        // Total outstanding
        $stats['total_outstanding'] = (float) BookingPaymentSchedule::query()
            ->whereIn('status', ['pending', 'overdue'])
            ->sum(DB::raw('emi_amount - paid_amount'));

        // Active EMI count
        $stats['active_emi_count'] = BookingPaymentSchedule::query()
            ->whereIn('status', ['pending', 'overdue'])
            ->count();

        // Total bookings value
        $stats['total_bookings_value'] = (float) PlotBooking::query()
            ->where('status', '!=', 'cancelled')
            ->sum('total_plot_value');

        // Vendors count
        $stats['total_vendors'] = Vendor::count();

        // Recent collections
        $collections = BookingPaymentSchedule::query()
            ->join('plot_bookings', 'plot_bookings.id', '=', 'booking_payment_schedules.booking_id')
            ->join('users', 'users.id', '=', 'plot_bookings.customer_id')
            ->select(
                'booking_payment_schedules.id',
                'booking_payment_schedules.installment_number',
                'booking_payment_schedules.emi_amount',
                'booking_payment_schedules.paid_amount',
                'booking_payment_schedules.due_date',
                'booking_payment_schedules.payment_date',
                'booking_payment_schedules.status',
                'plot_bookings.booking_number',
                'plot_bookings.total_plot_value',
                'users.name as customer_name'
            )
            ->where('booking_payment_schedules.paid_amount', '>', 0)
            ->orderBy('booking_payment_schedules.payment_date', 'desc')
            ->limit(15)
            ->get()
            ->toArray();

        // Upcoming EMI schedule
        $emi_schedule = BookingPaymentSchedule::query()
            ->join('plot_bookings', 'plot_bookings.id', '=', 'booking_payment_schedules.booking_id')
            ->join('users', 'users.id', '=', 'plot_bookings.customer_id')
            ->select(
                'booking_payment_schedules.id',
                'booking_payment_schedules.installment_number',
                'booking_payment_schedules.emi_amount',
                'booking_payment_schedules.paid_amount',
                'booking_payment_schedules.due_date',
                'booking_payment_schedules.status',
                'plot_bookings.booking_number',
                'users.name as customer_name',
                DB::raw('DATEDIFF(booking_payment_schedules.due_date, CURDATE()) as days_until_due')
            )
            ->whereIn('booking_payment_schedules.status', ['pending', 'overdue'])
            ->orderBy('booking_payment_schedules.due_date', 'asc')
            ->limit(20)
            ->get()
            ->toArray();

        $stats = [
            'todays_collection' => BookingPaymentSchedule::query()
                ->whereDate('payment_date', now()->toDateString())
                ->where('paid_amount', '>', 0)
                ->sum('paid_amount'),
            'pending_emi' => (float)($pendingEmi->v ?? 0),
            'total_outstanding' => (float) BookingPaymentSchedule::query()
                ->whereIn('status', ['pending', 'overdue'])
                ->sum(DB::raw('emi_amount - paid_amount')),
            'monthly_target_pct' => 0,
            'monthly_target_amount' => 0,
            'collected_this_month' => (float) BookingPaymentSchedule::query()
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->where('paid_amount', '>', 0)
                ->sum('paid_amount'),
            'total_bookings_value' => (float) PlotBooking::query()
                ->where('status', '!=', 'cancelled')
                ->sum('total_plot_value'),
            'active_emi_count' => BookingPaymentSchedule::query()
                ->whereIn('status', ['pending', 'overdue'])
                ->count(),
            'overdue_emi_count' => (int)($pendingEmi->c ?? 0),
            'total_vendors' => Vendor::count(),
        ];

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

        $tid = (int)$this->tenantId();
        if ($tid > 1) {
            $where .= " AND tenant_id = ?";
            $params[] = $tid;
        }

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

        // Enforce max file size (10 MB)
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $this->json(['success' => false, 'error' => 'File too large. Maximum 10MB allowed.'], 400);
            return;
        }

        // Validate file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'], true)) {
            $this->json(['success' => false, 'error' => 'Only CSV and TXT files are allowed'], 400);
            return;
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mimeType, ['text/csv', 'text/plain', 'text/x-csv', 'application/vnd.ms-excel', 'text/comma-separated-values'], true)) {
            $this->json(['success' => false, 'error' => 'Invalid file type. Only CSV files are accepted.'], 400);
            return;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            $this->json(['success' => false, 'error' => 'Cannot read CSV file'], 400);
            return;
        }

        $headers = fgetcsv($handle);
        $rows = [];
        $maxRows = 10000; // Prevent DoS via extremely large files
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $rows[] = array_combine($headers, $row);
            }
            if (count($rows) >= $maxRows) {
                fclose($handle);
                $this->json(['success' => false, 'error' => "File exceeds maximum of {$maxRows} rows"], 400);
                return;
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
