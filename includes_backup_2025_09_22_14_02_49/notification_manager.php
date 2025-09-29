<?php
class NotificationManager {
    private $conn;
    
    public function __construct($dbConnection = null) {
        $this->conn = $dbConnection ?? get_db_connection();
        if (!$this->conn) {
            throw new Exception('Database connection failed');
        }
    }
    
    /**
     * Send a basic notification
     * 
     * @param array $data Notification data
     * @return bool Whether notification was sent successfully
     */
    public function send($data) {
        try {
            // Validate required fields
            if (empty($data['user_id']) || empty($data['type']) || empty($data['message'])) {
                throw new Exception('Missing required notification fields');
            }
            
            // Prepare notification data
            $user_id = $data['user_id'];
            $type = $data['type'];
            $title = $data['title'] ?? '';
            $message = $data['message'];
            $link = $data['link'] ?? '';
            $status = 'unread';
            
            // Insert notification
            $stmt = $this->conn->prepare(
                "INSERT INTO notifications (user_id, type, title, message, link, status, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, NOW())"
            );
            
            $stmt->bind_param('isssss', $user_id, $type, $title, $message, $link, $status);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to insert notification: ' . $stmt->error);
            }
            
            $notification_id = $this->conn->insert_id;
            
            // Log notification
            error_log("Notification sent: ID=$notification_id, Type=$type, User=$user_id");
            
            return true;
        } catch (Exception $e) {
            error_log('Notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a templated email
     * 
     * @param string $email Recipient email
     * @param string $templateType Email template type
     * @param array $data Template data
     * @return bool Whether email was sent successfully
     */
    public function sendTemplatedEmail(string $email, string $templateType, array $data): bool {
        try {
            // Fetch email template from database
            $stmt = $this->conn->prepare(
                "SELECT title_template, body_template 
                 FROM notification_templates 
                 WHERE type = ? AND channel = 'email'"
            );
            $stmt->bind_param('s', $templateType);
            $stmt->execute();
            $result = $stmt->get_result();
            $template = $result->fetch_assoc();

            if (!$template) {
                throw new Exception("No email template found for type: $templateType");
            }

            // Replace placeholders in template
            $subject = $this->replacePlaceholders($template['title_template'], $data);
            $body = $this->replacePlaceholders($template['body_template'], $data);

            // Send email (replace with actual email sending logic)
            $this->sendEmail($email, $subject, $body);

            return true;
        } catch (Exception $e) {
            error_log('Templated Email Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a templated notification
     * 
     * @param int $userId User ID
     * @param string $templateType Notification template type
     * @param array $data Template data
     * @param string $channel Notification channel
     * @return bool Whether notification was created successfully
     */
    public function createTemplatedNotification(
        int $userId, 
        string $templateType, 
        array $data, 
        string $channel = 'in_app'
    ): bool {
        try {
            // Fetch notification template
            $stmt = $this->conn->prepare(
                "SELECT title_template, body_template 
                 FROM notification_templates 
                 WHERE type = ? AND channel = ?"
            );
            $stmt->bind_param('ss', $templateType, $channel);
            $stmt->execute();
            $result = $stmt->get_result();
            $template = $result->fetch_assoc();

            if (!$template) {
                throw new Exception("No notification template found for type: $templateType");
            }

            // Replace placeholders in template
            $title = $this->replacePlaceholders($template['title_template'], $data);
            $body = $this->replacePlaceholders($template['body_template'], $data);

            // Create notification
            return $this->send([
                'user_id' => $userId,
                'type' => $templateType,
                'title' => $title,
                'message' => $body
            ]);
        } catch (Exception $e) {
            error_log('Templated Notification Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Replace placeholders in a template
     * 
     * @param string $template Template string
     * @param array $data Replacement data
     * @return string Processed template
     */
    private function replacePlaceholders(string $template, array $data): string {
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }

    /**
     * Send email (placeholder implementation)
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body
     */
    private function sendEmail(string $to, string $subject, string $body): void {
        // Replace with actual email sending logic
        // This could use PHPMailer, mail() function, or another email library
        error_log("Email sent to $to: $subject");
    }
}
