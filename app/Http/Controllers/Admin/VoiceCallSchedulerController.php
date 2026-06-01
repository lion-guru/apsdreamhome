<?php

namespace App\Http\Controllers\Admin;

use App\Services\Voice\VoiceCallService;

class VoiceCallSchedulerController extends AdminController
{
    protected $voiceCallService;

    public function __construct()
    {
        parent::__construct();
        $this->voiceCallService = new VoiceCallService();
    }

    public function index()
    {
        $this->requireAdmin();

        $stats = $this->voiceCallService->getScheduleAnalytics();
        $agents = $this->voiceCallService->getAgentList();
        $callsOverTime = $this->voiceCallService->getCallsOverTime(14);
        $pendingCalls = $this->voiceCallService->getPendingCalls(5);

        return $this->render('admin/voice-scheduler/index', [
            'page_title' => 'Voice Call Scheduler',
            'stats' => $stats,
            'agents' => $agents,
            'calls_over_time' => $callsOverTime,
            'pending_calls' => $pendingCalls,
        ]);
    }

    public function schedule()
    {
        $this->requireAdmin();

        $agents = $this->voiceCallService->getAgentList();
        $scripts = $this->voiceCallService->getScriptList();
        $leads = $this->voiceCallService->getAvailableLeadsForScheduling();

        return $this->render('admin/voice-scheduler/schedule', [
            'page_title' => 'Schedule a Call',
            'agents' => $agents,
            'scripts' => $scripts,
            'leads' => $leads,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();

        $leadId = (int)($_POST['lead_id'] ?? 0);
        $leadName = $_POST['lead_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $agentId = $_POST['agent_id'] ?? '';
        $scheduledDate = $_POST['scheduled_date'] ?? '';
        $scheduledTime = $_POST['scheduled_time'] ?? '10:00';
        $scriptTemplate = $_POST['script_template'] ?? 'property_introduction';
        $priority = $_POST['priority'] ?? 'medium';

        if (!$leadId || !$phone || !$agentId || !$scheduledDate) {
            $_SESSION['error'] = 'Lead, phone, agent, and date are required';
            $this->redirect('/admin/voice-scheduler/schedule');
        }

        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $scheduledTime)) {
            $scheduledTime = $scheduledTime . ':00';
        }

        try {
            $result = $this->voiceCallService->scheduleCall(
                $leadId, $phone, $agentId, $scheduledDate, $scheduledTime, $scriptTemplate, $priority, $leadName
            );

            if ($result['success']) {
                $_SESSION['success'] = 'Call scheduled successfully for ' . $scheduledDate . ' at ' . $scheduledTime;
            } else {
                $_SESSION['error'] = $result['message'] ?? 'Failed to schedule call';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error scheduling call: ' . $e->getMessage();
        }

        $this->redirect('/admin/voice-scheduler/calls');
    }

    public function calls()
    {
        $this->requireAdmin();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = 'WHERE 1=1';
        $params = [];

        if (!empty($_GET['status'])) {
            $where .= ' AND s.status = ?';
            $params[] = $_GET['status'];
        }
        if (!empty($_GET['agent_id'])) {
            $where .= ' AND s.ai_agent_id = ?';
            $params[] = $_GET['agent_id'];
        }
        if (!empty($_GET['date_from'])) {
            $where .= ' AND s.scheduled_date >= ?';
            $params[] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $where .= ' AND s.scheduled_date <= ?';
            $params[] = $_GET['date_to'];
        }
        if (!empty($_GET['search'])) {
            $where .= ' AND (l.name LIKE ? OR s.phone LIKE ?)';
            $search = '%' . $_GET['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        try {
            $total = $this->db->fetch(
                "SELECT COUNT(*) as c FROM ai_calling_schedule s LEFT JOIN leads l ON l.id = s.lead_id $where",
                $params
            )['c'] ?? 0;

            $allCalls = $this->db->fetchAll(
                "SELECT s.*, l.name as lead_name, l.phone as lead_phone, l.property_interest,
                        a.agent_name
                 FROM ai_calling_schedule s
                 LEFT JOIN leads l ON l.id = s.lead_id
                 LEFT JOIN ai_calling_agents a ON a.agent_id = s.ai_agent_id
                 $where
                 ORDER BY s.scheduled_date DESC, s.scheduled_time DESC
                 LIMIT $perPage OFFSET $offset",
                $params
            );
        } catch (\Exception $e) {
            $total = 0;
            $allCalls = [];
        }

        $totalPages = max(1, ceil($total / $perPage));

        $agents = $this->voiceCallService->getAgentList();

        return $this->render('admin/voice-scheduler/calls', [
            'page_title' => 'All Scheduled Calls',
            'calls' => $allCalls,
            'agents' => $agents,
            'page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
            'filters' => $_GET,
        ]);
    }

    public function callDetail($id)
    {
        $this->requireAdmin();

        $call = $this->db->fetch(
            "SELECT s.*, l.name as lead_name, l.phone as lead_phone, l.email as lead_email,
                    l.property_interest, l.budget_range, l.status as lead_status,
                    a.agent_name, a.agent_id as agent_identifier
             FROM ai_calling_schedule s
             LEFT JOIN leads l ON l.id = s.lead_id
             LEFT JOIN ai_calling_agents a ON a.agent_id = s.ai_agent_id
             WHERE s.id = ?",
            [$id]
        );

        if (!$call) {
            $_SESSION['error'] = 'Call not found';
            $this->redirect('/admin/voice-scheduler/calls');
        }

        $session = null;
        if ($call['call_session_id']) {
            $session = $this->db->fetch(
                "SELECT * FROM ai_call_sessions WHERE id = ?",
                [$call['call_session_id']]
            );
        }

        $extractedLead = null;
        if ($call['call_session_id']) {
            $extractedLead = $this->db->fetch(
                "SELECT * FROM ai_call_extracted_leads WHERE call_session_id = ?",
                [$call['call_session_id']]
            );
        }

        $callHistory = [];
        if ($call['lead_id']) {
            $callHistory = $this->voiceCallService->getLeadCallHistory($call['lead_id']);
        }

        return $this->render('admin/voice-scheduler/call_detail', [
            'page_title' => 'Call Detail #' . $id,
            'call' => $call,
            'session' => $session,
            'extracted_lead' => $extractedLead,
            'call_history' => $callHistory,
        ]);
    }

    public function processQueue()
    {
        $this->requireAdmin();

        $limit = (int)($_POST['limit'] ?? 5);
        $result = $this->voiceCallService->processScheduledCalls($limit);

        $_SESSION['success'] = $result['processed'] . ' calls processed from queue';
        $this->redirect('/admin/voice-scheduler');
    }

    public function analytics()
    {
        $this->requireAdmin();

        $stats = $this->voiceCallService->getScheduleAnalytics();
        $callsByAgent = $this->voiceCallService->getCallsByAgent();
        $callsOverTime = $this->voiceCallService->getCallsOverTime(30);
        $agents = $this->voiceCallService->getAgentList();

        $leadSources = [];
        try {
            $leadSources = $this->db->fetchAll(
                "SELECT interest_level, COUNT(*) as count FROM ai_call_extracted_leads GROUP BY interest_level"
            );
        } catch (\Exception $e) {
        }

        return $this->render('admin/voice-scheduler/analytics', [
            'page_title' => 'Call Analytics',
            'stats' => $stats,
            'calls_by_agent' => $callsByAgent,
            'calls_over_time' => $callsOverTime,
            'agents' => $agents,
            'lead_sources' => $leadSources,
        ]);
    }

    public function reserveCall()
    {
        $this->requireAdmin();
        $this->redirect('/admin/voice-scheduler/schedule');
    }

    public function rescheduleCall()
    {
        $this->requireAdmin();

        $scheduleId = (int)($_POST['schedule_id'] ?? 0);
        $newDate = $_POST['new_date'] ?? '';
        $newTime = $_POST['new_time'] ?? '10:00:00';

        if (!$scheduleId || !$newDate) {
            $_SESSION['error'] = 'Schedule ID and date required';
            $this->redirect('/admin/voice-scheduler/calls');
        }

        $this->voiceCallService->rescheduleCall($scheduleId, $newDate, $newTime);
        $_SESSION['success'] = 'Call rescheduled';
        $this->redirect('/admin/voice-scheduler/calls');
    }

    public function cancelSchedule()
    {
        $this->requireAdmin();

        $scheduleId = (int)($_POST['schedule_id'] ?? 0);
        if (!$scheduleId) {
            $_SESSION['error'] = 'Invalid schedule ID';
            $this->redirect('/admin/voice-scheduler/calls');
        }

        $this->voiceCallService->cancelSchedule($scheduleId);
        $_SESSION['success'] = 'Call cancelled';
        $this->redirect('/admin/voice-scheduler/calls');
    }
}
