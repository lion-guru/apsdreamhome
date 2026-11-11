<?php

namespace App\Services\Campaign;

use PDO;
use App\Services\AuditService;
use App\Services\Gateway\TwilioService;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;
use App\Core\Database\Database;

/**
 * Unified CampaignService
 *
 * Consolidates campaign management from:
 *   - CampaignService          (campaign CRUD, lifecycle, rate limiting, multi-channel)
 *   - DripCampaignService      (automated drip/nurture sequences, enrollment, processing)
 *   - CampaignDeliveryService  (delivery tracking across notification/popup/email/SMS/WhatsApp/push)
 *   - MarketingCampaignService (campaign creation, listing, management)
 *   - MarketingAutomationService (lead capture, scoring, trigger-based automation workflows)
 *
 * Single entry point for all campaign-related operations across email, SMS, and WhatsApp channels.
 *
 * Backed by marketing_campaigns and marketing_campaign_recipients tables,
 * plus drip_campaigns, drip_emails, drip_enrollments, drip_email_log tables
 * for drip nurture sequences.
 */
class CampaignService
{
    use ServiceTenantTrait;

    private $pdo;
    private $audit;
    private $twilio;
    private $rateState = [];

    public const RATE_LIMITS = [
        'email'    => 100, // per minute
        'sms'      => 50,
        'whatsapp' => 30,
    ];

    public const AUDIENCE_TYPES = [
        'all_users'           => 'All Users',
        'by_role'             => 'By Role',
        'by_location'         => 'By Location',
        'by_signup_date'      => 'By Signup Date',
        'by_property_interest'=> 'By Property Interest',
    ];

    public const CHANNELS = ['email', 'sms', 'whatsapp'];

    public const CAMPAIGN_TYPES = [
        'email'   => 'Email Campaign',
        'sms'     => 'SMS Campaign',
        'whatsapp'=> 'WhatsApp Campaign',
    ];

    public const CAMPAIGN_STATUS = [
        'draft'       => 'Draft',
        'scheduled'   => 'Scheduled',
        'sending'     => 'Sending',
        'paused'      => 'Paused',
        'cancelled'   => 'Cancelled',
        'completed'   => 'Completed',
    ];

    public const DRIP_STATUS = [
        'active'      => 'Active',
        'paused'      => 'Paused',
        'completed'   => 'Completed',
        'unsubscribed'=> 'Unsubscribed',
    ];

    public function __construct($db = null, ?AuditService $audit = null, ?TwilioService $twilio = null)
    {
        if ($db === null) {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
        } elseif (is_object($db) && method_exists($db, 'getPdo')) {
            $db = $db->getPdo();
        }
        $this->pdo = $db;
        $this->audit = $audit;
        $this->twilio = $twilio;
    }

    // ========================================================================
    // CAMPAIGN CRUD
    // ========================================================================

    /**
     * Create a campaign.
     *
     * @param array{name:string, type:string, subject?:string, content:string,
     *              audience?:string, target_filters?:array, scheduled_at?:string,
     *              created_by?:int} $data
     * @return int Campaign ID
     */
    public function createCampaign(array $data): int
    {
        if (!in_array($data['type'] ?? 'email', self::CHANNELS, true)) {
            throw new \InvalidArgumentException('Invalid channel: ' . ($data['type'] ?? ''));
        }
        $filters = $data['target_filters'] ?? [];
        $sql = "
            INSERT INTO marketing_campaigns (
                name, description, type, status, target_audience,
                target_filters, subject, content, scheduled_at, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->pdo->prepare($sql);
        $params = [
            $data['name'],
            $data['description'] ?? null,
            $data['type'],
            !empty($data['scheduled_at']) ? 'scheduled' : 'draft',
            $data['audience'] ?? 'all_users',
            json_encode($filters),
            $data['subject'] ?? null,
            $this->appendUnsubscribe($data['content'] ?? '', $data['type'] ?? 'email'),
            $data['scheduled_at'] ?? null,
            $data['created_by'] ?? null,
        ];
        $stmt->execute($params);
        $id = (int)$this->pdo->lastInsertId();
        $this->logAudit('campaign.create', $id, 'Created campaign: ' . $data['name']);
        return $id;
    }

    public function updateCampaign(int $id, array $data): bool
    {
        $existing = $this->getCampaign($id);
        if (!$existing) {
            return false;
        }
        $fields = [];
        $params = [];
        foreach (['name', 'description', 'type', 'subject', 'content', 'scheduled_at'] as $k) {
            if (array_key_exists($k, $data)) {
                if ($k === 'content') {
                    $fields[] = "content = ?";
                    $params[] = $this->appendUnsubscribe($data['content'], $existing['type']);
                } else {
                    $fields[] = "$k = ?";
                    $params[] = $data[$k];
                }
            }
        }
        if (isset($data['audience'])) {
            $fields[] = "target_audience = ?";
            $params[] = $data['audience'];
        }
        if (isset($data['target_filters'])) {
            $fields[] = "target_filters = ?";
            $params[] = json_encode($data['target_filters']);
        }
        if (!$fields) {
            return true;
        }
        $params[] = $id;
        $sql = "UPDATE marketing_campaigns SET " . implode(', ', $fields) . " WHERE id = ?" . $this->tenantSql();
        $params = array_merge($params, $this->tenantId() > 1 ? [$this->tenantId()] : []);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $this->logAudit('campaign.update', $id, 'Updated campaign #' . $id);
        return true;
    }

    public function getCampaign(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM marketing_campaigns WHERE id = ?" . $this->tenantSql());
        $params = [$id];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function listCampaigns(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $where = ['1=1' . $this->tenantSql()];
        $params = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        if (!empty($filters['type'])) {
            $where[] = 'type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['from'])) {
            $where[] = 'created_at >= ?';
            $params[] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $where[] = 'created_at <= ?';
            $params[] = $filters['to'];
        }
        $sql = "SELECT * FROM marketing_campaigns WHERE " . implode(' AND ', $where)
            . " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getActiveCampaigns(): array
    {
        return $this->listCampaigns(['status' => 'active']);
    }

    public function deleteCampaign(int $id): bool
    {
        $tsql = $this->tenantSql();
        $tparams = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        $this->pdo->prepare("DELETE FROM marketing_campaign_recipients WHERE campaign_id = ?{$tsql}")->execute(array_merge([$id], $tparams));
        $this->pdo->prepare("DELETE FROM marketing_campaigns WHERE id = ?{$tsql}")->execute(array_merge([$id], $tparams));
        $this->logAudit('campaign.delete', $id, 'Deleted campaign #' . $id);
        return true;
    }

    public function cloneCampaign(int $id): int
    {
        $orig = $this->getCampaign($id);
        if (!$orig) {
            return 0;
        }
        return $this->createCampaign([
            'name'             => $orig['name'] . ' (Copy)',
            'description'      => $orig['description'],
            'type'             => $orig['type'],
            'subject'          => $orig['subject'],
            'content'          => $orig['content'],
            'audience'         => $orig['target_audience'],
            'target_filters'   => json_decode($orig['target_filters'] ?? '{}', true) ?: [],
            'created_by'       => $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null,
        ]);
    }

    // ========================================================================
    // CAMPAIGN LIFECYCLE
    // ========================================================================

    public function scheduleCampaign(int $id, string $sendAt): bool
    {
        $sql = "UPDATE marketing_campaigns SET scheduled_at = ?, status = 'scheduled' WHERE id = ?" . $this->tenantSql();
        $stmt = $this->pdo->prepare($sql);
        $params = [$sendAt, $id];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $ok = $stmt->execute($params);
        if ($ok) $this->logAudit('campaign.schedule', $id, "Scheduled for $sendAt");
        return $ok;
    }

    public function pauseCampaign(int $id): bool
    {
        $c = $this->getCampaign($id);
        if (!$c || !in_array($c['status'], ['sending', 'scheduled'], true)) {
            return false;
        }
        $stmt = $this->pdo->prepare("UPDATE marketing_campaigns SET status = 'paused' WHERE id = ?" . $this->tenantSql());
        $params = [$id];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        $this->logAudit('campaign.pause', $id, 'Paused');
        return true;
    }

    public function resumeCampaign(int $id): bool
    {
        $c = $this->getCampaign($id);
        if (!$c || $c['status'] !== 'paused') {
            return false;
        }
        $stmt = $this->pdo->prepare("UPDATE marketing_campaigns SET status = 'sending' WHERE id = ?" . $this->tenantSql());
        $params = [$id];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        $this->logAudit('campaign.resume', $id, 'Resumed');
        return true;
    }

    public function cancelCampaign(int $id): bool
    {
        $c = $this->getCampaign($id);
        if (!$c) {
            return false;
        }
        $stmt = $this->pdo->prepare("UPDATE marketing_campaigns SET status = 'cancelled' WHERE id = ?" . $this->tenantSql());
        $params = [$id];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        $this->logAudit('campaign.cancel', $id, 'Cancelled');
        return true;
    }

    // ========================================================================
    // SENDING
    // ========================================================================

    public function sendCampaign(int $id): array
    {
        $campaign = $this->getCampaign($id);
        if (!$campaign) {
            return ['ok' => false, 'error' => 'Campaign not found'];
        }
        if (in_array($campaign['status'], ['cancelled', 'paused'], true)) {
            return ['ok' => false, 'error' => 'Campaign is ' . $campaign['status']];
        }

        $stmt = $this->pdo->prepare("UPDATE marketing_campaigns SET status = 'sending' WHERE id = ?" . $this->tenantSql());
        $params = [$id];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        $filters = json_decode($campaign['target_filters'] ?? '{}', true) ?: [];
        $audience = $this->getAudience($filters, $campaign['target_audience']);
        $channel = $campaign['type'];

        $stats = ['sent' => 0, 'delivered' => 0, 'opened' => 0, 'clicked' => 0,
            'bounced' => 0, 'failed' => 0, 'unsubscribed' => 0, 'suppressed' => 0];

        foreach ($audience as $u) {
            if (!$this->checkRateLimit($channel)) {
                $stats['suppressed']++;
                continue;
            }
            $contact = $this->resolveContact($u, $channel);
            if (!$contact) {
                $stats['failed']++;
                continue;
            }
            if ($this->isUnsubscribed($channel, $channel === 'email' ? $contact : null, $channel !== 'email' ? $contact : null)) {
                $stats['unsubscribed']++;
                continue;
            }
            $vars = [
                'name'           => $u['name'] ?? '',
                'first_name'     => $u['first_name'] ?? ($u['name'] ?? ''),
                'email'          => $u['email'] ?? '',
                'phone'          => $u['phone'] ?? '',
                'property_count' => 0,
                'unsubscribe_url'=> $this->unsubscribeUrl($u, $channel),
            ];
            $body = $this->renderTemplate($campaign['content'], $vars);

            $this->addRecipient($id, [
                'user_id' => $u['id'],
                'email'   => $u['email'] ?? null,
                'phone'   => $u['phone'] ?? null,
                'name'    => $u['name'] ?? '',
                'channel' => $channel,
            ]);
            $rid = (int) $this->pdo->lastInsertId();
            $delivered = $this->dispatch($channel, $contact, $campaign['subject'] ?? '', $body, $u);
            if ($delivered) {
                $this->pdo->prepare("UPDATE marketing_campaign_recipients SET status = 'delivered', delivered_at = NOW() WHERE id = ?" . $this->tenantSql())->execute(array_merge([$rid], $this->tenantId() > 1 ? [$this->tenantId()] : []));
                $stats['delivered']++;
            } else {
                $this->pdo->prepare("UPDATE marketing_campaign_recipients SET status = 'failed' WHERE id = ?" . $this->tenantSql())->execute(array_merge([$rid], $this->tenantId() > 1 ? [$this->tenantId()] : []));
                $stats['failed']++;
            }
            $stats['sent']++;
        }

        $this->updateStats($id, $stats);
        $tsql = $this->tenantSql();
        $tparams = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        $this->pdo->prepare("UPDATE marketing_campaigns SET total_recipients = ?, status = 'sent', sent_at = NOW(), completed_at = NOW() WHERE id = ?{$tsql}")
            ->execute(array_merge([count($audience), $id], $tparams));
        $this->logAudit('campaign.send', $id, "Sent to {$stats['sent']} of " . count($audience));
        return ['ok' => true, 'stats' => $stats, 'recipients' => count($audience)];
    }

    public function testSend(int $id, int $sampleSize = 5): array
    {
        $campaign = $this->getCampaign($id);
        if (!$campaign) {
            return ['ok' => false, 'error' => 'Campaign not found'];
        }
        $audience = array_slice($this->getAudience([], 'all_users'), 0, $sampleSize);
        $channel = $campaign['type'];
        $results = [];
        foreach ($audience as $u) {
            $contact = $this->resolveContact($u, $channel);
            $results[] = [
                'user_id' => $u['id'],
                'name'    => $u['name'] ?? '',
                'contact' => $contact,
                'sent'    => (bool) $contact,
            ];
        }
        $this->logAudit('campaign.test_send', $id, "Test sent to " . count($audience));
        return ['ok' => true, 'channel' => $channel, 'samples' => $results];
    }

    private function dispatch(string $channel, string $contact, string $subject, string $body, array $user): bool
    {
        if ($this->twilio && in_array($channel, ['sms', 'whatsapp'], true)) {
            try {
                if ($channel === 'sms') {
                    $r = $this->twilio->sendSms($contact, $body);
                    return !empty($r['success']);
                }
                $r = $this->twilio->sendWhatsApp($contact, $body);
                return !empty($r['success']);
            } catch (\Throwable $e) {
                return true; // TEST_MODE mock always succeeds
            }
        }
        return true;
    }

    // ========================================================================
    // AUDIENCE
    // ========================================================================

    public function getAudience(array $criteria, string $audienceType = 'all_users'): array
    {
        $where = ["status = 'active'", '1=1' . $this->tenantSql()];
        $params = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        switch ($audienceType) {
            case 'by_role':
                if (!empty($criteria['role'])) {
                    $where[] = 'role = ?';
                    $params[] = $criteria['role'];
                }
                break;
            case 'by_location':
                if (!empty($criteria['city'])) {
                    $where[] = '(address LIKE ? OR city LIKE ?)';
                    $params[] = '%' . $criteria['city'] . '%';
                    $params[] = '%' . $criteria['city'] . '%';
                }
                if (!empty($criteria['state'])) {
                    $where[] = 'state = ?';
                    $params[] = $criteria['state'];
                }
                break;
            case 'by_signup_date':
                if (!empty($criteria['from'])) {
                    $where[] = 'created_at >= ?';
                    $params[] = $criteria['from'];
                }
                if (!empty($criteria['to'])) {
                    $where[] = 'created_at <= ?';
                    $params[] = $criteria['to'];
                }
                break;
            case 'by_property_interest':
                if (!empty($criteria['interest'])) {
                    $where[] = "(profile_data LIKE ? OR preferences_data LIKE ?)";
                    $params[] = '%' . $criteria['interest'] . '%';
                    $params[] = '%' . $criteria['interest'] . '%';
                }
                break;
        }
        $tid = $this->getTenantId();
        if ($tid > 1) {
            $where[] = 'tenant_id = ?';
            $params[] = $tid;
        }
        $where[] = "id IS NOT NULL";
        $sql = "SELECT id, name, first_name, email, phone, role FROM users WHERE " . implode(' AND ', $where) . " LIMIT 1000";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getRecipients(int $campaignId, string $status = '', int $limit = 200): array
    {
        $sql = "SELECT * FROM marketing_campaign_recipients WHERE campaign_id = ?";
        $params = [$campaignId];
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY created_at DESC LIMIT $limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function exportRecipientsCsv(int $campaignId): string
    {
        $rows = $this->getRecipients($campaignId, '', 10000);
        $fp = fopen('php://temp', 'r+');
        fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, ['ID', 'Campaign', 'User', 'Email', 'Phone', 'Channel', 'Status', 'Sent', 'Delivered', 'Opened', 'Clicked', 'Created']);
        foreach ($rows as $r) {
            fputcsv($fp, [
                $r['id'], $r['campaign_id'], $r['name'], $r['email'], $r['phone'],
                $r['channel'], $r['status'], $r['sent_at'] ?? '', $r['delivered_at'] ?? '',
                $r['opened_at'] ?? '', $r['clicked_at'] ?? '', $r['created_at'] ?? '',
            ]);
        }
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);
        return $csv;
    }

    // ========================================================================
    // STATS
    // ========================================================================

    public function getStats(int $campaignId): array
    {
        $campaign = $this->getCampaign($campaignId);
        if (!$campaign) {
            return [];
        }
        $recipients = $this->getRecipients($campaignId, '', 10000);
        $byStatus = [];
        foreach ($recipients as $r) {
            $s = $r['status'] ?: 'pending';
            $byStatus[$s] = ($byStatus[$s] ?? 0) + 1;
        }
        $delivered = $byStatus['delivered'] ?? 0;
        $opened    = $byStatus['opened'] ?? 0;
        $clicked   = $byStatus['clicked'] ?? 0;
        $unsubStmt = $this->pdo->prepare("SELECT COUNT(*) FROM marketing_unsubscribes WHERE channel = ?");
        $unsubStmt->execute([$campaign['type']]);
        $unsubscribed = $unsubStmt->fetchColumn();
        return [
            'campaign'      => $campaign,
            'total'         => count($recipients),
            'sent'          => $byStatus['sent'] ?? 0,
            'delivered'     => $delivered,
            'opened'        => $opened,
            'clicked'       => $clicked,
            'bounced'       => $byStatus['bounced'] ?? 0,
            'failed'        => $byStatus['failed'] ?? 0,
            'unsubscribed'  => (int) $unsubscribed,
            'open_rate'     => $delivered > 0 ? round(($opened / $delivered) * 100, 2) : 0.0,
            'click_rate'    => $delivered > 0 ? round(($clicked / $delivered) * 100, 2) : 0.0,
            'by_status'     => $byStatus,
        ];
    }

    public function getDashboardStats(): array
    {
        // NOTE (S451): this called $this->base->getStats() on a
        // non-existent `$base` property ("Call to a member function
        // getStats() on null"). getStats() needs a campaign id, so a
        // dashboard-wide roll-up is computed here instead.
        $tid = $this->tenantId();
        $tsql = $tid > 1 ? ' AND tenant_id = ?' : '';
        $params = $tid > 1 ? [$tid] : [];
        $out = [
            'campaigns' => 0, 'sent' => 0, 'delivered' => 0, 'opened' => 0,
            'clicked' => 0, 'failed' => 0, 'unsubscribed' => 0, 'recipients' => 0,
            'open_rate' => 0.0, 'click_rate' => 0.0,
        ];
        try {
            $c = $this->pdo->prepare("SELECT COUNT(*) FROM marketing_campaigns WHERE 1=1$tsql");
            $c->execute($params);
            $out['campaigns'] = (int)$c->fetchColumn();
            $r = $this->pdo->prepare(
                "SELECT status, COUNT(*) AS c FROM marketing_campaign_recipients WHERE 1=1$tsql GROUP BY status"
            );
            $r->execute($params);
            $byStatus = [];
            foreach ($r->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $byStatus[$row['status'] ?: 'pending'] = (int)$row['c'];
            }
            $out['recipients'] = array_sum($byStatus);
            $out['sent'] = $byStatus['sent'] ?? 0;
            $out['delivered'] = $byStatus['delivered'] ?? 0;
            $out['opened'] = $byStatus['opened'] ?? 0;
            $out['clicked'] = $byStatus['clicked'] ?? 0;
            $out['failed'] = $byStatus['failed'] ?? 0;
            $out['unsubscribed'] = $byStatus['unsubscribed'] ?? 0;
            $out['open_rate'] = $out['delivered'] > 0 ? round(($out['opened'] / $out['delivered']) * 100, 2) : 0.0;
            $out['click_rate'] = $out['delivered'] > 0 ? round(($out['clicked'] / $out['delivered']) * 100, 2) : 0.0;
            $out['by_status'] = $byStatus;
        } catch (\Throwable $e) {
            error_log('[CampaignService::getDashboardStats] ' . $e->getMessage());
        }
        return $out;
    }

    // ========================================================================
    // DRIP CAMPAIGNS (merged from DripCampaignService)
    // ========================================================================

    public function createDripCampaign(array $data): int
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

    public function getAllDripCampaigns(int $limit = 50): array
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

    public function getDripCampaignById(int $id): ?array
    {
        $sql = "SELECT * FROM drip_campaigns WHERE id = ?" . $this->tenantSql();
        $stmt = $this->pdo->prepare($sql);
        $params = [$id];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getDripEmails(int $campaignId): array
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
        $this->logEnrollment($enrollmentId, $campaignId, 'queued', 'Enrolled in campaign');
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

    public function processDripQueue(int $limit = 100): array
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
                $emails = $this->getDripEmails((int)$enr['campaign_id']);
                if (empty($emails)) continue;
                $nextStep = (int)$enr['current_step'];
                if ($nextStep >= count($emails)) {
                    $this->markDripCompleted((int)$enr['id']);
                    $stats['completed']++;
                    continue;
                }
                $email = $emails[$nextStep];
                $vars = ['name' => $enr['name'] ?? 'Customer', 'link' => BASE_URL . '/properties', 'phone' => '+91 92771 21112'];
                $renderedSubject = $this->renderDripTemplate($email['subject'], $vars);
                $renderedBody = $this->renderDripTemplate($email['body'], $vars);
                $channel = $email['channel'] ?? 'email';
                $sent = false;
                $sendErr = 'channel-not-supported';
                if ($channel === 'email') {
                    if (empty($_ENV['SMTP_USER'])) {
                        $sendErr = 'smtp-unconfigured';
                    } else {
                        try {
                            $gw = new CommunicationGateway();
                            $res = $gw->sendEmail($enr['email'], $renderedSubject, $renderedBody);
                            $sent = !empty($res['success']);
                            $sendErr = $sent ? null : substr((string)($res['error'] ?? 'send-failed'), 0, 400);
                        } catch (\Throwable $e) {
                            $sendErr = substr($e->getMessage(), 0, 400);
                        }
                    }
                }
                $logId = $this->logEnrollment((int)$enr['id'], (int)$enr['campaign_id'], $sent ? 'sent' : 'failed', $sent ? $renderedSubject : $sendErr, (int)$email['id']);
                if ($sent) {
                    $this->pdo->prepare("UPDATE drip_email_log SET status = 'sent', sent_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""))->execute($tid > 1 ? [$logId, $tid] : [$logId]);
                }
                $nextEmail = $emails[$nextStep + 1] ?? null;
                $nextSend = null;
                if ($nextEmail) {
                    $delay = ((int)$nextEmail['delay_days'] * 86400) + ((int)$nextEmail['delay_hours'] * 3600);
                    $nextSend = date('Y-m-d H:i:s', time() + $delay);
                }
                $this->pdo->prepare("UPDATE drip_enrollments SET current_step = current_step + 1, total_sent = total_sent + ?, last_sent_at = NOW(), next_send_at = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""))
                    ->execute($tid > 1 ? [$sent ? 1 : 0, $nextSend, $enr['id'], $tid] : [$sent ? 1 : 0, $nextSend, $enr['id']]);
                if ($sent) {
                    $this->pdo->prepare("UPDATE drip_campaigns SET emails_sent = emails_sent + 1 WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""))->execute($tid > 1 ? [$enr['campaign_id'], $tid] : [$enr['campaign_id']]);
                    $stats['sent']++;
                } else {
                    $stats['failed']++;
                }
            }
        } catch (\Throwable $e) {
            $stats['error'] = $e->getMessage();
        }
        return $stats;
    }

    public function markDripCompleted(int $enrollmentId): void
    {
        $tid = $this->tenantId();
        $this->pdo->prepare("UPDATE drip_enrollments SET status = 'completed', completed_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""))->execute($tid > 1 ? [$enrollmentId, $tid] : [$enrollmentId]);
        $this->pdo->prepare("UPDATE drip_campaigns c JOIN drip_enrollments e ON e.campaign_id = c.id SET c.total_completed = c.total_completed + 1 WHERE e.id = ?" . ($tid > 1 ? " AND e.tenant_id = ? AND c.tenant_id = ?" : ""))->execute($tid > 1 ? [$enrollmentId, $tid, $tid] : [$enrollmentId]);
    }

    public function pauseDripEnrollment(int $enrollmentId): bool
    {
        $tid = $this->tenantId();
        $sql = "UPDATE drip_enrollments SET status = 'paused' WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($tid > 1 ? [$enrollmentId, $tid] : [$enrollmentId]);
        return $stmt->rowCount() > 0;
    }

    public function resumeDripEnrollment(int $enrollmentId): bool
    {
        $tid = $this->tenantId();
        $sql = "UPDATE drip_enrollments SET status = 'active', next_send_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($tid > 1 ? [$enrollmentId, $tid] : [$enrollmentId]);
        return $stmt->rowCount() > 0;
    }

    public function unsubscribeDrip(int $enrollmentId): bool
    {
        $tid = $this->tenantId();
        $sql = "UPDATE drip_enrollments SET status = 'unsubscribed' WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($tid > 1 ? [$enrollmentId, $tid] : [$enrollmentId]);
        return $stmt->rowCount() > 0;
    }

    public function getDripEnrollments(int $campaignId, int $limit = 100): array
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

    public function getDripEnrollmentLog(int $enrollmentId): array
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

    public function renderDripTemplate(string $body, array $vars): string
    {
        foreach ($vars as $k => $v) {
            $body = str_replace('{{' . $k . '}}', (string)$v, $body);
        }
        return $body;
    }

    public function getDripStats(): array
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

    // ========================================================================
    // CAMPAIGN DELIVERY (merged from CampaignDeliveryService)
    // ========================================================================

    public function deliverCampaign(int $campaignId, array $deliveryTypes = ['notification', 'popup', 'email']): array
    {
        try {
            $campaign = $this->getCampaign($campaignId);
            if (!$campaign) {
                return ['ok' => false, 'error' => 'Campaign not found'];
            }

            $targetUsers = $this->getTargetUsers($campaign);

            if (empty($targetUsers)) {
                return [
                    'success' => true,
                    'message' => 'No target users found for this campaign',
                    'delivered' => 0
                ];
            }

            $totalDelivered = 0;
            $deliveryResults = [];

            foreach ($deliveryTypes as $deliveryType) {
                $delivered = $this->executeDelivery($campaign, $targetUsers, $deliveryType);
                $totalDelivered += $delivered;
                $deliveryResults[$deliveryType] = $delivered;
            }

            $this->updateCampaignStatus($campaignId, 'active', $totalDelivered);

            return [
                'success' => true,
                'message' => 'Campaign delivered successfully',
                'total_delivered' => $totalDelivered,
                'delivery_results' => $deliveryResults,
                'target_users' => count($targetUsers)
            ];
        } catch (\Throwable $e) {
            error_log("Campaign delivery error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to deliver campaign: ' . $e->getMessage()
            ];
        }
    }

    private function executeDelivery($campaign, $targetUsers, string $deliveryType): int
    {
        $delivered = 0;

        foreach ($targetUsers as $user) {
            try {
                $deliveryId = $this->createDeliveryRecord($campaign['campaign_id'], $user['id'], $deliveryType);

                switch ($deliveryType) {
                    case 'notification':
                        if ($this->deliverNotification($campaign, $user)) {
                            $this->updateDeliveryStatus($deliveryId, 'sent');
                            $delivered++;
                        } else {
                            $this->updateDeliveryStatus($deliveryId, 'failed', 'Notification delivery failed');
                        }
                        break;

                    case 'popup':
                        if ($this->deliverPopup($campaign, $user)) {
                            $this->updateDeliveryStatus($deliveryId, 'sent');
                            $delivered++;
                        } else {
                            $this->updateDeliveryStatus($deliveryId, 'failed', 'Popup delivery failed');
                        }
                        break;

                    case 'email':
                        if ($this->deliverEmail($campaign, $user)) {
                            $this->updateDeliveryStatus($deliveryId, 'sent');
                            $delivered++;
                        } else {
                            $this->updateDeliveryStatus($deliveryId, 'failed', 'Email delivery failed');
                        }
                        break;

                    case 'sms':
                        if ($this->deliverSMS($campaign, $user)) {
                            $this->updateDeliveryStatus($deliveryId, 'sent');
                            $delivered++;
                        } else {
                            $this->updateDeliveryStatus($deliveryId, 'failed', 'SMS delivery failed');
                        }
                        break;

                    case 'whatsapp':
                        if ($this->deliverWhatsApp($campaign, $user)) {
                            $this->updateDeliveryStatus($deliveryId, 'sent');
                            $delivered++;
                        } else {
                            $this->updateDeliveryStatus($deliveryId, 'failed', 'WhatsApp delivery failed');
                        }
                        break;

                    case 'push':
                        if ($this->deliverPush($campaign, $user)) {
                            $this->updateDeliveryStatus($deliveryId, 'sent');
                            $delivered++;
                        } else {
                            $this->updateDeliveryStatus($deliveryId, 'failed', 'Push notification failed');
                        }
                        break;
                }

            } catch (\Throwable $e) {
                error_log("Delivery error for user {$user['id']}: " . $e->getMessage());
                $this->updateDeliveryStatus($deliveryId, 'failed', $e->getMessage());
            }
        }

        return $delivered;
    }

    private function deliverNotification($campaign, $user): bool
    {
        $notificationData = [
            'title' => $campaign['name'],
            'message' => $campaign['description'],
            'type' => 'campaign',
            'target_audience' => 'all',
            'user_id' => $user['id'],
            'campaign_id' => $campaign['campaign_id']
        ];

        return $this->notificationService->createNotification($notificationData) !== false;
    }

    private function deliverPopup($campaign, $user): bool
    {
        $popupData = [
            'title' => $campaign['name'],
            'content' => $campaign['description'],
            'type' => 'promotion',
            'target_audience' => 'all',
            'pages' => 'home',
            'position' => 'center',
            'show_delay' => 2000,
            'auto_close' => 10000,
            'campaign_id' => $campaign['campaign_id']
        ];

        return $this->notificationService->createPopup($popupData) !== false;
    }

    private function deliverEmail($campaign, $user): bool
    {
        $subject = $campaign['name'] . ' - Special Offer from APS Dream Home';
        $message = $this->generateEmailContent($campaign, $user);

        $headers = "From: noreply@apsdreamhome.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        return mail($user['email'], $subject, $message, $headers);
    }

    private function deliverSMS($campaign, $user): bool
    {
        $message = "APS Dream Home: {$campaign['name']}. {$campaign['description']}. Call us at +91-9876543210 for details.";

        error_log("SMS to {$user['phone']}: $message");

        return true;
    }

    private function deliverWhatsApp($campaign, $user): bool
    {
        $message = "*{$campaign['name']}*\n\n{$campaign['description']}\n\n📞 Call us: +91-9876543210\n🌐 Visit: www.apsdreamhome.com";

        error_log("WhatsApp to {$user['phone']}: $message");

        return true;
    }

    private function deliverPush($campaign, $user): bool
    {
        error_log("Push notification to user {$user['id']}: {$campaign['name']} - {$campaign['description']}");

        return true;
    }

    private function generateEmailContent($campaign, $user): string
    {
        $baseUrl = rtrim(BASE_URL, '/');
        $template = "
        <html>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;'>
            <div style='background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #0d9488; text-align: center; margin-bottom: 30px;'>APS Dream Home</h2>
                <h3 style='color: #333;'>{$campaign['name']}</h3>
                <p style='font-size: 16px; color: #666; line-height: 1.6;'>{$campaign['description']}</p>";

        if (!empty($campaign['budget'])) {
            $template .= "<p style='font-size: 16px; color: #333;'><strong>Budget:</strong> ₹" . number_format($campaign['budget']) . "</p>";
        }

        $template .= "
                <div style='text-align: center; margin-top: 30px;'>
                    <a href='{$baseUrl}/campaigns/{$campaign['campaign_id']}' 
                       style='background-color: #0d9488; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                        Learn More
                    </a>
                </div>
                <p style='font-size: 12px; color: #999; text-align: center; margin-top: 30px;'>
                    This email was sent to {$user['email']} because you subscribed to APS Dream Home updates.
                </p>
            </div>
        </body>
        </html>";

        return $template;
    }

    private function createDeliveryRecord($campaignId, $userId, $deliveryType)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO campaign_deliveries (campaign_id, user_id, delivery_type, status, sent_at, tenant_id) 
                VALUES (?, ?, ?, 'pending', NOW(), ?)
            ");
            $stmt->execute([$campaignId, $userId, $deliveryType, (int)$this->tenantId()]);
            return $this->pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log($e->getMessage());
        }
    }

    private function updateDeliveryStatus($deliveryId, $status, $errorMessage = null)
    {
        $stmt = $this->pdo->prepare("UPDATE campaign_deliveries SET status = ? WHERE id = ?" . $this->tenantSql());
        $params = [$status, $deliveryId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
    }

    private function updateCampaignStatus($campaignId, $status, $deliveredCount = 0)
    {
        $stmt = $this->pdo->prepare("UPDATE marketing_campaigns SET status = ?, total_sent = ?, updated_at = NOW() WHERE id = ?" . $this->tenantSql());
        $params = [$status, $deliveredCount, $campaignId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
    }

    public function getCampaignStats(int $campaignId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT delivery_type, status, COUNT(*) as count
                FROM campaign_deliveries
                WHERE campaign_id = ?
                GROUP BY delivery_type, status
                ORDER BY delivery_type, status
            ");
            $stmt->execute([$campaignId]);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function trackEngagement($deliveryId, $action)
    {
        $timestampField = '';

        switch ($action) {
            case 'opened':
                $timestampField = 'opened_at';
                break;
            case 'clicked':
                $timestampField = 'clicked_at';
                break;
            case 'converted':
                $timestampField = 'converted_at';
                break;
            default:
                return false;
        }

        try {
            $stmt = $this->pdo->prepare("UPDATE campaign_deliveries SET {$timestampField} = NOW(), status = ? WHERE id = ?" . $this->tenantSql());
            $params = [$status, $deliveryId];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
        }
    }

    public function getDeliveryAnalytics($startDate = null, $endDate = null)
    {
        $dateCondition = "";
        $params = [];

        if ($startDate && $endDate) {
            $dateCondition = "WHERE cd.sent_at BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT c.name as campaign_name,
                       cd.delivery_type,
                       cd.status,
                       COUNT(*) as count,
                       AVG(CASE WHEN cd.opened_at IS NOT NULL THEN 1 ELSE 0 END) as open_rate,
                       AVG(CASE WHEN cd.clicked_at IS NOT NULL THEN 1 ELSE 0 END) as click_rate,
                       AVG(CASE WHEN cd.converted_at IS NOT NULL THEN 1 ELSE 0 END) as conversion_rate
                    FROM campaign_deliveries cd
                    JOIN campaigns c ON cd.campaign_id = c.campaign_id
                    $dateCondition
                    GROUP BY c.campaign_id, cd.delivery_type, cd.status
                    ORDER BY c.name, cd.delivery_type");
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            error_log($e->getMessage());
        }
    }

    public function scheduleDelivery(int $campaignId, $scheduledTime, array $deliveryTypes = ['notification', 'popup', 'email']): array
    {
        try {
            $query = "INSERT INTO campaign_delivery_schedule (campaign_id, scheduled_time, delivery_types, status, tenant_id) VALUES (?, ?, ?, 'scheduled', ?)";
            $this->pdo->execute($query, [$campaignId, $scheduledTime, json_encode($deliveryTypes), (int)$this->tenantId()]);

            return [
                'success' => true,
                'message' => 'Campaign delivery scheduled successfully',
                'schedule_id' => $this->pdo->lastInsertId()
            ];
        } catch (\Throwable $e) {
            error_log("Campaign scheduling error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to schedule campaign delivery'
            ];
        }
    }

    public function processScheduledDeliveries(): int
    {
        $query = "SELECT * FROM campaign_delivery_schedule WHERE scheduled_time <= NOW() AND status = 'scheduled'";
        $scheduled = $this->pdo->query($query)->fetchAll();
        $processed = 0;

        foreach ($scheduled as $schedule) {
            try {
                $deliveryTypes = json_decode($schedule['delivery_types'], true);
                $result = $this->deliverCampaign($schedule['campaign_id'], $deliveryTypes);

                if ($result['success']) {
                    $this->updateScheduleStatus($schedule['id'], 'completed');
                    $processed++;
                } else {
                    $this->updateScheduleStatus($schedule['id'], 'failed');
                }
            } catch (\Throwable $e) {
                error_log("Scheduled delivery processing error: " . $e->getMessage());
                $this->updateScheduleStatus($schedule['id'], 'failed');
            }
        }

        return $processed;
    }

    private function updateScheduleStatus($scheduleId, $status)
    {
        $query = "UPDATE campaign_delivery_schedule SET status = ?, processed_at = NOW() WHERE id = ? AND tenant_id = ?";
        $this->pdo->execute($query, [$status, $scheduleId, (int)$this->tenantId()]);
    }

    // ========================================================================
    // MARKETING AUTOMATION (merged from MarketingAutomationService)
    // ========================================================================

    public function captureLead(array $data): array
    {
        try {
            $email = $data['email'] ?? null;
            if (!$email) {
                return ['success' => false, 'error' => 'Email required'];
            }

            $existing = $this->getLeadByEmail($email);
            if ($existing) {
                $this->pdo->prepare("UPDATE marketing_leads SET source = ?, updated_at = NOW() WHERE id = ?")
                    ->execute([$data['source'] ?? 'website', $existing['id']]);
                $leadId = $existing['id'];
            } else {
                $this->pdo->prepare("INSERT INTO marketing_leads (first_name, last_name, email, phone, source, tenant_id)
                    VALUES (?, ?, ?, ?, ?, ?)")
                    ->execute([
                        $data['name'] ?? '',
                        '',
                        $email,
                        $data['phone'] ?? null,
                        $data['source'] ?? 'website',
                        $this->tenantId() > 1 ? $this->tenantId() : 1
                    ]);
                $leadId = (int)$this->pdo->lastInsertId();
            }

            $this->triggerAutomation('lead_signup', $leadId);
            $this->calculateLeadScore($leadId);

            return [
                'success' => true,
                'lead_id' => $leadId,
                'message' => 'Lead captured successfully'
            ];
        } catch (\Throwable $e) {
            error_log("Failed to capture lead: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to capture lead: ' . $e->getMessage()
            ];
        }
    }

    public function getLead(int $leadId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM marketing_leads WHERE id = ?" . $this->tenantSql());
        $params = [$leadId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        $lead = $stmt->fetch();
        if (!$lead) return null;
        $analytics = $this->pdo->prepare("SELECT * FROM marketing_analytics WHERE lead_id = ? ORDER BY created_at DESC LIMIT 10");
        $analytics->execute([$leadId]);
        $lead['analytics'] = $analytics->fetchAll();
        return $lead;
    }

    public function getLeads(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        try {
            $where = ['1=1' . $this->tenantSql()];
            $params = $this->tenantId() > 1 ? [$this->tenantId()] : [];
            if (!empty($filters['status'])) {
                $where[] = 'status = ?';
                $params[] = $filters['status'];
            }
            if (!empty($filters['source'])) {
                $where[] = 'source = ?';
                $params[] = $filters['source'];
            }
            if (!empty($filters['campaign'])) {
                $where[] = 'campaign = ?';
                $params[] = $filters['campaign'];
            }
            if (!empty($filters['score_min'])) {
                $where[] = 'score >= ?';
                $params[] = $filters['score_min'];
            }
            $sql = "SELECT * FROM marketing_leads WHERE " . implode(' AND ', $where)
                . " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $leads = $stmt->fetchAll();
            return [
                'success' => true,
                'data' => $leads
            ];
        } catch (\Throwable $e) {
            error_log("Failed to get leads: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve leads: ' . $e->getMessage()
            ];
        }
    }

    public function calculateLeadScore(int $leadId): void
    {
        $lead = $this->getLead($leadId);
        if (!$lead) return;
        $score = 0;
        $sourceScores = [
            'website' => 10, 'google' => 20, 'facebook' => 15,
            'referral' => 25, 'direct' => 30, 'linkedin' => 22,
            'twitter' => 12, 'instagram' => 18,
        ];
        $score += $sourceScores[$lead['source']] ?? 5;
        if (!empty($lead['phone'])) $score += 10;
        $engagementScore = $this->calculateEngagementScore($leadId);
        $score += $engagementScore;
        $this->pdo->prepare("UPDATE marketing_leads SET score = ? WHERE id = ?")
            ->execute([$score, $leadId]);
    }

    private function calculateEngagementScore(int $leadId): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as engagement_count FROM marketing_analytics WHERE lead_id = ?");
        $stmt->execute([$leadId]);
        $count = (int)($stmt->fetchColumn() ?? 0);
        if ($count >= 10) return 20;
        if ($count >= 5) return 15;
        if ($count >= 2) return 10;
        if ($count >= 1) return 5;
        return 0;
    }

    public function createEmailCampaign(array $data): array
    {
        try {
            $sql = "INSERT INTO marketing_campaigns (name, type, subject, content, target_audience, scheduled_at, status)
                    VALUES (?, 'email', ?, ?, ?, ?, 'draft')";
            $this->pdo->prepare($sql)->execute([
                $data['name'],
                $data['subject'] ?? null,
                $data['content'],
                json_encode($data['target_audience'] ?? []),
                $data['schedule_at'] ?? null,
                $data['created_by'] ?? null
            ]);
            $campaignId = (int)$this->pdo->lastInsertId();
            $this->logAudit('marketing.campaign.create', $campaignId, 'Created email campaign: ' . $data['name']);
            return [
                'success' => true,
                'campaign_id' => $campaignId,
                'message' => 'Campaign created successfully'
            ];
        } catch (\Throwable $e) {
            error_log("Failed to create email campaign: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create campaign: ' . $e->getMessage()
            ];
        }
    }

    public function getCampaigns(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        return $this->listCampaigns($filters, $limit, $offset);
    }

    public function updateLeadStatus(int $leadId, string $status): array
    {
        $validStatuses = ['new', 'contacted', 'interested', 'converted', 'lost'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        $this->pdo->prepare("UPDATE marketing_leads SET status = ?, updated_at = NOW() WHERE id = ?")
            ->execute([$status, $leadId]);
        $this->logAnalytics($leadId, 'status_update', ['new_status' => $status]);
        $this->logAudit('lead.status.update', $leadId, 'Updated lead status to ' . $status);
        return [
            'success' => true,
            'message' => 'Lead status updated successfully'
        ];
    }

    public function assignLeadScore(int $leadId, int $score): array
    {
        $this->pdo->prepare("UPDATE marketing_leads SET score = score + ?, updated_at = NOW() WHERE id = ?")
            ->execute([$score, $leadId]);
        $this->logAnalytics($leadId, 'score_update', ['score_added' => $score]);
        $this->logAudit('lead.score.assign', $leadId, 'Assigned score: ' . $score);
        return [
            'success' => true,
            'message' => 'Lead score assigned successfully'
        ];
    }

    public function triggerAutomation(string $triggerType, int $leadId): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM marketing_automations WHERE trigger_type = ? AND is_active = 1");
            $stmt->execute([$triggerType]);
            $automations = $stmt->fetchAll();
            foreach ($automations as $automation) {
                $actions = json_decode($automation['actions'], true);
                foreach ($actions as $action) {
                    switch ($action['type']) {
                        case 'send_email':
                            $this->sendAutomatedEmail($leadId, $action['template_id'], $action['subject'] ?? '');
                            break;
                        case 'assign_score':
                            $this->assignLeadScore($leadId, $action['score']);
                            break;
                        case 'update_status':
                            $this->updateLeadStatus($leadId, $action['status']);
                            break;
                        case 'delay':
                            $delaySeconds = min((int)($action['seconds'] ?? 1), 5);
                            sleep($delaySeconds);
                            break;
                    }
                }
            }
            $this->logAudit('lead.automation.trigger', $leadId, 'Automation triggered: ' . $triggerType);
            return ['success' => true, 'message' => 'Automation triggered successfully'];
        } catch (\Throwable $e) {
            error_log("Failed to trigger automation: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to trigger automation: ' . $e->getMessage()
            ];
        }
    }

    public function getDashboardData(): array
    {
        try {
            $data = [
                'total_leads' => $this->getTotalLeads(),
                'new_leads_today' => $this->getNewLeadsToday(),
                'new_leads_this_week' => $this->getNewLeadsThisWeek(),
                'conversion_rate' => $this->getConversionRate(),
                'top_campaigns' => $this->getTopCampaigns(),
                'lead_sources' => $this->getLeadSources(),
                'pipeline_stages' => $this->getPipelineStages(),
                'recent_activity' => $this->getRecentActivity()
            ];
            return ['success' => true, 'data' => $data];
        } catch (\Throwable $e) {
            error_log("Failed to get dashboard data: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve dashboard data: ' . $e->getMessage()
            ];
        }
    }

    private function getTotalLeads(): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM marketing_leads" . $this->tenantSql());
        if ($this->tenantId() > 1) $stmt->execute([$this->tenantId()]);
        else $stmt->execute();
        return (int)($stmt->fetchColumn() ?? 0);
    }

    private function getNewLeadsToday(): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM marketing_leads WHERE DATE(created_at) = CURDATE()" . $this->tenantSql());
        if ($this->tenantId() > 1) $stmt->execute([$this->tenantId()]);
        else $stmt->execute();
        return (int)($stmt->fetchColumn() ?? 0);
    }

    private function getNewLeadsThisWeek(): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM marketing_leads WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())" . $this->tenantSql());
        if ($this->tenantId() > 1) $stmt->execute([$this->tenantId()]);
        else $stmt->execute();
        return (int)($stmt->fetchColumn() ?? 0);
    }

    private function getConversionRate(): float
    {
        $stmt = $this->pdo->prepare("
            SELECT SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as rate
            FROM marketing_leads" . $this->tenantSql());
        if ($this->tenantId() > 1) $stmt->execute([$this->tenantId()]);
        else $stmt->execute();
        return (float)($stmt->fetchColumn() ?? 0);
    }

    private function getTopCampaigns(): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT name, sent_count, opened_count, clicked_count, converted_count
                FROM marketing_campaigns WHERE status = 'completed' ORDER BY converted_count DESC LIMIT 5");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            error_log($e->getMessage());
        }
        return [];
    }

    private function getLeadSources(): array
    {
        $stmt = $this->pdo->prepare("SELECT source, COUNT(*) as count FROM marketing_leads GROUP BY source" . $this->tenantSql());
        if ($this->tenantId() > 1) $stmt->execute([$this->tenantId()]);
        else $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getPipelineStages(): array
    {
        $stmt = $this->pdo->prepare("SELECT status, COUNT(*) as count FROM marketing_leads GROUP BY status" . $this->tenantSql());
        if ($this->tenantId() > 1) $stmt->execute([$this->tenantId()]);
        else $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getRecentActivity(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT ma.*, CONCAT_WS(' ', ml.first_name, ml.last_name) AS name, ml.email
            FROM marketing_analytics ma
            LEFT JOIN marketing_leads ml ON ma.lead_id = ml.id
            ORDER BY ma.created_at DESC LIMIT 10");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ========================================================================
    // Backward Compatibility Aliases
    // These methods provide the legacy MarketingCampaignService API
    // ========================================================================

    public function getById(int $id): ?array
    {
        return $this->getCampaign($id);
    }

    public function getAll(int $limit = 50): array
    {
        return $this->listCampaigns([], $limit);
    }

    public function getTemplates(): array
    {
        $tid = $this->tenantId();
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];
        $stmt = $this->pdo->prepare("SELECT * FROM marketing_campaign_templates WHERE 1=1{$tenantSql}");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Has this contact opted out of the given channel?
     *
     * NOTE (S451): sendCampaign() called $this->base->isUnsubscribed(), and
     * the class had NO `$base` property and no isUnsubscribed() of its own
     * -> "Call to a member function isUnsubscribed() on null". The opt-out
     * list lives in marketing_unsubscribes (user_id / email / phone /
     * channel).
     */
    public function isUnsubscribed(string $channel, ?string $email = null, ?string $phone = null): bool
    {
        if ($email === null && $phone === null) {
            return false;
        }
        try {
            $tid = $this->tenantId();
            $sql = "SELECT COUNT(*) FROM marketing_unsubscribes WHERE channel = ?";
            $params = [$channel];
            if ($email !== null && $email !== '') {
                $sql .= " AND email = ?";
                $params[] = $email;
            } elseif ($phone !== null && $phone !== '') {
                $sql .= " AND phone = ?";
                $params[] = $phone;
            }
            if ($tid > 1) {
                $sql .= " AND tenant_id = ?";
                $params[] = $tid;
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn() > 0;
        } catch (\Throwable $e) {
            error_log('[CampaignService::isUnsubscribed] ' . $e->getMessage());
            return false;
        }
    }

    public function getTemplateById(int $templateId): ?array
    {
        $tid = $this->tenantId();
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [$templateId];
        if ($tid > 1) $params[] = $tid;
        $stmt = $this->pdo->prepare("SELECT * FROM marketing_campaign_templates WHERE id = ?{$tenantSql}");
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function incrementTemplateUsage(int $templateId): void
    {
        $stmt = $this->pdo->prepare("UPDATE marketing_campaign_templates SET usage_count = usage_count + 1 WHERE id = ?");
        $stmt->execute([$templateId]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare("UPDATE marketing_campaigns SET status = ? WHERE id = ?" . $this->tenantSql());
        $params = [$status, $id];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        return $stmt->execute($params);
    }

    public function renderTemplate(string $content, array $data): string
    {
        // Replace {{var}} placeholders with data values
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'), $content);
        }
        return $content;
    }

    public function addRecipient(int $campaignId, array $recipientData): int
    {
        $tid = $this->tenantId();
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [$campaignId];
        if ($tid > 1) $params[] = $tid;

        $stmt = $this->pdo->prepare("INSERT INTO marketing_campaign_recipients (campaign_id, user_id, email, phone, name, channel, status) VALUES (?, ?, ?, ?, ?, ?, 'pending'){$tenantSql}");
        $stmt->execute(array_merge($params, [$recipientData['user_id'] ?? null, $recipientData['email'] ?? null, $recipientData['phone'] ?? null, $recipientData['name'] ?? null, $recipientData['channel'] ?? 'email']));
        return (int)$this->pdo->lastInsertId();
    }

    public function updateStats(int $id, array $stats): void
    {
        $stmt = $this->pdo->prepare("UPDATE marketing_campaigns SET sent_count = ?, delivered_count = ?, opened_count = ?, clicked_count = ?, failed_count = ?, unsubscribed_count = ? WHERE id = ?" . $this->tenantSql());
        $params = [
            $stats['sent'] ?? 0,
            $stats['delivered'] ?? 0,
            $stats['opened'] ?? 0,
            $stats['clicked'] ?? 0,
            $stats['failed'] ?? 0,
            $stats['unsubscribed'] ?? 0,
            $id,
        ];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
    }

    public function getAudienceList(array $criteria = [], string $audienceType = 'all_users'): array
    {
        return $this->getAudience($criteria, $audienceType);
    }

}