<?php

namespace App\Core;

/**
 * Email Notification System
 * Handles all email communications for APS Dream Home
 */
class EmailManager
{
    private $smtp_config = [];
    private $templates = [];

    public function __construct()
    {
        $this->smtp_config = [
            'host' => config('mail.host', 'smtp.gmail.com'),
            'port' => config('mail.port', 587),
            'username' => config('mail.username', ''),
            'password' => config('mail.password', ''),
            'encryption' => config('mail.encryption', 'tls'),
            'from_email' => config('mail.from.email', 'noreply@apsdreamhome.com'),
            'from_name' => config('mail.from.name', 'APS Dream Home')
        ];

        $this->loadEmailTemplates();
    }

    /**
     * Load email templates
     */
    private function loadEmailTemplates()
    {
        $this->templates = [
            'welcome' => [
                'subject' => 'Welcome to APS Dream Home!',
                'template' => 'emails/welcome.php'
            ],
            'property_inquiry' => [
                'subject' => 'New Property Inquiry - {property_title}',
                'template' => 'emails/property_inquiry.php'
            ],
            'property_booked' => [
                'subject' => 'Property Booking Confirmation',
                'template' => 'emails/property_booked.php'
            ],
            'payment_success' => [
                'subject' => 'Payment Successful - Order #{order_id}',
                'template' => 'emails/payment_success.php'
            ],
            'payment_failed' => [
                'subject' => 'Payment Failed - Order #{order_id}',
                'template' => 'emails/payment_failed.php'
            ],
            'admin_notification' => [
                'subject' => 'New {type} - Action Required',
                'template' => 'emails/admin_notification.php'
            ],
            'password_reset' => [
                'subject' => 'Password Reset Request',
                'template' => 'emails/password_reset.php'
            ]
        ];
    }

    /**
     * Send email using PHPMailer
     */
    public function sendEmail($to, $template, $data = [], $attachments = [])
    {
        try {
            require_once __DIR__ . '/../../vendor/autoload.php';

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtp_config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_config['username'];
            $mail->Password = $this->smtp_config['password'];
            $mail->SMTPSecure = $this->smtp_config['encryption'];
            $mail->Port = $this->smtp_config['port'];

            // Recipients
            $mail->setFrom($this->smtp_config['from_email'], $this->smtp_config['from_name']);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $this->processTemplate($template, $data, 'subject');
            $mail->Body = $this->processTemplate($template, $data, 'body');
            $mail->AltBody = strip_tags($this->processTemplate($template, $data, 'body'));

            // Attachments
            foreach ($attachments as $attachment) {
                if (isset($attachment['path']) && file_exists($attachment['path'])) {
                    $mail->addAttachment($attachment['path'], $attachment['name'] ?? '');
                }
            }

            $mail->send();

            // Log successful email
            $this->logEmail($to, $template, 'sent', $data);

            return [
                'success' => true,
                'message_id' => $mail->getLastMessageID()
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

    /**
     * Process email template with data
     */
    private function processTemplate($template_key, $data, $type = 'body')
    {
        if (!isset($this->templates[$template_key])) {
            throw new \Exception('Email template not found: ' . $template_key);
        }

        $template_info = $this->templates[$template_key];
        $template_path = __DIR__ . '/../views/' . $template_info['template'];

        if (!file_exists($template_path)) {
            throw new \Exception('Email template file not found: ' . $template_path);
        }

        // Extract data for template
        extract($data);

        // Capture output
        ob_start();
        include $template_path;
        $content = ob_get_clean();

        return $content;
    }

    /**
     * Log email activity
     */
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
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'system',
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

    /**
     * Send welcome email to new users
     */
    public function sendWelcomeEmail($user_email, $user_name)
    {
        return $this->sendEmail($user_email, 'welcome', [
            'user_name' => $user_name,
            'login_url' => BASE_URL . 'login',
            'support_email' => $this->smtp_config['from_email']
        ]);
    }

    /**
     * Send property inquiry notification
     */
    public function sendPropertyInquiryEmail($property, $inquiry_data)
    {
        // Send to admin
        $admin_result = $this->sendEmail(
            $this->smtp_config['from_email'],
            'property_inquiry',
            [
                'property_title' => $property['title'],
                'inquiry_name' => $inquiry_data['name'],
                'inquiry_email' => $inquiry_data['email'],
                'inquiry_phone' => $inquiry_data['phone'],
                'inquiry_message' => $inquiry_data['message'],
                'property_url' => BASE_URL . 'property/' . $property['id'],
                'admin_panel_url' => BASE_URL . 'admin/inquiries'
            ]
        );

        // Send confirmation to user
        $user_result = $this->sendEmail(
            $inquiry_data['email'],
            'inquiry_confirmation',
            [
                'user_name' => $inquiry_data['name'],
                'property_title' => $property['title'],
                'property_address' => $property['address'],
                'support_phone' => '+91-9876543210',
                'support_email' => $this->smtp_config['from_email']
            ]
        );

        return [
            'admin_notification' => $admin_result,
            'user_confirmation' => $user_result
        ];
    }

    /**
     * Send payment confirmation email
     */
    public function sendPaymentConfirmationEmail($payment_data, $user_email)
    {
        return $this->sendEmail($user_email, 'payment_success', [
            'order_id' => $payment_data['order_id'],
            'amount' => $payment_data['amount'],
            'currency' => $payment_data['currency'],
            'payment_method' => $payment_data['method'],
            'transaction_id' => $payment_data['transaction_id'],
            'payment_date' => date('d M Y, h:i A'),
            'user_name' => $payment_data['user_name'],
            'download_receipt_url' => BASE_URL . 'payment/receipt/' . $payment_data['order_id']
        ]);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($user_email, $user_name, $reset_token)
    {
        return $this->sendEmail($user_email, 'password_reset', [
            'user_name' => $user_name,
            'reset_url' => BASE_URL . 'reset-password?token=' . $reset_token,
            'expiry_hours' => 24,
            'support_email' => $this->smtp_config['from_email']
        ]);
    }

    /**
     * Send admin notification
     */
    public function sendAdminNotification($type, $data = [])
    {
        return $this->sendEmail($this->smtp_config['from_email'], 'admin_notification', [
            'type' => $type,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s'),
            'admin_panel_url' => BASE_URL . 'admin'
        ]);
    }

    /**
     * Get email statistics
     */
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

            // Calculate totals
            $totals = ['sent' => 0, 'failed' => 0];
            foreach ($stats as $stat) {
                $totals[$stat['status']] += $stat['count'];
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

    /**
     * Test email configuration
     */
    public function testEmailConfiguration()
    {
        try {
            $test_result = $this->sendEmail(
                $this->smtp_config['from_email'],
                'test_email',
                [
                    'test_time' => date('Y-m-d H:i:s'),
                    'server_info' => $_SERVER['SERVER_NAME'] ?? 'Unknown'
                ]
            );

            return [
                'success' => $test_result['success'],
                'message' => $test_result['success'] ?
                    'Email configuration is working correctly' :
                    'Email configuration test failed: ' . $test_result['error']
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Email test failed: ' . $e->getMessage()
            ];
        }
    }
}

/**
 * Email template functions
 */
function send_welcome_email($user_email, $user_name)
{
    return EmailManager::getInstance()->sendWelcomeEmail($user_email, $user_name);
}

function send_property_inquiry_email($property, $inquiry_data)
{
    return EmailManager::getInstance()->sendPropertyInquiryEmail($property, $inquiry_data);
}

function send_payment_confirmation_email($payment_data, $user_email)
{
    return EmailManager::getInstance()->sendPaymentConfirmationEmail($payment_data, $user_email);
}

function send_password_reset_email($user_email, $user_name, $reset_token)
{
    return EmailManager::getInstance()->sendPasswordResetEmail($user_email, $user_name, $reset_token);
}

?>
