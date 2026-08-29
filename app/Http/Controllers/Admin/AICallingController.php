<?php

namespace App\Http\Controllers\Admin;

class AICallingController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    public function index()
    {
        header('Location: ' . BASE_URL . '/admin/ai-calling/dashboard');
        exit;
    }

    public function campaign()
    {
        $this->data['page_title'] = 'Calling Campaigns';
        try {
            $db = $this->db;
            $this->data['campaigns'] = $db->fetchAll(
                "SELECT c.id, c.name, c.status, c.created_at,
                        COALESCE((SELECT COUNT(*) FROM ai_calling_schedule WHERE campaign_id = c.id), 0) as total_scheduled,
                        COALESCE((SELECT COUNT(*) FROM ai_calling_schedule WHERE campaign_id = c.id AND status = 'completed'), 0) as completed,
                        COALESCE((SELECT COUNT(*) FROM ai_call_sessions WHERE campaign_id = c.id), 0) as calls_made,
                        COALESCE((SELECT COUNT(*) FROM ai_call_sessions WHERE campaign_id = c.id AND status = 'interested'), 0) as interested
                 FROM ai_calling_campaigns c ORDER BY c.created_at DESC"
            ) ?: [];
            $this->data['totalCampaigns'] = count($this->data['campaigns']);
            $this->data['activeCampaigns'] = count(array_filter($this->data['campaigns'], fn($c) => $c['status'] === 'active'));
            $this->data['totalScheduled'] = (int)($db->query("SELECT COUNT(*) FROM ai_calling_schedule")->fetchColumn());
            $this->data['totalCompleted'] = (int)($db->query("SELECT COUNT(*) FROM ai_calling_schedule WHERE status='completed'")->fetchColumn());
        } catch (\Exception $e) {
            $this->data['campaigns'] = [];
            $this->data['totalCampaigns'] = $this->data['activeCampaigns'] = $this->data['totalScheduled'] = $this->data['totalCompleted'] = 0;
        }
        $this->render('admin/ai/calling-campaign');
    }

    public function history()
    {
        $this->data['page_title'] = 'Call History';
        try {
            $db = $this->db;
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 25;
            $offset = ($page - 1) * $perPage;
            $where = '1=1';
            $params = [];

            $filterStatus = $_GET['status'] ?? '';
            $filterFrom = $_GET['from'] ?? '';
            $filterTo = $_GET['to'] ?? '';
            $filterSearch = $_GET['q'] ?? '';

            if ($filterStatus) { $where .= " AND acs.status = ?"; $params[] = $filterStatus; }
            if ($filterFrom) { $where .= " AND DATE(acs.created_at) >= ?"; $params[] = $filterFrom; }
            if ($filterTo) { $where .= " AND DATE(acs.created_at) <= ?"; $params[] = $filterTo; }
            if ($filterSearch) {
                $where .= " AND (acs.phone LIKE ? OR l.name LIKE ?)";
                $params[] = "%$filterSearch%";
                $params[] = "%$filterSearch%";
            }

            $countStmt = $db->prepare("SELECT COUNT(*) FROM ai_call_sessions acs LEFT JOIN leads l ON acs.lead_id = l.id WHERE $where");
            $countStmt->execute($params);
            $totalRows = (int)$countStmt->fetchColumn();
            $totalPages = ceil($totalRows / $perPage);

            $sql = "SELECT acs.*, l.name as lead_name FROM ai_call_sessions acs LEFT JOIN leads l ON acs.lead_id = l.id WHERE $where ORDER BY acs.created_at DESC LIMIT $perPage OFFSET $offset";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $this->data['calls'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->data['totalCalls'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions")->fetchColumn());
            $this->data['completedCalls'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE status='completed'")->fetchColumn());
            $this->data['failedCalls'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE status='failed'")->fetchColumn());
            $this->data['interestedCount'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE customer_response='interested'")->fetchColumn());
            $this->data['pagination'] = ['page' => $page, 'total_pages' => $totalPages, 'total' => $totalRows, 'per_page' => $perPage];
            $this->data['filters'] = ['status' => $filterStatus, 'from' => $filterFrom, 'to' => $filterTo, 'q' => $filterSearch];
        } catch (\Exception $e) {
            $this->data['calls'] = [];
            $this->data['pagination'] = ['page' => 1, 'total_pages' => 1, 'total' => 0, 'per_page' => 25];
            $this->data['filters'] = [];
            $this->data['totalCalls'] = $this->data['completedCalls'] = $this->data['failedCalls'] = $this->data['interestedCount'] = 0;
        }
        $this->render('admin/ai/call-history');
    }

    public function analytics()
    {
        $this->data['page_title'] = 'Calling Analytics';
        $days = intval($_GET['days'] ?? 30);
        $since = date('Y-m-d', strtotime("-$days days"));
        try {
            $db = $this->db;
            $this->data['totals'] = $db->fetch(
                "SELECT COUNT(*) total,
                    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) completed,
                    SUM(CASE WHEN status='failed' THEN 1 ELSE 0 END) failed,
                    SUM(CASE WHEN customer_response='interested' THEN 1 ELSE 0 END) interested,
                    SUM(CASE WHEN customer_response='not_interested' THEN 1 ELSE 0 END) not_interested,
                    COALESCE(AVG(duration_seconds),0) avg_duration
                 FROM ai_call_sessions WHERE created_at >= ?", [$since]
            ) ?: ['total'=>0,'completed'=>0,'failed'=>0,'interested'=>0,'not_interested'=>0,'avg_duration'=>0];
            $daily = $db->fetchAll(
                "SELECT DATE(created_at) day, COUNT(*) total,
                    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) completed,
                    SUM(CASE WHEN customer_response='interested' THEN 1 ELSE 0 END) interested
                 FROM ai_call_sessions WHERE created_at >= ? GROUP BY day ORDER BY day", [$since]
            ) ?: [];
            $this->data['daily'] = $daily;
            $this->data['day_labels'] = array_map(fn($d) => date('d M', strtotime($d['day'])), $daily);
            $this->data['day_totals'] = array_column($daily, 'total');
            $this->data['day_completed'] = array_column($daily, 'completed');
            $this->data['day_interested'] = array_column($daily, 'interested');
            $this->data['days'] = $days;
            $this->data['byAgent'] = $db->fetchAll(
                "SELECT ai_agent_id, COUNT(*) total, SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) completed, SUM(CASE WHEN customer_response='interested' THEN 1 ELSE 0 END) interested FROM ai_call_sessions WHERE created_at >= ? AND ai_agent_id IS NOT NULL GROUP BY ai_agent_id ORDER BY total DESC", [$since]
            ) ?: [];
        } catch (\Exception $e) {
            $this->data['totals'] = ['total'=>0,'completed'=>0,'failed'=>0,'interested'=>0,'not_interested'=>0,'avg_duration'=>0];
            $this->data['daily'] = $this->data['byAgent'] = [];
            $this->data['day_labels'] = $this->data['day_totals'] = $this->data['day_completed'] = $this->data['day_interested'] = [];
            $this->data['days'] = $days;
        }
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
        try {
            $db = $this->db;
            // Voice Models
            $this->data['voiceModels'] = $db->fetchAll("SELECT * FROM ai_voice_models ORDER BY created_at DESC") ?: [];
            $this->data['totalVoiceModels'] = count($this->data['voiceModels']);
            $this->data['activeVoiceModels'] = count(array_filter($this->data['voiceModels'], fn($m) => $m['status'] === 'active'));
            // Scripts
            $this->data['scripts'] = $db->fetchAll("SELECT * FROM ai_calling_scripts ORDER BY created_at DESC") ?: [];
            $this->data['totalScripts'] = count($this->data['scripts']);
            $this->data['activeScripts'] = count(array_filter($this->data['scripts'], fn($s) => $s['is_active'] == 1));
            // Intents
            $this->data['intents'] = $db->fetchAll("SELECT * FROM ai_calling_intents ORDER BY priority DESC, total_triggers DESC") ?: [];
            $this->data['totalIntents'] = count($this->data['intents']);
            $this->data['activeIntents'] = count(array_filter($this->data['intents'], fn($i) => $i['is_active'] == 1));
            // Performance from existing call data
            $this->data['perfTotalCalls'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions")->fetchColumn());
            $this->data['perfCompletedCalls'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE status='completed'")->fetchColumn());
            $this->data['perfAvgDuration'] = round((float)($db->query("SELECT COALESCE(AVG(duration_seconds),0) FROM ai_call_sessions WHERE duration_seconds > 0")->fetchColumn()));
            $this->data['perfInterested'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE customer_response='interested'")->fetchColumn());
            // Per-script performance
            $scriptPerf = $db->fetchAll("SELECT s.id, s.script_name, s.total_calls_made, s.total_calls_connected, s.total_interested, s.conversion_rate FROM ai_calling_scripts s ORDER BY s.conversion_rate DESC") ?: [];
            $this->data['scriptPerformance'] = $scriptPerf;
        } catch (\Exception $e) {
            $this->data['voiceModels'] = $this->data['scripts'] = $this->data['intents'] = $this->data['scriptPerformance'] = [];
            $this->data['totalVoiceModels'] = $this->data['activeVoiceModels'] = $this->data['totalScripts'] = $this->data['activeScripts'] = 0;
            $this->data['totalIntents'] = $this->data['activeIntents'] = $this->data['perfTotalCalls'] = $this->data['perfCompletedCalls'] = 0;
            $this->data['perfAvgDuration'] = $this->data['perfInterested'] = 0;
        }
        $this->render('admin/ai/calling-training');
    }

    public function saveVoiceModel()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        $this->validateCsrfOrFail();
        $db = $this->db;
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'model_name' => trim($_POST['model_name'] ?? ''),
            'language' => $_POST['language'] ?? 'hi-IN',
            'voice_gender' => $_POST['voice_gender'] ?? 'female',
            'model_provider' => $_POST['model_provider'] ?? 'google',
            'status' => $_POST['status'] ?? 'inactive',
            'notes' => trim($_POST['notes'] ?? ''),
        ];
        if (empty($data['model_name'])) { $this->data['error'] = 'Model name is required'; return $this->training(); }
        $tid = $this->tenantId();
        if ($id) {
            $db->prepare("UPDATE ai_voice_models SET model_name=?, language=?, voice_gender=?, model_provider=?, status=?, notes=? WHERE id=?")
               ->execute([$data['model_name'], $data['language'], $data['voice_gender'], $data['model_provider'], $data['status'], $data['notes'], $id]);
            $_SESSION['success'] = 'Voice model updated';
        } else {
            $db->prepare("INSERT INTO ai_voice_models (model_name, language, voice_gender, model_provider, status, notes, created_by) VALUES (?,?,?,?,?,?,?)")
               ->execute([$data['model_name'], $data['language'], $data['voice_gender'], $data['model_provider'], $data['status'], $data['notes'], $_SESSION['admin_id'] ?? null]);
            $_SESSION['success'] = 'Voice model created';
        }
        redirect(BASE_URL . '/admin/ai-calling/training');
        exit;
    }

    public function saveScript()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        $this->validateCsrfOrFail();
        $db = $this->db;
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'script_name' => trim($_POST['script_name'] ?? ''),
            'script_code' => strtoupper(trim($_POST['script_code'] ?? '')),
            'category' => $_POST['category'] ?? 'sales',
            'language' => $_POST['language'] ?? 'hi-IN',
            'greeting_text' => trim($_POST['greeting_text'] ?? ''),
            'main_body' => trim($_POST['main_body'] ?? ''),
            'closing_text' => trim($_POST['closing_text'] ?? ''),
            'estimated_duration_seconds' => (int)($_POST['estimated_duration_seconds'] ?? 120),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
        if (empty($data['script_name']) || empty($data['greeting_text'])) {
            $_SESSION['error'] = 'Script name and greeting are required';
            return $this->training();
        }
        $tid = $this->tenantId();
        if ($id) {
            $db->prepare("UPDATE ai_calling_scripts SET script_name=?, script_code=?, category=?, language=?, greeting_text=?, main_body=?, closing_text=?, estimated_duration_seconds=?, is_active=? WHERE id=? AND tenant_id=?")
               ->execute([$data['script_name'], $data['script_code'], $data['category'], $data['language'], $data['greeting_text'], $data['main_body'], $data['closing_text'], $data['estimated_duration_seconds'], $data['is_active'], $id, $tid]);
            $_SESSION['success'] = 'Script updated';
        } else {
            $db->prepare("INSERT INTO ai_calling_scripts (script_name, script_code, category, language, greeting_text, main_body, closing_text, estimated_duration_seconds, is_active, created_by, tenant_id) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
               ->execute([$data['script_name'], $data['script_code'], $data['category'], $data['language'], $data['greeting_text'], $data['main_body'], $data['closing_text'], $data['estimated_duration_seconds'], $data['is_active'], $_SESSION['admin_id'] ?? null, $tid]);
            $_SESSION['success'] = 'Script created';
        }
        redirect(BASE_URL . '/admin/ai-calling/training');
        exit;
    }

    public function saveIntent()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        $this->validateCsrfOrFail();
        $db = $this->db;
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'intent_name' => trim($_POST['intent_name'] ?? ''),
            'intent_code' => strtoupper(trim($_POST['intent_code'] ?? '')),
            'category' => $_POST['category'] ?? 'interest',
            'description' => trim($_POST['description'] ?? ''),
            'priority' => (int)($_POST['priority'] ?? 5),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
        if (empty($data['intent_name'])) {
            $_SESSION['error'] = 'Intent name is required';
            return $this->training();
        }
        $tid = $this->tenantId();
        if ($id) {
            $db->prepare("UPDATE ai_calling_intents SET intent_name=?, intent_code=?, category=?, description=?, priority=?, is_active=? WHERE id=? AND tenant_id=?")
               ->execute([$data['intent_name'], $data['intent_code'], $data['category'], $data['description'], $data['priority'], $data['is_active'], $id, $tid]);
            $_SESSION['success'] = 'Intent updated';
        } else {
            $db->prepare("INSERT INTO ai_calling_intents (intent_name, intent_code, category, description, priority, is_active, tenant_id) VALUES (?,?,?,?,?,?,?)")
               ->execute([$data['intent_name'], $data['intent_code'], $data['category'], $data['description'], $data['priority'], $data['is_active'], $tid]);
            $_SESSION['success'] = 'Intent created';
        }
        redirect(BASE_URL . '/admin/ai-calling/training');
        exit;
    }

    public function autoDialer()
    {
        $this->data['page_title'] = 'Auto Dialer';
        try {
            $db = $this->db;
            $today = date('Y-m-d');

            // Schedule queue stats
            $this->data['total_scheduled'] = (int)($db->fetch("SELECT COUNT(*) c FROM ai_calling_schedule")['c'] ?? 0);
            $this->data['pending_total'] = (int)($db->fetch("SELECT COUNT(*) c FROM ai_calling_schedule WHERE status='pending'")['c'] ?? 0);
            $this->data['completed_total'] = (int)($db->fetch("SELECT COUNT(*) c FROM ai_calling_schedule WHERE status='completed'")['c'] ?? 0);
            $this->data['pending_today'] = (int)($db->fetch("SELECT COUNT(*) c FROM ai_calling_schedule WHERE scheduled_date=? AND status='pending'", [$today])['c'] ?? 0);
            $this->data['completed_today'] = (int)($db->fetch("SELECT COUNT(*) c FROM ai_calling_schedule WHERE scheduled_date=? AND status='completed'", [$today])['c'] ?? 0);

            // Today's queue
            $this->data['today_queue'] = $db->fetchAll(
                "SELECT s.*, l.name as lead_name, l.phone as lead_phone, l.budget
                 FROM ai_calling_schedule s
                 LEFT JOIN leads l ON l.id = s.lead_id
                 WHERE s.scheduled_date = ?
                 ORDER BY CASE s.priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END, s.scheduled_time",
                [$today]
            ) ?: [];

            // Upcoming
            $this->data['upcoming'] = $db->fetchAll(
                "SELECT s.*, l.name as lead_name, l.phone as lead_phone
                 FROM ai_calling_schedule s
                 LEFT JOIN leads l ON l.id = s.lead_id
                 WHERE s.scheduled_date > ? AND s.status='pending'
                 ORDER BY s.scheduled_date, s.scheduled_time LIMIT 15",
                [$today]
            ) ?: [];

            // Recent app call logs
            $this->data['recent_logs'] = $db->fetchAll(
                "SELECT * FROM calls_log ORDER BY created_at DESC LIMIT 15"
            ) ?: [];

            $this->data['total_logs'] = (int)($db->fetch("SELECT COUNT(*) c FROM calls_log")['c'] ?? 0);
        } catch (\Exception $e) {
            $this->data['today_queue'] = $this->data['upcoming'] = $this->data['recent_logs'] = [];
            $this->data['total_scheduled'] = $this->data['pending_total'] = $this->data['completed_total'] = 0;
            $this->data['pending_today'] = $this->data['completed_today'] = $this->data['total_logs'] = 0;
        }
        $this->render('admin/ai/auto-dialer');
    }

    public function callAnalytics()
    {
        $this->data['page_title'] = 'Call Analytics';
        $days = intval($_GET['days'] ?? 30);
        $since = date('Y-m-d', strtotime("-$days days"));
        try {
            $db = $this->db;
            $outcomes = $db->fetchAll(
                "SELECT outcome, COUNT(*) total FROM calls_log
                 WHERE created_at >= ? AND outcome IS NOT NULL AND outcome != ''
                 GROUP BY outcome ORDER BY total DESC", [$since]
            ) ?: [];
            $methods = $db->fetchAll(
                "SELECT method, COUNT(*) total FROM calls_log
                 WHERE created_at >= ? AND method IS NOT NULL
                 GROUP BY method ORDER BY total DESC", [$since]
            ) ?: [];
            $daily = $db->fetchAll(
                "SELECT DATE(created_at) day, COUNT(*) total,
                        SUM(CASE WHEN outcome='connected' THEN 1 ELSE 0 END) connected
                 FROM calls_log WHERE created_at >= ?
                 GROUP BY DATE(created_at) ORDER BY day", [$since]
            ) ?: [];
            $totals = $db->fetch(
                "SELECT COUNT(*) total,
                        SUM(CASE WHEN outcome='connected' THEN 1 ELSE 0 END) connected,
                        SUM(CASE WHEN outcome='not_answered' THEN 1 ELSE 0 END) not_answered,
                        SUM(CASE WHEN outcome='busy' THEN 1 ELSE 0 END) busy,
                        SUM(CASE WHEN outcome='call_later' THEN 1 ELSE 0 END) call_later,
                        SUM(CASE WHEN action LIKE '%whatsapp%' THEN 1 ELSE 0 END) whatsapp,
                        SUM(CASE WHEN action LIKE '%sms%' THEN 1 ELSE 0 END) sms
                 FROM calls_log WHERE created_at >= ?", [$since]
            ) ?: ['total' => 0,'connected' => 0,'not_answered' => 0,'busy' => 0,'call_later' => 0,'whatsapp' => 0,'sms' => 0];

            $this->data['days'] = $days;
            $this->data['outcomes'] = $outcomes;
            $this->data['methods'] = $methods;
            $this->data['daily'] = $daily;
            $this->data['totals'] = $totals;
            $this->data['day_labels'] = array_map(fn($d) => date('d M', strtotime($d['day'])), $daily);
            $this->data['day_totals'] = array_column($daily, 'total');
            $this->data['day_connected'] = array_column($daily, 'connected');
        } catch (\Exception $e) {
            $this->data['days'] = $days;
            $this->data['outcomes'] = $this->data['methods'] = $this->data['daily'] = [];
            $this->data['day_labels'] = $this->data['day_totals'] = $this->data['day_connected'] = [];
            $this->data['totals'] = ['total' => 0,'connected' => 0,'not_answered' => 0,'busy' => 0,'call_later' => 0,'whatsapp' => 0,'sms' => 0];
        }
        $this->render('admin/ai/auto-dialer-analytics');
    }

    public function autoDialerProcess()
    {
        try {
            $controller = new \App\Http\Controllers\Api\AutoDialerController();
            $result = $controller->processQueue();
            if (is_array($result)) {
                echo json_encode($result);
            }
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function autoDialerAiSchedule()
    {
        try {
            $minScore = intval($_POST['min_score'] ?? 70);
            $controller = new \App\Http\Controllers\Api\AutoDialerController();
            $_POST['min_score'] = $minScore;
            $result = $controller->aiSchedule();
            if (is_array($result)) {
                echo json_encode($result);
            }
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function healthCheck()
    {
        $results = [];

        // Database
        try {
            $this->db->query("SELECT 1");
            $results['database'] = ['status' => 'ok', 'message' => 'Connected'];
        } catch (\Exception $e) {
            $results['database'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        // Asterisk AMI
        try {
            $asterisk = new \App\Services\Voice\AsteriskService();
            $connected = $asterisk->ping();
            $results['asterisk'] = $connected
                ? ['status' => 'ok', 'message' => 'AMI connected']
                : ['status' => 'error', 'message' => 'AMI unreachable'];
        } catch (\Exception $e) {
            $results['asterisk'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        // Ollama
        $ollamaUrl = 'http://localhost:11434/api/tags';
        $ollamaCh = curl_init($ollamaUrl);
        curl_setopt_array($ollamaCh, [CURLOPT_TIMEOUT => 3, CURLOPT_RETURNTRANSFER => true]);
        $ollamaResp = curl_exec($ollamaCh);
        $ollamaCode = curl_getinfo($ollamaCh, CURLINFO_HTTP_CODE);
        curl_close($ollamaCh);
        $results['ollama'] = ($ollamaResp && $ollamaCode === 200)
            ? ['status' => 'ok', 'message' => 'Running']
            : ['status' => 'error', 'message' => 'Not reachable (expected if Docker not running)'];

        // Gemini
        $geminiKey = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? '');
        $results['gemini'] = !empty($geminiKey)
            ? ['status' => 'ok', 'message' => 'API key configured']
            : ['status' => 'warning', 'message' => 'No API key — will skip Gemini fallback'];

        // Whisper
        $whisperUrl = 'http://localhost:8080/health';
        $whisperCh = curl_init($whisperUrl);
        curl_setopt_array($whisperCh, [CURLOPT_TIMEOUT => 3, CURLOPT_RETURNTRANSFER => true]);
        $whisperResp = curl_exec($whisperCh);
        $whisperCode = curl_getinfo($whisperCh, CURLINFO_HTTP_CODE);
        curl_close($whisperCh);
        $results['whisper'] = ($whisperResp && $whisperCode === 200)
            ? ['status' => 'ok', 'message' => 'Running']
            : ['status' => 'error', 'message' => 'Not reachable (expected if Docker not running)'];

        // WhatsApp
        try {
            $whatsapp = new \App\Services\Communication\WhatsAppWebService();
            $waConnected = $whatsapp->isConnected();
            $results['whatsapp'] = $waConnected
                ? ['status' => 'ok', 'message' => 'WhatsApp connected']
                : ['status' => 'error', 'message' => 'WhatsApp not connected — scan QR first'];
        } catch (\Exception $e) {
            $results['whatsapp'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'services' => $results], JSON_PRETTY_PRINT);
    }

    public function callLogs()
    {
        $this->data['page_title'] = 'Voice Call Logs';
        try {
            $db = $this->db;
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 25;
            $offset = ($page - 1) * $perPage;
            $where = '1=1';
            $params = [];

            $filterStatus = $_GET['status'] ?? '';
            $filterAgent = $_GET['agent'] ?? '';
            $filterFrom = $_GET['from'] ?? '';
            $filterTo = $_GET['to'] ?? '';
            $filterSearch = $_GET['q'] ?? '';
            $filterSentiment = $_GET['sentiment'] ?? '';
            $filterResponse = $_GET['response'] ?? '';

            if ($filterStatus) { $where .= " AND acs.status = ?"; $params[] = $filterStatus; }
            if ($filterAgent) { $where .= " AND acs.ai_agent_id = ?"; $params[] = $filterAgent; }
            if ($filterFrom) { $where .= " AND DATE(acs.created_at) >= ?"; $params[] = $filterFrom; }
            if ($filterTo) { $where .= " AND DATE(acs.created_at) <= ?"; $params[] = $filterTo; }
            if ($filterSentiment) { $where .= " AND acs.sentiment = ?"; $params[] = $filterSentiment; }
            if ($filterResponse) { $where .= " AND acs.customer_response = ?"; $params[] = $filterResponse; }
            if ($filterSearch) {
                $where .= " AND (acs.phone LIKE ? OR l.name LIKE ? OR acs.call_transcript LIKE ?)";
                $searchTerm = "%$filterSearch%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $countStmt = $db->prepare("SELECT COUNT(*) FROM ai_call_sessions acs LEFT JOIN leads l ON acs.lead_id = l.id WHERE $where");
            $countStmt->execute($params);
            $totalRows = (int)$countStmt->fetchColumn();
            $totalPages = ceil($totalRows / $perPage);

            $sql = "SELECT acs.*, l.name as lead_name, l.phone as lead_phone, l.budget as lead_budget
                    FROM ai_call_sessions acs
                    LEFT JOIN leads l ON acs.lead_id = l.id
                    WHERE $where
                    ORDER BY acs.created_at DESC
                    LIMIT $perPage OFFSET $offset";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $this->data['calls'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->data['totalCalls'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions")->fetchColumn());
            $this->data['completedCalls'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE status='completed'")->fetchColumn());
            $this->data['failedCalls'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE status='failed'")->fetchColumn());
            $this->data['noAnswerCalls'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE status='no_answer'")->fetchColumn());
            $this->data['avgDuration'] = (float)($db->query("SELECT COALESCE(AVG(duration_seconds),0) FROM ai_call_sessions WHERE duration_seconds > 0")->fetchColumn());
            $this->data['callsToday'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE DATE(created_at) = CURDATE()")->fetchColumn());
            $this->data['interestedCount'] = (int)($db->query("SELECT COUNT(*) FROM ai_call_sessions WHERE customer_response='interested'")->fetchColumn());
            $this->data['agents'] = $db->query("SELECT DISTINCT ai_agent_id FROM ai_call_sessions WHERE ai_agent_id IS NOT NULL AND ai_agent_id != '' ORDER BY ai_agent_id")->fetchAll(\PDO::FETCH_COLUMN);

            $this->data['pagination'] = ['page' => $page, 'total_pages' => $totalPages, 'total' => $totalRows, 'per_page' => $perPage, 'offset' => $offset];
            $this->data['filters'] = ['status' => $filterStatus, 'agent' => $filterAgent, 'from' => $filterFrom, 'to' => $filterTo, 'q' => $filterSearch, 'sentiment' => $filterSentiment, 'response' => $filterResponse];
        } catch (\Exception $e) {
            $this->data['calls'] = $this->data['agents'] = $this->data['filters'] = [];
            $this->data['pagination'] = ['page' => 1, 'total_pages' => 1, 'total' => 0, 'per_page' => 25, 'offset' => 0];
            $this->data['totalCalls'] = $this->data['completedCalls'] = $this->data['failedCalls'] = $this->data['noAnswerCalls'] = 0;
            $this->data['avgDuration'] = $this->data['callsToday'] = $this->data['interestedCount'] = 0;
        }
        $this->render('admin/ai/call-logs');
    }

    public function callDetail()
    {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Missing id']);
            exit;
        }
        try {
            $db = $this->db;
            $call = $db->fetch(
                "SELECT acs.*, l.name as lead_name, l.phone as lead_phone, l.email as lead_email,
                        l.budget as lead_budget, l.city as lead_city, l.status as lead_status
                 FROM ai_call_sessions acs
                 LEFT JOIN leads l ON acs.lead_id = l.id
                 WHERE acs.id = ?", [$id]
            );
            if (!$call) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Call not found']);
                exit;
            }
            $extracted = $db->fetch(
                "SELECT * FROM ai_call_extracted_leads WHERE call_session_id = ? ORDER BY id DESC LIMIT 1", [$id]
            );
            $call['extracted'] = $extracted;
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'call' => $call]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}