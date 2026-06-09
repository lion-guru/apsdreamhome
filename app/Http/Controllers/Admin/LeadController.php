<?php
/**
 * Lead Management Controller
 * CRM: Leads, Enquiries, Follow-ups
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class LeadController extends AdminController
{
    /**
     * All leads list
     */
    public function index()
    {
        $this->requireAdmin();
        $leads = \App\Models\Lead::all();
        return $this->render('admin/leads/index', ['leads' => $leads]);
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
            $assignees = $db->query("SELECT id, name FROM users WHERE role IN ('employee','admin','manager') ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $sources = []; $statuses = []; $assignees = [];
        }
        return $this->render('admin/leads/create', ['sources' => $sources, 'statuses' => $statuses, 'assignees' => $assignees]);
    }
    
    /**
     * Store lead
     */
    public function store()
    {
        $this->requireAdmin();
        $this->redirect('/admin/leads');
    }
    
    /**
     * View lead
     */
    public function show($id)
    {
        $this->requireAdmin();
        $lead = \App\Models\Lead::find($id);
        return $this->render('admin/leads/show', ['lead' => $lead]);
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
            $notes = $db->prepare("SELECT ln.*, u.name as author FROM lead_notes ln LEFT JOIN users u ON ln.created_by=u.id WHERE ln.lead_id=? ORDER BY ln.created_at DESC");
            $notes->execute([$id]);
            $notes = $notes->fetchAll(\PDO::FETCH_ASSOC) ?: [];
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
        $lead = \App\Models\Lead::find($id);
        if (!$lead) {
            return $this->render('admin/leads/edit', ['error' => 'Lead not found', 'lead' => null]);
        }
        return $this->render('admin/leads/edit', ['lead' => $lead]);
    }

    public function update($id) { return $this->render('admin/leads/edit', ['lead' => \App\Models\Lead::find($id)]); }
    public function destroy($id) { try { \App\Models\Lead::delete($id); $this->setFlashMessage('success', 'Lead deleted'); } catch (\Exception $e) { $this->setFlashMessage('error', $e->getMessage()); } return $this->redirect('/admin/leads'); }
    public function addNote($id) { try { $this->db->query("INSERT INTO lead_notes (lead_id, note, created_by, created_at) VALUES (?, ?, ?, NOW())", [$id, $_POST['note'] ?? '', $_SESSION['admin_id'] ?? 0]); } catch (\Exception $e) {} return $this->redirect("/admin/leads/show/$id"); }
    public function updateStatus($id) {
        try {
            $this->db->query("UPDATE leads SET status = ? WHERE id = ?", [$_POST['status'] ?? 'new', $id]);
            // Hot-path: lead status changes affect admin dashboard KPI bundle.
            \App\Services\Cache\HotPathCacheService::invalidateAdminDashboard();
        } catch (\Exception $e) {}
        return $this->redirect("/admin/leads/show/$id");
    }
    public function uploadDocument($id) { try { $this->setFlashMessage('info', 'Document upload feature available'); } catch (\Exception $e) {} return $this->redirect("/admin/leads/show/$id"); }
    public function deleteDocument($id, $docId) { try { $this->db->query("DELETE FROM lead_documents WHERE id = ? AND lead_id = ?", [$docId, $id]); $this->setFlashMessage('success', 'Document deleted'); } catch (\Exception $e) { $this->setFlashMessage('error', $e->getMessage()); } return $this->redirect("/admin/leads/show/$id"); }
}