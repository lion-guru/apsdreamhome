<?php
// Advanced Email Service Manager

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    // Email Configuration
    private $config = [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => '',
        'password' => '',
        'from_email' => 'noreply@apsdreamhomes.com',
        'from_name' => 'APS Dream Homes',
        'reply_to_email' => 'support@apsdreamhomes.com',
        'reply_to_name' => 'APS Dream Homes Support',
        'encryption' => PHPMailer::ENCRYPTION_STARTTLS
    ];

    // Email Templates
    private $templates = [
        'welcome' => [
            'subject' => 'Welcome to APS Dream Homes',
            'body' => 'email_templates/welcome.html'
        ],
        'property_inquiry' => [
            'subject' => 'New Property Inquiry',
            'body' => 'email_templates/property_inquiry.html'
        ],
        'visit_confirmation' => [
            'subject' => 'Property Visit Confirmed',
            'body' => 'email_templates/visit_confirmation.html'
        ],
        'visit_reminder' => [
            'subject' => 'Upcoming Property Visit Reminder',
            'body' => 'email_templates/visit_reminder.html'
        ],
        'lead_assignment' => [
            'subject' => 'New Lead Assigned',
            'body' => 'email_templates/lead_assignment.html'
        ]
    ];

    // Dependencies
    private $logger;
    private $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;

        // Load email configuration from environment or database
        $this->loadConfiguration();
    }

    /**
     * Load email configuration
     */
    private function loadConfiguration() {
        // Priority: Environment Variables > Database Config > Default
        $this->config['username'] = getenv('EMAIL_USERNAME') ?: $this->config['username'];
        $this->config['password'] = getenv('EMAIL_PASSWORD') ?: $this->config['password'];

        // Optionally load from database configuration
        try {
            $stmt = $this->db->prepare("SELECT `key`, `value` FROM email_config");
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                if (isset($this->config[$row['key']])) {
                    $this->config[$row['key']] = $row['value'];
                }
            }
        } catch (Exception $e) {
            $this->logger->log(
                "Email config load error: " . $e->getMessage(), 
                'warning', 
                'email'
            );
        }
    }

    /**
     * Send email
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body
     * @param array $attachments Optional attachments
     * @param array $cc Optional CC recipients
     * @param array $bcc Optional BCC recipients
     * @return bool
     */
    public function send($to, $subject, $body, $attachments = [], $cc = [], $bcc = []) {
        try {
            // Create PHPMailer instance
            $mail = new PHPMailer(true);

            // Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host       = $this->config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->config['username'];
            $mail->Password   = $this->config['password'];
            $mail->SMTPSecure = $this->config['encryption'];
            $mail->Port       = $this->config['port'];

            // Recipients
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addReplyTo($this->config['reply_to_email'], $this->config['reply_to_name']);
            
            // Add primary recipient
            $mail->addAddress($to);

            // Add CC recipients
            foreach ($cc as $email) {
                $mail->addCC($email);
            }

            // Add BCC recipients
            foreach ($bcc as $email) {
                $mail->addBCC($email);
            }

            // Attachments
            foreach ($attachments as $attachment) {
                $mail->addAttachment($attachment);
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            // Send email
            $mail->send();

            // Log successful email
            $this->logger->log(
                "Email sent to: {$to}, Subject: {$subject}", 
                'info', 
                'email'
            );

            return true;
        } catch (Exception $e) {
            // Log email sending error
            $this->logger->log(
                "Email send error to {$to}: " . $e->getMessage(), 
                'error', 
                'email'
            );

            return false;
        }
    }

    /**
     * Send email from template
     * @param string $template_name Template name
     * @param string $to Recipient email
     * @param array $template_data Template replacement data
     * @return bool
     */
    public function sendFromTemplate($template_name, $to, $template_data = []) {
        try {
            // Check if template exists
            if (!isset($this->templates[$template_name])) {
                throw new Exception("Email template not found: {$template_name}");
            }

            $template = $this->templates[$template_name];

            // Load template content
            $template_path = __DIR__ . '/../' . $template['body'];
            if (!file_exists($template_path)) {
                throw new Exception("Email template file not found: {$template_path}");
            }

            $body = file_get_contents($template_path);

            // Replace template placeholders
            foreach ($template_data as $key => $value) {
                $body = str_replace("{{" . $key . "}}", $value, $body);
            }

            // Send email
            return $this->send(
                $to, 
                $template['subject'], 
                $body
            );
        } catch (Exception $e) {
            $this->logger->log(
                "Email template error: " . $e->getMessage(), 
                'error', 
                'email'
            );
            return false;
        }
    }

    /**
     * Queue email for later sending
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body
     * @param array $options Additional options
     * @return bool
     */
    public function queueEmail($to, $subject, $body, $options = []) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO email_queue 
                (recipient, subject, body, status, scheduled_at, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");

            $status = 'pending';
            $scheduled_at = $options['scheduled_at'] ?? date('Y-m-d H:i:s');

            $stmt->bind_param(
                'sssss', 
                $to, 
                $subject, 
                $body, 
                $status, 
                $scheduled_at
            );

            return $stmt->execute();
        } catch (Exception $e) {
            $this->logger->log(
                "Email queue error: " . $e->getMessage(), 
                'error', 
                'email'
            );
            return false;
        }
    }

    /**
     * Process queued emails
     */
    public function processEmailQueue() {
        try {
            // Fetch pending emails
            $stmt = $this->db->prepare("
                SELECT id, recipient, subject, body 
                FROM email_queue 
                WHERE status = 'pending' AND scheduled_at <= NOW()
                LIMIT 50
            ");
            $stmt->execute();
            $result = $stmt->get_result();

            while ($email = $result->fetch_assoc()) {
                // Attempt to send email
                $sent = $this->send(
                    $email['recipient'], 
                    $email['subject'], 
                    $email['body']
                );

                // Update email status
                $status = $sent ? 'sent' : 'failed';
                $update_stmt = $this->db->prepare("
                    UPDATE email_queue 
                    SET status = ?, sent_at = NOW() 
                    WHERE id = ?
                ");
                $update_stmt->bind_param('si', $status, $email['id']);
                $update_stmt->execute();
            }
        } catch (Exception $e) {
            $this->logger->log(
                "Email queue processing error: " . $e->getMessage(), 
                'error', 
                'email'
            );
        }
    }
}

// Helper function for dependency injection
function getEmailService() {
    $container = container(); // Assuming dependency container is loaded
    
    // Lazy load dependencies
    $logger = $container->resolve('logger');
    $db = $container->resolve('db_connection');
    
    return new EmailService($logger, $db);
}

return getEmailService();
