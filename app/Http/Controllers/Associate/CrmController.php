<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * AssociateCrmController
 * Handles associate CRM/leads management
 */
class CrmController extends BaseController
{
    use TenantAwareTrait;

    /**
     * Stamp speed-to-lead clock: first associate action wins, never overwritten.
     * Called after successful note/status/assign writes (row already proven in-scope).
     */
    private function stampFirstResponse($db, $id)
    {
        $db->execute("UPDATE leads SET first_response_at = NOW() WHERE id = ? AND first_response_at IS NULL", [$id]);
    }

    /**
     * Normalize Indian phone numbers for dedupe + storage: digits-only, strip
     * +91 / leading 0, keep the 10-digit tail. Short/foreign numbers pass
     * through untouched (never mangle what we don't understand).
     */
    private function normalizePhone($phone)
    {
        $d = preg_replace('/\D/', '', (string)$phone);
        if (strlen($d) === 12 && substr($d, 0, 2) === '91') return substr($d, -10);
        if (strlen($d) === 11 && $d[0] === '0') return substr($d, -10);
        return $d;
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Require associate authentication
     */
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
        }
    }

    /**
     * CRM Dashboard
     */
    public function crmDashboard()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        try {
            $db = \App\Core\Database\Database::getInstance();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            // Lead stats (trashed rows excluded throughout)
            $stats = [
                'total' => (int)$db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE assigned_to = ? AND deleted_at IS NULL{$tidSql}", $params)['count'] ?? 0,
                'new' => (int)$db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE assigned_to = ? AND status = 'new' AND deleted_at IS NULL{$tidSql}", $params)['count'] ?? 0,
                'contacted' => (int)$db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE assigned_to = ? AND status = 'contacted' AND deleted_at IS NULL{$tidSql}", $params)['count'] ?? 0,
                'qualified' => (int)$db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE assigned_to = ? AND status = 'qualified' AND deleted_at IS NULL{$tidSql}", $params)['count'] ?? 0,
                'proposal' => (int)$db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE assigned_to = ? AND status = 'proposal' AND deleted_at IS NULL{$tidSql}", $params)['count'] ?? 0,
                'won' => (int)$db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE assigned_to = ? AND status = 'won' AND deleted_at IS NULL{$tidSql}", $params)['count'] ?? 0,
                'lost' => (int)$db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE assigned_to = ? AND status = 'lost' AND deleted_at IS NULL{$tidSql}", $params)['count'] ?? 0,
            ];

            // Recent leads
            $recentLeads = $db->fetchAll("SELECT l.*, u.name as assigned_name FROM leads l LEFT JOIN users u ON u.id = l.assigned_to WHERE l.assigned_to = ? AND l.deleted_at IS NULL{$tidSql} ORDER BY l.created_at DESC LIMIT 10", $params) ?: [];

            // Followups due today/overdue
            $followups = $db->fetchAll("SELECT * FROM followups WHERE assigned_to = ? AND followup_date <= CURDATE() AND status = 'pending'{$tidSql} ORDER BY followup_date ASC LIMIT 10", $params) ?: [];

            $this->render('associate/crm_dashboard', [
                'page_title' => 'CRM Dashboard - Associate Portal',
                'page_description' => 'Manage your leads and pipeline',
                'stats' => $stats,
                'recent_leads' => $recentLeads,
                'followups' => $followups,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('AssociateCrmController error: ' . $e->getMessage());
        }
    }

    /**
     * List all leads
     */
    public function leads()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $status = $_GET['status'] ?? '';
        // Legacy tab keys predate the closed_won/closed_lost enum rename — map them.
        if ($status === 'won') $status = 'closed_won';
        if ($status === 'lost') $status = 'closed_lost';
        // View submits search as `q` (form + filterLeads JS); accept legacy `search` too.
        $search = trim($_GET['search'] ?? $_GET['q'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        $where = "WHERE (l.assigned_to = ? OR l.created_by = ?){$tidSql} AND l.deleted_at IS NULL";
        $params = [$userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        if ($status) {
            $where .= " AND l.status = ?";
            $params[] = $status;
        }
        if ($search) {
            $where .= " AND (l.name LIKE ? OR l.phone LIKE ? OR l.email LIKE ? OR EXISTS (SELECT 1 FROM lead_notes n WHERE n.lead_id = l.id AND n.note LIKE ?))";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $total = (int)$db->fetchOne("SELECT COUNT(*) as count FROM leads l {$where}", $params)['count'] ?? 0;
        $totalPages = max(1, ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $leads = $db->fetchAll("SELECT l.*, u.name as assigned_name FROM leads l LEFT JOIN users u ON u.id = l.assigned_to {$where} ORDER BY l.created_at DESC LIMIT {$perPage} OFFSET {$offset}", $params) ?: [];

        // Pipeline tab counts — same scope as the list (assigned OR created),
        // unfiltered by the active tab/search. View keys: new/contacted/
        // qualified/site_visit/proposal/negotiation/closed_won/closed_lost/nurture.
        // S264: single GROUP BY (was: 9 sequential COUNTs + 1 total = 12 queries/hit).
        $countBase = "FROM leads l WHERE (l.assigned_to = ? OR l.created_by = ?){$tidSql} AND l.deleted_at IS NULL";
        $countParams = [$userId, $userId];
        if (TenantContext::getId() > 1) $countParams[] = TenantContext::getId();
        $pipelineCounts = array_fill_keys(['new', 'contacted', 'qualified', 'site_visit', 'proposal', 'negotiation', 'closed_won', 'closed_lost', 'nurture'], 0);
        $unfilteredTotal = 0;
        $groupRows = $db->fetchAll(
            "SELECT l.status AS st, COUNT(*) AS count {$countBase} GROUP BY l.status",
            $countParams
        ) ?: [];
        foreach ($groupRows as $gr) {
            $c = (int)($gr['count'] ?? 0);
            $unfilteredTotal += $c;
            if (array_key_exists((string)($gr['st'] ?? ''), $pipelineCounts)) {
                $pipelineCounts[(string)$gr['st']] = $c;
            }
        }

        // Speed-to-lead SLA: own leads with NO associate action yet, older than 30 min.
        $slaRow = $db->fetchOne(
            "SELECT COUNT(*) as count {$countBase} AND l.first_response_at IS NULL AND l.created_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)",
            $countParams
        );
        $slaBreach = (int)($slaRow['count'] ?? 0);
        // Stale tier: open-pipeline leads untouched 14+ days (revive queue feeds on 30d).
        $staleRow = $db->fetchOne(
            "SELECT COUNT(*) as count {$countBase} AND l.status NOT IN ('closed_won','closed_lost') AND l.updated_at < DATE_SUB(NOW(), INTERVAL 14 DAY)",
            $countParams
        );
        $staleCount = (int)($staleRow['count'] ?? 0);

        // Pagination base URL preserving active filters (view appends `page=N`).
        $pq = [];
        if ($status !== '') $pq[] = 'status=' . urlencode($status);
        if ($search !== '') $pq[] = 'search=' . urlencode($search);
        $paginationUrl = BASE_URL . '/associate/leads?' . ($pq ? implode('&', $pq) . '&' : '');

        $this->render('associate/leads', [
            'page_title' => 'My Leads - Associate Portal',
            'page_description' => 'Manage your leads',
            'leads' => $leads,
            'status' => $status,
            'status_filter' => $status,
            'search' => $search,
            'page' => $page,
            'current_page_no' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
            'total_count' => $unfilteredTotal,
            'pipeline_counts' => $pipelineCounts,
            'sla_breach' => $slaBreach,
            'stale_count' => $staleCount,
            'pagination_url' => $paginationUrl,
            'statuses' => ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'],
        ], 'layouts/associate');
    }

    /**
     * Add new lead
     */
    public function addLead()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = TenantContext::getId() > 1 ? [TenantContext::getId()] : [];

        // Get sources
        $sources = $db->fetchAll("SELECT * FROM lead_sources WHERE is_active = 1 ORDER BY name") ?: [];

        $this->render('associate/add_lead', [
            'page_title' => 'Add Lead - Associate Portal',
            'page_description' => 'Add a new lead',
            'sources' => $sources,
        ], 'layouts/associate');
    }

    /**
     * Store new lead
     */
    public function storeLead()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/leads/add');
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance();

            $phone = $this->normalizePhone(trim($_POST['phone'] ?? ''));
            $budgetMin = (float)($_POST['budget_min'] ?? 0);
            $budgetMax = (float)($_POST['budget_max'] ?? 0);
            $budget = $budgetMax > 0 ? $budgetMax : $budgetMin;
            $budgetRange = ($budgetMin || $budgetMax) ? "₹" . number_format($budgetMin) . " - ₹" . number_format($budgetMax) : '';
            $propertyInterest = trim($_POST['property_type'] ?? ($_POST['property_interest'] ?? ''));

            // Check for duplicate phone
            $existing = $db->fetchOne("SELECT id, assigned_to FROM leads WHERE phone = ? AND tenant_id = ?", [$phone, $tid]);
            if ($existing) {
                if (empty($existing['assigned_to']) || (int)$existing['assigned_to'] === (int)$_SESSION['user_id']) {
                    $db->query("UPDATE leads SET assigned_to = ?, created_by = COALESCE(created_by, ?), name = ?, email = ?, property_interest = ?, budget = ?, budget_range = ?, location_preference = ?, notes = ?, updated_at = NOW() WHERE id = ?", [
                        $_SESSION['user_id'],
                        $_SESSION['user_id'],
                        trim($_POST['name'] ?? ''),
                        trim($_POST['email'] ?? ''),
                        $propertyInterest,
                        $budget,
                        $budgetRange,
                        trim($_POST['location_preference'] ?? ''),
                        trim($_POST['notes'] ?? ''),
                        $existing['id']
                    ]);
                    $_SESSION['success'] = 'Lead updated successfully!';
                    $this->redirect("/associate/leads/{$existing['id']}");
                    return;
                } else {
                    $_SESSION['info'] = 'Lead with this phone already exists';
                    $this->redirect("/associate/leads/{$existing['id']}");
                    return;
                }
            }

            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'phone' => $phone,
                'email' => trim($_POST['email'] ?? ''),
                'city' => $_POST['city'] ?? '',
                'budget' => $budget,
                'budget_range' => $budgetRange,
                'property_interest' => $propertyInterest,
                'source_id' => (int)($_POST['source_id'] ?? 0) ?: null,
                'priority' => in_array($_POST['priority'] ?? '', ['low','medium','high']) ? $_POST['priority'] : 'medium',
                'notes' => trim($_POST['notes'] ?? ''),
                'message' => trim($_POST['message'] ?? ''),
                'assigned_to' => $_SESSION['user_id'],
                'created_by' => $_SESSION['user_id'],
                'status' => 'new',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'tenant_id' => $tid,
            ];

            $cols = array_keys($data);
            $vals = array_fill(0, count($cols), '?');
            $stmt = $db->prepare("INSERT INTO leads (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")");
            $stmt->execute(array_values($data));
            $leadId = (int)$db->lastInsertId();

            // Add initial note if provided
            if (!empty($_POST['notes'])) {
                $db->insert('lead_notes', [
                    'lead_id' => $leadId,
                    'created_by' => $_SESSION['user_id'],
                    'note' => trim($_POST['notes']),
                    'content' => trim($_POST['notes']),
                    'created_at' => date('Y-m-d H:i:s'),
                    'tenant_id' => $tid,
                ]);
            }

            // Add activity
            $this->logActivity('lead_created', json_encode(['lead_id' => $leadId]));

            // Lead-automation rules (best-effort: never break the create path).
            try {
                (new \App\Services\LeadWorkflowService())->handle('lead_created', $leadId, $userId);
            } catch (\Throwable $e) {
                error_log('LeadWorkflow lead_created: ' . $e->getMessage());
            }

            $_SESSION['success'] = 'Lead added successfully!';
            $this->redirect("/associate/leads/{$leadId}");
        } catch (\Throwable $e) {
            error_log('AssociateCrmController::storeLead error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to add lead: ' . $e->getMessage();
            $this->redirect('/associate/leads/add');
        }
    }

    /**
     * Lead detail
     */
    public function leadDetail($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$id, $userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $lead = $db->fetchOne("SELECT l.*, u.name as assigned_name FROM leads l LEFT JOIN users u ON u.id = l.assigned_to WHERE l.id = ? AND (l.assigned_to = ? OR l.created_by = ?){$tidSql} AND l.deleted_at IS NULL LIMIT 1", $params);

        if (!$lead) {
            $_SESSION['error'] = 'Lead not found or access denied';
            $this->redirect('/associate/leads');
            return;
        }

        // Get notes
        $notes = $db->fetchAll("SELECT ln.*, u.name as user_name FROM lead_notes ln LEFT JOIN users u ON u.id = ln.created_by WHERE ln.lead_id = ? ORDER BY ln.created_at DESC", [$id]) ?: [];

        // Get activities
        $activities = $db->fetchAll("SELECT * FROM lead_activities WHERE lead_id = ? ORDER BY created_at DESC LIMIT 20", [$id]) ?: [];

        // Get followups
        $followups = $db->fetchAll("SELECT * FROM followups WHERE lead_id = ? ORDER BY followup_date DESC", [$id]) ?: [];

        $sources = $db->fetchAll("SELECT * FROM lead_sources WHERE is_active = 1 ORDER BY name") ?: [];

        $this->render('associate/lead_detail', [
            'page_title' => 'Lead Detail - Associate Portal',
            'page_description' => 'View and manage lead',
            'lead' => $lead,
            'notes' => $notes,
            'activities' => $activities,
            'followups' => $followups,
            'sources' => $sources,
        ], 'layouts/associate');
    }

    /**
     * Update lead status
     */
    public function updateLeadStatus($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/associate/leads/{$id}");
            return;
        }

        $status = $_POST['status'] ?? '';
        // Must match the detail view's pipeline keys AND the DB enum
        // (migration adds site_visit; won/lost never existed — closed_* do).
        $validStatuses = ['new', 'contacted', 'qualified', 'site_visit', 'proposal', 'negotiation', 'closed_won', 'closed_lost', 'nurture'];
        if (!in_array($status, $validStatuses)) {
            $_SESSION['error'] = 'Invalid status';
            $this->redirect("/associate/leads/{$id}");
            return;
        }

        // Lost needs a reason — otherwise the loss teaches nothing.
        $validReasons = ['price', 'location', 'loan', 'competitor', 'no_response', 'other'];
        $lostReason = trim($_POST['lost_reason'] ?? '');
        if ($status === 'closed_lost' && !in_array($lostReason, $validReasons, true)) {
            $_SESSION['error'] = 'Pick a lost reason';
            $this->redirect("/associate/leads/{$id}");
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        // Old status first — the timeline renders old → new.
        $oldParams = [$id, $userId, $userId];
        if (TenantContext::getId() > 1) $oldParams[] = TenantContext::getId();
        $oldRow = $db->fetchOne("SELECT status FROM leads WHERE id = ? AND (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL LIMIT 1", $oldParams);
        // Scope matches list + delete (assigned OR created) — detail is reachable from both.
        // Reopening clears the reason; closing stores it.
        $params = [$status, ($status === 'closed_lost' ? $lostReason : null), $id, $userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $stmt = $db->prepare("UPDATE leads SET status = ?, lost_reason = ?, updated_at = NOW() WHERE id = ? AND (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL");
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            // Timeline feed (lead_activities is what the detail view renders).
            $db->insert('lead_activities', [
                'lead_id' => $id,
                'activity_type' => 'status_change',
                'description' => $status === 'closed_lost' ? 'Closed lost: ' . $lostReason : 'Status updated',
                'old_value' => $oldRow['status'] ?? '',
                'new_value' => $status,
                'created_by' => $userId,
                'tenant_id' => $tid,
            ]);
            $this->logActivity('lead_status_changed', json_encode(['lead_id' => $id, 'new_status' => $status, 'lost_reason' => $status === 'closed_lost' ? $lostReason : null]));
            $this->stampFirstResponse($db, $id);
            try {
                (new \App\Services\LeadWorkflowService())->handle('status_changed', $id, $userId, ['new_status' => $status]);
            } catch (\Throwable $e) {
                error_log('LeadWorkflow status_changed: ' . $e->getMessage());
            }
            $_SESSION['success'] = 'Lead status updated!';
        } else {
            $_SESSION['error'] = 'Lead not found or access denied';
        }

        $this->redirect("/associate/leads/{$id}");
    }

    /**
     * Add lead note
     */
    public function addLeadNote($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/associate/leads/{$id}");
            return;
        }

        $note = trim($_POST['note'] ?? '');
        if (empty($note)) {
            $_SESSION['error'] = 'Note cannot be empty';
            $this->redirect("/associate/leads/{$id}");
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        // Scope matches list + delete + status (assigned OR created).
        $params = [$id, $userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        // Verify ownership
        $stmt = $db->prepare("SELECT id FROM leads WHERE id = ? AND (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL LIMIT 1");
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = 'Lead not found or access denied';
            $this->redirect('/associate/leads');
            return;
        }

        // Columns match the live table (lead_id/note/content/created_by — there is no user_id col).
        $db->insert('lead_notes', [
            'lead_id' => $id,
            'created_by' => $userId,
            'note' => $note,
            'content' => $note,
            'created_at' => date('Y-m-d H:i:s'),
            'tenant_id' => $tid,
        ]);

        // Schedule follow-up when the form's Schedule button was used (else the button silently only saves a note).
        if (!empty($_POST['schedule_followup']) && !empty($_POST['followup_date'])) {
            $db->insert('followups', [
                'lead_id' => $id,
                'assigned_to' => $userId,
                'followup_type' => $_POST['task_type'] ?? 'call',
                'followup_date' => $_POST['followup_date'],
                'followup_time' => !empty($_POST['followup_time']) ? $_POST['followup_time'] : null,
                'notes' => $note,
                'status' => 'pending',
                'tenant_id' => $tid,
            ]);
            $_SESSION['success'] = 'Note added & follow-up scheduled!';
        } else {
            $_SESSION['success'] = 'Note added!';
        }

        // Timeline feed (lead_activities is what the detail view renders).
        $db->insert('lead_activities', [
            'lead_id' => $id,
            'activity_type' => 'note',
            'description' => $note,
            'created_by' => $userId,
            'tenant_id' => $tid,
        ]);

        $this->logActivity('lead_note_added', json_encode(['lead_id' => $id]));
        $this->stampFirstResponse($db, $id);
        try {
            (new \App\Services\LeadWorkflowService())->handle('note_added', $id, $userId);
        } catch (\Throwable $e) {
            error_log('LeadWorkflow note_added: ' . $e->getMessage());
        }

        $this->redirect("/associate/leads/{$id}");
    }

    /**
     * Delete lead
     */
    public function deleteLead($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        // Scope matches the list view (assigned_to OR created_by) — the Delete
        // button renders for every listed row, so all of them must be deletable.
        $params = [$id, $userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $stmt = $db->prepare("UPDATE leads SET deleted_at = NOW() WHERE id = ? AND (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL");
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            // Soft delete only — children (notes/followups/activities/scores)
            // stay intact so Restore brings everything back.
            $this->logActivity('lead_deleted', json_encode(['lead_id' => $id, 'mode' => 'trash']));
            $_SESSION['success'] = 'Lead moved to Trash!';
        } else {
            $_SESSION['error'] = 'Lead not found or access denied';
        }

        $this->redirect('/associate/leads');
    }

    /**
     * Bulk status/assign for own in-scope leads (ids intersected with scope —
     * forged ids silently skip). Timeline rows per affected lead.
     */
    public function bulkUpdate()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/leads');
            return;
        }

        $ids = [];
        foreach ((array)($_POST['ids'] ?? []) as $v) {
            $v = (int)$v;
            if ($v > 0) $ids[] = $v;
        }
        $ids = array_values(array_unique($ids));
        if (empty($ids) || count($ids) > 200) {
            $_SESSION['error'] = empty($ids) ? 'Select at least one lead' : 'Max 200 at a time';
            $this->redirect('/associate/leads');
            return;
        }

        $action = $_POST['bulk_action'] ?? '';
        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $ph = implode(',', array_fill(0, count($ids), '?'));

        if ($action === 'status') {
            $to = $_POST['bulk_status'] ?? '';
            if (!in_array($to, ['new', 'contacted', 'qualified', 'site_visit', 'proposal', 'negotiation', 'closed_won', 'closed_lost', 'nurture'], true)) {
                $_SESSION['error'] = 'Invalid status';
                $this->redirect('/associate/leads');
                return;
            }
            $params = array_merge($ids, [$userId, $userId]);
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();
            $rows = $db->fetchAll("SELECT id, status FROM leads WHERE id IN ($ph) AND (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL", $params) ?: [];
            $n = 0;
            foreach ($rows as $r) {
                $db->execute("UPDATE leads SET status = ?, updated_at = NOW() WHERE id = ?", [$to, $r['id']]);
                $db->insert('lead_activities', [
                    'lead_id' => $r['id'], 'activity_type' => 'status_change',
                    'description' => 'Bulk status update',
                    'old_value' => $r['status'] ?? '', 'new_value' => $to,
                    'created_by' => $userId, 'tenant_id' => $tid,
                ]);
                $n++;
            }
            $this->logActivity('leads_bulk_status', json_encode(['count' => $n, 'status' => $to]));
            $_SESSION['success'] = $n . ' leads updated!';
        } elseif ($action === 'assign') {
            $to = (int)($_POST['bulk_assign_to'] ?? 0);
            $tp = [$to];
            if (TenantContext::getId() > 1) $tp[] = TenantContext::getId();
            $target = $to > 0 ? $db->fetchOne("SELECT id, name FROM users WHERE id = ? AND role = 'associate'{$tidSql} LIMIT 1", $tp) : null;
            if (!$target) {
                $_SESSION['error'] = 'Invalid team member id';
                $this->redirect('/associate/leads');
                return;
            }
            $params = array_merge($ids, [$userId, $userId]);
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();
            $rows = $db->fetchAll("SELECT id FROM leads WHERE id IN ($ph) AND (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL", $params) ?: [];
            $n = 0;
            foreach ($rows as $r) {
                $db->execute("UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id = ?", [$to, $r['id']]);
                $db->insert('lead_activities', [
                    'lead_id' => $r['id'], 'activity_type' => 'assignment',
                    'description' => 'Bulk assign',
                    'old_value' => '', 'new_value' => $target['name'] ?? '',
                    'created_by' => $userId, 'tenant_id' => $tid,
                ]);
                $n++;
            }
            $this->logActivity('leads_bulk_assign', json_encode(['count' => $n, 'assigned_to' => $to]));
            $_SESSION['success'] = $n . ' leads assigned!';
        } else {
            $_SESSION['error'] = 'Invalid bulk action';
        }
        $this->redirect('/associate/leads');
    }

    /**
     * Stage-aging analytics — read-only: average days leads spend per stage,
     * derived from the status_change timeline (own scope, last 5000 events).
     * Shows where the pipeline stalls.
     */
    public function stageAging()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND a.tenant_id = ?" : "";
        $params = [$userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();
        $events = $db->fetchAll(
            "SELECT a.lead_id, a.old_value, a.new_value, a.created_at
             FROM lead_activities a JOIN leads l ON l.id = a.lead_id
             WHERE (l.assigned_to = ? OR l.created_by = ?){$tidSql}
               AND a.activity_type = 'status_change'
             ORDER BY a.lead_id ASC, a.created_at ASC LIMIT 5000",
            $params
        ) ?: [];

        // Per stage: total days accumulated + exits counted.
        $stages = [];
        $prev = null;
        foreach ($events as $e) {
            if ($prev && $prev['lead_id'] === $e['lead_id']) {
                $stage = $prev['new_value'] ?: $prev['old_value'] ?: 'unknown';
                $days = (strtotime($e['created_at']) - strtotime($prev['created_at'])) / 86400;
                if ($days >= 0 && $days < 3650) {
                    if (!isset($stages[$stage])) $stages[$stage] = ['days' => 0, 'exits' => 0];
                    $stages[$stage]['days'] += $days;
                    $stages[$stage]['exits']++;
                }
            }
            $prev = $e;
        }
        $rows = [];
        foreach ($stages as $stage => $s) {
            $rows[] = ['stage' => $stage, 'avg_days' => $s['exits'] > 0 ? round($s['days'] / $s['exits'], 1) : 0, 'exits' => $s['exits']];
        }
        usort($rows, function ($a, $b) { return $b['avg_days'] <=> $a['avg_days']; });

        $this->render('associate/stage_aging', [
            'page_title' => 'Stage Aging - Associate Portal',
            'page_description' => 'Where your pipeline stalls',
            'rows' => $rows,
        ], 'layouts/associate');
    }

    /**
     * Trash bin — own soft-deleted leads (junk archive rows are NULL-owner
     * and never match this scope, so they stay out of the bin).
     */
    public function trash()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();
        $rows = $db->fetchAll(
            "SELECT id, name, phone, status, deleted_at FROM leads WHERE (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NOT NULL ORDER BY deleted_at DESC LIMIT 200",
            $params
        ) ?: [];

        $this->render('associate/trash', [
            'page_title' => 'Trash - Associate Portal',
            'page_description' => 'Restore or permanently delete',
            'rows' => $rows,
        ], 'layouts/associate');
    }

    /**
     * POST /associate/leads/{id}/restore — un-trash (children were never removed).
     */
    public function restoreLead($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/leads/trash');
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$id, $userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();
        $stmt = $db->prepare("UPDATE leads SET deleted_at = NULL WHERE id = ? AND (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NOT NULL");
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            $this->logActivity('lead_restored', json_encode(['lead_id' => $id]));
            $_SESSION['success'] = 'Lead restored!';
        } else {
            $_SESSION['error'] = 'Lead not found or access denied';
        }
        $this->redirect('/associate/leads/trash');
    }

    /**
     * POST /associate/leads/{id}/destroy — permanent delete (old deleteLead
     * body, moved here verbatim): followups explicit (no FK), rest cascades.
     */
    public function destroyLead($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/leads/trash');
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$id, $userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $stmt = $db->prepare("DELETE FROM leads WHERE id = ? AND (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NOT NULL");
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            // followups has NO FK to leads (no cascade) — delete explicitly or it orphans.
            $db->execute("DELETE FROM followups WHERE lead_id = ?", [$id]);
            $this->logActivity('lead_destroyed', json_encode(['lead_id' => $id]));
            $_SESSION['success'] = 'Lead permanently deleted!';
        } else {
            $_SESSION['error'] = 'Lead not found or access denied';
        }

        $this->redirect('/associate/leads/trash');
    }

    /**
     * POST /associate/leads/trash/empty — permanently delete own trashed rows
     * older than 30 days (explicit user action, never a cron).
     */
    public function emptyTrash()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/leads/trash');
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();
        $ids = $db->fetchAll(
            "SELECT id FROM leads WHERE (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NOT NULL AND deleted_at < DATE_SUB(NOW(), INTERVAL 30 DAY) LIMIT 500",
            $params
        ) ?: [];
        $n = 0;
        foreach ($ids as $row) {
            $dp = [$row['id'], $userId, $userId];
            if (TenantContext::getId() > 1) $dp[] = TenantContext::getId();
            $st = $db->prepare("DELETE FROM leads WHERE id = ? AND (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NOT NULL");
            $st->execute($dp);
            if ($st->rowCount() > 0) {
                $db->execute("DELETE FROM followups WHERE lead_id = ?", [$row['id']]);
                $n++;
            }
        }
        $this->logActivity('trash_emptied', json_encode(['count' => $n]));
        $_SESSION['success'] = $n . ' old trashed leads permanently deleted!';
        $this->redirect('/associate/leads/trash');
    }

    /**
     * Assign lead to another associate (was a "Feature moved to CRM" stub on the facade).
     */
    public function assignLead($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/associate/leads/{$id}");
            return;
        }

        $to = (int)($_POST['assign_to'] ?? 0);
        if ($to <= 0) {
            $_SESSION['error'] = 'Select a team member first';
            $this->redirect("/associate/leads/{$id}");
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";

        // Target must be a real associate (matches the form's offer list).
        $tParams = [$to];
        if (TenantContext::getId() > 1) $tParams[] = TenantContext::getId();
        $target = $db->fetchOne("SELECT id, name FROM users WHERE id = ? AND role = 'associate'{$tidSql} LIMIT 1", $tParams);
        if (!$target) {
            $_SESSION['error'] = 'Invalid team member';
            $this->redirect("/associate/leads/{$id}");
            return;
        }

        // Old assignee name first — the timeline renders old → new.
        $oParams = [$id, $userId, $userId];
        if (TenantContext::getId() > 1) $oParams[] = TenantContext::getId();
        $oldLead = $db->fetchOne("SELECT l.assigned_to, u.name AS old_name FROM leads l LEFT JOIN users u ON u.id = l.assigned_to WHERE l.id = ? AND (l.assigned_to = ? OR l.created_by = ?){$tidSql} AND l.deleted_at IS NULL LIMIT 1", $oParams);
        // Scope matches list/detail/delete/status (assigned OR created).
        $params = [$to, $id, $userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();
        $stmt = $db->prepare("UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id = ? AND (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL");
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            // Timeline feed (generic "Assignment" label — view styles known types only).
            $db->insert('lead_activities', [
                'lead_id' => $id,
                'activity_type' => 'assignment',
                'description' => 'Lead reassigned',
                'old_value' => $oldLead['old_name'] ?? '',
                'new_value' => $target['name'] ?? '',
                'created_by' => $userId,
                'tenant_id' => $tid,
            ]);
            $this->logActivity('lead_assigned', json_encode(['lead_id' => $id, 'assigned_to' => $to]));
            $this->stampFirstResponse($db, $id);
            $_SESSION['success'] = 'Lead assigned!';
        } else {
            $_SESSION['error'] = 'Lead not found or access denied';
        }

        $this->redirect("/associate/leads/{$id}");
    }

    /**
     * Recalculate AI score for one lead (was a stub). Thin glue over the
     * existing LeadScoringService — no scoring logic lives here.
     */
    public function recalculateScore($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/associate/leads/{$id}");
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$id, $userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();
        $lead = $db->fetchOne("SELECT id FROM leads WHERE id = ? AND (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL LIMIT 1", $params);
        if (!$lead) {
            $_SESSION['error'] = 'Lead not found or access denied';
            $this->redirect('/associate/leads');
            return;
        }

        $svc = new \App\Services\LeadScoringService();
        $scores = $svc->calculateScore($id);
        if (!$scores) {
            $_SESSION['error'] = 'Could not score this lead';
            $this->redirect("/associate/leads/{$id}");
            return;
        }
        $svc->saveScore($id, $scores);

        $this->logActivity('lead_score_recalculated', json_encode(['lead_id' => $id, 'score' => $scores['total'], 'rank' => $scores['rank']]));
        $_SESSION['success'] = 'Score recalculated: ' . $scores['total'] . ' (' . $scores['rank'] . ')';
        $this->redirect("/associate/leads/{$id}");
    }

    /**
     * Recalculate AI scores for the associate's OWN leads only (was a stub).
     * Deliberately NOT the service's global processAllLeads() — that would
     * write scores for other users' leads.
     */
    public function recalculateAllScores()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/leads');
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();
        // Chunked: unscored-first, 200 per run — a 900+ lead book must not
        // ride one PHP request past max_execution_time. Re-click to resume.
        // (l.-qualified tenant guard: both joined tables carry tenant_id.)
        $total = (int)($db->fetchOne("SELECT COUNT(*) c FROM leads WHERE (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL", $params)['c'] ?? 0);
        $lTidSql = TenantContext::getId() > 1 ? " AND l.tenant_id = ?" : "";
        $ids = $db->fetchAll("SELECT l.id FROM leads l LEFT JOIN lead_scores s ON s.lead_id = l.id WHERE (l.assigned_to = ? OR l.created_by = ?){$lTidSql} AND s.lead_id IS NULL ORDER BY l.id ASC LIMIT 200", $params) ?: [];
        if (empty($ids)) {
            // Everything scored — refresh the oldest 200 instead of no-op.
            $ids = $db->fetchAll("SELECT l.id FROM leads l JOIN lead_scores s ON s.lead_id = l.id WHERE (l.assigned_to = ? OR l.created_by = ?){$lTidSql} ORDER BY s.calculated_at ASC LIMIT 200", $params) ?: [];
        }

        $svc = new \App\Services\LeadScoringService();
        $done = 0;
        foreach ($ids as $row) {
            $scores = $svc->calculateScore($row['id']);
            if ($scores) {
                $svc->saveScore($row['id'], $scores);
                $done++;
            }
        }

        $this->logActivity('lead_scores_recalculated_bulk', json_encode(['count' => $done, 'total' => $total]));
        $_SESSION['success'] = $done . ' of ' . $total . ' lead scores recalculated!' . ($done < $total ? ' Run again to continue.' : '');
        $this->redirect('/associate/leads');
    }

    /**
     * My Business Report — read-only: own leads by source, lost reasons,
     * own bookings + revenue, aggregate conversion. No row-level lead↔booking
     * join exists in schema (bookings carry no lead_id), so conversion is
     * honestly aggregate, never fabricated per-row.
     */
    public function businessReport()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $bySource = $db->fetchAll(
            "SELECT COALESCE(NULLIF(source, ''), 'unknown') AS source, COUNT(*) c FROM leads WHERE (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL GROUP BY source ORDER BY c DESC",
            $params
        ) ?: [];
        $byReason = $db->fetchAll(
            "SELECT COALESCE(NULLIF(lost_reason, ''), 'other') AS reason, COUNT(*) c FROM leads WHERE (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL AND status = 'closed_lost' GROUP BY lost_reason ORDER BY c DESC",
            $params
        ) ?: [];
        $leadTotal = (int)($db->fetchOne("SELECT COUNT(*) c FROM leads WHERE (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL", $params)['c'] ?? 0);

        $bParams = [$userId];
        if (TenantContext::getId() > 1) $bParams[] = TenantContext::getId();
        $book = $db->fetchOne(
            "SELECT COUNT(*) n, COALESCE(SUM(booking_amount), 0) revenue FROM plot_bookings WHERE associate_id = ? AND status <> 'cancelled'{$tidSql}",
            $bParams
        ) ?: ['n' => 0, 'revenue' => 0];

        $this->render('associate/business_report', [
            'page_title' => 'My Business Report - Associate Portal',
            'page_description' => 'Sources, reasons, bookings, conversion',
            'by_source' => $bySource,
            'by_reason' => $byReason,
            'lead_total' => $leadTotal,
            'booking_count' => (int)($book['n'] ?? 0),
            'booking_revenue' => (float)($book['revenue'] ?? 0),
        ], 'layouts/associate');
    }

    /**
     * Revival queue — read-only: own open-pipeline leads untouched 30+ days.
     * Cold rows get a second life via the existing bulk-WhatsApp page (?ids=).
     */
    public function reviveQueue()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $rows = $db->fetchAll(
            "SELECT l.id, l.name, l.phone, l.status, l.updated_at, DATEDIFF(NOW(), l.updated_at) AS idle_days
             FROM leads l
             WHERE (l.assigned_to = ? OR l.created_by = ?){$tidSql} AND l.deleted_at IS NULL
               AND l.status NOT IN ('closed_won', 'closed_lost')
               AND l.updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
             ORDER BY l.updated_at ASC LIMIT 100",
            $params
        ) ?: [];

        $this->render('associate/revive_queue', [
            'page_title' => 'Revival Queue - Associate Portal',
            'page_description' => 'Cold leads worth a second touch',
            'rows' => $rows,
        ], 'layouts/associate');
    }

    /**
     * Log an outbound call (was missing entirely — tel: button dialed with
     * no record). Writes call_logs + timeline 'call' row + SLA stamp.
     */
    public function logCall($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/associate/leads/{$id}");
            return;
        }

        $outcome = $_POST['outcome'] ?? '';
        if (!in_array($outcome, ['connected', 'interested', 'callback', 'no_answer', 'busy'], true)) {
            $_SESSION['error'] = 'Invalid call outcome';
            $this->redirect("/associate/leads/{$id}");
            return;
        }
        $duration = max(0, min(999, (int)($_POST['duration'] ?? 0)));
        $callNotes = trim($_POST['call_notes'] ?? '');

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$id, $userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();
        $lead = $db->fetchOne("SELECT id, phone FROM leads WHERE id = ? AND (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL LIMIT 1", $params);
        if (!$lead) {
            $_SESSION['error'] = 'Lead not found or access denied';
            $this->redirect('/associate/leads');
            return;
        }

        // agent_id FKs agents.id — resolve via user_id, NULL when absent (column nullable).
        $agentRow = $db->fetchOne("SELECT id FROM agents WHERE user_id = ? LIMIT 1", [$userId]);
        $db->insert('call_logs', [
            'lead_id' => $id,
            'agent_id' => $agentRow ? $agentRow['id'] : null,
            'phone' => $lead['phone'] ?? null,
            'call_type' => 'outbound',
            'duration' => $duration,
            'status' => $outcome,
            'notes' => ($callNotes !== '' ? $callNotes : null),
            'tenant_id' => $tid,
        ]);
        $db->insert('lead_activities', [
            'lead_id' => $id,
            'activity_type' => 'call',
            'description' => 'Call: ' . $outcome . ($duration > 0 ? " ({$duration} min)" : '') . ($callNotes !== '' ? ' — ' . mb_substr($callNotes, 0, 200) : ''),
            'created_by' => $userId,
            'tenant_id' => $tid,
        ]);
        $this->logActivity('lead_call_logged', json_encode(['lead_id' => $id, 'outcome' => $outcome]));
        $this->stampFirstResponse($db, $id);
        $_SESSION['success'] = 'Call logged!';
        $this->redirect("/associate/leads/{$id}");
    }

    /**
     * Team pipeline roll-up — read-only: direct downline (mlm_network_tree)
     * with per-member leads/open/won + bookings/revenue. Managers see their
     * own downline only (same data the Network Tree already exposes).
     */
    public function teamPipeline()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $members = $db->fetchAll(
            "SELECT m.associate_id AS id, u.name FROM mlm_network_tree m JOIN users u ON u.id = m.associate_id WHERE m.parent_id = ?{$tidSql} ORDER BY u.name",
            $params
        ) ?: [];

        $rows = [];
        foreach ($members as $m) {
            $mid = (int)$m['id'];
            $lp = [$mid, $mid];
            if (TenantContext::getId() > 1) $lp[] = TenantContext::getId();
            $leads = (int)($db->fetchOne("SELECT COUNT(*) c FROM leads WHERE (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL", $lp)['c'] ?? 0);
            $open = (int)($db->fetchOne("SELECT COUNT(*) c FROM leads WHERE (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL AND status NOT IN ('closed_won','closed_lost')", $lp)['c'] ?? 0);
            $won = (int)($db->fetchOne("SELECT COUNT(*) c FROM leads WHERE (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL AND status = 'closed_won'", $lp)['c'] ?? 0);
            $bp = [$mid];
            if (TenantContext::getId() > 1) $bp[] = TenantContext::getId();
            $bk = $db->fetchOne("SELECT COUNT(*) n, COALESCE(SUM(booking_amount), 0) revenue FROM plot_bookings WHERE associate_id = ? AND status <> 'cancelled'{$tidSql}", $bp) ?: ['n' => 0, 'revenue' => 0];
            $rows[] = [
                'id' => $mid, 'name' => $m['name'] ?? ('#' . $mid),
                'leads' => $leads, 'open' => $open, 'won' => $won,
                'bookings' => (int)($bk['n'] ?? 0), 'revenue' => (float)($bk['revenue'] ?? 0),
            ];
        }

        // Escalation inbox: downline members' overdue pending follow-ups (3+ days past due).
        // (f.-qualified tenant guard: 3 tables joined, all carry tenant_id.)
        $escalations = [];
        $memberIds = array_values(array_unique(array_map(function ($m) { return (int)$m['id']; }, $members)));
        if (!empty($memberIds)) {
            $ph = implode(',', array_fill(0, count($memberIds), '?'));
            $fTidSql = TenantContext::getId() > 1 ? " AND f.tenant_id = ?" : "";
            $ep = $memberIds;
            if (TenantContext::getId() > 1) $ep[] = TenantContext::getId();
            $escalations = $db->fetchAll(
                "SELECT f.id, f.followup_date, f.followup_type, u.name AS owner_name, l.name AS lead_name, l.phone
                 FROM followups f
                 JOIN users u ON u.id = f.assigned_to
                 LEFT JOIN leads l ON l.id = f.lead_id
                 WHERE f.assigned_to IN ($ph){$fTidSql} AND f.status = 'pending' AND f.followup_date < DATE_SUB(CURDATE(), INTERVAL 3 DAY)
                 ORDER BY f.followup_date ASC LIMIT 50",
                $ep
            ) ?: [];
        }

        $this->render('associate/team_pipeline', [
            'page_title' => 'Team Pipeline - Associate Portal',
            'page_description' => 'Downline performance roll-up',
            'rows' => $rows,
            'escalations' => $escalations,
        ], 'layouts/associate');
    }

    /**
     * Workflow rules — list + builder (lead automation v1).
     */
    public function workflows()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();
        $rules = $db->fetchAll("SELECT * FROM workflow_rules WHERE user_id = ?{$tidSql} ORDER BY id DESC", $params) ?: [];

        $this->render('associate/workflows', [
            'page_title' => 'Workflow Automation - Associate Portal',
            'page_description' => 'When this happens, do that — automatically',
            'rules' => $rules,
        ], 'layouts/associate');
    }

    /**
     * GET /associate/workflow-builder — visual drag-drop workflow builder (Gap 21).
     */
    public function workflowBuilder()
    {
        $this->requireAuth();
        return $this->render('associate/workflow_builder', [
            'page_title' => 'Visual Workflow Builder - APS Dream Home',
            'page_description' => 'Drag-and-drop trigger → condition → action rule builder',
        ], 'layouts/associate');
    }

    /**
     * POST /associate/workflows/store — create a rule.
     */
    public function storeWorkflowRule()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/workflows');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $trigger = $_POST['trigger'] ?? '';
        $action = $_POST['action'] ?? '';
        if ($name === '' || !in_array($trigger, ['lead_created', 'status_changed', 'note_added'], true)
            || !in_array($action, ['add_note', 'set_status', 'create_followup'], true)) {
            $_SESSION['error'] = 'Name, trigger and action are required';
            $this->redirect('/associate/workflows');
            return;
        }

        $conds = [];
        if (trim($_POST['cond_status'] ?? '') !== '') $conds['status'] = trim($_POST['cond_status']);
        if ($trigger === 'status_changed' && trim($_POST['cond_new_status'] ?? '') !== '') {
            $conds['new_status'] = trim($_POST['cond_new_status']);
        }
        $actionParams = [];
        if ($action === 'add_note') $actionParams['text'] = mb_substr(trim($_POST['param_text'] ?? ''), 0, 1000);
        if ($action === 'set_status') $actionParams['status'] = trim($_POST['param_status'] ?? '');
        if ($action === 'create_followup') {
            $actionParams['days'] = max(1, min(90, (int)($_POST['param_days'] ?? 3)));
            $actionParams['type'] = preg_replace('/[^a-z_]/', '', (string)($_POST['param_type'] ?? 'call')) ?: 'call';
        }
        if (($action === 'add_note' && ($actionParams['text'] ?? '') === '')
            || ($action === 'set_status' && !in_array($actionParams['status'] ?? '', ['new','contacted','qualified','site_visit','proposal','negotiation','closed_won','closed_lost','nurture'], true))) {
            $_SESSION['error'] = 'Action needs its parameter (note text / valid status)';
            $this->redirect('/associate/workflows');
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        // NOTE: `trigger` is a MySQL reserved word — backticked explicitly
        // (the generic insert() helper does not quote columns).
        $db->execute(
            "INSERT INTO workflow_rules (user_id, name, `trigger`, conditions, `action`, action_params, is_active, tenant_id) VALUES (?, ?, ?, ?, ?, ?, 1, ?)",
            [$userId, mb_substr($name, 0, 150), $trigger, !empty($conds) ? json_encode($conds) : null, $action, json_encode($actionParams), $tid]
        );
        $_SESSION['success'] = 'Workflow rule created!';
        $this->redirect('/associate/workflows');
    }

    /**
     * POST /associate/workflows/{id}/toggle + /delete — owner-scoped.
     */
    public function toggleWorkflowRule($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/workflows');
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$id, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();
        $db->execute("UPDATE workflow_rules SET is_active = 1 - is_active WHERE id = ? AND user_id = ?{$tidSql}", $params);
        $this->redirect('/associate/workflows');
    }

    public function deleteWorkflowRule($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/workflows');
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$id, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();
        $db->execute("DELETE FROM workflow_rules WHERE id = ? AND user_id = ?{$tidSql}", $params);
        $_SESSION['success'] = 'Workflow rule deleted!';
        $this->redirect('/associate/workflows');
    }

    /**
     * Download a follow-up as .ics (no-creds calendar sync: Google/Outlook
     * import the file; full OAuth sync is an owner-credentials phase-2).
     */
    public function followupIcs($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND f.tenant_id = ?" : "";
        $params = [$id, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();
        $f = $db->fetchOne(
            "SELECT f.*, l.name AS lead_name FROM followups f LEFT JOIN leads l ON l.id = f.lead_id WHERE f.id = ? AND f.assigned_to = ?{$tidSql} LIMIT 1",
            $params
        );
        if (!$f || empty($f['followup_date'])) {
            $this->redirect('/associate/followups');
            return;
        }

        $dt = preg_replace('/[^0-9]/', '', $f['followup_date']) . 'T' . preg_replace('/[^0-9]/', '', substr($f['followup_time'] ?? '100000', 0, 8));
        $dt = substr(str_pad($dt, 15, '0'), 0, 15);
        $summary = 'Follow-up: ' . ($f['lead_name'] ?? ('lead #' . $f['lead_id']));
        $esc = function ($s) { return addcslashes((string)$s, ",;\\"); };
        $ics = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//APS Dream Home//CRM//EN\r\nBEGIN:VEVENT\r\nUID:followup-{$f['id']}@apsdreamhome\r\nDTSTART:{$dt}\r\nSUMMARY:" . $esc($summary) . "\r\nDESCRIPTION:" . $esc($f['notes'] ?? '') . "\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="followup-' . (int)$f['id'] . '.ics"');
        echo $ics;
        exit;
    }

    /**
     * Followups page
     */
    public function followups()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $followups = $db->fetchAll("
            SELECT f.*, l.name as lead_name, l.phone,
                   f.followup_date AS due_date, f.followup_type AS task_type, l.phone AS lead_phone
            FROM followups f
            JOIN leads l ON l.id = f.lead_id AND l.deleted_at IS NULL
            WHERE f.assigned_to = ?{$tidSql}
            ORDER BY f.followup_date ASC, f.followup_time ASC
        ", $params) ?: [];

        $this->render('associate/followups', [
            'page_title' => 'Follow-ups - Associate Portal',
            'page_description' => 'Manage your follow-ups',
            'followups' => $followups,
        ], 'layouts/associate');
    }

    /**
     * Update followup
     */
    public function updateFollowup($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/followups');
            return;
        }

        $status = $_POST['status'] ?? '';
        $notes = trim($_POST['notes'] ?? '');
        $nextDate = $_POST['next_followup_date'] ?? null;

        // DB enum is (pending/completed/cancelled/skipped) — reject the rest (S241 family).
        if (!in_array($status, ['pending', 'completed', 'cancelled', 'skipped'], true)) {
            $_SESSION['error'] = 'Invalid status';
            $this->redirect('/associate/followups');
            return;
        }
        // Garbage dates 1292 under strict mode — accept only real calendar dates.
        if (!empty($nextDate) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $nextDate)) {
            $_SESSION['error'] = 'Invalid follow-up date';
            $this->redirect('/associate/followups');
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$status];
        if ($notes !== '') $params[] = $notes;
        if ($nextDate) $params[] = $nextDate;
        $params[] = $id;
        $params[] = $userId;
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $sql = "UPDATE followups SET status = ?" . ($notes !== '' ? ", result = ?" : "") . ($nextDate ? ", followup_date = ?" : "") . " WHERE id = ? AND assigned_to = ?{$tidSql}";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            if ($nextDate && $status === 'pending') {
                // Create next followup
                $db->insert('followups', [
                    'lead_id' => $db->fetchOne("SELECT lead_id FROM followups WHERE id = ?", [$id])['lead_id'],
                    'assigned_to' => $userId,
                    'followup_date' => $nextDate,
                    'followup_time' => '10:00:00',
                    'notes' => $notes,
                    'status' => 'pending',
                    'tenant_id' => TenantContext::getId(),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            $_SESSION['success'] = 'Follow-up updated!';
        } else {
            $_SESSION['error'] = 'Follow-up not found or access denied';
        }

        $this->redirect('/associate/followups');
    }

    /* =========================================================
     *  Lead import / export / bulk-WhatsApp (were legacy redirects)
     * ========================================================= */

    /**
     * GET /associate/leads/import — renders the import form view.
     */
    public function importLeads()
    {
        $this->requireAuth();
        $this->render('associate/import_leads', [
            'page_title' => 'Import Leads - Associate Portal',
            'imported' => (int)($_GET['imported'] ?? 0),
            'skipped' => (int)($_GET['skipped'] ?? 0),
            'errors' => [],
        ], 'layouts/associate');
    }

    /**
     * POST /associate/leads/import — parse CSV (max 500 rows), validate,
     * skip duplicate phones, insert the rest as this associate's leads.
     */
    /**
     * Parse an import CSV into validated rows. Shared by direct import and
     * preview (single rule source — no divergent validation).
     * Returns ['rows' => [...], 'skipped' => [['row'=>n,'reason'=>...]]].
     * Throws RuntimeException on unreadable/empty/bad-header files.
     */
    private function parseImportCsv($tmpPath)
    {
        $fh = fopen($tmpPath, 'r');
        if (!$fh) {
            throw new \RuntimeException('Could not read the uploaded file.');
        }
        $header = fgetcsv($fh);
        if (!$header) {
            fclose($fh);
            throw new \RuntimeException('Empty CSV file.');
        }
        $map = [];
        foreach ($header as $i => $h) {
            $map[strtolower(trim((string)$h))] = $i;
        }
        if (!isset($map['name']) || !isset($map['phone'])) {
            fclose($fh);
            throw new \RuntimeException('CSV must have name and phone columns.');
        }
        $rows = [];
        $skipped = [];
        $rowNo = 1;
        while (($row = fgetcsv($fh)) !== false) {
            $rowNo++;
            if ($rowNo > 501) {
                $skipped[] = ['row' => $rowNo, 'reason' => 'import capped at 500 leads'];
                break;
            }
            $name = trim((string)($row[$map['name']] ?? ''));
            $phone = $this->normalizePhone((string)($row[$map['phone']] ?? ''));
            if ($name === '' || !preg_match('/^[6-9]\d{9}$/', $phone)) {
                $skipped[] = ['row' => $rowNo, 'reason' => 'bad name/phone'];
                continue;
            }
            $rows[] = [
                'name' => mb_substr($name, 0, 100),
                'phone' => $phone,
                'email' => isset($map['email']) ? mb_substr(trim((string)($row[$map['email']] ?? '')), 0, 100) : '',
                'source' => isset($map['source']) && trim((string)($row[$map['source']] ?? '')) !== ''
                    ? mb_substr(trim((string)$row[$map['source']]), 0, 50) : 'csv_import',
                'budget' => isset($map['budget']) ? mb_substr(trim((string)($row[$map['budget']] ?? '')), 0, 50) : null,
                'location' => isset($map['location']) ? mb_substr(trim((string)($row[$map['location']] ?? '')), 0, 100) : null,
                'notes' => isset($map['notes']) ? mb_substr(trim((string)($row[$map['notes']] ?? '')), 0, 500) : null,
            ];
        }
        fclose($fh);
        return ['rows' => $rows, 'skipped' => $skipped];
    }

    public function importLeadsStore()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('/associate/leads/import');
            return;
        }
        if (!isset($_FILES['csv_file']) || ($_FILES['csv_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Please choose a CSV file to upload.';
            $this->redirect('/associate/leads/import');
            return;
        }

        // Preview mode: stash file in temp, show rows, import on confirm.
        if (!empty($_POST['preview_only'])) {
            $token = 'leadimp_' . $userId . '_' . bin2hex(random_bytes(8)) . '.csv';
            $tmp = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . $token;
            if (!move_uploaded_file($_FILES['csv_file']['tmp_name'], $tmp)) {
                $_SESSION['flash_error'] = 'Could not stage the file for preview.';
                $this->redirect('/associate/leads/import');
                return;
            }
            try {
                $parsed = $this->parseImportCsv($tmp);
            } catch (\Throwable $e) {
                @unlink($tmp);
                $_SESSION['flash_error'] = 'Import failed: ' . $e->getMessage();
                $this->redirect('/associate/leads/import');
                return;
            }
            $this->render('associate/import_preview', [
                'page_title' => 'Preview Import - Associate Portal',
                'page_description' => 'Check rows before importing',
                'token' => $token,
                'rows' => array_slice($parsed['rows'], 0, 5),
                'valid_count' => count($parsed['rows']),
                'skipped' => array_slice($parsed['skipped'], 0, 10),
                'skipped_count' => count($parsed['skipped']),
            ], 'layouts/associate');
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        $imported = 0;
        $skipped = 0;
        $errors = [];
        try {
            $parsed = $this->parseImportCsv($_FILES['csv_file']['tmp_name']);
            $skipped = count($parsed['skipped']);
            foreach ($parsed['rows'] as $r) {
                $dup = $db->fetchOne(
                    "SELECT id FROM leads WHERE phone = ? AND tenant_id = ? AND deleted_at IS NULL LIMIT 1",
                    [$r['phone'], $tid]
                );
                if ($dup) {
                    $skipped++;
                    continue;
                }
                $db->insert('leads', [
                    'tenant_id' => $tid,
                    'name' => $r['name'],
                    'phone' => $r['phone'],
                    'email' => $r['email'] !== '' ? $r['email'] : null,
                    'source' => $r['source'],
                    'budget_range' => $r['budget'],
                    'location_preference' => $r['location'],
                    'notes' => $r['notes'],
                    'assigned_to' => $userId,
                    'created_by' => $userId,
                    'status' => 'new',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $imported++;
            }
            $this->logActivity('leads_imported', json_encode(['imported' => $imported, 'skipped' => $skipped]));
            $_SESSION['flash_success'] = "Imported $imported lead(s), skipped $skipped.";
        } catch (\Throwable $e) {
            error_log('AssociateCrmController::importLeadsStore error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Import failed: ' . $e->getMessage();
        }
        $this->redirect('/associate/leads/import?imported=' . $imported . '&skipped=' . $skipped);
    }

    /**
     * POST /associate/leads/import/confirm — import a staged preview file.
     */
    public function importConfirm()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('/associate/leads/import');
            return;
        }
        $token = basename((string)($_POST['token'] ?? ''));
        if (!preg_match('/^leadimp_\d+_[0-9a-f]{16}\.csv$/', $token)) {
            $_SESSION['flash_error'] = 'Invalid import token.';
            $this->redirect('/associate/leads/import');
            return;
        }
        // Token embeds the owner id — never import another user's staged file.
        if (!preg_match('/^leadimp_(\d+)_/', $token, $m) || (int)$m[1] !== (int)$userId) {
            $_SESSION['flash_error'] = 'Invalid import token.';
            $this->redirect('/associate/leads/import');
            return;
        }
        $tmp = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . $token;
        if (!is_file($tmp)) {
            $_SESSION['flash_error'] = 'Staged file expired — upload again.';
            $this->redirect('/associate/leads/import');
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        $imported = 0;
        $skipped = 0;
        try {
            $parsed = $this->parseImportCsv($tmp);
            $skipped = count($parsed['skipped']);
            foreach ($parsed['rows'] as $r) {
                $dup = $db->fetchOne(
                    "SELECT id FROM leads WHERE phone = ? AND tenant_id = ? AND deleted_at IS NULL LIMIT 1",
                    [$r['phone'], $tid]
                );
                if ($dup) {
                    $skipped++;
                    continue;
                }
                $db->insert('leads', [
                    'tenant_id' => $tid,
                    'name' => $r['name'],
                    'phone' => $r['phone'],
                    'email' => $r['email'] !== '' ? $r['email'] : null,
                    'source' => $r['source'],
                    'budget_range' => $r['budget'],
                    'location_preference' => $r['location'],
                    'notes' => $r['notes'],
                    'assigned_to' => $userId,
                    'created_by' => $userId,
                    'status' => 'new',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $imported++;
            }
            $this->logActivity('leads_imported', json_encode(['imported' => $imported, 'skipped' => $skipped, 'via' => 'preview']));
            $_SESSION['flash_success'] = "Imported $imported lead(s), skipped $skipped.";
        } catch (\Throwable $e) {
            error_log('AssociateCrmController::importConfirm error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Import failed: ' . $e->getMessage();
        }
        @unlink($tmp);
        $this->redirect('/associate/leads/import?imported=' . $imported . '&skipped=' . $skipped);
    }

    /**
     * GET /associate/leads/export — CSV download of this associate's leads.
     */
    public function exportLeads()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId, $userId];
        if ($tid > 1) $params[] = $tid;
        $rows = $db->fetchAll(
            "SELECT name, phone, email, source, budget_range, location_preference, status, created_at
             FROM leads WHERE (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL ORDER BY id DESC",
            $params
        ) ?: [];

        $filename = 'my-leads-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['name', 'phone', 'email', 'source', 'budget', 'location', 'status', 'created_at']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['name'] ?? '', $r['phone'] ?? '', $r['email'] ?? '', $r['source'] ?? '',
                $r['budget_range'] ?? '', $r['location_preference'] ?? '',
                $r['status'] ?? '', $r['created_at'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    /**
     * GET /associate/leads/bulk-whatsapp — compose screen for ?ids=1,2,3
     * (owned leads only). POST builds wa.me links (no auto-sending).
     */
    public function bulkWhatsApp()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();
        $db = \App\Core\Database\Database::getInstance();

        $ids = [];
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            foreach ((array)($_POST['lead_ids'] ?? []) as $lid) {
                $lid = (int)$lid;
                if ($lid > 0) $ids[] = $lid;
            }
        } else {
            foreach (explode(',', (string)($_GET['ids'] ?? '')) as $part) {
                $lid = (int)trim($part);
                if ($lid > 0) $ids[] = $lid;
            }
        }
        $ids = array_values(array_unique($ids));

        $leads = [];
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $params = array_merge($ids, [$userId, $userId]);
            $tidSql = '';
            if ($tid > 1) {
                $tidSql = ' AND tenant_id = ?';
                $params[] = $tid;
            }
            $leads = $db->fetchAll(
                "SELECT id, name, phone FROM leads
                 WHERE id IN ($placeholders) AND (assigned_to = ? OR created_by = ?){$tidSql} AND deleted_at IS NULL",
                $params
            ) ?: [];
        }

        $whatsappLinks = [];
        $message = trim((string)($_POST['message'] ?? ''));
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $message !== '' && !empty($leads)) {
            foreach ($leads as $l) {
                $digits = preg_replace('/\D/', '', (string)($l['phone'] ?? ''));
                if (!preg_match('/^[6-9]\d{9}$/', $digits)) {
                    continue;
                }
                $text = str_replace(
                    ['{name}', '{phone}'],
                    [$l['name'] ?? '', $digits],
                    $message
                );
                $whatsappLinks[] = [
                    'name' => $l['name'] ?? $digits,
                    'url' => 'https://wa.me/91' . $digits . '?text=' . urlencode($text),
                ];
            }
            $this->logActivity('bulk_whatsapp_links', json_encode(['count' => count($whatsappLinks)]));
        }

        $this->render('associate/bulk_whatsapp', [
            'page_title' => 'Bulk WhatsApp - Associate Portal',
            'leads' => $leads,
            'whatsappLinks' => $whatsappLinks,
            'sent' => count($whatsappLinks),
            'message' => $message,
        ], 'layouts/associate');
    }
}

