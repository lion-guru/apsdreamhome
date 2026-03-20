<?php

namespace App\Services;

use App\Core\Database\Database;
use Exception;

class CampaignDeliveryService
{
    private $db;
    private $notificationService;
    private $otpService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->notificationService = new NotificationService();
        $this->otpService = new OTPService();
    }

    /**
     * Deliver campaign to target users
     */
    public function deliverCampaign($campaignId, $deliveryTypes = ['notification', 'popup', 'email'])
    {
        try {
            // Get campaign details
            $campaign = $this->getCampaign($campaignId);
            if (!$campaign) {
                throw new Exception("Campaign not found");
            }

            // Get target users
            $targetUsers = $this->getTargetUsers($campaign);
            
            if (empty($targetUsers)) {
                return [
                    'success' => true,
                    'message' => 'No target users found for this campaign',
                    'delivered' => 0
                ];
            }

            $totalDelivered = 0;
            $deliveryResults = [];

            foreach ($deliveryTypes as $deliveryType) {
                $delivered = $this->executeDelivery($campaign, $targetUsers, $deliveryType);
                $totalDelivered += $delivered;
                $deliveryResults[$deliveryType] = $delivered;
            }

            // Update campaign status
            $this->updateCampaignStatus($campaignId, 'active', $totalDelivered);

            return [
                'success' => true,
                'message' => "Campaign delivered successfully",
                'total_delivered' => $totalDelivered,
                'delivery_results' => $deliveryResults,
                'target_users' => count($targetUsers)
            ];

        } catch (Exception $e) {
            error_log("Campaign delivery error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to deliver campaign: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Execute specific delivery type
     */
    private function executeDelivery($campaign, $targetUsers, $deliveryType)
    {
        $delivered = 0;

        foreach ($targetUsers as $user) {
            try {
                $deliveryId = $this->createDeliveryRecord($campaign['campaign_id'], $user['id'], $deliveryType);

                switch ($deliveryType) {
                    case 'notification':
                        if ($this->deliverNotification($campaign, $user)) {
                            $this->updateDeliveryStatus($deliveryId, 'sent');
                            $delivered++;
                        } else {
                            $this->updateDeliveryStatus($deliveryId, 'failed', 'Notification delivery failed');
                        }
                        break;

                    case 'popup':
                        if ($this->deliverPopup($campaign, $user)) {
                            $this->updateDeliveryStatus($deliveryId, 'sent');
                            $delivered++;
                        } else {
                            $this->updateDeliveryStatus($deliveryId, 'failed', 'Popup delivery failed');
                        }
                        break;

                    case 'email':
                        if ($this->deliverEmail($campaign, $user)) {
                            $this->updateDeliveryStatus($deliveryId, 'sent');
                            $delivered++;
                        } else {
                            $this->updateDeliveryStatus($deliveryId, 'failed', 'Email delivery failed');
                        }
                        break;

                    case 'sms':
                        if ($this->deliverSMS($campaign, $user)) {
                            $this->updateDeliveryStatus($deliveryId, 'sent');
                            $delivered++;
                        } else {
                            $this->updateDeliveryStatus($deliveryId, 'failed', 'SMS delivery failed');
                        }
                        break;

                    case 'whatsapp':
                        if ($this->deliverWhatsApp($campaign, $user)) {
                            $this->updateDeliveryStatus($deliveryId, 'sent');
                            $delivered++;
                        } else {
                            $this->updateDeliveryStatus($deliveryId, 'failed', 'WhatsApp delivery failed');
                        }
                        break;

                    case 'push':
                        if ($this->deliverPush($campaign, $user)) {
                            $this->updateDeliveryStatus($deliveryId, 'sent');
                            $delivered++;
                        } else {
                            $this->updateDeliveryStatus($deliveryId, 'failed', 'Push notification failed');
                        }
                        break;
                }

            } catch (Exception $e) {
                error_log("Delivery error for user {$user['id']}: " . $e->getMessage());
                $this->updateDeliveryStatus($deliveryId, 'failed', $e->getMessage());
            }
        }

        return $delivered;
    }

    /**
     * Deliver notification
     */
    private function deliverNotification($campaign, $user)
    {
        $notificationData = [
            'title' => $campaign['name'],
            'message' => $campaign['description'],
            'type' => 'campaign',
            'target_audience' => 'all',
            'user_id' => $user['id'],
            'campaign_id' => $campaign['campaign_id']
        ];

        return $this->notificationService->createNotification($notificationData) !== false;
    }

    /**
     * Deliver popup
     */
    private function deliverPopup($campaign, $user)
    {
        $popupData = [
            'title' => $campaign['name'],
            'content' => $campaign['description'],
            'type' => 'promotion',
            'target_audience' => 'all',
            'pages' => 'home',
            'position' => 'center',
            'show_delay' => 2000,
            'auto_close' => 10000,
            'campaign_id' => $campaign['campaign_id']
        ];

        return $this->notificationService->createPopup($popupData) !== false;
    }

    /**
     * Deliver email
     */
    private function deliverEmail($campaign, $user)
    {
        $subject = $campaign['name'] . ' - Special Offer from APS Dream Home';
        $message = $this->generateEmailContent($campaign, $user);

        $headers = "From: noreply@apsdreamhome.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        return mail($user['email'], $subject, $message, $headers);
    }

    /**
     * Deliver SMS
     */
    private function deliverSMS($campaign, $user)
    {
        $message = "APS Dream Home: {$campaign['name']}. {$campaign['description']}. Call us at +91-9876543210 for details.";
        
        // For demo purposes, just log the message
        error_log("SMS to {$user['phone']}: $message");
        
        // In production, integrate with SMS gateway
        return true;
    }

    /**
     * Deliver WhatsApp
     */
    private function deliverWhatsApp($campaign, $user)
    {
        $message = "*{$campaign['name']}*\n\n{$campaign['description']}\n\n📞 Call us: +91-9876543210\n🌐 Visit: www.apsdreamhome.com";
        
        // For demo purposes, just log the message
        error_log("WhatsApp to {$user['phone']}: $message");
        
        // In production, integrate with WhatsApp Business API
        return true;
    }

    /**
     * Deliver push notification
     */
    private function deliverPush($campaign, $user)
    {
        // For demo purposes, just log the push notification
        error_log("Push notification to user {$user['id']}: {$campaign['name']} - {$campaign['description']}");
        
        // In production, integrate with Firebase Cloud Messaging or similar
        return true;
    }

    /**
     * Generate email content
     */
    private function generateEmailContent($campaign, $user)
    {
        $template = "
        <html>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;'>
            <div style='background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #667eea; text-align: center; margin-bottom: 30px;'>APS Dream Home</h2>
                <h3 style='color: #333;'>{$campaign['name']}</h3>
                <p style='font-size: 16px; color: #666; line-height: 1.6;'>{$campaign['description']}</p>";
        
        if (!empty($campaign['budget'])) {
            $template .= "<p style='font-size: 16px; color: #333;'><strong>Budget:</strong> ₹" . number_format($campaign['budget']) . "</p>";
        }
        
        $template .= "
                <div style='text-align: center; margin-top: 30px;'>
                    <a href='http://localhost/apsdreamhome/campaigns/{$campaign['campaign_id']}' 
                       style='background-color: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                        Learn More
                    </a>
                </div>
                <p style='font-size: 12px; color: #999; text-align: center; margin-top: 30px;'>
                    This email was sent to {$user['email']} because you subscribed to APS Dream Home updates.
                </p>
            </div>
        </body>
        </html>";

        return $template;
    }

    /**
     * Get campaign details
     */
    private function getCampaign($campaignId)
    {
        $query = "SELECT * FROM campaigns WHERE campaign_id = ?";
        return $this->db->fetch($query, [$campaignId]);
    }

    /**
     * Get target users for campaign
     */
    private function getTargetUsers($campaign)
    {
        $targetAudience = $campaign['target_audience'];
        
        switch ($targetAudience) {
            case 'all':
                $query = "SELECT * FROM users WHERE status = 'active'";
                return $this->db->fetchAll($query);
                
            case 'customers':
                $query = "SELECT * FROM users WHERE role = 'customer' AND status = 'active'";
                return $this->db->fetchAll($query);
                
            case 'agents':
                $query = "SELECT * FROM users WHERE role = 'associate' AND status = 'active'";
                return $this->db->fetchAll($query);
                
            case 'employees':
                $query = "SELECT * FROM users WHERE role = 'employee' AND status = 'active'";
                return $this->db->fetchAll($query);
                
            case 'admin':
                $query = "SELECT * FROM users WHERE role IN ('admin', 'super_admin') AND status = 'active'";
                return $this->db->fetchAll($query);
                
            default:
                return [];
        }
    }

    /**
     * Create delivery record
     */
    private function createDeliveryRecord($campaignId, $userId, $deliveryType)
    {
        $query = "INSERT INTO campaign_deliveries (campaign_id, user_id, delivery_type, status, sent_at) VALUES (?, ?, ?, 'pending', NOW())";
        $this->db->execute($query, [$campaignId, $userId, $deliveryType]);
        return $this->db->getLastInsertId();
    }

    /**
     * Update delivery status
     */
    private function updateDeliveryStatus($deliveryId, $status, $errorMessage = null)
    {
        $query = "UPDATE campaign_deliveries SET status = ?, error_message = ? WHERE id = ?";
        
        $params = [$status];
        if ($errorMessage) {
            $params[] = $errorMessage;
        } else {
            $params[] = null;
        }
        $params[] = $deliveryId;
        
        $this->db->execute($query, $params);
    }

    /**
     * Update campaign status
     */
    private function updateCampaignStatus($campaignId, $status, $deliveredCount = 0)
    {
        $query = "UPDATE campaigns SET status = ?, delivered_count = ?, updated_at = NOW() WHERE campaign_id = ?";
        $this->db->execute($query, [$status, $deliveredCount, $campaignId]);
    }

    /**
     * Get campaign delivery statistics
     */
    public function getCampaignStats($campaignId)
    {
        $query = "SELECT 
                    delivery_type,
                    status,
                    COUNT(*) as count
                FROM campaign_deliveries 
                WHERE campaign_id = ?
                GROUP BY delivery_type, status
                ORDER BY delivery_type, status";
        
        return $this->db->fetchAll($query, [$campaignId]);
    }

    /**
     * Track delivery engagement
     */
    public function trackEngagement($deliveryId, $action)
    {
        $timestampField = '';
        
        switch ($action) {
            case 'opened':
                $timestampField = 'opened_at';
                break;
            case 'clicked':
                $timestampField = 'clicked_at';
                break;
            case 'converted':
                $timestampField = 'converted_at';
                break;
            default:
                return false;
        }

        $query = "UPDATE campaign_deliveries SET {$timestampField} = NOW(), status = ? WHERE id = ?";
        
        $status = 'sent';
        if ($action === 'converted') {
            $status = 'converted';
        } elseif ($action === 'clicked') {
            $status = 'clicked';
        } elseif ($action === 'opened') {
            $status = 'opened';
        }
        
        return $this->db->execute($query, [$status, $deliveryId]);
    }

    /**
     * Get delivery analytics
     */
    public function getDeliveryAnalytics($startDate = null, $endDate = null)
    {
        $dateCondition = "";
        $params = [];

        if ($startDate && $endDate) {
            $dateCondition = "WHERE cd.sent_at BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }

        $query = "SELECT 
                    c.name as campaign_name,
                    cd.delivery_type,
                    cd.status,
                    COUNT(*) as count,
                    AVG(CASE WHEN cd.opened_at IS NOT NULL THEN 1 ELSE 0 END) as open_rate,
                    AVG(CASE WHEN cd.clicked_at IS NOT NULL THEN 1 ELSE 0 END) as click_rate,
                    AVG(CASE WHEN cd.converted_at IS NOT NULL THEN 1 ELSE 0 END) as conversion_rate
                FROM campaign_deliveries cd
                JOIN campaigns c ON cd.campaign_id = c.campaign_id
                $dateCondition
                GROUP BY c.campaign_id, cd.delivery_type, cd.status
                ORDER BY c.name, cd.delivery_type";
        
        return $this->db->fetchAll($query, $params);
    }

    /**
     * Schedule campaign delivery
     */
    public function scheduleDelivery($campaignId, $scheduledTime, $deliveryTypes = ['notification', 'popup', 'email'])
    {
        try {
            // Store delivery schedule
            $query = "INSERT INTO campaign_delivery_schedule (campaign_id, scheduled_time, delivery_types, status) VALUES (?, ?, ?, 'scheduled')";
            $this->db->execute($query, [$campaignId, $scheduledTime, json_encode($deliveryTypes)]);
            
            return [
                'success' => true,
                'message' => 'Campaign delivery scheduled successfully',
                'schedule_id' => $this->db->getLastInsertId()
            ];
            
        } catch (Exception $e) {
            error_log("Campaign scheduling error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to schedule campaign delivery'
            ];
        }
    }

    /**
     * Process scheduled deliveries
     */
    public function processScheduledDeliveries()
    {
        $query = "SELECT * FROM campaign_delivery_schedule WHERE scheduled_time <= NOW() AND status = 'scheduled'";
        $scheduled = $this->db->fetchAll($query);
        
        $processed = 0;
        
        foreach ($scheduled as $schedule) {
            try {
                $deliveryTypes = json_decode($schedule['delivery_types'], true);
                $result = $this->deliverCampaign($schedule['campaign_id'], $deliveryTypes);
                
                if ($result['success']) {
                    $this->updateScheduleStatus($schedule['id'], 'completed');
                    $processed++;
                } else {
                    $this->updateScheduleStatus($schedule['id'], 'failed');
                }
                
            } catch (Exception $e) {
                error_log("Scheduled delivery processing error: " . $e->getMessage());
                $this->updateScheduleStatus($schedule['id'], 'failed');
            }
        }
        
        return $processed;
    }

    /**
     * Update schedule status
     */
    private function updateScheduleStatus($scheduleId, $status)
    {
        $query = "UPDATE campaign_delivery_schedule SET status = ?, processed_at = NOW() WHERE id = ?";
        return $this->db->execute($query, [$status, $scheduleId]);
    }
}