<?php
/**
 * Registration Followup Service
 * Automated WhatsApp/Email/SMS reminders for abandoned registrations
 */

namespace App\Services;

use App\Core\Database\Database;

use \App\Traits\ServiceTenantTrait;

class RegistrationFollowupService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Send WhatsApp reminder for incomplete registration
     */
    public function sendWhatsAppReminder($sessionId)
    {
        try {
            $session = $this->getSession($sessionId);
            if (!$session || !$session['phone']) {
                return false;
            }
            
            $phone = $session['phone'];
            $completionPct = $session['profile_completion_pct'] ?? 0;
            
            // Choose message based on completion
            if ($completionPct === 0) {
                $message = $this->getWhatsAppMessage('otp_sent', $phone);
            } elseif ($completionPct < 50) {
                $message = $this->getWhatsAppMessage('half_complete', $phone);
            } else {
                $message = $this->getWhatsAppMessage('almost_there', $phone);
            }
            
            // Send via WhatsApp service
            $whatsappService = new \App\Services\Communication\WhatsAppService();
            $result = $whatsappService->sendTextMessage($phone, $message);
            
            // Log reminder
            $this->logReminder($sessionId, 'whatsapp', $message, $result ? 'sent' : 'failed');
            
            // Update session
            $this->db->query(
                "UPDATE smart_registration_sessions SET followup_whatsapp_sent = 1, followup_count = followup_count + 1, last_followup_at = NOW(), updated_at = NOW() WHERE id = ?" . $this->tenantSql(),
                array_merge([$sessionId], $this->tenantId() > 1 ? [$this->tenantId()] : [])
            );
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("WhatsApp reminder failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send Email reminder for incomplete registration
     */
    public function sendEmailReminder($sessionId)
    {
        try {
            $session = $this->getSession($sessionId);
            if (!$session || !$session['email']) {
                return false;
            }
            
            $email = $session['email'];
            $phone = $session['phone'];
            $completionPct = $session['profile_completion_pct'] ?? 0;
            
            // Choose subject and body based on completion
            if ($completionPct === 0) {
                $subject = "Complete your APS Dream Home registration";
                $body = $this->getEmailBody('otp_sent', $phone);
            } elseif ($completionPct < 50) {
                $subject = "You're almost there! Complete your profile";
                $body = $this->getEmailBody('half_complete', $phone);
            } else {
                $subject = "Last step! Complete your profile for rewards";
                $body = $this->getEmailBody('almost_there', $phone);
            }
            
            // Send email
            $headers = "From: noreply@apsdreamhome.com\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            
            $result = mail($email, $subject, $body, $headers);
            
            // Log reminder
            $this->logReminder($sessionId, 'email', $subject, $result ? 'sent' : 'failed');
            
            // Update session
            $this->db->query(
                "UPDATE smart_registration_sessions SET followup_email_sent = 1, followup_count = followup_count + 1, last_followup_at = NOW(), updated_at = NOW() WHERE id = ?" . $this->tenantSql(),
                array_merge([$sessionId], $this->tenantId() > 1 ? [$this->tenantId()] : [])
            );
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Email reminder failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send SMS reminder for incomplete registration
     */
    public function sendSmsReminder($sessionId)
    {
        try {
            $session = $this->getSession($sessionId);
            if (!$session || !$session['phone']) {
                return false;
            }
            
            $phone = $session['phone'];
            $completionPct = $session['profile_completion_pct'] ?? 0;
            
            // Choose message based on completion
            if ($completionPct === 0) {
                $message = $this->getSmsMessage('otp_sent', $phone);
            } elseif ($completionPct < 50) {
                $message = $this->getSmsMessage('half_complete', $phone);
            } else {
                $message = $this->getSmsMessage('almost_there', $phone);
            }
            
            // Send via SMS service
            $smsService = new \App\Services\Communication\SmsService();
            $result = $smsService->sendOTP($phone);
            
            // Log reminder
            $this->logReminder($sessionId, 'sms', $message, $result ? 'sent' : 'failed');
            
            // Update session
            $this->db->query(
                "UPDATE smart_registration_sessions SET followup_sms_sent = 1, followup_count = followup_count + 1, last_followup_at = NOW(), updated_at = NOW() WHERE id = ?" . $this->tenantSql(),
                array_merge([$sessionId], $this->tenantId() > 1 ? [$this->tenantId()] : [])
            );
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("SMS reminder failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Process all abandoned registrations (for cron job)
     */
    public function processAbandonedRegistrations()
    {
        try {
            // Find registrations abandoned in last 7 days
            $abandoned = $this->db->fetchAll(
                "SELECT * FROM smart_registration_sessions 
                 WHERE registration_status IN ('otp_sent', 'account_created', 'profile_incomplete')
                 AND abandoned_at IS NOT NULL
                 AND abandoned_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
                 AND followup_count < 3" . $this->tenantSql() . "
                 ORDER BY abandoned_at ASC
                 LIMIT 100"
            );
            
            $results = [
                'processed' => 0,
                'whatsapp_sent' => 0,
                'email_sent' => 0,
                'sms_sent' => 0,
                'failed' => 0
            ];
            
            foreach ($abandoned as $session) {
                $results['processed']++;
                
                // Determine which channel to use based on preference and history
                $channel = $this->determineBestChannel($session);
                
                switch ($channel) {
                    case 'whatsapp':
                        if ($this->sendWhatsAppReminder($session['id'])) {
                            $results['whatsapp_sent']++;
                        } else {
                            $results['failed']++;
                        }
                        break;
                        
                    case 'email':
                        if ($this->sendEmailReminder($session['id'])) {
                            $results['email_sent']++;
                        } else {
                            $results['failed']++;
                        }
                        break;
                        
                    case 'sms':
                        if ($this->sendSmsReminder($session['id'])) {
                            $results['sms_sent']++;
                        } else {
                            $results['failed']++;
                        }
                        break;
                }
                
                // Rate limiting: 1 second between sends
                sleep(1);
            }
            
            return $results;
            
        } catch (\Exception $e) {
            error_log("Process abandoned failed: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Get statistics for admin dashboard
     */
    public function getStats()
    {
        try {
            $stats = $this->db->fetchOne(
                "SELECT 
                    COUNT(*) as total_sessions,
                    SUM(CASE WHEN registration_status = 'pending_otp' THEN 1 ELSE 0 END) as pending_otp,
                    SUM(CASE WHEN registration_status = 'otp_sent' THEN 1 ELSE 0 END) as otp_sent,
                    SUM(CASE WHEN registration_status = 'otp_verified' THEN 1 ELSE 0 END) as otp_verified,
                    SUM(CASE WHEN registration_status = 'account_created' THEN 1 ELSE 0 END) as account_created,
                    SUM(CASE WHEN registration_status = 'profile_incomplete' THEN 1 ELSE 0 END) as profile_incomplete,
                    SUM(CASE WHEN registration_status = 'profile_complete' THEN 1 ELSE 0 END) as profile_complete,
                    SUM(CASE WHEN registration_status = 'abandoned' THEN 1 ELSE 0 END) as abandoned,
                    AVG(profile_completion_pct) as avg_completion,
                    SUM(followup_whatsapp_sent) as whatsapp_reminders,
                    SUM(followup_email_sent) as email_reminders,
                    SUM(followup_sms_sent) as sms_reminders
                FROM smart_registration_sessions
                WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)" . $this->tenantSql()
            );
            
            return $stats;
            
        } catch (\Exception $e) {
            error_log("Get stats failed: " . $e->getMessage());
            return [];
        }
    }
    
    // ==================== PRIVATE HELPER METHODS ====================
    
    private function getSession($sessionId)
    {
        return $this->db->fetchOne(
            "SELECT * FROM smart_registration_sessions WHERE id = ?" . $this->tenantSql() . " LIMIT 1",
            array_merge([$sessionId], $this->tenantId() > 1 ? [$this->tenantId()] : [])
        );
    }
    
    private function logReminder($sessionId, $channel, $content, $status)
    {
        $insertData = array_merge([
            'session_id' => $sessionId,
            'reminder_type' => $channel,
            'message_content' => $content,
            'sent_status' => $status,
            'sent_at' => $status === 'sent' ? date('Y-m-d H:i:s') : null,
            'created_at' => date('Y-m-d H:i:s')
        ], $this->tenantInsertData());
        
        $cols = implode(', ', array_keys($insertData));
        $placeholders = implode(', ', array_fill(0, count($insertData), '?'));
        $this->db->insert('smart_registration_reminders', $insertData);
    }
    
    private function determineBestChannel($session)
    {
        // Priority: WhatsApp > Email > SMS
        if (!$session['followup_whatsapp_sent'] && $session['phone']) {
            return 'whatsapp';
        } elseif (!$session['followup_email_sent'] && $session['email']) {
            return 'email';
        } elseif (!$session['followup_sms_sent'] && $session['phone']) {
            return 'sms';
        }
        
        // Default to WhatsApp
        return 'whatsapp';
    }
    
    private function getWhatsAppMessage($type, $phone)
    {
        $messages = [
            'otp_sent' => "Hi! 👋\n\nYou started registering at APS Dream Home but haven't completed it yet.\n\nJust *1 more minute* to finish and unlock:\n✅ Save favorite properties\n✅ Get personalized recommendations\n✅ Earn ₹500 referral bonus\n\nComplete now: " . BASE_URL . "/register/smart\n\nNeed help? Reply to this message!",
            
            'half_complete' => "Hi! 👋\n\nYour APS Dream Home profile is *50% complete*!\n\nYou're missing:\n📝 Name & Email\n🏙️ City & Budget\n\nComplete now to get:\n✅ Save favorite properties\n✅ Get price alerts\n✅ Priority support\n\nFinish now: " . BASE_URL . "/register/smart\n\nIt takes just 30 seconds!",
            
            'almost_there' => "Hi! 🎉\n\nYou're *so close* to completing your APS Dream Home profile!\n\nJust *2 more fields* and you're done:\n📝 Name\n🏙️ City\n\nComplete now and get:\n✅ ₹500 bonus points\n✅ Save unlimited favorites\n✅ Get exclusive deals\n\nFinish now: " . BASE_URL . "/register/smart\n\nDon't miss out! 🏠"
        ];
        
        return $messages[$type] ?? $messages['otp_sent'];
    }
    
    private function getEmailBody($type, $phone)
    {
        $title = match($type) {
            'otp_sent' => 'Complete your registration',
            'half_complete' => "You're halfway there!",
            'almost_there' => 'Just one more step!',
            default => 'Complete your registration'
        };
        
        $benefits = match($type) {
            'otp_sent' => '<li>Save favorite properties</li><li>Get personalized recommendations</li><li>Earn ₹500 referral bonus</li>',
            'half_complete' => '<li>Save favorite properties</li><li>Get price alerts</li><li>Priority customer support</li>',
            'almost_there' => '<li>₹500 bonus points</li><li>Save unlimited favorites</li><li>Get exclusive deals</li>',
            default => '<li>Save favorite properties</li><li>Get personalized recommendations</li>'
        };
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #0d9488, #0f766e); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
                .btn { display: inline-block; background: linear-gradient(135deg, #0d9488, #0f766e); color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; }
                .benefits { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .benefits ul { list-style: none; padding: 0; }
                .benefits li { padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🏠 APS Dream Home</h1>
                    <p>$title</p>
                </div>
                <div class='content'>
                    <p>Hello!</p>
                    <p>You started your journey with APS Dream Home but haven't completed your profile yet.</p>
                    
                    <div class='benefits'>
                        <h3>Complete your profile to unlock:</h3>
                        <ul>$benefits</ul>
                    </div>
                    
                    <p style='text-align: center;'>
                        <a href='" . BASE_URL . "/register/smart' class='btn'>Complete Registration →</a>
                    </p>
                    
                    <p style='color: #666; font-size: 14px; margin-top: 20px;'>
                        It takes less than a minute! Your progress is saved.
                    </p>
                </div>
                <div class='footer'>
                    <p>© " . date('Y') . " APS Dream Home. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getSmsMessage($type, $phone)
    {
        $messages = [
            'otp_sent' => "APS Dream Home: You started registering but didn't finish! Complete in 1 min to save favorites & earn ₹500. Link: " . BASE_URL . "/register/smart",
            
            'half_complete' => "APS Dream Home: Your profile is 50% done! Just add name & city to unlock all features. Complete now: " . BASE_URL . "/register/smart",
            
            'almost_there' => "APS Dream Home: Almost there! Just 2 more fields and you're done. Complete for ₹500 bonus: " . BASE_URL . "/register/smart"
        ];
        
        return $messages[$type] ?? $messages['otp_sent'];
    }
}
?>
