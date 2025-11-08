<?php
/**
 * APS Dream Home - Email System
 * Handles all email communications using PHPMailer and Gmail SMTP
 */

// Prevent direct access
if (!defined('BASE_URL')) {
    exit('Direct access not allowed');
}

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailSystem {
    private $mail;
    private $config;
    private $whatsapp_enabled;

    public function __construct() {
        global $config;
        $this->config = $config['email'] ?? [];
        $this->whatsapp_enabled = $config['whatsapp']['enabled'] ?? false;

        if (!$this->config['enabled']) {
            throw new Exception('Email system is disabled');
        }

        $this->mail = new PHPMailer(true);

        $this->initializeMailer();
    }

    private function initializeMailer() {
        // Server settings
        $this->mail->SMTPDebug = SMTP::DEBUG_OFF; // Disable debug output
        $this->mail->isSMTP();
        $this->mail->Host = $this->config['smtp_host'] ?? 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $this->config['smtp_username'] ?? 'apsdreamhomes44@gmail.com';
        $this->mail->Password = $this->config['smtp_password'] ?? 'Aps@1601';
        $this->mail->SMTPSecure = $this->config['smtp_encryption'] ?? 'tls';
        $this->mail->Port = $this->config['smtp_port'] ?? 587;

        // Recipients
        $this->mail->setFrom(
            $this->config['from_email'] ?? 'apsdreamhomes44@gmail.com',
            $this->config['from_name'] ?? 'APS Dream Home'
        );

        // Default reply-to
        $this->mail->addReplyTo(
            $this->config['reply_to'] ?? 'apsdreamhomes44@gmail.com',
            $this->config['from_name'] ?? 'APS Dream Home'
        );
    }

    /**
     * Send welcome email to new users
     */
    public function sendWelcomeEmail($user_email, $user_name, $user_type = 'customer') {
        $subject = "Welcome to APS Dream Home - Your Real Estate Journey Begins!";

        $message = "
        <html>
        <head>
            <title>Welcome to APS Dream Home</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .button { background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
                .footer { background: #333; color: white; padding: 20px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>🏠 Welcome to APS Dream Home!</h1>
                <p>Your trusted partner in real estate</p>
            </div>
            <div class='content'>
                <h2>Hello {$user_name}!</h2>
                <p>Welcome to APS Dream Home! We're excited to have you join our community of property seekers and real estate enthusiasts.</p>

                <p>As a {$user_type}, you now have access to:</p>
                <ul>
                    <li>🔍 Advanced property search and filtering</li>
                    <li>🏠 Comprehensive property listings</li>
                    <li>👥 Expert real estate consultation</li>
                    <li>💼 Personalized property recommendations</li>
                    <li>📱 Mobile-responsive platform access</li>
                </ul>

                <p><strong>What's Next?</strong></p>
                <p>Start exploring our property listings and find your dream home today!</p>

                <a href='" . BASE_URL . "properties.php' class='button'>Browse Properties</a>
            </div>
            <div class='footer'>
                <p>Contact us: <a href='mailto:apsdreamhomes44@gmail.com' style='color: #667eea;'>apsdreamhomes44@gmail.com</a></p>
                <p>APS Dream Home - Your Real Estate Partner</p>
            </div>
        </body>
        </html>";

        return $this->sendEmail($user_email, $subject, $message);
    }

    /**
     * Send property inquiry notification
     */
    public function sendPropertyInquiryNotification($property_data, $customer_data) {
        $subject = "New Property Inquiry - {$property_data['title']}";

        $message = "
        <html>
        <head>
            <title>New Property Inquiry</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: #28a745; color: white; padding: 15px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .property-card { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>🏠 New Property Inquiry Received</h2>
            </div>
            <div class='content'>
                <div class='property-card'>
                    <h3>Property Details:</h3>
                    <p><strong>Title:</strong> {$property_data['title']}</p>
                    <p><strong>Location:</strong> {$property_data['location']}</p>
                    <p><strong>Price:</strong> ₹" . number_format($property_data['price']) . "</p>
                    <p><strong>Type:</strong> {$property_data['type']}</p>
                </div>

                <div class='property-card'>
                    <h3>Customer Details:</h3>
                    <p><strong>Name:</strong> {$customer_data['name']}</p>
                    <p><strong>Email:</strong> {$customer_data['email']}</p>
                    <p><strong>Phone:</strong> {$customer_data['phone']}</p>
                    <p><strong>Message:</strong> {$customer_data['message']}</p>
                </div>
            </div>
            <div class='footer'>
                <p>APS Dream Home Admin Panel</p>
            </div>
        </body>
        </html>";

        return $this->sendEmail($this->config['admin_email'], $subject, $message);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($user_email, $user_name, $reset_token) {
        $reset_link = BASE_URL . "reset_password.php?token=" . urlencode($reset_token);

        $subject = "Password Reset - APS Dream Home";

        $message = "
        <html>
        <head>
            <title>Password Reset</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .button { background: #dc3545; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; }
                .footer { background: #333; color: white; padding: 20px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>🔐 Password Reset Request</h1>
            </div>
            <div class='content'>
                <h2>Hello {$user_name}!</h2>
                <p>You have requested to reset your password for your APS Dream Home account.</p>

                <p>Click the button below to reset your password:</p>

                <a href='{$reset_link}' class='button'>Reset Password</a>

                <p><strong>Security Note:</strong> This link will expire in 24 hours for your security.</p>

                <p>If you didn't request this password reset, please ignore this email.</p>
            </div>
            <div class='footer'>
                <p>If you have any questions, contact us: <a href='mailto:apsdreamhomes44@gmail.com'>apsdreamhomes44@gmail.com</a></p>
                <p>APS Dream Home - Secure & Reliable</p>
            </div>
        </body>
        </html>";

        return $this->sendEmail($user_email, $subject, $message);
    }

    /**
     * Send booking confirmation email
     */
    public function sendBookingConfirmationEmail($booking_data) {
        $subject = "Booking Confirmed - {$booking_data['property_title']}";

        $message = "
        <html>
        <head>
            <title>Booking Confirmation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f8f9fa; }
                .success-box { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .footer { background: #333; color: white; padding: 20px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>✅ Booking Confirmed!</h1>
            </div>
            <div class='content'>
                <div class='success-box'>
                    <h3>Your booking has been successfully confirmed!</h3>
                </div>

                <h3>Booking Details:</h3>
                <p><strong>Property:</strong> {$booking_data['property_title']}</p>
                <p><strong>Location:</strong> {$booking_data['property_location']}</p>
                <p><strong>Booking Date:</strong> {$booking_data['booking_date']}</p>
                <p><strong>Booking ID:</strong> {$booking_data['booking_id']}</p>
                <p><strong>Amount:</strong> ₹" . number_format($booking_data['amount']) . "</p>

                <p>Our team will contact you shortly with further details and next steps.</p>

                <p>Thank you for choosing APS Dream Home!</p>
            </div>
            <div class='footer'>
                <p>Need help? Contact us: <a href='mailto:apsdreamhomes44@gmail.com' style='color: #007bff;'>apsdreamhomes44@gmail.com</a></p>
                <p>APS Dream Home - Your Property Partner</p>
            </div>
        </body>
        </html>";

        return $this->sendEmail($booking_data['customer_email'], $subject, $message);
    }

    /**
     * Send commission notification to associates
     */
    public function sendCommissionNotification($associate_data) {
        $subject = "Commission Earned - ₹" . number_format($associate_data['commission_amount']);

        $message = "
        <html>
        <head>
            <title>Commission Notification</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: #ffc107; color: #212529; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f8f9fa; }
                .commission-box { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .footer { background: #333; color: white; padding: 20px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>💰 Commission Earned!</h1>
            </div>
            <div class='content'>
                <div class='commission-box'>
                    <h3>Congratulations {$associate_data['associate_name']}!</h3>
                    <p>You have earned a commission of <strong>₹" . number_format($associate_data['commission_amount']) . "</strong></p>
                </div>

                <h3>Commission Details:</h3>
                <p><strong>Transaction ID:</strong> {$associate_data['transaction_id']}</p>
                <p><strong>Property:</strong> {$associate_data['property_title']}</p>
                <p><strong>Customer:</strong> {$associate_data['customer_name']}</p>
                <p><strong>Date:</strong> {$associate_data['transaction_date']}</p>

                <p>Your commission will be processed within 7-10 business days.</p>

                <p>Keep up the great work!</p>
            </div>
            <div class='footer'>
                <p>APS Dream Home - Associate Portal</p>
            </div>
        </body>
        </html>";
        return $this->sendEmail($associate_data['email'], $subject, $message);
    }

    /**
     * Send general notification email
     */
    public function sendNotification($to, $subject, $body, $template = 'general') {
        return $this->sendEmail($to, $subject, $body);
    }

    /**
     * Send WhatsApp welcome message
     */
    public function sendWhatsAppWelcome($phone_number, $customer_name) {
        if (!$this->whatsapp_enabled) {
            return ['success' => false, 'error' => 'WhatsApp integration disabled'];
        }

        return sendWhatsAppWelcome($phone_number, $customer_name);
    }

    /**
     * Send WhatsApp property inquiry notification
     */
    public function sendWhatsAppPropertyInquiry($phone_number, $property_data, $customer_data) {
        if (!$this->whatsapp_enabled) {
            return ['success' => false, 'error' => 'WhatsApp integration disabled'];
        }

        return sendWhatsAppPropertyInquiry($phone_number, $property_data, $customer_data);
    }

    /**
     * Send WhatsApp booking confirmation
     */
    public function sendWhatsAppBookingConfirmation($phone_number, $booking_data) {
        if (!$this->whatsapp_enabled) {
            return ['success' => false, 'error' => 'WhatsApp integration disabled'];
        }

        return sendWhatsAppBookingConfirmation($phone_number, $booking_data);
    }

    /**
     * Send WhatsApp commission notification
     */
    public function sendWhatsAppCommissionNotification($phone_number, $commission_data) {
        if (!$this->whatsapp_enabled) {
            return ['success' => false, 'error' => 'WhatsApp integration disabled'];
        }

        return sendWhatsAppCommissionNotification($phone_number, $commission_data);
    }

    /**
     * Send WhatsApp payment reminder
     */
    public function sendWhatsAppPaymentReminder($phone_number, $payment_data) {
        if (!$this->whatsapp_enabled) {
            return ['success' => false, 'error' => 'WhatsApp integration disabled'];
        }

        return sendWhatsAppPaymentReminder($phone_number, $payment_data);
    }

    /**
     * Send WhatsApp appointment reminder
     */
    public function sendWhatsAppAppointmentReminder($phone_number, $appointment_data) {
        if (!$this->whatsapp_enabled) {
            return ['success' => false, 'error' => 'WhatsApp integration disabled'];
        }

        return sendWhatsAppAppointmentReminder($phone_number, $appointment_data);
    }

    /**
     * Send WhatsApp system alert
     */
    public function sendWhatsAppSystemAlert($phone_number, $alert_data) {
        if (!$this->whatsapp_enabled) {
            return ['success' => false, 'error' => 'WhatsApp integration disabled'];
        }

        return sendWhatsAppSystemAlert($phone_number, $alert_data);
    }

    /**
     * Send both email and WhatsApp notification
     */
    public function sendDualNotification($email_data, $whatsapp_data) {
        $email_result = $this->sendEmail($email_data['to'], $email_data['subject'], $email_data['body']);
        $whatsapp_result = $this->sendWhatsAppMessage($whatsapp_data['phone'], $whatsapp_data['message']);

        return [
            'email' => $email_result,
            'whatsapp' => $whatsapp_result,
            'both_sent' => $email_result['success'] && $whatsapp_result['success']
        ];
    }

    /**
     * Send WhatsApp message (generic method)
     */
    private function sendWhatsAppMessage($phone_number, $message) {
        try {
            $whatsapp = new WhatsAppIntegration();
            return $whatsapp->sendMessage($phone_number, $message);
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Core email sending function
     */
    private function sendEmail($to_email, $subject, $message) {
        try {
            // Clear any previous recipients
            $this->mail->clearAddresses();
            $this->mail->clearCCs();
            $this->mail->clearBCCs();

            // Set recipient
            $this->mail->addAddress($to_email);

            // BCC admin if enabled
            if (($this->config['bcc_admin'] ?? false) && isset($this->config['admin_email'])) {
                $this->mail->addBCC($this->config['admin_email']);
            }

            // Email content
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $message;
            $this->mail->AltBody = strip_tags($message);

            // Send email
            $this->mail->send();

            // Log successful email
            $this->logEmail('SENT', $to_email, $subject);

            return ['success' => true, 'message' => 'Email sent successfully'];

        } catch (Exception $e) {
            // Log failed email
            $this->logEmail('FAILED', $to_email, $subject, $e->getMessage());

            return ['success' => false, 'error' => 'Email could not be sent. Error: ' . $this->mail->ErrorInfo];
        }
    }

    /**
     * Log email activity
     */
    private function logEmail($status, $recipient, $subject, $error = null) {
        $log_file = __DIR__ . '/../logs/email.log';
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => $status,
            'recipient' => $recipient,
            'subject' => $subject,
            'error' => $error,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'system'
        ];

        file_put_contents($log_file, json_encode($log_entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

// Utility functions for easy email integration

/**
 * Send welcome email to new user
 */
function sendUserWelcomeEmail($user_email, $user_name, $user_type = 'customer') {
    try {
        $email_system = new EmailSystem();
        return $email_system->sendWelcomeEmail($user_email, $user_name, $user_type);
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Send property inquiry notification
 */
function sendPropertyInquiryNotification($property_data, $customer_data) {
    try {
        $email_system = new EmailSystem();
        return $email_system->sendPropertyInquiryNotification($property_data, $customer_data);
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($user_email, $user_name, $reset_token) {
    try {
        $email_system = new EmailSystem();
        return $email_system->sendPasswordResetEmail($user_email, $user_name, $reset_token);
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Send booking confirmation
 */
function sendBookingConfirmationEmail($booking_data) {
    try {
        $email_system = new EmailSystem();
        return $email_system->sendBookingConfirmationEmail($booking_data);
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Send commission notification
 */
function sendCommissionNotification($associate_data) {
    try {
        $email_system = new EmailSystem();
        return $email_system->sendCommissionNotification($associate_data);
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Send general notification
 */
function sendNotificationEmail($to_email, $subject, $message, $cc_admin = false) {
    try {
        $email_system = new EmailSystem();
        return $email_system->sendNotification($to_email, $subject, $message);
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
