<?php

namespace App\Http\Controllers\Admin;

class AICallingController extends AdminController
{
    public function index()
    {
        $this->data['page_title'] = 'AI Calling';
        $this->data['calls'] = [];
        $this->render('admin/ai/calling');
    }

    public function campaign()
    {
        $this->data['page_title'] = 'Calling Campaign';
        $this->render('admin/ai/calling-campaign');
    }

    public function history()
    {
        $this->data['page_title'] = 'Call History';
        $this->render('admin/ai/call-history');
    }

    public function analytics()
    {
        $this->data['page_title'] = 'Calling Analytics';
        $this->render('admin/ai/calling-analytics');
    }

    public function dashboard()
    {
        $this->data['page_title'] = 'AI Calling Dashboard';
        try {
            $db = $this->db;
            $this->data['totalCalls'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions")->fetchColumn());
            $this->data['completedCalls'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE status = 'completed'")->fetchColumn());
            $this->data['failedCalls'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE status = 'failed'")->fetchColumn());
            $this->data['successRate'] = $this->data['totalCalls'] > 0 ? round($this->data['completedCalls'] / $this->data['totalCalls'] * 100, 1) : 0;
            $this->data['avgDuration'] = (float)($db->query("SELECT COALESCE(AVG(duration_seconds),0) FROM ai_call_sessions WHERE duration_seconds > 0")->fetchColumn());
            $this->data['totalExtracted'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_extracted_leads")->fetchColumn());
            $this->data['callsToday'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE DATE(created_at) = CURDATE()")->fetchColumn());
            $this->data['callsThisWeek'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn());
            $this->data['interestedCount'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE customer_response = 'interested'")->fetchColumn());
            $this->data['hotLeads'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_extracted_leads WHERE interest_level = 'hot'")->fetchColumn());
            $weeklyData = $db->query("SELECT DATE(created_at) as day, COUNT(*) as cnt FROM ai_call_sessions WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) GROUP BY day ORDER BY day ASC")->fetchAll(\PDO::FETCH_ASSOC);
            $this->data['weekLabels'] = array_map(function($d) { return date('d M', strtotime($d['day'])); }, $weeklyData);
            $this->data['weekData'] = array_column($weeklyData, 'cnt');
            $this->data['recentCalls'] = $db->query("SELECT acs.*, l.name as lead_name FROM ai_call_sessions acs LEFT JOIN leads l ON acs.lead_id = l.id ORDER BY acs.created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $this->data['totalCalls'] = $this->data['completedCalls'] = $this->data['failedCalls'] = $this->data['totalExtracted'] = 0;
            $this->data['callsToday'] = $this->data['callsThisWeek'] = $this->data['interestedCount'] = $this->data['hotLeads'] = 0;
            $this->data['successRate'] = $this->data['avgDuration'] = 0;
            $this->data['weekLabels'] = $this->data['weekData'] = $this->data['recentCalls'] = [];
        }
        $this->render('admin/ai/calling-dashboard');
    }

    public function schedule()
    {
        $this->data['page_title'] = 'Calling Schedule';
        try {
            $db = $this->db;
            $this->data['todayScheduled'] = $db->query("SELECT acs.*, l.name as lead_name FROM ai_calling_schedule acs LEFT JOIN leads l ON acs.lead_id = l.id WHERE acs.scheduled_date = CURDATE() ORDER BY acs.scheduled_time ASC")->fetchAll(\PDO::FETCH_ASSOC);
            $this->data['todayCount'] = count($this->data['todayScheduled']);
            $this->data['pendingToday'] = (int)($db->query("SELECT COUNT(*) FROM ai_calling_schedule WHERE scheduled_date = CURDATE() AND status = 'pending'")->fetchColumn());
            $this->data['completedToday'] = (int)($db->query("SELECT COUNT(*) FROM ai_calling_schedule WHERE scheduled_date = CURDATE() AND status = 'completed'")->fetchColumn());
            $this->data['upcoming'] = $db->query("SELECT acs.*, l.name as lead_name FROM ai_calling_schedule acs LEFT JOIN leads l ON acs.lead_id = l.id WHERE acs.scheduled_date > CURDATE() AND acs.status = 'pending' ORDER BY acs.scheduled_date, acs.scheduled_time ASC LIMIT 20")->fetchAll(\PDO::FETCH_ASSOC);
            $this->data['recentCompleted'] = $db->query("SELECT acs.*, l.name as lead_name, acs2.status as call_status, acs2.customer_response, acs2.duration_seconds FROM ai_calling_schedule acs LEFT JOIN leads l ON acs.lead_id = l.id LEFT JOIN ai_call_sessions acs2 ON acs.call_session_id = acs2.id WHERE acs.status = 'completed' ORDER BY acs.updated_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
            $this->data['totalScheduled'] = (int)($db->query("SELECT COUNT(*) FROM ai_calling_schedule")->fetchColumn());
            $this->data['totalPending'] = (int)($db->query("SELECT COUNT(*) FROM ai_calling_schedule WHERE status = 'pending'")->fetchColumn());
        } catch (\Exception $e) {
            $this->data['todayScheduled'] = $this->data['upcoming'] = $this->data['recentCompleted'] = [];
            $this->data['todayCount'] = $this->data['pendingToday'] = $this->data['completedToday'] = $this->data['totalScheduled'] = $this->data['totalPending'] = 0;
        }
        $this->render('admin/ai/calling-schedule');
    }

    public function sessions()
    {
        $this->data['page_title'] = 'Call Sessions';
        try {
            $db = $this->db;
            $filterStatus = $_GET['status'] ?? '';
            $filterAgent = $_GET['agent'] ?? '';
            $filterFrom = $_GET['from'] ?? '';
            $filterTo = $_GET['to'] ?? '';
            $sql = "SELECT acs.*, l.name as lead_name FROM ai_call_sessions acs LEFT JOIN leads l ON acs.lead_id = l.id WHERE 1=1";
            $params = [];
            if ($filterStatus) { $sql .= " AND acs.status = ?"; $params[] = $filterStatus; }
            if ($filterAgent) { $sql .= " AND acs.ai_agent_id = ?"; $params[] = $filterAgent; }
            if ($filterFrom) { $sql .= " AND DATE(acs.created_at) >= ?"; $params[] = $filterFrom; }
            if ($filterTo) { $sql .= " AND DATE(acs.created_at) <= ?"; $params[] = $filterTo; }
            $sql .= " ORDER BY acs.created_at DESC LIMIT 50";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $this->data['sessions'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $this->data['totalSessions'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions")->fetchColumn());
            $this->data['totalCompleted'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE status = 'completed'")->fetchColumn());
            $this->data['totalFailed'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE status = 'failed'")->fetchColumn());
            $this->data['avgDuration'] = (float)($db->query("SELECT COALESCE(AVG(duration_seconds),0) FROM ai_call_sessions WHERE duration_seconds > 0")->fetchColumn());
            $this->data['agents'] = $db->query("SELECT DISTINCT ai_agent_id FROM ai_call_sessions WHERE ai_agent_id IS NOT NULL ORDER BY ai_agent_id")->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            $this->data['sessions'] = $this->data['agents'] = [];
            $this->data['totalSessions'] = $this->data['totalCompleted'] = $this->data['totalFailed'] = 0;
            $this->data['avgDuration'] = 0;
        }
        $this->render('admin/ai/calling-sessions');
    }

    public function extractedLeads()
    {
        $this->data['page_title'] = 'Extracted Leads';
        try {
            $db = $this->db;
            $this->data['totalExtracted'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_extracted_leads")->fetchColumn());
            $this->data['verifiedCount'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_extracted_leads WHERE is_verified = 1")->fetchColumn());
            $this->data['pendingVerify'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_extracted_leads WHERE is_verified = 0")->fetchColumn());
            $this->data['hotCount'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_extracted_leads WHERE interest_level = 'hot'")->fetchColumn());
            $this->data['convertedCount'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_extracted_leads WHERE auto_created_lead_id IS NOT NULL")->fetchColumn());
            $this->data['extracted'] = $db->query("SELECT aec.*, acs.phone as call_phone, acs.customer_response, acs.duration_seconds, l.name as linked_lead_name FROM ai_call_extracted_leads aec LEFT JOIN ai_call_sessions acs ON aec.call_session_id = acs.id LEFT JOIN leads l ON aec.auto_created_lead_id = l.id ORDER BY aec.created_at DESC LIMIT 30")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $this->data['extracted'] = [];
            $this->data['totalExtracted'] = $this->data['verifiedCount'] = $this->data['pendingVerify'] = $this->data['hotCount'] = $this->data['convertedCount'] = 0;
        }
        $this->render('admin/ai/extracted-leads');
    }

    public function training()
    {
        $this->data['page_title'] = 'AI Calling Training';
        $this->render('admin/ai/calling-training');
    }
}