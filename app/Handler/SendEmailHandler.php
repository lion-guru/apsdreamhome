<?php

namespace App\Handler;

use App\Message\SendEmailMessage;
use App\Core\Database\Database;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendEmailHandler
{
    public function __invoke(SendEmailMessage $message): void
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            // Use existing email service
            $emailService = new \App\Services\Communication\EmailService();
            
            $result = $emailService->send(
                $message->to,
                $message->subject,
                $message->body,
                $message->from,
                $message->fromName,
                $message->attachments
            );
            
            // Log to email queue for tracking
            $queueId = 'EQ-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
            
            $stmt = $pdo->prepare("
                INSERT INTO email_queue (tenant_id, queue_id, to_email, to_name, from_email, from_name, subject, body_html, body_text, priority, status, created_at, sent_at)
                VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'sent', NOW(), NOW())
            ");
            $stmt->execute([
                $queueId,
                $message->to,
                $message->to,
                $message->from ?? 'noreply@apsdreamhome.com',
                $message->fromName ?? 'APS Dream Home',
                $message->subject,
                $message->body,
                strip_tags($message->body),
                $message->priority
            ]);
            
        } catch (\Throwable $e) {
            error_log("SendEmailHandler failed: " . $e->getMessage());
            
            // Log failed email
            try {
                $pdo = Database::getInstance()->getConnection();
                $stmt = $pdo->prepare("
                    INSERT INTO email_queue (tenant_id, queue_id, to_email, to_name, from_email, from_name, subject, body_html, body_text, priority, status, error_message, created_at)
                    VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, 'failed', ?, NOW())
                ");
                $stmt->execute([
                    'EQ-' . date('YmdHis') . '-' . mt_rand(1000, 9999),
                    $message->to,
                    $message->to,
                    $message->from ?? 'noreply@apsdreamhome.com',
                    $message->fromName ?? 'APS Dream Home',
                    $message->subject,
                    $message->body,
                    strip_tags($message->body),
                    $message->priority,
                    $e->getMessage()
                ]);
            } catch (\Throwable $e2) {
                error_log("Failed to log email error: " . $e2->getMessage());
            }
            
            throw $e;
        }
    }
}