<?php
/**
 * SMS Service
 * MSG91 Integration for OTP, Notifications, and Alerts
 */

namespace App\Services\Communication;

use App\Core\Database\Database;
use \App\Traits\ServiceTenantTrait;

class SMSService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $authKey;
    private $senderId;
    private $templateId;
    private $apiUrl = 'https://api.msg91.com/api/v5/flow/';
    private $otpApiUrl = 'https://control.msg91.com/api/v5/otp/';
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->authKey = $_ENV['MSG91_AUTH_KEY'] ?? '';
        $this->senderId = $_ENV['MSG91_SENDER_ID'] ?? 'APSDHM';
        $this->templateId = $_ENV['MSG91_TEMPLATE_ID'] ?? '';
    }
    
    /**
     * Send OTP to mobile number
     */
    public function sendOTP($mobile, $otp = null, $templateId = null)
    {
        try {
            // Generate OTP if not provided
            if (!$otp) {
                $otp = $this->generateOTP();
            }
            
            // Clean mobile number
            $mobile = $this->cleanMobileNumber($mobile);
            
            // Save OTP to database for verification
            $this->saveOTP($mobile, $otp);
            
            $payload = [
                'template_id' => $templateId ?? $this->templateId,
                'short_url' => '0',
                'realTimeResponse' => 'true',
                'recipients' => [
                    [
                        'mobiles' => $mobile,
                        'OTP' => $otp
                    ]
                ]
            ];
            
            $result = $this->callAPI($this->apiUrl, $payload);
            
            // Log the SMS
            $this->logSMS($mobile, 'OTP', "Your OTP is: {$otp}", $result['type'] ?? 'unknown');
            
            return [
                'success' => true,
                'otp' => $otp,
                'message_id' => $result['message_id'] ?? null
            ];
            
        } catch (\Exception $e) {
            error_log("SMS OTP send failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Verify OTP
     */
    public function verifyOTP($mobile, $otp)
    {
        try {
            $mobile = $this->cleanMobileNumber($mobile);
            
            // Get stored OTP
            $record = $this->db->fetchOne(
                "SELECT * FROM notifications_unified 
                 WHERE mobile = ? AND status = 'pending' 
                 AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                 ORDER BY created_at DESC LIMIT 1",
                [$mobile]
            );
            
            if (!$record) {
                return ['success' => false, 'error' => 'OTP expired or not found'];
            }
            
            if ($record['otp'] !== $otp) {
                return ['success' => false, 'error' => 'Invalid OTP'];
            }
            
            // Mark as verified
            $this->db->query(
                "UPDATE notifications_unified SET status = 'verified', verified_at = NOW() WHERE id = ?",
                [$record['id']]
            );
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            error_log("OTP verification failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Send welcome SMS to new user
     */
    public function sendWelcomeSMS($mobile, $name, $userType = 'customer')
    {
        $message = $userType === 'associate' 
            ? "Hi {$name}, Welcome to APS Dream Home Associate Program! Your referral journey starts now. Login: " . BASE_URL . "/associate/login"
            : "Hi {$name}, Welcome to APS Dream Home! Your dream property awaits. Start browsing: " . BASE_URL . "/properties";
        
        return $this->sendSMS($mobile, $message, 'WELCOME');
    }
    
    /**
     * Send payment confirmation SMS
     */
    public function sendPaymentConfirmationSMS($mobile, $amount, $bookingId)
    {
        $message = "Payment Successful! Amount: Rs.{$amount} received for Booking #{$bookingId}. Thank you for choosing APS Dream Home.";
        
        return $this->sendSMS($mobile, $message, 'PAYMENT');
    }
    
    /**
     * Send commission credit SMS
     */
    public function sendCommissionSMS($mobile, $amount, $walletBalance)
    {
        $message = "Commission Credited! Rs.{$amount} added to your wallet. Current Balance: Rs.{$walletBalance}. Keep referring!";
        
        return $this->sendSMS($mobile, $message, 'COMMISSION');
    }
    
    /**
     * Send property approval SMS
     */
    public function sendPropertyApprovalSMS($mobile, $propertyTitle)
    {
        $message = "Your property listing '{$propertyTitle}' has been approved and is now live on APS Dream Home!";
        
        return $this->sendSMS($mobile, $message, 'PROPERTY');
    }
    
    /**
     * Send site visit reminder
     */
    public function sendVisitReminderSMS($mobile, $propertyTitle, $visitDate, $visitTime)
    {
        $formattedDate = date('d M Y', strtotime($visitDate));
        $message = "Reminder: Site visit scheduled for {$propertyTitle} on {$formattedDate} at {$visitTime}. Our executive will meet you there.";
        
        return $this->sendSMS($mobile, $message, 'VISIT_REMINDER');
    }
    
    /**
     * Send payout confirmation
     */
    public function sendPayoutSMS($mobile, $amount, $transactionId)
    {
        $message = "Payout Processed! Rs.{$amount} has been transferred to your bank account. Transaction ID: {$transactionId}";
        
        return $this->sendSMS($mobile, $message, 'PAYOUT');
    }
    
    /**
     * Send login alert SMS
     */
    public function sendLoginAlertSMS($mobile, $name, $ip, $device, $time)
    {
        $message = "APS Dream Home: New login to your account at {$time} from {$device}. ";
        $message .= "If this wasn't you, change your password immediately. ";
        $message .= "Contact support: " . BASE_URL . "/support";
        
        return $this->sendSMS($mobile, $message, 'LOGIN_ALERT');
    }
    
    /**
     * Generic SMS send method
     */
    public function sendSMS($mobile, $message, $type = 'GENERAL')
    {
        try {
            $mobile = $this->cleanMobileNumber($mobile);
            
            $payload = [
                'template_id' => $this->templateId,
                'short_url' => '0',
                'realTimeResponse' => 'true',
                'recipients' => [
                    [
                        'mobiles' => $mobile,
                        'message' => $message
                    ]
                ]
            ];
            
            $result = $this->callAPI($this->apiUrl, $payload);
            
            $this->logSMS($mobile, $type, $message, $result['type'] ?? 'unknown');

            // Track SMS usage for tenant
            if (class_exists('\App\Core\Middleware\TenantContext') && class_exists('\App\Services\TenantService')) {
                try {
                    $tenantId = \App\Core\Middleware\TenantContext::getId();
                    if ($tenantId > 1) {
                        \App\Services\TenantService::getInstance()->incrementUsage($tenantId, 'sms');
                    }
                } catch (\Throwable $e) {
                // usage tracking should never break SMS sending
                error_log($e->getMessage());
                }
            }
            
            return ['success' => true, 'message_id' => $result['message_id'] ?? null];
            
        } catch (\Exception $e) {
            error_log("SMS send failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Make API call to MSG91
     */
    private function callAPI($url, $payload)
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'authkey: ' . $this->authKey,
            'accept: application/json',
            'content-type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception("CURL Error: " . $error);
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['type']) && $result['type'] === 'error') {
            throw new \Exception("MSG91 Error: " . ($result['message'] ?? 'Unknown error'));
        }
        
        return $result;
    }
    
    /**
     * Generate random OTP
     */
    private function generateOTP($length = 6)
    {
        return str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }
    
    /**
     * Clean and format mobile number
     */
    private function cleanMobileNumber($mobile)
    {
        // Remove all non-numeric characters
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        
        // Add country code if not present
        if (strlen($mobile) === 10) {
            $mobile = '91' . $mobile;
        }
        
        return $mobile;
    }
    
    /**
     * Save OTP to database
     */
    private function saveOTP($mobile, $otp)
    {
        // Invalidate old OTPs
        $this->db->query(
            "UPDATE notifications_unified SET status = 'expired' WHERE mobile = ? AND status = 'pending'",
            [$mobile]
        );
        
        // Save new OTP
        $this->db->insert('notifications_unified', [
            
            'tenant_id' => TenantContext::getId(),
            'mobile' => $mobile,
            'otp' => $otp,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes'))
        ]);
    }
    
    /**
     * Log SMS to database
     */
    private function logSMS($mobile, $type, $message, $status)
    {
        try {
            $this->db->insert('notifications_unified', [
                
                'tenant_id' => TenantContext::getId(),
                'mobile' => $mobile,
                'type' => $type,
                'message' => substr($message, 0, 500),
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            error_log("SMS logging failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get SMS statistics
     */
    public function getStats($days = 30)
    {
        return [
            'total_sent' => $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM notifications_unified WHERE status = 'success' AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$days]
            )['count'] ?? 0,
            'total_failed' => $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM notifications_unified WHERE status = 'error' AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$days]
            )['count'] ?? 0,
            'otp_sent' => $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM notifications_unified WHERE type = 'OTP' AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$days]
            )['count'] ?? 0
        ];
    }
}
