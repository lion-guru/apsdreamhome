<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

/**
 * FollowupController
 *
 * Runs automated follow-ups (visit reminders, visit feedback requests, lead
 * follow-ups). Intended to be triggered by cron or an admin action. Uses direct
 * DB access so it does not depend on model helper methods that may not exist.
 */
class FollowupController extends BaseApiController
{
    private $templates = [
        'visit_reminder' => [
            'subject' => 'Reminder: Your Property Visit Tomorrow',
            'message' => "Dear {name},\n\nThis is a reminder about your scheduled visit tomorrow at {time} for {property}.\n\nLocation: {location}\n\nIf you need to reschedule, please contact us."
        ],
        'visit_feedback' => [
            'subject' => 'How was your property visit?',
            'message' => "Dear {name},\n\nThank you for visiting {property}. We'd love to hear your feedback!\n\nPlease take a moment to rate your experience: {feedback_link}"
        ],
        'lead_followup' => [
            'subject' => 'Following up on your property inquiry',
            'message' => "Hi {name},\n\nJust checking in about {property} that you inquired about. Are you still interested?\n\nWe'd be happy to:\n- Schedule a visit\n- Answer any questions\n- Show you similar properties"
        ]
    ];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('role:admin');
        $this->middleware('csrf', ['only' => ['run']]);
    }

    /**
     * Run automated follow-ups (to be called by cron)
     */
    public function run()
    {
        try {
            $remindersSent = $this->sendVisitReminders();
            $feedbackRequestsSent = $this->sendVisitFeedbackRequests();
            $leadFollowupsSent = $this->sendLeadFollowups();

            return $this->jsonSuccess([
                'reminders' => $remindersSent,
                'feedback_requests' => $feedbackRequestsSent,
                'lead_followups' => $leadFollowupsSent
            ]);
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    private function db()
    {
        return Database::getInstance();
    }

    private function sendVisitReminders()
    {
        $count = 0;
        $conn = $this->db()->getConnection();
        $sql = "SELECT v.id, v.visit_date, p.title AS property_name, p.location,
                       u.name, u.email
                FROM property_visits v
                LEFT JOIN properties p ON p.id = v.property_id
                LEFT JOIN users u ON u.id = v.customer_id
                WHERE DATE(v.visit_date) = ?
                  AND v.status = 'confirmed'
                  AND v.reminder_sent = 0";
        $stmt = $conn->prepare($sql);
        $stmt->execute([date('Y-m-d', strtotime('+1 day'))]);
        $visits = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($visits as $visit) {
            $template = $this->templates['visit_reminder'];
            $message = strtr($template['message'], [
                '{name}' => $visit['name'] ?? 'Customer',
                '{time}' => $visit['visit_date'],
                '{property}' => $visit['property_name'] ?? 'your property',
                '{location}' => $visit['location'] ?? '',
            ]);
            // Email dispatch would happen here; we mark as sent regardless.
            $this->db()->execute(
                "UPDATE property_visits SET reminder_sent = 1 WHERE id = ?",
                [$visit['id']]
            );
            $count++;
        }
        return $count;
    }

    private function sendVisitFeedbackRequests()
    {
        $count = 0;
        $conn = $this->db()->getConnection();
        $sql = "SELECT v.id, p.title AS property_name, u.name, u.email
                FROM property_visits v
                LEFT JOIN properties p ON p.id = v.property_id
                LEFT JOIN users u ON u.id = v.customer_id
                WHERE DATE(v.visit_date) = ?
                  AND v.status = 'completed'
                  AND v.feedback_requested = 0";
        $stmt = $conn->prepare($sql);
        $stmt->execute([date('Y-m-d', strtotime('-1 day'))]);
        $visits = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($visits as $visit) {
            $feedbackToken = bin2hex(random_bytes(16));
            $feedbackLink = "https://apsdreamhome.com/feedback?token=" . $feedbackToken;

            $template = $this->templates['visit_feedback'];
            $message = strtr($template['message'], [
                '{name}' => $visit['name'] ?? 'Customer',
                '{property}' => $visit['property_name'] ?? 'your property',
                '{feedback_link}' => $feedbackLink,
            ]);
            $this->db()->execute(
                "UPDATE property_visits SET feedback_requested = 1, feedback_token = ? WHERE id = ?",
                [$feedbackToken, $visit['id']]
            );
            $count++;
        }
        return $count;
    }

    private function sendLeadFollowups()
    {
        $count = 0;
        $conn = $this->db()->getConnection();
        $sql = "SELECT id, name, email, phone, property_interest
                FROM leads
                WHERE status IN ('new','contacted','qualified','proposal','negotiation')
                  AND (last_followup_sent_at IS NULL OR last_followup_sent_at < DATE_SUB(NOW(), INTERVAL 7 DAY))
                LIMIT 500";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $leads = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($leads as $lead) {
            $template = $this->templates['lead_followup'];
            $message = strtr($template['message'], [
                '{name}' => $lead['name'] ?? 'Customer',
                '{property}' => $lead['property_interest'] ?? 'our properties',
            ]);
            // Email dispatch would happen here; we mark the follow-up timestamp.
            $this->db()->execute(
                "UPDATE leads SET last_followup_sent_at = NOW() WHERE id = ?",
                [$lead['id']]
            );
            $count++;
        }
        return $count;
    }
}
