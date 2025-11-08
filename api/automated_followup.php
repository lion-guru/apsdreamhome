<?php
/**
 * Automated Follow-up System
 * Handles scheduled follow-ups for property visits and leads
 */

require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/db_settings.php';

class AutomatedFollowup {
    private $conn;
    private $templates = [
        'visit_reminder' => [
            'subject' => 'Reminder: Your Property Visit Tomorrow',
            'message' => "Dear {name},\n\nThis is a reminder about your scheduled visit tomorrow at {time} for {property}.\n\nLocation: {location}\n\nIf you need to reschedule, please contact us at " . SUPPORT_EMAIL
        ],
        'visit_feedback' => [
            'subject' => 'How was your property visit?',
            'message' => "Dear {name},\n\nThank you for visiting {property}. We'd love to hear your feedback!\n\nPlease take a moment to rate your experience: {feedback_link}\n\nInterested in scheduling another visit? Browse more properties: " . SITE_URL
        ],
        'lead_followup' => [
            'subject' => 'Following up on your property inquiry',
            'message' => "Hi {name},\n\nJust checking in about {property} that you inquired about. Are you still interested?\n\nWe'd be happy to:\n- Schedule a visit\n- Answer any questions\n- Show you similar properties\n\nJust reply to this email or call us."
        ]
    ];

    public function __construct() {
        $this->conn = get_db_connection();
    }

    public function processFollowups() {
        $this->sendVisitReminders();
        $this->sendVisitFeedbackRequests();
        $this->sendLeadFollowups();
        $this->updateAIScores();
    }

    private function sendVisitReminders() {
        // Get tomorrow's visits
        $query = "SELECT b.*, p.title as property_name, p.location, c.name, c.email 
                 FROM bookings b 
                 JOIN properties p ON b.property_id = p.id 
                 JOIN customers c ON b.customer_id = c.id 
                 WHERE DATE(b.visit_date) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                 AND b.status = 'confirmed'
                 AND b.reminder_sent = 0";

        $result = $this->conn->query($query);
        while ($booking = $result->fetch_assoc()) {
            $template = $this->templates['visit_reminder'];
            $message = strtr($template['message'], [
                '{name}' => $booking['name'],
                '{time}' => $booking['visit_time'],
                '{property}' => $booking['property_name'],
                '{location}' => $booking['location']
            ]);

            if ($this->sendEmail($booking['email'], $template['subject'], $message)) {
                $this->conn->query("UPDATE bookings SET reminder_sent = 1 WHERE id = " . $booking['id']);
            }
        }
    }

    private function sendVisitFeedbackRequests() {
        // Get visits from yesterday
        $query = "SELECT b.*, p.title as property_name, c.name, c.email 
                 FROM bookings b 
                 JOIN properties p ON b.property_id = p.id 
                 JOIN customers c ON b.customer_id = c.id 
                 WHERE DATE(b.visit_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                 AND b.status = 'completed'
                 AND b.feedback_requested = 0";

        $result = $this->conn->query($query);
        while ($booking = $result->fetch_assoc()) {
            $feedback_token = bin2hex(random_bytes(16));
            $feedback_link = SITE_URL . "/feedback.php?token=" . $feedback_token;

            $template = $this->templates['visit_feedback'];
            $message = strtr($template['message'], [
                '{name}' => $booking['name'],
                '{property}' => $booking['property_name'],
                '{feedback_link}' => $feedback_link
            ]);

            if ($this->sendEmail($booking['email'], $template['subject'], $message)) {
                $this->conn->query("UPDATE bookings SET feedback_requested = 1, feedback_token = '$feedback_token' WHERE id = " . $booking['id']);
            }
        }
    }

    private function sendLeadFollowups() {
        // Get leads without response for 2 days
        $query = "SELECT l.*, p.title as property_name 
                 FROM leads l 
                 JOIN properties p ON l.property_id = p.id 
                 WHERE l.status = 'new' 
                 AND l.created_at <= DATE_SUB(NOW(), INTERVAL 2 DAY)
                 AND l.followup_count < 3";

        $result = $this->conn->query($query);
        while ($lead = $result->fetch_assoc()) {
            $template = $this->templates['lead_followup'];
            $message = strtr($template['message'], [
                '{name}' => $lead['name'],
                '{property}' => $lead['property_name']
            ]);

            if ($this->sendEmail($lead['email'], $template['subject'], $message)) {
                $this->conn->query("UPDATE leads SET 
                    followup_count = followup_count + 1,
                    last_followup = NOW() 
                    WHERE id = " . $lead['id']);
            }
        }
    }

    private function updateAIScores() {
        // Update AI scores based on user interactions
        $query = "UPDATE ai_lead_scores als 
                 JOIN (
                     SELECT property_id, customer_id,
                            SUM(CASE 
                                WHEN interaction_type = 'visit_completed' THEN 50
                                WHEN interaction_type = 'inquiry' THEN 30
                                WHEN interaction_type = 'view' THEN 10
                                ELSE 0 
                            END) as interaction_score
                     FROM customer_journeys
                     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                     GROUP BY property_id, customer_id
                 ) scores ON als.property_id = scores.property_id 
                    AND als.user_id = scores.customer_id
                 SET als.score = scores.interaction_score";

        $this->conn->query($query);
    }

    private function sendEmail($to, $subject, $message) {
        $headers = [
            'From: ' . SITE_NAME . ' <' . SUPPORT_EMAIL . '>',
            'Reply-To: ' . SUPPORT_EMAIL,
            'X-Mailer: PHP/' . phpversion()
        ];

        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
}

// Run automated follow-ups
try {
    $followup = new AutomatedFollowup();
    $followup->processFollowups();
    echo json_encode(['status' => 'success', 'message' => 'Follow-ups processed successfully']);
} catch (Exception $e) {
    error_log("Automated follow-up error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to process follow-ups']);
}
?>
