<?php

namespace App\Services;

use App\Core\Database\Database;
use Exception;

/**
 * CampaignService - Handle campaign and notification management
 * 
 * This service manages admin campaigns, offers, and user notifications
 * for the APS Dream Home platform.
 */
class CampaignService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new campaign/offer
     */
    public function createCampaign($data)
    {
        try {
            $query = "INSERT INTO campaigns (title, description, type, target_audience, start_date, end_date, is_active, created_by, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $params = [
                $data['title'] ?? '',
                $data['description'] ?? '',
                $data['type'] ?? 'general',
                $data['target_audience'] ?? 'all',
                $data['start_date'] ?? date('Y-m-d'),
                $data['end_date'] ?? date('Y-m-d', strtotime('+30 days')),
                $data['is_active'] ?? 1,
                $data['created_by'] ?? 0,
                date('Y-m-d H:i:s')
            ];

            $this->db->execute($query, $params);
            return $this->db->getLastInsertId();
        } catch (Exception $e) {
            error_log("Error creating campaign: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active campaigns for display
     */
    public function getActiveCampaigns($targetAudience = 'all')
    {
        try {
            $query = "SELECT * FROM campaigns 
                     WHERE is_active = 1 
                     AND start_date <= CURDATE() 
                     AND end_date >= CURDATE() 
                     AND (target_audience = ? OR target_audience = 'all')
                     ORDER BY created_at DESC";

            return $this->db->fetchAll($query, [$targetAudience]);
        } catch (Exception $e) {
            error_log("Error getting campaigns: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create user notification
     */
    public function createNotification($userId, $title, $message, $type = 'info', $campaignId = null)
    {
        try {
            $query = "INSERT INTO notifications (user_id, title, message, type, campaign_id, is_read, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";

            $params = [
                $userId,
                $title,
                $message,
                $type,
                $campaignId,
                0, // unread
                date('Y-m-d H:i:s')
            ];

            $this->db->execute($query, $params);
            return $this->db->getLastInsertId();
        } catch (Exception $e) {
            error_log("Error creating notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 10, $unreadOnly = false)
    {
        try {
            $query = "SELECT n.*, c.title as campaign_title 
                     FROM notifications n 
                     LEFT JOIN campaigns c ON n.campaign_id = c.id 
                     WHERE n.user_id = ?";

            if ($unreadOnly) {
                $query .= " AND n.is_read = 0";
            }

            $query .= " ORDER BY n.created_at DESC LIMIT ?";

            return $this->db->fetchAll($query, [$userId, $limit]);
        } catch (Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead($notificationId, $userId)
    {
        try {
            $query = "UPDATE notifications SET is_read = 1, read_at = ? 
                     WHERE id = ? AND user_id = ?";

            $this->db->execute($query, [date('Y-m-d H:i:s'), $notificationId, $userId]);
            return true;
        } catch (Exception $e) {
            error_log("Error marking notification read: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send campaign to target audience
     */
    public function sendCampaignToAudience($campaignId, $targetAudience)
    {
        try {
            $campaign = $this->getCampaignById($campaignId);
            if (!$campaign) {
                return false;
            }

            // Get target users
            $users = $this->getUsersByAudience($targetAudience);

            $sentCount = 0;
            foreach ($users as $user) {
                $success = $this->createNotification(
                    $user['id'],
                    $campaign['title'],
                    $campaign['description'],
                    'campaign',
                    $campaignId
                );

                if ($success) {
                    $sentCount++;
                }
            }

            return $sentCount;
        } catch (Exception $e) {
            error_log("Error sending campaign: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get campaign by ID
     */
    private function getCampaignById($campaignId)
    {
        try {
            $query = "SELECT * FROM campaigns WHERE id = ?";
            return $this->db->fetch($query, [$campaignId]);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get users by target audience
     */
    private function getUsersByAudience($targetAudience)
    {
        try {
            switch ($targetAudience) {
                case 'all':
                    $query = "SELECT id FROM users WHERE status = 'active'";
                    break;
                case 'customers':
                    $query = "SELECT id FROM users WHERE role = 'customer' AND status = 'active'";
                    break;
                case 'agents':
                    $query = "SELECT id FROM users WHERE role IN ('agent', 'associate') AND status = 'active'";
                    break;
                case 'employees':
                    $query = "SELECT id FROM users WHERE role = 'employee' AND status = 'active'";
                    break;
                default:
                    return [];
            }

            return $this->db->fetchAll($query);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats($userId = null)
    {
        try {
            if ($userId) {
                $query = "SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
                            SUM(CASE WHEN type = 'campaign' THEN 1 ELSE 0 END) as campaigns,
                            SUM(CASE WHEN type = 'system' THEN 1 ELSE 0 END) as system
                         FROM notifications WHERE user_id = ?";

                return $this->db->fetch($query, [$userId]);
            } else {
                $query = "SELECT 
                            COUNT(*) as total_notifications,
                            COUNT(DISTINCT user_id) as total_users,
                            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as total_unread
                         FROM notifications";

                return $this->db->fetch($query);
            }
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get popup content for current page
     */
    public function getPopupContent($page, $userRole = 'guest')
    {
        try {
            $query = "SELECT p.* FROM popups p
                     INNER JOIN campaigns c ON p.campaign_id = c.id
                     WHERE p.page = ? OR p.page = 'all'
                     AND (p.target_role = ? OR p.target_role = 'all')
                     AND c.is_active = 1
                     AND c.start_date <= CURDATE()
                     AND c.end_date >= CURDATE()
                     ORDER BY p.priority DESC, p.created_at DESC
                     LIMIT 1";

            return $this->db->fetch($query, [$page, $userRole]);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Dismiss popup for user
     */
    public function dismissPopup($userId, $popupId)
    {
        try {
            $query = "INSERT INTO popup_dismissals (user_id, popup_id, dismissed_at) 
                     VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE dismissed_at = ?";

            $this->db->execute($query, [$userId, $popupId, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
