<?php

namespace App\Services;

use App\Core\Database\Database;
use Exception;

class NotificationService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new notification
     */
    public function createNotification($data)
    {
        try {
            $query = "INSERT INTO notifications (user_id, title, message, type, target_audience, campaign_id) 
                     VALUES (?, ?, ?, ?, ?, ?)";
            
            $params = [
                $data['user_id'] ?? null,
                $data['title'] ?? '',
                $data['message'] ?? '',
                $data['type'] ?? 'info',
                $data['target_audience'] ?? 'all',
                $data['campaign_id'] ?? null
            ];
            
            $this->db->execute($query, $params);
            return $this->db->getLastInsertId();
            
        } catch (Exception $e) {
            error_log("Error creating notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get notifications for a user
     */
    public function getUserNotifications($userId, $filters = [])
    {
        try {
            $whereConditions = ["(user_id = ? OR user_id IS NULL)"];
            $whereParams = [$userId];
            
            if (!empty($filters['status'])) {
                $whereConditions[] = "status = ?";
                $whereParams[] = $filters['status'];
            }
            
            if (!empty($filters['type'])) {
                $whereConditions[] = "type = ?";
                $whereParams[] = $filters['type'];
            }
            
            $limit = !empty($filters['limit']) ? "LIMIT " . $filters['limit'] : "LIMIT 10";
            
            $query = "SELECT * FROM notifications 
                WHERE " . implode(' AND ', $whereConditions) . " 
                ORDER BY created_at DESC $limit";
            
            return $this->db->fetchAll($query, $whereParams);
            
        } catch (Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId)
    {
        try {
            $query = "UPDATE notifications SET status = 'read', read_at = NOW() 
                     WHERE id = ? AND user_id = ?";
            
            $this->db->execute($query, [$notificationId, $userId]);
            return true;
            
        } catch (Exception $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unread notification count for user
     */
    public function getUnreadCount($userId)
    {
        try {
            $query = "SELECT COUNT(*) as count FROM notifications 
                WHERE (user_id = ? OR user_id IS NULL) AND status = 'unread'";
            
            $result = $this->db->fetch($query, [$userId]);
            return $result['count'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Error getting unread count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Create a new popup
     */
    public function createPopup($data)
    {
        try {
            $query = "INSERT INTO popups (campaign_id, title, content, type, target_audience, pages, position, show_delay, auto_close) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $data['campaign_id'] ?? null,
                $data['title'] ?? '',
                $data['content'] ?? '',
                $data['type'] ?? 'info',
                $data['target_audience'] ?? 'all',
                $data['pages'] ?? 'all',
                $data['position'] ?? 'center',
                $data['show_delay'] ?? 0,
                $data['auto_close'] ?? 0
            ];
            
            $this->db->execute($query, $params);
            return $this->db->getLastInsertId();
            
        } catch (Exception $e) {
            error_log("Error creating popup: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active popups for a page and user
     */
    public function getActivePopups($page, $userRole = 'customer')
    {
        try {
            $query = "SELECT * FROM popups 
                WHERE status = 'active' 
                AND (pages = 'all' OR pages LIKE ?)
                AND (target_audience = 'all' OR target_audience = ?)
                AND (start_date <= NOW() OR start_date IS NULL)
                AND (end_date >= NOW() OR end_date IS NULL)
                ORDER BY show_delay ASC";
            
            $pagePattern = "%$page%";
            return $this->db->fetchAll($query, [$pagePattern, $userRole]);
            
        } catch (Exception $e) {
            error_log("Error getting popups: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Dismiss a popup
     */
    public function dismissPopup($popupId, $userId = null, $sessionId = null)
    {
        try {
            $query = "INSERT INTO popup_dismissals (popup_id, user_id, session_id, ip_address) 
                     VALUES (?, ?, ?, ?)";
            
            $params = [
                $popupId,
                $userId,
                $sessionId ?? session_id(),
                $_SERVER['REMOTE_ADDR'] ?? null
            ];
            
            $this->db->execute($query, $params);
            return true;
            
        } catch (Exception $e) {
            error_log("Error dismissing popup: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if popup was dismissed by user
     */
    public function isPopupDismissed($popupId, $userId = null, $sessionId = null)
    {
        try {
            $whereCondition = "popup_id = ?";
            $params = [$popupId];
            
            if ($userId) {
                $whereCondition .= " AND user_id = ?";
                $params[] = $userId;
            } elseif ($sessionId) {
                $whereCondition .= " AND session_id = ?";
                $params[] = $sessionId;
            }
            
            $query = "SELECT COUNT(*) as count FROM popup_dismissals WHERE $whereCondition";
            $result = $this->db->fetch($query, $params);
            
            return $result['count'] > 0;
            
        } catch (Exception $e) {
            error_log("Error checking popup dismissal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notifications to target audience
     */
    public function sendToTargetAudience($notificationData)
    {
        try {
            $targetAudience = $notificationData['target_audience'] ?? 'all';
            $userIds = [];
            
            switch ($targetAudience) {
                case 'customers':
                    $userIds = $this->getCustomerIds();
                    break;
                case 'agents':
                    $userIds = $this->getAgentIds();
                    break;
                case 'employees':
                    $userIds = $this->getEmployeeIds();
                    break;
                case 'admin':
                    $userIds = $this->getAdminIds();
                    break;
                case 'all':
                default:
                    // Create one notification for all users (user_id = NULL)
                    $this->createNotification($notificationData);
                    return true;
            }
            
            // Create individual notifications for each user
            foreach ($userIds as $userId) {
                $notificationData['user_id'] = $userId;
                $this->createNotification($notificationData);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error sending to target audience: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get customer IDs
     */
    private function getCustomerIds()
    {
        try {
            $result = $this->db->fetchAll("SELECT id FROM users WHERE role = 'customer'");
            return array_column($result, 'id');
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get agent IDs
     */
    private function getAgentIds()
    {
        try {
            $result = $this->db->fetchAll("SELECT id FROM users WHERE role IN ('agent', 'associate')");
            return array_column($result, 'id');
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get employee IDs
     */
    private function getEmployeeIds()
    {
        try {
            $result = $this->db->fetchAll("SELECT id FROM employees");
            return array_column($result, 'id');
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get admin IDs
     */
    private function getAdminIds()
    {
        try {
            $result = $this->db->fetchAll("SELECT id FROM users WHERE role IN ('admin', 'super_admin')");
            return array_column($result, 'id');
        } catch (Exception $e) {
            return [];
        }
    }
}