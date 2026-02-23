<?php

namespace App\Http\Controllers\Api;

use \Exception;

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
            // Check for a secret key or admin auth if called via web
            // For now, let's just implement the logic

            $remindersSent = $this->sendVisitReminders();
            $feedbackRequestsSent = $this->sendVisitFeedbackRequests();
            $leadFollowupsSent = $this->sendLeadFollowups();

            return $this->jsonSuccess([
                'reminders' => $remindersSent,
                'feedback_requests' => $feedbackRequestsSent,
                'lead_followups' => $leadFollowupsSent
            ]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    private function sendVisitReminders()
    {
        $count = 0;
        $visitModel = $this->model('PropertyVisit');
        $visits = $visitModel->getVisitsForReminder();

        foreach ($visits as $visit) {
            $template = $this->templates['visit_reminder'];
            $message = \strtr($template['message'], [
                '{name}' => $visit['name'],
                '{time}' => $visit['visit_date'], // Adjust if visit_time is separate
                '{property}' => $visit['property_name'],
                '{location}' => $visit['location']
            ]);

            // In a real app, you would call an EmailService here
            // if ($this->emailService->send($visit['email'], $template['subject'], $message)) {
                $visitModel->update($visit['id'], ['reminder_sent' => 1]);
                $count++;
            // }
        }
        return $count;
    }

    private function sendVisitFeedbackRequests()
    {
        $count = 0;
        $visitModel = $this->model('PropertyVisit');
        $visits = $visitModel->getVisitsForFeedback();

        foreach ($visits as $visit) {
            $feedbackToken = \bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes(16));
            $feedbackLink = "https://apsdreamhome.com/feedback?token=" . $feedbackToken;

            $template = $this->templates['visit_feedback'];
            $message = \strtr($template['message'], [
                '{name}' => $visit['name'],
                '{property}' => $visit['property_name'],
                '{feedback_link}' => $feedbackLink
            ]);

            // if ($this->emailService->send($visit['email'], $template['subject'], $message)) {
                $visitModel->update($visit['id'], [
                    'feedback_requested' => 1,
                    'feedback_token' => $feedbackToken
                ]);
                $count++;
            // }
        }
        return $count;
    }

    private function sendLeadFollowups()
    {
        $count = 0;
        $leadModel = $this->model('Lead');
        $leads = $leadModel->getLeadsForFollowup();

        foreach ($leads as $lead) {
            $template = $this->templates['lead_followup'];
            $message = \strtr($template['message'], [
                '{name}' => $lead['name'],
                '{property}' => $lead['property_interest'] ?? 'our properties'
            ]);

            // if ($this->emailService->send($lead['email'], $template['subject'], $message)) {
                $leadModel->update($lead['id'], [
                    'last_followup_sent_at' => \date('Y-m-d H:i:s')
                ]);
                $count++;
            // }
        }
        return $count;
    }
}
