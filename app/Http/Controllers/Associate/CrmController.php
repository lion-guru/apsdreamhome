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
            $db = \App\Core\Database\Database::getInstance()->getConnection();
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

        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $status = $_GET['status'] ?? '';
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        $where = "WHERE l.assigned_to = ?{$tidSql}";
        $params = [$userId];
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

        $total = (int)$db->fetchOne("SELECT COUNT(*) as count FROM leads l WHERE {$where}", $params)['count'] ?? 0;
        $totalPages = max(1, ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $leads = $db->fetchAll("SELECT l.*, u.name as assigned_name FROM leads l LEFT JOIN users u ON u.id = l.assigned_to WHERE {$where} ORDER BY l.created_at DESC LIMIT {$perPage} OFFSET {$offset}", $params) ?: [];

        $this->render('associate/leads', [
            'page_title' => 'My Leads - Associate Portal',
            'page_description' => 'Manage your leads',
            'leads' => $leads,
            'status' => $status,
            'search' => $search,
            'page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
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

        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = TenantContext::getId() > 1 ? [TenantContext::getId()] : [];

        // Get sources
        $sources = $db->fetchAll("SELECT * FROM lead_sources WHERE active = 1{$tidSql} ORDER BY name", $params) ?: [];

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
            $db = \App\Core\Database\Database::getInstance()->getConnection();

            // Check for duplicate phone
            $phone = trim($_POST['phone'] ?? '');
            $existing = $db->fetchOne("SELECT id FROM leads WHERE phone = ? AND tenant_id = ?", [$phone, $tid]);
            if ($existing) {
                $_SESSION['info'] = 'Lead with this phone already exists';
                $this->redirect("/associate/leads/detail/{$existing['id']}");
                return;
            }

            $tid = TenantContext::getId();
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'phone' => $phone,
                'email' => trim($_POST['email'] ?? ''),
                'city' => $_POST['city'] ?? '',
                'budget_min' => (float)($_POST['budget_min'] ?? 0),
                'budget_max' => (float)($_POST['budget_max'] ?? 0),
                'property_type' => $_POST['property_type'] ?? '',
                'source_id' => (int)($_POST['source_id'] ?? 0),
                'notes' => trim($_POST['notes'] ?? ''),
                'message' => trim($_POST['message'] ?? ''),
                'assigned_to' => $_SESSION['user_id'],
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
                    'user_id' => $_SESSION['user_id'],
                    'note' => trim($_POST['notes']),
                    'created_at' => date('Y-m-d H:i:s'),
                    'tenant_id' => $tid,
                ]);
            }

            // Add activity
            $this->logActivity($userId, 'lead_created', ['lead_id' => $leadId]);

            $_SESSION['success'] = 'Lead added successfully!';
            $this->redirect("/associate/leads/detail/{$leadId}");
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

        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$id, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $lead = $db->fetchOne("SELECT l.*, u.name as assigned_name FROM leads l LEFT JOIN users u ON u.id = l.assigned_to WHERE l.id = ? AND l.assigned_to = ?{$tidSql} LIMIT 1", $params);

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

        // Get sources
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = TenantContext::getId() > 1 ? [TenantContext::getId()] : [];
        $sources = $db->fetchAll("SELECT * FROM lead_sources WHERE active = 1{$tidSql} ORDER BY name", $params) ?: [];

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
            $this->redirect("/associate/leads/detail/{$id}");
            return;
        }

        $status = $_POST['status'] ?? '';
        $validStatuses = ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'];
        if (!in_array($status, $validStatuses)) {
            $_SESSION['error'] = 'Invalid status';
            $this->redirect("/associate/leads/detail/{$id}");
            return;
        }

        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$status, $id, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $stmt = $db->prepare("UPDATE leads SET status = ?, updated_at = NOW() WHERE id = ? AND assigned_to = ?{$tidSql}");
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            $this->logActivity($userId, 'lead_status_changed', ['lead_id' => $id, 'new_status' => $status]);
            $_SESSION['success'] = 'Lead status updated!';
        } else {
            $_SESSION['error'] = 'Lead not found or access denied';
        }

        $this->redirect("/associate/leads/detail/{$id}");
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
            $this->redirect("/associate/leads/detail/{$id}");
            return;
        }

        $note = trim($_POST['note'] ?? '');
        if (empty($note)) {
            $_SESSION['error'] = 'Note cannot be empty';
            $this->redirect("/associate/leads/detail/{$id}");
            return;
        }

        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$id, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        // Verify ownership
        $stmt = $db->prepare("SELECT id FROM leads WHERE id = ? AND assigned_to = ?{$tidSql} LIMIT 1");
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = 'Lead not found or access denied';
            $this->redirect('/associate/leads');
            return;
        }

        $db->insert('lead_notes', [
            'lead_id' => $id,
            'user_id' => $userId,
            'note' => $note,
            'created_at' => date('Y-m-d H:i:s'),
            'tenant_id' => $tid,
        ]);

        $this->logActivity($userId, 'lead_note_added', ['lead_id' => $id]);

        $_SESSION['success'] = 'Note added!';
        $this->redirect("/associate/leads/detail/{$id}");
    }

    /**
     * Delete lead
     */
    public function deleteLead($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$id, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $stmt = $db->prepare("DELETE FROM leads WHERE id = ? AND assigned_to = ?{$tidSql}");
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            $this->logActivity($userId, 'lead_deleted', ['lead_id' => $id]);
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

        $db = \App\Core\Database\Database::getInstance()->getConnection();
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

        $db = \App\Core\Database\Database::getInstance()->getConnection();
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
}

