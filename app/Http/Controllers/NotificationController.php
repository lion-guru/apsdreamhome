<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Admin\AdminController;

class NotificationController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

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
        } catch (\Throwable $e) { error_log("NotificationController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        try {
            $row = $this->db->query("SELECT COUNT(*) as cnt FROM sms_queue WHERE DATE(sent_at) = CURDATE() AND status = 'sent'")->fetch(\PDO::FETCH_ASSOC);
            $stats['sms_sent_today'] = (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) { error_log("NotificationController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        try {
            $row = $this->db->query("SELECT COUNT(*) as cnt FROM email_queue WHERE status = 'failed'")->fetch(\PDO::FETCH_ASSOC);
            $failedEmails = (int)($row['cnt'] ?? 0);
            $row2 = $this->db->query("SELECT COUNT(*) as cnt FROM sms_queue WHERE status = 'failed'")->fetch(\PDO::FETCH_ASSOC);
            $failedSms = (int)($row2['cnt'] ?? 0);
            $stats['failed_messages'] = $failedEmails + $failedSms;
        } catch (\Throwable $e) { error_log("NotificationController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        try {
            $row = $this->db->query("SELECT COUNT(*) as cnt FROM email_templates WHERE is_active = 1")->fetch(\PDO::FETCH_ASSOC);
            $emailTpls = (int)($row['cnt'] ?? 0);
            $row2 = $this->db->query("SELECT COUNT(*) as cnt FROM sms_templates WHERE is_active = 1")->fetch(\PDO::FETCH_ASSOC);
            $smsTpls = (int)($row2['cnt'] ?? 0);
            $stats['total_templates'] = $emailTpls + $smsTpls;
        } catch (\Throwable $e) { error_log("NotificationController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $emailLogs = [];
        try {
            $emailLogs = $this->db->query("SELECT to_email as recipient, subject, status, sent_at, created_at FROM email_queue ORDER BY created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { error_log("NotificationController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $smsLogs = [];
        try {
            $smsLogs = $this->db->query("SELECT recipient, message, status, sent_at, created_at FROM sms_queue ORDER BY created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { error_log("NotificationController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        return ['stats' => $stats, 'email_logs' => $emailLogs, 'sms_logs' => $smsLogs];
    }

    public function index()
    {
        $this->requireAdmin();
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
        $this->requireAdmin();
        $data = $this->getDashboardData();
        $data['page_title'] = 'Notification Templates';
        $this->render('notification/templates', $data);
    }

    public function createTemplate()
    {
        $this->requireAdmin();
        $this->render('notification/create_template', ['page_title' => 'Create Template']);
    }

    public function editTemplate($id)
    {
        $this->requireAdmin();
        $this->render('notification/edit_template', ['page_title' => 'Edit Template']);
    }

    public function emailLogs()
    {
        $this->requireAdmin();
        $data = $this->getDashboardData();
        $data['page_title'] = 'Email Logs';
        $this->render('notification/email_logs', $data);
    }

    public function smsLogs()
    {
        $this->requireAdmin();
        $data = $this->getDashboardData();
        $data['page_title'] = 'SMS Logs';
        $this->render('notification/sms_logs', $data);
    }

    public function settings()
    {
        $this->requireAdmin();
        $data = $this->getDashboardData();
        $data['page_title'] = 'Notification Settings';
        $this->render('notification/settings', $data);
    }

    public function sendTest()
    {
        $this->requireAdmin();
        $this->render('notification/send_test', ['page_title' => 'Send Test Notification']);
    }

    public function preview()
    {
        $this->requireAdmin();
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
        $role = strtolower((string)($_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'guest'));
        $isAdmin = in_array($role, ['admin', 'super_admin', 'manager', 'ceo', 'coo', 'cfo', 'director'], true) || !empty($_SESSION['admin_id']);
        $isEmployee = in_array($role, ['employee', 'staff'], true) || !empty($_SESSION['employee_id']);
        $isAssociate = in_array($role, ['associate', 'agent', 'partner'], true) || !empty($_SESSION['associate_id']) || !empty($_SESSION['agent_id']);
        $isCustomer = !$isAdmin && !$isEmployee && !$isAssociate && !empty($userId);
        $isGuest = empty($userId) && !$isAdmin && !$isEmployee && !$isAssociate;

        // Read-only poller: release session lock immediately
        if (function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE) session_write_close();

        try {
            // Case 1: Guest (not logged in) - only show public announcements
            if ($isGuest) {
                $sql = "SELECT id, user_id, type, title, message, status, is_read, priority, action_url, created_at 
                        FROM notifications 
                        WHERE user_id IS NULL 
                          AND type IN ('broadcast', 'announcement', 'public', 'offer')
                          AND title IS NOT NULL AND TRIM(title) != ''
                          AND message IS NOT NULL AND TRIM(message) != ''
                          AND (expires_at IS NULL OR expires_at > NOW())
                        ORDER BY created_at DESC LIMIT 10";
                $stmt = $this->db->query($sql);
                $rawNotifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                $notifications = [];
                foreach ($rawNotifications as $n) {
                    $notifications[] = [
                        'id'         => (int)$n['id'],
                        'user_id'    => null,
                        'type'       => $n['type'] ?: 'info',
                        'category'   => 'general',
                        'title'      => str_replace('???', '₹', (string)($n['title'] ?? '')),
                        'message'    => str_replace('???', '₹', (string)($n['message'] ?? '')),
                        'status'     => 'unread',
                        'is_read'    => 0,
                        'priority'   => $n['priority'] ?: 'normal',
                        'action_url' => $n['action_url'] ?: '',
                        'created_at' => $n['created_at'],
                    ];
                }

                echo json_encode([
                    'success'       => true,
                    'authenticated' => false,
                    'notifications' => $notifications,
                    'unread_count'  => 0,
                    'role'          => 'guest'
                ]);
                exit;
            }

            // Case 2: Authenticated user with RBAC scoping
            $params = [$userId];

            if ($isAdmin) {
                // Admin: sees personal + system/admin alerts, leads, invoices, reviews
                $sql = "SELECT id, user_id, type, title, message, status, is_read, priority, action_url, created_at 
                        FROM notifications 
                        WHERE (user_id = ? OR (user_id IS NULL AND (type IN ('system', 'alert', 'warning', 'error', 'lead', 'campaign', 'invoice_update', 'document_reviewed', 'broadcast', 'announcement', 'inquiry') OR is_important = 1)))
                          AND title IS NOT NULL AND TRIM(title) != ''
                          AND message IS NOT NULL AND TRIM(message) != ''
                          AND title NOT IN ('Reminder: Complete Your Profile', 'Pending Document Upload')
                          AND (expires_at IS NULL OR expires_at > NOW())";
            } elseif ($isEmployee) {
                // Employee: sees personal + company/HR notices, payslip/tasks
                $sql = "SELECT id, user_id, type, title, message, status, is_read, priority, action_url, created_at 
                        FROM notifications 
                        WHERE (user_id = ? OR (user_id IS NULL AND type IN ('broadcast', 'announcement', 'general', 'holiday', 'hr')))
                          AND title IS NOT NULL AND TRIM(title) != ''
                          AND message IS NOT NULL AND TRIM(message) != ''
                          AND type NOT IN ('system', 'backup', 'cron')
                          AND (expires_at IS NULL OR expires_at > NOW())";
            } elseif ($isAssociate) {
                // Associate/Agent: sees personal + commission, payout, network, public announcements
                $sql = "SELECT id, user_id, type, title, message, status, is_read, priority, action_url, created_at 
                        FROM notifications 
                        WHERE (user_id = ? OR (user_id IS NULL AND type IN ('broadcast', 'announcement', 'general', 'offer', 'commission', 'payout')))
                          AND title IS NOT NULL AND TRIM(title) != ''
                          AND message IS NOT NULL AND TRIM(message) != ''
                          AND type NOT IN ('system', 'backup', 'cron')
                          AND (expires_at IS NULL OR expires_at > NOW())";
            } else {
                // Customer: strictly their personal bookings, payments, and public broadcasts
                $sql = "SELECT id, user_id, type, title, message, status, is_read, priority, action_url, created_at 
                        FROM notifications 
                        WHERE (user_id = ? OR (user_id IS NULL AND type IN ('broadcast', 'announcement', 'general', 'offer')))
                          AND title IS NOT NULL AND TRIM(title) != ''
                          AND message IS NOT NULL AND TRIM(message) != ''
                          AND type NOT IN ('system', 'backup', 'cron', 'lead', 'invoice_update', 'review_assignment', 'performance_review')
                          AND (expires_at IS NULL OR expires_at > NOW())";
            }

            // Category filter if passed (?category=leads|alerts|system|payments)
            $category = trim($_GET['category'] ?? '');
            if ($category === 'leads') {
                $sql .= " AND (type IN ('lead', 'campaign') OR title LIKE '%Lead%' OR message LIKE '%Lead%')";
            } elseif ($category === 'alerts') {
                $sql .= " AND (type IN ('warning', 'error', 'alert', 'system') OR is_important = 1 OR title LIKE '%Alert%')";
            } elseif ($category === 'system') {
                $sql .= " AND (type IN ('system', 'backup', 'cron') OR title LIKE '%System%' OR title LIKE '%Backup%')";
            } elseif ($category === 'payments') {
                $sql .= " AND (type IN ('payment', 'invoice_update') OR title LIKE '%Payment%' OR title LIKE '%Invoice%')";
            }

            $sql .= " ORDER BY created_at DESC LIMIT 50";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $rawNotifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Clean up currency symbols & map categories
            $notifications = [];
            foreach ($rawNotifications as $n) {
                $title = str_replace('???', '₹', (string)($n['title'] ?? ''));
                $msg   = str_replace('???', '₹', (string)($n['message'] ?? ''));

                // Derive category for frontend tab filtering
                $t = strtolower((string)($n['type'] ?? 'info'));
                $cat = 'general';
                if (in_array($t, ['warning', 'error', 'alert', 'system'], true) || stripos($title, 'alert') !== false) {
                    $cat = 'alerts';
                } elseif (in_array($t, ['lead', 'campaign', 'inquiry'], true) || stripos($title, 'lead') !== false || stripos($title, 'inquiry') !== false) {
                    $cat = 'leads';
                } elseif (in_array($t, ['payment', 'invoice_update'], true) || stripos($title, 'payment') !== false || stripos($title, 'invoice') !== false) {
                    $cat = 'payments';
                } elseif (in_array($t, ['document_reviewed', 'performance_review', 'review_assignment'], true)) {
                    $cat = 'reviews';
                }

                $notifications[] = [
                    'id'         => (int)$n['id'],
                    'user_id'    => $n['user_id'] ? (int)$n['user_id'] : null,
                    'type'       => $n['type'] ?: 'info',
                    'category'   => $cat,
                    'title'      => $title,
                    'message'    => $msg,
                    'status'     => ((int)($n['is_read'] ?? 0) === 1 || ($n['status'] ?? '') === 'read') ? 'read' : 'unread',
                    'is_read'    => (int)($n['is_read'] ?? 0),
                    'priority'   => $n['priority'] ?: 'normal',
                    'action_url' => $n['action_url'] ?: '',
                    'created_at' => $n['created_at'],
                ];
            }

            // Precise RBAC-scoped unread count
            $unreadCount = 0;
            try {
                $cntSql = preg_replace('/SELECT .*? FROM/is', 'SELECT COUNT(*) as cnt FROM', $sql);
                $cntSql = preg_replace('/ORDER BY .*$/is', '', $cntSql);
                $cntSql .= " AND is_read = 0";
                $stmt2 = $this->db->prepare($cntSql);
                $stmt2->execute($params);
                $unreadCount = (int)($stmt2->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);
            } catch (\Throwable $e) { 
                error_log("NotificationController::" . __FUNCTION__ . " unread query failed: " . $e->getMessage()); 
            }

            echo json_encode([
                'success'       => true, 
                'authenticated' => true,
                'notifications' => $notifications, 
                'unread_count'  => $unreadCount,
                'role'          => $role
            ]);
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

        $rawInput = file_get_contents('php://input');
        $jsonData = json_decode($rawInput, true) ?: [];
        $id = $_POST['id'] ?? $_POST['notification_id'] ?? $jsonData['id'] ?? $jsonData['notification_id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Missing notification id']);
            exit;
        }
        try {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1, status = 'read', read_at = NOW() WHERE id = ? AND (user_id = ? OR user_id IS NULL)");
            $stmt->execute([$id, $userId]);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function markAllAsRead()
    {
        header('Content-Type: application/json');
        $this->ensureSession();
        $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        try {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1, status = 'read', read_at = NOW() WHERE is_read = 0 AND (user_id = ? OR user_id IS NULL)");
            $stmt->execute([$userId]);
            echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
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
        $role = strtolower((string)($_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'guest'));
        $isAdmin = in_array($role, ['admin', 'super_admin', 'manager', 'ceo', 'coo', 'cfo', 'director'], true) || !empty($_SESSION['admin_id']);
        $isEmployee = in_array($role, ['employee', 'staff'], true) || !empty($_SESSION['employee_id']);
        $isAssociate = in_array($role, ['associate', 'agent', 'partner'], true) || !empty($_SESSION['associate_id']) || !empty($_SESSION['agent_id']);

        if (!$userId) {
            echo json_encode(['success' => true, 'count' => 0, 'role' => 'guest']);
            exit;
        }

        // Read-only poller: release session lock immediately
        if (function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        try {
            $params = [$userId];
            if ($isAdmin) {
                $cntSql = "SELECT COUNT(*) as count FROM notifications 
                           WHERE is_read = 0 
                             AND (user_id = ? OR (user_id IS NULL AND (type IN ('system', 'alert', 'warning', 'error', 'lead', 'campaign', 'invoice_update', 'document_reviewed', 'broadcast', 'announcement') OR is_important = 1)))
                             AND title IS NOT NULL AND TRIM(title) != ''
                             AND message IS NOT NULL AND TRIM(message) != ''
                             AND title NOT IN ('Reminder: Complete Your Profile', 'Pending Document Upload')
                             AND (expires_at IS NULL OR expires_at > NOW())";
            } elseif ($isEmployee) {
                $cntSql = "SELECT COUNT(*) as count FROM notifications 
                           WHERE is_read = 0 
                             AND (user_id = ? OR (user_id IS NULL AND type IN ('broadcast', 'announcement', 'general', 'holiday', 'hr')))
                             AND title IS NOT NULL AND TRIM(title) != ''
                             AND message IS NOT NULL AND TRIM(message) != ''
                             AND type NOT IN ('system', 'backup', 'cron')
                             AND (expires_at IS NULL OR expires_at > NOW())";
            } elseif ($isAssociate) {
                $cntSql = "SELECT COUNT(*) as count FROM notifications 
                           WHERE is_read = 0 
                             AND (user_id = ? OR (user_id IS NULL AND type IN ('broadcast', 'announcement', 'general', 'offer', 'commission', 'payout')))
                             AND title IS NOT NULL AND TRIM(title) != ''
                             AND message IS NOT NULL AND TRIM(message) != ''
                             AND type NOT IN ('system', 'backup', 'cron')
                             AND (expires_at IS NULL OR expires_at > NOW())";
            } else {
                // Customer
                $cntSql = "SELECT COUNT(*) as count FROM notifications 
                           WHERE is_read = 0 
                             AND (user_id = ? OR (user_id IS NULL AND type IN ('broadcast', 'announcement', 'general', 'offer')))
                             AND title IS NOT NULL AND TRIM(title) != ''
                             AND message IS NOT NULL AND TRIM(message) != ''
                             AND type NOT IN ('system', 'backup', 'cron', 'lead', 'invoice_update', 'review_assignment', 'performance_review')
                             AND (expires_at IS NULL OR expires_at > NOW())";
            }

            $stmt = $this->db->prepare($cntSql);
            $stmt->execute($params);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'count' => (int)($row['count'] ?? 0), 'role' => $role]);
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
        $tid = (int)$this->tenantId();
        try {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND (user_id = ? OR user_id IS NULL) AND tenant_id = ?");
            $stmt->execute([$id, $userId, $tid]);
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
        $tid = (int)$this->tenantId();
        try {
            $stmt = $this->db->prepare("INSERT INTO notifications (user_id, type, title, message, status, is_read, tenant_id, created_at) VALUES (?, ?, ?, ?, 'delivered', 0, ?, NOW())");
            $stmt->execute([$userId, $type, $title, $message, $tid]);

            // Web Push dispatch
            try {
                $pushSender = new \App\Services\Communication\PushSender();
                if ($pushSender->isConfigured()) {
                    $pushSender->sendToUser((int)$userId, $title, $message, '/');
                }
            } catch (\Throwable $pt) {
                // Non-blocking
            }

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
        $tid = (int)$this->tenantId();
        try {
            $stmt = $this->db->prepare("INSERT INTO notifications (user_id, type, title, message, status, is_read, is_important, tenant_id, created_at) VALUES (?, ?, ?, ?, 'delivered', 0, 1, ?, NOW())");
            $stmt->execute([$userId, $type, $title, $message, $tid]);

            // Web Push dispatch
            try {
                $pushSender = new \App\Services\Communication\PushSender();
                if ($pushSender->isConfigured()) {
                    $pushSender->sendToUser((int)$userId, $title, $message, '/');
                }
            } catch (\Throwable $pt) {
                // Non-blocking
            }

            echo json_encode(['success' => true, 'message' => 'Popup created']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
