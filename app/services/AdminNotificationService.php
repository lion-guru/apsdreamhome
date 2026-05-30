<?php

namespace App\Services;

class AdminNotificationService
{
    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    /**
     * Log an internal admin notification
     */
    public function notify($type, $message, $userId = null, $actionUrl = null, $title = null)
    {
        try {
            $this->db->query(
                'INSERT INTO notifications (user_id, type, title, message, action_url, is_read, status, created_at) VALUES (?, ?, ?, ?, ?, 0, ?, NOW())',
                [$userId, $type, $title ?? ucfirst($type), $message, $actionUrl, 'unread']
            );
            return true;
        } catch (\Exception $e) {
            error_log('AdminNotificationService: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unread notifications
     */
    public function getUnread($userId = null, $limit = 20)
    {
        try {
            $sql = 'SELECT * FROM notifications WHERE is_read = 0';
            $params = [];
            if ($userId) {
                $sql .= ' AND (user_id = ? OR user_id IS NULL)';
                $params[] = $userId;
            }
            $sql .= ' ORDER BY created_at DESC LIMIT ?';
            $params[] = (int)$limit;
            return $this->db->fetchAll($sql, $params) ?: [];
        } catch (\Exception $e) {
            error_log('AdminNotificationService: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent notifications (read + unread)
     */
    public function getRecent($userId = null, $limit = 50)
    {
        try {
            $sql = 'SELECT * FROM notifications';
            $params = [];
            $conditions = [];
            if ($userId) {
                $conditions[] = '(user_id = ? OR user_id IS NULL)';
                $params[] = $userId;
            }
            if (!empty($conditions)) {
                $sql .= ' WHERE ' . implode(' AND ', $conditions);
            }
            $sql .= ' ORDER BY created_at DESC LIMIT ?';
            $params[] = (int)$limit;
            return $this->db->fetchAll($sql, $params) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Mark notification as read
     */
    public function markRead($id)
    {
        try {
            $this->db->query('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?', [(int)$id]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Mark all as read for a user
     */
    public function markAllRead($userId = null)
    {
        try {
            $sql = 'UPDATE notifications SET is_read = 1, read_at = NOW() WHERE is_read = 0';
            $params = [];
            if ($userId) {
                $sql .= ' AND (user_id = ? OR user_id IS NULL)';
                $params[] = $userId;
            }
            $this->db->query($sql, $params);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get unread count
     */
    public function getUnreadCount($userId = null)
    {
        try {
            $sql = 'SELECT COUNT(*) as cnt FROM notifications WHERE is_read = 0';
            $params = [];
            if ($userId) {
                $sql .= ' AND (user_id = ? OR user_id IS NULL)';
                $params[] = $userId;
            }
            $row = $this->db->fetch($sql, $params);
            return $row ? (int)$row['cnt'] : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Trigger: New lead created
     */
    public function newLead($leadId, $leadName)
    {
        return $this->notify(
            'lead',
            "New lead: $leadName",
            null,
            '/admin/leads/show/' . $leadId,
            'New Lead'
        );
    }

    /**
     * Trigger: New property listed
     */
    public function newProperty($propertyId, $propertyTitle)
    {
        return $this->notify(
            'property',
            "New property listed: $propertyTitle",
            null,
            '/admin/user-properties/verify/' . $propertyId,
            'New Property'
        );
    }

    /**
     * Trigger: New user registration
     */
    public function newRegistration($userId, $userName)
    {
        return $this->notify(
            'user',
            "New user registered: $userName",
            null,
            '/admin/users/' . $userId,
            'New Registration'
        );
    }

    /**
     * Trigger: New booking
     */
    public function newBooking($bookingId, $buyerName)
    {
        return $this->notify(
            'booking',
            "New booking: $buyerName",
            null,
            '/admin/bookings/' . $bookingId,
            'New Booking'
        );
    }

    /**
     * Trigger: Payment received
     */
    public function paymentReceived($transactionId, $amount)
    {
        return $this->notify(
            'payment',
            "Payment received: ₹$amount",
            null,
            '/admin/payments/' . $transactionId,
            'Payment Received'
        );
    }
}
