<?php
/**
 * Email Notification Manager
 * Handles sending email notifications for critical events
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../security_logger.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class EmailManager {
    private $mailer;
    private $logger;
    private $config;
    private $templates = [];
    private $adminEmails = [];

    public function __construct($security_logger = null) {
        $this->logger = $security_logger ?? new SecurityLogger();
        $this->loadConfig();
        $this->initializeMailer();
        $this->loadTemplates();
    }

    /**
     * Load email configuration
     */
    private function loadConfig() {
        // Load from environment variables
        $this->config = [
            'host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
            'port' => getenv('SMTP_PORT') ?: 587,
            'username' => getenv('SMTP_USERNAME'),
            'password' => getenv('SMTP_PASSWORD'),
            'from_email' => getenv('MAIL_FROM_ADDRESS'),
            'from_name' => getenv('MAIL_FROM_NAME') ?: 'APS Dream Homes',
            'encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls'
        ];

        // Load admin emails
        $adminEmailsStr = getenv('ADMIN_EMAILS');
        if ($adminEmailsStr) {
            $this->adminEmails = array_map('trim', explode(',', $adminEmailsStr));
        }
    }

    /**
     * Initialize PHPMailer
     */
    private function initializeMailer() {
        $this->mailer = new PHPMailer(true);

        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port = $this->config['port'];
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
            
            // Common settings
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
            
        } catch (Exception $e) {
            $this->logger->error('Failed to initialize mailer', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Load email templates
     */
    private function loadTemplates() {
        $this->templates = [
            'security_alert' => [
                'subject' => '[Security Alert] {title}',
                'template' => 'security_alert.html'
            ],
            'api_key_expired' => [
                'subject' => '[API Key Alert] API Key Expired',
                'template' => 'api_key_expired.html'
            ],
            'rate_limit_exceeded' => [
                'subject' => '[Rate Limit Alert] Rate Limit Exceeded',
                'template' => 'rate_limit_exceeded.html'
            ],
            'suspicious_activity' => [
                'subject' => '[Suspicious Activity] {title}',
                'template' => 'suspicious_activity.html'
            ],
            'backup_status' => [
                'subject' => '[Backup Status] {title}',
                'template' => 'backup_status.html'
            ]
        ];
    }

    /**
     * Send security alert to admins
     */
    public function sendSecurityAlert($title, $details, $priority = 'medium') {
        $template = $this->loadEmailTemplate('security_alert');
        $subject = str_replace('{title}', $title, $this->templates['security_alert']['subject']);

        $data = [
            'title' => $title,
            'details' => $details,
            'priority' => $priority,
            'timestamp' => date('Y-m-d H:i:s'),
            'server' => $_SERVER['SERVER_NAME']
        ];

        return $this->sendToAdmins($subject, $template, $data);
    }

    /**
     * Send API key expiration notice
     */
    public function sendApiKeyExpiredAlert($keyData, $userEmail) {
        $template = $this->loadEmailTemplate('api_key_expired');
        $subject = $this->templates['api_key_expired']['subject'];

        $data = [
            'key_name' => $keyData['name'],
            'expiry_date' => $keyData['expires_at'],
            'created_date' => $keyData['created_at'],
            'last_used' => $keyData['last_used_at']
        ];

        return $this->send($userEmail, $subject, $template, $data);
    }

    /**
     * Send rate limit exceeded alert
     */
    public function sendRateLimitAlert($keyData, $userEmail) {
        $template = $this->loadEmailTemplate('rate_limit_exceeded');
        $subject = $this->templates['rate_limit_exceeded']['subject'];

        $data = [
            'key_name' => $keyData['name'],
            'rate_limit' => $keyData['rate_limit'],
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Send to both user and admins
        $this->send($userEmail, $subject, $template, $data);
        return $this->sendToAdmins($subject, $template, $data);
    }

    /**
     * Send suspicious activity alert
     */
    public function sendSuspiciousActivityAlert($activity, $details) {
        $template = $this->loadEmailTemplate('suspicious_activity');
        $subject = str_replace('{title}', $activity, $this->templates['suspicious_activity']['subject']);

        $data = [
            'activity' => $activity,
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ];

        return $this->sendToAdmins($subject, $template, $data);
    }

    /**
     * Send backup status notification
     */
    public function sendBackupStatus($status, $details) {
        $template = $this->loadEmailTemplate('backup_status');
        $title = $status === 'success' ? 'Backup Completed Successfully' : 'Backup Failed';
        $subject = str_replace('{title}', $title, $this->templates['backup_status']['subject']);

        $data = [
            'status' => $status,
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s'),
            'server' => $_SERVER['SERVER_NAME']
        ];

        return $this->sendToAdmins($subject, $template, $data);
    }

    /**
     * Send email to all admins
     */
    private function sendToAdmins($subject, $template, $data) {
        $success = true;
        foreach ($this->adminEmails as $email) {
            if (!$this->send($email, $subject, $template, $data)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Send a single email
     */
    private function send($to, $subject, $template, $data) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $this->renderTemplate($template, $data);
            $this->mailer->AltBody = $this->createTextVersion($data);

            $result = $this->mailer->send();
            
            $this->logger->info('Email sent successfully', [
                'to' => $to,
                'subject' => $subject
            ]);

            return $result;

        } catch (Exception $e) {
            $this->logger->error('Failed to send email', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Load email template file
     */
    private function loadEmailTemplate($name) {
        if (!isset($this->templates[$name])) {
            throw new Exception("Email template not found: {$name}");
        }

        $templateFile = __DIR__ . '/templates/' . $this->templates[$name]['template'];
        if (!file_exists($templateFile)) {
            throw new Exception("Template file not found: {$templateFile}");
        }

        return file_get_contents($templateFile);
    }

    /**
     * Render template with data
     */
    private function renderTemplate($template, $data) {
        foreach ($data as $key => $value) {
            $template = str_replace("{{" . $key . "}}", htmlspecialchars($value), $template);
        }
        return $template;
    }

    /**
     * Create text version of email
     */
    private function createTextVersion($data) {
        $text = "";
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $text .= ucfirst($key) . ": " . $value . "\n";
            }
        }
        return $text;
    }
}

// Create global email manager instance
$emailManager = new EmailManager($securityLogger ?? null);
