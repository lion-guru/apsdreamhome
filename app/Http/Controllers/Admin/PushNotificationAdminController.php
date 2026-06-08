<?php
namespace App\Http\Controllers\Admin;

class PushNotificationAdminController extends AdminController
{
    public function index()
    {
        $service = new \App\Services\PushNotificationService();
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
        $service = new \App\Services\PushNotificationService();
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

        $service = new \App\Services\PushNotificationService();

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
        $service = new \App\Services\PushNotificationService();
        $log = $service->getLog(100);

        $this->render('admin/push-notifications/log', [
            'log' => $log,
            'page_title' => 'Push Notification Log',
        ]);
    }

    public function stats()
    {
        $service = new \App\Services\PushNotificationService();
        $stats = $service->getStats();
        $this->json($stats);
    }
}
