<?php
/**
 * Lead Management Controller
 * CRM: Leads, Enquiries, Follow-ups
 * Phase 1: Wired to CRMService for audit trail
 * Phase 2: Role-based lead visibility
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class LeadController extends AdminController
{
    private $crm;

    public function __construct() {
        parent::__construct();
        $this->crm = new \App\Services\CRMService();
    }

    private function getCurrentUserId() {
        return (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
    }

    private function getCurrentUserRole() {
        return $_SESSION['role'] ?? $_SESSION['admin_role'] ?? 'admin';
    }

    /**
     * All leads list — Phase 2: role-based visibility
     */
    public function index()
    {
        $this->requireAdmin();
        $userId = $this->getCurrentUserId();
        $role = $this->getCurrentUserRole();

        $filters = [
            'search' => $_GET['search'] ?? null,
            'status' => $_GET['status'] ?? null,
            'source' => $_GET['source'] ?? null,
            'assigned_to' => $_GET['assigned_to'] ?? null,
            'priority' => $_GET['priority'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 25),
        ];

        $result = $this->crm->getLeads($filters, $userId, $role);

        $stats = $this->crm->getDashboardStats($userId, $role);

        return $this->render('admin/leads/index', [
            'leads' => $result['leads'],
            'total' => $result['total'],
            'page' => $result['page'],
            'per_page' => $result['per_page'],
            'total_pages' => $result['total_pages'],
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }
    
    /**
     * Create new lead
     */
    public function create()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $sources = $db->query("SELECT id, name FROM lead_sources ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $statuses = $db->query("SELECT status_name FROM lead_statuses ORDER BY id")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $assignees = $db->query("SELECT id, name FROM users WHERE role IN ('employee','admin','manager','associate','agent') AND deleted_at IS NULL ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $sources = []; $statuses = []; $assignees = [];
        }
        return $this->render('admin/leads/create', ['sources' => $sources, 'statuses' => $statuses, 'assignees' => $assignees]);
    }
    
    /**
     * Store lead — Phase 1: uses CRMService for audit trail
     */
    public function store()
    {
        $this->requireAdmin();
        $adminId = $this->getCurrentUserId();

        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            $this->setFlash('error', 'Lead name is required');
            return $this->redirect('/admin/leads/create');
        }

        $result = $this->crm->createLead([
            'name' => $name,
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'company' => trim($_POST['company'] ?? ''),
            'source' => trim($_POST['source'] ?? 'manual'),
            'property_interest' => trim($_POST['property_interest'] ?? ''),
            'budget' => !empty($_POST['budget']) ? floatval($_POST['budget']) : 0,
            'budget_range' => trim($_POST['budget_range'] ?? ''),
            'location_preference' => trim($_POST['location_preference'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'notes' => trim($_POST['notes'] ?? $_POST['message'] ?? ''),
            'tags' => trim($_POST['tags'] ?? ''),
            'assigned_to' => !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null,
            'created_by' => $adminId,
            'priority' => trim($_POST['priority'] ?? 'medium'),
            'lead_score' => 0,
            'lead_category' => 'cold',
            'source_type' => trim($_POST['source'] ?? 'manual'),
        ]);

        if ($result['success']) {
            try {
                $automation = new \App\Services\AutomationTriggerService();
                $automation->onLeadCreated($result['lead_id']);
            } catch (\Exception $e) {
                error_log('LeadController::store automation error: ' . $e->getMessage());
            }

            // Phase 4: Trigger SLA on lead creation
            try {
                $slaTrigger = new \App\Services\SLATriggerService();
                $slaTrigger->onLeadCreated($result['lead_id'], [
                    'lead_score' => 0,
                    'priority' => trim($_POST['priority'] ?? 'medium'),
                    'lead_category' => 'cold',
                ]);
            } catch (\Exception $e) {
                error_log('LeadController::store SLA trigger error: ' . $e->getMessage());
            }

            $this->setFlash('success', 'Lead created successfully (' . ($result['lead_number'] ?? '') . ')');
        } else {
            $this->setFlash('error', 'Failed to create lead: ' . ($result['error'] ?? 'Unknown error'));
        }
        return $this->redirect('/admin/leads');
    }
    
    /**
     * View lead — Phase 1: uses CRMService, Phase 2: visibility check, Phase 5: timeline
     */
    public function show($id)
    {
        $this->requireAdmin();
        $adminId = $this->getCurrentUserId();
        $role = $this->getCurrentUserRole();

        $lead = $this->crm->getLeadById((int)$id);
        if (!$lead) {
            $this->setFlash('error', 'Lead not found');
            return $this->redirect('/admin/leads');
        }

        // Phase 2: Visibility check — non-admins can only see own leads
        if (!in_array($role, ['admin', 'super_admin', 'manager'])) {
            if ((int)($lead['assigned_to'] ?? 0) !== $adminId) {
                $this->setFlash('error', 'You do not have permission to view this lead');
                return $this->redirect('/admin/leads');
            }
        }

        $timeline = $this->crm->getLeadTimeline((int)$id, 100);
        $interactions = $this->crm->getLeadInteractions((int)$id, 50);
        $tasks = $this->crm->getLeadTasks((int)$id);
        $deals = $this->crm->getDeals(['lead_id' => (int)$id]);
        $scoreBreakdown = $this->crm->getScoreBreakdown((int)$id);
        $commission = $this->crm->estimateCommission((int)$id);
        $sourceDetails = $this->crm->getLeadSourceDetails((int)$id);
        $assignments = $this->crm->getLeadAssignments((int)$id);

        // Notes
        $notes = [];
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT n.*, u.name as created_by_name FROM lead_notes n LEFT JOIN users u ON n.created_by = u.id WHERE n.lead_id = ? ORDER BY n.created_at DESC LIMIT 50");
            $stmt->execute([(int)$id]);
            $notes = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {}

        // Available agents for reassignment
        $agents = [];
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $agents = $db->query("SELECT id, name FROM users WHERE role IN ('associate','employee','agent') AND deleted_at IS NULL ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {}

        return $this->render('admin/leads/show', [
            'lead' => $lead,
            'timeline' => $timeline,
            'interactions' => $interactions,
            'tasks' => $tasks,
            'deals' => $deals,
            'score_breakdown' => $scoreBreakdown,
            'commission' => $commission,
            'source_details' => $sourceDetails,
            'assignments' => $assignments,
            'notes' => $notes,
            'agents' => $agents,
            'page_title' => 'Lead: ' . ($lead['name'] ?? ''),
        ]);
    }
    
    /**
     * Lead sources
     */
    public function sources()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            
            // Source distribution
            $stmt = $db->query("SELECT source, COUNT(*) as count FROM leads GROUP BY source ORDER BY count DESC");
            $sourceRows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // For each source, get monthly and conversion
            $sourceData = [];
            foreach ($sourceRows as $row) {
                $src = $row['source'];
                $mStmt = $db->prepare("SELECT COUNT(*) as cnt FROM leads WHERE source = ? AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
                $mStmt->execute([$src]);
                $monthly = (int)$mStmt->fetch(\PDO::FETCH_ASSOC)['cnt'];
                
                $cStmt = $db->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status IN ('converted','closed') THEN 1 ELSE 0 END) as converted FROM leads WHERE source = ?");
                $cStmt->execute([$src]);
                $cData = $cStmt->fetch(\PDO::FETCH_ASSOC);
                $convPct = $cData['total'] > 0 ? round(($cData['converted'] / $cData['total']) * 100, 1) : 0;
                
                $sourceData[] = [
                    'source' => $src,
                    'count' => (int)$row['count'],
                    'monthly' => $monthly,
                    'conversion_pct' => $convPct,
                ];
            }
            
            // Monthly trend (last 6 months)
            $trendStmt = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM leads WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month ASC");
            $monthlyTrend = $trendStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $totalStmt = $db->query("SELECT COUNT(*) as total FROM leads");
            $totalLeads = (int)$totalStmt->fetch(\PDO::FETCH_ASSOC)['total'];
            
            $monthTotalStmt = $db->query("SELECT COUNT(*) as cnt FROM leads WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
            $monthlyLeads = (int)$monthTotalStmt->fetch(\PDO::FETCH_ASSOC)['cnt'];
            
        } catch (\Exception $e) {
            $sourceData = [];
            $monthlyTrend = [];
            $totalLeads = 0;
            $monthlyLeads = 0;
        }
        
        $this->render('admin/leads/sources', [
            'page_title' => 'Lead Source Analytics',
            'sourceData' => $sourceData,
            'monthlyTrend' => $monthlyTrend,
            'totalLeads' => $totalLeads,
            'monthlyLeads' => $monthlyLeads,
        ]);
    }
    
    public function status()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $statuses = $db->query("SELECT status, COUNT(*) as cnt FROM leads WHERE deleted_at IS NULL GROUP BY status ORDER BY cnt DESC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $total = (int)$db->query("SELECT COUNT(*) FROM leads WHERE deleted_at IS NULL")->fetchColumn();
            $by_source = $db->query("SELECT source, status, COUNT(*) as cnt FROM leads WHERE deleted_at IS NULL GROUP BY source, status ORDER BY source, cnt DESC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $statuses = []; $total = 0; $by_source = [];
        }
        return $this->render('admin/leads/status', ['statuses' => $statuses, 'total' => $total, 'by_source' => $by_source]);
    }

    public function followups()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $pending = $db->query("SELECT l.*, u.name as assignee_name FROM leads l LEFT JOIN users u ON l.assigned_to=u.id WHERE l.next_activity_date IS NOT NULL AND l.next_activity_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND l.status NOT IN ('converted','closed','dead') AND l.deleted_at IS NULL ORDER BY l.next_activity_date ASC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $recent = $db->query("SELECT la.*, l.name as lead_name FROM lead_activities la LEFT JOIN leads l ON la.lead_id=l.id ORDER BY la.activity_date DESC LIMIT 30")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $pending = []; $recent = [];
        }
        return $this->render('admin/leads/followups', ['pending' => $pending, 'recent' => $recent]);
    }

    public function scoring()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $scored = $db->query("SELECT l.*, u.name as assignee_name FROM leads l LEFT JOIN users u ON l.assigned_to=u.id WHERE l.deleted_at IS NULL ORDER BY l.lead_score DESC LIMIT 50")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $scored = [];
        }
        return $this->render('admin/leads/scoring', ['scored' => $scored]);
    }

    public function bulk()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $leads = $db->query("SELECT l.*, u.name as assignee_name FROM leads l LEFT JOIN users u ON l.assigned_to=u.id WHERE l.deleted_at IS NULL ORDER BY l.created_at DESC LIMIT 100")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $leads = [];
        }
        return $this->render('admin/leads/bulk', ['leads' => $leads]);
    }

    public function import()
    {
        $this->requireAdmin();
        return $this->render('admin/leads/import', []);
    }

    public function analysis()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $total = (int)$db->query("SELECT COUNT(*) FROM leads WHERE deleted_at IS NULL")->fetchColumn();
            $converted = (int)$db->query("SELECT COUNT(*) FROM leads WHERE status='converted' AND deleted_at IS NULL")->fetchColumn();
            $conv_rate = $total > 0 ? round(($converted / $total) * 100, 1) : 0;
            $by_source = $db->query("SELECT source, COUNT(*) as total, SUM(CASE WHEN status='converted' THEN 1 ELSE 0 END) as converted FROM leads WHERE deleted_at IS NULL GROUP BY source ORDER BY total DESC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $monthly = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total, SUM(CASE WHEN status='converted' THEN 1 ELSE 0 END) as converted FROM leads WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND deleted_at IS NULL GROUP BY month ORDER BY month ASC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $by_city = $db->query("SELECT city, COUNT(*) as cnt FROM leads WHERE city IS NOT NULL AND city != '' AND deleted_at IS NULL GROUP BY city ORDER BY cnt DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $total = 0; $converted = 0; $conv_rate = 0; $by_source = []; $monthly = []; $by_city = [];
        }
        return $this->render('admin/leads/analysis', [
            'total' => $total, 'converted' => $converted, 'conv_rate' => $conv_rate,
            'by_source' => $by_source, 'monthly' => $monthly, 'by_city' => $by_city,
        ]);
    }

    public function getDocuments($id)
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $lead = $db->prepare("SELECT * FROM leads WHERE id = ?");
            $lead->execute([$id]);
            $lead = $lead->fetch(\PDO::FETCH_ASSOC);
            $notesStmt = $db->prepare("SELECT * FROM lead_notes WHERE lead_id = ? ORDER BY created_at DESC");
            $notesStmt->execute([$id]);
            $notes = $notesStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $activities = $db->prepare("SELECT * FROM lead_activities WHERE lead_id=? ORDER BY activity_date DESC LIMIT 20");
            $activities->execute([$id]);
            $activities = $activities->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $lead = null; $notes = []; $activities = [];
        }
        return $this->render('admin/leads/documents', ['lead_id' => $id, 'lead' => $lead, 'notes' => $notes, 'activities' => $activities]);
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $lead = $this->crm->getLeadById((int)$id);
        if (!$lead) {
            $this->setFlash('error', 'Lead not found');
            return $this->redirect('/admin/leads');
        }
        $assignees = [];
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $assignees = $db->query("SELECT id, name FROM users WHERE role IN ('employee','admin','manager','associate','agent') AND deleted_at IS NULL ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {}
        return $this->render('admin/leads/edit', ['lead' => $lead, 'assignees' => $assignees]);
    }

    /**
     * Update lead — Phase 1: uses CRMService for audit trail
     */
    public function update($id)
    {
        $this->requireAdmin();
        $adminId = $this->getCurrentUserId();
        $role = $this->getCurrentUserRole();

        $lead = $this->crm->getLeadById((int)$id);
        if (!$lead) {
            $this->setFlash('error', 'Lead not found');
            return $this->redirect('/admin/leads');
        }

        // Phase 2: Visibility check
        if (!in_array($role, ['admin', 'super_admin', 'manager'])) {
            if ((int)($lead['assigned_to'] ?? 0) !== $adminId) {
                $this->setFlash('error', 'You do not have permission to edit this lead');
                return $this->redirect('/admin/leads');
            }
        }

        $result = $this->crm->updateLead((int)$id, [
            'name' => trim($_POST['name'] ?? $lead['name']),
            'phone' => trim($_POST['phone'] ?? $lead['phone']),
            'email' => trim($_POST['email'] ?? $lead['email']),
            'company' => trim($_POST['company'] ?? $lead['company']),
            'source' => trim($_POST['source'] ?? $lead['source']),
            'property_interest' => trim($_POST['property_interest'] ?? $lead['property_interest']),
            'budget' => isset($_POST['budget']) ? floatval($_POST['budget']) : $lead['budget'],
            'location_preference' => trim($_POST['location_preference'] ?? $lead['location_preference']),
            'city' => trim($_POST['city'] ?? $lead['city']),
            'notes' => trim($_POST['notes'] ?? $lead['notes']),
            'tags' => trim($_POST['tags'] ?? $lead['tags']),
            'priority' => trim($_POST['priority'] ?? $lead['priority']),
            'assigned_to' => isset($_POST['assigned_to']) ? ($_POST['assigned_to'] !== '' ? (int)$_POST['assigned_to'] : null) : $lead['assigned_to'],
        ]);

        if ($result['success']) {
            $this->crm->logActivity((int)$id, $adminId, 'update', 'Lead updated', 'Lead details modified by admin');
            $this->setFlash('success', 'Lead updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update: ' . ($result['error'] ?? 'Unknown'));
        }
        return $this->redirect("/admin/leads/$id");
    }

    public function destroy($id)
    {
        $this->requireAdmin();
        $adminId = $this->getCurrentUserId();

        $result = $this->crm->deleteLead((int)$id);
        if ($result['success']) {
            $this->crm->logActivity((int)$id, $adminId, 'delete', 'Lead deleted', 'Lead soft-deleted by admin');
            $this->setFlash('success', 'Lead moved to trash (recoverable)');
        } else {
            $this->setFlash('error', 'Failed to delete: ' . ($result['error'] ?? 'Unknown'));
        }
        return $this->redirect('/admin/leads');
    }

    public function trash()
    {
        $this->requireAdmin();

        $filters = [
            'search' => $_GET['search'] ?? null,
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 25),
        ];

        $result = $this->crm->getDeletedLeads($filters);

        return $this->render('admin/leads/trash', [
            'leads' => $result['leads'],
            'total' => $result['total'],
            'page' => $result['page'],
            'per_page' => $result['per_page'],
            'total_pages' => $result['total_pages'],
            'filters' => $filters,
        ]);
    }

    public function restore($id)
    {
        $this->requireAdmin();
        $adminId = $this->getCurrentUserId();

        $result = $this->crm->restoreLead((int)$id);
        if ($result['success']) {
            $this->crm->logActivity((int)$id, $adminId, 'restore', 'Lead restored', 'Lead restored from trash by admin');
            $this->setFlash('success', 'Lead restored successfully');
        } else {
            $this->setFlash('error', 'Failed to restore: ' . ($result['error'] ?? 'Unknown'));
        }
        return $this->redirect('/admin/leads/trash');
    }

    public function permanentDelete($id)
    {
        $this->requireAdmin();

        $result = $this->crm->permanentDeleteLead((int)$id);
        if ($result['success']) {
            $this->setFlash('success', 'Lead permanently deleted');
        } else {
            $this->setFlash('error', 'Failed to permanently delete');
        }
        return $this->redirect('/admin/leads/trash');
    }

    public function export()
    {
        $this->requireAdmin();

        $guard = \App\Services\CRMGuard::getInstance();
        if (!$guard->isExportEnabled()) {
            $_SESSION['error'] = 'Lead export is disabled by administrator';
            $this->redirect('/admin/leads');
            return;
        }

        $filters = [
            'search' => $_GET['search'] ?? null,
            'status' => $_GET['status'] ?? null,
            'source' => $_GET['source'] ?? null,
            'assigned_to' => $_GET['assigned_to'] ?? null,
        ];

        $userId = $this->getCurrentUserId();
        $role = $this->getCurrentUserRole();
        $result = $this->crm->getLeads(array_merge($filters, ['per_page' => 9999]), $userId, $role);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="leads_export_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Source', 'Status', 'Priority', 'Score', 'Budget', 'City', 'Assigned To', 'Created At']);

        foreach ($result['leads'] as $lead) {
            fputcsv($output, [
                $lead['id'] ?? '',
                $lead['name'] ?? '',
                $lead['email'] ?? '',
                $lead['phone'] ?? '',
                $lead['source'] ?? '',
                $lead['status'] ?? '',
                $lead['priority'] ?? '',
                $lead['lead_score'] ?? 0,
                $lead['budget'] ?? '',
                $lead['city'] ?? '',
                $lead['assigned_to_name'] ?? $lead['assigned_by_name'] ?? '',
                $lead['created_at'] ?? '',
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Add note — Phase 1: uses CRMService for activity logging
     */
    public function addNote($id)
    {
        $this->requireAdmin();
        $adminId = $this->getCurrentUserId();
        $noteText = trim($_POST['note'] ?? '');

        if (empty($noteText)) {
            $this->setFlash('error', 'Note text is required');
            return $this->redirect("/admin/leads/$id");
        }

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO lead_notes (lead_id, note, content, created_by, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([(int)$id, $noteText, $noteText, $adminId]);
        } catch (\Exception $e) {
            error_log('LeadController::addNote error: ' . $e->getMessage());
        }

        $this->crm->logActivity((int)$id, $adminId, 'note', 'Note added', $noteText);

        // Phase 4: SLA trigger on interaction
        try {
            $slaTrigger = new \App\Services\SLATriggerService();
            $slaTrigger->onInteractionLogged((int)$id, 'note');
        } catch (\Exception $e) {
            error_log('LeadController::addNote SLA trigger error: ' . $e->getMessage());
        }

        return $this->redirect("/admin/leads/$id");
    }

    /**
     * Update status — Phase 1: uses CRMService for pipeline tracking
     */
    public function updateStatus($id)
    {
        $this->requireAdmin();
        $adminId = $this->getCurrentUserId();
        $newStatus = $_POST['status'] ?? 'new';

        $result = $this->crm->moveLeadToStage((int)$id, $newStatus, $adminId);

        if ($result['success']) {
            try {
                $automation = new \App\Services\AutomationTriggerService();
                $automation->onLeadStatusChange($id, $result['old_status'], $newStatus);
            } catch (\Exception $e) {
                error_log('LeadController::updateStatus automation error: ' . $e->getMessage());
            }

            try {
                \App\Services\Cache\HotPathCacheService::invalidateAdminDashboard();
            } catch (\Exception $e) {}

            // Phase 4: SLA trigger on status change
            try {
                $slaTrigger = new \App\Services\SLATriggerService();
                $lead = $this->crm->getLeadById((int)$id);
                $slaTrigger->onStatusChanged((int)$id, $result['old_status'], $newStatus, $lead ?? []);
            } catch (\Exception $e) {
                error_log('LeadController::updateStatus SLA trigger error: ' . $e->getMessage());
            }

            $this->setFlash('success', "Status changed: {$result['old_status']} → $newStatus");
        } else {
            $this->setFlash('error', 'Failed to update status');
        }
        return $this->redirect("/admin/leads/$id");
    }

    /**
     * Assign lead — Phase 1: uses CRMService for assignment audit trail
     */
    public function assign($id)
    {
        $this->requireAdmin();
        $adminId = $this->getCurrentUserId();
        $assignedTo = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
        $reason = trim($_POST['reason'] ?? 'Admin assignment');

        if (!$assignedTo) {
            $this->setFlash('error', 'Please select an assignee');
            return $this->redirect("/admin/leads/$id");
        }

        $result = $this->crm->assignLead((int)$id, $assignedTo, $adminId, $reason);
        if ($result['success']) {
            $this->setFlash('success', 'Lead assigned successfully');
        } else {
            $this->setFlash('error', 'Failed to assign: ' . ($result['error'] ?? 'Unknown'));
        }
        return $this->redirect("/admin/leads/$id");
    }
    public function uploadDocument($id) { try { $this->setFlash('info', 'Document upload feature available'); } catch (\Exception $e) {} return $this->redirect("/admin/leads/$id"); }
    public function deleteDocument($id, $docId) {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $db->query("DELETE FROM lead_documents WHERE id = ? AND lead_id = ?", [$docId, $id]);
            $this->crm->logActivity((int)$id, $this->getCurrentUserId(), 'document_delete', 'Document deleted', "Document #$docId deleted");
            $this->setFlash('success', 'Document deleted');
        } catch (\Exception $e) { $this->setFlash('error', $e->getMessage()); }
        return $this->redirect("/admin/leads/$id");
    }

    /**
     * Lead Assignment Page — Phase 1: uses CRMService
     */
    public function assignPage()
    {
        $this->requireAdmin();
        $db = \App\Core\Database\Database::getInstance()->getConnection();

        $unassigned = $db->query("SELECT l.id, l.name, l.phone, l.email, l.source, l.status, l.lead_score, l.created_at,
            u.name as created_by_name
            FROM leads l
            LEFT JOIN users u ON u.id = l.created_by
            WHERE l.assigned_to IS NULL AND l.deleted_at IS NULL
            ORDER BY l.created_at DESC LIMIT 100")->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $assignees = $db->query("SELECT id, name, email, role FROM users WHERE role IN ('associate','agent','employee','telecaller') AND deleted_at IS NULL ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $recentAssignments = $db->query("SELECT ca.*, l.name as lead_name, u1.name as from_name, u2.name as to_name, u3.name as by_name
            FROM crm_assignments ca
            LEFT JOIN leads l ON l.id = ca.lead_id
            LEFT JOIN users u1 ON u1.id = ca.assigned_from
            LEFT JOIN users u2 ON u2.id = ca.assigned_to
            LEFT JOIN users u3 ON u3.id = ca.assigned_by
            ORDER BY ca.created_at DESC LIMIT 20")->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return $this->render('admin/leads/assign', [
            'unassigned' => $unassigned,
            'assignees' => $assignees,
            'recent_assignments' => $recentAssignments,
        ]);
    }

    /**
     * Process single or bulk lead assignment — Phase 1: uses CRMService
     */
    public function processAssignment()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/admin/leads/assign');
        }

        $leadIds = $_POST['lead_ids'] ?? [];
        $assignedTo = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
        $reason = trim($_POST['reason'] ?? '');

        if (empty($leadIds) || !$assignedTo) {
            $this->setFlash('error', 'Please select leads and an assignee.');
            return $this->redirect('/admin/leads/assign');
        }

        if (!is_array($leadIds)) $leadIds = [$leadIds];

        $adminId = $this->getCurrentUserId();
        $assigned = 0;

        foreach ($leadIds as $leadId) {
            $result = $this->crm->assignLead((int)$leadId, $assignedTo, $adminId, $reason ?: 'Admin assignment');
            if ($result['success']) $assigned++;
        }

        $this->setFlash('success', "{$assigned} lead(s) assigned successfully.");
        return $this->redirect('/admin/leads/assign');
    }

    /**
     * AJAX bulk action handler — status change, assign, delete
     */
    public function bulkAction()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $action = $_POST['action'] ?? '';
        $value = $_POST['value'] ?? '';
        $idsRaw = $_POST['ids'] ?? '';

        if (empty($idsRaw)) {
            echo json_encode(['success' => false, 'error' => 'No leads selected']);
            return;
        }

        $ids = array_map('intval', explode(',', $idsRaw));
        $ids = array_filter($ids, fn($id) => $id > 0);

        if (empty($ids)) {
            echo json_encode(['success' => false, 'error' => 'Invalid lead IDs']);
            return;
        }

        $guard = \App\Services\CRMGuard::getInstance();
        if (!$guard->isCrmEnabled()) {
            echo json_encode(['success' => false, 'error' => 'CRM is disabled']);
            return;
        }

        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $adminId = $this->getCurrentUserId();
        $affected = 0;

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            switch ($action) {
                case 'status':
                    $validStatuses = ['new','contacted','qualified','proposal','negotiation','converted','closed','lost','dead'];
                    if (!in_array($value, $validStatuses)) {
                        echo json_encode(['success' => false, 'error' => 'Invalid status']);
                        return;
                    }
                    $stmt = $db->prepare("UPDATE leads SET status = ?, updated_at = NOW() WHERE id IN ($placeholders) AND deleted_at IS NULL");
                    $stmt->execute(array_merge([$value], $ids));
                    $affected = $stmt->rowCount();
                    break;

                case 'assign':
                    $assignee = (int)$value;
                    if ($assignee <= 0) {
                        echo json_encode(['success' => false, 'error' => 'Invalid assignee']);
                        return;
                    }
                    $stmt = $db->prepare("UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id IN ($placeholders) AND deleted_at IS NULL");
                    $stmt->execute(array_merge([$assignee], $ids));
                    $affected = $stmt->rowCount();
                    break;

                case 'delete':
                    if (!$guard->canDeleteLead('admin')) {
                        echo json_encode(['success' => false, 'error' => 'Delete permission denied']);
                        return;
                    }
                    $stmt = $db->prepare("UPDATE leads SET deleted_at = NOW() WHERE id IN ($placeholders) AND deleted_at IS NULL");
                    $stmt->execute($ids);
                    $affected = $stmt->rowCount();
                    break;

                default:
                    echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);
                    return;
            }

            echo json_encode(['success' => true, 'affected' => $affected]);
        } catch (\Throwable $e) {
            error_log('BulkAction error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
    }
}