<?php

namespace App\Services;

use App\Core\Database\Database;

use \App\Traits\ServiceTenantTrait;

/**
 * Drip Campaign Service - Automated lead nurturing sequences
 */
class DripCampaignService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $pdo;

    public function __construct($db = null)
    {
        if ($db === null) {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
        } elseif (is_object($db) && method_exists($db, 'getPdo')) {
            $db = $db->getPdo();
        }
        $this->db = $db;
        $this->pdo = $db;
    }

    public function createCampaign(array $data): int
    {
        $insertData = $this->tenantInsertData();
        $cols = "name, description, trigger_event, status, target_filter, created_by" . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
        $ph = "?, ?, ?, ?, ?, ?" . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
        $stmt = $this->pdo->prepare("INSERT INTO drip_campaigns ($cols) VALUES ($ph)");
        $params = [
            $data['name'],
            $data['description'] ?? null,
            $data['trigger_event'] ?? 'new_lead',
            $data['status'] ?? 'draft',
            isset($data['target_filter']) ? json_encode($data['target_filter']) : null,
            $data['created_by'] ?? null
        ];
        if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
        $stmt->execute($params);
        return (int)$this->pdo->lastInsertId();
    }

    public function addEmail(int $campaignId, array $data): int
    {
        $insertData = $this->tenantInsertData();
        $cols = "campaign_id, sequence_order, delay_days, delay_hours, subject, body, channel" . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
        $ph = "?, ?, ?, ?, ?, ?, ?" . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
        $stmt = $this->pdo->prepare("INSERT INTO drip_emails ($cols) VALUES ($ph)");
        $params = [
            $campaignId,
            $data['sequence_order'] ?? 1,
            $data['delay_days'] ?? 0,
            $data['delay_hours'] ?? 0,
            $data['subject'],
            $data['body'],
            $data['channel'] ?? 'email'
        ];
        if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
        $stmt->execute($params);
        return (int)$this->pdo->lastInsertId();
    }

    public function getAllCampaigns(int $limit = 50): array
    {
        try {
            $sql = "SELECT * FROM drip_campaigns" . $this->tenantSql() . " ORDER BY created_at DESC LIMIT ?";
            $stmt = $this->pdo->prepare($sql);
            $params = [$limit];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getCampaignById(int $id): ?array
    {
        $sql = "SELECT * FROM drip_campaigns WHERE id = ?" . $this->tenantSql();
        $stmt = $this->pdo->prepare($sql);
        $params = [$id];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getEmails(int $campaignId): array
    {
        try {
            $tid = $this->tenantId();
            $sql = "SELECT * FROM drip_emails WHERE campaign_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY sequence_order ASC";
            $stmt = $this->pdo->prepare($sql);
            $params = [$campaignId];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function enroll(int $campaignId, array $data): int
    {
        $firstEmail = $this->pdo->prepare("SELECT * FROM drip_emails WHERE campaign_id = ? AND is_active = 1 ORDER BY sequence_order ASC LIMIT 1");
        $firstEmail->execute([$campaignId]);
        $first = $firstEmail->fetch();
        $nextSend = date('Y-m-d H:i:s');
        if ($first) {
            $delaySeconds = ((int)$first['delay_days'] * 86400) + ((int)$first['delay_hours'] * 3600);
            $nextSend = date('Y-m-d H:i:s', time() + $delaySeconds);
        }
        $insertData = $this->tenantInsertData();
        $cols = "campaign_id, lead_id, user_id, email, name, next_send_at, current_step" . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
        $ph = "?, ?, ?, ?, ?, ?, 0" . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
        $stmt = $this->pdo->prepare("INSERT INTO drip_enrollments ($cols) VALUES ($ph)");
        $params = [
            $campaignId,
            $data['lead_id'] ?? null,
            $data['user_id'] ?? null,
            $data['email'],
            $data['name'] ?? null,
            $nextSend
        ];
        if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
        $stmt->execute($params);
        $enrollmentId = (int)$this->pdo->lastInsertId();
        $tid = $this->tenantId();
        $this->pdo->prepare("UPDATE drip_campaigns SET total_enrolled = total_enrolled + 1 WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""))->execute($tid > 1 ? [$campaignId, $tid] : [$campaignId]);
        $this->logEnrollment($enrollmentId, $campaignId, 'enrolled', 'Enrolled in campaign');
        return $enrollmentId;
    }

    public function autoEnrollNewLeads(int $leadId, string $email, ?string $name = null): int
    {
        $tid = $this->tenantId();
        $sql = "SELECT id FROM drip_campaigns WHERE trigger_event = 'new_lead' AND status = 'active'" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($tid > 1 ? [$tid] : []);
        $cid = $stmt->fetchColumn();
        if (!$cid) return 0;
        return $this->enroll((int)$cid, ['lead_id' => $leadId, 'email' => $email, 'name' => $name]);
    }

    public function processQueue(int $limit = 100): array
    {
        $stats = ['processed' => 0, 'sent' => 0, 'failed' => 0, 'completed' => 0];
        $tid = $this->tenantId();
        try {
            $sql = "SELECT e.*, c.status as campaign_status FROM drip_enrollments e
                JOIN drip_campaigns c ON c.id = e.campaign_id
                WHERE e.status = 'active' AND c.status = 'active' AND e.next_send_at <= NOW()" . ($tid > 1 ? " AND e.tenant_id = ? AND c.tenant_id = ?" : "") . "
                ORDER BY e.next_send_at ASC LIMIT ?";
            $stmt = $this->pdo->prepare($sql);
            $params = $tid > 1 ? [$tid, $tid, $limit] : [$limit];
            $stmt->execute($params);
            $enrollments = $stmt->fetchAll();
            foreach ($enrollments as $enr) {
                $stats['processed']++;
                $emails = $this->getEmails((int)$enr['campaign_id']);
                if (empty($emails)) continue;
                $nextStep = (int)$enr['current_step'];
                if ($nextStep >= count($emails)) {
                    $this->markCompleted((int)$enr['id']);
                    $stats['completed']++;
                    continue;
                }
                $email = $emails[$nextStep];
                $vars = ['name' => $enr['name'] ?? 'Customer', 'link' => BASE_URL . '/properties', 'phone' => '+91 92771 21112'];
                $renderedSubject = $this->renderTemplate($email['subject'], $vars);
                $renderedBody = $this->renderTemplate($email['body'], $vars);
                $logId = $this->logEnrollment((int)$enr['id'], (int)$enr['campaign_id'], 'sent', $renderedSubject, (int)$email['id']);
                $this->pdo->prepare("UPDATE drip_email_log SET status = 'sent', sent_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""))->execute($tid > 1 ? [$logId, $tid] : [$logId]);
                $nextEmail = $emails[$nextStep + 1] ?? null;
                $nextSend = null;
                if ($nextEmail) {
                    $delay = ((int)$nextEmail['delay_days'] * 86400) + ((int)$nextEmail['delay_hours'] * 3600);
                    $nextSend = date('Y-m-d H:i:s', time() + $delay);
                }
                $this->pdo->prepare("UPDATE drip_enrollments SET current_step = current_step + 1, total_sent = total_sent + 1, last_sent_at = NOW(), next_send_at = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""))
                    ->execute($tid > 1 ? [$nextSend, $enr['id'], $tid] : [$nextSend, $enr['id']]);
                $this->pdo->prepare("UPDATE drip_campaigns SET emails_sent = emails_sent + 1 WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""))->execute($tid > 1 ? [$enr['campaign_id'], $tid] : [$enr['campaign_id']]);
                $stats['sent']++;
            }
        } catch (\Throwable $e) {
            $stats['error'] = $e->getMessage();
        }
        return $stats;
    }

    public function markCompleted(int $enrollmentId): void
    {
        $tid = $this->tenantId();
        $this->pdo->prepare("UPDATE drip_enrollments SET status = 'completed', completed_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""))->execute($tid > 1 ? [$enrollmentId, $tid] : [$enrollmentId]);
        $this->pdo->prepare("UPDATE drip_campaigns c JOIN drip_enrollments e ON e.campaign_id = c.id SET c.total_completed = c.total_completed + 1 WHERE e.id = ?" . ($tid > 1 ? " AND e.tenant_id = ? AND c.tenant_id = ?" : ""))->execute($tid > 1 ? [$enrollmentId, $tid, $tid] : [$enrollmentId]);
    }

    public function pauseEnrollment(int $enrollmentId): bool
    {
        $tid = $this->tenantId();
        $sql = "UPDATE drip_enrollments SET status = 'paused' WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($tid > 1 ? [$enrollmentId, $tid] : [$enrollmentId]);
        return $stmt->rowCount() > 0;
    }

    public function resumeEnrollment(int $enrollmentId): bool
    {
        $tid = $this->tenantId();
        $sql = "UPDATE drip_enrollments SET status = 'active', next_send_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($tid > 1 ? [$enrollmentId, $tid] : [$enrollmentId]);
        return $stmt->rowCount() > 0;
    }

    public function unsubscribe(int $enrollmentId): bool
    {
        $tid = $this->tenantId();
        $sql = "UPDATE drip_enrollments SET status = 'unsubscribed' WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($tid > 1 ? [$enrollmentId, $tid] : [$enrollmentId]);
        return $stmt->rowCount() > 0;
    }

    public function getEnrollments(int $campaignId, int $limit = 100): array
    {
        $tid = $this->tenantId();
        try {
            $sql = "SELECT * FROM drip_enrollments WHERE campaign_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY enrolled_at DESC LIMIT ?";
            $stmt = $this->pdo->prepare($sql);
            $params = $tid > 1 ? [$campaignId, $tid, $limit] : [$campaignId, $limit];
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getEnrollmentLog(int $enrollmentId): array
    {
        $tid = $this->tenantId();
        try {
            $sql = "SELECT * FROM drip_email_log WHERE enrollment_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $params = $tid > 1 ? [$enrollmentId, $tid] : [$enrollmentId];
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function logEnrollment(int $enrollmentId, int $campaignId, string $status, ?string $message = null, ?int $emailId = null): int
    {
        $insertData = $this->tenantInsertData();
        $cols = "enrollment_id, campaign_id, email_id, status, error_message" . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
        $ph = "?, ?, ?, ?, ?" . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
        $stmt = $this->pdo->prepare("INSERT INTO drip_email_log ($cols) VALUES ($ph)");
        $params = [$enrollmentId, $campaignId, $emailId, $status, $message];
        if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
        $stmt->execute($params);
        return (int)$this->pdo->lastInsertId();
    }

    public function renderTemplate(string $body, array $vars): string
    {
        foreach ($vars as $k => $v) {
            $body = str_replace('{{' . $k . '}}', (string)$v, $body);
        }
        return $body;
    }

    public function getStats(): array
    {
        $stats = [
            'total_campaigns' => 0, 'active_campaigns' => 0, 'draft_campaigns' => 0,
            'total_enrollments' => 0, 'active_enrollments' => 0, 'completed_enrollments' => 0,
            'emails_sent_today' => 0, 'emails_sent_week' => 0, 'avg_completion_rate' => 0
        ];
        $tid = $this->tenantId();
        $tidClause = $tid > 1 ? " AND tenant_id = ?" : "";
        $tidParam = $tid > 1 ? [$tid] : [];
        try {
            $sql = "SELECT COUNT(*) FROM drip_campaigns" . $tidClause;
            $stmt = $this->pdo->prepare($sql); $stmt->execute($tidParam);
            $stats['total_campaigns'] = (int)$stmt->fetchColumn();

            $sql = "SELECT COUNT(*) FROM drip_campaigns WHERE status = 'active'" . $tidClause;
            $stmt = $this->pdo->prepare($sql); $stmt->execute($tidParam);
            $stats['active_campaigns'] = (int)$stmt->fetchColumn();

            $sql = "SELECT COUNT(*) FROM drip_campaigns WHERE status = 'draft'" . $tidClause;
            $stmt = $this->pdo->prepare($sql); $stmt->execute($tidParam);
            $stats['draft_campaigns'] = (int)$stmt->fetchColumn();

            $sql = "SELECT COUNT(*) FROM drip_enrollments" . $tidClause;
            $stmt = $this->pdo->prepare($sql); $stmt->execute($tidParam);
            $stats['total_enrollments'] = (int)$stmt->fetchColumn();

            $sql = "SELECT COUNT(*) FROM drip_enrollments WHERE status = 'active'" . $tidClause;
            $stmt = $this->pdo->prepare($sql); $stmt->execute($tidParam);
            $stats['active_enrollments'] = (int)$stmt->fetchColumn();

            $sql = "SELECT COUNT(*) FROM drip_enrollments WHERE status = 'completed'" . $tidClause;
            $stmt = $this->pdo->prepare($sql); $stmt->execute($tidParam);
            $stats['completed_enrollments'] = (int)$stmt->fetchColumn();

            $sql = "SELECT COUNT(*) FROM drip_email_log WHERE DATE(created_at) = CURDATE() AND status = 'sent'" . $tidClause;
            $stmt = $this->pdo->prepare($sql); $stmt->execute($tidParam);
            $stats['emails_sent_today'] = (int)$stmt->fetchColumn();

            $sql = "SELECT COUNT(*) FROM drip_email_log WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status = 'sent'" . $tidClause;
            $stmt = $this->pdo->prepare($sql); $stmt->execute($tidParam);
            $stats['emails_sent_week'] = (int)$stmt->fetchColumn();

            if ($stats['total_enrollments'] > 0) {
                $stats['avg_completion_rate'] = round(($stats['completed_enrollments'] / $stats['total_enrollments']) * 100, 1);
            }
        } catch (\Throwable $e) {
        error_log($e->getMessage());
        }
        return $stats;
    }
}
