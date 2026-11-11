<?php

namespace App\Services\Messenger;

use App\Core\Database\Database;
use Psr\Log\LoggerInterface;
use PDO;

/**
 * DeadLetterMonitorService - Monitors and manages failed messages in the messenger queue
 * Provides alerting, statistics, and recovery capabilities
 */
class DeadLetterMonitorService
{
    private Database $db;
    private LoggerInterface $logger;
    private string $alertEmail;
    private int $thresholdCount;
    private int $retentionDays;

    public function __construct(
        Database $db = null,
        LoggerInterface $logger = null,
        string $alertEmail = '',
        int $thresholdCount = 10,
        int $retentionDays = 30
    ) {
        $this->db = $db ?? Database::getInstance();
        $this->logger = $logger ?? new \Psr\Log\NullLogger();
        $this->alertEmail = $alertEmail ?? ($_ENV['DEAD_LETTER_ALERT_EMAIL'] ?? '');
        $this->thresholdCount = $thresholdCount;
        $this->retentionDays = $retentionDays;
    }

    public function getThresholdCount(): int
    {
        return $this->thresholdCount;
    }
    
    public function getRetentionDays(): int
    {
        return $this->retentionDays;
    }
    
    public function getAlertEmail(): string
    {
        return $this->alertEmail;
    }
    
    /**
     * Get statistics about failed messages
     */
    public function getStats(): array
    {
        $pdo = $this->db->getConnection();
        
        $stats = [
            'total_failed' => 0,
            'by_transport' => [],
            'by_class' => [],
            'recent_failures' => [],
            'oldest_failure' => null,
            'newest_failure' => null,
        ];

        try {
            // Total failed count
            $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM messenger_messages WHERE queue_name = 'failed'");
            $stats['total_failed'] = (int)$stmt->fetchColumn();

            // By transport (queue name)
            $stmt = $pdo->query("
                SELECT queue_name, COUNT(*) as cnt 
                FROM messenger_messages 
                WHERE queue_name = 'failed'
                GROUP BY queue_name
            ");
            $stats['by_transport'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            // By message class
            $stmt = $pdo->query("
                SELECT 
                    JSON_UNQUOTE(JSON_EXTRACT(body, '$.class')) as class_name,
                    COUNT(*) as cnt
                FROM messenger_messages 
                WHERE queue_name = 'failed'
                GROUP BY class_name
            ");
            $stats['by_class'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            // Recent failures (last 24 hours)
            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    queue_name,
                    JSON_UNQUOTE(JSON_EXTRACT(body, '$.class')) as class_name,
                    available_at,
                    created_at
                FROM messenger_messages 
                WHERE queue_name = 'failed'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
                ORDER BY created_at DESC
                LIMIT 20
            ");
            $stmt->execute();
            $stats['recent_failures'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Oldest and newest
            $stmt = $pdo->query("
                SELECT MIN(created_at) as oldest, MAX(created_at) as newest
                FROM messenger_messages 
                WHERE queue_name = 'failed'
            ");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['oldest_failure'] = $row['oldest'] ?? null;
            $stats['newest_failure'] = $row['newest'] ?? null;

        } catch (\Throwable $e) {
            $this->logger->error('DeadLetterMonitorService: Failed to get stats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $stats;
    }

    /**
     * Check if alert threshold is exceeded
     */
    public function checkThreshold(): bool
    {
        $stats = $this->getStats();
        return $stats['total_failed'] >= $this->thresholdCount;
    }

    /**
     * Send alert if threshold exceeded
     */
    public function sendAlertIfNeeded(): void
    {
        if (!$this->checkThreshold() || empty($this->alertEmail)) {
            return;
        }

        $stats = $this->getStats();
        
        $subject = "[ALERT] Dead Letter Queue Threshold Exceeded";
        $message = "Dead letter queue has {$stats['total_failed']} failed messages (threshold: {$this->thresholdCount}).\n\n";
        $message .= "By Transport:\n";
        foreach ($stats['by_transport'] as $transport => $count) {
            $message .= "  - $transport: $count\n";
        }
        $message .= "\nBy Message Class:\n";
        foreach ($stats['by_class'] as $class => $count) {
            $message .= "  - $class: $count\n";
        }
        $message .= "\nRecent failures:\n";
        foreach ($stats['recent_failures'] as $failure) {
            $message .= "  - [{$failure['created_at']}] {$failure['class_name']} on {$failure['queue_name']}\n";
        }

        // Use the email service to send alert
        try {
            $emailService = new \App\Services\Communication\EmailService();
            $emailService->send(
                $this->alertEmail,
                $subject,
                nl2br($message),
                $message,
                []
            );
            $this->logger->info('Dead letter alert sent', ['to' => $this->alertEmail, 'count' => $stats['total_failed']]);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to send dead letter alert', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get failed messages for inspection
     */
    public function getFailedMessages(int $limit = 50, int $offset = 0): array
    {
        $pdo = $this->db->getConnection();
        
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    queue_name,
                    body,
                    available_at,
                    created_at
                FROM messenger_messages 
                WHERE queue_name = 'failed'
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $this->logger->error('DeadLetterMonitorService: Failed to get messages', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Retry a specific failed message
     */
    public function retryMessage(int $messageId): bool
    {
        $pdo = $this->db->getConnection();
        
        try {
            $pdo->beginTransaction();
            
            // Get the message
            $stmt = $pdo->prepare("SELECT * FROM messenger_messages WHERE id = ? AND queue_name = 'failed'");
            $stmt->execute([$messageId]);
            $message = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$message) {
                $pdo->rollBack();
                return false;
            }

            // Determine original transport from body
            $body = json_decode($message['body'], true);
            $originalTransport = $body['transport'] ?? 'notifications';
            
            // Move back to original queue
            $stmt = $pdo->prepare("
                UPDATE messenger_messages 
                SET queue_name = ?, available_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$originalTransport, $messageId]);
            
            $pdo->commit();
            
            $this->logger->info('Dead letter message retried', [
                'message_id' => $messageId,
                'original_transport' => $originalTransport,
            ]);
            
            return true;
            
        } catch (\Throwable $e) {
            $pdo->rollBack();
            $this->logger->error('Failed to retry dead letter message', [
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Delete a specific failed message
     */
    public function deleteMessage(int $messageId): bool
    {
        $pdo = $this->db->getConnection();
        
        try {
            $stmt = $pdo->prepare("DELETE FROM messenger_messages WHERE id = ? AND queue_name = 'failed'");
            $result = $stmt->execute([$messageId]);
            
            if ($result) {
                $this->logger->info('Dead letter message deleted', ['message_id' => $messageId]);
            }
            
            return $result;
            
        } catch (\Throwable $e) {
            $this->logger->error('Failed to delete dead letter message', [
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Clean up old failed messages
     */
    public function cleanupOldMessages(): int
    {
        $pdo = $this->db->getConnection();
        
        try {
            $stmt = $pdo->prepare("
                DELETE FROM messenger_messages 
                WHERE queue_name = 'failed'
                AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$this->retentionDays]);
            $deleted = $stmt->rowCount();
            
            if ($deleted > 0) {
                $this->logger->info('Cleaned up old dead letter messages', ['deleted' => $deleted]);
            }
            
            return $deleted;
            
        } catch (\Throwable $e) {
            $this->logger->error('Failed to cleanup old dead letter messages', [
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Get all failed messages grouped by transport for admin UI
     */
    public function getFailedByTransport(): array
    {
        $pdo = $this->db->getConnection();
        
        try {
            $stmt = $pdo->query("
                SELECT 
                    queue_name,
                    JSON_UNQUOTE(JSON_EXTRACT(body, '$.class')) as class_name,
                    COUNT(*) as count,
                    MIN(created_at) as first_failed,
                    MAX(created_at) as last_failed
                FROM messenger_messages 
                WHERE queue_name = 'failed'
                GROUP BY queue_name, class_name
                ORDER BY count DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to get failed by transport', ['error' => $e->getMessage()]);
            return [];
        }
    }
}