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

            // Lead stats
            $stats = [
                'total' => (int)$db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE assigned_to = ?{$tidSql}", $params)['count'] ?? 0,
                'new' => (int)$db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE assigned_to = ? AND status = 'new'{$tidSql}", $params)['count'] ?? 0,
                'contacted' => (int)$db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE assigned_to = ? AND status = 'contacted'{$tidSql}", $params)['count'] ?? 0,
                'qualified' => (int)$db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE assigned_to = ? AND status = 'qualified'{$tidSql}", $params)['count'] ?? 0,
                'proposal' => (int)$db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE assigned_to = ? AND status = 'proposal'{$tidSql}", $params)['count'] ?? 0,
                'won' => (int)$db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE assigned_to = ? AND status = 'won'{$tidSql}", $params)['count'] ?? 0,
                'lost' => (int)$db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE assigned_to = ? AND status = 'lost'{$tidSql}", $params)['count'] ?? 0,
            ];

            // Recent leads
            $recentLeads = $db->fetchAll("SELECT l.*, u.name as assigned_name FROM leads l LEFT JOIN users u ON u.id = l.assigned_to WHERE l.assigned_to = ?{$tidSql} ORDER BY l.created_at DESC LIMIT 10", $params) ?: [];

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

        $where = "WHERE (l.assigned_to = ? OR l.created_by = ?){$tidSql}";
        $params = [$userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        if ($status) {
            $where .= " AND l.status = ?";
            $params[] = $status;
        }
        if ($search) {
            $where .= " AND (l.name LIKE ? OR l.phone LIKE ? OR l.email LIKE ?)";
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
        $countBase = "FROM leads l WHERE (l.assigned_to = ? OR l.created_by = ?){$tidSql}";
        $countParams = [$userId, $userId];
        if (TenantContext::getId() > 1) $countParams[] = TenantContext::getId();
        $pipelineCounts = [];
        foreach (['new', 'contacted', 'qualified', 'site_visit', 'proposal', 'negotiation', 'closed_won', 'closed_lost', 'nurture'] as $sKey) {
            $row = $db->fetchOne(
                "SELECT COUNT(*) as count {$countBase} AND l.status = ?",
                array_merge($countParams, [$sKey])
            );
            $pipelineCounts[$sKey] = (int)($row['count'] ?? 0);
        }
        // "All" tab + header show the unfiltered in-scope total (matches sidebar badge).
        $unfilteredRow = $db->fetchOne("SELECT COUNT(*) as count {$countBase}", $countParams);
        $unfilteredTotal = (int)($unfilteredRow['count'] ?? 0);

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

            $phone = trim($_POST['phone'] ?? '');
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

        $lead = $db->fetchOne("SELECT l.*, u.name as assigned_name FROM leads l LEFT JOIN users u ON u.id = l.assigned_to WHERE l.id = ? AND (l.assigned_to = ? OR l.created_by = ?){$tidSql} LIMIT 1", $params);

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

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        // Scope matches list + delete (assigned OR created) — detail is reachable from both.
        $params = [$status, $id, $userId, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $stmt = $db->prepare("UPDATE leads SET status = ?, updated_at = NOW() WHERE id = ? AND (assigned_to = ? OR created_by = ?){$tidSql}");
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            $this->logActivity('lead_status_changed', json_encode(['lead_id' => $id, 'new_status' => $status]));
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
        $stmt = $db->prepare("SELECT id FROM leads WHERE id = ? AND (assigned_to = ? OR created_by = ?){$tidSql} LIMIT 1");
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

        $this->logActivity('lead_note_added', json_encode(['lead_id' => $id]));

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

        $stmt = $db->prepare("DELETE FROM leads WHERE id = ? AND (assigned_to = ? OR created_by = ?){$tidSql}");
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            // followups has NO FK to leads (no cascade) — delete explicitly or it orphans.
            $db->execute("DELETE FROM followups WHERE lead_id = ?", [$id]);
            $this->logActivity('lead_deleted', json_encode(['lead_id' => $id]));
            $_SESSION['success'] = 'Lead deleted!';
        } else {
            $_SESSION['error'] = 'Lead not found or access denied';
        }

        $this->redirect('/associate/leads');
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
            SELECT f.*, l.name as lead_name, l.phone
            FROM followups f
            JOIN leads l ON l.id = f.lead_id
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

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$status];
        if ($nextDate) $params[] = $nextDate;
        $params[] = $id;
        $params[] = $userId;
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $sql = "UPDATE followups SET status = ?" . ($nextDate ? ", followup_date = ?" : "") . " WHERE id = ? AND assigned_to = ?{$tidSql}";
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

        $db = \App\Core\Database\Database::getInstance();
        $imported = 0;
        $skipped = 0;
        $errors = [];
        try {
            $fh = fopen($_FILES['csv_file']['tmp_name'], 'r');
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
            $rowNo = 1;
            while (($row = fgetcsv($fh)) !== false) {
                $rowNo++;
                if ($rowNo > 501) {
                    $errors[] = "Row $rowNo: import capped at 500 leads.";
                    break;
                }
                $name = trim((string)($row[$map['name']] ?? ''));
                $phone = preg_replace('/\D/', '', (string)($row[$map['phone']] ?? ''));
                if ($name === '' || !preg_match('/^[6-9]\d{9}$/', $phone)) {
                    $skipped++;
                    continue;
                }
                $dup = $db->fetchOne(
                    "SELECT id FROM leads WHERE phone = ? AND tenant_id = ? LIMIT 1",
                    [$phone, $tid]
                );
                if ($dup) {
                    $skipped++;
                    continue;
                }
                $email = isset($map['email']) ? trim((string)($row[$map['email']] ?? '')) : '';
                $db->insert('leads', [
                    'tenant_id' => $tid,
                    'name' => mb_substr($name, 0, 100),
                    'phone' => $phone,
                    'email' => mb_substr($email, 0, 100) ?: null,
                    'source' => isset($map['source']) && trim((string)($row[$map['source']] ?? '')) !== ''
                        ? mb_substr(trim((string)$row[$map['source']]), 0, 50) : 'csv_import',
                    'budget_range' => isset($map['budget']) ? mb_substr(trim((string)($row[$map['budget']] ?? '')), 0, 50) : null,
                    'location_preference' => isset($map['location']) ? mb_substr(trim((string)($row[$map['location']] ?? '')), 0, 100) : null,
                    'notes' => isset($map['notes']) ? mb_substr(trim((string)($row[$map['notes']] ?? '')), 0, 500) : null,
                    'assigned_to' => $userId,
                    'created_by' => $userId,
                    'status' => 'new',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $imported++;
            }
            fclose($fh);
            $this->logActivity('leads_imported', json_encode(['imported' => $imported, 'skipped' => $skipped]));
            $_SESSION['flash_success'] = "Imported $imported lead(s), skipped $skipped.";
        } catch (\Throwable $e) {
            error_log('AssociateCrmController::importLeadsStore error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Import failed: ' . $e->getMessage();
        }
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
             FROM leads WHERE (assigned_to = ? OR created_by = ?){$tidSql} ORDER BY id DESC",
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
                 WHERE id IN ($placeholders) AND (assigned_to = ? OR created_by = ?){$tidSql}",
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

