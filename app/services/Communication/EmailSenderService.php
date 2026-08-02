<?php
namespace App\Services\Communication;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use App\Core\Middleware\TenantContext;
use App\Core\Database\Database;
use \App\Traits\ServiceTenantTrait;

class EmailSenderService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $mailer;
    private $config;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadConfig();
        $this->initMailer();
    }

    private function loadConfig()
    {
        $this->config = [
            'host' => $_ENV['SMTP_HOST'] ?? 'mail.apsdreamhome.com',
            'port' => $_ENV['SMTP_PORT'] ?? 587,
            'user' => $_ENV['SMTP_USER'] ?? '',
            'pass' => $_ENV['SMTP_PASS'] ?? '',
            'encryption' => $_ENV['SMTP_ENCRYPTION'] ?? 'tls',
            'from_email' => $_ENV['SMTP_FROM_EMAIL'] ?? 'notifications@apsdreamhome.com',
            'from_name' => $_ENV['SMTP_FROM_NAME'] ?? 'APS Dream Home',
        ];

        try {
            $row = $this->db->fetchOne("SELECT `key`, `value` FROM email_config WHERE `key` IN ('smtp_host','smtp_port','smtp_user','smtp_pass','smtp_encryption','from_email','from_name')");
        } catch (\Exception $e) {
            return;
        }
    }

    private function initMailer()
    {
        $this->mailer = new PHPMailer(true);
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['user'];
            $this->mailer->Password = $this->config['pass'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port = (int)$this->config['port'];
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
            $this->mailer->isHTML(true);
        } catch (PHPMailerException $e) {
            error_log("EmailSenderService: Mailer init failed: " . $e->getMessage());
        }
    }

    public function send($to, $subject, $bodyHtml, $bodyText = '')
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $bodyHtml;
            $this->mailer->AltBody = $bodyText ?: strip_tags($bodyHtml);
            $result = $this->mailer->send();
            $this->logToQueue($to, $subject, $bodyHtml, $bodyText, $result ? 'sent' : 'failed');
            return $result;
        } catch (PHPMailerException $e) {
            error_log("EmailSenderService: Send failed: " . $e->getMessage());
            $this->logToQueue($to, $subject, $bodyHtml, $bodyText, 'failed', $e->getMessage());
            return false;
        }
    }

public function sendFromQueue($queueId)
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $sql = "SELECT * FROM email_queue WHERE id = ? AND status = 'pending'" . $tenantFilter;
        $params = [$queueId];
        if ($tid > 1) $params[] = $tid;
        $email = $this->db->fetchOne($sql, $params);
        if (!$email) return false;

        $updParams = [$queueId];
        if ($tid > 1) $updParams[] = $tid;
        $this->db->query("UPDATE email_queue SET status = 'processing', attempts = attempts + 1 WHERE id = ?" . $tenantFilter, $updParams);

        try {
            $sent = $this->send($email['to_email'], $email['subject'], $email['body_html'], $email['body_text'] ?? '');
            $status = $sent ? 'sent' : 'failed';
            $error = $sent ? null : 'Send method returned false';
            $updParams2 = [$status, $error, $queueId];
            if ($tid > 1) $updParams2[] = $tid;
            $this->db->query(
                "UPDATE email_queue SET status = ?, sent_at = NOW(), error_message = ? WHERE id = ?" . $tenantFilter,
                $updParams2
            );
            return $sent;
        } catch (\Exception $e) {
            $updParams3 = [$e->getMessage(), $queueId];
            if ($tid > 1) $updParams3[] = $tid;
            $this->db->query(
                "UPDATE email_queue SET status = 'failed', error_message = ? WHERE id = ?" . $tenantFilter,
                $updParams3
            );
            return false;
        }
    }

public function processQueue($limit = 10)
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $sql = "SELECT * FROM email_queue 
                WHERE status = 'pending' AND attempts < 3" . $tenantFilter . "
                ORDER BY created_at ASC LIMIT ?";
        $params = [$limit];
        if ($tid > 1) $params[] = $tid;
        $emails = $this->db->fetchAll($sql, $params);
        $results = ['processed' => 0, 'sent' => 0, 'failed' => 0];
        foreach ($emails as $email) {
            $ok = $this->sendFromQueue($email['id']);
            $results['processed']++;
            if ($ok) $results['sent']++; else $results['failed']++;
        }
        return $results;
    }

    private function logToQueue($to, $subject, $bodyHtml, $bodyText, $status, $error = null)
    {
        try {
            $this->db->insert('email_queue', [
                'tenant_id' => TenantContext::getId(),
                'to_email' => $to,
                'subject' => $subject,
                'body_html' => $bodyHtml,
                'body_text' => $bodyText ?: strip_tags($bodyHtml),
                'status' => $status,
                'error_message' => $error,
                'attempts' => $status === 'sent' ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'sent_at' => $status === 'sent' ? date('Y-m-d H:i:s') : null,
            ]);
        } catch (\Exception $e) {
            error_log("EmailSenderService: Queue log failed: " . $e->getMessage());
        }
    }

public function getQueueStats()
    {
        $stats = ['pending' => 0, 'processing' => 0, 'sent' => 0, 'failed' => 0, 'cancelled' => 0];
        try {
            $tid = $this->tenantId();
            $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
            $rows = $this->db->fetchAll("SELECT status, COUNT(*) as cnt FROM email_queue WHERE 1=1" . $tenantFilter . " GROUP BY status", $tid > 1 ? [$tid] : []);
            foreach ($rows as $r) $stats[$r['status']] = (int)$r['cnt'];
        } catch (\Exception $e) {
                    error_log("EmailSenderService.php: " . $e->getMessage());
        }
        return $stats;
    }

    public function getQueueItems($status = null, $limit = 50, $offset = 0)
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $where = $status ? "WHERE status = ?" : "";
        $params = $status ? [$status] : [];
        if ($tid > 1) $params[] = $tid;
        try {
            $sql = "SELECT * FROM email_queue $where" . $tenantFilter . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            return $this->db->fetchAll($sql, array_merge($params, [$limit, $offset]));
        } catch (\Exception $e) { return []; }
    }
}
