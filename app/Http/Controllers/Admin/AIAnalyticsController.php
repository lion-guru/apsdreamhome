<?php

namespace App\Http\Controllers\Admin;

class AIAnalyticsController extends AdminController
{
    public function index()
    {
        $this->data['page_title'] = 'AI Analytics';
        $this->data['analytics'] = [];
        $this->render('admin/ai/analytics');
    }

    public function reports()
    {
        $this->data['page_title'] = 'AI Reports';
        try {
            $db = $this->db;

            $this->data['totalCalls'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions")->fetchColumn());
            $this->data['completedCalls'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE status='completed'")->fetchColumn());
            $this->data['totalLeads'] = (int)($db->query("SELECT COUNT(*) FROM leads")->fetchColumn());
            $this->data['hotLeads'] = (int)($db->query("SELECT COUNT(*) FROM leads WHERE score >= 70")->fetchColumn());
            $this->data['totalCampaigns'] = (int)($db->query("SELECT COUNT(*) FROM ai_calling_campaigns")->fetchColumn());
            $this->data['activeCampaigns'] = (int)($db->query("SELECT COUNT(*) FROM ai_calling_campaigns WHERE status='active'")->fetchColumn());

            $this->data['conversionBySource'] = $db->fetchAll(
                "SELECT source, COUNT(*) total, SUM(CASE WHEN status='qualified' OR status='proposal' THEN 1 ELSE 0 END) qualified, SUM(CASE WHEN status='won' THEN 1 ELSE 0 END) won FROM leads WHERE source IS NOT NULL GROUP BY source ORDER BY total DESC"
            ) ?: [];

            $this->data['callsByScript'] = $db->fetchAll(
                "SELECT s.script_name, COUNT(*) total, SUM(CASE WHEN acs.status='completed' THEN 1 ELSE 0 END) completed, SUM(CASE WHEN acs.customer_response='interested' THEN 1 ELSE 0 END) interested FROM ai_call_sessions acs LEFT JOIN ai_calling_scripts s ON acs.script_id = s.id WHERE s.script_name IS NOT NULL GROUP BY s.script_name ORDER BY total DESC"
            ) ?: [];

            $this->data['weeklyLeads'] = $db->fetchAll(
                "SELECT DATE(created_at) day, COUNT(*) total, SUM(CASE WHEN status IN ('qualified','proposal','won') THEN 1 ELSE 0 END) qualified FROM leads WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY day ORDER BY day"
            ) ?: [];

            $this->data['leadLabels'] = array_map(fn($d) => date('d M', strtotime($d['day'])), $this->data['weeklyLeads']);
            $this->data['leadTotals'] = array_column($this->data['weeklyLeads'], 'total');
            $this->data['leadQualified'] = array_column($this->data['weeklyLeads'], 'qualified');

        } catch (\Exception $e) {
            $this->data['totalCalls'] = $this->data['completedCalls'] = $this->data['totalLeads'] = 0;
            $this->data['hotLeads'] = $this->data['totalCampaigns'] = $this->data['activeCampaigns'] = 0;
            $this->data['conversionBySource'] = $this->data['callsByScript'] = $this->data['weeklyLeads'] = [];
            $this->data['leadLabels'] = $this->data['leadTotals'] = $this->data['leadQualified'] = [];
        }
        $this->render('admin/ai/reports');
    }

    public function insights()
    {
        $this->data['page_title'] = 'AI Insights';
        try {
            $db = $this->db;

            $this->data['totalLeads'] = (int)($db->query("SELECT COUNT(*) FROM leads")->fetchColumn());
            $this->data['newLeadsWeek'] = (int)($db->query("SELECT COUNT(*) FROM leads WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn());
            $this->data['hotLeads'] = (int)($db->query("SELECT COUNT(*) FROM leads WHERE score >= 70")->fetchColumn());
            $this->data['coldLeads'] = (int)($db->query("SELECT COUNT(*) FROM leads WHERE score < 30 AND score > 0")->fetchColumn());
            $this->data['unassignedLeads'] = (int)($db->query("SELECT COUNT(*) FROM leads WHERE assigned_to IS NULL OR assigned_to = 0")->fetchColumn());

            $this->data['totalCalls'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions")->fetchColumn());
            $this->data['interestedCalls'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE customer_response='interested'")->fetchColumn());
            $this->data['avgCallDuration'] = round((float)($db->query("SELECT COALESCE(AVG(duration_seconds),0) FROM ai_call_sessions WHERE duration_seconds > 0")->fetchColumn()));

            $this->data['staleLeads'] = $db->fetchAll(
                "SELECT l.id, l.name, l.phone, l.status, l.score, l.updated_at, DATEDIFF(NOW(), l.updated_at) as days_stale FROM leads l WHERE l.status NOT IN ('won','lost') AND DATEDIFF(NOW(), l.updated_at) > 7 ORDER BY l.updated_at ASC LIMIT 10"
            ) ?: [];

            $this->data['topPerformingScripts'] = $db->fetchAll(
                "SELECT s.script_name, s.total_calls_made, s.total_interested, s.conversion_rate FROM ai_calling_scripts s WHERE s.total_calls_made > 0 ORDER BY s.conversion_rate DESC LIMIT 5"
            ) ?: [];

            $this->data['sourcePerformance'] = $db->fetchAll(
                "SELECT source, COUNT(*) total, ROUND(SUM(CASE WHEN status='won' THEN 1 ELSE 0 END)/COUNT(*)*100,1) win_rate FROM leads WHERE source IS NOT NULL GROUP BY source HAVING total > 5 ORDER BY win_rate DESC"
            ) ?: [];

            $this->data['insights'] = [];
            if ($this->data['unassignedLeads'] > 20) {
                $this->data['insights'][] = ['type' => 'warning', 'icon' => 'fas fa-user-slash', 'title' => 'Unassigned Leads', 'text' => $this->data['unassignedLeads'] . ' leads are not assigned to any agent. Auto-assign or manually distribute.'];
            }
            if ($this->data['coldLeads'] > $this->data['hotLeads'] * 2) {
                $this->data['insights'][] = ['type' => 'info', 'icon' => 'fas fa-thermometer-half', 'title' => 'Cold Lead Heavy', 'text' => 'Cold leads (' . $this->data['coldLeads'] . ') outnumber hot leads (' . $this->data['hotLeads'] . ') by 2x. Consider re-engagement campaigns.'];
            }
            if ($this->data['totalCalls'] > 0 && $this->data['interestedCalls'] / $this->data['totalCalls'] < 0.15) {
                $this->data['insights'][] = ['type' => 'danger', 'icon' => 'fas fa-phone-slash', 'title' => 'Low Interest Rate', 'text' => 'Only ' . round($this->data['interestedCalls']/$this->data['totalCalls']*100, 1) . '% of calls result in interest. Review scripts and targeting.'];
            }
            if ($this->data['newLeadsWeek'] > 50) {
                $this->data['insights'][] = ['type' => 'success', 'icon' => 'fas fa-chart-line', 'title' => 'High Lead Volume', 'text' => $this->data['newLeadsWeek'] . ' new leads this week. Pipeline is healthy.'];
            }
            if (empty($this->data['insights'])) {
                $this->data['insights'][] = ['type' => 'success', 'icon' => 'fas fa-check-circle', 'title' => 'All Good', 'text' => 'No critical insights at this time. All metrics are within normal range.'];
            }

        } catch (\Exception $e) {
            $this->data['totalLeads'] = $this->data['newLeadsWeek'] = $this->data['hotLeads'] = 0;
            $this->data['coldLeads'] = $this->data['unassignedLeads'] = $this->data['totalCalls'] = 0;
            $this->data['interestedCalls'] = $this->data['avgCallDuration'] = 0;
            $this->data['staleLeads'] = $this->data['topPerformingScripts'] = $this->data['sourcePerformance'] = [];
            $this->data['insights'] = [['type' => 'info', 'icon' => 'fas fa-info-circle', 'title' => 'No Data', 'text' => 'Insufficient data for analysis.']];
        }
        $this->render('admin/ai/insights');
    }
}
