<?php

namespace App\Handler;

use App\Message\SendSmsMessage;
use App\Core\Database\Database;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendSmsHandler
{
    public function __invoke(SendSmsMessage $message): void
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            // Use existing SMS service
            $smsService = new \App\Services\Communication\SMSService();
            
            $result = $smsService->send(
                $message->to,
                $message->message,
                false // useQueue = false for immediate sending
            );
            
            // Log to sms queue for tracking (sms_queue table)
            $stmt = $pdo->prepare("
                INSERT INTO sms_queue (tenant_id, recipient, message, status, provider, created_at, sent_at)
                VALUES (1, ?, ?, 'sent', 'msg91', NOW(), NOW())
            ");
            $stmt->execute([$message->to, $message->message]);
            
        } catch (\Throwable $e) {
            error_log("SendSmsHandler failed: " . $e->getMessage());
            
            // Log failed SMS
            try {
                $pdo = Database::getInstance()->getConnection();
                $stmt = $pdo->prepare("
                    INSERT INTO sms_queue (tenant_id, recipient, message, status, provider, error_message, created_at)
                    VALUES (1, ?, ?, 'failed', 'msg91', ?, NOW())
                ");
                $stmt->execute([$message->to, $message->message, $e->getMessage()]);
            } catch (\Throwable $e2) {
                error_log("Failed to log SMS error: " . $e2->getMessage());
            }
            
            throw $e;
        }
    }
}