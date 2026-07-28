<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Admin\AdminController;

class NotificationController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
    }

    private function getDashboardData(): array
    {
        $stats = [
            'emails_sent_today' => 0,
            'sms_sent_today' => 0,
            'failed_messages' => 0,
            'total_templates' => 0,
        ];

        try {
            $row = $this->db->query("SELECT COUNT(*) as cnt FROM email_queue WHERE DATE(sent_at) = CURDATE() AND status = 'sent'")->fetch(\PDO::FETCH_ASSOC);
            $stats['emails_sent_today'] = (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) {}

        try {
            $row = $this->db->query("SELECT COUNT(*) as cnt FROM sms_queue WHERE DATE(sent_at) = CURDATE() AND status = 'sent'")->fetch(\PDO::FETCH_ASSOC);
            $stats['sms_sent_today'] = (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) {}

        try {
            $row = $this->db->query("SELECT COUNT(*) as cnt FROM email_queue WHERE status = 'failed'")->fetch(\PDO::FETCH_ASSOC);
            $failedEmails = (int)($row['cnt'] ?? 0);
            $row2 = $this->db->query("SELECT COUNT(*) as cnt FROM sms_queue WHERE status = 'failed'")->fetch(\PDO::FETCH_ASSOC);
            $failedSms = (int)($row2['cnt'] ?? 0);
            $stats['failed_messages'] = $failedEmails + $failedSms;
        } catch (\Throwable $e) {}

        try {
            $row = $this->db->query("SELECT COUNT(*) as cnt FROM email_templates WHERE is_active = 1")->fetch(\PDO::FETCH_ASSOC);
            $emailTpls = (int)($row['cnt'] ?? 0);
            $row2 = $this->db->query("SELECT COUNT(*) as cnt FROM sms_templates WHERE is_active = 1")->fetch(\PDO::FETCH_ASSOC);
            $smsTpls = (int)($row2['cnt'] ?? 0);
            $stats['total_templates'] = $emailTpls + $smsTpls;
        } catch (\Throwable $e) {}

        $emailLogs = [];
        try {
            $emailLogs = $this->db->query("SELECT to_email as recipient, subject, status, sent_at, created_at FROM email_queue ORDER BY created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {}

        $smsLogs = [];
        try {
            $smsLogs = $this->db->query("SELECT recipient, message, status, sent_at, created_at FROM sms_queue ORDER BY created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {}

        return ['stats' => $stats, 'email_logs' => $emailLogs, 'sms_logs' => $smsLogs];
    }

    public function index()
    {
        $data = $this->getDashboardData();
        $data['page_title'] = 'Notification Management';
        $this->render('notification/index', $data);
    }

    public function create()
    {
        return $this->createNotification();
    }

    public function templates()
    {
        $data = $this->getDashboardData();
        $data['page_title'] = 'Notification Templates';
        $this->render('notification/templates', $data);
    }

    public function createTemplate()
    {
        $this->render('notification/create_template', ['page_title' => 'Create Template']);
    }

    public function editTemplate($id)
    {
        $this->render('notification/edit_template', ['page_title' => 'Edit Template']);
    }

    public function emailLogs()
    {
        $data = $this->getDashboardData();
        $data['page_title'] = 'Email Logs';
        $this->render('notification/email_logs', $data);
    }

    public function smsLogs()
    {
        $data = $this->getDashboardData();
        $data['page_title'] = 'SMS Logs';
        $this->render('notification/sms_logs', $data);
    }

    public function settings()
    {
        $data = $this->getDashboardData();
        $data['page_title'] = 'Notification Settings';
        $this->render('notification/settings', $data);
    }

    public function sendTest()
    {
        $this->render('notification/send_test', ['page_title' => 'Send Test Notification']);
    }

    public function preview()
    {
        $this->render('notification/preview', ['page_title' => 'Preview Template']);
    }

    private function ensureSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function getNotifications()
    {
        header('Content-Type: application/json');
        $this->ensureSession();
        $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        try {
            $stmt = $this->db->prepare("SELECT * FROM notifications WHERE (user_id = ? OR user_id IS NULL) ORDER BY created_at DESC LIMIT 50");
            $stmt->execute([$userId]);
            $notifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $unreadCount = 0;
            try {
                $stmt2 = $this->db->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE is_read = 0 AND (user_id = ? OR user_id IS NULL)");
                $stmt2->execute([$userId]);
                $unreadCount = (int)($stmt2->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);
            } catch (\Throwable $e) {}
            echo json_encode(['success' => true, 'notifications' => $notifications, 'unread_count' => $unreadCount]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function markAsRead()
    {
        header('Content-Type: application/json');
        $this->ensureSession();
        $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Missing notification id']);
            exit;
        }
        try {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND (user_id = ? OR user_id IS NULL)");
            $stmt->execute([$id, $userId]);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function getUnreadCount()
    {
        header('Content-Type: application/json');
        $this->ensureSession();
        $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM notifications WHERE is_read = 0 AND (user_id = ? OR user_id IS NULL)");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'count' => (int)($row['count'] ?? 0)]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function getPopups()
    {
        header('Content-Type: application/json');
        $this->ensureSession();
        $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        try {
            $stmt = $this->db->prepare("SELECT * FROM notifications WHERE (user_id = ? OR user_id IS NULL) AND is_important = 1 AND is_read = 0 ORDER BY created_at DESC LIMIT 10");
            $stmt->execute([$userId]);
            $popups = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'popups' => $popups]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function dismissPopup()
    {
        header('Content-Type: application/json');
        $this->ensureSession();
        $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Missing popup id']);
            exit;
        }
        try {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND (user_id = ? OR user_id IS NULL)");
            $stmt->execute([$id, $userId]);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function createNotification()
    {
        header('Content-Type: application/json');
        $this->ensureSession();
        if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        $userId = $_POST['user_id'] ?? null;
        $title = $_POST['title'] ?? '';
        $message = $_POST['message'] ?? '';
        $type = $_POST['type'] ?? 'info';
        if (!$userId || empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields (user_id, title)']);
            exit;
        }
        try {
            $stmt = $this->db->prepare("INSERT INTO notifications (user_id, type, title, message, status, is_read, created_at) VALUES (?, ?, ?, ?, 'delivered', 0, NOW())");
            $stmt->execute([$userId, $type, $title, $message]);
            echo json_encode(['success' => true, 'message' => 'Notification created']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function createPopup()
    {
        header('Content-Type: application/json');
        $this->ensureSession();
        if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        $userId = $_POST['user_id'] ?? null;
        $title = $_POST['title'] ?? '';
        $message = $_POST['message'] ?? '';
        $type = $_POST['type'] ?? 'info';
        if (!$userId || empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields (user_id, title)']);
            exit;
        }
        try {
            $stmt = $this->db->prepare("INSERT INTO notifications (user_id, type, title, message, status, is_read, is_important, created_at) VALUES (?, ?, ?, ?, 'delivered', 0, 1, NOW())");
            $stmt->execute([$userId, $type, $title, $message]);
            echo json_encode(['success' => true, 'message' => 'Popup created']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
