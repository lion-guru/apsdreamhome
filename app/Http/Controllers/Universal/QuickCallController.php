<?php

namespace App\Http\Controllers\Universal;

use App\Traits\TenantAwareTrait;

class QuickCallController extends \App\Http\Controllers\BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
        $this->data = [];
    }

    /**
     * Universal call/SMS desk — works for any authenticated user
     * Uses tel: and sms: protocols (Jio Fiber free calling/SMS)
     */
    public function index()
    {
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['employee_id']) && !isset($_SESSION['admin_id'])) {
            $this->redirect('/login');
            return;
        }

        $userId = (int)($_SESSION['user_id'] ?? $_SESSION['employee_id'] ?? $_SESSION['admin_id'] ?? 0);
        $role = $_SESSION['role'] ?? 'employee';

        $leads = [];
        $recentCalls = [];

        try {
            // Show leads assigned to this user OR unassigned
            $leads = $this->db->fetchAll("
                SELECT l.id, l.name, l.phone, l.email, l.status,
                       l.created_at, l.location_preference, l.property_interest
                FROM leads l
                WHERE (l.assigned_to = ? OR l.assigned_to IS NULL OR l.created_by = ?)
                  AND l.status IN ('new', 'contacted', 'qualified', 'follow_up', 'pending', 'proposal', 'negotiation')
                  AND l.phone IS NOT NULL AND l.phone != ''
                ORDER BY 
                    CASE WHEN l.status = 'new' THEN 0
                         WHEN l.status = 'follow_up' THEN 1
                         ELSE 2 END,
                    l.created_at DESC
                LIMIT 100
            ", [$userId, $userId]);
        } catch (\Throwable $e) { error_log('[' . __CLASS__ . ']: ' . $e->getMessage()); }

        try {
            $recentCalls = $this->db->fetchAll("
                SELECT cl.*, l.name as lead_name, l.phone as lead_phone
                FROM call_logs cl
                LEFT JOIN leads l ON l.id = cl.lead_id
                WHERE cl.agent_id = ?
                ORDER BY cl.call_time DESC
                LIMIT 20
            ", [$userId]);
        } catch (\Throwable $e) { error_log('[' . __CLASS__ . ']: ' . $e->getMessage()); }

        $this->data = [
            'page_title' => 'Quick Call & SMS Desk — Jio Fiber Free Calling',
            'leads' => $leads,
            'recentCalls' => $recentCalls,
            'role' => $role,
            'userId' => $userId,
        ];
        $this->render('universal/quick_call_desk');
    }

    public function logCall()
    {
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['employee_id']) && !isset($_SESSION['admin_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Not authenticated']);
            return;
        }
        $this->validateCsrfOrFail();

        $leadId = (int)($_POST['lead_id'] ?? 0);
        $outcome = trim($_POST['outcome'] ?? '');
        $duration = (int)($_POST['duration'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        $userId = (int)($_SESSION['user_id'] ?? $_SESSION['employee_id'] ?? $_SESSION['admin_id'] ?? 0);

        $allowedOutcomes = ['connected', 'not_answered', 'busy', 'switched_off', 'wrong_number', 'interested', 'not_interested', 'callback_requested'];
        if (!in_array($outcome, $allowedOutcomes)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid outcome']);
            return;
        }

        try {
            $this->db->execute("
                INSERT INTO call_logs (lead_id, agent_id, call_type, status, duration, notes, call_time)
                VALUES (?, ?, 'manual_jio', ?, ?, ?, NOW())
            ", [$leadId, $userId, $outcome, $duration, $notes]);

            $newStatus = null;
            if (in_array($outcome, ['interested'])) $newStatus = 'qualified';
            if (in_array($outcome, ['not_interested'])) $newStatus = 'closed_lost';
            if (in_array($outcome, ['callback_requested'])) $newStatus = 'follow_up';

            if ($newStatus) {
                $this->db->execute("UPDATE leads SET status = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $leadId]);
            }

            $this->jsonResponse(['success' => true, 'message' => 'Call logged successfully']);
        } catch (\Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to log call: ' . $e->getMessage()]);
        }
    }

    public function sendSmsTemplate()
    {
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['employee_id']) && !isset($_SESSION['admin_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Not authenticated']);
            return;
        }
        $this->validateCsrfOrFail();

        $leadId = (int)($_POST['lead_id'] ?? 0);
        $template = trim($_POST['template'] ?? '');

        $templates = [
            'site_visit' => "Namaste! APS Dream Home se. Aapka site visit schedule karne ke liye hum contact kar rahe hain. Kya aap kal visit kar sakte hain? Reply YES/NO. - APS Dream Home, Gorakhpur",
            'emi_info' => "APS Dream Home: Aapke selected plot ke liye EMI plan available hai. Low down payment, easy monthly installments. Details ke liye call karein. - APS Dream Home",
            'offer' => "APS Dream Home: Special offer! Selected plots pe 5% discount. Limited time. Call karein for details. - APS Dream Home, Gorakhpur",
            'followup' => "Namaste! APS Dream Home se. Aapne hamare project me interest dikhaya tha. Kya aap abhi bhi interested hain? Reply YES/NO. - APS Dream Home",
            'welcome' => "Welcome to APS Dream Home! Aapka account create ho gaya hai. Apne portal pe login karein: https://apsdreamhome.com - APS Dream Home",
            'payment_reminder' => "APS Dream Home: Aapka EMI installment due hai. Please timely payment karein. Late payment pe penalty lagegi. - APS Dream Home",
            'booking_confirm' => "APS Dream Home: Aapka booking confirm ho gaya hai! Plot details aur payment receipt portal pe available hai. - APS Dream Home, Gorakhpur",
        ];

        if (!isset($templates[$template])) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid template']);
            return;
        }

        $message = $templates[$template];

        try {
            $lead = $this->db->fetchOne("SELECT phone, name FROM leads WHERE id = ?", [$leadId]);
            if (!$lead || empty($lead['phone'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Lead not found or no phone']);
                return;
            }

            $phone = preg_replace('/[^0-9]/', '', $lead['phone']);
            if (strlen($phone) === 10) $phone = '91' . $phone;

            $this->db->execute("
                INSERT INTO sms_queue (phone, message, status, created_at" . ($this->tenantId() > 1 ? ', tenant_id' : '') . ")
                VALUES (?, ?, 'pending_manual', NOW()" . ($this->tenantId() > 1 ? ', ' . $this->tenantId() : '') . ")
            ", [$lead['phone'], $message]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'SMS template ready. Open your phone SMS app to send.',
                'phone' => $lead['phone'],
                'sms_text' => $message,
                'sms_link' => 'sms:' . $lead['phone'] . '?body=' . urlencode($message)
            ]);
        } catch (\Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk SMS — prepare SMS for multiple leads at once
     */
    public function bulkSms()
    {
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['employee_id']) && !isset($_SESSION['admin_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Not authenticated']);
            return;
        }
        $this->validateCsrfOrFail();

        $leadIds = $_POST['lead_ids'] ?? [];
        $template = trim($_POST['template'] ?? '');

        if (empty($leadIds) || !is_array($leadIds)) {
            $this->jsonResponse(['success' => false, 'message' => 'No leads selected']);
            return;
        }

        $templates = [
            'site_visit' => "Namaste! APS Dream Home se. Aapka site visit schedule karne ke liye hum contact kar rahe hain. Kya aap kal visit kar sakte hain? Reply YES/NO. - APS Dream Home, Gorakhpur",
            'emi_info' => "APS Dream Home: Aapke selected plot ke liye EMI plan available hai. Low down payment, easy monthly installments. Details ke liye call karein. - APS Dream Home",
            'offer' => "APS Dream Home: Special offer! Selected plots pe 5% discount. Limited time. Call karein for details. - APS Dream Home, Gorakhpur",
            'followup' => "Namaste! APS Dream Home se. Aapne hamare project me interest dikhaya tha. Kya aap abhi bhi interested hain? Reply YES/NO. - APS Dream Home",
            'welcome' => "Welcome to APS Dream Home! Aapka account create ho gaya hai. Apne portal pe login karein: https://apsdreamhome.com - APS Dream Home",
        ];

        if (!isset($templates[$template])) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid template']);
            return;
        }

        $message = $templates[$template];
        $results = [];

        try {
            foreach ($leadIds as $leadId) {
                $leadId = (int)$leadId;
                $lead = $this->db->fetchOne("SELECT phone, name FROM leads WHERE id = ?", [$leadId]);
                if ($lead && !empty($lead['phone'])) {
                    $this->db->execute("
                        INSERT INTO sms_queue (phone, message, status, created_at" . ($this->tenantId() > 1 ? ', tenant_id' : '') . ")
                        VALUES (?, ?, 'pending_manual', NOW()" . ($this->tenantId() > 1 ? ', ' . $this->tenantId() : '') . ")
                    ", [$lead['phone'], $message]);

                    $results[] = [
                        'lead_id' => $leadId,
                        'name' => $lead['name'],
                        'phone' => $lead['phone'],
                        'sms_link' => 'sms:' . $lead['phone'] . '?body=' . urlencode($message)
                    ];
                }
            }

            $this->jsonResponse([
                'success' => true,
                'message' => count($results) . ' SMS templates prepared',
                'count' => count($results),
                'results' => $results
            ]);
        } catch (\Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed: ' . $e->getMessage()]);
        }
    }
}