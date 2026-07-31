<?php

namespace App\Services;

use App\Core\Database\Database;

use \App\Traits\ServiceTenantTrait;

/**
 * Email Queue Service - Background Email Processing
 * Queue emails for async sending with retry logic
 */
class EmailQueueService
{
    use \App\Traits\ServiceTenantTrait;

    private $database;
    private $maxRetries = 3;
    private $batchSize = 50;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure queue table exists
     */
    private function ensureTablesExist(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS email_queue (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            priority TINYINT DEFAULT 5,
            from_email VARCHAR(100) NOT NULL,
            from_name VARCHAR(100) NULL,
            to_email VARCHAR(100) NOT NULL,
            to_name VARCHAR(100) NULL,
            subject VARCHAR(200) NOT NULL,
            body_html LONGTEXT NULL,
            body_text LONGTEXT NULL,
            attachments JSON NULL,
            template VARCHAR(50) NULL,
            template_data JSON NULL,
            status ENUM('pending', 'processing', 'sent', 'failed', 'cancelled') DEFAULT 'pending',
            attempts TINYINT DEFAULT 0,
            error_message TEXT NULL,
            scheduled_at TIMESTAMP NULL,
            sent_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_priority (priority),
            INDEX idx_scheduled (scheduled_at),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->database->getConnection()->exec($sql);
        
        // Email templates table
        $sql2 = "CREATE TABLE IF NOT EXISTS email_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            template_code VARCHAR(50) NOT NULL UNIQUE,
            template_name VARCHAR(100) NOT NULL,
            subject VARCHAR(200) NOT NULL,
            body_html LONGTEXT NOT NULL,
            body_text LONGTEXT NOT NULL,
            variables JSON NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->database->getConnection()->exec($sql2);
        
        // Seed default templates
        $this->seedTemplates();
    }
    
    /**
     * Seed default email templates
     */
    private function seedTemplates(): void
    {
        try {
            // Check if template_code column exists (schema may differ from older migration)
            $colCheck = $this->database->getConnection()->query(
                "SHOW COLUMNS FROM email_templates LIKE 'template_code'"
            );
            if (!$colCheck || !$colCheck->fetch()) {
                error_log("EmailQueueService: email_templates table missing 'template_code' column — skipping seed");
                return;
            }
        } catch (\Exception $e) {
            error_log("EmailQueueService: cannot check email_templates schema: " . $e->getMessage());
            return;
        }
        
        $templates = [
            ['welcome_email', 'Welcome Email', 'Welcome to APS Dream Home!',
             '<h1>Welcome {{name}}!</h1><p>Thank you for joining APS Dream Home.</p>',
             'Welcome {{name}}! Thank you for joining APS Dream Home.'],
            ['booking_confirmation', 'Booking Confirmation', 'Your Booking is Confirmed - {{booking_id}}',
             '<h1>Booking Confirmed</h1><p>Dear {{customer_name}}, your booking for {{property_name}} has been confirmed.</p>',
             'Dear {{customer_name}}, your booking for {{property_name}} has been confirmed. Booking ID: {{booking_id}}'],
            ['payment_receipt', 'Payment Receipt', 'Payment Receipt - {{receipt_number}}',
             '<h1>Payment Receipt</h1><p>Amount: ₹{{amount}}</p><p>Thank you for your payment!</p>',
             'Payment Receipt - Amount: ₹{{amount}}. Thank you!'],
            ['lead_followup', 'Lead Follow-up', 'Following up on your property inquiry',
             '<p>Dear {{lead_name}}, we are following up on your interest in {{property_type}}.</p>',
             'Dear {{lead_name}}, following up on your property inquiry.'],
            ['site_visit_reminder', 'Site Visit Reminder', 'Reminder: Site Visit Tomorrow',
             '<h1>Site Visit Reminder</h1><p>Your site visit for {{property_name}} is scheduled for {{visit_date}}.</p>',
             'Reminder: Your site visit for {{property_name}} is on {{visit_date}}.'],
        ];
        
        try {
            $stmt = $this->database->prepare("INSERT IGNORE INTO email_templates 
                (template_code, template_name, subject, body_html, body_text) 
                VALUES (?, ?, ?, ?, ?)");
            foreach ($templates as $template) {
                $stmt->execute($template);
            }
        } catch (\Exception $e) {
            error_log("EmailQueueService: seed error (non-critical): " . $e->getMessage());
        }
    }
    
    /**
     * Queue an email
     */
    public function queue(string $toEmail, string $subject, string $bodyHtml, 
                         ?string $bodyText = null, array $options = []): int
    {
        $sql = "INSERT INTO email_queue 
                (priority, from_email, from_name, to_email, to_name, subject, 
                 body_html, body_text, attachments, template, template_data, scheduled_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([
            $options['priority'] ?? 5,
            $options['from_email'] ?? 'noreply@apsdreamhome.com',
            $options['from_name'] ?? 'APS Dream Home',
            $toEmail,
            $options['to_name'] ?? null,
            $subject,
            $bodyHtml,
            $bodyText ?? strip_tags($bodyHtml),
            isset($options['attachments']) ? json_encode($options['attachments']) : null,
            $options['template'] ?? null,
            isset($options['template_data']) ? json_encode($options['template_data']) : null,
            $options['scheduled_at'] ?? null
        ]);
        
        return $this->database->lastInsertId();
    }
    
    /**
     * Queue using template
     */
    public function queueTemplate(string $toEmail, string $templateCode, 
                                   array $templateData, array $options = []): int
    {
        // Get template
        $sql = "SELECT * FROM email_templates WHERE template_code = ? AND is_active = 1";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$templateCode]);
        $template = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$template) {
            throw new \Exception("Template not found: {$templateCode}");
        }
        
        // Fill template variables
        $subject = $this->fillTemplate($template['subject'], $templateData);
        $bodyHtml = $this->fillTemplate($template['body_html'], $templateData);
        $bodyText = $this->fillTemplate($template['body_text'], $templateData);
        
        return $this->queue($toEmail, $subject, $bodyHtml, $bodyText, [
            'template' => $templateCode,
            'template_data' => $templateData,
            'priority' => $options['priority'] ?? 5,
            'from_email' => $options['from_email'] ?? null,
            'from_name' => $options['from_name'] ?? null,
            'to_name' => $options['to_name'] ?? null,
            'scheduled_at' => $options['scheduled_at'] ?? null
        ]);
    }
    
    /**
     * Fill template with variables
     */
    private function fillTemplate(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
            $template = str_replace('{{ ' . $key . ' }}', $value, $template);
        }
        return $template;
    }
    
    /**
     * Process email queue
     */
    public function processQueue(int $limit = null): array
    {
        $limit = $limit ?? $this->batchSize;
        
        // Get pending emails
        $sql = "SELECT * FROM email_queue 
                WHERE status = 'pending' 
                AND (scheduled_at IS NULL OR scheduled_at <= NOW())
                AND attempts < ?
                ORDER BY priority ASC, created_at ASC
                LIMIT ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$this->maxRetries, $limit]);
        $emails = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $results = ['processed' => 0, 'sent' => 0, 'failed' => 0];
        
        foreach ($emails as $email) {
            $this->markAsProcessing($email['id']);
            
            try {
                $sent = $this->sendEmail($email);
                
                if ($sent) {
                    $this->markAsSent($email['id']);
                    $results['sent']++;
                } else {
                    $this->markAsFailed($email['id'], 'Failed to send');
                    $results['failed']++;
                }
                
                $results['processed']++;
                
            } catch (\Exception $e) {
                $this->markAsFailed($email['id'], $e->getMessage());
                $results['failed']++;
            }
        }
        
        return $results;
    }
    
    /**
     * Actually send email
     */
    private function sendEmail(array $email): bool
    {
        $headers = "From: {$email['from_name']} <{$email['from_email']}>\r\n";
        $headers .= "Reply-To: {$email['from_email']}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        // Use PHP mail function or SMTP
        if (function_exists('mail')) {
            return mail($email['to_email'], $email['subject'], $email['body_html'], $headers);
        }
        
        // Fallback: log to file for testing
        $logEntry = date('Y-m-d H:i:s') . " | TO: {$email['to_email']} | SUBJECT: {$email['subject']}\n";
        file_put_contents(STORAGE_PATH . '/logs/email.log', $logEntry, FILE_APPEND);
        
        return true;
    }
    
    /**
     * Mark as processing
     */
    private function markAsProcessing(int $id): void
    {
        $sql = "UPDATE email_queue SET status = 'processing', attempts = attempts + 1 WHERE id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$id]);
    }
    
    /**
     * Mark as sent
     */
    private function markAsSent(int $id): void
    {
        $sql = "UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$id]);
    }
    
    /**
     * Mark as failed
     */
    private function markAsFailed(int $id, string $error): void
    {
        $sql = "UPDATE email_queue SET status = 'failed', error_message = ? WHERE id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$error, $id]);
    }
    
    /**
     * Get queue statistics
     */
    public function getStats(): array
    {
        $stats = [];
        
        $sql = "SELECT status, COUNT(*) as count FROM email_queue GROUP BY status";
        $results = $this->database->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($results as $row) {
            $stats[$row['status']] = $row['count'];
        }
        
        // Hourly stats
        $sql2 = "SELECT HOUR(created_at) as hour, COUNT(*) as count 
                 FROM email_queue 
                 WHERE DATE(created_at) = CURDATE() 
                 GROUP BY HOUR(created_at)";
        $stats['hourly'] = $this->database->query($sql2)->fetchAll(\PDO::FETCH_ASSOC);
        
        return $stats;
    }
    
    /**
     * Cancel scheduled email
     */
    public function cancel(int $id): bool
    {
        $sql = "UPDATE email_queue SET status = 'cancelled' WHERE id = ? AND status = 'pending'";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Retry failed emails
     */
    public function retryFailed(): int
    {
        $sql = "UPDATE email_queue 
                SET status = 'pending', attempts = 0, error_message = NULL 
                WHERE status = 'failed' AND attempts < ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$this->maxRetries]);
        
        return $stmt->rowCount();
    }
    
    /**
     * Get all templates
     */
    public function getTemplates(): array
    {
        $sql = "SELECT * FROM email_templates WHERE is_active = 1 ORDER BY template_name";
        return $this->database->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Add custom template
     */
    public function addTemplate(string $code, string $name, string $subject, 
                                 string $html, string $text): bool
    {
        $sql = "INSERT INTO email_templates (template_code, template_name, subject, body_html, body_text) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                template_name = VALUES(template_name),
                subject = VALUES(subject),
                body_html = VALUES(body_html),
                body_text = VALUES(body_text)";
        
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$code, $name, $subject, $html, $text]);
    }
}
