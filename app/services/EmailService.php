<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Models\AppConfig;

class EmailService {
    private $mail;
    private $config;

    public function __construct() {
        $this->config = AppConfig::getInstance()->get('email');
        $this->mail = new PHPMailer(true);
        
        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host = $this->config['smtp_host'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $this->config['smtp_username'];
        $this->mail->Password = $this->config['smtp_password'];
        $this->mail->SMTPSecure = $this->config['smtp_secure'];
        $this->mail->Port = $this->config['smtp_port'];
        
        // Sender info
        $this->mail->setFrom($this->config['smtp_username'], 'APS Dream Home');
        $this->mail->isHTML(true);
    }

    /**
     * Send verification email
     */
    public function sendVerificationEmail($to, $name, $token) {
        try {
            $this->mail->addAddress($to, $name);
            $this->mail->Subject = 'Verify Your Email Address';
            
            // Email body
            $verificationUrl = "http://localhost/apsdreamhome" . "/verify-email?token=$token";
            $message = file_get_contents(__DIR__ . '/../../resources/emails/verification.html');
            $message = str_replace(
                ['{{name}}', '{{verification_url}}'],
                [$name, $verificationUrl],
                $message
            );
            
            $this->mail->msgHTML($message);
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $this->mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Send lead welcome email
     */
    public function sendLeadWelcomeEmail($to, $name) {
        try {
            $this->mail->addAddress($to, $name);
            $this->mail->Subject = 'Welcome to APS Dream Home - Lead Created';

            // Email body
            $message = "
            <html>
            <body>
                <h2>Welcome to APS Dream Home!</h2>
                <p>Dear $name,</p>
                <p>Thank you for your interest in APS Dream Home. We have received your inquiry and our team will contact you shortly.</p>
                <p>We will help you find your dream property!</p>
                <br>
                <p>Best regards,<br>APS Dream Home Team</p>
            </body>
            </html>
            ";

            $this->mail->msgHTML($message);
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Lead welcome email failed: " . $this->mail->ErrorInfo);
            return false;
        }
    }
}
