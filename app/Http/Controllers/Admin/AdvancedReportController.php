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
            
            $warmLeads = $db->query("SELECT COUNT(*) as total FROM leads WHERE status IN ('warm', 'hot', 'interested') AND deleted_at IS NULL")->fetch();
            $proposals = $db->query("SELECT COUNT(*) as total FROM proposals WHERE deleted_at IS NULL")->fetch();
            $negotiations = $db->query("SELECT COUNT(*) as total FROM deals WHERE status = 'negotiation' AND deleted_at IS NULL")->fetch();
            $closed = $db->query("SELECT COUNT(*) as total FROM deals WHERE status = 'closed' AND deleted_at IS NULL")->fetch();
            
            $funnelData = [
                'warm_leads' => (int)($warmLeads['total'] ?? 0),
                'proposals' => (int)($proposals['total'] ?? 0),
                'negotiations' => (int)($negotiations['total'] ?? 0),
                'closed_deals' => (int)($closed['total'] ?? 0),
            ];
            
            $monthlyConversions = $db->query("
                SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as deals
                FROM deals WHERE status = 'closed' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
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
                    (SELECT COUNT(*) FROM inquiries WHERE agent_id = u.id AND deleted_at IS NULL) as inquiries_received,
                    (SELECT COUNT(*) FROM deals WHERE agent_id = u.id AND deleted_at IS NULL) as deals_closed,
                    ROUND((SELECT COUNT(*) FROM deals WHERE agent_id = u.id AND deleted_at IS NULL) * 100.0 / 
                        NULLIF((SELECT COUNT(*) FROM inquiries WHERE agent_id = u.id AND deleted_at IS NULL), 0), 1) as conversion_rate,
                    (SELECT COALESCE(SUM(amount), 0) FROM deals WHERE agent_id = u.id AND status = 'closed' AND deleted_at IS NULL) as revenue
                FROM users u
                WHERE u.role IN ('agent', 'admin')
                ORDER BY deals_closed DESC
                LIMIT 20
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
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
            
            $conversionData = $db->query("
                SELECT 
                    DATE_FORMAT(l.created_at, '%Y-%m') as month,
                    COUNT(DISTINCT l.id) as leads,
                    COUNT(DISTINCT p.id) as proposals,
                    COUNT(DISTINCT d.id) as deals,
                    ROUND(COUNT(DISTINCT d.id) * 100.0 / NULLIF(COUNT(DISTINCT l.id), 0), 1) as lead_to_deal_pct,
                    ROUND(COUNT(DISTINCT d.id) * 100.0 / NULLIF(COUNT(DISTINCT p.id), 0), 1) as proposal_to_deal_pct
                FROM leads l
                LEFT JOIN proposals p ON p.lead_id = l.id
                LEFT JOIN deals d ON d.lead_id = l.id AND d.status = 'closed'
                WHERE l.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(l.created_at, '%Y-%m')
                ORDER BY month
                LIMIT 12
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            $conversionData = [];
        }
        
        $this->render('admin/reports/conversion', [
            'page_title' => 'Conversion Analytics',
            'conversion_data' => $conversionData,
        ]);
    }
}
