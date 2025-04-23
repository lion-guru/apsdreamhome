<?php
namespace Services;

class EmailService {
    private $mailer;
    private $from_email;
    private $from_name;

    public function __construct() {
        $this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        $this->setupMailer();
        $this->from_email = getenv('MAIL_FROM_ADDRESS') ?: 'noreply@apsrealestate.com';
        $this->from_name = getenv('MAIL_FROM_NAME') ?: APP_NAME;
    }

    private function setupMailer() {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = getenv('MAIL_USERNAME');
            $this->mailer->Password = getenv('MAIL_PASSWORD');
            $this->mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = getenv('MAIL_PORT') ?: 587;
            $this->mailer->CharSet = 'UTF-8';
        } catch (\Exception $e) {
            throw new \Exception('Mail configuration error: ' . $e->getMessage());
        }
    }

    public function sendPasswordResetEmail($to_email, $user_name, $reset_link) {
        try {
            $this->mailer->setFrom($this->from_email, $this->from_name);
            $this->mailer->addAddress($to_email, $user_name);
            $this->mailer->isHTML(true);

            $this->mailer->Subject = APP_NAME . ' - Password Reset Request';
            $this->mailer->Body = $this->getPasswordResetTemplate($user_name, $reset_link);
            $this->mailer->AltBody = strip_tags($this->mailer->Body);

            return $this->mailer->send();
        } catch (\Exception $e) {
            throw new \Exception('Failed to send password reset email: ' . $e->getMessage());
        }
    }

    public function sendContactFormEmail($from_email, $name, $subject, $message) {
        try {
            $this->mailer->setFrom($this->from_email, $this->from_name);
            $this->mailer->addReplyTo($from_email, $name);
            $this->mailer->addAddress(getenv('ADMIN_EMAIL') ?: 'admin@apsrealestate.com');
            $this->mailer->isHTML(true);

            $this->mailer->Subject = 'New Contact Form Submission: ' . $subject;
            $this->mailer->Body = $this->getContactFormTemplate($name, $from_email, $message);
            $this->mailer->AltBody = strip_tags($this->mailer->Body);

            return $this->mailer->send();
        } catch (\Exception $e) {
            throw new \Exception('Failed to send contact form email: ' . $e->getMessage());
        }
    }

    public function sendPropertyEnquiryEmail($property, $user, $message) {
        try {
            $this->mailer->setFrom($this->from_email, $this->from_name);
            $this->mailer->addReplyTo($user['email'], $user['name']);
            $this->mailer->addAddress(getenv('AGENT_EMAIL') ?: 'agent@apsrealestate.com');
            $this->mailer->isHTML(true);

            $this->mailer->Subject = 'New Property Enquiry: ' . $property['title'];
            $this->mailer->Body = $this->getPropertyEnquiryTemplate($property, $user, $message);
            $this->mailer->AltBody = strip_tags($this->mailer->Body);

            return $this->mailer->send();
        } catch (\Exception $e) {
            throw new \Exception('Failed to send property enquiry email: ' . $e->getMessage());
        }
    }

    private function getPasswordResetTemplate($user_name, $reset_link) {
        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2>Password Reset Request</h2>
            <p>Dear {$user_name},</p>
            <p>We received a request to reset your password. Click the button below to reset it:</p>
            <p style="text-align: center;">
                <a href="{$reset_link}" style="background-color: #4CAF50; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;">Reset Password</a>
            </p>
            <p>If you didn't request this, please ignore this email.</p>
            <p>This link will expire in 24 hours.</p>
            <p>Best regards,<br>{$this->from_name}</p>
        </div>
        HTML;
    }

    private function getContactFormTemplate($name, $email, $message) {
        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2>New Contact Form Submission</h2>
            <p><strong>From:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Message:</strong></p>
            <div style="background-color: #f5f5f5; padding: 15px; border-radius: 4px;">
                {$message}
            </div>
        </div>
        HTML;
    }

    private function getPropertyEnquiryTemplate($property, $user, $message) {
        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2>New Property Enquiry</h2>
            <p><strong>Property:</strong> {$property['title']}</p>
            <p><strong>From:</strong> {$user['name']}</p>
            <p><strong>Email:</strong> {$user['email']}</p>
            <p><strong>Message:</strong></p>
            <div style="background-color: #f5f5f5; padding: 15px; border-radius: 4px;">
                {$message}
            </div>
            <p style="margin-top: 20px;">
                <a href="" . APP_URL . ">View Property</a>
            </p>
        </div>
        HTML;
    }
}