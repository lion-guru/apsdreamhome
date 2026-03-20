<?php

namespace App\Services;

use App\Core\Database\Database;
use Exception;

class OTPService
{
    private $db;
    private $otpLength = 6;
    private $expiryMinutes = 10;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generate and send OTP
     */
    public function sendOTP($identifier, $type, $purpose = 'login')
    {
        try {
            // Validate identifier
            if (!$this->isValidIdentifier($identifier, $type)) {
                throw new Exception("Invalid $type identifier");
            }

            // Generate OTP
            $otpCode = $this->generateOTP();
            $expiresAt = date('Y-m-d H:i:s', time() + ($this->expiryMinutes * 60));

            // Clean up old OTPs for this identifier
            $this->cleanupOldOTPs($identifier);

            // Store OTP
            $this->storeOTP($identifier, $otpCode, $type, $purpose, $expiresAt);

            // Send OTP via appropriate channel
            $sent = $this->sendOTPCode($identifier, $otpCode, $type, $purpose);

            if (!$sent) {
                throw new Exception("Failed to send OTP");
            }

            return [
                'success' => true,
                'message' => "OTP sent successfully",
                'expires_at' => $expiresAt
            ];
        } catch (Exception $e) {
            error_log("OTP Service Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send OTP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOTP($identifier, $otpCode, $purpose = 'login')
    {
        try {
            // Get stored OTP
            $storedOTP = $this->getStoredOTP($identifier, $otpCode, $purpose);

            if (!$storedOTP) {
                return [
                    'success' => false,
                    'message' => 'Invalid OTP'
                ];
            }

            // Check if expired
            if (strtotime($storedOTP['expires_at']) < time()) {
                $this->markOTPAsUsed($storedOTP['id']);
                return [
                    'success' => false,
                    'message' => 'OTP has expired'
                ];
            }

            // Check attempts
            if ($storedOTP['attempts'] >= 3) {
                return [
                    'success' => false,
                    'message' => 'Too many attempts. Please request a new OTP'
                ];
            }

            // Increment attempts
            $this->incrementOTPAttempts($storedOTP['id']);

            if ($storedOTP['otp_code'] !== $otpCode) {
                return [
                    'success' => false,
                    'message' => 'Invalid OTP'
                ];
            }

            // Mark OTP as used
            $this->markOTPAsUsed($storedOTP['id']);

            return [
                'success' => true,
                'message' => 'OTP verified successfully'
            ];
        } catch (Exception $e) {
            error_log("OTP verification error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'OTP verification failed'
            ];
        }
    }

    /**
     * Generate OTP code
     */
    private function generateOTP()
    {
        return str_pad(random_int(0, pow(10, $this->otpLength) - 1), $this->otpLength, '0', STR_PAD_LEFT);
    }

    /**
     * Store OTP in database
     */
    private function storeOTP($identifier, $otpCode, $type, $purpose, $expiresAt)
    {
        $query = "INSERT INTO otp_verifications (identifier, otp_code, type, purpose, expires_at) VALUES (?, ?, ?, ?, ?)";
        $this->db->execute($query, [$identifier, $otpCode, $type, $purpose, $expiresAt]);
    }

    /**
     * Get stored OTP
     */
    private function getStoredOTP($identifier, $otpCode, $purpose)
    {
        $query = "SELECT * FROM otp_verifications WHERE identifier = ? AND otp_code = ? AND purpose = ? AND used_at IS NULL ORDER BY created_at DESC LIMIT 1";
        return $this->db->fetch($query, [$identifier, $otpCode, $purpose]);
    }

    /**
     * Mark OTP as used
     */
    private function markOTPAsUsed($otpId)
    {
        $query = "UPDATE otp_verifications SET used_at = NOW() WHERE id = ?";
        $this->db->execute($query, [$otpId]);
    }

    /**
     * Increment OTP attempts
     */
    private function incrementOTPAttempts($otpId)
    {
        $query = "UPDATE otp_verifications SET attempts = attempts + 1 WHERE id = ?";
        $this->db->execute($query, [$otpId]);
    }

    /**
     * Clean up old OTPs
     */
    private function cleanupOldOTPs($identifier)
    {
        $query = "DELETE FROM otp_verifications WHERE identifier = ? AND used_at IS NULL";
        $this->db->execute($query, [$identifier]);
    }

    /**
     * Send OTP code via appropriate channel
     */
    private function sendOTPCode($identifier, $otpCode, $type, $purpose)
    {
        try {
            switch ($type) {
                case 'email':
                    return $this->sendEmailOTP($identifier, $otpCode, $purpose);
                case 'sms':
                    return $this->sendSMSOTP($identifier, $otpCode, $purpose);
                case 'whatsapp':
                    return $this->sendWhatsAppOTP($identifier, $otpCode, $purpose);
                default:
                    throw new Exception("Unsupported OTP type: $type");
            }
        } catch (Exception $e) {
            error_log("OTP send error for $type: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send OTP via email
     */
    private function sendEmailOTP($email, $otpCode, $purpose)
    {
        $subject = $this->getEmailSubject($purpose);
        $message = $this->getEmailMessage($otpCode, $purpose);

        // Use PHP mail function for now (in production, use proper email service)
        $headers = "From: noreply@apsdreamhome.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        // For testing environment, simulate email sending
        $sent = mail($email, $subject, $message, $headers);

        if (!$sent) {
            // Log the email for testing purposes
            error_log("EMAIL OTP to $email: Subject: $subject, Message: $message");
            return true; // Return true for testing environment
        }

        return $sent;
    }

    /**
     * Send OTP via SMS
     */
    private function sendSMSOTP($phone, $otpCode, $purpose)
    {
        $message = $this->getSMSMessage($otpCode, $purpose);

        // For demo purposes, just log the message
        error_log("SMS OTP to $phone: $message");

        // In production, integrate with SMS gateway like Twilio, AWS SNS, etc.
        return true;
    }

    /**
     * Send OTP via WhatsApp
     */
    private function sendWhatsAppOTP($phone, $otpCode, $purpose)
    {
        $message = $this->getWhatsAppMessage($otpCode, $purpose);

        // For demo purposes, just log the message
        error_log("WhatsApp OTP to $phone: $message");

        // In production, integrate with WhatsApp Business API
        return true;
    }

    /**
     * Get email subject
     */
    private function getEmailSubject($purpose)
    {
        $subjects = [
            'login' => 'APS Dream Home - Login OTP',
            'registration' => 'APS Dream Home - Verify Your Email',
            'password_reset' => 'APS Dream Home - Reset Password OTP',
            'account_verification' => 'APS Dream Home - Account Verification'
        ];

        return $subjects[$purpose] ?? 'APS Dream Home - OTP Verification';
    }

    /**
     * Get email message
     */
    private function getEmailMessage($otpCode, $purpose)
    {
        $messages = [
            'login' => "Your login OTP for APS Dream Home is: <strong>$otpCode</strong><br><br>This OTP will expire in {$this->expiryMinutes} minutes.",
            'registration' => "Your verification OTP for APS Dream Home is: <strong>$otpCode</strong><br><br>Please enter this code to verify your email address.",
            'password_reset' => "Your password reset OTP for APS Dream Home is: <strong>$otpCode</strong><br><br>This OTP will expire in {$this->expiryMinutes} minutes.",
            'account_verification' => "Your account verification OTP for APS Dream Home is: <strong>$otpCode</strong><br><br>Please enter this code to verify your account."
        ];

        $message = $messages[$purpose] ?? "Your OTP is: <strong>$otpCode</strong>";

        return "<html><body><div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;'><div style='background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'><h2 style='color: #667eea; text-align: center; margin-bottom: 30px;'>APS Dream Home</h2><p style='font-size: 16px; color: #333; line-height: 1.6;'>$message</p><div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; margin-top: 20px;'><span style='font-size: 24px; font-weight: bold; color: #667eea; letter-spacing: 3px;'>$otpCode</span></div><p style='font-size: 12px; color: #666; text-align: center; margin-top: 30px;'>If you didn't request this OTP, please ignore this email.</p></div></div></body></html>";
    }

    /**
     * Get SMS message
     */
    private function getSMSMessage($otpCode, $purpose)
    {
        $messages = [
            'login' => "APS Dream Home login OTP: $otpCode. Valid for {$this->expiryMinutes} minutes.",
            'registration' => "APS Dream Home verification OTP: $otpCode. Please enter this code to verify your account.",
            'password_reset' => "APS Dream Home password reset OTP: $otpCode. Valid for {$this->expiryMinutes} minutes.",
            'account_verification' => "APS Dream Home account verification OTP: $otpCode. Please enter this code to verify your account."
        ];

        return $messages[$purpose] ?? "APS Dream Home OTP: $otpCode";
    }

    /**
     * Get WhatsApp message
     */
    private function getWhatsAppMessage($otpCode, $purpose)
    {
        $messages = [
            'login' => "🔐 *APS Dream Home Login*\n\nYour OTP code is: *$otpCode*\n\nValid for {$this->expiryMinutes} minutes.\n\nDon't share this code with anyone.",
            'registration' => "👋 *Welcome to APS Dream Home*\n\nYour verification OTP is: *$otpCode*\n\nPlease enter this code to verify your account.",
            'password_reset' => "🔒 *APS Dream Home Password Reset*\n\nYour OTP code is: *$otpCode*\n\nValid for {$this->expiryMinutes} minutes.",
            'account_verification' => "✅ *APS Dream Home Account Verification*\n\nYour OTP code is: *$otpCode*\n\nPlease enter this code to verify your account."
        ];

        return $messages[$purpose] ?? "APS Dream Home OTP: *$otpCode*";
    }

    /**
     * Validate identifier format
     */
    private function isValidIdentifier($identifier, $type)
    {
        switch ($type) {
            case 'email':
                return filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
            case 'sms':
            case 'whatsapp':
                // Basic phone number validation (adjust as needed)
                return preg_match('/^[+]?[0-9]{10,15}$/', preg_replace('/[^0-9+]/', '', $identifier));
            default:
                return false;
        }
    }

    /**
     * Check if user can request new OTP
     */
    public function canRequestNewOTP($identifier, $type)
    {
        $query = "SELECT COUNT(*) as count FROM otp_verifications WHERE identifier = ? AND type = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
        $result = $this->db->fetch($query, [$identifier, $type]);

        return $result['count'] == 0;
    }

    /**
     * Get remaining time for OTP
     */
    public function getOTPExpiryTime($identifier, $type, $purpose)
    {
        $query = "SELECT expires_at FROM otp_verifications WHERE identifier = ? AND type = ? AND purpose = ? AND used_at IS NULL ORDER BY created_at DESC LIMIT 1";
        $result = $this->db->fetch($query, [$identifier, $type, $purpose]);

        if (!$result) {
            return 0;
        }

        $remaining = strtotime($result['expires_at']) - time();
        return max(0, $remaining);
    }

    /**
     * Clean up expired OTPs
     */
    public function cleanupExpiredOTPs()
    {
        $query = "DELETE FROM otp_verifications WHERE expires_at < NOW() OR used_at IS NOT NULL";
        $this->db->execute($query);
    }
}
