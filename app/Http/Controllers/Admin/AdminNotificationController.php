<?php
namespace App\Http\Controllers\Admin;

use PDO;

class AdminNotificationController extends AdminController
{
    private const CONTENT_TYPE_JSON = 'Content-Type: application/json';
    private const NOTIF_INDEX_URL = '/admin/notifications';

    private $notificationService;

    public function __construct() {
        parent::__construct();
        $db = \App\Core\Database\Database::getInstance();
        $this->notificationService = new \App\Services\NotificationService($db);
    }

    public function index() {
        $this->requireAdmin();
        $userId = $this->getUserId();

        $statusFilter   = strtolower(trim($_GET['status'] ?? 'all'));
        $categoryFilter = strtolower(trim($_GET['category'] ?? 'all'));
        $searchQuery    = trim($_GET['q'] ?? '');
        $dateFilter     = trim($_GET['date'] ?? '');
        $page           = max(1, (int)($_GET['page'] ?? 1));
        $perPage        = 25;
        $offset         = ($page - 1) * $perPage;

        $baseWhere = "(user_id = :uid OR user_id IS NULL)
                      AND title IS NOT NULL AND TRIM(title) != ''
                      AND message IS NOT NULL AND TRIM(message) != ''
                      AND title NOT IN ('Reminder: Complete Your Profile', 'Pending Document Upload')
                      AND (expires_at IS NULL OR expires_at > NOW())";

        $params = [':uid' => $userId];
        $filterSql = $this->buildFilterConditions($statusFilter, $categoryFilter, $searchQuery, $dateFilter, $params);

        // Count matching items
        $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM notifications WHERE $baseWhere $filterSql");
        $countStmt->execute($params);
        $totalItems = (int)($countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        $totalPages = max(1, (int)ceil($totalItems / $perPage));

        // Fetch paginated rows
        $dataSql = "SELECT id, user_id, type, title, message, status, is_read, priority, action_url, created_at
                    FROM notifications
                    WHERE $baseWhere $filterSql
                    ORDER BY created_at DESC
                    LIMIT :limit OFFSET :offset";
        $dataStmt = $this->db->prepare($dataSql);
        foreach ($params as $k => $v) {
            $dataStmt->bindValue($k, $v);
        }
        $dataStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStmt->execute();

        $notifications = $this->formatNotifications($dataStmt->fetchAll(PDO::FETCH_ASSOC));
        $stats = $this->fetchStats($baseWhere, $userId);

        if ($this->isAjaxRequest()) {
            $this->respondJson([
                'success'       => true,
                'notifications' => $notifications,
                'pagination'    => [
                    'current_page' => $page,
                    'total_pages'  => $totalPages,
                    'total_items'  => $totalItems,
                    'per_page'     => $perPage
                ],
                'stats'         => $stats
            ]);
        }

        $this->render('admin/notifications/index', [
            'page_title'    => 'Notification Center',
            'page_heading'  => 'Notification Center',
            'notifications' => $notifications,
            'filters'       => [
                'status'   => $statusFilter,
                'category' => $categoryFilter,
                'q'        => $searchQuery,
                'date'     => $dateFilter,
            ],
            'pagination'    => [
                'current_page' => $page,
                'total_pages'  => $totalPages,
                'total_items'  => $totalItems,
                'per_page'     => $perPage,
            ],
            'stats'         => $stats,
        ]);
    }

    private function buildFilterConditions(string $status, string $category, string $q, string $date, array &$params): string
    {
        $sql = "";
        if ($status === 'unread') {
            $sql .= " AND is_read = 0";
        } elseif ($status === 'read') {
            $sql .= " AND is_read = 1";
        }

        if ($category === 'leads') {
            $sql .= " AND (type IN ('lead', 'campaign') OR title LIKE '%Lead%' OR message LIKE '%Lead%')";
        } elseif ($category === 'payments') {
            $sql .= " AND (type IN ('payment', 'invoice_update') OR title LIKE '%Payment%' OR title LIKE '%Invoice%')";
        } elseif ($category === 'alerts') {
            $sql .= " AND (type IN ('warning', 'error', 'alert', 'system') OR title LIKE '%Alert%' OR title LIKE '%Backup%' OR title LIKE '%Failed%')";
        } elseif ($category === 'reviews') {
            $sql .= " AND (type IN ('document_reviewed', 'performance_review', 'review_assignment') OR title LIKE '%Review%')";
        } elseif ($category === 'system') {
            $sql .= " AND (type IN ('system', 'backup', 'cron') OR title LIKE '%System%' OR title LIKE '%Summary%')";
        }

        if ($q !== '') {
            $sql .= " AND (title LIKE :search OR message LIKE :search)";
            $params[':search'] = "%$q%";
        }

        if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $sql .= " AND DATE(created_at) = :dt";
            $params[':dt'] = $date;
        }

        return $sql;
    }

    private function formatNotifications(array $rawRows): array
    {
        $notifications = [];
        foreach ($rawRows as $r) {
            $title = str_replace('???', '₹', (string)($r['title'] ?? ''));
            $msg   = str_replace('???', '₹', (string)($r['message'] ?? ''));

            $t = strtolower((string)($r['type'] ?? 'info'));
            $cat = 'general';
            if (in_array($t, ['warning', 'error', 'alert', 'system'], true) || stripos($title, 'alert') !== false || stripos($title, 'backup') !== false) {
                $cat = 'alerts';
            } elseif (in_array($t, ['lead', 'campaign'], true) || stripos($title, 'lead') !== false) {
                $cat = 'leads';
            } elseif (in_array($t, ['payment', 'invoice_update'], true) || stripos($title, 'payment') !== false || stripos($title, 'invoice') !== false) {
                $cat = 'payments';
            } elseif (in_array($t, ['document_reviewed', 'performance_review', 'review_assignment'], true) || stripos($title, 'review') !== false) {
                $cat = 'reviews';
            }

            $notifications[] = [
                'id'         => (int)$r['id'],
                'user_id'    => $r['user_id'] ? (int)$r['user_id'] : null,
                'type'       => $r['type'] ?: 'info',
                'category'   => $cat,
                'title'      => $title,
                'message'    => $msg,
                'status'     => ((int)($r['is_read'] ?? 0) === 1 || ($r['status'] ?? '') === 'read') ? 'read' : 'unread',
                'is_read'    => (int)($r['is_read'] ?? 0),
                'priority'   => $r['priority'] ?: 'normal',
                'action_url' => $r['action_url'] ?: '',
                'created_at' => $r['created_at'],
            ];
        }
        return $notifications;
    }

    private function fetchStats(string $baseWhere, int $userId): array
    {
        $stats = ['all' => 0, 'unread' => 0, 'read' => 0, 'leads' => 0, 'payments' => 0, 'alerts' => 0];
        try {
            $statStmt = $this->db->prepare(
                "SELECT
                    COUNT(*) as cnt_all,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as cnt_unread,
                    SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as cnt_read,
                    SUM(CASE WHEN type IN ('lead', 'campaign') OR title LIKE '%Lead%' OR message LIKE '%Lead%' THEN 1 ELSE 0 END) as cnt_leads,
                    SUM(CASE WHEN type IN ('payment', 'invoice_update') OR title LIKE '%Payment%' OR title LIKE '%Invoice%' THEN 1 ELSE 0 END) as cnt_payments,
                    SUM(CASE WHEN type IN ('warning', 'error', 'alert', 'system') OR title LIKE '%Alert%' OR title LIKE '%Backup%' THEN 1 ELSE 0 END) as cnt_alerts
                 FROM notifications
                 WHERE $baseWhere"
            );
            $statStmt->execute([':uid' => $userId]);
            $res = $statStmt->fetch(PDO::FETCH_ASSOC);
            if ($res) {
                $stats['all']      = (int)($res['cnt_all'] ?? 0);
                $stats['unread']   = (int)($res['cnt_unread'] ?? 0);
                $stats['read']     = (int)($res['cnt_read'] ?? 0);
                $stats['leads']    = (int)($res['cnt_leads'] ?? 0);
                $stats['payments'] = (int)($res['cnt_payments'] ?? 0);
                $stats['alerts']   = (int)($res['cnt_alerts'] ?? 0);
            }
        } catch (\Throwable $e) {
            error_log("AdminNotificationController stats query failed: " . $e->getMessage());
        }
        return $stats;
    }

    private function isAjaxRequest(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }

    private function respondJson(array $data): void
    {
        header(self::CONTENT_TYPE_JSON);
        echo json_encode($data);
        exit;
    }

    private function redirectToIndex(): void
    {
        header('Location: ' . BASE_URL . self::NOTIF_INDEX_URL);
        exit;
    }

    public function panel() {
        $this->requireAdmin();
        $notifications = $this->notificationService->getUnread();
        $this->render('admin/notifications/panel', [
            'page_title' => 'Notifications Panel',
            'page_heading' => 'Recent Notifications',
            'notifications' => $notifications
        ]);
    }

    public function markRead($id) {
        $this->requireAdmin();
        $userId = $this->getUserId();
        try {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1, status = 'read', read_at = NOW() WHERE id = ? AND (user_id = ? OR user_id IS NULL)");
            $stmt->execute([(int)$id, $userId]);
        } catch (\Throwable $e) {
            error_log("AdminNotificationController::markRead failed: " . $e->getMessage());
        }

        if ($this->isAjaxRequest()) {
            $this->respondJson(['success' => true]);
        }
        $this->redirectToIndex();
    }

    public function markAllRead() {
        $this->requireAdmin();
        $userId = $this->getUserId();
        try {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1, status = 'read', read_at = NOW() WHERE is_read = 0 AND (user_id = ? OR user_id IS NULL)");
            $stmt->execute([$userId]);
        } catch (\Throwable $e) {
            error_log("AdminNotificationController::markAllRead failed: " . $e->getMessage());
        }

        if ($this->isAjaxRequest()) {
            $this->respondJson(['success' => true, 'message' => 'All marked as read']);
        }
        $this->redirectToIndex();
    }

    public function delete($id) {
        $this->requireAdmin();
        $userId = $this->getUserId();
        try {
            $stmt = $this->db->prepare("DELETE FROM notifications WHERE id = ? AND (user_id = ? OR user_id IS NULL)");
            $stmt->execute([(int)$id, $userId]);
        } catch (\Throwable $e) {
            error_log("AdminNotificationController::delete failed: " . $e->getMessage());
        }

        if ($this->isAjaxRequest()) {
            $this->respondJson(['success' => true]);
        }
        $this->redirectToIndex();
    }

    public function clearRead() {
        $this->requireAdmin();
        $userId = $this->getUserId();
        try {
            $stmt = $this->db->prepare("DELETE FROM notifications WHERE is_read = 1 AND (user_id = ? OR user_id IS NULL)");
            $stmt->execute([$userId]);
        } catch (\Throwable $e) {
            error_log("AdminNotificationController::clearRead failed: " . $e->getMessage());
        }

        if ($this->isAjaxRequest()) {
            $this->respondJson(['success' => true, 'message' => 'Read notifications cleared']);
        }
        $this->redirectToIndex();
    }

    public function bookingLog() {
        $this->requireAdmin();

        $filters = [
            'type'      => $_GET['type'] ?? '',
            'channel'   => $_GET['channel'] ?? '',
            'status'    => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to'] ?? '',
            'search'    => $_GET['search'] ?? '',
            'limit'     => 50,
            'offset'    => 0,
        ];

        $filters = array_filter($filters, fn($v) => $v !== '' && $v !== null);

        try {
            $notifier = new \App\Services\BookingNotificationService();
            $logs = $notifier->getBookingLog($filters);
            $stats = $notifier->getLogStats();
        } catch (\Throwable $e) {
            error_log("[AdminNotificationController] bookingLog error: " . $e->getMessage());
            $logs = [];
            $stats = ['total' => 0, 'email_sent' => 0, 'sms_sent' => 0, 'failed' => 0, 'today' => 0];
        }

        $this->render('admin/notifications/booking-log', [
            'page_title'   => 'Booking Notification Log - APS Dream Home',
            'page_heading' => 'Booking Notification Log',
            'logs'         => $logs,
            'stats'        => $stats,
            'filters'      => $filters,
        ]);
    }

    public function archiveNow() {
        $this->requireAdmin();
        $days = max(30, (int)($_POST['days'] ?? 90));
        $limit = max(10, min(1000, (int)($_POST['limit'] ?? 500)));

        try {
            $archiveService = new \App\Services\NotificationArchiveService($this->db);
            $result = $archiveService->archiveOldNotifications($days, $limit);
            $count = $result['archived_count'] ?? 0;
            $msg = "Archived {$count} old read notifications to archive table.";
            if ($this->isAjaxRequest()) {
                $this->respondJson([
                    'success'  => true,
                    'message'  => $msg,
                    'archived' => $count,
                    'stats'    => $archiveService->getArchiveStats(),
                ]);
            }
            $_SESSION['flash_success'] = $msg;
        } catch (\Throwable $e) {
            error_log("AdminNotificationController::archiveNow failed: " . $e->getMessage());
            if ($this->isAjaxRequest()) {
                $this->respondJson(['success' => false, 'error' => $e->getMessage()], 500);
            }
            $_SESSION['flash_error'] = "Archiving failed: " . $e->getMessage();
        }

        $this->redirectToIndex();
    }

    public function preferences() {
        $this->requireAdmin();
        $userId = $this->getUserId();

        $types = [
            'leads'       => ['title' => 'New Leads & Chatbot Inquiries', 'desc' => 'Instant alerts when potential buyers submit inquiries or chat', 'icon' => 'fa-user-plus'],
            'inquiries'   => ['title' => 'Direct Inquiries & Assignments', 'desc' => 'Notifications when inquiries are assigned to staff or updated', 'icon' => 'fa-envelope-open-text'],
            'payments'    => ['title' => 'Payment Confirmations & Invoices', 'desc' => 'Alerts for token payments, EMI receipts, and approvals', 'icon' => 'fa-rupee-sign'],
            'alerts'      => ['title' => 'System & Security Alerts', 'desc' => 'Database backup status, error alerts, and security logins', 'icon' => 'fa-shield-alt'],
            'reviews'     => ['title' => 'Performance Reviews & Tasks', 'desc' => 'Employee review schedules, approvals, and grievances', 'icon' => 'fa-clipboard-check'],
            'broadcast'   => ['title' => 'Company Broadcasts & Bulletins', 'desc' => 'Official announcements and notices to all staff', 'icon' => 'fa-bullhorn'],
        ];

        // Load existing preferences from user_notification_preferences
        $prefs = [];
        foreach (array_keys($types) as $typeKey) {
            $prefs[$typeKey] = [
                'in_app'   => true,
                'push'     => true,
                'email'    => true,
                'whatsapp' => false,
            ];
        }

        try {
            $stmt = $this->db->prepare("SELECT notification_type, email_enabled, sms_enabled, whatsapp_enabled, push_enabled FROM user_notification_preferences WHERE user_id = ?");
            $stmt->execute([$userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $k = $r['notification_type'];
                if (isset($prefs[$k])) {
                    $prefs[$k]['email']    = (bool)$r['email_enabled'];
                    $prefs[$k]['whatsapp'] = (bool)$r['whatsapp_enabled'];
                    $prefs[$k]['push']     = (bool)$r['push_enabled'];
                    $prefs[$k]['in_app']   = (bool)($r['sms_enabled'] ?? 1); // reuse sms_enabled column as in-app
                }
            }
        } catch (\Throwable $e) {
            error_log("AdminNotificationController::preferences load error: " . $e->getMessage());
        }

        $archiveService = new \App\Services\NotificationArchiveService($this->db);
        $archiveStats = $archiveService->getArchiveStats();

        $this->render('admin/notifications/preferences', [
            'page_title'   => 'Notification Preferences - APS Dream Home',
            'page_heading' => 'Notification Preferences',
            'types'        => $types,
            'prefs'        => $prefs,
            'archive_stats'=> $archiveStats,
            'flash_success'=> $_SESSION['flash_success'] ?? '',
            'flash_error'  => $_SESSION['flash_error'] ?? '',
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function savePreferences() {
        $this->requireAdmin();
        $userId = $this->getUserId();
        $submitted = $_POST['channels'] ?? [];

        $knownTypes = ['leads', 'inquiries', 'payments', 'alerts', 'reviews', 'broadcast'];

        try {
            $stmt = $this->db->prepare("INSERT INTO user_notification_preferences
                (user_id, user_type, notification_type, email_enabled, sms_enabled, whatsapp_enabled, push_enabled, frequency, updated_at)
                VALUES (?, 'admin', ?, ?, ?, ?, ?, 'immediate', NOW())
                ON DUPLICATE KEY UPDATE
                    email_enabled = VALUES(email_enabled),
                    sms_enabled = VALUES(sms_enabled),
                    whatsapp_enabled = VALUES(whatsapp_enabled),
                    push_enabled = VALUES(push_enabled),
                    updated_at = NOW()");

            foreach ($knownTypes as $type) {
                $ch = $submitted[$type] ?? [];
                $email    = !empty($ch['email']) ? 1 : 0;
                $inApp    = !empty($ch['in_app']) ? 1 : 0;
                $whatsapp = !empty($ch['whatsapp']) ? 1 : 0;
                $push     = !empty($ch['push']) ? 1 : 0;

                $stmt->execute([$userId, $type, $email, $inApp, $whatsapp, $push]);
            }

            $_SESSION['flash_success'] = 'Notification preferences updated successfully.';
        } catch (\Throwable $e) {
            error_log("AdminNotificationController::savePreferences error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to save preferences: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/admin/notification-preferences');
        exit;
    }
}
