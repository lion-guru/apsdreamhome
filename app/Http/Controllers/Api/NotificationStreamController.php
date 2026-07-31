<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\NotificationService;

class NotificationStreamController extends BaseController
{
    public function __construct() { parent::__construct(); }

    public function poll()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $channel = $_GET['channel'] ?? 'global';
        $sinceId = (int)($_GET['since_id'] ?? 0);

        if (!$userId) {
            return $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }

        $notifier = new NotificationService($this->db);
        $notifications = $notifier->fetchPending($userId, $channel, 20, $sinceId);

        if (!empty($notifications)) {
            $ids = array_map(fn($n) => (int)$n['id'], $notifications);
            $notifier->markDelivered($ids);
        }

        $unread = $notifier->getUnreadCount($userId);

        return $this->jsonResponse([
            'notifications' => $notifications,
            'unread_count' => $unread,
            'last_id' => !empty($notifications) ? max(array_column($notifications, 'id')) : $sinceId,
            'timestamp' => time()
        ]);
    }

    public function markRead()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) return $this->jsonResponse(['error' => 'Unauthorized'], 401);

        $ids = json_decode($_POST['ids'] ?? '[]', true) ?: [];
        $notifier = new NotificationService($this->db);
        $count = 0;
        foreach ($ids as $id) {
            if ($notifier->markRead((int)$id)) $count++;
        }

        return $this->jsonResponse(['ok' => true, 'marked' => $count]);
    }

    public function stream()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $channel = $_GET['channel'] ?? 'global';

        if (!$userId) {
            http_response_code(401);
            exit;
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        $notifier = new NotificationService($this->db);
        $lastId = (int)($_GET['last_id'] ?? 0);
        $startTime = time();
        $maxDuration = 60;

        while (time() - $startTime < $maxDuration) {
            $notifications = $notifier->fetchPending($userId, $channel, 10, $lastId);
            if (!empty($notifications)) {
                foreach ($notifications as $n) {
                    echo "id: {$n['id']}\n";
                    echo "event: {$n['event_type']}\n";
                    echo "data: " . json_encode([
                        'id' => (int)$n['id'],
                        'event' => $n['event_type'],
                        'payload' => json_decode($n['payload'] ?? '{}', true),
                        'created_at' => $n['created_at']
                    ]) . "\n\n";
                    $lastId = max($lastId, (int)$n['id']);
                }
                $notifier->markDelivered(array_map(fn($n) => (int)$n['id'], $notifications));
                @ob_flush();
                @flush();
            }
            echo ": heartbeat\n\n";
            @ob_flush();
            @flush();
            sleep(3);
            if (connection_aborted()) break;
        }
        exit;
    }
}
