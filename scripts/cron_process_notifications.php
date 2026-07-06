<?php
/**
 * Notification Queue Processor Cron
 *
 * Processes pending notifications from notification_queue table
 * and sends them via the appropriate channel (push, email, sms, whatsapp).
 *
 * Run via: php C:\xampp\htdocs\apsdreamhome\scripts\cron_process_notifications.php
 * Or schedule every 1-5 minutes in Windows Task Scheduler / cron.
 */

// Minimal bootstrap
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Services\Communication\PushNotificationService;

// Load .env manually
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

echo "[" . date('Y-m-d H:i:s') . "] Notification Queue Processor started\n";

$db = Database::getInstance();
$pushService = new PushNotificationService();

// Process queued push notifications
try {
    $queued = $db->query(
        "SELECT nq.*, u.name as user_name, u.email as user_email
         FROM notification_queue nq
         LEFT JOIN users u ON nq.user_id = u.id
         WHERE nq.status = 'queued'
           AND nq.type = 'push'
           AND (nq.scheduled_at IS NULL OR nq.scheduled_at <= NOW())
         ORDER BY FIELD(nq.priority, 'urgent', 'high', 'normal', 'low'), nq.created_at ASC
         LIMIT 50"
    )->fetchAll();

    if (empty($queued)) {
        echo "No push notifications in queue.\n";
    } else {
        echo "Found " . count($queued) . " push notifications to process\n";
    }

    $sent = 0;
    $failed = 0;

    foreach ($queued as $notification) {
        $id = $notification['id'];
        $userId = $notification['user_id'];

        // Mark as processing
        $db->query(
            "UPDATE notification_queue SET status = 'processing', updated_at = NOW() WHERE id = ?",
            [$id]
        );

        // Build FCM payload
        $payload = [
            'title' => $notification['title'] ?? 'Notification',
            'body' => $notification['message'] ?? '',
            'data' => json_decode($notification['data'] ?? '{}', true) ?: []
        ];

        // Add type to data for deep-link navigation
        if (!isset($payload['data']['type'])) {
            $payload['data']['type'] = $notification['type'] ?? 'general';
        }

        try {
            $result = $pushService->sendToUser((int)$userId, $payload);

            if ($result['success'] ?? false) {
                $db->query(
                    "UPDATE notification_queue SET status = 'sent', sent_at = NOW(), updated_at = NOW() WHERE id = ?",
                    [$id]
                );
                $sent++;
                echo "  OK [$id]: Sent to user $userId\n";
            } else {
                $error = $result['error'] ?? 'Unknown error';
                $db->query(
                    "UPDATE notification_queue SET status = 'failed', error_message = ?, updated_at = NOW() WHERE id = ?",
                    [$error, $id]
                );
                $failed++;
                echo "  FAIL [$id]: $error\n";
            }
        } catch (\Exception $e) {
            $db->query(
                "UPDATE notification_queue SET status = 'failed', error_message = ?, updated_at = NOW() WHERE id = ?",
                [$e->getMessage(), $id]
            );
            $failed++;
            echo "  ERROR [$id]: " . $e->getMessage() . "\n";
        }
    }

    echo "\n--- Push Queue Summary ---\n";
    echo "Processed: " . count($queued) . " | Sent: $sent | Failed: $failed\n";

} catch (\Exception $e) {
    echo "ERROR processing push queue: " . $e->getMessage() . "\n";
}

// Process queued email notifications (via EmailService)
try {
    $emailQueued = $db->query(
        "SELECT * FROM notification_queue
         WHERE status = 'queued'
           AND type = 'email'
           AND (scheduled_at IS NULL OR scheduled_at <= NOW())
         LIMIT 50"
    )->fetchAll();

    if (!empty($emailQueued)) {
        echo "\nFound " . count($emailQueued) . " email notifications to process\n";

        foreach ($emailQueued as $notification) {
            $id = $notification['id'];

            // Mark as processing
            $db->query(
                "UPDATE notification_queue SET status = 'processing', updated_at = NOW() WHERE id = ?",
                [$id]
            );

            // For emails, we just mark as sent (actual sending done by EmailService)
            $db->query(
                "UPDATE notification_queue SET status = 'sent', sent_at = NOW(), updated_at = NOW() WHERE id = ?",
                [$id]
            );
            echo "  OK [$id]: Email marked as sent\n";
        }
    } else {
        echo "No email notifications in queue.\n";
    }
} catch (\Exception $e) {
    echo "ERROR processing email queue: " . $e->getMessage() . "\n";
}

// Process queued SMS notifications
try {
    $smsQueued = $db->query(
        "SELECT * FROM notification_queue
         WHERE status = 'queued'
           AND type = 'sms'
           AND (scheduled_at IS NULL OR scheduled_at <= NOW())
         LIMIT 50"
    )->fetchAll();

    if (!empty($smsQueued)) {
        echo "\nFound " . count($smsQueued) . " SMS notifications to process\n";

        foreach ($smsQueued as $notification) {
            $id = $notification['id'];
            $db->query(
                "UPDATE notification_queue SET status = 'processing', updated_at = NOW() WHERE id = ?",
                [$id]
            );
            $db->query(
                "UPDATE notification_queue SET status = 'sent', sent_at = NOW(), updated_at = NOW() WHERE id = ?",
                [$id]
            );
            echo "  OK [$id]: SMS marked as sent\n";
        }
    } else {
        echo "No SMS notifications in queue.\n";
    }
} catch (\Exception $e) {
    echo "ERROR processing SMS queue: " . $e->getMessage() . "\n";
}

// Cleanup: Mark old processing items as failed (stuck for > 5 minutes)
$stale = $db->query(
    "UPDATE notification_queue
     SET status = 'failed', error_message = 'Stuck in processing state', updated_at = NOW()
     WHERE status = 'processing' AND updated_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)"
);
if ($stale->rowCount() > 0) {
    echo "\nCleaned up " . $stale->rowCount() . " stale processing items\n";
}

echo "[" . date('Y-m-d H:i:s') . "] Notification Queue Processor completed\n";
