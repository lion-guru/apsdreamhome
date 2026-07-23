<?php

namespace App\Http\Controllers\Admin;

use App\Services\CRMService;

class CRMController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stats = [
                'total_customers' => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn(),
                'active_leads' => (int)$db->query("SELECT COUNT(*) FROM leads WHERE status NOT IN ('converted','closed','dead') AND deleted_at IS NULL")->fetchColumn(),
                'open_tickets' => (int)$db->query("SELECT COUNT(*) FROM support_tickets WHERE status IN ('open','in_progress','pending')")->fetchColumn(),
                'total_inquiries' => (int)$db->query("SELECT COUNT(*) FROM inquiries")->fetchColumn(),
                'converted_this_month' => (int)$db->query("SELECT COUNT(*) FROM leads WHERE status='converted' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetchColumn(),
                'pending_followups' => (int)$db->query("SELECT COUNT(*) FROM leads WHERE next_activity_date <= CURDATE() AND status NOT IN ('converted','closed','dead') AND deleted_at IS NULL")->fetchColumn(),
            ];
            $recent_tickets = $db->query("SELECT st.*, u.name as user_name FROM support_tickets st LEFT JOIN users u ON st.user_id=u.id ORDER BY st.created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $recent_leads = $db->query("SELECT l.*, u.name as assignee_name FROM leads l LEFT JOIN users u ON l.assigned_to=u.id WHERE l.deleted_at IS NULL ORDER BY l.created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $stats = ['total_customers'=>0,'active_leads'=>0,'open_tickets'=>0,'total_inquiries'=>0,'converted_this_month'=>0,'pending_followups'=>0];
            $recent_tickets = [];
            $recent_leads = [];
        }
        return $this->render('admin/crm/index', ['stats' => $stats, 'recent_tickets' => $recent_tickets, 'recent_leads' => $recent_leads]);
    }

    public function users()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $customers = $db->query("SELECT u.*, (SELECT COUNT(*) FROM leads WHERE email=u.email AND deleted_at IS NULL) as lead_count, (SELECT COUNT(*) FROM inquiries WHERE user_id=u.id) as inquiry_count FROM users u WHERE u.role='customer' ORDER BY u.created_at DESC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $customers = [];
        }
        return $this->render('admin/crm/customers', ['customers' => $customers]);
    }

    public function createCustomer()
    {
        $this->requireAdmin();
        return $this->render('admin/crm/customers/create', []);
    }

    /**
     * Store a new CRM customer (users row with role='customer')
     */
    public function storeCustomer()
    {
        $this->requireAdmin();
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $budget = trim($_POST['budget'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (!$name || !$email || !$phone) {
            $this->setFlash('error', 'Name, Email and Phone are required');
            header('Location: ' . BASE_URL . '/admin/crm/users/create');
            exit;
        }

        try {
            $exists = $this->db->fetch("SELECT id FROM users WHERE email=?", [$email]);
            if ($exists) {
                $this->setFlash('error', 'A user with this email already exists');
                header('Location: ' . BASE_URL . '/admin/crm/users/create');
                exit;
            }

            $regService = new \App\Services\UserRegistrationService();
            $user = null;
            $result = $regService->createUser('customer', [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $password ?: ('cust@' . rand(100000, 999999)),
                'registration_method' => 'admin',
            ], $user);

            if (!$result['success']) {
                $this->setFlash('error', 'Error: ' . ($result['message'] ?? 'could not create customer'));
                header('Location: ' . BASE_URL . '/admin/crm/users/create');
                exit;
            }

            $userId = $result['user_id'];

            // Persist optional profile fields if columns exist
            $cols = $this->db->fetchAll("SHOW COLUMNS FROM users WHERE Field IN ('city','budget','address')");
            $extra = array_column($cols, 'Field');
            $updates = [];
            $params = [];
            if (in_array('city', $extra, true)) { $updates[] = 'city=?'; $params[] = $city; }
            if (in_array('budget', $extra, true)) { $updates[] = 'budget=?'; $params[] = $budget; }
            if (in_array('address', $extra, true)) { $updates[] = 'address=?'; $params[] = $address; }
            if ($updates) {
                $params[] = $userId;
                $this->db->execute("UPDATE users SET " . implode(',', $updates) . " WHERE id=?", $params);
            }

            $this->setFlash('success', 'Customer created successfully. ID: ' . $userId);
        } catch (\Exception $e) {
            error_log('[CRMController::storeCustomer] ' . $e->getMessage());
            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        header('Location: ' . BASE_URL . '/admin/crm/users');
        exit;
    }

    public function groups()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $leads = $db->query("SELECT l.*, u.name as assignee_name FROM leads l LEFT JOIN users u ON l.assigned_to=u.id WHERE l.deleted_at IS NULL ORDER BY l.created_at DESC LIMIT 100")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $statuses = $db->query("SELECT status, COUNT(*) as cnt FROM leads WHERE deleted_at IS NULL GROUP BY status ORDER BY cnt DESC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $sources = $db->query("SELECT source, COUNT(*) as cnt FROM leads WHERE deleted_at IS NULL GROUP BY source ORDER BY cnt DESC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $leads = [];
            $statuses = [];
            $sources = [];
        }
        return $this->render('admin/crm/groups', ['leads' => $leads, 'statuses' => $statuses, 'sources' => $sources]);
    }

    public function followups()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $followups = $db->query("SELECT la.*, l.name as lead_name, l.phone as lead_phone, u.name as assignee_name FROM lead_activities la LEFT JOIN leads l ON la.lead_id=l.id LEFT JOIN users u ON la.created_by=u.id WHERE la.activity_type IN ('call','email','sms','meeting','follow_up') ORDER BY la.activity_date DESC LIMIT 50")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $pending = $db->query("SELECT l.*, l.next_activity_date, u.name as assignee_name FROM leads l LEFT JOIN users u ON l.assigned_to=u.id WHERE l.next_activity_date IS NOT NULL AND l.next_activity_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND l.status NOT IN ('converted','closed','dead') AND l.deleted_at IS NULL ORDER BY l.next_activity_date ASC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $followups = [];
            $pending = [];
        }
        return $this->render('admin/crm/followups', ['followups' => $followups, 'pending' => $pending]);
    }

    public function feedback()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tickets = $db->query("SELECT st.*, u.name as user_name, a.name as assignee_name FROM support_tickets st LEFT JOIN users u ON st.user_id=u.id LEFT JOIN users a ON st.assigned_to=a.id WHERE st.satisfaction_rating IS NOT NULL ORDER BY st.created_at DESC LIMIT 50")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $avg = $db->query("SELECT AVG(satisfaction_rating) as avg_rating, COUNT(*) as rated FROM support_tickets WHERE satisfaction_rating IS NOT NULL")->fetch(\PDO::FETCH_ASSOC) ?: ['avg_rating' => 0, 'rated' => 0];
        } catch (\Exception $e) {
            $tickets = [];
            $avg = ['avg_rating' => 0, 'rated' => 0];
        }
        return $this->render('admin/crm/feedback', ['tickets' => $tickets, 'avg_rating' => $avg]);
    }

    public function support()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tickets = $db->query("SELECT st.*, u.name as user_name, a.name as assignee_name FROM support_tickets st LEFT JOIN users u ON st.user_id=u.id LEFT JOIN users a ON st.assigned_to=a.id ORDER BY st.created_at DESC LIMIT 100")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $stats = [
                'total' => (int)$db->query("SELECT COUNT(*) FROM support_tickets")->fetchColumn(),
                'open' => (int)$db->query("SELECT COUNT(*) FROM support_tickets WHERE status='open'")->fetchColumn(),
                'in_progress' => (int)$db->query("SELECT COUNT(*) FROM support_tickets WHERE status='in_progress'")->fetchColumn(),
                'resolved' => (int)$db->query("SELECT COUNT(*) FROM support_tickets WHERE status='resolved'")->fetchColumn(),
            ];
        } catch (\Exception $e) {
            $tickets = [];
            $stats = ['total' => 0, 'open' => 0, 'in_progress' => 0, 'resolved' => 0];
        }
        return $this->render('admin/crm/support', ['tickets' => $tickets, 'stats' => $stats]);
    }

    /**
     * CRM Analytics Dashboard — conversion funnel, source analytics, agent performance
     */
    public function analytics()
    {
        $this->requireAdmin();
        $service = new CRMService();

        $period = $_GET['period'] ?? '30d';
        $funnel = $service->getConversionFunnel();
        $sourceAnalytics = $service->getSourceAnalytics($period);
        $agentPerformance = $service->getAgentPerformance();

        // Pipeline value (weighted)
        $pipelineValue = ['total' => 0, 'by_stage' => []];
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $deals = $db->query("SELECT stage, COUNT(*) as cnt, SUM(deal_value) as total_value, SUM(CASE WHEN stage IN ('won','booking') THEN deal_value ELSE 0 END) as won_value FROM lead_deals GROUP BY stage")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            foreach ($deals as $d) {
                $pipelineValue['total'] += (float)($d['total_value'] ?? 0);
                $pipelineValue['by_stage'][] = [
                    'stage' => $d['stage'],
                    'count' => (int)$d['cnt'],
                    'value' => (float)($d['total_value'] ?? 0),
                    'won_value' => (float)($d['won_value'] ?? 0),
                ];
            }
        } catch (\Throwable $e) {}

        return $this->render('admin/crm/analytics', [
            'funnel' => $funnel,
            'source_analytics' => $sourceAnalytics,
            'agent_performance' => $agentPerformance,
            'pipeline_value' => $pipelineValue,
            'current_period' => $period,
            'page_title' => 'CRM Analytics',
        ]);
    }

    /**
     * Lead Timeline — full activity timeline for a lead
     */
    public function leadTimeline($leadId)
    {
        $this->requireAdmin();
        $service = new CRMService();

        $lead = $service->getLeadById((int)$leadId);
        $timeline = $service->getLeadTimeline((int)$leadId, 100);

        return $this->render('admin/crm/lead_timeline', [
            'lead' => $lead,
            'timeline' => $timeline,
            'page_title' => 'Lead Timeline',
        ]);
    }
}
