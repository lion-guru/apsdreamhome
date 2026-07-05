<?php
/**
 * MeetingService — Calendar-based meeting scheduling
 */
namespace App\Services;

use App\Core\Database;

class MeetingService
{
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getMeetings($filters = []) {
        try {
            $where = ["1=1"]; $params = [];
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
                 WHERE " . implode(' AND ', $where) . " ORDER BY m.start_time DESC LIMIT 100",
                $params
            ) ?: [];
        } catch (\Exception $e) { return []; }
    }

    public function getMeetingById($id) {
        try {
            return $this->db->fetch(
                "SELECT m.*, l.name as lead_name, l.phone as lead_phone, l.email as lead_email, u.name as agent_name
                 FROM crm_meetings m
                 LEFT JOIN leads l ON l.id = m.lead_id
                 LEFT JOIN users u ON u.id = m.user_id
                 WHERE m.id = ?", [$id]
            );
        } catch (\Exception $e) { return null; }
    }

    public function createMeeting($data) {
        try {
            $this->db->query(
                "INSERT INTO crm_meetings (lead_id, user_id, meeting_type, title, description, location, start_time, end_time, status, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', ?)",
                [
                    $data['lead_id'], $data['user_id'], $data['meeting_type'] ?? 'site_visit',
                    $data['title'], $data['description'] ?? null, $data['location'] ?? null,
                    $data['start_time'], $data['end_time'] ?? null, $data['created_by'] ?? null
                ]
            );
            $id = $this->db->lastInsertId();

            // Log activity
            require_once __DIR__ . '/CRMService.php';
            $crm = new CRMService();
            $crm->logActivity($data['lead_id'], 'meeting', "Meeting scheduled: " . $data['title'], null, null, $data['created_by'] ?? null);

            return ['success' => true, 'id' => $id];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function updateMeeting($id, $data) {
        try {
            $fields = []; $params = [];
            foreach (['lead_id','user_id','meeting_type','title','description','location','start_time','end_time','status','notes','outcome'] as $f) {
                if (array_key_exists($f, $data)) { $fields[] = "$f = ?"; $params[] = $data[$f]; }
            }
            if (empty($fields)) return ['success' => false, 'error' => 'No fields'];
            $params[] = $id;
            $this->db->query("UPDATE crm_meetings SET " . implode(', ', $fields) . " WHERE id = ?", $params);
            return ['success' => true];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function deleteMeeting($id) {
        try {
            $this->db->query("DELETE FROM crm_meetings WHERE id = ?", [$id]);
            return ['success' => true];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function completeMeeting($id, $outcome, $notes) {
        try {
            $this->db->query(
                "UPDATE crm_meetings SET status = 'completed', outcome = ?, notes = ?, completed_at = NOW() WHERE id = ?",
                [$outcome, $notes, $id]
            );
            $meeting = $this->getMeetingById($id);
            if ($meeting) {
                require_once __DIR__ . '/CRMService.php';
                $crm = new CRMService();
                $crm->logActivity($meeting['lead_id'], 'meeting', "Meeting completed: " . ($outcome ?: 'No outcome'), null, null, null);
            }
            return ['success' => true];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function getUpcomingMeetings($userId, $limit = 10) {
        try {
            return $this->db->fetchAll(
                "SELECT m.*, l.name as lead_name, l.phone as lead_phone
                 FROM crm_meetings m LEFT JOIN leads l ON l.id = m.lead_id
                 WHERE m.user_id = ? AND m.status = 'scheduled' AND m.start_time >= NOW()
                 ORDER BY m.start_time ASC LIMIT ?",
                [$userId, $limit]
            ) ?: [];
        } catch (\Exception $e) { return []; }
    }

    public function getLeadMeetings($leadId) {
        try {
            return $this->db->fetchAll(
                "SELECT m.*, u.name as agent_name FROM crm_meetings m
                 LEFT JOIN users u ON u.id = m.user_id
                 WHERE m.lead_id = ? ORDER BY m.start_time DESC LIMIT 20",
                [$leadId]
            ) ?: [];
        } catch (\Exception $e) { return []; }
    }

    public function getCalendarEvents($userId, $start, $end) {
        try {
            return $this->db->fetchAll(
                "SELECT m.*, l.name as lead_name FROM crm_meetings m
                 LEFT JOIN leads l ON l.id = m.lead_id
                 WHERE m.user_id = ? AND m.start_time >= ? AND m.start_time <= ? AND m.status != 'cancelled'
                 ORDER BY m.start_time ASC",
                [$userId, $start, $end]
            ) ?: [];
        } catch (\Exception $e) { return []; }
    }

    public function getStats() {
        try {
            return $this->db->fetch(
                "SELECT COUNT(*) as total,
                    SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show
                 FROM crm_meetings WHERE start_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            ) ?: ['total' => 0, 'scheduled' => 0, 'completed' => 0, 'cancelled' => 0, 'no_show' => 0];
        } catch (\Exception $e) { return ['total' => 0, 'scheduled' => 0, 'completed' => 0, 'cancelled' => 0, 'no_show' => 0]; }
    }
}
