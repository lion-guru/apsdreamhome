<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use Exception;

class AnnouncementService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get announcements for employee
     */
    public function getEmployeeAnnouncements(int $employeeId): array
    {
        $emp = $this->fetchOne("SELECT department, role FROM users WHERE id = ?{$this->tenantSql()}", array_merge([$employeeId], $this->tVal()));
        if (!$emp) return [];

        $query = "
            SELECT ca.*, 
                   (SELECT 1 FROM announcement_reads ar WHERE ar.announcement_id = ca.id AND ar.employee_id = ?) as read
            FROM company_announcements ca
            WHERE ca.tenant_id = 1
              AND ca.status = 'published'
              AND ca.start_date <= CURDATE()
              AND (ca.end_date IS NULL OR ca.end_date >= CURDATE())
              AND (
                  ca.target_audience = 'all'
                  OR (ca.target_audience = 'department' AND JSON_CONTAINS(ca.target_departments, ?))
                  OR (ca.target_audience = 'role' AND JSON_CONTAINS(ca.target_roles, ?))
                  OR (ca.target_audience = 'specific' AND JSON_CONTAINS(ca.target_users, ?))
              )
            ORDER BY ca.is_pinned DESC, ca.created_at DESC
        ";

        return $this->fetchAll($query, [
            $this->tenantId(), $employeeId,
            json_encode($emp['department']),
            json_encode($emp['role']),
            json_encode($employeeId)
        ]);
    }

    /**
     * Mark announcement as read
     */
    public function markAsRead(int $announcementId, int $employeeId): array
    {
        $this->execute("
            INSERT IGNORE INTO announcement_reads (tenant_id, announcement_id, employee_id)
            VALUES (?, ?, ?)
        ", [$this->tenantId(), $announcementId, $employeeId]);

        return ['success' => true];
    }

    /**
     * Get unread count
     */
    public function getUnreadCount(int $employeeId): int
    {
        $emp = $this->fetchOne("SELECT department, role FROM users WHERE id = ?{$this->tenantSql()}", array_merge([$employeeId], $this->tVal()));
        if (!$emp) return 0;

        $result = $this->fetchOne("
            SELECT COUNT(*) as cnt
            FROM company_announcements ca
            WHERE ca.tenant_id = 1
              AND ca.status = 'published'
              AND ca.start_date <= CURDATE()
              AND (ca.end_date IS NULL OR ca.end_date >= CURDATE())
              AND (
                  ca.target_audience = 'all'
                  OR (ca.target_audience = 'department' AND JSON_CONTAINS(ca.target_departments, ?))
                  OR (ca.target_audience = 'role' AND JSON_CONTAINS(ca.target_roles, ?))
                  OR (ca.target_audience = 'specific' AND JSON_CONTAINS(ca.target_users, ?))
              )
              AND NOT EXISTS (
                  SELECT 1 FROM announcement_reads ar
                  WHERE ar.announcement_id = ca.id AND ar.employee_id = ?
              )
        ", [
            1, json_encode($emp['department']),
            json_encode($emp['role']),
            json_encode($employeeId),
            $employeeId
        ]);

        return (int)($result['cnt'] ?? 0);
    }

    /**
     * Create announcement (admin)
     */
    public function createAnnouncement(array $data): array
    {
        $this->execute("
            INSERT INTO company_announcements
            (tenant_id, title, content, type, priority, target_audience, target_departments, target_roles, target_users,
             start_date, end_date, is_pinned, attachments, created_by, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', NOW())
        ", [
            $this->tenantId(),
            $data['title'],
            $data['content'],
            $data['type'] ?? 'general',
            $data['priority'] ?? 'normal',
            $data['target_audience'] ?? 'all',
            json_encode($data['target_departments'] ?? []),
            json_encode($data['target_roles'] ?? []),
            json_encode($data['target_users'] ?? []),
            $data['start_date'] ?? date('Y-m-d'),
            $data['end_date'] ?? null,
            $data['is_pinned'] ?? 0,
            json_encode($data['attachments'] ?? []),
            $data['created_by'] ?? 1
        ]);

        return ['success' => true, 'id' => $this->db->lastInsertId()];
    }

    /**
     * Get all announcements (admin)
     */
    public function getAllAnnouncements(): array
    {
        return $this->fetchAll("
            SELECT ca.*, u.name as created_by_name
            FROM company_announcements ca
            LEFT JOIN users u ON ca.created_by = u.id
            WHERE ca.tenant_id = 1
            ORDER BY ca.is_pinned DESC, ca.created_at DESC
        ");
    }

    /**
     * Update announcement
     */
    public function updateAnnouncement(int $id, array $data): array
    {
        $allowed = ['title', 'content', 'type', 'priority', 'target_audience', 'target_departments', 'target_roles', 'target_users', 'start_date', 'end_date', 'is_pinned', 'status', 'attachments'];
        $updates = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $updates[] = "$key = ?";
                $params[] = is_array($value) ? json_encode($value) : $value;
            }
        }

        if (empty($updates)) return ['success' => false, 'message' => 'No valid fields'];

        $params[] = $this->tenantId();
        $params[] = $id;

        $this->execute(
            "UPDATE company_announcements SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE tenant_id = ? AND id = ?",
            $params
        );

        return ['success' => true];
    }

    /**
     * Delete announcement
     */
    public function deleteAnnouncement(int $id): array
    {
        $this->execute("DELETE FROM company_announcements WHERE tenant_id = ? AND id = ?", [$this->tenantId(), $id]);
        return ['success' => true];
    }
}