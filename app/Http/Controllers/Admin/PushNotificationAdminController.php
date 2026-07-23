<?php
namespace App\Http\Controllers\Admin;

class PushNotificationAdminController extends AdminController
{
    public function skipCsrfProtection(): bool
    {
        return false;
    }

    private function db()
    {
        return \App\Core\Database::getInstance();
    }

    private function processQueueBatch(int $batchSize = 50): array
    {
        $db = $this->db();
        $pending = $db->fetchAll(
            "SELECT * FROM push_notification_queue WHERE status = 'pending' AND channel = 'push' ORDER BY priority ASC, scheduled_at ASC LIMIT ?",
            [$batchSize]
        );

        $sent = 0;
        $failed = 0;

        if (empty($pending)) {
            return ['processed' => 0, 'sent' => 0, 'failed' => 0];
        }

        $service = new \App\Services\Communication\PushNotificationService();

        foreach ($pending as $item) {
            $db->query(
                "UPDATE push_notification_queue SET status = 'processing', updated_at = NOW() WHERE id = ?",
                [$item['id']]
            );

            try {
                $result = $service->sendToUser((int)$item['user_id'], [
                    'title' => $item['title'],
                    'body' => $item['body'],
                    'data' => json_decode($item['data'] ?? '{}', true) ?: [],
                ]);

                if ($result['success'] ?? false) {
                    $db->query(
                        "UPDATE push_notification_queue SET status = 'sent', sent_at = NOW(), updated_at = NOW() WHERE id = ?",
                        [$item['id']]
                    );
                    $sent++;
                } else {
                    $retryCount = (int)$item['retry_count'] + 1;
                    $newStatus = $retryCount >= (int)$item['max_retries'] ? 'failed' : 'pending';
                    $db->query(
                        "UPDATE push_notification_queue SET status = ?, retry_count = ?, last_error = ?, updated_at = NOW() WHERE id = ?",
                        [$newStatus, $retryCount, $result['error'] ?? 'Unknown error', $item['id']]
                    );
                    $failed++;
                }
            } catch (\Throwable $e) {
                $retryCount = (int)$item['retry_count'] + 1;
                $newStatus = $retryCount >= (int)$item['max_retries'] ? 'failed' : 'pending';
                $db->query(
                    "UPDATE push_notification_queue SET status = ?, retry_count = ?, last_error = ?, updated_at = NOW() WHERE id = ?",
                    [$newStatus, $retryCount, $e->getMessage(), $item['id']]
                );
                $failed++;
            }
        }

        return ['processed' => count($pending), 'sent' => $sent, 'failed' => $failed];
    }

    public function index()
    {
        $service = new \App\Services\Communication\PushNotificationService();
        $stats = $service->getStats();
        $log = $service->getLog(20);

        $this->render('admin/push-notifications/index', [
            'stats' => $stats,
            'log' => $log,
            'page_title' => 'Push Notifications',
        ]);
    }

    public function sendForm()
    {
        $service = new \App\Services\Communication\PushNotificationService();
        $stats = $service->getStats();

        $this->render('admin/push-notifications/send', [
            'stats' => $stats,
            'page_title' => 'Send Push Notification',
        ]);
    }

    public function send()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'POST required'], 405);
            return;
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $url = trim($_POST['url'] ?? '/');
        $targetUser = $_POST['target_user'] ?? 'all';

        if (!$title || !$body) {
            $this->json(['success' => false, 'error' => 'Title and body are required'], 400);
            return;
        }

        $service = new \App\Services\Communication\PushNotificationService();

        if ($targetUser === 'all') {
            $result = $service->broadcast($title, $body, $url);
        } else {
            $userId = (int)$targetUser;
            $result = $service->send($userId, $title, $body, $url);
        }

        $this->json([
            'success' => true,
            'sent' => $result['sent'] ?? 0,
            'failed' => $result['failed'] ?? 0,
            'message' => "Push notification sent: {$result['sent']} delivered, {$result['failed']} failed",
        ]);
    }

    public function log()
    {
        $service = new \App\Services\Communication\PushNotificationService();
        $log = $service->getLog(100);

        $this->render('admin/push-notifications/log', [
            'log' => $log,
            'page_title' => 'Push Notification Log',
        ]);
    }

    public function stats()
    {
        $service = new \App\Services\Communication\PushNotificationService();
        $stats = $service->getStats();
        $this->json($stats);
    }

    // ================================================================
    //  TEMPLATE MANAGEMENT
    // ================================================================

    public function templates()
    {
        $db = $this->db();
        $templates = $db->fetchAll(
            "SELECT * FROM push_notification_templates WHERE is_active = 1 ORDER BY created_at DESC"
        );

        $this->render('admin/push-notifications/templates', [
            'templates' => $templates,
            'page_title' => 'Push Notification Templates',
        ]);
    }

    public function templateForm($id = null)
    {
        $template = null;
        if ($id) {
            $db = $this->db();
            $template = $db->fetchOne(
                "SELECT * FROM push_notification_templates WHERE id = ?",
                [(int)$id]
            );
            if (!$template) {
                $this->setFlash('error', 'Template not found');
                $this->redirect('/admin/push-notifications/templates');
                return;
            }
        }

        $this->render('admin/push-notifications/template_form', [
            'template' => $template,
            'page_title' => $template ? 'Edit Template' : 'Create Template',
        ]);
    }

    public function templateStore()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/push-notifications/templates');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $channel = $_POST['channel'] ?? 'push';
        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $variables = trim($_POST['variables'] ?? '');
        $id = (int)($_POST['id'] ?? 0);

        if (!$name || !$body) {
            $_SESSION['error'] = 'Name and body are required';
            $this->redirect($id ? "/admin/push-notifications/templates/{$id}/edit" : '/admin/push-notifications/templates/new');
            return;
        }

        $db = $this->db();

        if ($id) {
            $db->query(
                "UPDATE push_notification_templates SET name = ?, channel = ?, title = ?, body = ?, subject = ?, variables = ?, updated_at = NOW() WHERE id = ?",
                [$name, $channel, $title ?: null, $body, $subject ?: null, $variables ?: null, $id]
            );
            $_SESSION['success'] = 'Template updated successfully';
        } else {
            $db->query(
                "INSERT INTO push_notification_templates (name, channel, title, body, subject, variables, is_active, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 1, ?, NOW(), NOW())",
                [$name, $channel, $title ?: null, $body, $subject ?: null, $variables ?: null, $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0]
            );
            $_SESSION['success'] = 'Template created successfully';
        }

        $this->redirect('/admin/push-notifications/templates');
    }

    public function templateDelete($id)
    {
        $db = $this->db();
        $db->query(
            "UPDATE push_notification_templates SET is_active = 0, updated_at = NOW() WHERE id = ?",
            [(int)$id]
        );
        $_SESSION['success'] = 'Template deleted';
        $this->redirect('/admin/push-notifications/templates');
    }

    // ================================================================
    //  CAMPAIGN MANAGEMENT
    // ================================================================

    public function campaigns()
    {
        $db = $this->db();
        $campaigns = $db->fetchAll(
            "SELECT c.*, t.name AS template_name FROM push_notification_campaigns c LEFT JOIN push_notification_templates t ON t.id = c.template_id ORDER BY c.created_at DESC"
        );

        $stats = [
            'total' => count($campaigns),
            'draft' => 0,
            'running' => 0,
            'completed' => 0,
        ];
        foreach ($campaigns as $c) {
            $stats[$c['status']] = ($stats[$c['status']] ?? 0) + 1;
        }

        $this->render('admin/push-notifications/campaigns', [
            'campaigns' => $campaigns,
            'stats' => $stats,
            'page_title' => 'Push Notification Campaigns',
        ]);
    }

    public function campaignForm($id = null)
    {
        $campaign = null;
        if ($id) {
            $db = $this->db();
            $campaign = $db->fetchOne(
                "SELECT * FROM push_notification_campaigns WHERE id = ?",
                [(int)$id]
            );
            if (!$campaign) {
                $_SESSION['error'] = 'Campaign not found';
                $this->redirect('/admin/push-notifications/campaigns');
                return;
            }
        }

        $db = $this->db();
        $templates = $db->fetchAll(
            "SELECT id, name, channel, title, body FROM push_notification_templates WHERE is_active = 1 ORDER BY name"
        );

        $roles = ['admin', 'super_admin', 'manager', 'employee', 'associate', 'agent', 'customer', 'telecaller'];

        $this->render('admin/push-notifications/campaign_form', [
            'campaign' => $campaign,
            'templates' => $templates,
            'roles' => $roles,
            'page_title' => $campaign ? 'Edit Campaign' : 'Create Campaign',
        ]);
    }

    public function campaignStore()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/push-notifications/campaigns');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $channel = $_POST['channel'] ?? 'push';
        $templateId = (int)($_POST['template_id'] ?? 0) ?: null;
        $targetType = $_POST['target_type'] ?? 'all_users';
        $targetValue = trim($_POST['target_value'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $scheduledAt = trim($_POST['scheduled_at'] ?? '');
        $id = (int)($_POST['id'] ?? 0);

        if (!$name || !$title || !$body) {
            $_SESSION['error'] = 'Name, title, and body are required';
            $this->redirect($id ? "/admin/push-notifications/campaigns/{$id}/edit" : '/admin/push-notifications/campaigns/new');
            return;
        }

        $db = $this->db();

        if ($id) {
            $db->query(
                "UPDATE push_notification_campaigns SET name = ?, description = ?, channel = ?, template_id = ?, target_type = ?, target_value = ?, title = ?, body = ?, scheduled_at = NULLIF(?, ''), updated_at = NOW() WHERE id = ?",
                [$name, $description ?: null, $channel, $templateId, $targetType, $targetValue ?: null, $title, $body, $scheduledAt, $id]
            );
            $_SESSION['success'] = 'Campaign updated successfully';
        } else {
            $status = $scheduledAt ? 'scheduled' : 'draft';
            $db->query(
                "INSERT INTO push_notification_campaigns (name, description, channel, template_id, target_type, target_value, title, body, status, scheduled_at, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULLIF(?, ''), ?, NOW(), NOW())",
                [$name, $description ?: null, $channel, $templateId, $targetType, $targetValue ?: null, $title, $body, $status, $scheduledAt, $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0]
            );
            $_SESSION['success'] = 'Campaign created successfully';
        }

        $this->redirect('/admin/push-notifications/campaigns');
    }

    public function campaignLaunch($id)
    {
        $db = $this->db();
        $campaign = $db->fetchOne(
            "SELECT * FROM push_notification_campaigns WHERE id = ?",
            [(int)$id]
        );

        if (!$campaign) {
            $_SESSION['error'] = 'Campaign not found';
            $this->redirect('/admin/push-notifications/campaigns');
            return;
        }

        if (!in_array($campaign['status'], ['draft', 'scheduled', 'paused'])) {
            $_SESSION['error'] = 'Campaign cannot be launched from status: ' . $campaign['status'];
            $this->redirect('/admin/push-notifications/campaigns');
            return;
        }

        $db->query(
            "UPDATE push_notification_campaigns SET status = 'running', started_at = NOW(), updated_at = NOW() WHERE id = ?",
            [(int)$id]
        );

        $targetType = $campaign['target_type'];
        $targetValue = $campaign['target_value'];
        $userIds = [];

        switch ($targetType) {
            case 'all_users':
                $rows = $db->fetchAll("SELECT id FROM users WHERE status = 'active'");
                $userIds = array_column($rows, 'id');
                break;
            case 'role':
                $rows = $db->fetchAll("SELECT id FROM users WHERE role = ? AND status = 'active'", [$targetValue]);
                $userIds = array_column($rows, 'id');
                break;
            case 'segment':
                $rows = $db->fetchAll("SELECT id FROM users WHERE FIND_IN_SET(?, COALESCE(tags, '')) AND status = 'active'", [$targetValue]);
                $userIds = array_column($rows, 'id');
                break;
            case 'individual':
                if ($targetValue && is_numeric($targetValue)) {
                    $userIds = [(int)$targetValue];
                }
                break;
        }

        $inserted = 0;
        if (!empty($userIds)) {
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $batchParams = array_merge(
                [$campaign['title'], $campaign['body'], json_encode(['campaign_id' => (int)$id])],
                $userIds
            );

            $db->query(
                "INSERT INTO push_notification_queue (user_id, title, body, data, channel, status, priority, scheduled_at, created_at, updated_at)
                 SELECT u.id, ?, ?, ?, 'push', 'pending', 5, NOW(), NOW(), NOW()
                 FROM users u WHERE u.id IN ($placeholders)",
                $batchParams
            );
            $inserted = $db->fetchColumn("SELECT ROW_COUNT()") ?: count($userIds);
        }

        $db->query(
            "UPDATE push_notification_campaigns SET total_recipients = ?, updated_at = NOW() WHERE id = ?",
            [count($userIds), (int)$id]
        );

        $this->processQueueBatch(50);

        $campaignAfter = $db->fetchOne("SELECT sent_count, failed_count FROM push_notification_campaigns WHERE id = ?", [(int)$id]);
        if (($campaignAfter['sent_count'] ?? 0) > 0 || ($campaignAfter['failed_count'] ?? 0) > 0) {
            $allDone = ((int)($campaignAfter['sent_count'] ?? 0) + (int)($campaignAfter['failed_count'] ?? 0)) >= count($userIds);
            if ($allDone) {
                $db->query(
                    "UPDATE push_notification_campaigns SET status = 'completed', completed_at = NOW(), updated_at = NOW() WHERE id = ?",
                    [(int)$id]
                );
            }
        }

        $total = count($userIds);
        $_SESSION['success'] = "Campaign launched! Queued {$total} notifications for delivery.";
        $this->redirect("/admin/push-notifications/campaigns/{$id}");
    }

    public function campaignPause($id)
    {
        $db = $this->db();
        $db->query(
            "UPDATE push_notification_campaigns SET status = 'paused', updated_at = NOW() WHERE id = ? AND status = 'running'",
            [(int)$id]
        );
        $db->query(
            "UPDATE push_notification_queue SET status = 'cancelled', updated_at = NOW() WHERE status = 'pending' AND data LIKE ?",
            ['%"campaign_id":' . (int)$id . '%']
        );

        $_SESSION['success'] = 'Campaign paused';
        $this->redirect("/admin/push-notifications/campaigns/{$id}");
    }

    public function campaignDetail($id)
    {
        $db = $this->db();
        $campaign = $db->fetchOne(
            "SELECT c.*, t.name AS template_name FROM push_notification_campaigns c LEFT JOIN push_notification_templates t ON t.id = c.template_id WHERE c.id = ?",
            [(int)$id]
        );

        if (!$campaign) {
            $_SESSION['error'] = 'Campaign not found';
            $this->redirect('/admin/push-notifications/campaigns');
            return;
        }

        $logs = $db->fetchAll(
            "SELECT pl.*, u.name AS user_name FROM push_notification_logs pl LEFT JOIN users u ON u.id = pl.user_id WHERE pl.campaign_id = ? ORDER BY pl.created_at DESC LIMIT 50",
            [(int)$id]
        );

        $queueStats = $db->fetchOne(
            "SELECT
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) AS processing,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) AS sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
             FROM push_notification_queue WHERE data LIKE ?",
            ['%"campaign_id":' . (int)$id . '%']
        ) ?: ['pending' => 0, 'processing' => 0, 'sent' => 0, 'failed' => 0, 'cancelled' => 0];

        $this->render('admin/push-notifications/campaign_detail', [
            'campaign' => $campaign,
            'logs' => $logs,
            'queueStats' => $queueStats,
            'page_title' => 'Campaign: ' . htmlspecialchars($campaign['name']),
        ]);
    }

    // ================================================================
    //  QUEUE PROCESSING
    // ================================================================

    public function processQueue()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'POST required'], 405);
            return;
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        $result = $this->processQueueBatch(50);
        $this->json([
            'success' => true,
            'processed' => $result['processed'],
            'sent' => $result['sent'],
            'failed' => $result['failed'],
            'message' => "Processed {$result['processed']} items: {$result['sent']} sent, {$result['failed']} failed",
        ]);
    }

    public function queueStatus()
    {
        $db = $this->db();
        $stats = $db->fetchOne(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) AS processing,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) AS sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
             FROM push_notification_queue"
        );

        $todayStats = $db->fetchOne(
            "SELECT
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) AS sent_today,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed_today
             FROM push_notification_queue WHERE DATE(created_at) = CURDATE()"
        );

        $campaignStats = $db->fetchOne(
            "SELECT
                COUNT(*) AS total_campaigns,
                SUM(CASE WHEN status = 'running' THEN 1 ELSE 0 END) AS active_campaigns
             FROM push_notification_campaigns"
        );

        $this->render('admin/push-notifications/queue_status', [
            'stats' => $stats,
            'todayStats' => $todayStats,
            'campaignStats' => $campaignStats,
            'page_title' => 'Queue Status',
        ]);
    }
}
