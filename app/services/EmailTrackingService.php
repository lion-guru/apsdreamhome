<?php
/**
 * EmailTrackingService — Track email opens and link clicks
 */
namespace App\Services;

use App\Core\Database\Database;

use \App\Traits\ServiceTenantTrait;

class EmailTrackingService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function generateTrackingPixel($emailId, $recipient) {
        return '/api/email/track/open/' . $emailId . '?r=' . urlencode($recipient);
    }

    public function generateTrackingLink($emailId, $originalUrl, $recipient) {
        return '/api/email/track/click/' . $emailId . '?url=' . urlencode($originalUrl) . '&r=' . urlencode($recipient);
    }

    public function trackOpen($emailId, $recipient, $ipAddress, $userAgent) {
        try {
            $existing = $this->db->fetch("SELECT id FROM email_tracking WHERE email_id = ? AND recipient = ? AND event_type = 'open'" . $this->tenantSql(), array_merge([$emailId, $recipient], $this->tenantId() > 1 ? [$this->tenantId()] : []));
            if (!$existing) {
                $insertCols = array_merge(['email_id', 'recipient', 'event_type', 'ip_address', 'user_agent', 'event_at'], array_keys($this->tenantInsertData()));
                $insertVals = array_merge([$emailId, $recipient, 'open', $ipAddress, $userAgent, 'NOW()'], array_values($this->tenantInsertData()));
                $placeholders = implode(',', array_fill(0, count($insertCols), '?'));
                $this->db->query(
                    "INSERT INTO email_tracking (" . implode(', ', $insertCols) . ") VALUES (" . $placeholders . ")",
                    $insertVals
                );
                // Bump engagement score
                $this->updateEngagementScore($emailId);
            }
            return ['success' => true];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function trackClick($emailId, $recipient, $linkUrl, $ipAddress, $userAgent) {
        try {
        $insertCols = array_merge(['email_id', 'recipient', 'event_type', 'link_url', 'ip_address', 'user_agent', 'event_at'], array_keys($this->tenantInsertData()));
        $insertVals = array_merge([$emailId, $recipient, 'click', $linkUrl, $ipAddress, $userAgent, 'NOW()'], array_values($this->tenantInsertData()));
        $placeholders = implode(',', array_fill(0, count($insertCols), '?'));
        $this->db->query(
            "INSERT INTO email_tracking (" . implode(', ', $insertCols) . ") VALUES (" . $placeholders . ")",
            $insertVals
        );
            $this->updateEngagementScore($emailId);
            return ['success' => true];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    private function updateEngagementScore($emailId) {
        try {
            $tracking = $this->db->fetch("SELECT recipient FROM email_tracking WHERE email_id = ?" . $this->tenantSql(), array_merge([$emailId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
            if ($tracking) {
                $lead = $this->db->fetch("SELECT id, lead_score FROM leads WHERE email = ? AND deleted_at IS NULL" . $this->tenantSql(), array_merge([$tracking['recipient']], $this->tenantId() > 1 ? [$this->tenantId()] : []));
                if ($lead) {
                    $opens = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM email_tracking WHERE email_id = ? AND event_type = 'open'" . $this->tenantSql(), array_merge([$emailId], $this->tenantId() > 1 ? [$this->tenantId()] : []))['cnt'];
                    $clicks = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM email_tracking WHERE email_id = ? AND event_type = 'click'" . $this->tenantSql(), array_merge([$emailId], $this->tenantId() > 1 ? [$this->tenantId()] : []))['cnt'];
                    $bonus = ($opens * 2) + ($clicks * 5);
                    $newScore = min(100, (int)$lead['lead_score'] + $bonus);
                    $this->db->query("UPDATE leads SET lead_score = ? WHERE id = ?" . $this->tenantSql(), array_merge([$newScore, $lead['id']], $this->tenantId() > 1 ? [$this->tenantId()] : []));
                }
            }
        } catch (\Exception $e) { error_log($e->getMessage()); }
    }

    public function getOverallStats($days = 30) {
        try {
            $stats = $this->db->fetch(
                "SELECT COUNT(*) as total_events,
                    SUM(CASE WHEN event_type = 'open' THEN 1 ELSE 0 END) as opens,
                    SUM(CASE WHEN event_type = 'click' THEN 1 ELSE 0 END) as clicks,
                    COUNT(DISTINCT email_id) as emails_tracked,
                    COUNT(DISTINCT recipient) as unique_recipients
                 FROM email_tracking WHERE event_at >= DATE_SUB(NOW(), INTERVAL ? DAY)" . $this->tenantSql(),
                array_merge([$days], $this->tenantId() > 1 ? [$this->tenantId()] : [])
            );
            return $stats ?: ['total_events' => 0, 'opens' => 0, 'clicks' => 0, 'emails_tracked' => 0, 'unique_recipients' => 0];
        } catch (\Exception $e) { return ['total_events' => 0, 'opens' => 0, 'clicks' => 0, 'emails_tracked' => 0, 'unique_recipients' => 0]; }
    }

    public function getDailyStats($days = 30) {
        try {
            return $this->db->fetchAll(
                "SELECT DATE(event_at) as day, event_type, COUNT(*) as cnt
                 FROM email_tracking WHERE event_at >= DATE_SUB(NOW(), INTERVAL ? DAY)" . $this->tenantSql() . "
                 GROUP BY DATE(event_at), event_type ORDER BY day ASC",
                array_merge([$days], $this->tenantId() > 1 ? [$this->tenantId()] : [])
            ) ?: [];
        } catch (\Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }

    public function getLeadEmailStats($leadId) {
        try {
            $lead = $this->db->fetch("SELECT email FROM leads WHERE id = ?" . $this->tenantSql(), array_merge([$leadId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
            if (!$lead || empty($lead['email'])) return ['opens' => 0, 'clicks' => 0, 'last_activity' => null];
            $stats = $this->db->fetch(
                "SELECT SUM(CASE WHEN event_type = 'open' THEN 1 ELSE 0 END) as opens,
                    SUM(CASE WHEN event_type = 'click' THEN 1 ELSE 0 END) as clicks,
                    MAX(event_at) as last_activity
                 FROM email_tracking WHERE recipient = ?" . $this->tenantSql(),
                array_merge([$lead['email']], $this->tenantId() > 1 ? [$this->tenantId()] : [])
            );
            return $stats ?: ['opens' => 0, 'clicks' => 0, 'last_activity' => null];
        } catch (\Exception $e) { return ['opens' => 0, 'clicks' => 0, 'last_activity' => null]; }
    }

    public function getTopClickedLinks($limit = 20) {
        try {
            return $this->db->fetchAll(
                "SELECT link_url, COUNT(*) as clicks, COUNT(DISTINCT recipient) as unique_clicks
                 FROM email_tracking WHERE event_type = 'click' AND link_url IS NOT NULL" . $this->tenantSql() . "
                 GROUP BY link_url ORDER BY clicks DESC LIMIT ?",
                array_merge([$limit], $this->tenantId() > 1 ? [$this->tenantId()] : [])
            ) ?: [];
        } catch (\Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }
}
