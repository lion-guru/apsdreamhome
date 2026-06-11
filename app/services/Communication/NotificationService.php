<?php
namespace App\Services\Communication;

use App\Core\Database\Database;
use App\Services\Gateway\CommunicationGateway;

class NotificationService
{
    private $db;
    private $fromEmail;
    private $fromName;
    private $gateway;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->fromEmail = $_ENV['SMTP_FROM_EMAIL'] ?? 'notifications@apsdreamhome.com';
        $this->fromName = $_ENV['SMTP_FROM_NAME'] ?? 'APS Dream Home';
        $this->gateway = new CommunicationGateway();
    }

    public function sendNotification($userId, $channel, $title, $message, $data = [])
    {
        $notificationId = uniqid('notif_', true);

        try {
            $this->db->insert('notification_queue', [
                'notification_id' => null,
                'user_id' => $userId,
                'user_type' => 'customer',
                'channel' => $channel === 'email' ? 'email' : ($channel === 'sms' ? 'sms' : ($channel === 'push' ? 'push' : ($channel === 'whatsapp' ? 'whatsapp' : 'in_app'))),
                'title' => $title,
                'message' => $message,
                'data' => !empty($data) ? json_encode($data) : null,
                'priority' => 'normal',
                'status' => 'queued',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            error_log("NotificationService: queue insert failed: " . $e->getMessage());
        }

        $this->addToFeed($userId, $notificationId, $title, $message, $data);

        // WebSocket real-time push (best-effort, never throws).
        // The connected browser sees the notification instantly without waiting
        // for the next page load or long-poll cycle.
        try {
            \App\Services\WebSocketBroadcaster::broadcastToUser((int)$userId, [
                'event' => 'notification',
                'notification_id' => $notificationId,
                'title' => $title,
                'message' => $message,
                'channel' => $channel,
                'data' => $data,
                'ts' => time()
            ], 'user_' . (int)$userId . '_notifications');
        } catch (\Throwable $e) {
            error_log("NotificationService: WS broadcast failed: " . $e->getMessage());
        }

        // Delegate real-time channel sends to the unified CommunicationGateway.
        // This makes provider swaps, retries, and logging consistent across
        // every channel, and keeps the DB queue path intact for async processing.
        $user = $this->getUser($userId);
        if ($user) {
            $this->dispatchViaGateway($channel, $user, $title, $message, $data);
        }
    }

    /**
     * Send via CommunicationGateway for the given channel.
     * Never throws — failures are logged inside the gateway.
     */
    private function dispatchViaGateway($channel, array $user, $title, $message, array $data = [])
    {
        try {
            switch ($channel) {
                case 'email':
                    $this->gateway->sendEmail($user['email'] ?? '', $title, nl2br(htmlspecialchars($message)), [
                        'from'      => $this->fromEmail,
                        'from_name' => $this->fromName,
                        'isHtml'    => true,
                    ]);
                    break;
                case 'sms':
                    if (!empty($user['phone'])) {
                        $this->gateway->sendSms($user['phone'], $message, $data);
                    }
                    break;
                case 'whatsapp':
                    if (!empty($user['phone'])) {
                        $this->gateway->sendWhatsApp($user['phone'], $message, $data);
                    }
                    break;
                case 'push':
                    $this->gateway->sendPush($user['id'] ?? null, $title, $message, $data);
                    break;
                case 'in_app':
                    $senderId = $data['sender_id'] ?? 0;
                    if (!empty($user['id']) && $senderId) {
                        $this->gateway->sendInApp($senderId, $user['id'], $message, $data);
                    }
                    break;
            }
        } catch (\Throwable $e) {
            error_log("NotificationService: gateway dispatch failed for channel=$channel: " . $e->getMessage());
        }
    }

    private function addToFeed($userId, $notificationId, $title, $message, $data = [])
    {
        $typeMap = [
            'booking' => 'payment',
            'agreement' => 'property',
            'payment' => 'payment',
            'registry' => 'property',
            'possession' => 'property',
            'defect' => 'property',
        ];
        $key = $data['event_type'] ?? '';
        $feedType = $typeMap[$key] ?? 'info';

        try {
            $this->db->insert('notifications', [
                'user_id' => $userId,
                'type' => $feedType,
                'title' => $title,
                'message' => $message,
                'status' => 'delivered',
                'is_read' => 0,
                'priority' => ($data['priority'] ?? '') === 'high' ? 'high' : 'normal',
                'related_id' => $data['booking_id'] ?? null,
                'related_type' => $data['event_type'] ?? null,
                'action_url' => $data['action_url'] ?? null,
                'template_key' => $data['template_key'] ?? null,
                'template_data' => !empty($data) ? json_encode($data) : null,
                'is_important' => ($data['priority'] ?? '') === 'high' ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            error_log("NotificationService: feed insert failed: " . $e->getMessage());
        }
    }

    private function addToEmailQueue($userId, $title, $message, $data = [])
    {
        $user = $this->getUser($userId);
        if (!$user || empty($user['email'])) return;

        $bodyHtml = nl2br(htmlspecialchars($message));
        $bodyText = strip_tags($message);

        try {
            $this->db->insert('email_queue', [
                'queue_id' => uniqid('email_', true),
                'to_email' => $user['email'],
                'to_name' => $user['name'] ?? 'Customer',
                'from_email' => $this->fromEmail,
                'from_name' => $this->fromName,
                'subject' => $title,
                'body_html' => $bodyHtml,
                'body_text' => $bodyText,
                'priority' => 'normal',
                'status' => 'pending',
                'scheduled_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            error_log("NotificationService: email queue insert failed: " . $e->getMessage());
        }
    }

    private function addToSmsQueue($userId, $message)
    {
        $user = $this->getUser($userId);
        if (!$user || empty($user['phone'])) return;

        $phone = preg_replace('/[^0-9]/', '', $user['phone']);
        if (strlen($phone) === 10) $phone = '91' . $phone;

        try {
            $this->db->insert('sms_queue', [
                'recipient' => $phone,
                'message' => mb_substr($message, 0, 160),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            error_log("NotificationService: sms queue insert failed: " . $e->getMessage());
        }
    }

    private function addToPushQueue($userId, $title, $message, $data = [])
    {
        try {
            $this->db->insert('push_notifications', [
                'user_id' => $userId,
                'app_id' => 1,
                'title' => $title,
                'message' => $message,
                'data' => !empty($data) ? json_encode($data) : null,
                'device_tokens_sent' => 0,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            error_log("NotificationService: push queue insert failed: " . $e->getMessage());
        }
    }

    private function sendWhatsApp($userId, $message, $data = [])
    {
        $user = $this->getUser($userId);
        if (!$user || empty($user['phone'])) return;

        try {
            $whatsapp = new \App\Services\Communication\WhatsAppSenderService();
            $whatsapp->sendMessage($user['phone'], $message);
        } catch (\Exception $e) {
            error_log("NotificationService: sendWhatsApp failed: " . $e->getMessage());
        }
    }

    private function getUser($userId)
    {
        try {
            return $this->db->fetchOne("SELECT id, name, email, phone FROM users WHERE id = ?", [$userId]);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getBookingCustomerUserId($bookingId)
    {
        try {
            $booking = $this->db->fetchOne(
                "SELECT customer_id, user_id FROM bookings WHERE id = ?",
                [$bookingId]
            );
            if (!$booking) return null;
            return $booking['customer_id'] ?? $booking['user_id'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function sendBookingConfirmed($bookingId)
    {
        $userId = $this->getBookingCustomerUserId($bookingId);
        if (!$userId) return;

        $title = 'Booking Confirmed';
        $message = 'Your booking has been confirmed. Please check your account for plot details and payment information.';
        $data = [
            'event_type' => 'booking',
            'booking_id' => $bookingId,
            'action_url' => '/user/bookings/' . $bookingId,
            'priority' => 'high',
        ];

        $this->sendNotification($userId, 'email', $title, $message, $data);
        $this->sendNotification($userId, 'sms', $title, $message, $data);
        $this->sendNotification($userId, 'push', $title, $message, $data);
        $this->sendNotification($userId, 'in_app', $title, $message, $data);
    }

    public function sendAgreementGenerated($bookingId, $agreementType)
    {
        $userId = $this->getBookingCustomerUserId($bookingId);
        if (!$userId) return;

        $typeLabel = ucwords(str_replace('_', ' ', $agreementType));
        $title = $typeLabel . ' Ready';
        $message = 'Your ' . $typeLabel . ' has been generated. You can download it from your account.';
        $data = [
            'event_type' => 'agreement',
            'booking_id' => $bookingId,
            'agreement_type' => $agreementType,
            'action_url' => '/user/bookings/' . $bookingId,
        ];

        $this->sendNotification($userId, 'email', $title, $message, $data);
        $this->sendNotification($userId, 'in_app', $title, $message, $data);
    }

    public function sendPaymentReceived($bookingId, $amount)
    {
        $userId = $this->getBookingCustomerUserId($bookingId);
        if (!$userId) return;

        $title = 'Payment Received';
        $message = 'Payment of ₹' . number_format($amount) . ' has been received for your booking.';
        $data = [
            'event_type' => 'payment',
            'booking_id' => $bookingId,
            'amount' => $amount,
            'action_url' => '/user/bookings/' . $bookingId,
        ];

        $this->sendNotification($userId, 'email', $title, $message, $data);
        $this->sendNotification($userId, 'sms', $title, $message, $data);
        $this->sendNotification($userId, 'in_app', $title, $message, $data);
    }

    public function sendRegistryUpdate($bookingId, $status)
    {
        $userId = $this->getBookingCustomerUserId($bookingId);
        if (!$userId) return;

        $statusLabels = [
            'documents_pending' => 'Documents Pending',
            'stamp_duty_pending' => 'Stamp Duty Pending',
            'appointment_scheduled' => 'Registry Appointment Scheduled',
            'registered' => 'Property Registered',
            'completed' => 'Registry Completed',
        ];
        $label = $statusLabels[$status] ?? ucwords(str_replace('_', ' ', $status));

        $title = 'Registry Update';
        $message = 'Your registry status has been updated to: ' . $label . '.';
        $data = [
            'event_type' => 'registry',
            'booking_id' => $bookingId,
            'registry_status' => $status,
            'action_url' => '/user/bookings/' . $bookingId,
        ];

        $this->sendNotification($userId, 'email', $title, $message, $data);
        $this->sendNotification($userId, 'in_app', $title, $message, $data);
    }

    public function sendPossessionScheduled($bookingId, $date)
    {
        $userId = $this->getBookingCustomerUserId($bookingId);
        if (!$userId) return;

        $title = 'Possession Scheduled';
        $message = 'Your property possession has been scheduled for ' . date('d F Y', strtotime($date)) . '.';
        $data = [
            'event_type' => 'possession',
            'booking_id' => $bookingId,
            'possession_date' => $date,
            'action_url' => '/user/bookings/' . $bookingId,
            'priority' => 'high',
        ];

        $this->sendNotification($userId, 'email', $title, $message, $data);
        $this->sendNotification($userId, 'sms', $title, $message, $data);
        $this->sendNotification($userId, 'in_app', $title, $message, $data);
    }

    public function sendPossessionCompleted($bookingId)
    {
        $userId = $this->getBookingCustomerUserId($bookingId);
        if (!$userId) return;

        $title = 'Possession Completed';
        $message = 'Congratulations! Your property possession has been completed. You can now report any defects through your account.';
        $data = [
            'event_type' => 'possession',
            'booking_id' => $bookingId,
            'possession_completed' => true,
            'action_url' => '/user/bookings/' . $bookingId,
            'priority' => 'high',
        ];

        $this->sendNotification($userId, 'email', $title, $message, $data);
        $this->sendNotification($userId, 'sms', $title, $message, $data);
        $this->sendNotification($userId, 'in_app', $title, $message, $data);
    }

    public function getCustomerNotifications($userId, $limit = 20)
    {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
                [$userId, $limit]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getUnreadCount($userId)
    {
        try {
            $row = $this->db->fetchOne(
                "SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0",
                [$userId]
            );
            return (int)($row['cnt'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function markAsRead($notificationId)
    {
        try {
            $this->db->query(
                "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?",
                [$notificationId]
            );
        } catch (\Exception $e) {
            error_log("NotificationService: markAsRead failed: " . $e->getMessage());
        }
    }

    public function markAllAsRead($userId)
    {
        try {
            $this->db->query(
                "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0",
                [$userId]
            );
        } catch (\Exception $e) {
            error_log("NotificationService: markAllAsRead failed: " . $e->getMessage());
        }
    }

    public function sendBookingConfirmedEmail($bookingId)
    {
        $this->sendBookingConfirmed($bookingId);
    }
}
