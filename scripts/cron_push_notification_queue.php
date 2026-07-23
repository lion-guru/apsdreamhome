<?php
/**
 * Push Notification Queue Processor Cron
 *
 * Processes pending push notifications from push_notification_queue table
 * using the new PushNotificationAdminController processQueueBatch method.
 *
 * Run via: php C:\xampp\htdocs\apsdreamhome\scripts\cron_push_notification_queue.php
 * Or schedule every 1-5 minutes in Windows Task Scheduler / cron.
 */

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Services\Communication\PushNotificationService;

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Push Notification Queue Processor started\n";

$db = Database::getInstance();
$pushService = new PushNotificationService();

try {
    $pending = $db->fetchAll(
        "SELECT * FROM push_notification_queue 
         WHERE status = 'pending' AND channel = 'push' 
           AND (scheduled_at IS NULL OR scheduled_at <= NOW())
         ORDER BY 
           CASE priority 
             WHEN 'urgent' THEN 1 
             WHEN 'high' THEN 2 
             WHEN 'normal' THEN 3 
             WHEN 'low' THEN 4 
             ELSE 5 
           END,
           scheduled_at ASC, created_at ASC
         LIMIT 50"
    );

    if (empty($pending)) {
        echo "No push notifications in queue.\n";
    } else {
        echo "Found " . count($pending) . " push notifications to process\n";
    }

    $sent = 0;
    $failed = 0;

    foreach ($pending as $notification) {
        $id = $notification['id'];
        $userId = $notification['user_id'];

        $db->query(
            "UPDATE push_notification_queue SET status = 'processing', updated_at = NOW() WHERE id = ?",
            [$id]
        );

        $payload = [
            'title' => $notification['title'] ?? 'Notification',
            'body' => $notification['message'] ?? '',
            'data' => json_decode($notification['data'] ?? '{}', true) ?: []
        ];

        if (!isset($payload['data']['type'])) {
            $payload['data']['type'] = $notification['type'] ?? 'general';
        }

        try {
            $result = $pushService->sendToUser((int)$userId, $payload);

            if ($result['success'] ?? false) {
                $db->query(
                    "UPDATE push_notification_queue SET status = 'sent', sent_at = NOW(), updated_at = NOW() WHERE id = ?",
                    [$id]
                );
                $sent++;
                echo "  OK [$id]: Sent to user $userId\n";
            } else {
                $error = $result['error'] ?? 'Unknown error';
                $db->query(
                    "UPDATE push_notification_queue SET status = 'failed', error_message = ?, updated_at = NOW() WHERE id = ?",
                    [$error, $id]
                );
                $failed++;
                echo "  FAIL [$id]: $error\n";
            }
        } catch (\Exception $e) {
            $db->query(
                "UPDATE push_notification_queue SET status = 'failed', error_message = ?, updated_at = NOW() WHERE id = ?",
                [$e->getMessage(), $id]
            );
            $failed++;
            echo "  ERROR [$id]: " . $e->getMessage() . "\n";
        }
    }

    echo "\n--- Push Queue Summary ---\n";
    echo "Processed: " . count($pending) . " | Sent: $sent | Failed: $failed\n";

} catch (\Exception $e) {
    echo "ERROR processing push queue: " . $e->getMessage() . "\n";
}

$stale = $db->query(
    "UPDATE push_notification_queue
     SET status = 'failed', error_message = 'Stuck in processing state', updated_at = NOW()
     WHERE status = 'processing' AND updated_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)"
);
if ($stale->rowCount() > 0) {
    echo "\nCleaned up " . $stale->rowCount() . " stale processing items\n";
}

echo "[" . date('Y-m-d H:i:s') . "] Push Notification Queue Processor completed\n";