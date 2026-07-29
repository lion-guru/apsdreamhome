<?php

namespace App\Http\Controllers\Admin;

// AdminController resolved via namespace
use App\Core\Database\Database;

/**
 * Admin Notification Dashboard
 * Shows notification delivery stats, channel health, and recent sends.
 */
class NotificationDashboardController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Main dashboard — delivery stats, channel breakdown, recent activity
     */
    public function index()
    {
        $db = Database::getInstance();
        $stats = $this->getNotificationStats($db);
        $recentLogs = $this->getRecentLogs($db);
        $channelStats = $this->getChannelStats($db);
        $typeStats = $this->getTypeStats($db);
        $dailyStats = $this->getDailyStats($db);
        $templateStats = $this->getTemplateStats($db);

        $this->render('admin/notification-dashboard/index', [
            'page_title' => 'Notification Dashboard',
            'stats' => $stats,
            'recent_logs' => $recentLogs,
            'channel_stats' => $channelStats,
            'type_stats' => $typeStats,
            'daily_stats' => $dailyStats,
            'template_stats' => $templateStats,
        ]);
    }

    /**
     * SMS templates management
     */
    public function smsTemplates()
    {
        $db = Database::getInstance();
        $templates = $db->query("SELECT * FROM sms_templates ORDER BY template_code")->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('admin/notification-dashboard/sms_templates', [
            'page_title' => 'SMS Templates',
            'templates' => $templates,
        ]);
    }

    /**
     * WhatsApp templates management
     */
    public function whatsappTemplates()
    {
        $db = Database::getInstance();
        $templates = $db->query("SELECT * FROM whatsapp_templates ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('admin/notification-dashboard/whatsapp_templates', [
            'page_title' => 'WhatsApp Templates',
            'templates' => $templates,
        ]);
    }

    /**
     * Test notification — send test email/SMS/push to a user
     */
    public function sendTest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/notifications');
            exit;
        }

        $channel = $_POST['channel'] ?? 'email';
        $userId = (int)($_POST['user_id'] ?? 0);
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        $results = [];

        try {
            require_once __DIR__ . '/../../../Services/Communication/LoginNotificationService.php';
            $service = new \App\Services\Communication\LoginNotificationService();

            if ($channel === 'email' || $channel === 'all') {
                // Send test email
                $emailService = new \App\Services\Communication\EmailService();
                $results['email'] = $emailService->send($email, 'Test Notification - APS Dream Home', '<h2>Test Email</h2><p>This is a test notification from APS Dream Home admin panel.</p>');
            }

            if ($channel === 'push' || $channel === 'all') {
                // Send test push
                $pushService = new \App\Services\Communication\PushNotificationService();
                $results['push'] = $pushService->sendToUser($userId, [
                    'title' => 'Test Notification',
                    'body' => 'This is a test push notification from admin.',
                    'data' => ['type' => 'test'],
                ]);
            }

            $_SESSION['success'] = 'Test notification sent via ' . strtoupper($channel);
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Failed: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/admin/notifications');
        exit;
    }

    // ─── Stats Helpers ──────────────────────────────────────

    private function getNotificationStats($db = null): array
    {
        $stats = [
            'total_sent' => 0,
            'today_sent' => 0,
            'success_rate' => 0,
            'channels_active' => 0,
            'sms_templates' => 0,
            'wa_templates' => 0,
        ];

        try {
            $stats['total_sent'] = (int)$db->query("SELECT COUNT(*) FROM notification_logs")->fetchColumn();
            $stats['today_sent'] = (int)$db->query("SELECT COUNT(*) FROM notification_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
            
            $total = $stats['total_sent'];
            $sent = (int)$db->query("SELECT COUNT(*) FROM notification_logs WHERE status = 'sent'")->fetchColumn();
            $stats['success_rate'] = $total > 0 ? round(($sent / $total) * 100, 1) : 0;
            
            $stats['channels_active'] = (int)$db->query("SELECT COUNT(DISTINCT channel) FROM notification_logs WHERE channel IS NOT NULL")->fetchColumn();
            $stats['sms_templates'] = (int)$db->query("SELECT COUNT(*) FROM sms_templates WHERE is_active = 1")->fetchColumn();
            $stats['wa_templates'] = (int)$db->query("SELECT COUNT(*) FROM whatsapp_templates WHERE is_active = 1")->fetchColumn();
        } catch (\Throwable $e) { error_log("NotificationDashboardController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        return $stats;
    }

    private function getRecentLogs($db, int $limit = 20): array
    {
        try {
            return $db->query(
                "SELECT nl.*, u.name as user_name, u.email 
                 FROM notification_logs nl 
                 LEFT JOIN users u ON nl.user_id = u.id 
                 ORDER BY nl.created_at DESC 
                 LIMIT {$limit}"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getChannelStats($db): array
    {
        try {
            return $db->query(
                "SELECT channel, COUNT(*) as count, 
                        SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
                 FROM notification_logs 
                 WHERE channel IS NOT NULL 
                 GROUP BY channel 
                 ORDER BY count DESC"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getTypeStats($db): array
    {
        try {
            return $db->query(
                "SELECT type, COUNT(*) as count 
                 FROM notification_logs 
                 GROUP BY type 
                 ORDER BY count DESC 
                 LIMIT 10"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getDailyStats($db): array
    {
        try {
            return $db->query(
                "SELECT DATE(created_at) as date, COUNT(*) as count 
                 FROM notification_logs 
                 WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                 GROUP BY DATE(created_at) 
                 ORDER BY date ASC"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getTemplateStats($db): array
    {
        $stats = ['sms' => [], 'whatsapp' => []];

        try {
            $stats['sms'] = $db->query(
                "SELECT template_code, template_name, 0 as usage_count FROM sms_templates WHERE is_active = 1 ORDER BY template_name"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { error_log("NotificationDashboardController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        try {
            $stats['whatsapp'] = $db->query(
                "SELECT name as template_name, category, status, usage_count FROM whatsapp_templates WHERE is_active = 1 ORDER BY name"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { error_log("NotificationDashboardController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        return $stats;
    }
}
