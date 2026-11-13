<?php

namespace App\Http\Controllers\Employee;

use App\Traits\TenantAwareTrait;

class ManualCallingDeskController extends \App\Http\Controllers\BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/employee';
        $this->data = [];
    }

    public function index()
    {
        if (!isset($_SESSION['employee_id'])) { $this->redirect('/employee/login'); return; }
        $employeeId = (int)$_SESSION['employee_id'];

        $leads = [];
        $todayFollowups = [];
        $recentCalls = [];

        try {
            // Leads assigned to this employee, not yet contacted or in progress
            // S347: show leads assigned to this employee OR unassigned (for test/demo).
            // In production, leads are assigned to the employee's user_id.
            $leads = $this->db->fetchAll("
                SELECT l.id, l.name, l.phone, l.email, l.status,
                       l.created_at, l.location_preference, l.property_interest
                FROM leads l
                WHERE (l.assigned_to = ? OR l.assigned_to IS NULL OR l.created_by = ?)
                  AND l.status IN ('new', 'contacted', 'qualified', 'follow_up', 'pending')
                  AND l.phone IS NOT NULL AND l.phone != ''
                ORDER BY 
                    CASE WHEN l.status = 'new' THEN 0
                         WHEN l.status = 'follow_up' THEN 1
                         ELSE 2 END,
                    l.created_at DESC
                LIMIT 50
            ", [$employeeId, $employeeId]);
        } catch (\Throwable $e) { error_log('[' . __CLASS__ . ']: ' . $e->getMessage()); }

        try {
            $todayFollowups = $this->db->fetchAll("
                SELECT f.*, l.name as lead_name, l.phone as lead_phone
                FROM followups f
                JOIN leads l ON l.id = f.lead_id
                WHERE f.assigned_to = ? 
                  AND f.status = 'pending'
                  AND DATE(f.followup_date) <= CURDATE()
                ORDER BY f.followup_date ASC
                LIMIT 20
            ", [$employeeId]);
        } catch (\Throwable $e) { error_log('[' . __CLASS__ . ']: ' . $e->getMessage()); }

        try {
            $recentCalls = $this->db->fetchAll("
                SELECT cl.*, l.name as lead_name, l.phone as lead_phone
                FROM call_logs cl
                LEFT JOIN leads l ON l.id = cl.lead_id
                WHERE cl.agent_id = ?
                ORDER BY cl.call_time DESC
                LIMIT 10
            ", [$employeeId]);
        } catch (\Throwable $e) { error_log('[' . __CLASS__ . ']: ' . $e->getMessage()); }
        
        $this->data = [
            'page_title' => 'Manual Calling Desk — Jio Fiber Free Calling',
            'leads' => $leads,
            'todayFollowups' => $todayFollowups,
            'recentCalls' => $recentCalls,
        ];
        $this->render('employee/manual_calling_desk');
    }

    public function logCall()
    {
        if (!isset($_SESSION['employee_id'])) { $this->redirect('/employee/login'); return; }
        $this->validateCsrfOrFail();

        $leadId = (int)($_POST['lead_id'] ?? 0);
        $outcome = trim($_POST['outcome'] ?? '');
        $duration = (int)($_POST['duration'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        $employeeId = (int)($_SESSION['employee_id'] ?? 0);

        $allowedOutcomes = ['connected', 'not_answered', 'busy', 'switched_off', 'wrong_number', 'interested', 'not_interested', 'callback_requested'];
        if (!in_array($outcome, $allowedOutcomes)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid outcome']);
            return;
        }

        try {
            $this->db->execute("
                INSERT INTO call_logs (lead_id, agent_id, call_type, status, duration, notes, call_time)
                VALUES (?, ?, 'manual_jio', ?, ?, ?, NOW())
            ", [$leadId, $employeeId, $outcome, $duration, $notes]);

            // Update lead status based on outcome
            $newStatus = null;
            if (in_array($outcome, ['interested'])) $newStatus = 'qualified';
            if (in_array($outcome, ['not_interested'])) $newStatus = 'closed_lost';
            if (in_array($outcome, ['callback_requested'])) $newStatus = 'follow_up';

            if ($newStatus) {
                $this->db->execute("UPDATE leads SET status = ?, last_contacted_at = NOW() WHERE id = ?", [$newStatus, $leadId]);
            } else {
                $this->db->execute("UPDATE leads SET last_contacted_at = NOW() WHERE id = ?", [$leadId]);
            }

            $this->jsonResponse(['success' => true, 'message' => 'Call logged successfully']);
        } catch (\Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to log call: ' . $e->getMessage()]);
        }
    }

    public function sendSmsTemplate()
    {
        if (!isset($_SESSION['employee_id'])) { $this->redirect('/employee/login'); return; }
        $this->validateCsrfOrFail();

        $leadId = (int)($_POST['lead_id'] ?? 0);
        $template = trim($_POST['template'] ?? '');

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

        try {
            $lead = $this->db->fetchOne("SELECT phone, name FROM leads WHERE id = ?", [$leadId]);
            if (!$lead || empty($lead['phone'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Lead not found or no phone']);
                return;
            }

            // Log the SMS attempt (actual sending via Jio Fiber SMS app is manual)
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
}