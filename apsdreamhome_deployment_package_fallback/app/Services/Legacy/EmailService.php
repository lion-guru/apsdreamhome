<?php

namespace App\Services\Legacy;
// Advanced Email Service Manager

require_once __DIR__ . '/../app/core/Database.php';

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

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
        'encryption' => 'tls' // Use string for default, or PHPMailer::ENCRYPTION_STARTTLS if class exists
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

    public function __construct($logger = null, $db = null) {
        $this->logger = $logger ?? new class { 
            public function log($msg, $level = 'info', $channel = 'app') { 
                error_log("[$level][$channel] $msg"); 
            } 
        };
        
        // Initialize DB using provided connection or singleton fallback
        $this->db = $db ?: \App\Core\App::database();

        // Set encryption default properly if PHPMailer is loaded
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $this->config['encryption'] = PHPMailer::ENCRYPTION_STARTTLS;
        }

        // Load email configuration from environment or database
        $this->loadConfiguration();
    }

    /**
     * Send inquiry notification email
     */
    public function sendInquiryNotification($data) {
        $subject = "New Property Inquiry: " . ($data['property_title'] ?? 'General');
        $body = "
            <h3>New Property Inquiry</h3>
            <p><strong>Name:</strong> {$data['name']}</p>
            <p><strong>Email:</strong> {$data['email']}</p>
            <p><strong>Phone:</strong> {$data['phone']}</p>
            <p><strong>Property:</strong> {$data['property_title']} (ID: {$data['property_id']})</p>
            <p><strong>Message:</strong><br>{$data['message']}</p>
            <p><strong>Date:</strong> {$data['inquiry_date']}</p>
        ";
        
        return [
            'success' => $this->send($this->config['from_email'], $subject, $body),
            'error' => ''
        ];
    }

    /**
     * Send payment confirmation email
     */
    public function sendPaymentConfirmation($email, $order) {
        $subject = "Payment Confirmation: Order #" . $order['order_id'];
        $body = "
            <h3>Payment Successful</h3>
            <p>Dear {$order['user_name']},</p>
            <p>Thank you for your payment. Your order has been successfully processed.</p>
            <p><strong>Order ID:</strong> {$order['order_id']}</p>
            <p><strong>Property:</strong> {$order['property_title']}</p>
            <p><strong>Amount Paid:</strong> {$order['amount']} {$order['currency']}</p>
            <p><strong>Transaction ID:</strong> {$order['transaction_id']}</p>
            <p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>
            <br>
            <p>Best regards,<br>APS Dream Homes Team</p>
        ";
        
        return [
            'success' => $this->send($email, $subject, $body),
            'error' => ''
        ];
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($email, $name, $reset_link) {
        $subject = "Password Reset Request - APS Dream Homes";
        $body = "
            <h3>Password Reset Request</h3>
            <p>Dear {$name},</p>
            <p>We received a request to reset your password. Click the link below to set a new password:</p>
            <p><a href='{$reset_link}' style='display:inline-block; padding:10px 20px; background-color:#667eea; color:white; text-decoration:none; border-radius:5px;'>Reset Password</a></p>
            <p>If you didn't request this, you can safely ignore this email.</p>
            <p>The link will expire in 1 hour.</p>
            <br>
            <p>Best regards,<br>APS Dream Homes Team</p>
        ";
        
        return [
            'success' => $this->send($email, $subject, $body),
            'error' => ''
        ];
    }

    /**
     * Send welcome email to user
     */
    public function sendWelcomeEmail($email, $name) {
        return $this->sendTemplatedEmail($email, 'welcome', [
            'name' => $name,
            'company_name' => $this->config['from_name']
        ]);
    }

    /**
     * Send admin notification
     */
    public function sendAdminNotification($admin_email, $template, $data) {
        $subject = "Admin Notification: " . ($data['type'] ?? 'System Alert');
        $body = "
            <h3>System Notification</h3>
            <p><strong>Type:</strong> {$data['type']}</p>
            <p><strong>Details:</strong> {$data['details']}</p>
        ";
        
        return $this->send($admin_email, $subject, $body);
    }

    /**
     * Send inquiry confirmation/response
     */
    public function sendInquiryConfirmation($email, $property_title, $inquiry, $response_message) {
        $subject = "Re: Inquiry for " . $property_title;
        $body = "
            <h3>Inquiry Response</h3>
            <p>Dear Customer,</p>
            <p>Thank you for your inquiry regarding <strong>{$property_title}</strong>.</p>
            <p><strong>Response:</strong><br>{$response_message}</p>
            <p>Best regards,<br>{$this->config['from_name']}</p>
        ";
        
        return [
            'success' => $this->send($email, $subject, $body),
            'error' => ''
        ];
    }

    /**
     * Test email configuration
     */
    public function testEmailConfiguration() {
        $subject = "Test Email Configuration";
        $body = "This is a test email to verify your email configuration is working correctly.";
        return $this->send($this->config['from_email'], $subject, $body);
    }

    /**
     * Send lead welcome email
     */
    public function sendLeadWelcomeEmail($leadId) {
        try {
            $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$leadId]);

            if (!$lead) return false;

            return $this->sendTemplatedEmail($lead['email'], 'welcome', [
                'lead_name' => $lead['name'] ?? (($lead['first_name'] ?? '') . ' ' . ($lead['last_name'] ?? '')),
                'company_name' => $this->config['from_name']
            ]);
        } catch (\Exception $e) {
            $this->logger->log("Error sending lead welcome email: " . $e->getMessage(), 'error', 'email');
            return false;
        }
    }

    /**
     * Send templated email
     */
    public function sendTemplatedEmail($to, $template, $data = []) {
        if (!isset($this->templates[$template])) {
            return false;
        }

        $templateInfo = $this->templates[$template];
        $subject = $templateInfo['subject'];
        $bodyFile = dirname(__DIR__) . '/' . $templateInfo['body'];

        if (!file_exists($bodyFile)) {
            // Fallback body if file doesn't exist
            $body = "Hello,\n\nThis is a notification from " . $this->config['from_name'];
        } else {
            $body = file_get_contents($bodyFile);
            foreach ($data as $key => $value) {
                $body = str_replace('{{' . $key . '}}', $value, $body);
                $body = str_replace('{{ ' . $key . ' }}', $value, $body);
            }
        }

        return $this->send($to, $subject, $body);
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
            $results = $this->db->fetchAll("SELECT `key`, `value` FROM email_config");
            foreach ($results as $row) {
                if (isset($this->config[$row['key']])) {
                    $this->config[$row['key']] = $row['value'];
                }
            }
        } catch (\Exception $e) {
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
            $status = 'pending';
            $scheduled_at = $options['scheduled_at'] ?? date('Y-m-d H:i:s');

            return $this->db->execute("
                INSERT INTO email_queue 
                (recipient, subject, body, status, scheduled_at, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ", [$to, $subject, $body, $status, $scheduled_at]);
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
            $emails = $this->db->fetchAll("
                SELECT id, recipient, subject, body 
                FROM email_queue 
                WHERE status = 'pending' AND scheduled_at <= NOW()
                LIMIT 50
            ");

            foreach ($emails as $email) {
                // Attempt to send email
                $sent = $this->send(
                    $email['recipient'], 
                    $email['subject'], 
                    $email['body']
                );

                // Update email status
                $status = $sent ? 'sent' : 'failed';
                $this->db->execute("
                    UPDATE email_queue 
                    SET status = ?, sent_at = NOW() 
                    WHERE id = ?
                ", [$status, $email['id']]);
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
    // If a global container function exists, use it
    if (function_exists('container')) {
        try {
            $container = container();
            $logger = $container->resolve('logger');
            $db = $container->resolve('db_connection');
            return new EmailService($logger, $db);
        } catch (\Exception $e) {
            // Fallback to default instantiation if resolution fails
        }
    }
    
    // Default instantiation with internal defaults
    return new EmailService();
}

return getEmailService();
