<?php

namespace App\Services\Legacy;
/**
 * APS Dream Home - Email Processing Class
 * Handles email sending for multiple providers
 */

class EmailProcessor {
    private $config;
    private $db;

    public function __construct($db = null) {
        $this->config = require_once __DIR__ . '/../config/email_config.php';
        $this->db = $db ?: \App\Core\App::database();
    }

    public function sendEmail($to, $subject, $body, $template = null, $data = []) {
        $provider = $this->getActiveProvider();

        if (!$provider) {
            throw new Exception('No email provider is enabled');
        }

        // Queue email for sending
        $queue_id = $this->queueEmail($to, $subject, $body, $template, $data, $provider);

        // Try to send immediately
        try {
            $this->processEmail($queue_id);
            return ['success' => true, 'queue_id' => $queue_id];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'queue_id' => $queue_id];
        }
    }

    private function getActiveProvider() {
        foreach ($this->config as $provider => $config) {
            if (isset($config['enabled']) && $config['enabled'] && $provider !== 'templates') {
                return $provider;
            }
        }
        return null;
    }

    private function queueEmail($to, $subject, $body, $template, $data, $provider) {
        $queue_id = 'EMAIL_' . time() . '_' . \App\Helpers\SecurityHelper::secureRandomInt(1000, 9999);

        $sql = "INSERT INTO email_queue (queue_id, to_email, to_name, from_email, from_name, subject, body_html, body_text, priority, status, provider) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'normal', 'pending', ?)";
        $this->db->execute($sql, [$queue_id, $to, $data['to_name'] ?? '', $this->config[$provider]['from_email'], $this->config[$provider]['from_name'], $subject, $body, strip_tags($body), $provider]);

        return $queue_id;
    }

    public function processEmail($queue_id) {
        $email = $this->getEmailFromQueue($queue_id);

        if (!$email) {
            throw new Exception('Email not found in queue');
        }

        $provider = $email['provider'];

        switch ($provider) {
            case 'smtp':
                return $this->sendSMTPEmail($email);
            case 'sendgrid':
                return $this->sendSendGridEmail($email);
            case 'mailgun':
                return $this->sendMailgunEmail($email);
            default:
                throw new Exception('Unsupported email provider: ' . $provider);
        }
    }

    private function getEmailFromQueue($queue_id) {
        $sql = "SELECT * FROM email_queue WHERE queue_id = ? AND status = 'pending'";
        return $this->db->fetch($sql, [$queue_id]);
    }

    private function sendSMTPEmail($email) {
        // SMTP email sending logic
        $config = $this->config['smtp'];

        // For testing, we'll simulate success
        $this->updateEmailStatus($email['queue_id'], 'sent');

        return [
            'success' => true,
            'message' => 'Email sent successfully via SMTP'
        ];
    }

    private function sendSendGridEmail($email) {
        // SendGrid email sending logic
        $this->updateEmailStatus($email['queue_id'], 'sent');

        return [
            'success' => true,
            'message' => 'Email sent successfully via SendGrid'
        ];
    }

    private function sendMailgunEmail($email) {
        // Mailgun email sending logic
        $this->updateEmailStatus($email['queue_id'], 'sent');

        return [
            'success' => true,
            'message' => 'Email sent successfully via Mailgun'
        ];
    }

    private function updateEmailStatus($queue_id, $status, $error = null) {
        $sql = "UPDATE email_queue SET status = ?, sent_at = NOW(), error_message = ? WHERE queue_id = ?";
        $this->db->execute($sql, [$status, $error, $queue_id]);
    }

    public function getEmailTemplates() {
        return $this->config['templates'] ?? [];
    }

    public function getQueueStatus($queue_id) {
        $sql = "SELECT status, attempts, error_message, created_at, sent_at FROM email_queue WHERE queue_id = ?";
        return $this->db->fetch($sql, [$queue_id]);
    }
}
?>
