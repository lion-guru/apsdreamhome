<?php

namespace App\Http\Controllers\Admin;

class VoiceAgentAdminController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
        $this->data = [];
    }

    public function index()
    {
        $this->requireAdmin();
        $this->redirect('/admin/voice-users/dashboard');
    }

    public function dashboard()
    {
        $this->requireAdmin();

        try {
            $todayCalls = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_sessions WHERE DATE(created_at) = CURDATE()")['c'] ?? 0;
            $connected = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_sessions WHERE DATE(created_at) = CURDATE() AND status = 'completed'")['c'] ?? 0;
            $interested = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_sessions WHERE DATE(created_at) = CURDATE() AND (customer_response = 'interested' OR interest_level IN ('hot','warm'))")['c'] ?? 0;
            $bookings = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_extracted_leads WHERE auto_created_lead_id IS NOT NULL AND DATE(created_at) = CURDATE()")['c'] ?? 0;
        } catch (\Exception $e) {
            $todayCalls = 0; $connected = 0; $interested = 0; $bookings = 0;
        }

        $conversionRate = $todayCalls > 0 ? round(($interested / $todayCalls) * 100, 1) : 0;

        try {
            $callTrend = $this->db->fetchAll("
                SELECT DATE(created_at) as date, 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                FROM ai_call_sessions 
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ");
        } catch (\Exception $e) {
            $callTrend = [];
        }

        try {
            $leadSources = $this->db->fetchAll("
                SELECT interest_level, COUNT(*) as count 
                FROM ai_call_extracted_leads 
                GROUP BY interest_level
            ");
        } catch (\Exception $e) {
            $leadSources = [];
        }

        try {
            $users = $this->db->fetchAll("
                SELECT agent_name, total_calls_made, successful_calls, 
                       avg_call_duration, conversion_rate, status
                FROM ai_calling_agents 
                ORDER BY total_calls_made DESC
            ");
        } catch (\Exception $e) {
            $users = [];
        }

        try {
            $recentCalls = $this->db->fetchAll("
                SELECT s.*, l.name as lead_name, l.phone as lead_phone,
                       a.agent_name as agent_display_name
                FROM ai_call_sessions s
                LEFT JOIN leads l ON l.id = s.lead_id
                LEFT JOIN ai_calling_agents a ON a.agent_id = s.ai_agent_id
                ORDER BY s.created_at DESC
                LIMIT 10
            ");
        } catch (\Exception $e) {
            $recentCalls = [];
        }

        return $this->render('admin/voice-users/dashboard', [
            'page_title' => 'Voice users Dashboard',
            'today_calls' => $todayCalls,
            'connected' => $connected,
            'interested' => $interested,
            'bookings' => $bookings,
            'conversion_rate' => $conversionRate,
            'call_trend' => $callTrend,
            'lead_sources' => $leadSources,
            'users' => $users,
            'recent_calls' => $recentCalls,
        ]);
    }

    public function history()
    {
        $this->requireAdmin();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $filters = [];
        $params = [];
        $where = '';

        if (!empty($_GET['status'])) {
            $where .= " AND s.status = ?";
            $params[] = $_GET['status'];
            $filters['status'] = $_GET['status'];
        }
        if (!empty($_GET['agent'])) {
            $where .= " AND s.ai_agent_id = ?";
            $params[] = $_GET['agent'];
            $filters['agent'] = $_GET['agent'];
        }
        if (!empty($_GET['date_from'])) {
            $where .= " AND DATE(s.created_at) >= ?";
            $params[] = $_GET['date_from'];
            $filters['date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $where .= " AND DATE(s.created_at) <= ?";
            $params[] = $_GET['date_to'];
            $filters['date_to'] = $_GET['date_to'];
        }
        if (!empty($_GET['search'])) {
            $where .= " AND (l.name LIKE ? OR s.phone LIKE ?)";
            $searchTerm = '%' . $_GET['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $filters['search'] = $_GET['search'];
        }

        try {
            $total = $this->db->fetch("
                SELECT COUNT(*) as c
                FROM ai_call_sessions s
                LEFT JOIN leads l ON l.id = s.lead_id
                WHERE 1=1 $where
            ", $params)['c'] ?? 0;

            $calls = $this->db->fetchAll("
                SELECT s.*, l.name as lead_name, l.phone as lead_phone,
                       a.agent_name as agent_display_name
                FROM ai_call_sessions s
                LEFT JOIN leads l ON l.id = s.lead_id
                LEFT JOIN ai_calling_agents a ON a.agent_id = s.ai_agent_id
                WHERE 1=1 $where
                ORDER BY s.created_at DESC
                LIMIT $perPage OFFSET $offset
            ", $params);
        } catch (\Exception $e) {
            $total = 0; $calls = [];
        }

        $totalPages = max(1, ceil($total / $perPage));

        try {
            $agentsList = $this->db->fetchAll("SELECT agent_id, agent_name FROM ai_calling_agents ORDER BY agent_name");
        } catch (\Exception $e) {
            $agentsList = [];
        }

        return $this->render('admin/voice-users/history', [
            'page_title' => 'Call History',
            'calls' => $calls,
            'agents_list' => $agentsList,
            'filters' => $filters,
            'page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
        ]);
    }

    public function schedule()
    {
        $this->requireAdmin();

        try {
            $today = $this->db->fetch("SELECT COUNT(*) as c FROM ai_calling_schedule WHERE DATE(scheduled_date) = CURDATE() AND status = 'pending'")['c'] ?? 0;
            $pending = $this->db->fetch("SELECT COUNT(*) as c FROM ai_calling_schedule WHERE status = 'pending'")['c'] ?? 0;
            $completed = $this->db->fetch("SELECT COUNT(*) as c FROM ai_calling_schedule WHERE status = 'completed'")['c'] ?? 0;
            $failed = $this->db->fetch("SELECT COUNT(*) as c FROM ai_calling_schedule WHERE status = 'failed'")['c'] ?? 0;

            $scheduleItems = $this->db->fetchAll("
                SELECT s.*, l.name as lead_name, l.phone as lead_phone,
                       a.agent_name as agent_display_name
                FROM ai_calling_schedule s
                LEFT JOIN leads l ON l.id = s.lead_id
                LEFT JOIN ai_calling_agents a ON a.agent_id = s.ai_agent_id
                ORDER BY s.scheduled_date ASC, s.scheduled_time ASC
                LIMIT 50
            ");
        } catch (\Exception $e) {
            $today = 0; $pending = 0; $completed = 0; $failed = 0; $scheduleItems = [];
        }

        try {
            $agentsList = $this->db->fetchAll("SELECT agent_id, agent_name, status, current_calls, max_concurrent_calls FROM ai_calling_agents ORDER BY agent_name");
        } catch (\Exception $e) {
            $agentsList = [];
        }

        try {
            $leadsForSchedule = $this->db->fetchAll("SELECT id, name, phone, property_interest FROM leads WHERE status IN ('new','contacted','nurture') AND id NOT IN (SELECT lead_id FROM ai_calling_schedule WHERE status = 'pending') LIMIT 50");
        } catch (\Exception $e) {
            $leadsForSchedule = [];
        }

        return $this->render('admin/voice-users/schedule', [
            'page_title' => 'Call Schedule',
            'today_count' => $today,
            'pending_count' => $pending,
            'completed_count' => $completed,
            'failed_count' => $failed,
            'schedule_items' => $scheduleItems,
            'agents_list' => $agentsList,
            'leads_list' => $leadsForSchedule,
        ]);
    }

    public function scripts()
    {
        $this->requireAdmin();

        try {
            $scripts = $this->db->fetchAll("
                SELECT * FROM ai_call_scripts 
                ORDER BY is_active DESC, usage_count DESC
            ");
        } catch (\Exception $e) {
            $scripts = [];
        }

        return $this->render('admin/voice-users/scripts', [
            'page_title' => 'Call Scripts',
            'scripts' => $scripts,
        ]);
    }

    public function extractedLeads()
    {
        $this->requireAdmin();

        $filters = [];
        $params = [];
        $where = '';

        if (!empty($_GET['interest_level'])) {
            $where .= " AND e.interest_level = ?";
            $params[] = $_GET['interest_level'];
            $filters['interest_level'] = $_GET['interest_level'];
        }
        if (!empty($_GET['timeline'])) {
            $where .= " AND e.buying_timeline = ?";
            $params[] = $_GET['timeline'];
            $filters['timeline'] = $_GET['timeline'];
        }
        if (!empty($_GET['quality_min'])) {
            $where .= " AND e.quality_score >= ?";
            $params[] = (int)$_GET['quality_min'];
            $filters['quality_min'] = (int)$_GET['quality_min'];
        }

        try {
            $totalExtracted = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_extracted_leads")['c'] ?? 0;
            $verified = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_extracted_leads WHERE is_verified = 1")['c'] ?? 0;
            $converted = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_extracted_leads WHERE auto_created_lead_id IS NOT NULL")['c'] ?? 0;
            $pendingReview = $this->db->fetch("SELECT COUNT(*) as c FROM ai_call_extracted_leads WHERE is_verified = 0 AND auto_created_lead_id IS NULL")['c'] ?? 0;

            $leads = $this->db->fetchAll("
                SELECT e.*, s.status as call_status, s.interest_level as call_interest,
                       s.sentiment_score, s.ai_summary
                FROM ai_call_extracted_leads e
                LEFT JOIN ai_call_sessions s ON s.id = e.call_session_id
                WHERE 1=1 $where
                ORDER BY e.quality_score DESC, e.created_at DESC
                LIMIT 100
            ", $params);
        } catch (\Exception $e) {
            $totalExtracted = 0; $verified = 0; $converted = 0; $pendingReview = 0; $leads = [];
        }

        return $this->render('admin/voice-users/extracted-leads', [
            'page_title' => 'Extracted Leads',
            'total_extracted' => $totalExtracted,
            'verified' => $verified,
            'converted_to_leads' => $converted,
            'pending_review' => $pendingReview,
            'leads' => $leads,
            'filters' => $filters,
        ]);
    }

    public function settings()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            try {
                if (isset($_POST['agent_status'])) {
                    $this->db->execute("UPDATE ai_calling_agents SET status = ? WHERE agent_id = ?", [$_POST['agent_status'], $_POST['agent_id']]);
                    $_SESSION['success'] = 'Agent status updated';
                }
                if (isset($_POST['voice_provider'])) {
                    $this->db->execute("UPDATE ai_settings SET settings = ?, api_key = ? WHERE service = 'voice_provider'", [
                        json_encode(['provider' => $_POST['voice_provider'], 'default_script' => $_POST['default_script'] ?? '']),
                        $_POST['api_key'] ?? ''
                    ]);
                    $_SESSION['success'] = 'Voice provider settings saved';
                }
                if (isset($_POST['max_attempts'])) {
                    $this->db->execute("UPDATE ai_settings SET settings = ? WHERE service = 'schedule_settings'", [
                        json_encode([
                            'calling_hours_start' => $_POST['calling_hours_start'] ?? '09:00',
                            'calling_hours_end' => $_POST['calling_hours_end'] ?? '20:00',
                            'max_attempts' => (int)$_POST['max_attempts'],
                            'retry_interval' => (int)$_POST['retry_interval'],
                        ])
                    ]);
                    $_SESSION['success'] = 'Schedule settings saved';
                }
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Error saving settings: ' . $e->getMessage();
            }
            $this->redirect('/admin/voice-users/settings');
        }

        try {
            $users = $this->db->fetchAll("SELECT * FROM ai_calling_agents ORDER BY agent_name");
        } catch (\Exception $e) {
            $users = [];
        }

        try {
            $voiceSettings = $this->db->fetch("SELECT * FROM ai_settings WHERE service = 'voice_provider'");
        } catch (\Exception $e) {
            $voiceSettings = ['api_key' => '', 'settings' => '{}'];
        }

        try {
            $scheduleSettings = $this->db->fetch("SELECT * FROM ai_settings WHERE service = 'schedule_settings'");
        } catch (\Exception $e) {
            $scheduleSettings = ['settings' => '{}'];
        }

        return $this->render('admin/voice-users/settings', [
            'page_title' => 'Voice Agent Settings',
            'users' => $users,
            'voice_settings' => $voiceSettings,
            'schedule_settings' => $scheduleSettings,
        ]);
    }

    public function olnDashboard()
    {
        $this->requireAdmin();

        try {
            $newLeads = $this->db->fetch("SELECT COUNT(*) as c FROM leads WHERE status = 'new'")['c'] ?? 0;
            $contacted = $this->db->fetch("SELECT COUNT(*) as c FROM leads WHERE status = 'contacted'")['c'] ?? 0;
            $qualified = $this->db->fetch("SELECT COUNT(*) as c FROM leads WHERE status = 'qualified'")['c'] ?? 0;
            $negotiation = $this->db->fetch("SELECT COUNT(*) as c FROM leads WHERE status = 'negotiation'")['c'] ?? 0;
            $closedWon = $this->db->fetch("SELECT COUNT(*) as c FROM leads WHERE status = 'closed_won'")['c'] ?? 0;
            $closedLost = $this->db->fetch("SELECT COUNT(*) as c FROM leads WHERE status = 'closed_lost'")['c'] ?? 0;
            $nurture = $this->db->fetch("SELECT COUNT(*) as c FROM leads WHERE status = 'nurture'")['c'] ?? 0;
            $proposal = $this->db->fetch("SELECT COUNT(*) as c FROM leads WHERE status = 'proposal'")['c'] ?? 0;

            $totalLeads = $newLeads + $contacted + $qualified + $proposal + $negotiation + $nurture;
            $conversionRate = $totalLeads > 0 ? round(($closedWon / $totalLeads) * 100, 1) : 0;
        } catch (\Exception $e) {
            $newLeads = 0; $contacted = 0; $qualified = 0; $negotiation = 0;
            $closedWon = 0; $closedLost = 0; $nurture = 0; $proposal = 0;
            $totalLeads = 0; $conversionRate = 0;
        }

        try {
            $leadDetails = $this->db->fetchAll("
                SELECT id, name, phone, email, property_interest, budget,
                       status, priority, lead_score, created_at, last_activity_date,
                       assigned_to
                FROM leads
                ORDER BY FIELD(status, 'new','contacted','qualified','proposal','negotiation','nurture','closed_won','closed_lost'),
                         lead_score DESC
                LIMIT 100
            ");
        } catch (\Exception $e) {
            $leadDetails = [];
        }

        try {
            $settings = $this->db->fetchAll("SELECT * FROM ai_settings WHERE service IN ('voice_provider','schedule_settings')");
        } catch (\Exception $e) {
            $settings = [];
        }

        return $this->render('admin/voice-users/oln', [
            'page_title' => 'OLN - Online Lead Nurturing',
            'stage_new' => $newLeads,
            'stage_contacted' => $contacted,
            'stage_qualified' => $qualified,
            'stage_proposal' => $proposal,
            'stage_negotiation' => $negotiation,
            'stage_closed_won' => $closedWon,
            'stage_closed_lost' => $closedLost,
            'stage_nurture' => $nurture,
            'total_pipeline' => $totalLeads,
            'conversion_rate' => $conversionRate,
            'leads' => $leadDetails,
            'settings' => $settings,
        ]);
    }

    public function ajaxConvertLead()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $extractedId = (int)($_POST['extracted_id'] ?? 0);
        if (!$extractedId) {
            echo json_encode(['success' => false, 'message' => 'Invalid extracted lead ID']);
            exit;
        }

        try {
            $extracted = $this->db->fetch("SELECT * FROM ai_call_extracted_leads WHERE id = ?", [$extractedId]);
            if (!$extracted) {
                echo json_encode(['success' => false, 'message' => 'Extracted lead not found']);
                exit;
            }

            if ($extracted['auto_created_lead_id']) {
                echo json_encode(['success' => false, 'message' => 'Already converted']);
                exit;
            }

            $existingLead = $this->db->fetch("SELECT id FROM leads WHERE phone = ?", [$extracted['extracted_phone']]);
            if ($existingLead) {
                $leadId = $existingLead['id'];
            } else {
                $this->db->execute("INSERT INTO leads (name, phone, email, budget_range, location_preference, property_interest, status, source, notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 'new', 'voice_agent', ?, NOW(), NOW())", [
                    $extracted['extracted_name'],
                    $extracted['extracted_phone'],
                    $extracted['extracted_email'] ?? '',
                    $extracted['extracted_budget'] ?? '',
                    $extracted['extracted_location'] ?? '',
                    $extracted['property_type_interest'] ?? '',
                    $extracted['extracted_requirements'] ?? '',
                ]);
                $leadId = $this->db->lastInsertId();
            }

            $this->db->execute("UPDATE ai_call_extracted_leads SET is_verified = 1, verified_by = ?, verified_at = NOW(), auto_created_lead_id = ? WHERE id = ?", [
                $_SESSION['admin_id'] ?? 0,
                $leadId,
                $extractedId,
            ]);

            echo json_encode(['success' => true, 'message' => 'Lead converted successfully', 'lead_id' => $leadId]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function rescheduleCall()
    {
        $this->requireAdmin();

        $scheduleId = (int)($_POST['schedule_id'] ?? 0);
        $newDate = $_POST['new_date'] ?? '';
        $newTime = $_POST['new_time'] ?? '';

        if (!$scheduleId || !$newDate) {
            $_SESSION['error'] = 'Invalid schedule data';
            $this->redirect('/admin/voice-users/schedule');
        }

        try {
            $this->db->execute("UPDATE ai_calling_schedule SET scheduled_date = ?, scheduled_time = ?, status = 'pending', attempt_count = 0 WHERE id = ?", [$newDate, $newTime, $scheduleId]);
            $_SESSION['success'] = 'Call rescheduled';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error rescheduling: ' . $e->getMessage();
        }

        $this->redirect('/admin/voice-users/schedule');
    }

    public function cancelSchedule()
    {
        $this->requireAdmin();

        $scheduleId = (int)($_POST['schedule_id'] ?? 0);
        if (!$scheduleId) {
            $_SESSION['error'] = 'Invalid schedule ID';
            $this->redirect('/admin/voice-users/schedule');
        }

        try {
            $this->db->execute("UPDATE ai_calling_schedule SET status = 'cancelled' WHERE id = ?", [$scheduleId]);
            $_SESSION['success'] = 'Call cancelled';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error cancelling: ' . $e->getMessage();
        }

        $this->redirect('/admin/voice-users/schedule');
    }

    public function bulkSchedule()
    {
        $this->requireAdmin();

        $leadIds = $_POST['lead_ids'] ?? [];
        $agentId = $_POST['agent_id'] ?? '';
        $scheduleDate = $_POST['schedule_date'] ?? '';
        $scheduleTime = $_POST['schedule_time'] ?? '';

        if (empty($leadIds) || empty($agentId) || empty($scheduleDate)) {
            $_SESSION['error'] = 'Please select leads, agent, and date';
            $this->redirect('/admin/voice-users/schedule');
        }

        $success = 0;
        foreach ((array)$leadIds as $leadId) {
            try {
                $lead = $this->db->fetch("SELECT id, name, phone FROM leads WHERE id = ?", [(int)$leadId]);
                if (!$lead) continue;

                $existing = $this->db->fetch("SELECT id FROM ai_calling_schedule WHERE lead_id = ? AND status='pending'", [(int)$leadId]);
                if ($existing) continue;

                $this->db->execute("INSERT INTO ai_calling_schedule (lead_id, phone, priority, scheduled_date, scheduled_time, ai_agent_id, status, max_attempts, created_by, created_at) VALUES (?, ?, 'medium', ?, ?, ?, 'pending', 3, ?, NOW())", [
                    $lead['id'],
                    $lead['phone'],
                    $scheduleDate,
                    $scheduleTime,
                    $agentId,
                    $_SESSION['admin_id'] ?? 0,
                ]);
                $success++;
            } catch (\Exception $e) { error_log('VoiceAgentAdminController bulkSchedule: ' . $e->getMessage()); }
        }

        $_SESSION['success'] = "$success leads scheduled for calling";
        $this->redirect('/admin/voice-users/schedule');
    }

    public function autoAssign()
    {
        $this->requireAdmin();

        try {
            $users = $this->db->fetchAll("SELECT agent_id, current_calls, max_concurrent_calls, daily_call_limit, status FROM ai_calling_agents WHERE status = 'active'");
            $pendingLeads = $this->db->fetchAll("SELECT l.id, l.name, l.phone, l.property_interest FROM leads l LEFT JOIN ai_calling_schedule s ON s.lead_id = l.id AND s.status = 'pending' WHERE l.status IN ('new','contacted','nurture') AND s.id IS NULL LIMIT 50");
        } catch (\Exception $e) {
            $users = []; $pendingLeads = [];
        }

        if (empty($users) || empty($pendingLeads)) {
            $_SESSION['error'] = 'No available users or leads to assign';
            $this->redirect('/admin/voice-users/schedule');
        }

        $assigned = 0;
        $agentIndex = 0;
        $agentCount = count($users);

        foreach ($pendingLeads as $lead) {
            $agent = $users[$agentIndex % $agentCount];
            $agentIndex++;

            try {
                $this->db->execute("INSERT INTO ai_calling_schedule (lead_id, phone, priority, scheduled_date, scheduled_time, ai_agent_id, status, max_attempts, created_by, created_at) VALUES (?, ?, 'medium', CURDATE(), '10:00:00', ?, 'pending', 3, ?, NOW())", [
                    $lead['id'],
                    $lead['phone'],
                    $agent['agent_id'],
                    $_SESSION['admin_id'] ?? 0,
                ]);
                $assigned++;
            } catch (\Exception $e) { error_log('VoiceAgentAdminController autoAssign: ' . $e->getMessage()); }
        }

        $_SESSION['success'] = "$assigned leads auto-assigned to users";
        $this->redirect('/admin/voice-users/schedule');
    }

    public function ajaxLeadTimeline()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $leadId = (int)($_GET['lead_id'] ?? 0);
        if (!$leadId) {
            echo json_encode(['success' => false, 'timeline' => []]);
            exit;
        }

        $timeline = [];

        try {
            $activities = $this->db->fetchAll("
                SELECT activity_type, description, created_at, subject
                FROM lead_activities 
                WHERE lead_id = ? 
                ORDER BY created_at DESC 
                LIMIT 20
            ", [$leadId]);
            foreach ($activities as $a) {
                $timeline[] = [
                    'type' => $a['activity_type'] ?? 'note',
                    'action' => $a['subject'] ?? ucfirst(str_replace('_', ' ', $a['activity_type'] ?? 'activity')),
                    'description' => $a['description'] ?? '',
                    'date' => date('d M Y h:i A', strtotime($a['created_at'] ?? 'now')),
                ];
            }
        } catch (\Exception $e) { error_log('VoiceAgentAdminController ajaxLeadTimeline activities: ' . $e->getMessage()); }

        try {
            $calls = $this->db->fetchAll("
                SELECT status, customer_response, ai_summary, duration_seconds, created_at
                FROM ai_call_sessions 
                WHERE lead_id = ? 
                ORDER BY created_at DESC 
                LIMIT 10
            ", [$leadId]);
            foreach ($calls as $c) {
                $desc = 'Call ' . str_replace('_', ' ', $c['status'] ?? 'unknown');
                if (!empty($c['customer_response'])) {
                    $desc .= ' - ' . str_replace('_', ' ', $c['customer_response']);
                }
                if (!empty($c['ai_summary'])) {
                    $desc .= ': ' . mb_substr($c['ai_summary'], 0, 100);
                }
                $timeline[] = [
                    'type' => 'call',
                    'action' => 'Voice Call',
                    'description' => $desc,
                    'date' => date('d M Y h:i A', strtotime($c['created_at'] ?? 'now')),
                ];
            }
        } catch (\Exception $e) { error_log('VoiceAgentAdminController ajaxLeadTimeline calls: ' . $e->getMessage()); }

        if (empty($timeline)) {
            $timeline[] = [
                'type' => 'note',
                'action' => 'Lead Created',
                'description' => 'Lead entered the system',
                'date' => date('d M Y'),
            ];
        }

        usort($timeline, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        echo json_encode(['success' => true, 'timeline' => $timeline]);
        exit;
    }

    /**
     * Live voice call monitor (Cluster 2 - 2026-06-05).
     * Shows in-progress and recent completed calls with controls.
     */
    public function live()
    {
        $this->requireAdmin();

        $sessions = [];
        try {
            $sessions = $this->db->fetchAll("
                SELECT s.*, l.name as lead_name, l.phone as lead_phone
                FROM ai_call_sessions s
                LEFT JOIN leads l ON l.id = s.lead_id
                ORDER BY s.id DESC
                LIMIT 50
            ");
        } catch (\Throwable $e) {
            error_log("VoiceAgentAdminController::live() session fetch failed: " . $e->getMessage());
        }

        $inProgress = array_filter($sessions, fn($s) => in_array($s['status'] ?? '', ['queued', 'ringing', 'in-progress'], true));
        $recent     = array_filter($sessions, fn($s) => in_array($s['status'] ?? '', ['completed', 'failed', 'busy', 'no-answer', 'canceled'], true));

        $stat = [
            'in_progress'     => count($inProgress),
            'completed'       => count(array_filter($recent, fn($s) => ($s['status'] ?? '') === 'completed')),
            'failed'          => count(array_filter($recent, fn($s) => in_array($s['status'] ?? '', ['failed', 'busy', 'no-answer'], true))),
            'with_recording'  => count(array_filter($sessions, fn($s) => !empty($s['recording_url']))),
        ];

        $this->render('admin/voice-agents/live', [
            'page_title'   => 'Live Voice Calls',
            'page_heading' => 'Live Voice Calls Monitor',
            'activePage'   => 'voice-agents-live',
            'sessions'     => $sessions,
            'inProgress'   => $inProgress,
            'recent'       => $recent,
            'stat'         => $stat,
        ]);
    }

    /**
     * Transfer an in-progress call to a different number.
     * Accepts POST {call_sid, to}.
     */
    public function transferCall()
    {
        $this->requireAdmin();

        $callSid = $_POST['call_sid'] ?? '';
        $to      = $_POST['to']      ?? '';

        if (!$callSid || !$to) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Missing call_sid or to']);
            exit;
        }

        try {
            $voice = new \App\Services\Voice\TwilioVoiceService();
            $result = $voice->transferCall($callSid, $to);
            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (\Throwable $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Hang up an in-progress call.
     * Accepts POST {call_sid}.
     */
    public function hangupCall()
    {
        $this->requireAdmin();

        $callSid = $_POST['call_sid'] ?? '';
        if (!$callSid) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Missing call_sid']);
            exit;
        }

        try {
            $voice = new \App\Services\Voice\TwilioVoiceService();
            $result = $voice->hangupCall($callSid);
            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (\Throwable $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
