<?php
/**
 * Email Service
 * Handles all email notifications using PHPMailer
 */

namespace App\Services\Communication;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Core\Database\Database;

class EmailService
{
    private $mailer;
    private $db;
    private $fromEmail;
    private $fromName;
    private $smtpHost;
    private $smtpPort;
    private $smtpUser;
    private $smtpPass;
    private $smtpSecure;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        
        // Load settings from database or environment
        $this->fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@apsdreamhome.com';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? 'APS Dream Home';
        $this->smtpHost = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $this->smtpPort = $_ENV['MAIL_PORT'] ?? 587;
        $this->smtpUser = $_ENV['MAIL_USERNAME'] ?? '';
        $this->smtpPass = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->smtpSecure = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
        
        $this->initMailer();
    }
    
    /**
     * Initialize PHPMailer
     */
    private function initMailer()
    {
        $this->mailer = new PHPMailer(true);
        
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->smtpHost;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->smtpUser;
            $this->mailer->Password = $this->smtpPass;
            $this->mailer->SMTPSecure = $this->smtpSecure;
            $this->mailer->Port = $this->smtpPort;
            
            // Default from
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            
            // HTML email format
            $this->mailer->isHTML(true);
            
        } catch (Exception $e) {
            error_log("PHPMailer initialization failed: " . $e->getMessage());
        }
    }
    
    /**
     * Send welcome email to new customer
     */
    public function sendWelcomeEmail($userId)
    {
        try {
            $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
            if (!$user) return false;
            
            $subject = "Welcome to APS Dream Home - Your Dream Property Awaits!";
            
            $body = $this->getWelcomeTemplate([
                'name' => $user['name'],
                'email' => $user['email'],
                'customer_id' => $user['customer_id'] ?? $user['id'],
                'login_url' => BASE_URL . '/login',
                'properties_url' => BASE_URL . '/properties',
                'support_email' => $this->fromEmail
            ]);
            
            return $this->send($user['email'], $subject, $body);
            
        } catch (\Exception $e) {
            error_log("Welcome email failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send welcome email to new associate
     */
    public function sendAssociateWelcomeEmail($userId, $referralCode)
    {
        try {
            $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
            if (!$user) return false;
            
            $subject = "Welcome to APS Dream Home Associate Program!";
            
            $body = $this->getAssociateWelcomeTemplate([
                'name' => $user['name'],
                'email' => $user['email'],
                'associate_id' => $user['customer_id'] ?? $user['id'],
                'referral_code' => $referralCode,
                'referral_link' => BASE_URL . '/register?ref=' . $referralCode,
                'dashboard_url' => BASE_URL . '/associate/dashboard',
                'network_url' => BASE_URL . '/associate/network',
                'support_email' => $this->fromEmail
            ]);
            
            return $this->send($user['email'], $subject, $body);
            
        } catch (\Exception $e) {
            error_log("Associate welcome email failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send payment confirmation email
     */
    public function sendPaymentConfirmation($paymentId)
    {
        try {
            $payment = $this->db->fetchOne(
                "SELECT p.*, u.name, u.email, b.property_id, pr.title as property_title 
                 FROM payments p 
                 JOIN users u ON p.user_id = u.id 
                 JOIN bookings b ON p.booking_id = b.id 
                 JOIN properties pr ON b.property_id = pr.id 
                 WHERE p.id = ?",
                [$paymentId]
            );
            
            if (!$payment) return false;
            
            $subject = "Payment Confirmation - Booking #" . $payment['booking_id'];
            
            $body = $this->getPaymentConfirmationTemplate([
                'name' => $payment['name'],
                'amount' => number_format($payment['amount'], 2),
                'transaction_id' => $payment['transaction_id'],
                'booking_id' => $payment['booking_id'],
                'property_title' => $payment['property_title'],
                'payment_date' => date('d M Y, h:i A', strtotime($payment['created_at'])),
                'dashboard_url' => BASE_URL . '/customer/dashboard',
                'support_email' => $this->fromEmail
            ]);
            
            return $this->send($payment['email'], $subject, $body);
            
        } catch (\Exception $e) {
            error_log("Payment confirmation email failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send property approval notification
     */
    public function sendPropertyApprovalNotification($propertyId)
    {
        try {
            $property = $this->db->fetchOne(
                "SELECT up.*, u.name, u.email 
                 FROM user_properties up 
                 JOIN users u ON up.user_id = u.id 
                 WHERE up.id = ?",
                [$propertyId]
            );
            
            if (!$property) return false;
            
            $subject = "Your Property Listing is Approved!";
            
            $body = $this->getPropertyApprovalTemplate([
                'name' => $property['name'],
                'property_title' => $property['title'] ?? $property['property_type'],
                'property_type' => $property['property_type'],
                'location' => $property['address'],
                'price' => number_format($property['price'], 2),
                'listing_url' => BASE_URL . '/properties/' . $propertyId,
                'dashboard_url' => BASE_URL . '/customer/dashboard',
                'support_email' => $this->fromEmail
            ]);
            
            return $this->send($property['email'], $subject, $body);
            
        } catch (\Exception $e) {
            error_log("Property approval email failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send commission credit notification
     */
    public function sendCommissionCreditNotification($commissionId)
    {
        try {
            $commission = $this->db->fetchOne(
                "SELECT c.*, u.name, u.email, u2.name as referred_name 
                 FROM commissions c 
                 JOIN users u ON c.associate_id = u.id 
                 JOIN users u2 ON c.referred_user_id = u2.id 
                 WHERE c.id = ?",
                [$commissionId]
            );
            
            if (!$commission) return false;
            
            $subject = "Commission Credited - ₹" . number_format($commission['commission_amount'], 2);
            
            $body = $this->getCommissionCreditTemplate([
                'name' => $commission['name'],
                'amount' => number_format($commission['commission_amount'], 2),
                'percentage' => $commission['percentage'],
                'referred_user' => $commission['referred_name'],
                'level' => $commission['level'],
                'wallet_balance' => $this->getWalletBalance($commission['associate_id']),
                'dashboard_url' => BASE_URL . '/associate/dashboard',
                'payout_url' => BASE_URL . '/wallet/withdrawal',
                'support_email' => $this->fromEmail
            ]);
            
            return $this->send($commission['email'], $subject, $body);
            
        } catch (\Exception $e) {
            error_log("Commission credit email failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($email, $token)
    {
        try {
            $resetUrl = BASE_URL . '/reset-password?token=' . $token;
            
            $subject = "Password Reset Request - APS Dream Home";
            
            $body = $this->getPasswordResetTemplate([
                'reset_url' => $resetUrl,
                'expiry_hours' => 24,
                'support_email' => $this->fromEmail
            ]);
            
            return $this->send($email, $subject, $body);
            
        } catch (\Exception $e) {
            error_log("Password reset email failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send OTP email
     */
    public function sendOTPEmail($email, $otp, $purpose = 'verification')
    {
        try {
            $subject = "Your OTP for " . ucfirst($purpose);
            
            $body = $this->getOTPTemplate([
                'otp' => $otp,
                'purpose' => $purpose,
                'expiry_minutes' => 10,
                'support_email' => $this->fromEmail
            ]);
            
            return $this->send($email, $subject, $body);
            
        } catch (\Exception $e) {
            error_log("OTP email failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send daily report to admin
     */
    public function sendDailyReport($adminEmail)
    {
        try {
            // Get daily stats
            $stats = $this->db->fetchOne(
                "SELECT 
                    (SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()) as new_users,
                    (SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()) as new_leads,
                    (SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = CURDATE()) as new_bookings,
                    (SELECT SUM(amount) FROM payments WHERE DATE(created_at) = CURDATE() AND status = 'completed') as revenue
                "
            );
            
            $subject = "Daily Report - " . date('d M Y');
            
            $body = $this->getDailyReportTemplate([
                'date' => date('d M Y'),
                'new_users' => $stats['new_users'] ?? 0,
                'new_leads' => $stats['new_leads'] ?? 0,
                'new_bookings' => $stats['new_bookings'] ?? 0,
                'revenue' => number_format($stats['revenue'] ?? 0, 2),
                'admin_url' => BASE_URL . '/admin/dashboard'
            ]);
            
            return $this->send($adminEmail, $subject, $body);
            
        } catch (\Exception $e) {
            error_log("Daily report email failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generic send method
     */
    public function send($to, $subject, $body, $attachments = [])
    {
        try {
            // Reset recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Add recipient
            $this->mailer->addAddress($to);
            
            // Add CC if configured
            $adminEmail = $_ENV['ADMIN_EMAIL'] ?? null;
            if ($adminEmail && $adminEmail !== $to) {
                $this->mailer->addBCC($adminEmail);
            }
            
            // Add attachments
            foreach ($attachments as $attachment) {
                if (file_exists($attachment['path'])) {
                    $this->mailer->addAttachment(
                        $attachment['path'], 
                        $attachment['name'] ?? basename($attachment['path'])
                    );
                }
            }
            
            // Content
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            // Send
            $result = $this->mailer->send();
            
            // Log to database
            $this->logEmail($to, $subject, $body, $result ? 'sent' : 'failed');
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Email send failed: " . $e->getMessage());
            $this->logEmail($to, $subject, $body, 'failed', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log email to database
     */
    private function logEmail($to, $subject, $body, $status, $error = null)
    {
        try {
            $this->db->insert('email_logs', [
                'recipient' => $to,
                'subject' => $subject,
                'body' => substr($body, 0, 1000), // Truncate for storage
                'status' => $status,
                'error_message' => $error,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            error_log("Email logging failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get wallet balance
     */
    private function getWalletBalance($userId)
    {
        $wallet = $this->db->fetchOne(
            "SELECT points_balance FROM wallet_points WHERE user_id = ?",
            [$userId]
        );
        return $wallet ? number_format($wallet['points_balance'], 2) : '0.00';
    }
    
    // ==================== EMAIL TEMPLATES ====================
    
    private function getWelcomeTemplate($data)
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; background: #4f46e5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to APS Dream Home!</h1>
        </div>
        <div class="content">
            <h2>Hi {$data['name']},</h2>
            <p>Thank you for joining APS Dream Home! Your account has been successfully created.</p>
            
            <p><strong>Your Customer ID:</strong> {$data['customer_id']}</p>
            <p><strong>Email:</strong> {$data['email']}</p>
            
            <p>You can now:</p>
            <ul>
                <li>Browse premium properties in Uttar Pradesh</li>
                <li>Save your favorite listings</li>
                <li>Book site visits</li>
                <li>Connect with our agents</li>
            </ul>
            
            <center>
                <a href="{$data['properties_url']}" class="button">Browse Properties</a>
            </center>
            
            <p>If you need any assistance, feel free to contact us at <a href="mailto:{$data['support_email']}">{$data['support_email']}</a></p>
            
            <p>Best regards,<br><strong>APS Dream Home Team</strong></p>
        </div>
        <div class="footer">
            <p>&copy; 2026 APS Dream Home. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    private function getAssociateWelcomeTemplate($data)
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .referral-box { background: #4f46e5; color: white; padding: 20px; border-radius: 10px; text-align: center; margin: 20px 0; }
        .referral-code { font-size: 24px; font-weight: bold; letter-spacing: 2px; }
        .button { display: inline-block; background: #4f46e5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Associate Program!</h1>
        </div>
        <div class="content">
            <h2>Hi {$data['name']},</h2>
            <p>Congratulations! You're now an APS Dream Home Associate. Start earning commissions today!</p>
            
            <div class="referral-box">
                <p>Your Referral Code</p>
                <div class="referral-code">{$data['referral_code']}</div>
                <p style="margin-top: 15px;">Share this code with friends & family</p>
            </div>
            
            <p><strong>Your Referral Link:</strong></p>
            <p><a href="{$data['referral_link']}">{$data['referral_link']}</a></p>
            
            <center>
                <a href="{$data['dashboard_url']}" class="button">View Dashboard</a>
                <a href="{$data['network_url']}" class="button">My Network</a>
            </center>
            
            <p><strong>How it works:</strong></p>
            <ol>
                <li>Share your referral code</li>
                <li>Friends register using your code</li>
                <li>They book a property</li>
                <li>You earn commission!</li>
            </ol>
            
            <p>Questions? Contact us at <a href="mailto:{$data['support_email']}">{$data['support_email']}</a></p>
            
            <p>Best regards,<br><strong>APS Dream Home Team</strong></p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    private function getPaymentConfirmationTemplate($data)
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .success-box { background: #d1fae5; border: 2px solid #10b981; padding: 20px; border-radius: 10px; margin: 20px 0; text-align: center; }
        .details { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; }
        .details table { width: 100%; }
        .details td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
        .button { display: inline-block; background: #4f46e5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Payment Successful!</h1>
        </div>
        <div class="content">
            <div class="success-box">
                <h2>₹{$data['amount']}</h2>
                <p>Paid Successfully</p>
            </div>
            
            <p>Hi {$data['name']},</p>
            <p>Your payment has been received and your booking is confirmed!</p>
            
            <div class="details">
                <h3>Payment Details</h3>
                <table>
                    <tr><td><strong>Amount</strong></td><td>₹{$data['amount']}</td></tr>
                    <tr><td><strong>Transaction ID</strong></td><td>{$data['transaction_id']}</td></tr>
                    <tr><td><strong>Booking ID</strong></td><td>#{$data['booking_id']}</td></tr>
                    <tr><td><strong>Property</strong></td><td>{$data['property_title']}</td></tr>
                    <tr><td><strong>Date</strong></td><td>{$data['payment_date']}</td></tr>
                </table>
            </div>
            
            <center>
                <a href="{$data['dashboard_url']}" class="button">View Dashboard</a>
            </center>
            
            <p>Thank you for choosing APS Dream Home!</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    private function getPropertyApprovalTemplate($data)
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .approved-box { background: #fef3c7; border: 2px solid #f59e0b; padding: 20px; border-radius: 10px; margin: 20px 0; text-align: center; }
        .button { display: inline-block; background: #4f46e5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Property Approved!</h1>
        </div>
        <div class="content">
            <div class="approved-box">
                <h2>✓ Your listing is now live</h2>
            </div>
            
            <p>Hi {$data['name']},</p>
            <p>Great news! Your property listing has been approved and is now visible to potential buyers.</p>
            
            <h3>Property Details</h3>
            <ul>
                <li><strong>Type:</strong> {$data['property_type']}</li>
                <li><strong>Location:</strong> {$data['location']}</li>
                <li><strong>Price:</strong> ₹{$data['price']}</li>
            </ul>
            
            <center>
                <a href="{$data['listing_url']}" class="button">View Listing</a>
            </center>
            
            <p>You can manage your properties from your dashboard.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    private function getCommissionCreditTemplate($data)
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .commission-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; text-align: center; margin: 20px 0; }
        .amount { font-size: 36px; font-weight: bold; }
        .button { display: inline-block; background: #4f46e5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Commission Credited!</h1>
        </div>
        <div class="content">
            <p>Hi {$data['name']},</p>
            <p>You've earned a commission from your referral network!</p>
            
            <div class="commission-box">
                <p>Commission Amount</p>
                <div class="amount">₹{$data['amount']}</div>
                <p>Level {$data['level']} ({$data['percentage']}%)</p>
            </div>
            
            <p><strong>Referred User:</strong> {$data['referred_user']}</p>
            <p><strong>Current Wallet Balance:</strong> ₹{$data['wallet_balance']}</p>
            
            <center>
                <a href="{$data['payout_url']}" class="button">Request Payout</a>
                <a href="{$data['dashboard_url']}" class="button">View Dashboard</a>
            </center>
            
            <p>Keep referring to earn more!</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    private function getPasswordResetTemplate($data)
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4f46e5; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; background: #4f46e5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .warning { background: #fef3c7; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Password Reset</h1>
        </div>
        <div class="content">
            <p>You requested a password reset. Click the button below to reset your password:</p>
            
            <center>
                <a href="{$data['reset_url']}" class="button">Reset Password</a>
            </center>
            
            <div class="warning">
                <p><strong>Note:</strong> This link expires in {$data['expiry_hours']} hours.</p>
            </div>
            
            <p>If you didn't request this, please ignore this email.</p>
            
            <p>Need help? Contact <a href="mailto:{$data['support_email']}">{$data['support_email']}</a></p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    private function getOTPTemplate($data)
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; text-align: center; }
        .otp-box { background: #4f46e5; color: white; padding: 40px; border-radius: 10px; margin: 30px 0; }
        .otp-code { font-size: 48px; font-weight: bold; letter-spacing: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Your OTP Code</h1>
        <p>Use this code for {$data['purpose']}:</p>
        
        <div class="otp-box">
            <div class="otp-code">{$data['otp']}</div>
        </div>
        
        <p>This code expires in {$data['expiry_minutes']} minutes.</p>
        <p>Don't share this code with anyone.</p>
        
        <p>Need help? <a href="mailto:{$data['support_email']}">Contact Support</a></p>
    </div>
</body>
</html>
HTML;
    }
    
    private function getDailyReportTemplate($data)
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .stats { display: flex; justify-content: space-around; margin: 30px 0; }
        .stat-box { text-align: center; padding: 20px; background: white; border-radius: 10px; min-width: 120px; }
        .stat-number { font-size: 32px; font-weight: bold; color: #4f46e5; }
        .button { display: inline-block; background: #4f46e5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Daily Report - {$data['date']}</h1>
        </div>
        <div class="content">
            <div class="stats">
                <div class="stat-box">
                    <div class="stat-number">{$data['new_users']}</div>
                    <div>New Users</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">{$data['new_leads']}</div>
                    <div>New Leads</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">{$data['new_bookings']}</div>
                    <div>Bookings</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">₹{$data['revenue']}</div>
                    <div>Revenue</div>
                </div>
            </div>
            
            <center>
                <a href="{$data['admin_url']}" class="button">View Full Dashboard</a>
            </center>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
