<?php
/**
 * Lead Management Controller
 * CRM: Leads, Enquiries, Follow-ups
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;

class LeadController extends BaseController
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
        return $this->render('admin/leads/create', []);
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
    
    /**
     * Lead status management
     */
    public function status()
    {
        $this->requireAdmin();
        return $this->render('admin/leads/status', []);
    }
    
    /**
     * Follow-ups
     */
    public function followups()
    {
        $this->requireAdmin();
        return $this->render('admin/leads/followups', []);
    }
    
    /**
     * Lead scoring/prioritization
     */
    public function scoring()
    {
        $this->requireAdmin();
        return $this->render('admin/leads/scoring', []);
    }
    
    /**
     * Bulk actions
     */
    public function bulk()
    {
        $this->requireAdmin();
        return $this->render('admin/leads/bulk', []);
    }
    
    /**
     * Import leads
     */
    public function import()
    {
        $this->requireAdmin();
        return $this->render('admin/leads/import', []);
    }
    
    /**
     * Lead analysis/reports
     */
    public function analysis()
    {
        $this->requireAdmin();
        return $this->render('admin/leads/analysis', []);
    }

    public function getDocuments($id)
    {
        $this->requireAdmin();
        return $this->render('admin/leads/documents', ['lead_id' => $id]);
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
}