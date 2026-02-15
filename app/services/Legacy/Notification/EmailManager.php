<?php

namespace App\Services\Legacy\Notification;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Services\Legacy\SecurityLogger;

/**
 * Email Notification Manager
 * Handles sending email notifications for critical events
 */
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
     * Initialize PHPMailer instance
     */
    private function initializeMailer() {
        $this->mailer = new PHPMailer(true);

        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host       = $this->config['host'];
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = $this->config['username'];
            $this->mailer->Password   = $this->config['password'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port       = $this->config['port'];

            // Recipients
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
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
        $templatePath = __DIR__ . '/../../../../../resources/views/notifications/email';
        if (is_dir($templatePath)) {
            $files = glob($templatePath . '/*.html');
            foreach ($files as $file) {
                $name = basename($file, '.html');
                $this->templates[$name] = file_get_contents($file);
            }
        }
    }

    /**
     * Send email notification
     */
    public function send($to, $subject, $body, $altBody = '') {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            $this->mailer->AltBody = $altBody ?: strip_tags($body);

            return $this->mailer->send();
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
     * Send critical alert to admins
     */
    public function sendCriticalAlert($subject, $details) {
        $sentCount = 0;
        foreach ($this->adminEmails as $email) {
            if ($this->send($email, "[CRITICAL] $subject", $details)) {
                $sentCount++;
            }
        }
        return $sentCount > 0;
    }
}
