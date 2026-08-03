<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use Exception;

class SocialMediaService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ==================== ACCOUNT MANAGEMENT ====================

    public function getAccounts(array $filters = []): array
    {
        $sql = "SELECT sa.*,
            (SELECT COUNT(*) FROM social_media_leads WHERE account_id = sa.id) as leads_count,
            (SELECT COUNT(*) FROM social_media_campaigns WHERE account_id = sa.id AND status = 'active') as active_campaigns,
            (SELECT MAX(started_at) FROM social_media_sync_log WHERE account_id = sa.id) as last_sync_at
            FROM social_media_accounts sa
            WHERE 1=1";
        $params = [];

        if (!empty($filters['platform'])) {
            $sql .= " AND sa.platform = ?";
            $params[] = $filters['platform'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND sa.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['user_id'])) {
            $sql .= " AND sa.user_id = ?";
            $params[] = $filters['user_id'];
        }

        $sql .= $this->tenantSqlForAlias("sa") . " ORDER BY sa.created_at DESC";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getAccount(int $id): ?array
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM social_media_accounts WHERE id = ?" . $this->tenantSql(),
            [$id]
        );
        return $row ?: null;
    }

    public function createAccount(array $data): int
    {
        $insertData = $this->tenantInsertData();
        $cols = "user_id, platform, account_id, account_name, account_type, access_token,
             refresh_token, token_expires_at, scopes, status, metadata";
        $vals = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
        $params = [
            $data['user_id'] ?? null,
            $data['platform'],
            $data['account_id'] ?? '',
            $data['account_name'],
            $data['account_type'] ?? 'business_page',
            $data['access_token'] ?? null,
            $data['refresh_token'] ?? null,
            $data['token_expires_at'] ?? null,
            isset($data['scopes']) ? json_encode($data['scopes']) : null,
            $data['status'] ?? 'connected',
            isset($data['metadata']) ? json_encode($data['metadata']) : null,
        ];
        if (!empty($insertData)) {
            $cols .= ", " . implode(', ', array_keys($insertData));
            $vals .= ", " . str_repeat('?,', count($insertData) - 1) . '?';
            $params = array_merge($params, array_values($insertData));
        }

        $sql = "INSERT INTO social_media_accounts ($cols) VALUES ($vals)";
        $this->db->execute($sql, $params);
        return (int)$this->db->lastInsertId();
    }

    public function updateAccount(int $id, array $data): bool
    {
        $allowed = ['account_name', 'account_type', 'access_token', 'refresh_token', 'token_expires_at', 'scopes', 'status', 'metadata'];
        $set = [];
        $params = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $set[] = "`$field` = ?";
                $val = $data[$field];
                if (in_array($field, ['scopes', 'metadata']) && !is_null($val)) {
                    $val = json_encode($val);
                }
                $params[] = $val;
            }
        }

        if (empty($set)) return false;

        $params[] = $id;
        $sql = "UPDATE social_media_accounts SET " . implode(', ', $set) . ", updated_at = NOW() WHERE id = ?" . $this->tenantSql();
        $this->db->execute($sql, $params);
        return true;
    }

    public function deleteAccount(int $id): bool
    {
        $this->db->execute("DELETE FROM social_media_accounts WHERE id = ?" . $this->tenantSql(), [$id]);
        return true;
    }

    // ==================== LEAD SYNC ====================

    public function syncLeads(int $accountId, array $options = []): array
    {
        $account = $this->getAccount($accountId);
        if (!$account) throw new Exception("Account not found: $accountId");

        $this->logSync($accountId, 'leads', 'started');

        try {
            $leads = $this->fetchLeadsFromPlatform($account, $options);
            $stats = ['fetched' => count($leads), 'new' => 0, 'updated' => 0];

            foreach ($leads as $lead) {
                $result = $this->upsertLead($accountId, $lead);
                if ($result === 'insert') $stats['new']++;
                elseif ($result === 'update') $stats['updated']++;
            }

            $this->logSync($accountId, 'leads', 'completed', $stats['fetched'], $stats['new'], $stats['updated']);

            $this->db->execute(
                "UPDATE social_media_accounts SET last_sync_at = NOW() WHERE id = ?",
                [$accountId]
            );

            return ['success' => true, 'stats' => $stats];

        } catch (Exception $e) {
            $this->logSync($accountId, 'leads', 'failed', 0, 0, 0, $e->getMessage());
            throw $e;
        }
    }

    private function fetchLeadsFromPlatform(array $account, array $options): array
    {
        $platform = $account['platform'];
        $since = $options['since'] ?? date('Y-m-d H:i:s', strtotime('-7 days'));

        switch ($platform) {
            case 'facebook':
            case 'instagram':
                return $this->fetchFacebookLeads($account, $since);
            case 'linkedin':
                return $this->fetchLinkedInLeads($account, $since);
            case 'whatsapp_business':
                return $this->fetchWhatsAppLeads($account, $since);
            default:
                return [];
        }
    }

    private function fetchFacebookLeads(array $account, string $since): array
    {
        $appId = $this->getConfig('fb_app_id');
        $appSecret = $this->getConfig('fb_app_secret');
        if (!$appId || !$appSecret || !$account['access_token']) return [];

        $url = "https://graph.facebook.com/v18.0/{$account['account_id']}/leadgen_forms";
        $params = ['access_token' => $account['access_token'], 'fields' => 'id,name'];

        $forms = $this->httpGet($url, $params);
        $allLeads = [];

        foreach (($forms['data'] ?? []) as $form) {
            $leadUrl = "https://graph.facebook.com/v18.0/{$form['id']}/leads";
            $leadParams = [
                'access_token' => $account['access_token'],
                'fields' => 'id,field_data,created_time,ad_id,form_id',
                'filtering' => json_encode([['field' => 'created_time', 'operator' => 'GREATER_THAN', 'value' => strtotime($since)]])
            ];
            $leads = $this->httpGet($leadUrl, $leadParams);
            foreach (($leads['data'] ?? []) as $lead) {
                $allLeads[] = $this->normalizeFacebookLead($lead, $form['id'], $form['name']);
            }
        }
        return $allLeads;
    }

    private function normalizeFacebookLead(array $lead, string $formId, string $formName): array
    {
        $data = [
            'platform_lead_id' => $lead['id'],
            'form_id' => $formId,
            'form_name' => $formName,
            'raw_fields' => [],
        ];
        foreach (($lead['field_data'] ?? []) as $field) {
            $key = strtolower($field['name']);
            $val = $field['values'][0] ?? '';
            if (str_contains($key, 'email')) $data['email'] = $val;
            elseif (str_contains($key, 'phone') || str_contains($key, 'mobile')) $data['phone'] = $val;
            elseif (str_contains($key, 'name')) $data['full_name'] = $val;
            elseif (str_contains($key, 'city') || str_contains($key, 'location')) $data['city'] = $val;
            elseif (str_contains($key, 'state')) $data['state'] = $val;
            elseif (str_contains($key, 'budget')) {
                $num = preg_replace('/[^0-9.]/', '', $val);
                if ($num) $data['budget_min'] = (float)$num;
            }
            $data['raw_fields'][$key] = $val;
        }
        $data['created_at_platform'] = $lead['created_time'] ?? date('c');
        $data['source_ad'] = $lead['ad_id'] ?? null;
        return $data;
    }

    private function fetchLinkedInLeads(array $account, string $since): array
    {
        $clientId = $this->getConfig('li_client_id');
        $clientSecret = $this->getConfig('li_client_secret');
        if (!$clientId || !$clientSecret || !$account['access_token']) return [];
        return [];
    }

    private function fetchWhatsAppLeads(array $account, string $since): array
    {
        $phoneId = $this->getConfig('wa_phone_id');
        $token = $this->getConfig('wa_token');
        if (!$phoneId || !$token) return [];
        return [];
    }

    private function upsertLead(int $accountId, array $lead): string
    {
        $existing = $this->db->fetchOne(
            "SELECT id FROM social_media_leads WHERE account_id = ? AND platform_lead_id = ?" . $this->tenantSql(),
            [$accountId, $lead['platform_lead_id']]
        );

        $data = [
            'platform' => $lead['platform'] ?? null,
            'form_name' => $lead['form_name'] ?? null,
            'full_name' => $lead['full_name'] ?? null,
            'email' => $lead['email'] ?? null,
            'phone' => $lead['phone'] ?? null,
            'city' => $lead['city'] ?? null,
            'state' => $lead['state'] ?? null,
            'budget_min' => $lead['budget_min'] ?? null,
            'budget_max' => $lead['budget_max'] ?? null,
            'source_ad' => $lead['source_ad'] ?? null,
            'raw_data' => json_encode($lead['raw_fields'] ?? []),
        ];

        if ($existing) {
            $tid = $this->isTenantScoped() ? $this->tenantId() : null;
            $set = [];
            $params = [];
            foreach ($data as $col => $val) {
                $set[] = "$col = ?";
                $params[] = $val;
            }
            $params[] = $existing['id'];
            $sql = "UPDATE social_media_leads SET " . implode(', ', $set) . ", updated_at=NOW() WHERE id = ?";
            if ($tid) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->execute($sql, $params);
            return 'update';
        }

        $insertData = $this->tenantInsertData();
        $cols = "account_id, platform_lead_id, form_name, full_name, email, phone, city, state, budget_min, budget_max, source_ad, raw_data, status";
        $vals = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'new'";
        $params = [$accountId, $lead['platform_lead_id'], $data['form_name'], $data['full_name'], $data['email'], $data['phone'], $data['city'], $data['state'], $data['budget_min'], $data['budget_max'], $data['source_ad'], $data['raw_data']];
        if (!empty($insertData)) {
            $cols .= ", " . implode(', ', array_keys($insertData));
            $vals .= ", " . str_repeat('?,', count($insertData) - 1) . '?';
            $params = array_merge($params, array_values($insertData));
        }

        $this->db->execute(
            "INSERT INTO social_media_leads ($cols, created_at) VALUES ($vals, NOW())",
            $params
        );
        return 'insert';
    }

    public function getLeads(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $sql = "SELECT l.*, a.platform, a.account_name FROM social_media_leads l JOIN social_media_accounts a ON a.id = l.account_id WHERE 1=1";
        $params = [];

        if (!empty($filters['account_id'])) { $sql .= " AND l.account_id = ?"; $params[] = $filters['account_id']; }
        if (!empty($filters['status'])) { $sql .= " AND l.status = ?"; $params[] = $filters['status']; }
        if (!empty($filters['platform'])) { $sql .= " AND a.platform = ?"; $params[] = $filters['platform']; }
        if (!empty($filters['search'])) { $sql .= " AND (l.full_name LIKE ? OR l.email LIKE ? OR l.phone LIKE ?)"; $term = "%{$filters['search']}%"; $params[] = $term; $params[] = $term; $params[] = $term; }

        $countSql = "SELECT COUNT(*) as c FROM social_media_leads l JOIN social_media_accounts a ON a.id = l.account_id WHERE 1=1";
        $countParams = [];
        $cw = '';
        if (!empty($filters['account_id'])) { $cw .= " AND l.account_id = ?"; $countParams[] = $filters['account_id']; }
        if (!empty($filters['status'])) { $cw .= " AND l.status = ?"; $countParams[] = $filters['status']; }
        if (!empty($filters['platform'])) { $cw .= " AND a.platform = ?"; $countParams[] = $filters['platform']; }
        if (!empty($filters['search'])) { $cw .= " AND (l.full_name LIKE ? OR l.email LIKE ? OR l.phone LIKE ?)"; $term = "%{$filters['search']}%"; $countParams[] = $term; $countParams[] = $term; $countParams[] = $term; }

        $countSql .= $this->tenantSqlForAlias("l");
        $total = (int)($this->db->fetchOne($countSql . $cw, $countParams)['c'] ?? 0);

        $sql .= $this->tenantSqlForAlias("l") . " ORDER BY l.created_at DESC";
        $offset = ($page - 1) * $limit;
        $sql .= " LIMIT $limit OFFSET $offset";

        $data = $this->db->fetchAll($sql, $params) ?: [];

        return ['data' => $data, 'total' => $total, 'page' => $page, 'limit' => $limit];
    }

    public function updateLeadStatus(int $leadId, string $status, int $assignedTo = null): bool
    {
        $sql = "UPDATE social_media_leads SET status = ?, last_activity_at = NOW()";
        $params = [$status];
        if ($assignedTo) { $sql .= ", assigned_to = ?"; $params[] = $assignedTo; }
        $sql .= " WHERE id = ?" . $this->tenantSql(); $params[] = $leadId;
        $this->db->execute($sql, $params);
        return true;
    }

    public function assignLead(int $leadId, int $userId): bool
    {
        $this->db->execute("UPDATE social_media_leads SET assigned_to = ?, last_activity_at = NOW() WHERE id = ?" . $this->tenantSql(), [$userId, $leadId]);
        return true;
    }

    // ==================== CAMPAIGN MANAGEMENT ====================

    public function getCampaigns(int $accountId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM social_media_campaigns WHERE account_id = ? ORDER BY created_at DESC",
            [$accountId]
        ) ?: [];
    }

    public function createCampaign(int $accountId, array $data): int
    {
        $insertData = $this->tenantInsertData();
        $cols = "account_id, platform_campaign_id, platform, name, objective, status, daily_budget, lifetime_budget, start_date, end_date, targeting, creative_preview";
        $vals = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
        $params = [
            $accountId,
            $data['platform_campaign_id'] ?? '',
            $data['platform'] ?? 'facebook',
            $data['name'],
            $data['objective'] ?? 'lead_generation',
            $data['status'] ?? 'active',
            $data['daily_budget'] ?? null,
            $data['lifetime_budget'] ?? null,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            isset($data['targeting']) ? json_encode($data['targeting']) : null,
            isset($data['creative_preview']) ? json_encode($data['creative_preview']) : null,
        ];
        if (!empty($insertData)) {
            $cols .= ", " . implode(', ', array_keys($insertData));
            $vals .= ", " . str_repeat('?,', count($insertData) - 1) . '?';
            $params = array_merge($params, array_values($insertData));
        }

        $sql = "INSERT INTO social_media_campaigns ($cols) VALUES ($vals)";
        $this->db->execute($sql, $params);
        return (int)$this->db->lastInsertId();
    }

    // ==================== POSTS / CONTENT ====================

    public function createPost(array $accountId, array $data): int
    {
        $insertData = $this->tenantInsertData();
        $cols = "platform, post_content, image_url, post_url, posted_by, scheduled_date, status";
        $vals = "?, ?, ?, ?, ?, ?, ?";
        $params = [
            $data['platform'] ?? 'facebook',
            $data['post_content'] ?? '',
            $data['image_url'] ?? null,
            $data['post_url'] ?? null,
            $data['posted_by'] ?? null,
            $data['scheduled_date'] ?? null,
            $data['status'] ?? 'draft',
        ];
        if (!empty($insertData)) {
            $cols .= ", " . implode(', ', array_keys($insertData));
            $vals .= ", " . str_repeat('?,', count($insertData) - 1) . '?';
            $params = array_merge($params, array_values($insertData));
        }

        $sql = "INSERT INTO social_media_posts ($cols, engagement_likes, engagement_shares, engagement_comments, created_at) VALUES ($vals, 0, 0, 0, NOW())";
        $this->db->execute($sql, $params);
        return (int)$this->db->lastInsertId();
    }

    public function getPosts(int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM social_media_posts" . $this->tenantSql() . " ORDER BY created_at DESC LIMIT ?",
            [$limit]
        ) ?: [];
    }

    // ==================== ANALYTICS / INSIGHTS ====================

    public function getAccountInsights(int $accountId, string $period = '7d'): array
    {
        $since = $this->periodToDate($period);
        $leads = $this->db->fetchAll(
            "SELECT DATE(created_at) as date, COUNT(*) as count FROM social_media_leads WHERE account_id = ? AND created_at >= ?" . $this->tenantSql() . " GROUP BY DATE(created_at) ORDER BY date",
            [$accountId, $since]
        );

        $totalLeads = $this->db->fetchOne("SELECT COUNT(*) as c FROM social_media_leads WHERE account_id = ?" . $this->tenantSql(), [$accountId])['c'] ?? 0;
        $newLeads = $this->db->fetchOne("SELECT COUNT(*) as c FROM social_media_leads WHERE account_id = ? AND status = 'new'" . $this->tenantSql(), [$accountId])['c'] ?? 0;
        $contacted = $this->db->fetchOne("SELECT COUNT(*) as c FROM social_media_leads WHERE account_id = ? AND status = 'contacted'" . $this->tenantSql(), [$accountId])['c'] ?? 0;
        $converted = $this->db->fetchOne("SELECT COUNT(*) as c FROM social_media_leads WHERE account_id = ? AND status IN ('converted', 'qualified')" . $this->tenantSql(), [$accountId])['c'] ?? 0;

        $account = $this->getAccount($accountId);
        $leadsByStatus = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM social_media_leads WHERE account_id = ?" . $this->tenantSql() . " GROUP BY status",
            [$accountId]
        );

        return [
            'account' => $account,
            'period' => $period,
            'total_leads' => (int)$totalLeads,
            'new_leads' => (int)$newLeads,
            'contacted' => (int)$contacted,
            'converted' => (int)$converted,
            'conversion_rate' => $totalLeads > 0 ? round(($converted / $totalLeads) * 100, 1) : 0,
            'leads_trend' => $leads,
            'leads_by_status' => $leadsByStatus,
        ];
    }

    public function getAllCampaigns(): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, a.account_name FROM social_media_campaigns c" . $this->tenantSqlForAlias("c") . "
              LEFT JOIN social_media_accounts a ON a.id = c.account_id" . $this->tenantSqlForAlias("a") . "
              ORDER BY c.created_at DESC"
        ) ?: [];
    }

    public function getAllInsights(string $period = '7d'): array
    {
        $since = $this->periodToDate($period);
        $tid = $this->tenantId();
        $leads = $this->db->fetchAll(
            "SELECT DATE(created_at) as date, COUNT(*) as count FROM social_media_leads WHERE created_at >= ?" . ($tid > 1 ? " AND tenant_id = $tid" : "") . " GROUP BY DATE(created_at) ORDER BY date",
            [$since]
        );
        $tenantSql = $tid > 1 ? " AND tenant_id = $tid" : "";
        $totalLeads = $this->db->fetchOne("SELECT COUNT(*) as c FROM social_media_leads" . $tenantSql)['c'] ?? 0;
        $newLeads = $this->db->fetchOne("SELECT COUNT(*) as c FROM social_media_leads WHERE status = 'new'" . $tenantSql)['c'] ?? 0;
        $contacted = $this->db->fetchOne("SELECT COUNT(*) as c FROM social_media_leads WHERE status = 'contacted'" . $tenantSql)['c'] ?? 0;
        $converted = $this->db->fetchOne("SELECT COUNT(*) as c FROM social_media_leads WHERE status IN ('converted', 'qualified')" . $tenantSql)['c'] ?? 0;
        $leadsByStatus = $this->db->fetchAll("SELECT status, COUNT(*) as count FROM social_media_leads" . $tenantSql . " GROUP BY status");
        $leadsByAccount = $this->db->fetchAll(
            "SELECT a.account_name, COUNT(*) as count FROM social_media_leads l
             LEFT JOIN social_media_accounts a ON a.id = l.account_id" . ($tid > 1 ? " WHERE l.tenant_id = $tid" : "") . "
             GROUP BY l.account_id ORDER BY count DESC"
        );
        return [
            'account' => null,
            'period' => $period,
            'total_leads' => (int)$totalLeads,
            'new_leads' => (int)$newLeads,
            'contacted' => (int)$contacted,
            'converted' => (int)$converted,
            'conversion_rate' => $totalLeads > 0 ? round(($converted / $totalLeads) * 100, 1) : 0,
            'leads_trend' => $leads,
            'leads_by_status' => $leadsByStatus,
            'leads_by_account' => $leadsByAccount,
        ];
    }

    // ==================== HELPERS ====================

    private function getConfig(string $key): ?string
    {
        $row = $this->db->fetchOne(
            "SELECT content_value FROM site_content WHERE section = 'social_media' AND content_key = ?",
            [$key]
        );
        return $row['content_value'] ?? null;
    }

    private function httpGet(string $url, array $params): array
    {
        $ch = curl_init($url . '?' . http_build_query($params));
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_SSL_VERIFYPEER => false]);
        $resp = curl_exec($ch);
        curl_close($ch);
        return json_decode($resp, true) ?: [];
    }

    private function logSync(int $accountId, string $type, string $status, int $fetched = 0, int $new = 0, int $updated = 0, string $error = null): void
    {
        $insertData = $this->tenantInsertData();
        $cols = "account_id, sync_type, status, records_fetched, records_new, records_updated, error_message";
        $vals = "?, ?, ?, ?, ?, ?, ?";
        $params = [$accountId, $type, $status, $fetched, $new, $updated, $error];
        if (!empty($insertData)) {
            $cols .= ", " . implode(', ', array_keys($insertData));
            $vals .= ", " . str_repeat('?,', count($insertData) - 1) . '?';
            $params = array_merge($params, array_values($insertData));
        }

        $this->db->execute(
            "INSERT INTO social_media_sync_log ($cols, completed_at) VALUES ($vals, ?)",
            array_merge($params, [$status !== 'started' ? date('Y-m-d H:i:s') : null])
        );
    }

    private function periodToDate(string $period): string
    {
        return match($period) {
            '1d' => date('Y-m-d', strtotime('-1 day')),
            '7d' => date('Y-m-d', strtotime('-7 days')),
            '30d' => date('Y-m-d', strtotime('-30 days')),
            '90d' => date('Y-m-d', strtotime('-90 days')),
            default => date('Y-m-d', strtotime('-7 days')),
        };
    }
}
