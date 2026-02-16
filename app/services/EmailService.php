<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class EmailService {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host = getenv('MAIL_HOST') ?: 'localhost';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = getenv('MAIL_USERNAME');
        $this->mail->Password = getenv('MAIL_PASSWORD');
        $this->mail->SMTPSecure = getenv('MAIL_ENCRYPTION') ?: 'tls';
        $this->mail->Port = getenv('MAIL_PORT') ?: 587;
        
        // Sender info
        $this->mail->setFrom(getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@apsdreamhome.com', getenv('MAIL_FROM_NAME') ?: 'APS Dream Home');
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
