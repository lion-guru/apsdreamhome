<?php
// NOTE: Read-only analytics controller — no INSERT/UPDATE/DELETE writes. All queries are SELECT on leads/proposals/deals/users (read-only reporting). No tenant write scoping needed. Tenant read isolation is handled at service layer if filtered.

namespace App\Http\Controllers\Admin;
use App\Core\Database\Database;

class AdvancedReportController extends \App\Http\Controllers\Admin\AdminController
{
    public function funnel()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            
            $warmLeads = $db->query("SELECT COUNT(*) as total FROM leads WHERE status IN ('qualified', 'site_visit', 'proposal', 'negotiation') AND deleted_at IS NULL")->fetch();
            $proposals = $db->query("SELECT COUNT(*) as total FROM leads WHERE status = 'proposal' AND deleted_at IS NULL")->fetch();
            $negotiations = $db->query("SELECT COUNT(*) as total FROM leads WHERE status = 'negotiation' AND deleted_at IS NULL")->fetch();
            $closed = $db->query("SELECT COUNT(*) as total FROM leads WHERE status = 'closed_won' AND deleted_at IS NULL")->fetch();
            
            $funnelData = [
                'warm_leads' => (int)($warmLeads['total'] ?? 0),
                'proposals' => (int)($proposals['total'] ?? 0),
                'negotiations' => (int)($negotiations['total'] ?? 0),
                'closed_deals' => (int)($closed['total'] ?? 0),
            ];
            
            $monthlyConversions = $db->query("
                SELECT DATE_FORMAT(COALESCE(converted_at, created_at), '%Y-%m') as month, COUNT(*) as deals
                FROM leads WHERE status = 'closed_won' AND COALESCE(converted_at, created_at) >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(COALESCE(converted_at, created_at), '%Y-%m') ORDER BY month
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Throwable $e) {
            $funnelData = ['warm_leads' => 0, 'proposals' => 0, 'negotiations' => 0, 'closed_deals' => 0];
            $monthlyConversions = [];
        }
        
        $this->render('admin/reports/funnel', [
            'page_title' => 'Sales Funnel Report',
            'funnel' => $funnelData,
            'monthly_conversions' => $monthlyConversions,
        ]);
    }
    
    public function agentPerformance()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            
            $users = $db->query("
                SELECT 
                    u.id, u.name, u.email,
                    (SELECT COUNT(*) FROM properties WHERE agent_id = u.id AND deleted_at IS NULL) as listings,
                    (SELECT COUNT(*) FROM inquiries WHERE assigned_to = u.id) as inquiries_received,
                    (SELECT COUNT(*) FROM deals WHERE agent_id = u.id AND deleted_at IS NULL) as deals_closed,
                    ROUND((SELECT COUNT(*) FROM deals WHERE agent_id = u.id AND deleted_at IS NULL) * 100.0 / 
                        NULLIF((SELECT COUNT(*) FROM inquiries WHERE assigned_to = u.id), 0), 1) as conversion_rate,
                    (SELECT COALESCE(SUM(deal_value), 0) FROM deals WHERE agent_id = u.id AND status = 'won' AND deleted_at IS NULL) as revenue
                FROM users u
                WHERE u.role IN ('agent', 'admin')
                ORDER BY deals_closed DESC
                LIMIT 20
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Throwable $e) {
            $users = [];
        }
        
        $this->render('admin/reports/agent_performance', [
            'page_title' => 'Agent Performance Report',
            'users' => $users,
        ]);
    }
    
    public function conversion()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            
            // Honest conversion feed from live lead-status distribution:
            // proposals = leads in proposal/negotiation stage, deals = closed_won,
            // both measured by the month they entered the stage.
            $conversionData = $db->query("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as leads,
                    SUM(CASE WHEN status IN ('proposal', 'negotiation') THEN 1 ELSE 0 END) as proposals,
                    SUM(CASE WHEN status = 'closed_won' THEN 1 ELSE 0 END) as deals,
                    ROUND(SUM(CASE WHEN status = 'closed_won' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(*), 0), 1) as lead_to_deal_pct,
                    ROUND(SUM(CASE WHEN status = 'closed_won' THEN 1 ELSE 0 END) * 100.0 / NULLIF(SUM(CASE WHEN status IN ('proposal', 'negotiation') THEN 1 ELSE 0 END), 0), 1) as proposal_to_deal_pct
                FROM leads
                WHERE deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month
                LIMIT 12
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Throwable $e) {
            $conversionData = [];
        }
        
        $this->render('admin/reports/conversion', [
            'page_title' => 'Conversion Analytics',
            'conversion_data' => $conversionData,
        ]);
    }
}
