<?php
/**
 * Call Recording Service
 * Handles call recordings tied to leads
 */

namespace App\Services;

use App\Core\Database\Database;

class CallRecordingService
{
    private $db;

    public function __construct(?\PDO $pdo = null)
    {
        $this->db = $pdo ?? Database::getInstance();
    }

    /**
     * Get recordings for associate's leads
     */
    public function getAssociateRecordings(int $associateId, array $filters = []): array
    {
        $where = ['cr.associate_id = ?'];
        $params = [$associateId];

        if (!empty($filters['lead_id'])) {
            $where[] = 'cr.lead_id = ?';
            $params[] = $filters['lead_id'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(cr.created_at) >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(cr.created_at) <= ?';
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['duration_min'])) {
            $where[] = 'cr.duration >= ?';
            $params[] = (int)$filters['duration_min'] * 60; // convert minutes to seconds
        }

        if (!empty($filters['search'])) {
            $where[] = '(l.name LIKE ? OR l.phone LIKE ? OR cr.notes LIKE ?)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $sql = "
            SELECT cr.*, l.name as lead_name, l.phone as lead_phone, l.lead_number
            FROM call_recordings cr
            LEFT JOIN leads l ON cr.lead_id = l.id
            $whereClause
            ORDER BY cr.created_at DESC
        ";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get recordings for a specific lead
     */
    public function getLeadRecordings(int $leadId, int $associateId): array
    {
        // Verify lead belongs to associate
        $lead = $this->db->fetchOne("SELECT id FROM leads WHERE id = ? AND assigned_to = ?", [$leadId, $associateId]);
        if (!$lead) {
            return [];
        }

        return $this->db->fetchAll("
            SELECT cr.*, l.name as lead_name
            FROM call_recordings cr
            LEFT JOIN leads l ON cr.lead_id = l.id
            WHERE cr.lead_id = ? AND cr.associate_id = ?
            ORDER BY cr.created_at DESC
        ", [$leadId, $associateId]);
    }

    /**
     * Get single recording with access check
     */
    public function getRecording(int $id, int $associateId): ?array
    {
        return $this->db->fetchOne("
            SELECT cr.*, l.name as lead_name, l.phone as lead_phone, l.lead_number
            FROM call_recordings cr
            LEFT JOIN leads l ON cr.lead_id = l.id
            WHERE cr.id = ? AND cr.associate_id = ?
        ", [$id, $associateId]);
    }

    /**
     * Get recording stats for associate
     */
    public function getRecordingStats(int $associateId): array
    {
        return [
            'total' => (int)$this->db->fetchOne("SELECT COUNT(*) FROM call_recordings WHERE associate_id = ?", [$associateId]) ?: 0,
            'total_duration' => (int)$this->db->fetchOne("SELECT COALESCE(SUM(duration), 0) FROM call_recordings WHERE associate_id = ?", [$associateId]) ?: 0,
            'this_month' => (int)$this->db->fetchOne("SELECT COUNT(*) FROM call_recordings WHERE associate_id = ? AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())", [$associateId]) ?: 0,
            'avg_duration' => (int)$this->db->fetchOne("SELECT COALESCE(AVG(duration), 0) FROM call_recordings WHERE associate_id = ?", [$associateId]) ?: 0,
        ];
    }

    /**
     * Store new call recording
     */
    public function storeRecording(int $associateId, array $data): array
    {
        try {
            $this->db->execute("
                INSERT INTO call_recordings (
                    associate_id, lead_id, file_path, file_name, 
                    duration, call_type, direction, caller_number, 
                    notes, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ", [
                $associateId,
                $data['lead_id'] ?? null,
                $data['file_path'] ?? '',
                $data['file_name'] ?? '',
                $data['duration'] ?? 0,
                $data['call_type'] ?? 'voice',
                $data['direction'] ?? 'outbound',
                $data['caller_number'] ?? '',
                $data['notes'] ?? '',
            ]);

            return ['success' => true, 'message' => 'Recording saved', 'id' => $this->db->lastInsertId()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Failed to save recording: ' . $e->getMessage()];
        }
    }

    /**
     * Delete recording
     */
    public function deleteRecording(int $id, int $associateId): array
    {
        $recording = $this->getRecording($id, $associateId);
        if (!$recording) {
            return ['success' => false, 'message' => 'Recording not found or access denied'];
        }

        try {
            // Delete file if exists
            if (!empty($recording['file_path']) && file_exists($recording['file_path'])) {
                @unlink($recording['file_path']);
            }

            $this->db->execute("DELETE FROM call_recordings WHERE id = ? AND associate_id = ?", [$id, $associateId]);

            return ['success' => true, 'message' => 'Recording deleted'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage()];
        }
    }

    /**
     * Add note to recording
     */
    public function addNote(int $id, int $associateId, string $note): array
    {
        $recording = $this->getRecording($id, $associateId);
        if (!$recording) {
            return ['success' => false, 'message' => 'Recording not found'];
        }

        try {
            $this->db->execute("
                UPDATE call_recordings 
                SET notes = CONCAT(COALESCE(notes, ''), '\n', NOW(), ' - ', ?), 
                    updated_at = NOW()
                WHERE id = ? AND associate_id = ?
            ", [$note, $id, $associateId]);

            return ['success' => true, 'message' => 'Note added'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Failed to add note: ' . $e->getMessage()];
        }
    }
}