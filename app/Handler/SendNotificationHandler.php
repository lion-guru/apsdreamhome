<?php

namespace App\Handler;

use App\Message\SendNotificationMessage;
use App\Core\Database\Database;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendNotificationHandler
{
    public function __invoke(SendNotificationMessage $message): void
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            $stmt = $pdo->prepare("
                INSERT INTO notifications (user_id, type, title, message, template_data, action_url, priority, is_read, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())
            ");
            
            $stmt->execute([
                $message->userId,
                $message->type,
                $message->title,
                $message->message,
                json_encode($message->data),
                $message->actionUrl,
                $message->priority
            ]);
            
            // Also send push notification if user has web push enabled
            $this->sendWebPush($message);
            
        } catch (\Throwable $e) {
            error_log("SendNotificationHandler failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function sendWebPush(SendNotificationMessage $message): void
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            // Check if user has push subscription
            $stmt = $pdo->prepare("
                SELECT endpoint, p256dh, auth FROM push_subscriptions 
                WHERE user_id = ? AND is_active = 1 LIMIT 1
            ");
            $stmt->execute([$message->userId]);
            $sub = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($sub) {
                // Use existing push service
                require_once __DIR__ . '/../../config/bootstrap.php';
                $pushService = new \App\Services\PushSender();
                $pushService->sendToUser(
                    $message->userId,
                    $message->title,
                    $message->message,
                    $message->actionUrl ?? '/notifications'
                );
            }
        } catch (\Throwable $e) {
            error_log("Web push failed: " . $e->getMessage());
            // Don't throw - push is best effort
        }
    }
}