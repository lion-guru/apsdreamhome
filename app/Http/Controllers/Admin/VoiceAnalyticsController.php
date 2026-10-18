<?php

namespace App\Http\Controllers\Admin;

use \App\Traits\TenantAwareTrait;

class VoiceAnalyticsController extends AdminController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
        $this->data = [];
    }

    public function index()
    {
        $this->requireAdmin();

        $range = $_GET['range'] ?? '7d';
        $where = $this->getDateWhere($range);

        $analytics = [
            'overview' => $this->getOverviewStats($where),
            'daily_trend' => $this->getDailyTrend($where),
            'agent_performance' => $this->getAgentPerformance($where),
            'interest_breakdown' => $this->getInterestBreakdown($where),
            'call_outcomes' => $this->getCallOutcomes($where),
            'hourly_distribution' => $this->getHourlyDistribution($where),
            'lead_conversion_funnel' => $this->getLeadConversionFunnel($where),
            'provider_comparison' => $this->getProviderComparison($where),
        ];

        $this->data = [
            'page_title' => 'Voice Calling Analytics',
            'range' => $range,
            'analytics' => $analytics,
        ];
        $this->render('admin/voice_analytics/index');
    }

    private function getDateWhere($range)
    {
        switch ($range) {
            case '24h': return "created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
            case '7d': return "created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            case '30d': return "created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            case '90d': return "created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)";
            default: return "created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        }
    }

    private function getOverviewStats($where)
    {
        $stats = [
            'total_calls' => 0,
            'connected_calls' => 0,
            'avg_duration' => 0,
            'total_leads' => 0,
            'conversion_rate' => 0,
            'cost_per_call' => 0,
        ];

        try {
            $stats['total_calls'] = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_sessions WHERE $where")['c'] ?? 0;
            $stats['connected_calls'] = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_sessions WHERE $where AND status = 'completed'")['c'] ?? 0;
            $stats['avg_duration'] = $this->db->fetch("SELECT AVG(duration) as avg FROM ai_call_sessions WHERE $where AND duration > 0")['avg'] ?? 0;
            $stats['total_leads'] = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_extracted_leads WHERE $where AND auto_created_lead_id IS NOT NULL")['c'] ?? 0;
            $stats['conversion_rate'] = $stats['total_calls'] > 0 ? round(($stats['total_leads'] / $stats['total_calls']) * 100, 2) : 0;
        } catch (\Exception $e) {}

        return $stats;
    }

    private function getDailyTrend($where)
    {
        try {
            return $this->db->fetchAll("
                SELECT DATE(created_at) as date, 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as connected,
                    SUM(CASE WHEN customer_response = 'interested' OR interest_level IN ('hot','warm') THEN 1 ELSE 0 END) as interested,
                    SUM(CASE WHEN customer_response = 'not_interested' OR interest_level = 'cold' THEN 1 ELSE 0 END) as not_interested,
                    AVG(duration) as avg_duration
                FROM ai_call_sessions 
                WHERE $where
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ");
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getAgentPerformance($where)
    {
        try {
            return $this->db->fetchAll("
                SELECT a.agent_id, a.agent_name, 
                    COUNT(s.id) as total_calls,
                    SUM(CASE WHEN s.status = 'completed' THEN 1 ELSE 0 END) as connected,
                    SUM(CASE WHEN s.customer_response = 'interested' OR s.interest_level IN ('hot','warm') THEN 1 ELSE 0 END) as interested,
                    SUM(CASE WHEN e.auto_created_lead_id IS NOT NULL THEN 1 ELSE 0 END) as leads_created,
                    AVG(s.duration) as avg_duration,
                    a.status
                FROM ai_calling_agents a
                LEFT JOIN ai_call_sessions s ON s.ai_agent_id = a.agent_id AND $where
                LEFT JOIN ai_call_extracted_leads e ON e.call_session_id = s.id
                GROUP BY a.agent_id
                ORDER BY total_calls DESC
            ");
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getInterestBreakdown($where)
    {
        try {
            return $this->db->fetchAll("
                SELECT 
                    COALESCE(interest_level, 'unknown') as level,
                    COUNT(*) as count
                FROM ai_call_sessions 
                WHERE $where
                GROUP BY interest_level
            ");
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getCallOutcomes($where)
    {
        try {
            return $this->db->fetchAll("
                SELECT 
                    COALESCE(call_outcome, customer_response, 'unknown') as outcome,
                    COUNT(*) as count
                FROM ai_call_sessions 
                WHERE $where
                GROUP BY call_outcome, customer_response
                ORDER BY count DESC
            ");
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getHourlyDistribution($where)
    {
        try {
            return $this->db->fetchAll("
                SELECT HOUR(created_at) as hour, COUNT(*) as count
                FROM ai_call_sessions 
                WHERE $where
                GROUP BY HOUR(created_at)
                ORDER BY hour
            ");
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getLeadConversionFunnel($where)
    {
        try {
            $total = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_sessions WHERE $where")['c'] ?? 0;
            $connected = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_sessions WHERE $where AND status = 'completed'")['c'] ?? 0;
            $interested = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_sessions WHERE $where AND (customer_response = 'interested' OR interest_level IN ('hot','warm'))")['c'] ?? 0;
            $leads = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_extracted_leads WHERE $where AND auto_created_lead_id IS NOT NULL")['c'] ?? 0;
            $booked = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_extracted_leads e JOIN plot_bookings b ON b.lead_id = e.auto_created_lead_id WHERE $where")['c'] ?? 0;

            return [
                ['stage' => 'Total Calls', 'count' => $total, 'pct' => 100],
                ['stage' => 'Connected', 'count' => $connected, 'pct' => $total > 0 ? round($connected/$total*100,1) : 0],
                ['stage' => 'Interested', 'count' => $interested, 'pct' => $total > 0 ? round($interested/$total*100,1) : 0],
                ['stage' => 'Leads Created', 'count' => $leads, 'pct' => $total > 0 ? round($leads/$total*100,1) : 0],
                ['stage' => 'Bookings', 'count' => $booked, 'pct' => $total > 0 ? round($booked/$total*100,1) : 0],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getProviderComparison($where)
    {
        try {
            return $this->db->fetchAll("
                SELECT 
                    a.provider as provider,
                    COUNT(s.id) as total_calls,
                    SUM(CASE WHEN s.status = 'completed' THEN 1 ELSE 0 END) as connected,
                    AVG(s.duration) as avg_duration,
                    SUM(CASE WHEN s.customer_response = 'interested' OR s.interest_level IN ('hot','warm') THEN 1 ELSE 0 END) as interested
                FROM ai_call_sessions s
                JOIN ai_calling_agents a ON a.agent_id = s.ai_agent_id
                WHERE $where
                GROUP BY a.provider
            ");
        } catch (\Exception $e) {
            return [];
        }
    }
}