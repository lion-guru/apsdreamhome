<?php
/**
 * MeetingService — Calendar-based meeting scheduling
 */
namespace App\Services;

use App\Core\Database;
use \App\Traits\ServiceTenantTrait;

class MeetingService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getMeetings($filters = []) {
        try {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND m.tenant_id = ?" : "";
            $where = ["1=1"]; $params = [];
            $params[] = $tid > 1 ? $tid : null;
            if (!empty($filters['user_id'])) { $where[] = "m.user_id = ?"; $params[] = $filters['user_id']; }
            if (!empty($filters['lead_id'])) { $where[] = "m.lead_id = ?"; $params[] = $filters['lead_id']; }
            if (!empty($filters['status'])) { $where[] = "m.status = ?"; $params[] = $filters['status']; }
            if (!empty($filters['date_from'])) { $where[] = "m.start_time >= ?"; $params[] = $filters['date_from']; }
            if (!empty($filters['date_to'])) { $where[] = "m.start_time <= ?"; $params[] = $filters['date_to'] . ' 23:59:59'; }
            if (!empty($filters['meeting_type'])) { $where[] = "m.meeting_type = ?"; $params[] = $filters['meeting_type']; }

            return $this->db->fetchAll(
                "SELECT m.*, l.name as lead_name, l.phone as lead_phone, u.name as agent_name
                 FROM crm_meetings m
                 LEFT JOIN leads l ON l.id = m.lead_id
                 LEFT JOIN users u ON u.id = m.user_id
                 WHERE " . implode(' AND ', $where) . $tidSql . " ORDER BY m.start_time DESC LIMIT 100",
                $tid > 1 ? $params : array_slice($params, 1)
            ) ?: [];
        } catch (\Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }

    public function getMeetingById($id) {
        try {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND m.tenant_id = ?" : "";
            return $this->db->fetch(
                "SELECT m.*, l.name as lead_name, l.phone as lead_phone, l.email as lead_email, u.name as agent_name
                 FROM crm_meetings m
                 LEFT JOIN leads l ON l.id = m.lead_id
                 LEFT JOIN users u ON u.id = m.user_id
                 WHERE m.id = ?" . $tidSql,
                $tid > 1 ? [$id, $tid] : [$id]
            );
        } catch (\Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return null; }
    }

    public function createMeeting($data) {
        try {
            $tid = $this->tenantId();
            $tidCol = $tid > 1 ? ", tenant_id" : "";
            $tidVal = $tid > 1 ? ", ?" : "";
            $this->db->query(
                "INSERT INTO crm_meetings (lead_id, user_id, meeting_type, title, description, location, start_time, end_time, status, created_by" . $tidCol . ")
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', ?" . $tidVal . ")",
                array_merge([
                    $data['lead_id'], $data['user_id'], $data['meeting_type'] ?? 'site_visit',
                    $data['title'], $data['description'] ?? null, $data['location'] ?? null,
                    $data['start_time'], $data['end_time'] ?? null, $data['created_by'] ?? null
                ], $tid > 1 ? [$tid] : [])
            );
            $id = $this->db->lastInsertId();

            // Log activity
            $crm = new CRMService();
            $crm->logActivity($data['lead_id'], 'meeting', "Meeting scheduled: " . $data['title'], null, null, $data['created_by'] ?? null);

            return ['success' => true, 'id' => $id];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function updateMeeting($id, $data) {
        try {
            $tid = $this->tenantId();
            $whereClause = $tid > 1 ? " AND tenant_id = ?" : "";
            $fields = []; $params = [];
            foreach (['lead_id','user_id','meeting_type','title','description','location','start_time','end_time','status','notes','outcome'] as $f) {
                if (array_key_exists($f, $data)) { $fields[] = "$f = ?"; $params[] = $data[$f]; }
            }
            if (empty($fields)) return ['success' => false, 'error' => 'No fields'];
            $params[] = $id;
            if ($tid > 1) $params[] = $tid;
            $this->db->query("UPDATE crm_meetings SET " . implode(', ', $fields) . " WHERE id = ?" . $whereClause, $params);
            return ['success' => true];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function deleteMeeting($id) {
        try {
            $tid = $this->tenantId();
            $whereClause = $tid > 1 ? " AND tenant_id = ?" : "";
            $this->db->query("DELETE FROM crm_meetings WHERE id = ?" . $whereClause, $tid > 1 ? [$id, $tid] : [$id]);
            return ['success' => true];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function completeMeeting($id, $outcome, $notes) {
        try {
            $tid = $this->tenantId();
            $whereClause = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [$outcome, $notes, $id];
            if ($tid > 1) $params[] = $tid;
            $this->db->query(
                "UPDATE crm_meetings SET status = 'completed', outcome = ?, notes = ?, end_time = NOW() WHERE id = ?" . $whereClause,
                $params
            );
            $meeting = $this->getMeetingById($id);
            if ($meeting) {
                $crm = new CRMService();
                $crm->logActivity($meeting['lead_id'], 'meeting', "Meeting completed: " . ($outcome ?: 'No outcome'), null, null, null);
            }
            return ['success' => true];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function getUpcomingMeetings($userId, $limit = 10) {
        try {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND m.tenant_id = ?" : "";
            $params = [$userId, $limit];
            if ($tid > 1) $params[] = $tid;
            return $this->db->fetchAll(
                "SELECT m.*, l.name as lead_name, l.phone as lead_phone
                 FROM crm_meetings m LEFT JOIN leads l ON l.id = m.lead_id
                 WHERE m.user_id = ? AND m.status = 'scheduled' AND m.start_time >= NOW()" . $tidSql . "
                 ORDER BY m.start_time ASC LIMIT ?",
                $params
            ) ?: [];
        } catch (\Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }

    public function getLeadMeetings($leadId) {
        try {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND m.tenant_id = ?" : "";
            return $this->db->fetchAll(
                "SELECT m.*, u.name as agent_name FROM crm_meetings m
                 LEFT JOIN users u ON u.id = m.user_id
                 WHERE m.lead_id = ?" . $tidSql . " ORDER BY m.start_time DESC LIMIT 20",
                $tid > 1 ? [$leadId, $tid] : [$leadId]
            ) ?: [];
        } catch (\Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }

    public function getCalendarEvents($userId, $start, $end) {
        try {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND m.tenant_id = ?" : "";
            $params = [$userId, $start, $end];
            if ($tid > 1) $params[] = $tid;
            return $this->db->fetchAll(
                "SELECT m.*, l.name as lead_name FROM crm_meetings m
                 LEFT JOIN leads l ON l.id = m.lead_id
                 WHERE m.user_id = ? AND m.start_time >= ? AND m.start_time <= ? AND m.status != 'cancelled'" . $tidSql . "
                 ORDER BY m.start_time ASC",
                $params
            ) ?: [];
        } catch (\Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }

    public function getStats() {
        try {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            return $this->db->fetch(
                "SELECT COUNT(*) as total,
                    SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show
                 FROM crm_meetings WHERE start_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)" . $tidSql,
                $tid > 1 ? [$tid] : []
            ) ?: ['total' => 0, 'scheduled' => 0, 'completed' => 0, 'cancelled' => 0, 'no_show' => 0];
        } catch (\Exception $e) { return ['total' => 0, 'scheduled' => 0, 'completed' => 0, 'cancelled' => 0, 'no_show' => 0]; }
    }
}
