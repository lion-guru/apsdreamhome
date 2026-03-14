<?php

namespace App\Core;

/**
 * Email Notification System
 * Handles all email communications for APS Dream Home
 */
class EmailManager
{
    private static $instance = null;
    private $config = [];
    private $mailer = null;
    private $templates = [];
    private $adminEmails = [];

    private function __construct()
    {
        $this->loadConfig();
        $this->initializeMailer();
        $this->loadTemplates();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Send email using PHPMailer
     */
    public function sendEmail($to, $template, $data = [], $attachments = [])
    {
        try {
            // Reset recipients for each send
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);

            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $this->renderSubject($template, $data);
            $this->mailer->Body = $this->renderBody($template, $data);
            $this->mailer->AltBody = strip_tags($this->mailer->Body);

            // Attachments
            foreach ($attachments as $attachment) {
                if (isset($attachment['path']) && file_exists($attachment['path'])) {
                    $this->mailer->addAttachment($attachment['path'], $attachment['name'] ?? '');
                }
            }

            $this->mailer->send();

            // Log successful email
            $this->logEmail($to, $template, 'sent', $data);

            return [
                'success' => true,
                'message_id' => $this->mailer->getLastMessageID()
            ];
        } catch (\Exception $e) {
            // Log failed email
            $this->logEmail($to, $template, 'failed', $data, $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function renderSubject($template, $data) {
        $subjects = [
            'welcome' => 'Welcome to APS Dream Home!',
            'property_inquiry' => 'New Property Inquiry - {property_title}',
            'inquiry_confirmation' => 'We received your inquiry',
            'payment_success' => 'Payment Successful - Order #{order_id}',
            'password_reset' => 'Password Reset Request',
            'admin_notification' => 'Admin Notification: {type}',
            'test_email' => 'Email Test'
        ];

        $subject = $subjects[$template] ?? 'Notification from APS Dream Home';
        foreach ($data as $key => $value) {
            $subject = str_replace('{' . $key . '}', (string)$value, $subject);
        }
        return $subject;
    }

    private function renderBody($template, $data) {
        if (isset($this->templates[$template])) {
            $content = $this->templates[$template];
            foreach ($data as $key => $value) {
                $content = str_replace('{{' . $key . '}}', (string)$value, $content);
            }
            return $content;
        }

        // Fallback for demo/missing templates
        return "<h1>" . ucfirst($template) . "</h1><p>Data: " . json_encode($data) . "</p>";
    }

    private function logEmail($to, $template, $status, $data = [], $error = null)
    {
        try {
            global $pdo;

            $log_data = [
                'recipient' => $to,
                'template' => $template,
                'status' => $status,
                'data' => json_encode($data),
                'error_message' => $error,
                'ip_address' => 'system',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $stmt = $pdo->prepare("
                INSERT INTO email_logs (recipient, template, status, data, error_message, ip_address, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $log_data['recipient'],
                $log_data['template'],
                $log_data['status'],
                $log_data['data'],
                $log_data['error_message'],
                $log_data['ip_address'],
                $log_data['created_at']
            ]);
        } catch (\Exception $e) {
            error_log('Email logging failed: ' . $e->getMessage());
        }
    }

    public function sendWelcomeEmail($user_email, $user_name)
    {
        return $this->sendEmail($user_email, 'welcome', [
            'user_name' => $user_name,
            'login_url' => BASE_URL . 'login'
        ]);
    }

    public function sendPropertyInquiryEmail($property, $inquiry_data)
    {
        $admin_result = $this->sendEmail(
            $this->config['from_email'],
            'property_inquiry',
            [
                'property_title' => $property['title'],
                'inquiry_name' => $inquiry_data['name'],
                'inquiry_email' => $inquiry_data['email'],
                'inquiry_phone' => $inquiry_data['phone'],
                'inquiry_message' => $inquiry_data['message']
            ]
        );

        $user_result = $this->sendEmail(
            $inquiry_data['email'],
            'inquiry_confirmation',
            [
                'user_name' => $inquiry_data['name'],
                'property_title' => $property['title']
            ]
        );

        return [
            'admin_notification' => $admin_result,
            'user_confirmation' => $user_result
        ];
    }

    public function sendPaymentConfirmationEmail($payment_data, $user_email)
    {
        return $this->sendEmail($user_email, 'payment_success', [
            'order_id' => $payment_data['order_id'],
            'amount' => $payment_data['amount'],
            'currency' => $payment_data['currency'],
            'payment_method' => $payment_data['method'],
            'transaction_id' => $payment_data['transaction_id'],
            'user_name' => $payment_data['user_name']
        ]);
    }

    public function sendPasswordResetEmail($user_email, $user_name, $reset_token)
    {
        return $this->sendEmail($user_email, 'password_reset', [
            'user_name' => $user_name,
            'reset_url' => BASE_URL . 'reset-password/' . $reset_token
        ]);
    }

    public function getEmailStats($days = 30)
    {
        try {
            global $pdo;

            $stmt = $pdo->prepare("
                SELECT
                    status,
                    COUNT(*) as count,
                    DATE(created_at) as date
                FROM email_logs
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY status, DATE(created_at)
                ORDER BY date DESC, status
            ");
            $stmt->execute([$days]);
            $stats = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $totals = ['sent' => 0, 'failed' => 0];
            foreach ($stats as $stat) {
                if (isset($totals[$stat['status']])) {
                    $totals[$stat['status']] += $stat['count'];
                }
            }

            return [
                'total_sent' => $totals['sent'],
                'total_failed' => $totals['failed'],
                'success_rate' => $totals['sent'] + $totals['failed'] > 0 ?
                    round(($totals['sent'] / ($totals['sent'] + $totals['failed'])) * 100, 2) : 0,
                'daily_stats' => $stats
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'total_sent' => 0,
                'total_failed' => 0,
                'success_rate' => 0
            ];
        }
    }

    public function testEmailConfiguration()
    {
        return $this->sendEmail(
            $this->config['from_email'],
            'test_email',
            ['test_time' => date('Y-m-d H:i:s')]
        );
    }

    private function loadConfig() {
        $this->config = [
            'host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
            'port' => getenv('SMTP_PORT') ?: 587,
            'username' => getenv('SMTP_USERNAME'),
            'password' => getenv('SMTP_PASSWORD'),
            'from_email' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@apsdreamhome.com',
            'from_name' => getenv('MAIL_FROM_NAME') ?: 'APS Dream Homes',
            'encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls'
        ];

        $adminEmailsStr = getenv('ADMIN_EMAILS');
        if ($adminEmailsStr) {
            $this->adminEmails = array_map('trim', explode(',', $adminEmailsStr));
        }
    }

    private function initializeMailer() {
        $this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $this->mailer->isSMTP();
            $this->mailer->Host       = $this->config['host'];
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = $this->config['username'];
            $this->mailer->Password   = $this->config['password'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port       = $this->config['port'];
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
        } catch (\Exception $e) {
            error_log("Mailer initialization failed: " . $e->getMessage());
        }
    }

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

    public function sendCriticalAlert($subject, $details) {
        $sentCount = 0;
        foreach ($this->adminEmails as $email) {
            if ($this->sendEmail($email, 'admin_notification', ['type' => $subject, 'details' => $details])) {
                $sentCount++;
            }
        }
        return $sentCount;
    }
}

/**
 * Email template functions
 */
if (!function_exists('send_welcome_email')) {
    function send_welcome_email($user_email, $user_name) {
        return EmailManager::getInstance()->sendWelcomeEmail($user_email, $user_name);
    }
}

if (!function_exists('send_property_inquiry_email')) {
    function send_property_inquiry_email($property, $inquiry_data) {
        return EmailManager::getInstance()->sendPropertyInquiryEmail($property, $inquiry_data);
    }
}

if (!function_exists('send_payment_confirmation_email')) {
    function send_payment_confirmation_email($payment_data, $user_email) {
        return EmailManager::getInstance()->sendPaymentConfirmationEmail($payment_data, $user_email);
    }
}

if (!function_exists('send_password_reset_email')) {
    function send_password_reset_email($user_email, $user_name, $reset_token) {
        return EmailManager::getInstance()->sendPasswordResetEmail($user_email, $user_name, $reset_token);
    }
}